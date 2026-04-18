'use client'

import { useMemo, useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { Badge } from '@/components/ui/badge'
import { Button, buttonVariants } from '@/components/ui/button'
import { cn, formatDateShort, formatNumber } from '@/lib/utils'
import { Edit, ExternalLink, Eye, EyeOff, Plus } from 'lucide-react'
import { setJobStatusAction } from '@/app/actions/admin'
import { useToast } from '@/components/ui/toast-provider'

export type OpportuniteAdminRow = {
  id: number
  title: string
  type: string
  city: string | null
  status: string
  views: number
  created_at: string
  company_name: string | null
  profiles: { prenom: string; nom: string } | null
}

const TYPE_LABELS: Record<string, string> = {
  job: 'Emploi',
  internship: 'Stage',
  hackathon: 'Hackathon',
  stage: 'Stage',
  emploi: 'Emploi',
  freelance: 'Freelance',
  formation: 'Formation liée',
}

interface Props {
  jobs: OpportuniteAdminRow[]
}

export function OpportunitesAdminClient({ jobs }: Props) {
  const router = useRouter()
  const toast = useToast()
  const [pending, start] = useTransition()
  const [q, setQ] = useState('')
  const [statusF, setStatusF] = useState<'all' | 'active' | 'closed' | 'expired'>('all')
  const [typeF, setTypeF] = useState<string>('all')
  const [cityF, setCityF] = useState<string>('all')

  const types = useMemo(() => {
    const s = new Set<string>()
    for (const j of jobs) {
      if (j.type) s.add(j.type)
    }
    return [...s].sort((a, b) => a.localeCompare(b, 'fr'))
  }, [jobs])

  const cities = useMemo(() => {
    const s = new Set<string>()
    for (const j of jobs) {
      const c = j.city?.trim()
      if (c) s.add(c)
    }
    return [...s].sort((a, b) => a.localeCompare(b, 'fr'))
  }, [jobs])

  const stats = useMemo(() => {
    const active = jobs.filter(j => j.status === 'active').length
    const closed = jobs.filter(j => j.status === 'closed').length
    const expired = jobs.filter(j => j.status === 'expired').length
    const views = jobs.reduce((acc, j) => acc + (j.views ?? 0), 0)
    return { total: jobs.length, active, closed, expired, views }
  }, [jobs])

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase()
    return jobs.filter(j => {
      if (statusF !== 'all' && j.status !== statusF) return false
      if (typeF !== 'all' && j.type !== typeF) return false
      if (cityF !== 'all' && (j.city ?? '').trim() !== cityF) return false
      if (!needle) return true
      const title = j.title.toLowerCase()
      const company = (j.company_name ?? '').toLowerCase()
      const contact = j.profiles ? `${j.profiles.prenom} ${j.profiles.nom}`.toLowerCase() : ''
      return title.includes(needle) || company.includes(needle) || contact.includes(needle)
    })
  }, [jobs, q, statusF, typeF, cityF])

  function runClose(id: number) {
    start(async () => {
      const r = await setJobStatusAction(id, 'closed')
      if (r.ok) router.refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  function runPublish(id: number) {
    start(async () => {
      const r = await setJobStatusAction(id, 'active')
      if (r.ok) router.refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  function statusBadge(st: string) {
    if (st === 'active') return <Badge variant="success">Publiée</Badge>
    if (st === 'closed') return <Badge variant="danger">Fermée</Badge>
    if (st === 'expired') return <Badge variant="outline">Expirée</Badge>
    return <Badge variant="default">{st}</Badge>
  }

  return (
    <div className="space-y-4">
      <div className="stats-grid-admin">
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-briefcase" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.total)}</h3>
            <p>Offres</p>
            <span className="stat-trend positive">
              <i className="fas fa-list" aria-hidden /> Échantillon chargé
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-check" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.active)}</h3>
            <p>En ligne</p>
            <span className="stat-trend positive">
              <i className="fas fa-globe" aria-hidden /> Catalogue public
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-reports">
          <div className="stat-icon-admin">
            <i className="fas fa-lock" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.closed + stats.expired)}</h3>
            <p>Hors catalogue</p>
            <span className="stat-trend" style={{ color: 'var(--dark-color, #334155)' }}>
              <i className="fas fa-pause" aria-hidden /> Fermées / expirées
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-chart-line" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.views)}</h3>
            <p>Vues cumulées</p>
            <span className="stat-trend positive">
              <i className="fas fa-eye" aria-hidden /> Sur cet échantillon
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
              placeholder="Rechercher par titre, société…"
              value={q}
              onChange={e => setQ(e.target.value)}
              aria-label="Rechercher une offre"
            />
          </div>
          <select
            className="filter-select-admin"
            value={statusF}
            onChange={e => setStatusF(e.target.value as typeof statusF)}
            aria-label="Filtrer par statut"
          >
            <option value="all">Tous les statuts</option>
            <option value="active">Publiée</option>
            <option value="closed">Fermée</option>
            <option value="expired">Expirée</option>
          </select>
          <select
            className="filter-select-admin"
            value={typeF}
            onChange={e => setTypeF(e.target.value)}
            aria-label="Filtrer par type"
          >
            <option value="all">Tous les types</option>
            {types.map(t => (
              <option key={t} value={t}>
                {TYPE_LABELS[t] ?? t}
              </option>
            ))}
          </select>
          <select
            className="filter-select-admin"
            value={cityF}
            onChange={e => setCityF(e.target.value)}
            aria-label="Filtrer par ville"
          >
            <option value="all">Toutes les villes</option>
            {cities.map(c => (
              <option key={c} value={c}>
                {c}
              </option>
            ))}
          </select>
        </div>
        <Link
          href="/job/creer"
          className={cn(
            buttonVariants({ variant: 'primary', size: 'md' }),
            'admin-primary-cta shrink-0 w-fit max-w-full whitespace-nowrap px-6'
          )}
        >
          <Plus size={14} aria-hidden /> Nouvelle offre
        </Link>
      </div>

      <p className="text-sm text-gray-600 m-0">
        {filtered.length === jobs.length
          ? `${jobs.length} offre${jobs.length === 1 ? '' : 's'}.`
          : `${filtered.length} sur ${jobs.length} offre${jobs.length === 1 ? '' : 's'} (filtres actifs).`}
      </p>

      <div className="recent-section p-0 shadow-none bg-transparent">
        <div className="table-responsive">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Offre</th>
                <th className="hidden lg:table-cell">Société</th>
                <th className="hidden md:table-cell">Type / lieu</th>
                <th className="hidden md:table-cell">Création</th>
                <th>Statut</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center text-gray-500 py-8">
                    Aucune offre ne correspond aux filtres.
                  </td>
                </tr>
              ) : (
                filtered.map(j => (
                  <tr key={j.id}>
                    <td>
                      <Link
                        href={`/job/${j.id}`}
                        className="font-semibold hover:underline text-[var(--dark-color,#0f172a)]"
                      >
                        {j.title}
                      </Link>
                      <div className="text-xs text-gray-500">{formatNumber(j.views ?? 0)} vues</div>
                    </td>
                    <td className="hidden lg:table-cell text-gray-600">
                      {j.company_name ?? (j.profiles ? `${j.profiles.prenom} ${j.profiles.nom}` : '—')}
                    </td>
                    <td className="hidden md:table-cell">
                      <Badge variant="outline">{TYPE_LABELS[j.type] ?? j.type}</Badge>
                      {j.city && <span className="text-xs text-gray-500 ml-2">{j.city}</span>}
                    </td>
                    <td className="hidden md:table-cell text-gray-600 whitespace-nowrap">
                      {formatDateShort(j.created_at)}
                    </td>
                    <td>{statusBadge(j.status)}</td>
                    <td className="text-right">
                      <div className="flex justify-end flex-wrap gap-1">
                        <Link href={`/job/${j.id}`} title="Voir sur le site">
                          <Button variant="ghost" size="sm" disabled={j.status !== 'active'}>
                            <ExternalLink size={14} />
                          </Button>
                        </Link>
                        <Link href={`/job/${j.id}/modifier`} title="Modifier">
                          <Button variant="ghost" size="sm">
                            <Edit size={14} />
                          </Button>
                        </Link>
                        {j.status === 'active' ? (
                          <Button
                            variant="danger"
                            size="sm"
                            loading={pending}
                            title="Fermer l’offre (hors catalogue)"
                            onClick={() => {
                              if (!window.confirm('Fermer cette offre ? Elle disparaîtra du catalogue public.')) return
                              runClose(j.id)
                            }}
                          >
                            <EyeOff size={14} />
                          </Button>
                        ) : (
                          <Button
                            variant="secondary"
                            size="sm"
                            loading={pending}
                            title="Republiquer"
                            onClick={() => runPublish(j.id)}
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
