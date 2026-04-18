import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { Badge } from '@/components/ui/badge'

export const metadata: Metadata = { title: 'Projets' }
export const dynamic = 'force-dynamic'

export default async function AdminProjectsPage() {
  const supabase = await createClient()
  const { data } = await supabase
    .from('projects')
    .select('id, title, status, visibility, looking_for_members, created_at, profiles!projects_owner_id_fkey(prenom, nom)')
    .order('created_at', { ascending: false })
    .limit(100)
  const projects = (data ?? []) as unknown as Array<{
    id: number; title: string; status: string; visibility: string
    looking_for_members: boolean; created_at: string
    profiles: { prenom: string; nom: string } | null
  }>

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Projets</h1>
      <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 dark:bg-gray-800/40 text-xs uppercase text-gray-500">
            <tr>
              <th className="text-left p-3">Projet</th>
              <th className="text-left p-3 hidden md:table-cell">Porteur</th>
              <th className="text-left p-3">Statut</th>
              <th className="text-left p-3">Visibilité</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
            {projects.map(p => (
              <tr key={p.id}>
                <td className="p-3">
                  <Link href={`/project/${p.id}`} className="font-medium hover:text-[#C8102E]">{p.title}</Link>
                  {p.looking_for_members && <Badge variant="accent" className="ml-2 text-[10px]">Recrute</Badge>}
                </td>
                <td className="p-3 hidden md:table-cell text-gray-600 dark:text-gray-400">
                  {p.profiles ? `${p.profiles.prenom} ${p.profiles.nom}` : '—'}
                </td>
                <td className="p-3"><Badge variant="outline">{p.status}</Badge></td>
                <td className="p-3"><Badge variant="default">{p.visibility}</Badge></td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
