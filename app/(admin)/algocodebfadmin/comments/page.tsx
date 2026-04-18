import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import {
  CommentairesAdminClient,
  type CommentaireAdminRow,
} from '@/components/admin/commentaires-admin-client'
import type { CommentableType } from '@/types'

export const metadata: Metadata = { title: 'Commentaires (admin)' }
export const dynamic = 'force-dynamic'

export default async function AdminCommentsPage() {
  const supabase = await createClient()
  const { data: rows, error } = await supabase
    .from('comments')
    .select(
      `
      id,
      body,
      status,
      commentable_type,
      commentable_id,
      created_at,
      profiles (prenom, nom, id)
    `
    )
    .order('created_at', { ascending: false })
    .limit(450)

  if (error) {
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Modération des commentaires</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger les commentaires : {error.message}</p>
      </div>
    )
  }

  type RawRow = {
    id: number
    body: string
    status: string
    commentable_type: CommentableType
    commentable_id: number
    created_at: string
    profiles:
      | { prenom: string | null; nom: string | null; id: string }
      | { prenom: string | null; nom: string | null; id: string }[]
      | null
  }

  const normalized = (rows ?? []).map((r): Omit<CommentaireAdminRow, 'blogSlug'> => {
    const raw = r as RawRow
    const prof = raw.profiles
    const profiles = Array.isArray(prof) ? prof[0] ?? null : prof ?? null
    return { ...raw, profiles }
  })

  const blogIds = [
    ...new Set(normalized.filter(c => c.commentable_type === 'blog').map(c => c.commentable_id)),
  ]
  let slugMap = new Map<number, string>()
  if (blogIds.length > 0) {
    const { data: posts } = await supabase.from('blog_posts').select('id, slug').in('id', blogIds)
    slugMap = new Map((posts ?? []).map(p => [p.id as number, p.slug as string]))
  }

  const comments: CommentaireAdminRow[] = normalized.map(c => ({
    ...c,
    blogSlug: c.commentable_type === 'blog' ? slugMap.get(c.commentable_id) ?? null : null,
  }))

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold m-0">Modération des commentaires</h1>
      <p className="text-sm text-gray-600 m-0">
        Forum, formations, blog et projets : recherche locale, filtres par type et statut, masquage ou rétablissement.
      </p>
      <CommentairesAdminClient comments={comments} />
    </div>
  )
}
