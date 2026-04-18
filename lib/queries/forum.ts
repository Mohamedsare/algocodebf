import { createClient } from '@/lib/supabase/server'
import type { Post, ForumCategory, PostAttachment, Comment } from '@/types'

/** Auteur compact (projection utilisée dans les listings). */
export interface PostAuthorSummary {
  id: string
  prenom: string
  nom: string
  photo_path: string | null
  university: string | null
}

/**
 * Résultat d'une liste paginée de posts avec l'auteur dénormalisé
 * et quelques compteurs (commentaires, likes) calculés en lot.
 * On n'étend pas `Post` car `Post.author` attend un `Profile` complet,
 * alors que le listing ne projette qu'un sous-ensemble.
 */
export type ForumPostListItem = Omit<Post, 'author' | 'comments_count' | 'likes_count'> & {
  author: PostAuthorSummary | null
  comments_count: number
  likes_count: number
}

interface ListForumOptions {
  category?: string | null
  search?: string
  page?: number
  pageSize?: number
}

export async function listForumPosts(opts: ListForumOptions = {}) {
  const supabase = await createClient()
  const { category, search, page = 1, pageSize = 20 } = opts

  let q = supabase
    .from('posts')
    .select(
      `id, user_id, title, body, category, views, is_pinned, status, created_at, updated_at,
       profiles!posts_user_id_fkey(id, prenom, nom, photo_path, university)`,
      { count: 'exact' }
    )
    .eq('status', 'active')

  if (category) q = q.eq('category', category)
  if (search) {
    const p = `%${search}%`
    q = q.or(`title.ilike.${p},body.ilike.${p}`)
  }

  q = q.order('is_pinned', { ascending: false }).order('created_at', { ascending: false })
  const from = (page - 1) * pageSize
  q = q.range(from, from + pageSize - 1)

  const { data, count, error } = await q
  if (error) throw error

  const rows = (data ?? []) as Array<Record<string, unknown>>
  const ids = rows.map(r => r.id as number)

  // Compteurs en lot
  const [comments, likes] = await Promise.all([
    ids.length
      ? supabase
          .from('comments')
          .select('commentable_id')
          .eq('commentable_type', 'post')
          .eq('status', 'active')
          .in('commentable_id', ids)
      : Promise.resolve({ data: [] }),
    ids.length
      ? supabase.from('likes').select('likeable_id').eq('likeable_type', 'post').in('likeable_id', ids)
      : Promise.resolve({ data: [] }),
  ])

  const commentCount = new Map<number, number>()
  for (const c of comments.data ?? []) {
    const id = (c as { commentable_id: number }).commentable_id
    commentCount.set(id, (commentCount.get(id) ?? 0) + 1)
  }
  const likeCount = new Map<number, number>()
  for (const l of likes.data ?? []) {
    const id = (l as { likeable_id: number }).likeable_id
    likeCount.set(id, (likeCount.get(id) ?? 0) + 1)
  }

  const posts: ForumPostListItem[] = rows.map(r => {
    const raw = r as unknown as {
      id: number
      user_id: string | null
      title: string
      body: string
      category: string | null
      views: number
      is_pinned: boolean
      status: 'active' | 'inactive'
      created_at: string
      updated_at: string
      profiles:
        | { id: string; prenom: string; nom: string; photo_path: string | null; university: string | null }
        | null
    }
    return {
      id: raw.id,
      user_id: raw.user_id,
      title: raw.title,
      body: raw.body,
      category: raw.category,
      views: raw.views,
      is_pinned: raw.is_pinned,
      status: raw.status,
      created_at: raw.created_at,
      updated_at: raw.updated_at,
      author: raw.profiles,
      comments_count: commentCount.get(raw.id) ?? 0,
      likes_count: likeCount.get(raw.id) ?? 0,
    }
  })

  return {
    posts,
    total: count ?? 0,
    page,
    pageSize,
    totalPages: Math.ceil((count ?? 0) / pageSize),
  }
}

export async function getForumCategories(): Promise<ForumCategory[]> {
  const supabase = await createClient()
  const { data } = await supabase
    .from('forum_categories')
    .select('id, name, slug, description')
    .order('name')
  return (data ?? []) as ForumCategory[]
}

