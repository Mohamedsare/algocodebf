'use client'

import { useMemo, useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { Badge } from '@/components/ui/badge'
import { Button, buttonVariants } from '@/components/ui/button'
import { cn, formatDateShort, formatNumber } from '@/lib/utils'
import { Edit, ExternalLink, Eye, EyeOff, Plus } from 'lucide-react'
import { setProjectAdminStatusAction } from '@/app/actions/admin'
import { useToast } from '@/components/ui/toast-provider'

export type ProjetAdminRow = {
  id: number
  title: string
  status: string
  visibility: string
  looking_for_members: boolean
  created_at: string
  profiles: { prenom: string; nom: string } | null
}

const STATUS_LABELS: Record<string, string> = {
  active: 'Actif',
  completed: 'Terminé',
  archived: 'Archivé',
  planning: 'Planification',
  in_progress: 'En cours',
  paused: 'En pause',
}

interface Props {
  projects: ProjetAdminRow[]
}

export function ProjetsAdminClient({ projects }: Props) {
  const router = useRouter()
  const toast = useToast()
  const [pending, start] = useTransition()
  const [q, setQ] = useState('')
  const [statusF, setStatusF] = useState<string>('all')
  const [visF, setVisF] = useState<'all' | 'public' | 'private'>('all')
  const [recruitF, setRecruitF] = useState<'all' | 'yes' | 'no'>('all')

  const statusOptions = useMemo(() => {
    const s = new Set<string>()
    for (const p of projects) {
      if (p.status) s.add(p.status)
    }
    return [...s].sort((a, b) => a.localeCompare(b, 'fr'))
  }, [projects])

  const stats = useMemo(() => {
    const archived = projects.filter(p => p.status === 'archived').length
    const publicVis = projects.filter(p => p.visibility === 'public').length
    const recruiting = projects.filter(p => p.looking_for_members).length
    return { total: projects.length, archived, publicVis, recruiting }
  }, [projects])

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase()
    return projects.filter(p => {
      if (statusF !== 'all' && p.status !== statusF) return false
      if (visF !== 'all' && p.visibility !== visF) return false
      if (recruitF === 'yes' && !p.looking_for_members) return false
      if (recruitF === 'no' && p.looking_for_members) return false
      if (!needle) return true
      const title = p.title.toLowerCase()
      const owner = p.profiles ? `${p.profiles.prenom} ${p.profiles.nom}`.toLowerCase() : ''
      return title.includes(needle) || owner.includes(needle)
    })
  }, [projects, q, statusF, visF, recruitF])

  function runArchive(id: number) {
    start(async () => {
      const r = await setProjectAdminStatusAction(id, 'archived')
      if (r.ok) router.refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  function runRestore(id: number) {
    start(async () => {
      const r = await setProjectAdminStatusAction(id, 'active')
      if (r.ok) router.refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  function statusBadge(st: string) {
    if (st === 'archived') return <Badge variant="danger">Archivé</Badge>
    if (st === 'completed') return <Badge variant="outline">Terminé</Badge>
    if (st === 'active') return <Badge variant="success">Actif</Badge>
    return <Badge variant="default">{STATUS_LABELS[st] ?? st}</Badge>
  }

  const canShowPublic = (p: ProjetAdminRow) =>
    p.visibility === 'public' && p.status !== 'archived'

  return (
    <div className="space-y-4">
      <div className="stats-grid-admin">
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-project-diagram" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.total)}</h3>
            <p>Projets</p>
            <span className="stat-trend positive">
              <i className="fas fa-list" aria-hidden /> Catalogue admin
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-globe" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.publicVis)}</h3>
            <p>Visibilité publique</p>
            <span className="stat-trend positive">
              <i className="fas fa-eye" aria-hidden /> Liste / fiche ouverte
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-tutorials">
          <div className="stat-icon-admin">
            <i className="fas fa-user-plus" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.recruiting)}</h3>
            <p>Recrutent</p>
            <span className="stat-trend positive">
              <i className="fas fa-users" aria-hidden /> Cherchent des membres
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-reports">
          <div className="stat-icon-admin">
            <i className="fas fa-box-archive" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.archived)}</h3>
            <p>Archivés</p>
            <span className="stat-trend" style={{ color: 'var(--dark-color, #334155)' }}>
              <i className="fas fa-archive" aria-hidden /> Hors vitrine
            </span>
          </div>
        </div>
      </div>

      <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div className="header-actions admin-users-filters-form flex-wrap">
          <div className="search-box-admin min-w-[200px] flex-1">
            <i className="fas fa-search" aria-hidden />
            <input
              type="search"
              className="filter-select-admin border-0 bg-transparent shadow-none flex-1 min-w-0"
              placeholder="Rechercher par titre ou porteur…"
              value={q}
              onChange={e => setQ(e.target.value)}
              aria-label="Rechercher un projet"
            />
          </div>
          <select
            className="filter-select-admin"
            value={statusF}
            onChange={e => setStatusF(e.target.value)}
            aria-label="Filtrer par statut"
          >
            <option value="all">Tous les statuts</option>
            {statusOptions.map(s => (
              <option key={s} value={s}>
                {STATUS_LABELS[s] ?? s}
              </option>
            ))}
          </select>
          <select
            className="filter-select-admin"
            value={visF}
            onChange={e => setVisF(e.target.value as typeof visF)}
            aria-label="Filtrer par visibilité"
          >
            <option value="all">Toutes visibilités</option>
            <option value="public">Public</option>
            <option value="private">Privé</option>
          </select>
          <select
            className="filter-select-admin"
            value={recruitF}
            onChange={e => setRecruitF(e.target.value as typeof recruitF)}
            aria-label="Recrutement"
          >
            <option value="all">Recrutement : tous</option>
            <option value="yes">Qui recrutent</option>
            <option value="no">Sans recrutement</option>
          </select>
        </div>
        <Link
          href="/project/creer"
          className={cn(
            buttonVariants({ variant: 'primary', size: 'md' }),
            'admin-primary-cta shrink-0 w-fit max-w-full whitespace-nowrap px-6'
          )}
        >
          <Plus size={14} aria-hidden /> Nouveau projet
        </Link>
      </div>

      <p className="text-sm text-gray-600 m-0">
        {filtered.length === projects.length
          ? `${projects.length} projet${projects.length === 1 ? '' : 's'}.`
          : `${filtered.length} sur ${projects.length} projet${projects.length === 1 ? '' : 's'} (filtres actifs).`}
      </p>

      <div className="recent-section p-0 shadow-none bg-transparent">
        <div className="table-responsive">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Projet</th>
                <th className="hidden lg:table-cell">Porteur</th>
                <th className="hidden md:table-cell">Visibilité</th>
                <th className="hidden md:table-cell">Création</th>
                <th>Statut</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center text-gray-500 py-8">
                    Aucun projet ne correspond aux filtres.
                  </td>
                </tr>
              ) : (
                filtered.map(p => (
                  <tr key={p.id}>
                    <td>
                      <Link
                        href={`/project/${p.id}`}
                        className="font-semibold hover:underline text-[var(--dark-color,#0f172a)]"
                      >
                        {p.title}
                      </Link>
                      {p.looking_for_members && (
                        <Badge variant="accent" className="ml-2 align-middle text-[10px]">
                          Recrute
                        </Badge>
                      )}
                    </td>
                    <td className="hidden lg:table-cell text-gray-600">
                      {p.profiles ? `${p.profiles.prenom} ${p.profiles.nom}` : '—'}
                    </td>
                    <td className="hidden md:table-cell">
                      {p.visibility === 'public' ? (
                        <Badge variant="success">Public</Badge>
                      ) : (
                        <Badge variant="outline">Privé</Badge>
                      )}
                    </td>
                    <td className="hidden md:table-cell text-gray-600 whitespace-nowrap">
                      {formatDateShort(p.created_at)}
                    </td>
                    <td>{statusBadge(p.status)}</td>
                    <td className="text-right">
                      <div className="flex justify-end flex-wrap gap-1">
                        <Link href={`/project/${p.id}`} title="Voir la fiche">
                          <Button variant="ghost" size="sm" disabled={!canShowPublic(p)}>
                            <ExternalLink size={14} />
                          </Button>
                        </Link>
                        <Link href={`/project/${p.id}/modifier`} title="Modifier">
                          <Button variant="ghost" size="sm">
                            <Edit size={14} />
                          </Button>
                        </Link>
                        {p.status !== 'archived' ? (
                          <Button
                            variant="danger"
                            size="sm"
                            loading={pending}
                            title="Archiver (retirer de la vitrine)"
                            onClick={() => {
                              if (!window.confirm('Archiver ce projet ?')) return
                              runArchive(p.id)
                            }}
                          >
                            <EyeOff size={14} />
                          </Button>
                        ) : (
                          <Button
                            variant="secondary"
                            size="sm"
                            loading={pending}
                            title="Réactiver (statut actif)"
                            onClick={() => runRestore(p.id)}
                          >
                            <Eye size={14} />
                          </Button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
