import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Plus, Edit } from 'lucide-react'

export const metadata: Metadata = { title: 'Blog' }
export const dynamic = 'force-dynamic'

export default async function AdminBlogPage() {
  const supabase = await createClient()
  const { data } = await supabase
    .from('blog_posts')
    .select('id, title, slug, category, status, views, created_at, profiles!blog_posts_author_id_fkey(prenom, nom)')
    .order('created_at', { ascending: false })
    .limit(100)
  const posts = (data ?? []) as unknown as Array<{
    id: number; title: string; slug: string; category: string | null
    status: 'draft' | 'published' | 'archived'; views: number; created_at: string
    profiles: { prenom: string; nom: string } | null
  }>

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between flex-wrap gap-3">
        <h1 className="text-2xl font-bold">Articles du blog</h1>
        <Link href="/blog/creer"><Button><Plus size={14} /> Nouvel article</Button></Link>
      </div>
      <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 dark:bg-gray-800/40 text-xs uppercase text-gray-500">
            <tr>
              <th className="text-left p-3">Titre</th>
              <th className="text-left p-3 hidden md:table-cell">Catégorie</th>
              <th className="text-left p-3 hidden md:table-cell">Auteur</th>
              <th className="text-left p-3">Statut</th>
              <th className="text-right p-3"></th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
            {posts.map(p => (
              <tr key={p.id}>
                <td className="p-3">
                  <Link href={`/blog/${p.slug}`} className="font-medium hover:text-[#C8102E]">{p.title}</Link>
                  <div className="text-xs text-gray-500">{p.views} vues</div>
                </td>
                <td className="p-3 hidden md:table-cell text-gray-600 dark:text-gray-400">{p.category ?? '—'}</td>
                <td className="p-3 hidden md:table-cell text-gray-600 dark:text-gray-400">
                  {p.profiles ? `${p.profiles.prenom} ${p.profiles.nom}` : '—'}
                </td>
                <td className="p-3">
                  {p.status === 'published' && <Badge variant="success">Publié</Badge>}
                  {p.status === 'draft' && <Badge variant="warning">Brouillon</Badge>}
                  {p.status === 'archived' && <Badge variant="outline">Archivé</Badge>}
                </td>
                <td className="p-3 text-right">
                  <Link href={`/blog/${p.slug}/modifier`}>
                    <Button variant="ghost" size="sm"><Edit size={14} /></Button>
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
