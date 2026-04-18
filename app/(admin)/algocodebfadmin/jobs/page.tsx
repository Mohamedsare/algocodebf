import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import {
  OpportunitesAdminClient,
  type OpportuniteAdminRow,
} from '@/components/admin/opportunites-admin-client'

export const metadata: Metadata = { title: 'Opportunités (admin)' }
export const dynamic = 'force-dynamic'

export default async function AdminJobsPage() {
  const supabase = await createClient()
  const { data, error } = await supabase
    .from('jobs')
    .select(
      'id, title, type, city, status, views, created_at, company_name, profiles!jobs_company_id_fkey(prenom, nom)'
    )
    .order('created_at', { ascending: false })
    .limit(400)

  if (error) {
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Gestion des opportunités</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger les offres : {error.message}</p>
      </div>
    )
  }

  const jobs: OpportuniteAdminRow[] = (data ?? []).map(r => {
    const row = r as OpportuniteAdminRow & {
      profiles: OpportuniteAdminRow['profiles'] | OpportuniteAdminRow['profiles'][] | null
    }
    const p = row.profiles
    const profiles = Array.isArray(p) ? p[0] ?? null : p ?? null
    return { ...row, profiles }
  })

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold m-0">Gestion des opportunités</h1>
      <p className="text-sm text-gray-600 m-0">
        Offres emploi, stages et hackathons : filtres, fermeture / republication sur le catalogue public.
      </p>
      <OpportunitesAdminClient jobs={jobs} />
    </div>
  )
}
