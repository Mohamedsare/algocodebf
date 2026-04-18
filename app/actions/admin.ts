'use server'

import { revalidatePath } from 'next/cache'
import { createClient } from '@/lib/supabase/server'
import { requireAdmin } from '@/lib/auth'
import { setFlash } from '@/lib/flash'
import type { ActionResult } from '@/app/actions/users'
import type { UserRole, UserStatus, ReportStatus } from '@/types'

export async function setUserRoleAction(userId: string, role: UserRole): Promise<ActionResult> {
  const admin = await requireAdmin()
  if (userId === admin.id) return { ok: false, message: 'Vous ne pouvez pas modifier votre propre rôle.' }
  const supabase = await createClient()
  const { error } = await supabase.from('profiles').update({ role }).eq('id', userId)
  if (error) return { ok: false, message: error.message }
  revalidatePath('/admin/users')
  await setFlash('success', 'Rôle mis à jour.')
  return { ok: true }
}

export async function setUserStatusAction(userId: string, status: UserStatus): Promise<ActionResult> {
  const admin = await requireAdmin()
  if (userId === admin.id) return { ok: false, message: 'Vous ne pouvez pas modifier votre propre statut.' }
  const supabase = await createClient()
  const { error } = await supabase.from('profiles').update({ status }).eq('id', userId)
  if (error) return { ok: false, message: error.message }
  revalidatePath('/admin/users')
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
  revalidatePath('/admin/users')
  revalidatePath('/admin/permissions')
  return { ok: true }
}

export async function togglePinPostAction(postId: number): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { data } = await supabase.from('posts').select('is_pinned').eq('id', postId).maybeSingle()
  if (!data) return { ok: false, message: 'Post introuvable.' }
  const { error } = await supabase.from('posts').update({ is_pinned: !data.is_pinned }).eq('id', postId)
  if (error) return { ok: false, message: error.message }
  revalidatePath('/admin/forum')
  revalidatePath('/forum')
  return { ok: true }
}

export async function hidePostAction(postId: number): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase.from('posts').update({ status: 'inactive' }).eq('id', postId)
  if (error) return { ok: false, message: error.message }
  revalidatePath('/admin/forum')
  revalidatePath('/forum')
  return { ok: true }
}

export async function restorePostAction(postId: number): Promise<ActionResult> {
  await requireAdmin()
  const supabase = await createClient()
  const { error } = await supabase.from('posts').update({ status: 'active' }).eq('id', postId)
  if (error) return { ok: false, message: error.message }
  revalidatePath('/admin/forum')
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
  revalidatePath('/admin/reports')
  return { ok: true }
}
