import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { ADMIN_CONSOLE_PATH } from '@/lib/routes'
import { buildAvatarUrl, formatNumber, formatDate } from '@/lib/utils'

export const dynamic = 'force-dynamic'

function startOfDay(d: Date): Date {
  const x = new Date(d)
  x.setHours(0, 0, 0, 0)
  return x
}

function bucketSignupsByDay(rows: { created_at: string }[], days: number): number[] {
  const counts = Array.from({ length: days }, () => 0)
  const end = startOfDay(new Date())
  const start = new Date(end)
  start.setDate(start.getDate() - (days - 1))
  for (const row of rows) {
    const t = startOfDay(new Date(row.created_at))
    const idx = Math.round((t.getTime() - start.getTime()) / 86400000)
    if (idx >= 0 && idx < days) counts[idx] += 1
  }
  return counts
}

function lastNDayLabels(days: number): string[] {
  const out: string[] = []
  for (let i = days - 1; i >= 0; i--) {
    const d = new Date()
    d.setDate(d.getDate() - i)
    out.push(
      d.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' })
    )
  }
  return out
}

export default async function AdminDashboardPage() {
  const supabase = await createClient()

  const [
    { count: totalUsers },
    { count: totalPosts },
    { count: totalTutorials },
    { count: pendingReports },
    { count: totalProjects },
    { count: totalBlog },
  ] = await Promise.all([
    supabase.from('profiles').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('posts').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('tutorials').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('reports').select('*', { count: 'exact', head: true }).eq('status', 'pending'),
    supabase.from('projects').select('*', { count: 'exact', head: true }),
    supabase.from('blog_posts').select('*', { count: 'exact', head: true }).eq('status', 'published'),
  ])

  const weekAgo = new Date()
  weekAgo.setDate(weekAgo.getDate() - 7)
  const { data: signupsRaw } = await supabase
    .from('profiles')
    .select('created_at')
    .gte('created_at', weekAgo.toISOString())

  const dayBuckets = bucketSignupsByDay((signupsRaw ?? []) as { created_at: string }[], 7)
  const dayLabels = lastNDayLabels(7)
  const maxSignup = Math.max(1, ...dayBuckets)

  const contentTotal =
    (totalPosts ?? 0) + (totalTutorials ?? 0) + (totalBlog ?? 0) + (totalProjects ?? 0)
  const mix = [
    { label: 'Forum', value: totalPosts ?? 0, color: '#3498db' },
    { label: 'Formations', value: totalTutorials ?? 0, color: '#ffc107' },
    { label: 'Blog', value: totalBlog ?? 0, color: '#e74c3c' },
    { label: 'Projets', value: totalProjects ?? 0, color: '#27ae60' },
  ]

  const { data: recentUsers } = await supabase
    .from('profiles')
    .select('id, prenom, nom, photo_path, university, status, created_at')
    .order('created_at', { ascending: false })
    .limit(5)

  return (
    <>
      <div className="stats-grid-admin">
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-users"></i>
          </div>
          <div className="stat-data">
            <h3>{formatNumber(totalUsers ?? 0)}</h3>
            <p>Utilisateurs</p>
            <span className="stat-trend positive">
              <i className="fas fa-arrow-up"></i> Actifs
            </span>
          </div>
        </div>

        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-comments"></i>
          </div>
          <div className="stat-data">
            <h3>{formatNumber(totalPosts ?? 0)}</h3>
            <p>Discussions</p>
            <span className="stat-trend positive">
              <i className="fas fa-arrow-up"></i> Publiées
            </span>
          </div>
        </div>

        <div className="stat-card-admin card-tutorials">
          <div className="stat-icon-admin">
            <i className="fas fa-book-open"></i>
          </div>
          <div className="stat-data">
            <h3>{formatNumber(totalTutorials ?? 0)}</h3>
            <p>Formations</p>
            <span className="stat-trend positive">
              <i className="fas fa-arrow-up"></i> En ligne
            </span>
          </div>
        </div>

        <div className="stat-card-admin card-reports">
          <div className="stat-icon-admin">
            <i className="fas fa-flag"></i>
          </div>
          <div className="stat-data">
            <h3>{formatNumber(pendingReports ?? 0)}</h3>
            <p>Signalements</p>
            <span className="stat-trend warning">
              <i className="fas fa-exclamation-circle"></i> À traiter
            </span>
          </div>
        </div>
      </div>

      <div className="charts-row">
        <div className="chart-card">
          <h3>
            <i className="fas fa-chart-line"></i> Inscriptions (7 derniers jours)
          </h3>
          <div className="admin-bar-chart" role="img" aria-label="Histogramme des inscriptions sur 7 jours">
            {dayBuckets.map((n, i) => (
              <div key={i} className="admin-bar-chart-col">
                <div
                  className="admin-bar-chart-bar"
                  style={{ height: `${Math.max(6, (n / maxSignup) * 100)}%` }}
                  title={`${n} inscription(s)`}
                />
                <span className="admin-bar-chart-label">{dayLabels[i]}</span>
              </div>
            ))}
          </div>
        </div>
        <div className="chart-card">
          <h3>
            <i className="fas fa-chart-pie"></i> Contenu public (parts relatives)
          </h3>
          {contentTotal === 0 ? (
            <p className="text-sm text-gray-500 m-0">Pas encore de contenu indexé.</p>
          ) : (
            <div className="admin-content-mix">
              {mix.map(row => {
                const pct = Math.round((row.value / contentTotal) * 1000) / 10
                return (
                  <div key={row.label} className="admin-content-mix-row">
                    <span className="w-[88px] shrink-0 text-gray-700">{row.label}</span>
                    <div className="admin-content-mix-barwrap">
                      <div
                        className="admin-content-mix-bar"
                        style={{ width: `${pct}%`, background: row.color }}
                      />
                    </div>
                    <span className="w-12 text-right text-xs font-semibold text-gray-600">{pct}%</span>
                  </div>
                )
              })}
            </div>
          )}
        </div>
      </div>

      <div className="recent-section" style={{ marginTop: 35 }}>
        <div className="section-header">
          <h2>
            <i className="fas fa-history"></i> Nouveaux Utilisateurs
          </h2>
          <Link href={`${ADMIN_CONSOLE_PATH}/users`} className="btn-view-all">
            Voir tout <i className="fas fa-arrow-right"></i>
          </Link>
        </div>
        <div className="table-responsive">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Utilisateur</th>
                <th>Université</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {(recentUsers ?? []).length > 0 ? (
                (recentUsers ?? []).map(user => {
                  const name = `${user.prenom ?? ''} ${user.nom ?? ''}`.trim() || 'Utilisateur'
                  const initial = (user.prenom?.charAt(0) ?? 'U').toUpperCase()
                  return (
                    <tr key={user.id}>
                      <td>
                        <div className="user-cell">
                          {user.photo_path ? (
                            // eslint-disable-next-line @next/next/no-img-element
                            <img src={buildAvatarUrl(user.photo_path)} alt={name} />
                          ) : (
                            <div className="avatar-placeholder-dash">{initial}</div>
                          )}
                          <span>{name}</span>
                        </div>
                      </td>
                      <td>{user.university ?? '-'}</td>
                      <td>{formatDate(user.created_at)}</td>
                      <td>
                        <span className={`status-badge ${user.status}`}>
                          {statusLabel(user.status as string)}
                        </span>
                      </td>
                      <td>
                        <div className="action-buttons">
                          <Link
                            href={`/user/${user.id}`}
                            className="btn-action btn-view"
                            title="Voir"
                          >
                            <i className="fas fa-eye"></i>
                          </Link>
                          <Link
                            href={`${ADMIN_CONSOLE_PATH}/users?edit=${user.id}`}
                            className="btn-action btn-edit"
                            title="Gérer"
                          >
                            <i className="fas fa-edit"></i>
                          </Link>
                        </div>
                      </td>
                    </tr>
                  )
                })
              ) : (
                <tr>
                  <td colSpan={5} className="text-center">
                    Aucun utilisateur récent
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </>
  )
}

function statusLabel(status: string) {
  switch (status) {
    case 'active':
      return 'Actif'
    case 'pending':
      return 'En attente'
    case 'suspended':
      return 'Suspendu'
    case 'banned':
      return 'Banni'
    default:
      return status
  }
}
