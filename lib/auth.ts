import { redirect } from 'next/navigation'
import { headers } from 'next/headers'
import { createClient } from '@/lib/supabase/server'
import type { Profile } from '@/types'

/**
 * Helpers d'autorisation côté serveur (Server Components & Server Actions).
 * - `requireLogin()` → redirige vers /login si non connecté, sinon retourne le profil.
 * - `requireAdmin()` → identique + contrôle du rôle 'admin'.
 * - `requireRole()` → contrôle de rôle générique.
 * - `requirePermission()` → permissions granulaires (can_create_tutorial, etc.).
 *
 * Équivaut à `Controller::requireLogin()` et `Controller::requireAdmin()` du PHP.
 */

async function currentPath(): Promise<string> {
  const h = await headers()
  return h.get('x-invoke-path') ?? h.get('x-next-pathname') ?? h.get('referer') ?? '/'
}

export async function getCurrentProfile(): Promise<Profile | null> {
  const supabase = await createClient()
  const { data: { user } } = await supabase.auth.getUser()
  if (!user) return null
  const { data } = await supabase.from('profiles').select('*').eq('id', user.id).single()
  return data as Profile | null
}

export async function requireLogin(redirectTo?: string): Promise<Profile> {
  const profile = await getCurrentProfile()
  if (!profile) {
    const target = redirectTo ?? (await currentPath())
    const url = `/login?redirect=${encodeURIComponent(target)}`
    redirect(url)
  }
  return profile
}

export async function requireAdmin(redirectTo?: string): Promise<Profile> {
  const profile = await requireLogin(redirectTo)
  if (profile.role !== 'admin') {
    redirect('/')
  }
  return profile
}

export async function requireRole(
  roles: Profile['role'] | Profile['role'][],
  redirectTo?: string
): Promise<Profile> {
  const profile = await requireLogin(redirectTo)
  const list = Array.isArray(roles) ? roles : [roles]
  if (!list.includes(profile.role)) redirect('/')
  return profile
}

export async function requirePermission(
  permission: 'can_create_tutorial' | 'can_create_project',
  redirectTo?: string
): Promise<Profile> {
  const profile = await requireLogin(redirectTo)
  if (profile.role === 'admin') return profile
  if (!profile[permission]) redirect('/')
  return profile
}

export async function isAdmin(profile: Profile | null): Promise<boolean> {
  return profile?.role === 'admin'
}

/** Alias ergonomique : currentProfile() === getCurrentProfile() */
export const currentProfile = getCurrentProfile
