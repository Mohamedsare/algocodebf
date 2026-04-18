import { createClient } from '@/lib/supabase/server'
import type { Profile, LeaderboardEntry } from '@/types'

/** Période utilisée pour filtrer le classement (équivalent PHP User::getLeaderboard). */
export type LeaderboardPeriod = 'week' | 'month' | 'all'

interface ListUsersOptions {
  search?: string
  university?: string
  city?: string
  skill?: string
  sort?: 'recent' | 'name' | 'popular'
  page?: number
  pageSize?: number
}

/** Annuaire des membres avec filtres. Équivalent de UserController@filterMembers. */
export async function listUsers(opts: ListUsersOptions = {}) {
  const supabase = await createClient()
  const { search, university, city, sort = 'recent', page = 1, pageSize = 24 } = opts

  let query = supabase
    .from('profiles')
    .select('id, prenom, nom, university, faculty, city, bio, photo_path, role, created_at', {
      count: 'exact',
    })
    .eq('status', 'active')

  if (search) {
    const p = `%${search}%`
    query = query.or(`prenom.ilike.${p},nom.ilike.${p},university.ilike.${p},faculty.ilike.${p}`)
  }
  if (university) query = query.ilike('university', `%${university}%`)
  if (city) query = query.eq('city', city)

  switch (sort) {
    case 'name':
      query = query.order('prenom', { ascending: true })
      break
    case 'popular':
      // ordre par score du classement — fait après fetch (voir page.tsx)
      query = query.order('created_at', { ascending: false })
      break
    default:
      query = query.order('created_at', { ascending: false })
  }

  const from = (page - 1) * pageSize
  query = query.range(from, from + pageSize - 1)

  const { data, count, error } = await query
  if (error) throw error
  return {
    users: (data ?? []) as Array<Pick<Profile, 'id' | 'prenom' | 'nom' | 'university' | 'faculty' | 'city' | 'bio' | 'photo_path' | 'role' | 'created_at'>>,
    total: count ?? 0,
    page,
    pageSize,
    totalPages: Math.ceil((count ?? 0) / pageSize),
  }
}

/** Options de filtres (universités / villes / compétences) pour l'annuaire. */
export async function getUserFilterOptions() {
  const supabase = await createClient()
  const [{ data: unis }, { data: cities }, { data: skills }] = await Promise.all([
    supabase.from('profiles').select('university').eq('status', 'active').not('university', 'is', null),
    supabase.from('profiles').select('city').eq('status', 'active').not('city', 'is', null),
    supabase.from('skills').select('id, name, category').order('name'),
  ])

  const universities = Array.from(new Set((unis ?? []).map(u => u.university).filter(Boolean))).sort() as string[]
  const cityList = Array.from(new Set((cities ?? []).map(c => c.city).filter(Boolean))).sort() as string[]
  return { universities, cities: cityList, skills: skills ?? [] }
}

/**
 * Profil public enrichi : compteurs + followers + badges + skills.
 * Équivalent UserController@profile (agrégation en JS plutôt qu'en SQL).
 */
export async function getPublicProfile(userId: string) {
  const supabase = await createClient()

  const { data: profile } = await supabase
    .from('profiles')
    .select('*')
    .eq('id', userId)
    .eq('status', 'active')
    .maybeSingle()

  if (!profile) return null

  const [
    { count: postsCount },
    { count: tutorialsCount },
    { count: projectsCount },
    { count: followersCount },
    { count: followingCount },
    { data: badges },
    { data: skills },
  ] = await Promise.all([
    supabase.from('posts').select('*', { count: 'exact', head: true }).eq('user_id', userId).eq('status', 'active'),
    supabase.from('tutorials').select('*', { count: 'exact', head: true }).eq('user_id', userId).eq('status', 'active'),
    supabase.from('projects').select('*', { count: 'exact', head: true }).eq('owner_id', userId),
    supabase.from('follows').select('*', { count: 'exact', head: true }).eq('following_id', userId),
    supabase.from('follows').select('*', { count: 'exact', head: true }).eq('follower_id', userId),
    supabase
      .from('user_badges')
      .select('awarded_at, badges(id, name, description, icon)')
      .eq('user_id', userId)
      .order('awarded_at', { ascending: false }),
    supabase
      .from('user_skills')
      .select('level, skills(id, name, category)')
      .eq('user_id', userId),
  ])

  return {
    profile: profile as Profile,
    counts: {
      posts: postsCount ?? 0,
      tutorials: tutorialsCount ?? 0,
      projects: projectsCount ?? 0,
      followers: followersCount ?? 0,
      following: followingCount ?? 0,
    },
    badges: badges ?? [],
    skills: skills ?? [],
  }
}

