import type { SupabaseClient } from '@supabase/supabase-js'
import { FORMATIONS_PATH } from '@/lib/routes'
import { sanitizeSearchQuery } from '@/lib/search/sanitize'

export type SearchSuggestScope =
  | 'members'
  | 'posts'
  | 'tutorials'
  | 'blog'
  | 'projects'
  | 'jobs'

export interface SearchSuggestItem {
  id: string | number
  title: string
  subtitle: string | null
  href: string
}

export type SearchSuggestResponse = Record<SearchSuggestScope, SearchSuggestItem[]>

const ALL_SCOPES: SearchSuggestScope[] = [
  'members',
  'posts',
  'tutorials',
  'blog',
  'projects',
  'jobs',
]

const SCOPE_LABELS: Record<SearchSuggestScope, string> = {
  members: 'Membres',
  posts: 'Forum',
  tutorials: 'Formations',
  blog: 'Blog',
  projects: 'Projets',
  jobs: 'Opportunités',
}

export function parseSuggestScopes(param: string | null): Set<SearchSuggestScope> {
  if (!param || param === 'all' || param === '*') {
    return new Set(ALL_SCOPES)
  }
  const set = new Set<SearchSuggestScope>()
  for (const part of param.split(',').map(s => s.trim().toLowerCase())) {
    if (ALL_SCOPES.includes(part as SearchSuggestScope)) {
      set.add(part as SearchSuggestScope)
    }
  }
  return set.size > 0 ? set : new Set(ALL_SCOPES)
}

export function scopeLabel(scope: SearchSuggestScope): string {
  return SCOPE_LABELS[scope]
}

export async function runSearchSuggest(
  supabase: SupabaseClient,
  rawQ: string,
  opts: { scopes: Set<SearchSuggestScope>; limit: number }
): Promise<SearchSuggestResponse> {
  const q = sanitizeSearchQuery(rawQ)
  const empty: SearchSuggestResponse = {
    members: [],
    posts: [],
    tutorials: [],
    blog: [],
    projects: [],
    jobs: [],
  }

  if (q.length < 2) return empty

  const { scopes, limit } = opts
  const like = `%${q}%`
  const lim = Math.min(Math.max(1, limit), 12)

  const tasks: Promise<void>[] = []

  if (scopes.has('members')) {
    tasks.push(
      (async () => {
        const { data } = await supabase
          .from('profiles')
          .select('id, prenom, nom, university, faculty, points')
          .eq('status', 'active')
          .or(`prenom.ilike.${like},nom.ilike.${like},university.ilike.${like},faculty.ilike.${like}`)
          .order('points', { ascending: false })
          .limit(lim)
        empty.members = (data ?? []).map(row => ({
          id: row.id,
          title: [row.prenom, row.nom].filter(Boolean).join(' ').trim() || 'Membre',
          subtitle: row.university ?? row.faculty ?? null,
          href: `/user/${row.id}`,
        }))
      })()
    )
  }

  if (scopes.has('posts')) {
    tasks.push(
      (async () => {
        const { data } = await supabase
          .from('posts')
          .select('id, title, profiles!inner(prenom, nom)')
          .eq('status', 'active')
          .ilike('title', like)
          .order('created_at', { ascending: false })
          .limit(lim)
        empty.posts = (data ?? []).map(row => {
          const author = row.profiles as unknown as { prenom: string; nom: string } | null
          return {
            id: row.id,
            title: row.title,
            subtitle: author ? `${author.prenom} ${author.nom}`.trim() : null,
            href: `/forum/${row.id}`,
          }
        })
      })()
    )
  }

  if (scopes.has('tutorials')) {
    tasks.push(
      (async () => {
        const { data } = await supabase
          .from('tutorials')
          .select('id, title, description, profiles!inner(prenom, nom)')
          .eq('status', 'active')
          .or(`title.ilike.${like},description.ilike.${like}`)
          .order('views', { ascending: false })
          .limit(lim)
        empty.tutorials = (data ?? []).map(row => {
          const author = row.profiles as unknown as { prenom: string; nom: string } | null
          return {
            id: row.id,
            title: row.title,
            subtitle: author ? `Par ${author.prenom} ${author.nom}`.trim() : null,
            href: `${FORMATIONS_PATH}/${row.id}`,
          }
        })
      })()
    )
  }

  if (scopes.has('blog')) {
    tasks.push(
      (async () => {
        const { data } = await supabase
          .from('blog_posts')
          .select('id, title, slug, excerpt')
          .eq('status', 'published')
          .or(`title.ilike.${like},excerpt.ilike.${like}`)
          .order('published_at', { ascending: false })
          .limit(lim)
        empty.blog = (data ?? []).map(row => ({
          id: row.id,
          title: row.title,
          subtitle: row.excerpt ? row.excerpt.slice(0, 80) : null,
          href: `/blog/${row.slug ?? row.id}`,
        }))
      })()
    )
  }

  if (scopes.has('projects')) {
    tasks.push(
      (async () => {
        const { data } = await supabase
          .from('projects')
          .select('id, title, description, profiles!projects_owner_id_fkey(prenom, nom)')
          .eq('visibility', 'public')
          .or(`title.ilike.${like},description.ilike.${like}`)
          .order('created_at', { ascending: false })
          .limit(lim)
        empty.projects = (data ?? []).map(row => {
          const author = row.profiles as unknown as { prenom: string; nom: string } | null
          return {
            id: row.id,
            title: row.title,
            subtitle: author ? `${author.prenom} ${author.nom}` : null,
            href: `/project/${row.id}`,
          }
        })
      })()
    )
  }

  if (scopes.has('jobs')) {
    tasks.push(
      (async () => {
        const { data } = await supabase
          .from('jobs')
          .select('id, title, company_name, city, type')
          .eq('status', 'active')
          .or(`title.ilike.${like},description.ilike.${like},company_name.ilike.${like}`)
          .order('created_at', { ascending: false })
          .limit(lim)
        empty.jobs = (data ?? []).map(row => ({
          id: row.id,
          title: row.title,
          subtitle: [row.company_name, row.city, row.type].filter(Boolean).join(' · ') || null,
          href: `/job/${row.id}`,
        }))
      })()
    )
  }

  await Promise.all(tasks)
  return empty
}
