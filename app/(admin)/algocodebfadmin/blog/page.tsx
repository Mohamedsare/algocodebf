import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import { BlogAdminClient, type BlogPostAdminRow } from '@/components/admin/blog-admin-client'
import type { BlogCategory } from '@/types'

export const metadata: Metadata = { title: 'Blog (admin)' }
export const dynamic = 'force-dynamic'

export default async function AdminBlogPage() {
  const supabase = await createClient()
  const [{ data: cats, error: catErr }, { data: postsData, error: postErr }] = await Promise.all([
    supabase.from('blog_categories').select('id, name, slug, description').order('name'),
    supabase
      .from('blog_posts')
      .select('id, title, slug, category, status, views, created_at, profiles!blog_posts_author_id_fkey(prenom, nom)')
      .order('created_at', { ascending: false })
      .limit(400),
  ])

  if (catErr || postErr) {
    const msg = catErr?.message ?? postErr?.message ?? 'Erreur'
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Gestion du blog</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger le blog : {msg}</p>
      </div>
    )
  }

  const categories = (cats ?? []) as BlogCategory[]
  const posts: BlogPostAdminRow[] = (postsData ?? []).map(r => {
    const row = r as {
      profiles: { prenom: string; nom: string } | { prenom: string; nom: string }[] | null
    } & Omit<BlogPostAdminRow, 'profiles'>
    const p = row.profiles
    const profiles = Array.isArray(p) ? p[0] ?? null : p ?? null
    return { ...row, profiles }
  })

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold m-0">Gestion du blog</h1>
      <p className="text-sm text-gray-600 m-0">
        Articles : statistiques, filtres, publication / archivage rapide. Catégories : création, édition et suppression.
      </p>
      <BlogAdminClient categories={categories} posts={posts} />
    </div>
  )
}
