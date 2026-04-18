'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { z } from 'zod'
import { createClient } from '@/lib/supabase/server'
import { requireLogin } from '@/lib/auth'
import { setFlash } from '@/lib/flash'
import { BUCKETS, uploadFile, deleteFile, validateUpload } from '@/lib/uploads'
import type { ActionResult } from '@/app/actions/users'

/** Schéma identique à l'ancien Validator PHP (title >= 5, body >= 20). */
const forumPostSchema = z.object({
  category: z.string().min(1, 'Catégorie requise'),
  title: z.string().min(5, 'Le titre doit contenir au moins 5 caractères').max(200),
  body: z.string().min(20, 'Le contenu doit contenir au moins 20 caractères'),
})

/** Création d'une discussion forum + optionnellement des pièces jointes. */
export async function createPostAction(formData: FormData): Promise<ActionResult<{ id: number }>> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const raw = {
    category: (formData.get('category')?.toString() ?? '').trim(),
    title: (formData.get('title')?.toString() ?? '').trim(),
    body: (formData.get('body')?.toString() ?? '').trim(),
  }
  const parsed = forumPostSchema.safeParse(raw)
  if (!parsed.success) {
    const errs: Record<string, string> = {}
    for (const i of parsed.error.issues) if (i.path[0]) errs[String(i.path[0])] = i.message
    return { ok: false, errors: errs, message: 'Veuillez corriger les erreurs du formulaire.' }
  }

  const { data: inserted, error } = await supabase
    .from('posts')
    .insert({ ...parsed.data, user_id: profile.id })
    .select('id')
    .single()
  if (error || !inserted) return { ok: false, message: error?.message ?? 'Création impossible.' }

  const files = formData.getAll('attachments').filter((f): f is File => f instanceof File && f.size > 0)
  for (const file of files) {
    try {
      validateUpload(file, 'attachment')
      const { path } = await uploadFile(supabase, BUCKETS.forum, file, `post-${inserted.id}`, 'attachment')
      await supabase.from('post_attachments').insert({
        post_id: inserted.id,
        user_id: profile.id,
        file_path: path,
        original_name: file.name,
        mime_type: file.type,
        file_size: file.size,
      })
    } catch {
      // on ignore les pièces jointes qui échouent (comportement proche du PHP)
    }
  }

  revalidatePath('/forum')
  await setFlash('success', 'Discussion créée avec succès.')
  return { ok: true, data: { id: inserted.id } }
}

/** Mise à jour d'une discussion existante (propriétaire uniquement). */
export async function updatePostAction(postId: number, formData: FormData): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: existing } = await supabase.from('posts').select('user_id').eq('id', postId).maybeSingle()
  if (!existing) return { ok: false, message: 'Discussion introuvable.' }
  if (existing.user_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const raw = {
    category: (formData.get('category')?.toString() ?? '').trim(),
    title: (formData.get('title')?.toString() ?? '').trim(),
    body: (formData.get('body')?.toString() ?? '').trim(),
  }
  const parsed = forumPostSchema.safeParse(raw)
  if (!parsed.success) {
    const errs: Record<string, string> = {}
    for (const i of parsed.error.issues) if (i.path[0]) errs[String(i.path[0])] = i.message
    return { ok: false, errors: errs }
  }

  const { error } = await supabase.from('posts').update(parsed.data).eq('id', postId)
  if (error) return { ok: false, message: error.message }

  const files = formData.getAll('attachments').filter((f): f is File => f instanceof File && f.size > 0)
  for (const file of files) {
    try {
      validateUpload(file, 'attachment')
      const { path } = await uploadFile(supabase, BUCKETS.forum, file, `post-${postId}`, 'attachment')
      await supabase.from('post_attachments').insert({
        post_id: postId,
        user_id: profile.id,
        file_path: path,
        original_name: file.name,
        mime_type: file.type,
        file_size: file.size,
      })
    } catch {}
  }

  revalidatePath(`/forum/${postId}`)
  revalidatePath('/forum')
  await setFlash('success', 'Discussion modifiée.')
  return { ok: true }
}

/** Soft-delete : status = 'inactive' (schema Next.js n'autorise pas 'deleted'). */
export async function deletePostAction(postId: number): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: post } = await supabase.from('posts').select('user_id').eq('id', postId).maybeSingle()
  if (!post) return { ok: false, message: 'Discussion introuvable.' }
  if (post.user_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const { error } = await supabase.from('posts').update({ status: 'inactive' }).eq('id', postId)
  if (error) return { ok: false, message: error.message }

  revalidatePath('/forum')
  await setFlash('success', 'Discussion supprimée.')
  redirect('/forum')
}

/** Supprime une pièce jointe (propriétaire de la discussion ou admin). */
export async function deleteAttachmentAction(attachmentId: number): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: att } = await supabase
    .from('post_attachments')
    .select('id, post_id, file_path, user_id')
    .eq('id', attachmentId)
    .maybeSingle()
  if (!att) return { ok: false, message: 'Pièce jointe introuvable.' }
  if (att.user_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  await deleteFile(supabase, BUCKETS.forum, att.file_path).catch(() => null)
  const { error } = await supabase.from('post_attachments').delete().eq('id', attachmentId)
  if (error) return { ok: false, message: error.message }

  revalidatePath(`/forum/${att.post_id}`)
  return { ok: true }
}

// ------------------------------------------------------------
// Commentaires (polymorphe : post / tutorial / blog / project)
// ------------------------------------------------------------
const commentSchema = z.object({
  type: z.enum(['post', 'tutorial', 'blog', 'project']),
  id: z.coerce.number().int().positive(),
  body: z.string().min(5, 'Le commentaire doit contenir au moins 5 caractères').max(2000),
})

