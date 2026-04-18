import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { Badge } from '@/components/ui/badge'
import { ModeratePostRow } from '@/components/admin/moderate-post-row'

export const metadata: Metadata = { title: 'Forum' }
export const dynamic = 'force-dynamic'

export default async function AdminForumPage() {
  const supabase = await createClient()
  const { data } = await supabase
    .from('posts')
    .select('id, title, category, status, is_pinned, views, created_at, profiles!posts_user_id_fkey(prenom, nom)')
    .order('created_at', { ascending: false })
    .limit(100)
  const posts = (data ?? []) as unknown as Array<{
    id: number; title: string; category: string | null; status: 'active' | 'inactive'
    is_pinned: boolean; views: number; created_at: string
    profiles: { prenom: string; nom: string } | null
  }>

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Modération du forum</h1>
      <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 dark:bg-gray-800/40 text-xs uppercase text-gray-500">
            <tr>
              <th className="text-left p-3">Sujet</th>
              <th className="text-left p-3 hidden md:table-cell">Auteur</th>
              <th className="text-left p-3">Statut</th>
              <th className="text-right p-3">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
            {posts.map(p => (
              <tr key={p.id}>
                <td className="p-3">
                  <Link href={`/forum/${p.id}`} className="font-medium hover:text-[#C8102E]">
                    {p.title}
                  </Link>
                  <div className="text-xs text-gray-500">
                    {p.category ?? '—'} · {p.views} vues
                  </div>
                </td>
                <td className="p-3 hidden md:table-cell text-gray-600 dark:text-gray-400">
                  {p.profiles ? `${p.profiles.prenom} ${p.profiles.nom}` : 'Anonyme'}
                </td>
                <td className="p-3">
                  {p.is_pinned && <Badge variant="accent" className="mr-1">Épinglé</Badge>}
                  {p.status === 'active' ? <Badge variant="success">Actif</Badge> : <Badge variant="danger">Masqué</Badge>}
                </td>
                <td className="p-3 text-right">
                  <ModeratePostRow id={p.id} isPinned={p.is_pinned} status={p.status} />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
