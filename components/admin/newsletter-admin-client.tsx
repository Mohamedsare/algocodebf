'use client'

import { useMemo, useState } from 'react'
import { cn, formatDateShort, formatNumber } from '@/lib/utils'
import { Badge } from '@/components/ui/badge'
import { buttonVariants } from '@/components/ui/button'

export type NewsletterSubscriberRow = {
  id: number
  email: string
  status: string
  subscribed_at: string
  unsubscribed_at: string | null
  total_sent: number | null
}

interface Props {
  subscribers: NewsletterSubscriberRow[]
  counts: {
    active: number
    unsubscribed: number
    bounced: number
  }
  exportUrl: string
}

export function NewsletterAdminClient({ subscribers, counts, exportUrl }: Props) {
  const [q, setQ] = useState('')
  const [statusF, setStatusF] = useState<'all' | 'active' | 'unsubscribed' | 'bounced'>('all')

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase()
    return subscribers.filter(s => {
      if (statusF !== 'all' && s.status !== statusF) return false
      if (!needle) return true
      return s.email.toLowerCase().includes(needle)
    })
  }, [subscribers, q, statusF])

  const totalListed = subscribers.length

  return (
    <div className="space-y-4">
      <div className="stats-grid-admin">
        <div
          className="stat-card-admin card-users"
          style={{ background: 'linear-gradient(135deg, #28a745, #20c997)' }}
        >
          <div className="stat-icon-admin" style={{ background: 'rgba(255,255,255,0.25)' }}>
            <i className="fas fa-users" aria-hidden />
          </div>
          <div className="stat-data">
            <h3 style={{ color: '#fff' }}>{formatNumber(counts.active)}</h3>
            <p style={{ color: 'rgba(255,255,255,0.95)' }}>Abonnés actifs</p>
          </div>
        </div>
        <div
          className="stat-card-admin card-reports"
          style={{ background: 'linear-gradient(135deg, #dc3545, #c82333)' }}
        >
          <div className="stat-icon-admin" style={{ background: 'rgba(255,255,255,0.25)' }}>
            <i className="fas fa-user-slash" aria-hidden />
          </div>
          <div className="stat-data">
            <h3 style={{ color: '#fff' }}>{formatNumber(counts.unsubscribed)}</h3>
            <p style={{ color: 'rgba(255,255,255,0.95)' }}>Désabonnés</p>
          </div>
        </div>
        <div
          className="stat-card-admin card-tutorials"
          style={{ background: 'linear-gradient(135deg, #ffc107, #ff9800)' }}
        >
          <div className="stat-icon-admin" style={{ background: 'rgba(255,255,255,0.25)' }}>
            <i className="fas fa-exclamation-triangle" aria-hidden />
          </div>
          <div className="stat-data">
            <h3 style={{ color: '#fff' }}>{formatNumber(counts.bounced)}</h3>
            <p style={{ color: 'rgba(255,255,255,0.95)' }}>Rebondis</p>
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
              placeholder="Rechercher un e-mail…"
              value={q}
              onChange={e => setQ(e.target.value)}
              aria-label="Rechercher un abonné"
            />
          </div>
          <select
            className="filter-select-admin"
            value={statusF}
            onChange={e => setStatusF(e.target.value as typeof statusF)}
            aria-label="Statut"
          >
            <option value="all">Tous les statuts</option>
            <option value="active">Actifs</option>
            <option value="unsubscribed">Désabonnés</option>
            <option value="bounced">Rebondis</option>
          </select>
        </div>
        <a
          href={exportUrl}
          download
          className={cn(
            buttonVariants({ variant: 'primary', size: 'md' }),
            'admin-primary-cta shrink-0 w-fit max-w-full whitespace-nowrap px-6'
          )}
        >
          <i className="fas fa-download" aria-hidden /> Exporter CSV
        </a>
      </div>

      <p className="text-sm text-gray-600 m-0">
        {filtered.length === totalListed
          ? `${totalListed} ligne${totalListed === 1 ? '' : 's'} affichée${totalListed === 1 ? '' : 's'}.`
          : `${filtered.length} sur ${totalListed} (filtres actifs).`}{' '}
        Les envois de campagnes se font hors interface ; utilisez l’export pour Brevo, Mailchimp, etc.
      </p>

      <div className="recent-section p-0 shadow-none bg-transparent">
        <div className="table-responsive">
          <table className="admin-table">
            <thead>
              <tr>
                <th>E-mail</th>
                <th>Statut</th>
                <th className="hidden md:table-cell">Inscription</th>
                <th className="hidden lg:table-cell">Désabonnement</th>
                <th className="text-right">Envois</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={5} className="text-center text-gray-500 py-8">
                    Aucun abonné ne correspond aux filtres.
                  </td>
                </tr>
              ) : (
                filtered.map(r => (
                  <tr key={r.id}>
                    <td className="font-mono text-sm">{r.email}</td>
                    <td>
                      {r.status === 'active' && <Badge variant="success">Actif</Badge>}
                      {r.status === 'unsubscribed' && <Badge variant="outline">Désabonné</Badge>}
                      {r.status === 'bounced' && <Badge variant="warning">Rebondi</Badge>}
                    </td>
                    <td className="hidden md:table-cell text-sm text-gray-600 whitespace-nowrap">
                      {formatDateShort(r.subscribed_at)}
                    </td>
                    <td className="hidden lg:table-cell text-sm text-gray-600 whitespace-nowrap">
                      {r.unsubscribed_at ? formatDateShort(r.unsubscribed_at) : '—'}
                    </td>
                    <td className="text-right font-medium">{r.total_sent ?? 0}</td>
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
