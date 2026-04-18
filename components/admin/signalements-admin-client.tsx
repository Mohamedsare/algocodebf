'use client'

import { useMemo, useState } from 'react'
import { formatNumber } from '@/lib/utils'
import { ReportRow } from '@/components/admin/report-row'
import type { ReportStatus, ReportableType } from '@/types'

export type SignalementRow = {
  id: number
  reportable_type: ReportableType
  reportable_id: number | null
  reportable_uuid: string | null
  reason: string
  details: string | null
  status: ReportStatus
  created_at: string
  profiles: { prenom: string; nom: string } | null
  contentHref: string
  targetLabel: string
}

interface Props {
  reports: SignalementRow[]
}

const TYPE_LABELS: Record<string, string> = {
  post: 'Forum',
  comment: 'Commentaire',
  blog: 'Blog',
  tutorial: 'Formation',
  project: 'Projet',
  user: 'Profil',
}

export function SignalementsAdminClient({ reports }: Props) {
  const [q, setQ] = useState('')
  const [statusF, setStatusF] = useState<'all' | ReportStatus>('all')
  const [typeF, setTypeF] = useState<string>('all')

  const stats = useMemo(() => {
    const pending = reports.filter(r => r.status === 'pending').length
    const resolved = reports.filter(r => r.status === 'resolved').length
    const dismissed = reports.filter(r => r.status === 'dismissed').length
    const reviewed = reports.filter(r => r.status === 'reviewed').length
    return { total: reports.length, pending, resolved, dismissed, reviewed }
  }, [reports])

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase()
    return reports.filter(r => {
      if (statusF !== 'all' && r.status !== statusF) return false
      if (typeF !== 'all' && r.reportable_type !== typeF) return false
      if (!needle) return true
      const reason = r.reason.toLowerCase()
      const details = (r.details ?? '').toLowerCase()
      const reporter = r.profiles
        ? `${r.profiles.prenom} ${r.profiles.nom}`.toLowerCase()
        : ''
      return reason.includes(needle) || details.includes(needle) || reporter.includes(needle)
    })
  }, [reports, q, statusF, typeF])

  const pendingList = filtered.filter(r => r.status === 'pending')
  const doneList = filtered.filter(r => r.status !== 'pending')

  return (
    <div className="space-y-4">
      <div className="stats-grid-admin">
        <div className="stat-card-admin card-reports">
          <div className="stat-icon-admin">
            <i className="fas fa-flag" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.pending)}</h3>
            <p>En attente</p>
            <span className="stat-trend warning">
              <i className="fas fa-clock" aria-hidden /> Action requise
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-check" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.resolved)}</h3>
            <p>Résolus</p>
            <span className="stat-trend positive">
              <i className="fas fa-check-circle" aria-hidden /> Traités
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-ban" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.dismissed)}</h3>
            <p>Rejetés</p>
            <span className="stat-trend" style={{ color: 'var(--dark-color, #334155)' }}>
              <i className="fas fa-times" aria-hidden /> Sans suite
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-tutorials">
          <div className="stat-icon-admin">
            <i className="fas fa-list" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(stats.total)}</h3>
            <p>Total chargé</p>
            <span className="stat-trend">
              <i className="fas fa-database" aria-hidden /> Échantillon
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
            placeholder="Motif, détail, signaleur…"
            value={q}
            onChange={e => setQ(e.target.value)}
            aria-label="Rechercher un signalement"
          />
        </div>
        <select
          className="filter-select-admin"
          value={statusF}
          onChange={e => setStatusF(e.target.value as typeof statusF)}
          aria-label="Statut"
        >
          <option value="all">Tous les statuts</option>
          <option value="pending">En attente</option>
          <option value="resolved">Résolu</option>
          <option value="dismissed">Rejeté</option>
          <option value="reviewed">Examiné</option>
        </select>
        <select
          className="filter-select-admin"
          value={typeF}
          onChange={e => setTypeF(e.target.value)}
          aria-label="Type de cible"
        >
          <option value="all">Tous les types</option>
          {(Object.keys(TYPE_LABELS) as ReportableType[]).map(t => (
            <option key={t} value={t}>
              {TYPE_LABELS[t]}
            </option>
          ))}
        </select>
      </div>

      <p className="text-sm text-gray-600 m-0">
        {filtered.length === reports.length
          ? `${reports.length} signalement${reports.length === 1 ? '' : 's'}.`
          : `${filtered.length} sur ${reports.length} (filtres actifs).`}
      </p>

      <section>
        <h2 className="text-sm font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-3">
          En attente ({pendingList.length})
        </h2>
        {pendingList.length === 0 ? (
          <p className="text-sm text-gray-500">Aucun signalement en attente pour ces filtres.</p>
        ) : (
          <ul className="space-y-3 list-none m-0 p-0">
            {pendingList.map(r => (
              <ReportRow
                key={r.id}
                id={r.id}
                reportableType={r.reportable_type}
                reportableId={r.reportable_id}
                contentHref={r.contentHref}
                targetLabel={r.targetLabel}
                reason={r.reason}
                details={r.details}
                status={r.status}
                createdAt={r.created_at}
                reporter={r.profiles}
              />
            ))}
          </ul>
        )}
      </section>

      {doneList.length > 0 && (
        <section>
          <h2 className="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">
            Historique ({doneList.length})
          </h2>
          <ul className="space-y-3 list-none m-0 p-0">
            {doneList.map(r => (
              <ReportRow
                key={r.id}
                id={r.id}
                reportableType={r.reportable_type}
                reportableId={r.reportable_id}
                contentHref={r.contentHref}
                targetLabel={r.targetLabel}
                reason={r.reason}
                details={r.details}
                status={r.status}
                createdAt={r.created_at}
                reporter={r.profiles}
              />
            ))}
          </ul>
        </section>
      )}
    </div>
  )
}
