'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { z } from 'zod'
import { createClient } from '@/lib/supabase/server'
import { requireLogin, requirePermission } from '@/lib/auth'
import { setFlash } from '@/lib/flash'
import { BUCKETS, uploadFile, deleteFile, validateUpload } from '@/lib/uploads'
import type { ActionResult } from '@/app/actions/users'
import { FORMATIONS_PATH } from '@/lib/routes'

function revalidateFormationRoutes(tutorialId?: number) {
  revalidatePath('/tutorial')
  revalidatePath(FORMATIONS_PATH)
  if (tutorialId != null) {
    revalidatePath(`/tutorial/${tutorialId}`)
    revalidatePath(`${FORMATIONS_PATH}/${tutorialId}`)
  }
}

/**
 * Server actions pour les tutoriels (catalogue « Formations »).
 * - Création/édition : utilisateurs avec `can_create_tutorial = true` (ou admin).
 * - Upload de vidéos + chapitres associés.
 */

const tutorialSchema = z.object({
  title: z.string().min(5, 'Titre trop court (5 car. min)').max(200),
  description: z.string().min(20, 'Description trop courte').max(1000),
  content: z.string().optional(),
  category: z.string().min(1, 'Catégorie requise'),
  type: z.enum(['video', 'text', 'mixed']),
  level: z.enum(['beginner', 'intermediate', 'advanced']),
})

function parseTutorial(fd: FormData) {
  return tutorialSchema.safeParse({
    title: fd.get('title')?.toString() ?? '',
    description: fd.get('description')?.toString() ?? '',
    content: fd.get('content')?.toString() ?? '',
    category: fd.get('category')?.toString() ?? '',
    type: fd.get('type')?.toString() ?? 'video',
    level: fd.get('level')?.toString() ?? 'beginner',
  })
}

function issuesToErrors(err: z.ZodError): Record<string, string> {
  const errs: Record<string, string> = {}
  for (const i of err.issues) if (i.path[0]) errs[String(i.path[0])] = i.message
  return errs
}

export async function createTutorialAction(formData: FormData): Promise<ActionResult<{ id: number }>> {
  const profile = await requirePermission('can_create_tutorial')
  const supabase = await createClient()

  const parsed = parseTutorial(formData)
  if (!parsed.success) return { ok: false, errors: issuesToErrors(parsed.error), message: 'Formulaire invalide.' }

  // Thumbnail optionnel
  let thumbnailPath: string | null = null
  const thumb = formData.get('thumbnail') as File | null
  if (thumb && thumb.size > 0) {
    try {
      validateUpload(thumb, 'image')
      const { path } = await uploadFile(supabase, BUCKETS.tutorials, thumb, 'tutorials', 'image')
      thumbnailPath = path
    } catch (e) {
      return { ok: false, message: (e as Error).message }
    }
  }

  const { data: inserted, error } = await supabase
    .from('tutorials')
    .insert({
      user_id: profile.id,
      title: parsed.data.title,
      description: parsed.data.description,
      content: parsed.data.content ?? null,
      thumbnail: thumbnailPath,
      category: parsed.data.category,
      type: parsed.data.type,
      level: parsed.data.level,
    })
    .select('id')
    .single()

  if (error || !inserted) {
    if (thumbnailPath) await deleteFile(supabase, BUCKETS.tutorials, thumbnailPath).catch(() => null)
    return { ok: false, message: error?.message ?? 'Création impossible.' }
  }

  // Tags (CSV)
  const tagsCsv = (formData.get('tags')?.toString() ?? '').trim()
  if (tagsCsv) {
    await ensureTagsAndAttach(inserted.id, tagsCsv)
  }

  revalidateFormationRoutes(inserted.id)
  await setFlash('success', 'Formation publiée.')
  return { ok: true, data: { id: inserted.id } }
}

export async function updateTutorialAction(id: number, formData: FormData): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: current } = await supabase
    .from('tutorials')
    .select('user_id, thumbnail')
    .eq('id', id)
    .maybeSingle()
  if (!current) return { ok: false, message: 'Formation introuvable.' }
  if (current.user_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const parsed = parseTutorial(formData)
  if (!parsed.success) return { ok: false, errors: issuesToErrors(parsed.error), message: 'Formulaire invalide.' }

  let thumbnailPath: string | null = current.thumbnail
  const thumb = formData.get('thumbnail') as File | null
  if (thumb && thumb.size > 0) {
    try {
      validateUpload(thumb, 'image')
      const { path } = await uploadFile(supabase, BUCKETS.tutorials, thumb, 'tutorials', 'image')
      if (current.thumbnail) await deleteFile(supabase, BUCKETS.tutorials, current.thumbnail).catch(() => null)
      thumbnailPath = path
    } catch (e) {
      return { ok: false, message: (e as Error).message }
    }
  }

  const { error } = await supabase
    .from('tutorials')
    .update({
      title: parsed.data.title,
      description: parsed.data.description,
      content: parsed.data.content ?? null,
      thumbnail: thumbnailPath,
      category: parsed.data.category,
      type: parsed.data.type,
      level: parsed.data.level,
    })
    .eq('id', id)

  if (error) return { ok: false, message: error.message }

  // Rebuild tags
  const tagsCsv = (formData.get('tags')?.toString() ?? '').trim()
  await supabase.from('tutorial_tags').delete().eq('tutorial_id', id)
  if (tagsCsv) await ensureTagsAndAttach(id, tagsCsv)

  revalidateFormationRoutes(id)
  await setFlash('success', 'Formation mise à jour.')
  return { ok: true }
}

