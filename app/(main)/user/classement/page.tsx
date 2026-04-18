import type { Metadata } from 'next'
import Link from 'next/link'
import { getLeaderboard, type LeaderboardPeriod } from '@/lib/queries/users'
import { currentProfile } from '@/lib/auth'
import { buildAvatarUrl, formatNumber } from '@/lib/utils'

export const metadata: Metadata = {
  title: 'Classement - AlgoCodeBF',
  description:
    'Classement des membres les plus actifs d’AlgoCodeBF : publications, formations, commentaires, likes reçus.',
}

export const revalidate = 300

interface PageProps {
  searchParams: Promise<{ period?: LeaderboardPeriod }>
}

export default async function LeaderboardPage({ searchParams }: PageProps) {
  const sp = await searchParams
  const period: LeaderboardPeriod = sp.period ?? 'month'
  const [entries, me] = await Promise.all([
    getLeaderboard(period, 100),
    currentProfile(),
  ])

  const top = entries.slice(0, 3)

  return (
    <>
      <section className="leaderboard-hero">
        <div className="container">
          <div className="hero-content">
            <h1>
              <i className="fas fa-trophy"></i> Classement de la Communauté
            </h1>
            <p>Les membres les plus actifs et contributeurs de AlgoCodeBF</p>
          </div>
        </div>
      </section>

      <section className="leaderboard-content">
        <div className="container">
          <div className="period-selector">
            <Link
              href="/user/classement?period=week"
              className={`period-btn${period === 'week' ? ' active' : ''}`}
            >
              <i className="fas fa-calendar-week"></i> Cette Semaine
            </Link>
            <Link
              href="/user/classement?period=month"
              className={`period-btn${period === 'month' ? ' active' : ''}`}
            >
              <i className="fas fa-calendar-alt"></i> Ce Mois
            </Link>
            <Link
              href="/user/classement?period=all"
              className={`period-btn${period === 'all' ? ' active' : ''}`}
            >
              <i className="fas fa-calendar"></i> Tout Temps
            </Link>
          </div>

          {top.length >= 3 && (
            <div className="podium-section">
              {/* 2nd place */}
              <div className="podium-card rank-2">
                <div className="podium-rank">
                  <i className="fas fa-medal"></i>
                  <span>2</span>
                </div>
                <div className="podium-avatar">
                  {top[1].photo_path ? (
                    <img
                      src={buildAvatarUrl(top[1].photo_path)}
                      alt={`${top[1].prenom} ${top[1].nom}`}
                    />
                  ) : (
                    <div className="avatar-placeholder-podium">
                      {top[1].prenom.charAt(0).toUpperCase()}
                    </div>
                  )}
                </div>
                <h3>
                  {top[1].prenom} {top[1].nom}
                </h3>
                <p className="podium-university">
                  {top[1].university || 'Non spécifiée'}
                </p>
                <div className="podium-score">
                  <i className="fas fa-star"></i>
                  {formatNumber(top[1].score ?? 0)} pts
                </div>
                <Link
                  href={`/user/${top[1].id}`}
                  className="btn-view-podium"
                >
                  Voir le profil
                </Link>
              </div>

              {/* 1st place */}
              <div className="podium-card rank-1">
                <div className="crown-icon">
                  <i className="fas fa-crown"></i>
                </div>
                <div className="podium-rank winner">
                  <i className="fas fa-trophy"></i>
                  <span>1</span>
                </div>
                <div className="podium-avatar winner-avatar">
                  {top[0].photo_path ? (
                    <img
                      src={buildAvatarUrl(top[0].photo_path)}
                      alt={`${top[0].prenom} ${top[0].nom}`}
                    />
                  ) : (
                    <div className="avatar-placeholder-podium">
                      {top[0].prenom.charAt(0).toUpperCase()}
                    </div>
                  )}
                </div>
                <h3>
                  {top[0].prenom} {top[0].nom}
                </h3>
                <p className="podium-university">
                  {top[0].university || 'Non spécifiée'}
                </p>
                <div className="podium-score winner-score">
                  <i className="fas fa-star"></i>
                  {formatNumber(top[0].score ?? 0)} pts
                </div>
                <Link
                  href={`/user/${top[0].id}`}
                  className="btn-view-podium"
                >
                  Voir le profil
                </Link>
              </div>

              {/* 3rd place */}
              <div className="podium-card rank-3">
                <div className="podium-rank">
                  <i className="fas fa-medal"></i>
                  <span>3</span>
                </div>
                <div className="podium-avatar">
                  {top[2].photo_path ? (
                    <img
                      src={buildAvatarUrl(top[2].photo_path)}
                      alt={`${top[2].prenom} ${top[2].nom}`}
                    />
                  ) : (
                    <div className="avatar-placeholder-podium">
                      {top[2].prenom.charAt(0).toUpperCase()}
                    </div>
                  )}
                </div>
                <h3>
                  {top[2].prenom} {top[2].nom}
                </h3>
                <p className="podium-university">
                  {top[2].university || 'Non spécifiée'}
                </p>
                <div className="podium-score">
                  <i className="fas fa-star"></i>
                  {formatNumber(top[2].score ?? 0)} pts
                </div>
                <Link
                  href={`/user/${top[2].id}`}
                  className="btn-view-podium"
                >
                  Voir le profil
                </Link>
              </div>
            </div>
          )}

          <div className="leaderboard-table-card">
            <div className="table-header">
              <h2>
                <i className="fas fa-list-ol"></i> Classement Complet
              </h2>
            </div>

            <div className="table-responsive">
              <table className="leaderboard-table">
                <thead>
                  <tr>
                    <th className="rank-col">Rang</th>
                    <th>Membre</th>
                    <th className="center-col">Posts</th>
                    <th className="center-col">Formations</th>
                    <th className="center-col">Commentaires</th>
                    <th className="center-col">J&apos;aime</th>
                    <th className="center-col">Score Total</th>
                    <th className="action-col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  {entries.length > 0 ? (
                    entries.map((user, index) => {
                      const rank = index + 1
                      const isCurrentUser = me && me.id === user.id
                      return (
                        <tr
                          key={user.id}
                          className={`leaderboard-row${isCurrentUser ? ' current-user' : ''}`}
                        >
                          <td className="rank-cell">
                            <div className={`rank-badge rank-${rank}`}>
                              {rank <= 3 && (
                                <i
                                  className={`fas ${rank === 1 ? 'fa-trophy' : 'fa-medal'}`}
                                ></i>
                              )}
                              <span>{rank}</span>
                            </div>
                          </td>
                          <td>
                            <div className="user-cell-leaderboard">
                              <div className="user-avatar-small">
                                {user.photo_path ? (
                                  <img
                                    src={buildAvatarUrl(user.photo_path)}
                                    alt={`${user.prenom} ${user.nom}`}
                                  />
                                ) : (
                                  <div className="avatar-placeholder-small">
                                    {user.prenom.charAt(0).toUpperCase()}
                                  </div>
                                )}
                              </div>
                              <div className="user-info-small">
                                <Link
                                  href={`/user/${user.id}`}
                                  className="user-name-link"
                                >
                                  {user.prenom} {user.nom}
                                </Link>
                                {user.city && (
                                  <span className="user-location">
                                    <i className="fas fa-map-marker-alt"></i>
                                    {user.city}
                                  </span>
                                )}
                              </div>
                            </div>
                          </td>
                          <td className="center-col">
                            <span className="stat-value">
                              {user.posts_count ?? 0}
                            </span>
                          </td>
                          <td className="center-col">
                            <span className="stat-value">
                              {user.tutorials_count ?? 0}
                            </span>
                          </td>
                          <td className="center-col">
                            <span className="stat-value">
                              {user.comments_count ?? 0}
                            </span>
                          </td>
                          <td className="center-col">
                            <span className="stat-value">
                              {user.likes_received ?? 0}
                            </span>
                          </td>
                          <td className="center-col">
                            <strong className="total-score">
                              {formatNumber(user.score ?? 0)}
                            </strong>
                          </td>
                          <td className="action-col">
                            <Link
                              href={`/user/${user.id}`}
                              className="btn-view-small"
                            >
                              <i className="fas fa-eye"></i>
                            </Link>
                          </td>
                        </tr>
                      )
                    })
                  ) : (
                    <tr>
                      <td colSpan={8} className="text-center">
                        <div className="no-data">
                          <i className="fas fa-trophy"></i>
                          <p>Aucune donnée de classement disponible</p>
                        </div>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>

          <div className="points-info-card">
            <h3>
              <i className="fas fa-info-circle"></i> Comment fonctionnent les
              points ?
            </h3>
            <div className="points-grid">
              <div className="point-item">
                <div className="point-icon">
                  <i className="fas fa-comment"></i>
                </div>
                <div className="point-info">
                  <strong>5 points</strong>
                  <span>Par post publié</span>
                </div>
              </div>
              <div className="point-item">
                <div className="point-icon">
                  <i className="fas fa-book"></i>
                </div>
                <div className="point-info">
                  <strong>10 points</strong>
                  <span>Par formation publiée</span>
                </div>
              </div>
              <div className="point-item">
                <div className="point-icon">
                  <i className="fas fa-reply"></i>
                </div>
                <div className="point-info">
                  <strong>2 points</strong>
                  <span>Par commentaire</span>
                </div>
              </div>
              <div className="point-item">
                <div className="point-icon">
                  <i className="fas fa-heart"></i>
                </div>
                <div className="point-info">
                  <strong>1 point</strong>
                  <span>Par j&apos;aime reçu</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  )
}
