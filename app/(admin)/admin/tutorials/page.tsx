import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { Badge } from '@/components/ui/badge'

export const metadata: Metadata = { title: 'Formations (admin)' }
export const dynamic = 'force-dynamic'

export default async function AdminTutorialsPage() {
  const supabase = await createClient()
  const { data } = await supabase
    .from('tutorials')
    .select('id, title, category, type, level, status, views, created_at, profiles!tutorials_user_id_fkey(prenom, nom)')
    .order('created_at', { ascending: false })
    .limit(100)
  const tutorials = (data ?? []) as unknown as Array<{
    id: number; title: string; category: string | null
    type: 'video' | 'text' | 'mixed'; level: 'beginner' | 'intermediate' | 'advanced'
    status: 'active' | 'inactive'; views: number; created_at: string
    profiles: { prenom: string; nom: string } | null
  }>

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Formations</h1>
      <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 dark:bg-gray-800/40 text-xs uppercase text-gray-500">
            <tr>
              <th className="text-left p-3">Titre</th>
              <th className="text-left p-3 hidden md:table-cell">Auteur</th>
              <th className="text-left p-3">Type / Niveau</th>
              <th className="text-left p-3">Statut</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
            {tutorials.map(t => (
              <tr key={t.id}>
                <td className="p-3">
                  <Link href={`/formations/${t.id}`} className="font-medium hover:text-[#C8102E]">{t.title}</Link>
                  <div className="text-xs text-gray-500">{t.category ?? '—'} · {t.views} vues</div>
                </td>
                <td className="p-3 hidden md:table-cell text-gray-600 dark:text-gray-400">
                  {t.profiles ? `${t.profiles.prenom} ${t.profiles.nom}` : '—'}
                </td>
                <td className="p-3"><Badge variant="outline">{t.type}</Badge> <Badge variant="default">{t.level}</Badge></td>
                <td className="p-3">
                  {t.status === 'active' ? <Badge variant="success">Actif</Badge> : <Badge variant="danger">Inactif</Badge>}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
