'use client'

import Link from 'next/link'
import type { SearchSuggestResponse, SearchSuggestScope } from '@/lib/search/run-suggest'
import { scopeLabel } from '@/lib/search/run-suggest'

const ORDER: SearchSuggestScope[] = [
  'members',
  'blog',
  'tutorials',
  'posts',
  'projects',
  'jobs',
]

export function SearchSuggestPanel({
  data,
  loading,
  onPick,
  variant = 'overlay',
  showAllHref,
  allLabel = 'Voir tous les résultats',
}: {
  data: SearchSuggestResponse | null
  loading: boolean
  onPick?: () => void
  variant?: 'overlay' | 'inline'
  showAllHref?: string
  allLabel?: string
}) {
  if (!data && !loading) return null

  const rootClass = variant === 'overlay' ? 'ss-panel ss-panel--overlay' : 'ss-panel ss-panel--inline'

  if (loading && !data) {
    return (
      <div className={rootClass} role="status" aria-live="polite">
        <div className="ss-panel-loading">
          <span className="ss-dot-pulse" aria-hidden />
          Recherche en cours…
        </div>
      </div>
    )
  }

  if (!data) return null

  const sections = ORDER.map(key => ({ key, items: data[key] })).filter(s => s.items.length > 0)
  const total = ORDER.reduce((n, k) => n + data[k].length, 0)

  if (total === 0 && !loading) {
    return (
      <div className={rootClass}>
        <p className="ss-panel-empty">Aucun résultat pour cette saisie.</p>
        {showAllHref && (
          <Link href={showAllHref} className="ss-panel-all" onClick={onPick}>
            {allLabel} →
          </Link>
        )}
      </div>
    )
  }

  return (
    <div className={`${rootClass}${loading ? ' ss-panel--loading' : ''}`} role="listbox" aria-label="Suggestions">
      {sections.map(({ key, items }) => (
        <div key={key} className="ss-section">
          <p className="ss-section-title">{scopeLabel(key)}</p>
          <ul className="ss-list">
            {items.map(item => (
              <li key={`${key}-${item.id}`}>
                <Link href={item.href} className="ss-item" onClick={onPick} role="option">
                  <span className="ss-item-title">{item.title}</span>
                  {item.subtitle && <span className="ss-item-sub">{item.subtitle}</span>}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      ))}
      {loading && (
        <div className="ss-panel-refresh" aria-hidden>
          <span className="ss-dot-pulse" />
        </div>
      )}
      {showAllHref && (
        <Link href={showAllHref} className="ss-panel-all" onClick={onPick}>
          {allLabel} →
        </Link>
      )}
    </div>
  )
}
