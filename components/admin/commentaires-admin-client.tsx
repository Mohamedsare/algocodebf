'use client'

import { useMemo, useState } from 'react'
import Link from 'next/link'
import { Badge } from '@/components/ui/badge'
import { formatDateShort, formatNumber } from '@/lib/utils'
import type { CommentableType } from '@/types'
import { CommentModerationActions } from '@/components/admin/comment-moderation-actions'

export type CommentaireAdminRow = {
  id: number
  body: string
  status: string
  commentable_type: CommentableType
  commentable_id: number
  created_at: string
  profiles: { prenom: string | null; nom: string | null; id: string } | null
  blogSlug: string | null
}

function targetHref(c: CommentaireAdminRow): string {
  switch (c.commentable_type) {
    case 'post':
      return `/forum/${c.commentable_id}`
    case 'tutorial':
      return `/formations/${c.commentable_id}`
    case 'blog':
      return c.blogSlug ? `/blog/${c.blogSlug}` : '/blog'
    case 'project':
      return `/project/${c.commentable_id}`
    default:
      return '/'
  }
}

function typeLabel(t: string): string {
  switch (t) {
    case 'post':
      return 'Forum'
    case 'tutorial':
      return 'Formation'
    case 'blog':
      return 'Blog'
    case 'project':
      return 'Projet'
    default:
      return t
  }
}

interface Props {
  comments: CommentaireAdminRow[]
}

export function CommentairesAdminClient({ comments }: Props) {
  const [q, setQ] = useState('')
  const [typeF, setTypeF] = useState<string>('all')
  const [statusF, setStatusF] = useState<'all' | 'active' | 'deleted'>('all')

  const stats = useMemo(() => {
    const active = comments.filter(c => c.status === 'active').length
    const deleted = comments.filter(c => c.status === 'deleted').length
    return { total: comments.length, active, deleted }
  }, [comments])

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase()
    return comments.filter(c => {
      if (typeF !== 'all' && c.commentable_type !== typeF) return false
      if (statusF !== 'all' && c.status !== statusF) return false
      if (!needle) return true
      const body = c.body.toLowerCase()
      const author = c.profiles
        ? `${c.profiles.prenom ?? ''} ${c.profiles.nom ?? ''}`.trim().toLowerCase()
        : ''
      return body.includes(needle) || author.includes(needle)
    })
  }, [comments, q, typeF, statusF])

  return (
    <div className="space-y-4">
      <div className="stats-grid-admin">
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-comment-dots" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.total)}</h3>
            <p>Commentaires</p>
            <span className="stat-trend positive">
              <i className="fas fa-list" aria-hidden /> Échantillon chargé
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-check" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.active)}</h3>
            <p>Visibles</p>
            <span className="stat-trend positive">
              <i className="fas fa-eye" aria-hidden /> Actifs
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-reports">
          <div className="stat-icon-admin">
            <i className="fas fa-eye-slash" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.deleted)}</h3>
            <p>Masqués</p>
            <span className="stat-trend" style={{ color: 'var(--dark-color, #334155)' }}>
              <i className="fas fa-ban" aria-hidden /> Modération
            </span>
          </div>
        </div>
      </div>

      <div className="header-actions admin-users-filters-form flex-wrap">
        <div className="search-box-admin min-w-[200px] flex-1">
          <i className="fas fa-search" aria-hidden />
          <input
            type="search"
            className="filter-select-admin border-0 bg-transparent shadow-none flex-1 min-w-0"
            placeholder="Rechercher dans le texte ou l’auteur…"
            value={q}
            onChange={e => setQ(e.target.value)}
            aria-label="Rechercher un commentaire"
          />
        </div>
        <select
          className="filter-select-admin"
          value={typeF}
          onChange={e => setTypeF(e.target.value)}
          aria-label="Type de contenu"
        >
          <option value="all">Tous les types</option>
          <option value="post">Forum</option>
          <option value="tutorial">Formations</option>
          <option value="blog">Blog</option>
          <option value="project">Projets</option>
        </select>
        <select
          className="filter-select-admin"
          value={statusF}
          onChange={e => setStatusF(e.target.value as typeof statusF)}
          aria-label="Statut"
        >
          <option value="all">Tous les statuts</option>
          <option value="active">Actifs</option>
          <option value="deleted">Masqués</option>
        </select>
      </div>

      <p className="text-sm text-gray-600 m-0">
        {filtered.length === comments.length
          ? `${comments.length} commentaire${comments.length === 1 ? '' : 's'}.`
          : `${filtered.length} sur ${comments.length} commentaire${comments.length === 1 ? '' : 's'} (filtres actifs).`}
      </p>

      <div className="recent-section p-0 shadow-none bg-transparent">
        <div className="table-responsive">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Auteur</th>
                <th>Contenu</th>
                <th>Cible</th>
                <th className="hidden md:table-cell">Date</th>
                <th>Statut</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center text-gray-500 py-8">
                    Aucun commentaire ne correspond aux filtres.
                  </td>
                </tr>
              ) : (
                filtered.map(c => {
                  const author = c.profiles
                  const name =
                    `${author?.prenom ?? ''} ${author?.nom ?? ''}`.trim() || 'Utilisateur'
                  const excerpt =
                    c.body.length > 140 ? `${c.body.slice(0, 140).trim()}…` : c.body
                  const href = targetHref(c)
                  return (
                    <tr key={c.id}>
                      <td>
                        {author?.id ? (
                          <Link href={`/user/${author.id}`} className="font-semibold hover:underline">
                            {name}
                          </Link>
                        ) : (
                          <span className="text-gray-500">—</span>
                        )}
                      </td>
                      <td className="max-w-[min(320px,40vw)]">
                        <span className="text-gray-700 text-sm">{excerpt}</span>
                      </td>
                      <td>
                        <span className="text-xs font-semibold text-gray-600">
                          {typeLabel(c.commentable_type)}
                        </span>
                        <div>
                          <Link href={href} className="text-sm hover:underline text-[#C8102E]">
                            Voir la cible
                          </Link>
                        </div>
                      </td>
                      <td className="hidden md:table-cell text-gray-600 whitespace-nowrap text-sm">
                        {formatDateShort(c.created_at)}
                      </td>
                      <td>
                        {c.status === 'active' ? (
                          <Badge variant="success">Actif</Badge>
                        ) : (
                          <Badge variant="danger">Masqué</Badge>
                        )}
                      </td>
                      <td className="text-right">
                        <CommentModerationActions commentId={c.id} status={c.status} />
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
