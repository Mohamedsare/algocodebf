import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import { getForumCategories } from '@/lib/queries/forum'
import { ForumAdminClient } from '@/components/admin/forum-admin-client'

export const metadata: Metadata = { title: 'Forum' }
export const dynamic = 'force-dynamic'

export default async function AdminForumPage() {
  const supabase = await createClient()
  const [categories, { data }] = await Promise.all([
    getForumCategories(),
    supabase
      .from('posts')
      .select('id, title, category, status, is_pinned, views, created_at, profiles!posts_user_id_fkey(prenom, nom)')
      .order('created_at', { ascending: false })
      .limit(100),
  ])

  type PostRow = {
    id: number
    title: string
    category: string | null
    status: 'active' | 'inactive'
    is_pinned: boolean
    views: number
    created_at: string
    profiles: { prenom: string; nom: string } | null
  }
  const posts: PostRow[] = (data ?? []).map(r => {
    const row = r as {
      profiles: { prenom: string; nom: string } | { prenom: string; nom: string }[] | null
    } & Omit<PostRow, 'profiles'>
    const p = row.profiles
    const profiles = Array.isArray(p) ? p[0] ?? null : p ?? null
    return { ...row, profiles }
  })

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold text-[var(--dark-color,#2c3e50)] m-0">Gestion du forum</h1>
      <ForumAdminClient categories={categories} posts={posts} />
    </div>
  )
}
