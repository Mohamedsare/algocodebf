import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { Badge } from '@/components/ui/badge'

export const metadata: Metadata = { title: 'Offres' }
export const dynamic = 'force-dynamic'

export default async function AdminJobsPage() {
  const supabase = await createClient()
  const { data } = await supabase
    .from('jobs')
    .select('id, title, type, city, status, views, created_at, company_name, profiles!jobs_company_id_fkey(prenom, nom)')
    .order('created_at', { ascending: false })
    .limit(100)
  const jobs = (data ?? []) as unknown as Array<{
    id: number; title: string; type: string; city: string | null
    status: string; views: number; created_at: string
    company_name: string | null
    profiles: { prenom: string; nom: string } | null
  }>

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Offres</h1>
      <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 dark:bg-gray-800/40 text-xs uppercase text-gray-500">
            <tr>
              <th className="text-left p-3">Offre</th>
              <th className="text-left p-3 hidden md:table-cell">Société</th>
              <th className="text-left p-3">Type / Ville</th>
              <th className="text-left p-3">Statut</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
            {jobs.map(j => (
              <tr key={j.id}>
                <td className="p-3">
                  <Link href={`/job/${j.id}`} className="font-medium hover:text-[#C8102E]">{j.title}</Link>
                  <div className="text-xs text-gray-500">{j.views} vues</div>
                </td>
                <td className="p-3 hidden md:table-cell text-gray-600 dark:text-gray-400">
                  {j.company_name ?? (j.profiles ? `${j.profiles.prenom} ${j.profiles.nom}` : '—')}
                </td>
                <td className="p-3">
                  <Badge variant="outline">{j.type}</Badge>
                  {j.city && <span className="text-xs text-gray-500 ml-2">{j.city}</span>}
                </td>
                <td className="p-3"><Badge variant={j.status === 'active' ? 'success' : 'default'}>{j.status}</Badge></td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
