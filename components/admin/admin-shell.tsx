'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { useEffect, useState } from 'react'
import { ADMIN_CONSOLE_PATH } from '@/lib/routes'
import { buildAvatarUrl } from '@/lib/utils'
import type { Profile } from '@/types'

interface Stats {
  total_users: number
  total_posts: number
  total_tutorials: number
  total_projects: number
  total_jobs: number
  total_subscribers: number
  pending_reports: number
  total_comments: number
}

interface NavItem {
  href: string
  icon: string
  label: string
  countKey?: keyof Stats
  alertKey?: keyof Stats
}

const NAV: NavItem[] = [
  { href: ADMIN_CONSOLE_PATH, icon: 'fa-chart-pie', label: "Vue d'ensemble" },
  { href: `${ADMIN_CONSOLE_PATH}/users`, icon: 'fa-users', label: 'Utilisateurs', countKey: 'total_users' },
  { href: `${ADMIN_CONSOLE_PATH}/forum`, icon: 'fa-comments', label: 'Forum', countKey: 'total_posts' },
  { href: `${ADMIN_CONSOLE_PATH}/tutorials`, icon: 'fa-book-open', label: 'Formations', countKey: 'total_tutorials' },
  { href: `${ADMIN_CONSOLE_PATH}/projects`, icon: 'fa-project-diagram', label: 'Projets', countKey: 'total_projects' },
  { href: `${ADMIN_CONSOLE_PATH}/jobs`, icon: 'fa-briefcase', label: 'Opportunités', countKey: 'total_jobs' },
  { href: `${ADMIN_CONSOLE_PATH}/blog`, icon: 'fa-blog', label: 'Blog' },
  { href: `${ADMIN_CONSOLE_PATH}/comments`, icon: 'fa-comment-dots', label: 'Commentaires', countKey: 'total_comments' },
  { href: `${ADMIN_CONSOLE_PATH}/reports`, icon: 'fa-flag', label: 'Signalements', alertKey: 'pending_reports' },
  { href: `${ADMIN_CONSOLE_PATH}/newsletter`, icon: 'fa-envelope-open-text', label: 'Newsletter', countKey: 'total_subscribers' },
  { href: `${ADMIN_CONSOLE_PATH}/statistics`, icon: 'fa-chart-bar', label: 'Statistiques' },
  { href: `${ADMIN_CONSOLE_PATH}/content`, icon: 'fa-layer-group', label: 'Contenus' },
  { href: `${ADMIN_CONSOLE_PATH}/logs`, icon: 'fa-history', label: 'Logs' },
  { href: `${ADMIN_CONSOLE_PATH}/permissions`, icon: 'fa-shield-alt', label: 'Permissions' },
  { href: `${ADMIN_CONSOLE_PATH}/settings`, icon: 'fa-cog', label: 'Paramètres' },
]

const TITLES: Record<string, { icon: string; label: string }> = {
  [ADMIN_CONSOLE_PATH]: { icon: 'fa-chart-pie', label: "Vue d'ensemble" },
  [`${ADMIN_CONSOLE_PATH}/users`]: { icon: 'fa-users', label: 'Gestion des Utilisateurs' },
  [`${ADMIN_CONSOLE_PATH}/forum`]: { icon: 'fa-comments', label: 'Gestion du Forum' },
  [`${ADMIN_CONSOLE_PATH}/tutorials`]: { icon: 'fa-book-open', label: 'Gestion des formations' },
  [`${ADMIN_CONSOLE_PATH}/projects`]: { icon: 'fa-project-diagram', label: 'Gestion des Projets' },
  [`${ADMIN_CONSOLE_PATH}/jobs`]: { icon: 'fa-briefcase', label: 'Gestion des Opportunités' },
  [`${ADMIN_CONSOLE_PATH}/blog`]: { icon: 'fa-blog', label: 'Gestion du Blog' },
  [`${ADMIN_CONSOLE_PATH}/comments`]: { icon: 'fa-comment-dots', label: 'Modération des Commentaires' },
  [`${ADMIN_CONSOLE_PATH}/reports`]: { icon: 'fa-flag', label: 'Signalements' },
  [`${ADMIN_CONSOLE_PATH}/newsletter`]: { icon: 'fa-envelope-open-text', label: 'Newsletter' },
  [`${ADMIN_CONSOLE_PATH}/statistics`]: { icon: 'fa-chart-bar', label: 'Statistiques avancées' },
  [`${ADMIN_CONSOLE_PATH}/content`]: { icon: 'fa-layer-group', label: 'Gestion des contenus' },
  [`${ADMIN_CONSOLE_PATH}/logs`]: { icon: 'fa-history', label: "Logs d'activité" },
  [`${ADMIN_CONSOLE_PATH}/permissions`]: { icon: 'fa-shield-alt', label: 'Gestion des Permissions' },
  [`${ADMIN_CONSOLE_PATH}/settings`]: { icon: 'fa-cog', label: 'Paramètres' },
}

