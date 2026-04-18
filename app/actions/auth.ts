'use server'

import { redirect } from 'next/navigation'
import { revalidatePath } from 'next/cache'
import { createClient } from '@/lib/supabase/server'
import { setFlash } from '@/lib/flash'

/**
 * Server action de déconnexion.
 * Équivalent de AuthController@logout en PHP.
 */
export async function logoutAction() {
  const supabase = await createClient()
  await supabase.auth.signOut()
  await setFlash('success', 'Vous avez été déconnecté.')
  revalidatePath('/', 'layout')
  redirect('/')
}

/**
 * Met à jour le timestamp de dernière connexion sur profiles.
 * Appelé après un login réussi côté client (ou via callback).
 */
export async function touchLastLogin() {
  const supabase = await createClient()
  const { data: { user } } = await supabase.auth.getUser()
  if (!user) return
  await supabase
    .from('profiles')
    .update({ last_login: new Date().toISOString() })
    .eq('id', user.id)
}