/** Posts récents d'un utilisateur (pour son onglet profil). */
export async function getUserPosts(userId: string, limit = 10) {
  const supabase = await createClient()
  const { data } = await supabase
    .from('posts')
    .select('id, title, body, category, views, created_at')
    .eq('user_id', userId)
    .eq('status', 'active')
    .order('created_at', { ascending: false })
    .limit(limit)
  return data ?? []
}

export async function getUserTutorials(userId: string, limit = 10) {
  const supabase = await createClient()
  const { data } = await supabase
    .from('tutorials')
    .select('id, title, description, thumbnail, category, level, views, likes_count, created_at')
    .eq('user_id', userId)
    .eq('status', 'active')
    .order('created_at', { ascending: false })
    .limit(limit)
  return data ?? []
}

export async function getUserProjects(userId: string, limit = 10) {
  const supabase = await createClient()
  const { data } = await supabase
    .from('projects')
    .select('id, title, description, status, visibility, looking_for_members, created_at')
    .eq('owner_id', userId)
    .eq('visibility', 'public')
    .order('created_at', { ascending: false })
    .limit(limit)
  return data ?? []
}

/** Vérifie si currentUserId suit userId. */
export async function isFollowing(currentUserId: string, userId: string): Promise<boolean> {
  if (currentUserId === userId) return false
  const supabase = await createClient()
  const { data } = await supabase
    .from('follows')
    .select('follower_id')
    .eq('follower_id', currentUserId)
    .eq('following_id', userId)
    .maybeSingle()
  return Boolean(data)
}

/**
 * Classement — formule PHP :
 * score = posts*5 + tutorials*10 + comments*2 + likes_received*1
 * Filtre période optionnel.
 */
export async function getLeaderboard(
  period: LeaderboardPeriod = 'all',
  limit = 50
): Promise<LeaderboardEntry[]> {
  const supabase = await createClient()

  // La vue leaderboard_scores (all-time) est déjà définie dans schema.sql
  if (period === 'all') {
    const { data } = await supabase
      .from('leaderboard_scores')
      .select('*')
      .limit(limit)
    return (data ?? []) as LeaderboardEntry[]
  }

  // Période : on recalcule en JS à partir des données récentes (simple mais correct)
  const sinceDate = new Date()
  if (period === 'week') sinceDate.setDate(sinceDate.getDate() - 7)
  else sinceDate.setMonth(sinceDate.getMonth() - 1)
  const since = sinceDate.toISOString()

  const [
    { data: profiles },
    { data: posts },
    { data: tutorials },
    { data: comments },
    { data: likes },
  ] = await Promise.all([
    supabase
      .from('profiles')
      .select('id, prenom, nom, photo_path, university, city')
      .eq('status', 'active'),
    supabase.from('posts').select('user_id').eq('status', 'active').gte('created_at', since),
    supabase.from('tutorials').select('user_id').eq('status', 'active').gte('created_at', since),
    supabase.from('comments').select('user_id').eq('status', 'active').gte('created_at', since),
    supabase.from('likes').select('likeable_id, likeable_type').gte('created_at', since).eq('likeable_type', 'post'),
  ])

  const countsBy = (rows: Array<{ user_id: string | null }> | null | undefined) => {
    const out = new Map<string, number>()
    for (const r of rows ?? []) {
      if (!r.user_id) continue
      out.set(r.user_id, (out.get(r.user_id) ?? 0) + 1)
    }
    return out
  }

  const postByUser = countsBy(posts)
  const tutoByUser = countsBy(tutorials)
  const commentByUser = countsBy(comments)

  // likes reçus : on joint likes -> posts.user_id
  const likedPostIds = (likes ?? []).map(l => Number(l.likeable_id))
  const likesByUser = new Map<string, number>()
  if (likedPostIds.length) {
    const { data: owners } = await supabase
      .from('posts')
      .select('id, user_id')
      .in('id', likedPostIds)
    const ownerOf = new Map((owners ?? []).map(o => [o.id, o.user_id] as const))
    for (const l of likes ?? []) {
      const owner = ownerOf.get(Number(l.likeable_id))
      if (!owner) continue
      likesByUser.set(owner, (likesByUser.get(owner) ?? 0) + 1)
    }
  }

  const entries: LeaderboardEntry[] = (profiles ?? []).map(p => {
    const posts_count = postByUser.get(p.id) ?? 0
    const tutorials_count = tutoByUser.get(p.id) ?? 0
    const comments_count = commentByUser.get(p.id) ?? 0
    const likes_received = likesByUser.get(p.id) ?? 0
    const score = posts_count * 5 + tutorials_count * 10 + comments_count * 2 + likes_received
    return {
      id: p.id,
      prenom: p.prenom,
      nom: p.nom,
      photo_path: p.photo_path,
      university: p.university,
      city: p.city,
      score,
      posts_count,
      tutorials_count,
      comments_count,
      likes_received,
    }
  })

  return entries
    .filter(e => e.score > 0)
    .sort((a, b) => b.score - a.score)
    .slice(0, limit)
}