export async function deleteTutorialAction(id: number): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: tuto } = await supabase.from('tutorials').select('user_id, thumbnail').eq('id', id).maybeSingle()
  if (!tuto) return { ok: false, message: 'Formation introuvable.' }
  if (tuto.user_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  // Soft delete : les fichiers associés restent (suppression dure possible en admin)
  const { error } = await supabase.from('tutorials').update({ status: 'inactive' }).eq('id', id)
  if (error) return { ok: false, message: error.message }

  revalidateFormationRoutes()
  await setFlash('success', 'Formation retirée du catalogue.')
  redirect(FORMATIONS_PATH)
}

// ------------------------------------------------------------
// Vidéos
// ------------------------------------------------------------
export async function addTutorialVideoAction(
  tutorialId: number,
  formData: FormData
): Promise<ActionResult<{ id: number; path: string }>> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: tuto } = await supabase.from('tutorials').select('user_id').eq('id', tutorialId).maybeSingle()
  if (!tuto) return { ok: false, message: 'Formation introuvable.' }
  if (tuto.user_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const file = formData.get('video') as File | null
  if (!file || file.size === 0) return { ok: false, message: 'Vidéo requise.' }

  try {
    validateUpload(file, 'video')
  } catch (e) {
    return { ok: false, message: (e as Error).message }
  }

  let path: string
  try {
    const up = await uploadFile(supabase, BUCKETS.tutorials, file, `tutorials/${tutorialId}`, 'video')
    path = up.path
  } catch (e) {
    return { ok: false, message: (e as Error).message }
  }

  const { data: { count } } = { data: { count: 0 } }
  const { data: maxOrder } = await supabase
    .from('tutorial_videos')
    .select('order_index')
    .eq('tutorial_id', tutorialId)
    .order('order_index', { ascending: false })
    .limit(1)
    .maybeSingle()

  const { data: video, error } = await supabase
    .from('tutorial_videos')
    .insert({
      tutorial_id: tutorialId,
      title: formData.get('title')?.toString() || file.name,
      description: formData.get('description')?.toString() || null,
      file_path: path,
      file_name: file.name,
      file_size: file.size,
      order_index: (maxOrder?.order_index ?? 0) + 1,
    })
    .select('id')
    .single()

  if (error || !video) {
    await deleteFile(supabase, BUCKETS.tutorials, path).catch(() => null)
    return { ok: false, message: error?.message ?? 'Enregistrement impossible.' }
  }

  revalidateFormationRoutes(tutorialId)
  return { ok: true, data: { id: video.id, path } }
}

export async function deleteTutorialVideoAction(videoId: number): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: video } = await supabase
    .from('tutorial_videos')
    .select('tutorial_id, file_path, tutorials(user_id)')
    .eq('id', videoId)
    .maybeSingle()
  if (!video) return { ok: false, message: 'Vidéo introuvable.' }
  // Supabase retourne parfois la relation en tableau (to-many). On supporte les deux formes.
  const rawTutorials = (video as unknown as { tutorials: unknown }).tutorials
  const ownerId = Array.isArray(rawTutorials)
    ? (rawTutorials[0] as { user_id: string } | undefined)?.user_id
    : (rawTutorials as { user_id: string } | null)?.user_id
  if (ownerId !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  if (video.file_path) await deleteFile(supabase, BUCKETS.tutorials, video.file_path).catch(() => null)
  const { error } = await supabase.from('tutorial_videos').delete().eq('id', videoId)
  if (error) return { ok: false, message: error.message }

  revalidateFormationRoutes(video.tutorial_id)
  return { ok: true }
}

// ------------------------------------------------------------
// Chapitres (ordre + rattachement à une vidéo)
// ------------------------------------------------------------
export async function saveChaptersAction(
  tutorialId: number,
  chapters: Array<{ id?: number; chapter_number?: number; title: string; description?: string; video_id?: number | null }>
): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: tuto } = await supabase.from('tutorials').select('user_id').eq('id', tutorialId).maybeSingle()
  if (!tuto) return { ok: false, message: 'Formation introuvable.' }
  if (tuto.user_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  await supabase.from('tutorial_chapters').delete().eq('tutorial_id', tutorialId)
  if (chapters.length) {
    const rows = chapters.map((c, i) => ({
      tutorial_id: tutorialId,
      chapter_number: c.chapter_number ?? i + 1,
      title: c.title,
      description: c.description ?? null,
      video_id: c.video_id ?? null,
      order_index: i,
    }))
    const { error } = await supabase.from('tutorial_chapters').insert(rows)
    if (error) return { ok: false, message: error.message }
  }

  revalidateFormationRoutes(tutorialId)
  return { ok: true }
}

async function ensureTagsAndAttach(tutorialId: number, csv: string) {
  const supabase = await createClient()
  const names = Array.from(
    new Set(
      csv
        .split(',')
        .map(t => t.trim())
        .filter(Boolean)
        .slice(0, 10)
    )
  )
  if (!names.length) return

  // Insert manquants
  for (const name of names) {
    await supabase.from('tags').upsert({ name }, { onConflict: 'name', ignoreDuplicates: true })
  }
  const { data: tags } = await supabase.from('tags').select('id, name').in('name', names)
  const rows = (tags ?? []).map(t => ({ tutorial_id: tutorialId, tag_id: (t as { id: number }).id }))
  if (rows.length) {
    await supabase.from('tutorial_tags').upsert(rows, { onConflict: 'tutorial_id,tag_id', ignoreDuplicates: true })
  }
}