interface Props {
  profile: Profile
  stats: Stats
  children: React.ReactNode
}

export function AdminShell({ profile, stats, children }: Props) {
  const pathname = usePathname() ?? ADMIN_CONSOLE_PATH
  const title = TITLES[pathname] ?? TITLES[ADMIN_CONSOLE_PATH]
  const adminInitial = (profile.prenom?.charAt(0) ?? 'A').toUpperCase()
  const adminName = `${profile.prenom ?? 'Admin'} ${profile.nom ?? ''}`.trim()
  const [navOpen, setNavOpen] = useState(false)

  useEffect(() => {
    setNavOpen(false)
  }, [pathname])

  useEffect(() => {
    const mq = window.matchMedia('(min-width: 992px)')
    const onChange = () => {
      if (mq.matches) setNavOpen(false)
    }
    mq.addEventListener('change', onChange)
    onChange()
    return () => mq.removeEventListener('change', onChange)
  }, [])

  useEffect(() => {
    if (navOpen) document.body.classList.add('admin-mobile-nav-open')
    else document.body.classList.remove('admin-mobile-nav-open')
    return () => document.body.classList.remove('admin-mobile-nav-open')
  }, [navOpen])

  useEffect(() => {
    if (!navOpen) return
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setNavOpen(false)
    }
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [navOpen])

  return (
    <div className={`admin-dashboard-ultra${navOpen ? ' admin-nav-drawer-open' : ''}`}>
      {/* eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions -- voile tactile pour fermer le tiroir */}
      <div
        className="admin-sidebar-backdrop"
        aria-hidden={!navOpen}
        onClick={() => setNavOpen(false)}
      />
      <aside className="admin-sidebar-ultra" id="admin-sidebar-nav" aria-label="Navigation administration">
        <div className="sidebar-header">
          <div className="admin-logo">
            <i className="fas fa-shield-halved"></i>
            <span>Admin Panel</span>
          </div>
        </div>

        <nav className="admin-nav-ultra">
          {NAV.map(item => {
            const active = pathname === item.href
            const countVal = item.countKey ? stats[item.countKey] : null
            const alertVal = item.alertKey ? stats[item.alertKey] : null
            return (
              <Link
                key={item.href}
                href={item.href}
                className={`nav-item-ultra${active ? ' active' : ''}`}
                onClick={() => setNavOpen(false)}
              >
                <i className={`fas ${item.icon}`} aria-hidden />
                <span className="nav-item-ultra-label">{item.label}</span>
                {countVal !== null && countVal !== undefined && (
                  <span className="count-badge">{countVal}</span>
                )}
                {alertVal !== null && alertVal !== undefined && alertVal > 0 && (
                  <span className="alert-badge">{alertVal}</span>
                )}
              </Link>
            )
          })}
        </nav>

        <div className="sidebar-footer">
          <Link href="/" className="btn-back-site" onClick={() => setNavOpen(false)}>
            <i className="fas fa-home"></i> Retour au site
          </Link>
        </div>
      </aside>

      <main className="admin-content-ultra">
        <div className="admin-topbar">
          <div className="topbar-left">
            <button
              type="button"
              className="admin-burger"
              aria-expanded={navOpen}
              aria-controls="admin-sidebar-nav"
              aria-label={navOpen ? 'Fermer le menu' : 'Ouvrir le menu'}
              onClick={() => setNavOpen(o => !o)}
            >
              <i className={`fas ${navOpen ? 'fa-times' : 'fa-bars'}`} aria-hidden />
            </button>
            <h1 className="admin-topbar-title">
              <i className={`fas ${title.icon}`} aria-hidden />
              <span className="admin-topbar-title-text">{title.label}</span>
            </h1>
          </div>
          <div className="topbar-right">
            <div className="admin-profile">
              <div className="profile-avatar">
                {profile.photo_path ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img src={buildAvatarUrl(profile.photo_path)} alt={adminName} />
                ) : (
                  <div className="avatar-placeholder-admin">{adminInitial}</div>
                )}
              </div>
              <div className="profile-info">
                <strong>{adminName || 'Admin'}</strong>
                <span>Administrateur</span>
              </div>
            </div>
          </div>
        </div>

        <div className="admin-sections">
          <div className="admin-section-content active">{children}</div>
        </div>
      </main>
    </div>
  )
}
