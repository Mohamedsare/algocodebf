'use server'

import { revalidatePath } from 'next/cache'
import { createClient } from '@/lib/supabase/server'
import { ADMIN_CONSOLE_PATH, FORMATIONS_PATH } from '@/lib/routes'
import { requireAdmin } from '@/lib/auth'
import { setFlash } from '@/lib/flash'
import type { ActionResult } from '@/app/actions/users'
import type { UserRole, UserStatus, ReportStatus } from '@/types'
import { slugifyTitle } from '@/lib/slugify'

const SYSTEM_SETTING_KEYS = [
  'site_name',
  'site_description',
  'maintenance_mode',
  'allow_registration',
  'default_user_role',
] as const

export async function setUserRoleAction(userId: string, role: UserRole): Promise<ActionResult> {
  const admin = await requireAdmin()
  if (userId === admin.id) return { ok: false, message: 'Vous ne pouvez pas modifier votre propre rôle.' }
  const supabase = await createClient()
  const { error } = await supabase.from('profiles').update({ role }).eq('id', userId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/users`)
  await setFlash('success', 'Rôle mis à jour.')
  return { ok: true }
}

export async function setUserStatusAction(userId: string, status: UserStatus): Promise<ActionResult> {
  const admin = await requireAdmin()
  if (userId === admin.id) return { ok: false, message: 'Vous ne pouvez pas modifier votre propre statut.' }
  const supabase = await createClient()
  const { error } = await supabase.from('profiles').update({ status }).eq('id', userId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/users`)
  await setFlash('success', 'Statut mis à jour.')
  return { ok: true }
}

export async function setUserPermissionAction(
  userId: string,
  permission: 'can_create_tutorial' | 'can_create_project',
  value: boolean
): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase.from('profiles').update({ [permission]: value }).eq('id', userId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/users`)
  revalidatePath(`${ADMIN_CONSOLE_PATH}/permissions`)
  return { ok: true }
}

export async function togglePinPostAction(postId: number): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { data } = await supabase.from('posts').select('is_pinned').eq('id', postId).maybeSingle()
  if (!data) return { ok: false, message: 'Post introuvable.' }
  const { error } = await supabase.from('posts').update({ is_pinned: !data.is_pinned }).eq('id', postId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/forum`)
  revalidatePath('/forum')
  return { ok: true }
}

export async function hidePostAction(postId: number): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase.from('posts').update({ status: 'inactive' }).eq('id', postId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/forum`)
  revalidatePath('/forum')
  return { ok: true }
}

export async function restorePostAction(postId: number): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase.from('posts').update({ status: 'active' }).eq('id', postId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/forum`)
  return { ok: true }
}

export async function setTutorialStatusAction(
  tutorialId: number,
  status: 'active' | 'inactive'
): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase.from('tutorials').update({ status }).eq('id', tutorialId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/tutorials`)
  revalidatePath(ADMIN_CONSOLE_PATH)
  revalidatePath('/tutorial')
  revalidatePath(FORMATIONS_PATH)
  revalidatePath(`/tutorial/${tutorialId}`)
  revalidatePath(`${FORMATIONS_PATH}/${tutorialId}`)
  await setFlash('success', status === 'active' ? 'Formation republiée.' : 'Formation masquée.')
  return { ok: true }
}

export async function setJobStatusAction(
  jobId: number,
  status: 'active' | 'closed' | 'expired'
): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase.from('jobs').update({ status }).eq('id', jobId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/jobs`)
  revalidatePath(ADMIN_CONSOLE_PATH)
  revalidatePath('/job')
  revalidatePath(`/job/${jobId}`)
  const msg =
    status === 'active'
      ? 'Offre republiée sur le catalogue.'
      : status === 'closed'
        ? 'Offre fermée.'
        : 'Offre marquée comme expirée.'
  await setFlash('success', msg)
  return { ok: true }
}

export async function setProjectAdminStatusAction(
  projectId: number,
  status: 'active' | 'completed' | 'archived'
): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase.from('projects').update({ status }).eq('id', projectId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/projects`)
  revalidatePath(ADMIN_CONSOLE_PATH)
  revalidatePath('/project')
  revalidatePath(`/project/${projectId}`)
  await setFlash(
    'success',
    status === 'archived' ? 'Projet archivé.' : status === 'active' ? 'Projet réactivé.' : 'Statut du projet mis à jour.'
  )
  return { ok: true }
}

export async function setBlogPostStatusAction(
  postId: number,
  status: 'draft' | 'published' | 'archived'
): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { data: current } = await supabase
    .from('blog_posts')
    .select('slug, published_at')
    .eq('id', postId)
    .maybeSingle()
  if (!current?.slug) return { ok: false, message: 'Article introuvable.' }
  const slug = current.slug as string
  const payload: { status: typeof status; published_at?: string } = { status }
  if (status === 'published' && !current.published_at) {
    payload.published_at = new Date().toISOString()
  }
  const { error } = await supabase.from('blog_posts').update(payload).eq('id', postId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/blog`)
  revalidatePath(ADMIN_CONSOLE_PATH)
  revalidatePath('/blog')
  revalidatePath(`/blog/${slug}`)
  const msg =
    status === 'published'
      ? 'Article publié.'
      : status === 'archived'
        ? 'Article archivé.'
        : 'Article repassé en brouillon.'
  await setFlash('success', msg)
  return { ok: true }
}

