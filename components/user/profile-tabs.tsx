'use client'

import { useState } from 'react'

interface Tab {
  id: string
  label: string
  icon: string
  content: React.ReactNode
}

interface Props {
  tabs: Tab[]
}

export function ProfileTabs({ tabs }: Props) {
  const [active, setActive] = useState(tabs[0]?.id ?? '')

  return (
    <div className="profile-main-modern">
      <div
        className="activity-tabs"
        role="tablist"
        aria-label="Activité du membre"
      >
        {tabs.map(t => (
          <button
            key={t.id}
            type="button"
            role="tab"
            id={`profile-tab-${t.id}`}
            aria-selected={active === t.id}
            aria-controls={`profile-panel-${t.id}`}
            tabIndex={active === t.id ? 0 : -1}
            className={`tab-btn${active === t.id ? ' active' : ''}`}
            onClick={() => setActive(t.id)}
          >
            <i className={`fas ${t.icon}`} aria-hidden="true"></i> {t.label}
          </button>
        ))}
      </div>
      {tabs.map(t => (
        <div
          key={t.id}
          role="tabpanel"
          id={`profile-panel-${t.id}`}
          aria-labelledby={`profile-tab-${t.id}`}
          hidden={active !== t.id}
          className={`tab-content${active === t.id ? ' active' : ''}`}
        >
          {t.content}
        </div>
      ))}
    </div>
  )
}
