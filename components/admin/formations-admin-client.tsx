'use client'

import { useMemo, useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { Badge } from '@/components/ui/badge'
import { Button, buttonVariants } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import { Edit, ExternalLink, Eye, EyeOff, Plus } from 'lucide-react'
import { setTutorialStatusAction } from '@/app/actions/admin'
import { FORMATIONS_PATH } from '@/lib/routes'
import { useToast } from '@/components/ui/toast-provider'
import { formatDateShort, formatNumber } from '@/lib/utils'

export type FormationAdminRow = {
  id: number
  title: string
  category: string | null
  type: 'video' | 'text' | 'mixed'
  level: 'beginner' | 'intermediate' | 'advanced'
  status: 'active' | 'inactive'
  views: number
  created_at: string
  profiles: { prenom: string; nom: string } | null
}

const TYPE_LABELS: Record<FormationAdminRow['type'], string> = {
  video: 'Vidéo',
  text: 'Texte',
  mixed: 'Mixte',
}

const LEVEL_LABELS: Record<FormationAdminRow['level'], string> = {
  beginner: 'Débutant',
  intermediate: 'Intermédiaire',
  advanced: 'Avancé',
}

interface Props {
  tutorials: FormationAdminRow[]
}

export function FormationsAdminClient({ tutorials }: Props) {
  const router = useRouter()
  const toast = useToast()
  const [pending, start] = useTransition()
  const [q, setQ] = useState('')
  const [status, setStatus] = useState<'all' | 'active' | 'inactive'>('all')
  const [typeF, setTypeF] = useState<'all' | FormationAdminRow['type']>('all')
  const [levelF, setLevelF] = useState<'all' | FormationAdminRow['level']>('all')
  const [categoryF, setCategoryF] = useState<string>('all')

  const categories = useMemo(() => {
    const s = new Set<string>()
    for (const t of tutorials) {
      if (t.category?.trim()) s.add(t.category.trim())
    }
    return [...s].sort((a, b) => a.localeCompare(b, 'fr'))
  }, [tutorials])

  const stats = useMemo(() => {
    const active = tutorials.filter(t => t.status === 'active').length
    const inactive = tutorials.length - active
    const views = tutorials.reduce((acc, t) => acc + (t.views ?? 0), 0)
    return { total: tutorials.length, active, inactive, views }
  }, [tutorials])

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase()
    return tutorials.filter(t => {
      if (status !== 'all' && t.status !== status) return false
      if (typeF !== 'all' && t.type !== typeF) return false
      if (levelF !== 'all' && t.level !== levelF) return false
      if (categoryF !== 'all' && (t.category ?? '').trim() !== categoryF) return false
      if (!needle) return true
      const title = t.title.toLowerCase()
      const author = t.profiles ? `${t.profiles.prenom} ${t.profiles.nom}`.toLowerCase() : ''
      return title.includes(needle) || author.includes(needle)
    })
  }, [tutorials, q, status, typeF, levelF, categoryF])

  function runStatus(id: number, next: 'active' | 'inactive') {
    start(async () => {
      const r = await setTutorialStatusAction(id, next)
      if (r.ok) router.refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  return (
    <div className="space-y-4">
      <div className="stats-grid-admin">
        <div className="stat-card-admin card-tutorials">
          <div className="stat-icon-admin">
            <i className="fas fa-book-open" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.total)}</h3>
            <p>Formations listées</p>
            <span className="stat-trend positive">
              <i className="fas fa-list" aria-hidden /> Catalogue admin
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-eye" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.active)}</h3>
            <p>Publiées</p>
            <span className="stat-trend positive">
              <i className="fas fa-check" aria-hidden /> Visibles sur le site
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-reports">
          <div className="stat-icon-admin">
            <i className="fas fa-eye-slash" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.inactive)}</h3>
            <p>Masquées</p>
            <span className="stat-trend" style={{ color: 'var(--dark-color, #334155)' }}>
              <i className="fas fa-pause" aria-hidden /> Hors catalogue public
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
              <i className="fas fa-play" aria-hidden /> Sur cet échantillon
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
              placeholder="Rechercher par titre ou auteur…"
              value={q}
              onChange={e => setQ(e.target.value)}
              aria-label="Rechercher une formation"
            />
          </div>
          <select
            className="filter-select-admin"
            value={status}
            onChange={e => setStatus(e.target.value as typeof status)}
            aria-label="Filtrer par statut"
          >
            <option value="all">Tous les statuts</option>
            <option value="active">Publiée</option>
            <option value="inactive">Masquée</option>
          </select>
          <select
            className="filter-select-admin"
            value={typeF}
            onChange={e => setTypeF(e.target.value as typeof typeF)}
            aria-label="Filtrer par type"
          >
            <option value="all">Tous les types</option>
            <option value="video">Vidéo</option>
            <option value="text">Texte</option>
            <option value="mixed">Mixte</option>
          </select>
          <select
            className="filter-select-admin"
            value={levelF}
            onChange={e => setLevelF(e.target.value as typeof levelF)}
            aria-label="Filtrer par niveau"
          >
            <option value="all">Tous les niveaux</option>
            <option value="beginner">Débutant</option>
            <option value="intermediate">Intermédiaire</option>
            <option value="advanced">Avancé</option>
          </select>
          <select
            className="filter-select-admin"
            value={categoryF}
            onChange={e => setCategoryF(e.target.value)}
            aria-label="Filtrer par catégorie"
          >
            <option value="all">Toutes les catégories</option>
            {categories.map(c => (
              <option key={c} value={c}>
                {c}
              </option>
            ))}
          </select>
        </div>
        <Link
          href={`${FORMATIONS_PATH}/creer`}
          className={cn(
            buttonVariants({ variant: 'primary', size: 'md' }),
            'admin-primary-cta shrink-0 w-fit max-w-full whitespace-nowrap px-6'
          )}
        >
          <Plus size={14} aria-hidden /> Nouvelle formation
        </Link>
      </div>

      <p className="text-sm text-gray-600 m-0">
        {filtered.length === tutorials.length
          ? `${tutorials.length} formation${tutorials.length === 1 ? '' : 's'}.`
          : `${filtered.length} sur ${tutorials.length} formation${tutorials.length === 1 ? '' : 's'} (filtres actifs).`}
      </p>

      <div className="recent-section p-0 shadow-none bg-transparent">
        <div className="table-responsive">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Formation</th>
                <th className="hidden lg:table-cell">Auteur</th>
                <th className="hidden md:table-cell">Type / niveau</th>
                <th className="hidden md:table-cell">Création</th>
                <th>Statut</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center text-gray-500 py-8">
                    Aucune formation ne correspond aux filtres.
                  </td>
                </tr>
              ) : (
                filtered.map(t => (
                  <tr key={t.id}>
                    <td>
                      <Link
                        href={`${FORMATIONS_PATH}/${t.id}`}
                        className="font-semibold hover:underline text-[var(--dark-color,#0f172a)]"
                      >
                        {t.title}
                      </Link>
                      <div className="text-xs text-gray-500">
                        {t.category ?? '—'} · {formatNumber(t.views ?? 0)} vues
                      </div>
                    </td>
                    <td className="hidden lg:table-cell text-gray-600">
                      {t.profiles ? `${t.profiles.prenom} ${t.profiles.nom}` : '—'}
                    </td>
                    <td className="hidden md:table-cell">
                      <Badge variant="outline">{TYPE_LABELS[t.type]}</Badge>{' '}
                      <Badge variant="default">{LEVEL_LABELS[t.level]}</Badge>
                    </td>
                    <td className="hidden md:table-cell text-gray-600 whitespace-nowrap">
                      {formatDateShort(t.created_at)}
                    </td>
                    <td>
                      {t.status === 'active' ? (
                        <Badge variant="success">Publiée</Badge>
                      ) : (
                        <Badge variant="danger">Masquée</Badge>
                      )}
                    </td>
                    <td className="text-right">
                      <div className="flex justify-end flex-wrap gap-1">
                        <Link href={`${FORMATIONS_PATH}/${t.id}`} title="Voir sur le site">
                          <Button variant="ghost" size="sm" disabled={t.status !== 'active'}>
                            <ExternalLink size={14} />
                          </Button>
                        </Link>
                        <Link href={`${FORMATIONS_PATH}/${t.id}/modifier`} title="Modifier">
                          <Button variant="ghost" size="sm">
                            <Edit size={14} />
                          </Button>
                        </Link>
                        {t.status === 'active' ? (
                          <Button
                            variant="danger"
                            size="sm"
                            loading={pending}
                            title="Masquer du catalogue"
                            onClick={() => {
                              if (!window.confirm('Masquer cette formation du catalogue public ?')) return
                              runStatus(t.id, 'inactive')
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
                            onClick={() => runStatus(t.id, 'active')}
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
