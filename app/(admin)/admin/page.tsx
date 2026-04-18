import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { buildAvatarUrl, formatNumber, formatDate } from '@/lib/utils'

export const dynamic = 'force-dynamic'

export default async function AdminDashboardPage() {
  const supabase = await createClient()

  const [
    { count: totalUsers },
    { count: totalPosts },
    { count: totalTutorials },
    { count: pendingReports },
  ] = await Promise.all([
    supabase.from('profiles').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('posts').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('tutorials').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('reports').select('*', { count: 'exact', head: true }).eq('status', 'pending'),
  ])

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

      <div className="recent-section" style={{ marginTop: 35 }}>
        <div className="section-header">
          <h2>
            <i className="fas fa-history"></i> Nouveaux Utilisateurs
          </h2>
          <Link href="/admin/users" className="btn-view-all">
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
                            href={`/admin/users?edit=${user.id}`}
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
