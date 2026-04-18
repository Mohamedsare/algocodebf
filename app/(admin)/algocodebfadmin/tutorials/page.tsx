import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import {
  FormationsAdminClient,
  type FormationAdminRow,
} from '@/components/admin/formations-admin-client'

export const metadata: Metadata = { title: 'Formations (admin)' }
export const dynamic = 'force-dynamic'

export default async function AdminTutorialsPage() {
  const supabase = await createClient()
  const { data, error } = await supabase
    .from('tutorials')
    .select('id, title, category, type, level, status, views, created_at, profiles!tutorials_user_id_fkey(prenom, nom)')
    .order('created_at', { ascending: false })
    .limit(400)

  if (error) {
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Gestion des formations</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger les formations : {error.message}</p>
      </div>
    )
  }

  const tutorials: FormationAdminRow[] = (data ?? []).map(r => {
    const row = r as FormationAdminRow & {
      profiles: FormationAdminRow['profiles'] | FormationAdminRow['profiles'][] | null
    }
    const p = row.profiles
    const profiles = Array.isArray(p) ? p[0] ?? null : p ?? null
    return { ...row, profiles }
  })

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold m-0">Gestion des formations</h1>
      <p className="text-sm text-gray-600 m-0">
        Modération du catalogue public : recherche, filtres, masquage / republication et accès rapide à l’édition.
      </p>
      <FormationsAdminClient tutorials={tutorials} />
    </div>
  )
}