export async function addCommentAction(formData: FormData): Promise<ActionResult<{ id: number }>> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const parsed = commentSchema.safeParse({
    type: formData.get('type'),
    id: formData.get('id'),
    body: (formData.get('body')?.toString() ?? '').trim(),
  })
  if (!parsed.success) {
    return { ok: false, message: parsed.error.issues[0]?.message ?? 'Commentaire invalide.' }
  }

  const { data, error } = await supabase
    .from('comments')
    .insert({
      user_id: profile.id,
      commentable_type: parsed.data.type,
      commentable_id: parsed.data.id,
      body: parsed.data.body,
    })
    .select('id')
    .single()
  if (error || !data) return { ok: false, message: error?.message ?? 'Erreur.' }

  // (pas d'incrément de vues ici — les vues sont gérées côté page détail)

  const path = pathForType(parsed.data.type, parsed.data.id)
  if (path) revalidatePath(path)
  return { ok: true, data: { id: data.id } }
}

export async function deleteCommentAction(commentId: number): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data } = await supabase
    .from('comments')
    .select('id, user_id, commentable_type, commentable_id')
    .eq('id', commentId)
    .maybeSingle()
  if (!data) return { ok: false, message: 'Commentaire introuvable.' }
  if (data.user_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const { error } = await supabase.from('comments').update({ status: 'deleted' }).eq('id', commentId)
  if (error) return { ok: false, message: error.message }

  const path = pathForType(
    data.commentable_type as 'post' | 'tutorial' | 'blog' | 'project',
    data.commentable_id as number
  )
  if (path) revalidatePath(path)
  return { ok: true }
}

// ------------------------------------------------------------
// Likes polymorphes — utilisables pour le forum, blog, tuto, project
// ------------------------------------------------------------
const likeSchema = z.object({
  type: z.enum(['post', 'comment', 'tutorial', 'blog', 'project']),
  id: z.coerce.number().int().positive(),
})

export async function toggleLikeAction(
  type: 'post' | 'comment' | 'tutorial' | 'blog' | 'project',
  id: number
): Promise<ActionResult<{ liked: boolean; count: number }>> {
  const profile = await requireLogin()
  const parsed = likeSchema.safeParse({ type, id })
  if (!parsed.success) return { ok: false, message: 'Données invalides.' }

  const supabase = await createClient()

  const { data: existing } = await supabase
    .from('likes')
    .select('id')
    .eq('user_id', profile.id)
    .eq('likeable_type', type)
    .eq('likeable_id', id)
    .maybeSingle()

  if (existing) {
    await supabase.from('likes').delete().eq('id', existing.id)
  } else {
    await supabase.from('likes').insert({ user_id: profile.id, likeable_type: type, likeable_id: id })
  }

  // Maj du compteur dénormalisé si applicable
  const { count } = await supabase
    .from('likes')
    .select('*', { count: 'exact', head: true })
    .eq('likeable_type', type)
    .eq('likeable_id', id)

  if (type === 'blog') {
    await supabase.from('blog_posts').update({ likes_count: count ?? 0 }).eq('id', id)
  } else if (type === 'tutorial') {
    await supabase.from('tutorials').update({ likes_count: count ?? 0 }).eq('id', id)
  }

  const path = pathForType(type === 'comment' ? 'post' : type, id)
  if (path) revalidatePath(path)

  return { ok: true, data: { liked: !existing, count: count ?? 0 } }
}

// ------------------------------------------------------------
// Reports (signalements)
// ------------------------------------------------------------
const reportSchema = z.object({
  type: z.enum(['post', 'comment', 'blog', 'tutorial', 'project', 'user']),
  id: z.string().min(1),
  reason: z.string().min(5, 'Motif trop court').max(500),
  details: z.string().max(2000).optional(),
})

export async function reportAction(formData: FormData): Promise<ActionResult> {
  const profile = await requireLogin()
  const parsed = reportSchema.safeParse({
    type: formData.get('type'),
    id: formData.get('id'),
    reason: formData.get('reason'),
    details: formData.get('details') ?? '',
  })
  if (!parsed.success) return { ok: false, message: parsed.error.issues[0]?.message ?? 'Données invalides.' }

  const supabase = await createClient()
  const isUuid = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(parsed.data.id)

  const { error } = await supabase.from('reports').insert({
    reporter_id: profile.id,
    reportable_type: parsed.data.type,
    reportable_id: isUuid ? null : Number(parsed.data.id),
    reportable_uuid: isUuid ? parsed.data.id : null,
    reason: parsed.data.reason,
    details: parsed.data.details || null,
  })
  if (error) return { ok: false, message: error.message }

  await setFlash('success', 'Signalement envoyé. Merci !')
  return { ok: true }
}

/** Incrémente les vues d'un post (appelé depuis la page détail). */
export async function incrementPostViews(postId: number) {
  try {
    const supabase = await createClient()
    await supabase.rpc('increment_post_views', { p_id: postId })
  } catch {
    // Fire and forget — on ignore les erreurs (RLS ou autres).
  }
}

function pathForType(type: 'post' | 'tutorial' | 'blog' | 'project', id: number) {
  switch (type) {
    case 'post':
      return `/forum/${id}`
    case 'tutorial':
      return `/formations/${id}`
    case 'blog':
      return `/blog/${id}` // revalide le slug paradoxalement via tag — approximation acceptable
    case 'project':
      return `/project/${id}`
  }
  return null
}
