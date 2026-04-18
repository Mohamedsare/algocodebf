'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { useEffect, useState } from 'react'
import type { AccountKind, Profile } from '@/types'
import { getAccountKindLabel } from '@/lib/my-space'
import { getEspaceSidebarNav } from '@/lib/espace-nav'

const DASH_PATHS = ['/espace/etudiant', '/espace/formateur', '/espace/entreprise'] as const

function isNavActive(pathname: string, href: string, exact?: boolean): boolean {
  if (exact) return pathname === href
  return pathname === href || pathname.startsWith(`${href}/`)
}

export function EspaceAppShell({
  profile,
  kind,
  children,
}: {
  profile: Profile
  kind: AccountKind
  children: React.ReactNode
}) {
  const pathname = usePathname()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const sections = getEspaceSidebarNav(kind, profile.id)
  const kindLabel = getAccountKindLabel(kind)
  const displayName =
    [profile.prenom, profile.nom].filter(Boolean).join(' ').trim() || 'Membre'

  const pageTitle = DASH_PATHS.includes(pathname as (typeof DASH_PATHS)[number])
    ? 'Vue d’ensemble'
    : 'Mon espace'

  useEffect(() => {
    setSidebarOpen(false)
  }, [pathname])

  useEffect(() => {
    if (!sidebarOpen) return
    const prev = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    return () => {
      document.body.style.overflow = prev
    }
  }, [sidebarOpen])

  return (
    <div className="espace-app">
      {sidebarOpen && (
        <button
          type="button"
          className="espace-app-backdrop"
          aria-label="Fermer le menu"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      <aside
        id="espace-sidebar"
        className={`espace-app-sidebar${sidebarOpen ? ' espace-app-sidebar--open' : ''}`}
        aria-label="Navigation de l’espace"
      >
        <div className="espace-app-sidebar-brand">
          <Link href={sections[0]?.items[0]?.href ?? '/espace'} className="espace-app-sidebar-logo">
            <span className="espace-app-sidebar-logo-mark" aria-hidden>
              <i className="fas fa-layer-group" />
            </span>
            <span className="espace-app-sidebar-logo-text">
              <strong>Mon espace</strong>
              <small>{kindLabel}</small>
            </span>
          </Link>
        </div>

        <nav className="espace-app-nav">
          {sections.map(section => (
            <div key={section.title} className="espace-app-nav-group">
              <p className="espace-app-nav-title">{section.title}</p>
              <ul>
                {section.items.map(item => {
                  const active = isNavActive(pathname, item.href, item.exact)
                  return (
                    <li key={item.href}>
                      <Link
                        href={item.href}
                        className={active ? 'espace-app-nav-link espace-app-nav-link--active' : 'espace-app-nav-link'}
                      >
                        <span className="espace-app-nav-ico" aria-hidden>
                          <i className={`fas ${item.icon}`} />
                        </span>
                        {item.label}
                      </Link>
                    </li>
                  )
                })}
              </ul>
            </div>
          ))}
        </nav>

        <div className="espace-app-sidebar-footer">
          <Link href="/" className="espace-app-exit">
            <i className="fas fa-arrow-left" aria-hidden />
            Retour au site
          </Link>
          <div className="espace-app-user">
            <div className="espace-app-user-avatar" aria-hidden>
              <i className="fas fa-user" />
            </div>
            <div className="espace-app-user-meta">
              <span className="espace-app-user-name">{displayName}</span>
              <Link href={`/user/${profile.id}`}>Voir le profil</Link>
            </div>
          </div>
        </div>
      </aside>

      <div className="espace-app-main">
        <div className="espace-app-topbar">
          <button
            type="button"
            className="espace-app-menu-btn"
            aria-expanded={sidebarOpen}
            aria-controls="espace-sidebar"
            onClick={() => setSidebarOpen(v => !v)}
          >
            <i className="fas fa-bars" aria-hidden />
            <span>Menu</span>
          </button>
          <div className="espace-app-topbar-center">
            <span className="espace-app-topbar-kicker">AlgoCodeBF</span>
            <span className="espace-app-topbar-title">{pageTitle}</span>
          </div>
          <Link href="/message" className="espace-app-topbar-action" title="Messages">
            <i className="fas fa-envelope" aria-hidden />
            <span className="espace-app-topbar-action-label">Messages</span>
          </Link>
        </div>

        <div className="espace-app-body">{children}</div>
      </div>
    </div>
  )
}
