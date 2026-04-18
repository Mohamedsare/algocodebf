'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { z } from 'zod'
import { createClient } from '@/lib/supabase/server'
import { requireAdmin } from '@/lib/auth'
import { setFlash } from '@/lib/flash'
import { BUCKETS, uploadFile, deleteFile, validateUpload } from '@/lib/uploads'
import { generateSlug } from '@/lib/utils'
import type { ActionResult } from '@/app/actions/users'

/**
 * Server actions pour les articles de blog.
 * Réservé aux administrateurs (RLS + garde-fou côté action).
 */

const blogSchema = z.object({
  title: z.string().min(5, 'Titre trop court (5 car. min)').max(200),
  excerpt: z.string().min(20, 'Extrait trop court (20 car. min)').max(500),
  content: z.string().min(100, 'Contenu trop court (100 car. min)'),
  category: z.string().min(1, 'Catégorie requise'),
  status: z.enum(['draft', 'published', 'archived']),
})

async function uniqueSlug(base: string, excludeId?: number): Promise<string> {
  const supabase = await createClient()
  let slug = generateSlug(base)
  let i = 1
  for (;;) {
    const q = supabase.from('blog_posts').select('id').eq('slug', slug).limit(1)
    const { data } = await q
    const conflict = (data ?? []).find(r => r.id !== excludeId)
    if (!conflict) return slug
    slug = `${generateSlug(base)}-${++i}`
  }
}

function parse(fd: FormData) {
  return blogSchema.safeParse({
    title: fd.get('title')?.toString() ?? '',
    excerpt: fd.get('excerpt')?.toString() ?? '',
    content: fd.get('content')?.toString() ?? '',
    category: fd.get('category')?.toString() ?? '',
    status: fd.get('status')?.toString() ?? 'draft',
  })
}

function issuesToErrors(err: z.ZodError): Record<string, string> {
  const errs: Record<string, string> = {}
  for (const i of err.issues) if (i.path[0]) errs[String(i.path[0])] = i.message
  return errs
}

export async function createBlogPostAction(formData: FormData): Promise<ActionResult<{ id: number; slug: string }>> {
  const profile = await requireAdmin()
  const supabase = await createClient()
  const parsed = parse(formData)
  if (!parsed.success) return { ok: false, errors: issuesToErrors(parsed.error), message: 'Formulaire invalide.' }

  const slug = await uniqueSlug(parsed.data.title)
  let featuredPath: string | null = null
  const file = formData.get('featured_image') as File | null
  if (file && file.size > 0) {
    try {
      validateUpload(file, 'image')
      const { path } = await uploadFile(supabase, BUCKETS.blog, file, 'blog', 'image')
      featuredPath = path
    } catch (e) {
      return { ok: false, message: (e as Error).message }
    }
  }

  const { data: inserted, error } = await supabase
    .from('blog_posts')
    .insert({
      author_id: profile.id,
      title: parsed.data.title,
      slug,
      excerpt: parsed.data.excerpt,
      content: parsed.data.content,
      featured_image: featuredPath,
      category: parsed.data.category,
      status: parsed.data.status,
      published_at: parsed.data.status === 'published' ? new Date().toISOString() : null,
    })
    .select('id, slug')
    .single()

  if (error || !inserted) {
    if (featuredPath) await deleteFile(supabase, BUCKETS.blog, featuredPath).catch(() => null)
    return { ok: false, message: error?.message ?? 'Création impossible.' }
  }

  revalidatePath('/blog')
  await setFlash('success', 'Article publié.')
  return { ok: true, data: { id: inserted.id, slug: inserted.slug } }
}

export async function updateBlogPostAction(id: number, formData: FormData): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const parsed = parse(formData)
  if (!parsed.success) return { ok: false, errors: issuesToErrors(parsed.error), message: 'Formulaire invalide.' }

  const { data: current } = await supabase
    .from('blog_posts')
    .select('slug, status, featured_image, published_at')
    .eq('id', id)
    .maybeSingle()
  if (!current) return { ok: false, message: 'Article introuvable.' }

  const slug = (formData.get('slug')?.toString() || parsed.data.title) !== current.slug
    ? await uniqueSlug(formData.get('slug')?.toString() || parsed.data.title, id)
    : current.slug

  let featuredPath: string | null = current.featured_image
  const file = formData.get('featured_image') as File | null
  if (file && file.size > 0) {
    try {
      validateUpload(file, 'image')
      const { path } = await uploadFile(supabase, BUCKETS.blog, file, 'blog', 'image')
      if (current.featured_image) await deleteFile(supabase, BUCKETS.blog, current.featured_image).catch(() => null)
      featuredPath = path
    } catch (e) {
      return { ok: false, message: (e as Error).message }
    }
  }

  const publishedAt =
    parsed.data.status === 'published' && !current.published_at
      ? new Date().toISOString()
      : current.published_at

  const { error } = await supabase
    .from('blog_posts')
    .update({
      title: parsed.data.title,
      slug,
      excerpt: parsed.data.excerpt,
      content: parsed.data.content,
      featured_image: featuredPath,
      category: parsed.data.category,
      status: parsed.data.status,
      published_at: publishedAt,
    })
    .eq('id', id)

  if (error) return { ok: false, message: error.message }

  revalidatePath('/blog')
  revalidatePath(`/blog/${slug}`)
  await setFlash('success', 'Article mis à jour.')
  return { ok: true }
}

export async function deleteBlogPostAction(id: number): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { data } = await supabase.from('blog_posts').select('featured_image').eq('id', id).maybeSingle()
  if (data?.featured_image) await deleteFile(supabase, BUCKETS.blog, data.featured_image).catch(() => null)

  const { error } = await supabase.from('blog_posts').delete().eq('id', id)
  if (error) return { ok: false, message: error.message }

  revalidatePath('/blog')
  await setFlash('success', 'Article supprimé.')
  redirect('/blog')
}
