'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
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
}

interface NavItem {
  href: string
  icon: string
  label: string
  countKey?: keyof Stats
  alertKey?: keyof Stats
}

const NAV: NavItem[] = [
  { href: '/admin', icon: 'fa-chart-pie', label: "Vue d'ensemble" },
  { href: '/admin/users', icon: 'fa-users', label: 'Utilisateurs', countKey: 'total_users' },
  { href: '/admin/forum', icon: 'fa-comments', label: 'Forum', countKey: 'total_posts' },
  { href: '/admin/tutorials', icon: 'fa-book-open', label: 'Formations', countKey: 'total_tutorials' },
  { href: '/admin/projects', icon: 'fa-project-diagram', label: 'Projets', countKey: 'total_projects' },
  { href: '/admin/jobs', icon: 'fa-briefcase', label: 'Opportunités', countKey: 'total_jobs' },
  { href: '/admin/blog', icon: 'fa-blog', label: 'Blog' },
  { href: '/admin/reports', icon: 'fa-flag', label: 'Signalements', alertKey: 'pending_reports' },
  { href: '/admin/permissions', icon: 'fa-shield-alt', label: 'Permissions' },
  { href: '/admin/settings', icon: 'fa-cog', label: 'Paramètres' },
]

const TITLES: Record<string, { icon: string; label: string }> = {
  '/admin': { icon: 'fa-chart-pie', label: "Vue d'ensemble" },
  '/admin/users': { icon: 'fa-users', label: 'Gestion des Utilisateurs' },
  '/admin/forum': { icon: 'fa-comments', label: 'Gestion du Forum' },
  '/admin/tutorials': { icon: 'fa-book-open', label: 'Gestion des formations' },
  '/admin/projects': { icon: 'fa-project-diagram', label: 'Gestion des Projets' },
  '/admin/jobs': { icon: 'fa-briefcase', label: 'Gestion des Opportunités' },
  '/admin/blog': { icon: 'fa-blog', label: 'Gestion du Blog' },
  '/admin/reports': { icon: 'fa-flag', label: 'Signalements' },
  '/admin/permissions': { icon: 'fa-shield-alt', label: 'Gestion des Permissions' },
  '/admin/settings': { icon: 'fa-cog', label: 'Paramètres' },
}

interface Props {
  profile: Profile
  stats: Stats
  children: React.ReactNode
}

export function AdminShell({ profile, stats, children }: Props) {
  const pathname = usePathname() ?? '/admin'
  const title = TITLES[pathname] ?? TITLES['/admin']
  const adminInitial = (profile.prenom?.charAt(0) ?? 'A').toUpperCase()
  const adminName = `${profile.prenom ?? 'Admin'} ${profile.nom ?? ''}`.trim()

  return (
    <div className="admin-dashboard-ultra">
      <aside className="admin-sidebar-ultra">
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
              >
                <i className={`fas ${item.icon}`}></i>
                <span>{item.label}</span>
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
          <Link href="/" className="btn-back-site">
            <i className="fas fa-home"></i> Retour au site
          </Link>
        </div>
      </aside>

      <main className="admin-content-ultra">
        <div className="admin-topbar">
          <div className="topbar-left">
            <h1>
              <i className={`fas ${title.icon}`}></i> {title.label}
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