export async function resolveReportAction(reportId: number, decision: ReportStatus): Promise<ActionResult> {
  const admin = await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase
    .from('reports')
    .update({ status: decision, reviewed_by: admin.id, reviewed_at: new Date().toISOString() })
    .eq('id', reportId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/reports`)
  return { ok: true }
}

export async function setCommentStatusAction(
  commentId: number,
  status: 'active' | 'deleted'
): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase.from('comments').update({ status }).eq('id', commentId)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/comments`)
  await setFlash('success', status === 'deleted' ? 'Commentaire masqué.' : 'Commentaire rétabli.')
  return { ok: true }
}

/** Comme `AdminController::handleReport` action « hide » : masque le contenu puis résout le signalement. */
export async function hideReportedContentAndResolveAction(reportId: number): Promise<ActionResult> {
  const admin = await requireAdmin()
  const supabase = await createClient()
  const { data: report } = await supabase
    .from('reports')
    .select('id, status, reportable_type, reportable_id')
    .eq('id', reportId)
    .maybeSingle()

  if (!report || report.status !== 'pending') {
    return { ok: false, message: 'Signalement introuvable ou déjà traité.' }
  }

  const rid = report.reportable_id as number | null
  if (rid != null) {
    switch (report.reportable_type) {
      case 'post':
        await supabase.from('posts').update({ status: 'inactive' }).eq('id', rid)
        break
      case 'comment':
        await supabase.from('comments').update({ status: 'deleted' }).eq('id', rid)
        break
      case 'tutorial':
        await supabase.from('tutorials').update({ status: 'inactive' }).eq('id', rid)
        break
      case 'blog':
        await supabase.from('blog_posts').update({ status: 'archived' }).eq('id', rid)
        break
      case 'project':
        await supabase.from('projects').update({ status: 'archived' }).eq('id', rid)
        break
      default:
        break
    }
  }

  const { error } = await supabase
    .from('reports')
    .update({
      status: 'resolved',
      reviewed_by: admin.id,
      reviewed_at: new Date().toISOString(),
    })
    .eq('id', reportId)

  if (error) return { ok: false, message: error.message }

  revalidatePath(`${ADMIN_CONSOLE_PATH}/reports`)
  revalidatePath(`${ADMIN_CONSOLE_PATH}/forum`)
  revalidatePath(`${ADMIN_CONSOLE_PATH}/blog`)
  revalidatePath(`${ADMIN_CONSOLE_PATH}/tutorials`)
  revalidatePath(`${ADMIN_CONSOLE_PATH}/projects`)
  revalidatePath(`${ADMIN_CONSOLE_PATH}/comments`)
  revalidatePath('/forum')
  revalidatePath('/blog')
  revalidatePath('/project')
  await setFlash('success', 'Contenu masqué et signalement résolu.')
  return { ok: true }
}

async function uniqueForumSlug(supabase: Awaited<ReturnType<typeof createClient>>, base: string): Promise<string> {
  let slug = base
  let n = 0
  for (;;) {
    const { data } = await supabase.from('forum_categories').select('id').eq('slug', slug).maybeSingle()
    if (!data) return slug
    n += 1
    slug = `${base}-${n}`
  }
}

export async function createForumCategoryAction(name: string, description: string): Promise<ActionResult> {
  await requireAdmin()
  const trimmed = name.trim()
  if (!trimmed) return { ok: false, message: 'Le nom est requis.' }
  const supabase = await createClient()
  const base = slugifyTitle(trimmed)
  const slug = await uniqueForumSlug(supabase, base)
  const { error } = await supabase.from('forum_categories').insert({
    name: trimmed,
    slug,
    description: description.trim() || null,
  })
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/forum`)
  revalidatePath('/forum')
  await setFlash('success', 'Catégorie forum créée.')
  return { ok: true }
}

export async function updateForumCategoryAction(
  id: number,
  name: string,
  description: string
): Promise<ActionResult> {
  await requireAdmin()
  const trimmed = name.trim()
  if (!trimmed) return { ok: false, message: 'Le nom est requis.' }
  const supabase = await createClient()
  const { data: row } = await supabase.from('forum_categories').select('slug').eq('id', id).maybeSingle()
  if (!row?.slug) return { ok: false, message: 'Catégorie introuvable.' }
  const oldSlug = row.slug as string
  const base = slugifyTitle(trimmed)
  let slug = base
  if (base !== oldSlug) {
    slug = await uniqueForumSlug(supabase, base)
    await supabase.from('posts').update({ category: slug }).eq('category', oldSlug)
  }
  const { error } = await supabase
    .from('forum_categories')
    .update({ name: trimmed, slug, description: description.trim() || null })
    .eq('id', id)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/forum`)
  revalidatePath('/forum')
  await setFlash('success', 'Catégorie forum mise à jour.')
  return { ok: true }
}

