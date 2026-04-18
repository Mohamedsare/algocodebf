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
      <div className="activity-tabs">
        {tabs.map(t => (
          <button
            key={t.id}
            type="button"
            className={`tab-btn${active === t.id ? ' active' : ''}`}
            onClick={() => setActive(t.id)}
          >
            <i className={`fas ${t.icon}`}></i> {t.label}
          </button>
        ))}
      </div>
      {tabs.map(t => (
        <div
          key={t.id}
          className={`tab-content${active === t.id ? ' active' : ''}`}
        >
          {t.content}
        </div>
      ))}
    </div>
  )
}
