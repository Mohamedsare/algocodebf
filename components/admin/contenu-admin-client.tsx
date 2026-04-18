'use client'

import { useMemo, useState } from 'react'
import Link from 'next/link'
import { Badge } from '@/components/ui/badge'
import { ADMIN_CONSOLE_PATH } from '@/lib/routes'
import { formatDateShort, formatNumber } from '@/lib/utils'

export type ContenuPostRow = {
  id: number
  title: string
  category: string | null
  status: string
  created_at: string
  profiles: { prenom: string; nom: string } | null
}

export type ContenuTutorialRow = {
  id: number
  title: string
  category: string | null
  status: string
  created_at: string
  profiles: { prenom: string; nom: string } | null
}

export type ContenuJobRow = {
  id: number
  title: string
  type: string | null
  status: string
  company_name: string | null
  created_at: string
  profiles: { prenom: string; nom: string } | null
}

export type ContenuBlogRow = {
  id: number
  title: string
  slug: string
  category: string | null
  status: string
  created_at: string
  profiles: { prenom: string; nom: string } | null
}

export interface ContenuBundle {
  posts: ContenuPostRow[]
  tutorials: ContenuTutorialRow[]
  jobs: ContenuJobRow[]
  blog: ContenuBlogRow[]
}

type TabId = 'posts' | 'tutorials' | 'jobs' | 'blog'

const TABS: { id: TabId; label: string; icon: string; moderateHref: string }[] = [
  { id: 'posts', label: 'Forum', icon: 'fa-comments', moderateHref: `${ADMIN_CONSOLE_PATH}/forum` },
  { id: 'tutorials', label: 'Formations', icon: 'fa-book-open', moderateHref: `${ADMIN_CONSOLE_PATH}/tutorials` },
  { id: 'jobs', label: 'Opportunités', icon: 'fa-briefcase', moderateHref: `${ADMIN_CONSOLE_PATH}/jobs` },
  { id: 'blog', label: 'Blog', icon: 'fa-blog', moderateHref: `${ADMIN_CONSOLE_PATH}/blog` },
]

function normProfile(
  p: { prenom: string; nom: string } | { prenom: string; nom: string }[] | null | undefined
): { prenom: string; nom: string } | null {
  if (!p) return null
  return Array.isArray(p) ? p[0] ?? null : p
}

interface Props {
  data: ContenuBundle
}