export async function deleteForumCategoryAction(id: number): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { data: row } = await supabase.from('forum_categories').select('slug').eq('id', id).maybeSingle()
  if (!row?.slug) return { ok: false, message: 'Catégorie introuvable.' }
  const slug = row.slug as string
  const { count } = await supabase.from('posts').select('*', { count: 'exact', head: true }).eq('category', slug)
  if ((count ?? 0) > 0) {
    return { ok: false, message: 'Impossible de supprimer : des discussions utilisent cette catégorie.' }
  }
  const { error } = await supabase.from('forum_categories').delete().eq('id', id)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/forum`)
  revalidatePath('/forum')
  await setFlash('success', 'Catégorie forum supprimée.')
  return { ok: true }
}

async function uniqueBlogSlug(supabase: Awaited<ReturnType<typeof createClient>>, base: string): Promise<string> {
  let slug = base
  let n = 0
  for (;;) {
    const { data } = await supabase.from('blog_categories').select('id').eq('slug', slug).maybeSingle()
    if (!data) return slug
    n += 1
    slug = `${base}-${n}`
  }
}

export async function createBlogCategoryAction(name: string, description: string): Promise<ActionResult> {
  await requireAdmin()
  const trimmed = name.trim()
  if (!trimmed) return { ok: false, message: 'Le nom est requis.' }
  const supabase = await createClient()
  const base = slugifyTitle(trimmed)
  const slug = await uniqueBlogSlug(supabase, base)
  const { error } = await supabase.from('blog_categories').insert({
    name: trimmed,
    slug,
    description: description.trim() || null,
  })
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/blog`)
  revalidatePath('/blog')
  await setFlash('success', 'Catégorie blog créée.')
  return { ok: true }
}

export async function updateBlogCategoryAction(
  id: number,
  name: string,
  description: string
): Promise<ActionResult> {
  await requireAdmin()
  const trimmed = name.trim()
  if (!trimmed) return { ok: false, message: 'Le nom est requis.' }
  const supabase = await createClient()
  const { data: row } = await supabase.from('blog_categories').select('name, slug').eq('id', id).maybeSingle()
  if (!row?.slug) return { ok: false, message: 'Catégorie introuvable.' }
  const oldName = row.name as string
  const oldSlug = row.slug as string
  const newSlug = slugifyTitle(trimmed)
  let slug: string
  if (newSlug !== oldSlug) {
    const { data: taken } = await supabase
      .from('blog_categories')
      .select('id')
      .eq('slug', newSlug)
      .neq('id', id)
      .maybeSingle()
    slug = taken ? await uniqueBlogSlug(supabase, newSlug) : newSlug
  } else {
    slug = oldSlug
  }
  if (oldName !== trimmed) {
    await supabase.from('blog_posts').update({ category: trimmed }).eq('category', oldName)
  }
  const { error } = await supabase
    .from('blog_categories')
    .update({ name: trimmed, slug, description: description.trim() || null })
    .eq('id', id)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/blog`)
  revalidatePath('/blog')
  await setFlash('success', 'Catégorie blog mise à jour.')
  return { ok: true }
}

export async function deleteBlogCategoryAction(id: number): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { data: row } = await supabase.from('blog_categories').select('name').eq('id', id).maybeSingle()
  if (!row?.name) return { ok: false, message: 'Catégorie introuvable.' }
  const catName = row.name as string
  const { count } = await supabase.from('blog_posts').select('*', { count: 'exact', head: true }).eq('category', catName)
  if ((count ?? 0) > 0) {
    return { ok: false, message: 'Impossible de supprimer : des articles utilisent cette catégorie.' }
  }
  const { error } = await supabase.from('blog_categories').delete().eq('id', id)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/blog`)
  revalidatePath('/blog')
  await setFlash('success', 'Catégorie blog supprimée.')
  return { ok: true }
}

export async function updateSystemSettingAction(key: string, value: string): Promise<ActionResult> {
  await requireAdmin()
  if (!SYSTEM_SETTING_KEYS.includes(key as (typeof SYSTEM_SETTING_KEYS)[number])) {
    return { ok: false, message: 'Cette clé ne peut pas être modifiée depuis l’interface.' }
  }
  const supabase = await createClient()
  const { error } = await supabase
    .from('system_settings')
    .update({ value, updated_at: new Date().toISOString() })
    .eq('key', key)
  if (error) return { ok: false, message: error.message }
  revalidatePath(`${ADMIN_CONSOLE_PATH}/settings`)
  await setFlash('success', 'Paramètre enregistré.')
  return { ok: true }
}
