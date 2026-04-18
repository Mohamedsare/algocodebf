import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import { ProjetsAdminClient, type ProjetAdminRow } from '@/components/admin/projets-admin-client'

export const metadata: Metadata = { title: 'Projets (admin)' }
export const dynamic = 'force-dynamic'

export default async function AdminProjectsPage() {
  const supabase = await createClient()
  const { data, error } = await supabase
    .from('projects')
    .select('id, title, status, visibility, looking_for_members, created_at, profiles!projects_owner_id_fkey(prenom, nom)')
    .order('created_at', { ascending: false })
    .limit(400)

  if (error) {
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Gestion des projets</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger les projets : {error.message}</p>
      </div>
    )
  }

  const projects: ProjetAdminRow[] = (data ?? []).map(r => {
    const row = r as ProjetAdminRow & {
      profiles: ProjetAdminRow['profiles'] | ProjetAdminRow['profiles'][] | null
    }
    const p = row.profiles
    const profiles = Array.isArray(p) ? p[0] ?? null : p ?? null
    return { ...row, profiles }
  })

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold m-0">Gestion des projets</h1>
      <p className="text-sm text-gray-600 m-0">
        Modération de la vitrine : recherche, filtres, archivage / réactivation et liens vers la fiche ou l’édition.
      </p>
      <ProjetsAdminClient projects={projects} />
    </div>
  )
}