export function ContenuAdminClient({ data: raw }: Props) {
  const data = useMemo(
    () => ({
      posts: raw.posts.map(r => ({ ...r, profiles: normProfile(r.profiles) })),
      tutorials: raw.tutorials.map(r => ({ ...r, profiles: normProfile(r.profiles) })),
      jobs: raw.jobs.map(r => ({ ...r, profiles: normProfile(r.profiles) })),
      blog: raw.blog.map(r => ({ ...r, profiles: normProfile(r.profiles) })),
    }),
    [raw]
  )

  const [tab, setTab] = useState<TabId>('posts')
  const [q, setQ] = useState('')
  const [statusF, setStatusF] = useState<string>('all')

  const list = data[tab]

  const statusOptions = useMemo(() => {
    const s = new Set<string>()
    for (const r of list) {
      if (r.status) s.add(r.status)
    }
    return [...s].sort((a, b) => a.localeCompare(b, 'fr'))
  }, [list])

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase()
    return list.filter(r => {
      if (statusF !== 'all' && r.status !== statusF) return false
      if (!needle) return true
      const title = r.title.toLowerCase()
      const author = r.profiles ? `${r.profiles.prenom} ${r.profiles.nom}`.toLowerCase() : ''
      const extra =
        tab === 'jobs'
          ? `${(r as ContenuJobRow).company_name ?? ''} ${(r as ContenuJobRow).type ?? ''}`.toLowerCase()
          : ''
      return title.includes(needle) || author.includes(needle) || extra.includes(needle)
    })
  }, [list, q, statusF, tab])

  const stats = useMemo(() => {
    const total = list.length
    const activeLike = list.filter(r =>
      ['active', 'published'].includes(String(r.status).toLowerCase())
    ).length
    return { total, activeLike }
  }, [list])

  const tabMeta = TABS.find(t => t.id === tab)!

  function publicHref(row: ContenuPostRow | ContenuTutorialRow | ContenuJobRow | ContenuBlogRow): string {
    switch (tab) {
      case 'posts':
        return `/forum/${(row as ContenuPostRow).id}`
      case 'tutorials':
        return `/formations/${(row as ContenuTutorialRow).id}`
      case 'jobs':
        return `/job/${(row as ContenuJobRow).id}`
      case 'blog':
        return `/blog/${(row as ContenuBlogRow).slug}`
      default:
        return '/'
    }
  }

  function statusBadge(st: string) {
    const s = st.toLowerCase()
    if (s === 'active' || s === 'published') return <Badge variant="success">{st}</Badge>
    if (s === 'inactive' || s === 'archived' || s === 'closed' || s === 'expired')
      return <Badge variant="danger">{st}</Badge>
    if (s === 'draft' || s === 'pending') return <Badge variant="warning">{st}</Badge>
    return <Badge variant="outline">{st}</Badge>
  }

  return (
    <div className="space-y-4">
      <div className="stats-grid-admin">
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-database" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.total)}</h3>
            <p>Entrées ({tabMeta.label})</p>
            <span className="stat-trend positive">
              <i className="fas fa-list" aria-hidden /> Onglet courant
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-check" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.activeLike)}</h3>
            <p>Actifs / publiés</p>
            <span className="stat-trend positive">Estimation rapide</span>
          </div>
        </div>
        <div className="stat-card-admin card-tutorials">
          <div className="stat-icon-admin">
            <i className="fas fa-sliders" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(filtered.length)}</h3>
            <p>Après filtres</p>
            <span className="stat-trend">Recherche + statut</span>
          </div>
        </div>
      </div>

      <div className="forum-tabs">
        {TABS.map(t => (
          <button
            key={t.id}
            type="button"
            className={`forum-tab-btn${tab === t.id ? ' active' : ''}`}
            onClick={() => {
              setTab(t.id)
              setStatusF('all')
              setQ('')
            }}
          >
            <i className={`fas ${t.icon}`} aria-hidden /> {t.label}
          </button>
        ))}
      </div>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between flex-wrap">
        <div className="header-actions admin-users-filters-form flex-wrap flex-1">
          <div className="search-box-admin min-w-[200px] flex-1">
            <i className="fas fa-search" aria-hidden />
            <input
              type="search"
              className="filter-select-admin border-0 bg-transparent shadow-none flex-1 min-w-0"
              placeholder="Titre, auteur…"
              value={q}
              onChange={e => setQ(e.target.value)}
              aria-label="Rechercher"
            />
          </div>
          <select
            className="filter-select-admin"
            value={statusF}
            onChange={e => setStatusF(e.target.value)}
            aria-label="Statut"
          >
            <option value="all">Tous les statuts</option>
            {statusOptions.map(s => (
              <option key={s} value={s}>
                {s}
              </option>
            ))}
          </select>
        </div>
        <Link
          href={tabMeta.moderateHref}
          className="text-sm font-semibold text-[#C8102E] hover:underline whitespace-nowrap"
        >
          Modération détaillée →
        </Link>
      </div>

      <div className="recent-section p-0 shadow-none bg-transparent">
        <div className="table-responsive">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Titre</th>
                <th className="hidden md:table-cell">
                  {tab === 'jobs' ? 'Type / société' : 'Catégorie'}
                </th>
                <th className="hidden lg:table-cell">Auteur / contact</th>
                <th>Statut</th>
                <th className="hidden md:table-cell">Date</th>
                <th className="text-right"></th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center text-gray-500 py-8">
                    Aucune ligne ne correspond aux filtres.
                  </td>
                </tr>
              ) : (
                filtered.map(row => {
                  const author = row.profiles ? `${row.profiles.prenom} ${row.profiles.nom}` : '—'
                  const mid =
                    tab === 'jobs' ? (
                      <span className="text-sm text-gray-600">
                        {(row as ContenuJobRow).type ?? '—'}
                        {(row as ContenuJobRow).company_name && (
                          <span className="block text-xs text-gray-500">
                            {(row as ContenuJobRow).company_name}
                          </span>
                        )}
                      </span>
                    ) : (
                      <span className="text-sm">
                        {(row as ContenuPostRow | ContenuTutorialRow | ContenuBlogRow).category ?? '—'}
                      </span>
                    )
                  return (
                    <tr key={`${tab}-${row.id}`}>
                      <td className="font-medium max-w-[220px]">{row.title}</td>
                      <td className="hidden md:table-cell">{mid}</td>
                      <td className="hidden lg:table-cell text-sm text-gray-600">{author}</td>
                      <td>{statusBadge(row.status)}</td>
                      <td className="hidden md:table-cell text-sm text-gray-600 whitespace-nowrap">
                        {formatDateShort(row.created_at)}
                      </td>
                      <td className="text-right">
                        <Link
                          href={publicHref(row)}
                          className="btn-view-all text-xs py-1 px-2 inline-block"
                          target="_blank"
                          rel="noopener noreferrer"
                        >
                          Voir
                        </Link>
                      </td>
                    </tr>
                  )
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