/**
 * Statistiques Forum (équivalent de forumData['stats'] dans le PHP).
 * - total_posts : posts actifs
 * - active_members : utilisateurs ayant posté les 30 derniers jours
 * - trending_topics : posts avec >5 vues dans les 7 derniers jours
 * - today_posts : posts créés aujourd'hui
 */
export async function getForumStats() {
  const supabase = await createClient()
  const now = new Date()
  const d30 = new Date(now.getTime() - 30 * 24 * 3600 * 1000).toISOString()
  const d7 = new Date(now.getTime() - 7 * 24 * 3600 * 1000).toISOString()
  const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate()).toISOString()

  const [total, activeRows, trending, today] = await Promise.all([
    supabase.from('posts').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('posts').select('user_id').eq('status', 'active').gte('created_at', d30),
    supabase
      .from('posts')
      .select('*', { count: 'exact', head: true })
      .eq('status', 'active')
      .gt('views', 5)
      .gte('created_at', d7),
    supabase
      .from('posts')
      .select('*', { count: 'exact', head: true })
      .eq('status', 'active')
      .gte('created_at', todayStart),
  ])

  const activeSet = new Set((activeRows.data ?? []).map(r => (r as { user_id: string }).user_id).filter(Boolean))

  return {
    total_posts: total.count ?? 0,
    active_members: activeSet.size,
    trending_topics: trending.count ?? 0,
    today_posts: today.count ?? 0,
  }
}

/**
 * Détail complet d'une discussion avec auteur + attachments + compteurs.
 */
export async function getForumPost(id: number, currentUserId?: string | null) {
  const supabase = await createClient()

  const { data: raw } = await supabase
    .from('posts')
    .select(
      `id, user_id, title, body, category, views, is_pinned, status, created_at, updated_at,
       profiles!posts_user_id_fkey(id, prenom, nom, photo_path, university, faculty, city)`
    )
    .eq('id', id)
    .eq('status', 'active')
    .maybeSingle()

  if (!raw) return null

  const post = raw as unknown as Post & {
    profiles: {
      id: string
      prenom: string
      nom: string
      photo_path: string | null
      university: string | null
      faculty: string | null
      city: string | null
    } | null
  }

  const [{ data: attachments }, { count: likesCount }, liked] = await Promise.all([
    supabase.from('post_attachments').select('*').eq('post_id', id).order('created_at'),
    supabase.from('likes').select('*', { count: 'exact', head: true }).eq('likeable_type', 'post').eq('likeable_id', id),
    currentUserId
      ? supabase
          .from('likes')
          .select('id')
          .eq('likeable_type', 'post')
          .eq('likeable_id', id)
          .eq('user_id', currentUserId)
          .maybeSingle()
      : Promise.resolve({ data: null }),
  ])

  return {
    post,
    author: post.profiles,
    attachments: (attachments ?? []) as PostAttachment[],
    likes_count: likesCount ?? 0,
    liked_by_user: Boolean(liked.data),
  }
}

/**
 * Récupère les commentaires d'une ressource polymorphe, avec leurs auteurs.
 * Utilisé pour le forum et autres (tutoriels, blog, projets).
 */
export type CommentWithAuthor = Omit<Comment, 'author'> & {
  author: { id: string; prenom: string; nom: string; photo_path: string | null } | null
}

export async function getComments(
  type: 'post' | 'tutorial' | 'blog' | 'project',
  id: number
): Promise<CommentWithAuthor[]> {
  const supabase = await createClient()
  const { data } = await supabase
    .from('comments')
    .select(
      `id, user_id, commentable_type, commentable_id, body, status, created_at, updated_at,
       profiles!comments_user_id_fkey(id, prenom, nom, photo_path)`
    )
    .eq('commentable_type', type)
    .eq('commentable_id', id)
    .eq('status', 'active')
    .order('created_at', { ascending: true })

  return ((data ?? []) as unknown as Array<
    Omit<Comment, 'author'> & {
      profiles: { id: string; prenom: string; nom: string; photo_path: string | null } | null
    }
  >).map(c => {
    const { profiles, ...rest } = c
    return { ...rest, author: profiles }
  })
}
