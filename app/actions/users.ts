'use server'

import { revalidatePath } from 'next/cache'
import { createClient } from '@/lib/supabase/server'
import { requireLogin } from '@/lib/auth'
import { BUCKETS, uploadFile, validateUpload, deleteFile } from '@/lib/uploads'
import { setFlash } from '@/lib/flash'
import { profileEditSchema } from '@/lib/validation'

export type ActionResult<T = unknown> = {
  ok: boolean
  message?: string
  errors?: Record<string, string>
  data?: T
}

/** Met à jour le profil texte (bio, université, etc.). */
export async function updateProfileAction(formData: FormData): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const raw = {
    prenom: formData.get('prenom')?.toString() ?? '',
    nom: formData.get('nom')?.toString() ?? '',
    phone: formData.get('phone')?.toString() ?? '',
    university: formData.get('university')?.toString() ?? '',
    faculty: formData.get('faculty')?.toString() ?? '',
    city: formData.get('city')?.toString() ?? '',
    bio: formData.get('bio')?.toString() ?? '',
  }

  const parsed = profileEditSchema.safeParse(raw)
  if (!parsed.success) {
    const errs: Record<string, string> = {}
    for (const issue of parsed.error.issues) {
      if (issue.path[0]) errs[String(issue.path[0])] = issue.message
    }
    return { ok: false, errors: errs, message: 'Veuillez corriger les champs en erreur.' }
  }

  const payload = parsed.data
  const { error } = await supabase
    .from('profiles')
    .update({
      prenom: payload.prenom,
      nom: payload.nom,
      phone: payload.phone || null,
      university: payload.university || null,
      faculty: payload.faculty || null,
      city: payload.city || null,
      bio: payload.bio || null,
    })
    .eq('id', profile.id)

  if (error) return { ok: false, message: error.message }

  revalidatePath('/user/modifier')
  revalidatePath(`/user/${profile.id}`)
  await setFlash('success', 'Profil mis à jour avec succès.')
  return { ok: true }
}

/** Upload de la photo de profil vers le bucket avatars/{user_id}/ */
export async function uploadAvatarAction(formData: FormData): Promise<ActionResult<{ path: string }>> {
  const profile = await requireLogin()
  const supabase = await createClient()
  const file = formData.get('avatar') as File | null
  if (!file || file.size === 0) return { ok: false, message: 'Aucun fichier fourni.' }

  try {
    validateUpload(file, 'avatar')
  } catch (e) {
    return { ok: false, message: (e as Error).message }
  }

  // Supprime l'ancien (best-effort)
  if (profile.photo_path && !profile.photo_path.startsWith('http')) {
    await deleteFile(supabase, BUCKETS.avatars, profile.photo_path).catch(() => null)
  }

  try {
    const { path } = await uploadFile(supabase, BUCKETS.avatars, file, profile.id, 'avatar')
    await supabase.from('profiles').update({ photo_path: path }).eq('id', profile.id)
    revalidatePath('/user/modifier')
    revalidatePath(`/user/${profile.id}`)
    return { ok: true, data: { path } }
  } catch (e) {
    return { ok: false, message: (e as Error).message }
  }
}

/** Upload du CV (PDF). */
export async function uploadCvAction(formData: FormData): Promise<ActionResult<{ path: string }>> {
  const profile = await requireLogin()
  const supabase = await createClient()
  const file = formData.get('cv') as File | null
  if (!file || file.size === 0) return { ok: false, message: 'Aucun fichier fourni.' }

  try {
    validateUpload(file, 'cv')
  } catch (e) {
    return { ok: false, message: (e as Error).message }
  }

  if (profile.cv_path && !profile.cv_path.startsWith('http')) {
    await deleteFile(supabase, BUCKETS.cvs, profile.cv_path).catch(() => null)
  }

  try {
    const { path } = await uploadFile(supabase, BUCKETS.cvs, file, profile.id, 'cv')
    await supabase.from('profiles').update({ cv_path: path }).eq('id', profile.id)
    revalidatePath('/user/modifier')
    return { ok: true, data: { path } }
  } catch (e) {
    return { ok: false, message: (e as Error).message }
  }
}

export async function deleteCvAction(): Promise<ActionResult> {
  const profile = await requireLogin()
  if (!profile.cv_path) return { ok: true }
  const supabase = await createClient()
  await deleteFile(supabase, BUCKETS.cvs, profile.cv_path).catch(() => null)
  await supabase.from('profiles').update({ cv_path: null }).eq('id', profile.id)
  revalidatePath('/user/modifier')
  return { ok: true, message: 'CV supprimé.' }
}

/** Toggle follow/unfollow. */
export async function toggleFollowAction(targetId: string): Promise<ActionResult<{ following: boolean }>> {
  const me = await requireLogin()
  if (me.id === targetId) return { ok: false, message: 'Impossible de se suivre soi-même.' }

  const supabase = await createClient()
  const { data: existing } = await supabase
    .from('follows')
    .select('follower_id')
    .eq('follower_id', me.id)
    .eq('following_id', targetId)
    .maybeSingle()

  if (existing) {
    await supabase
      .from('follows')
      .delete()
      .eq('follower_id', me.id)
      .eq('following_id', targetId)
    revalidatePath(`/user/${targetId}`)
    return { ok: true, data: { following: false } }
  }

  await supabase.from('follows').insert({ follower_id: me.id, following_id: targetId })
  revalidatePath(`/user/${targetId}`)
  return { ok: true, data: { following: true } }
}

type SkillLevel = 'beginner' | 'intermediate' | 'advanced'

export interface SkillEntry {
  id: number
  level: SkillLevel
}

/** Met à jour les skills de l'utilisateur (remplacement complet). */
export async function updateSkillsAction(
  skills: number[] | SkillEntry[],
  level: SkillLevel = 'intermediate'
): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  await supabase.from('user_skills').delete().eq('user_id', profile.id)

  const entries: SkillEntry[] = skills.length
    ? typeof (skills[0] as SkillEntry).id === 'number' && typeof (skills[0] as SkillEntry).level === 'string'
      ? (skills as SkillEntry[])
      : (skills as number[]).map(id => ({ id, level }))
    : []

  if (entries.length) {
    const rows = entries.map(e => ({
      user_id: profile.id,
      skill_id: e.id,
      level: e.level,
    }))
    const { error } = await supabase.from('user_skills').insert(rows)
    if (error) return { ok: false, message: error.message }
  }

  revalidatePath('/user/modifier')
  revalidatePath(`/user/${profile.id}`)
  return { ok: true }
}
