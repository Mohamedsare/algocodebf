import type { Metadata } from 'next'
import Link from 'next/link'
import { getLeaderboard, type LeaderboardPeriod } from '@/lib/queries/users'
import { currentProfile } from '@/lib/auth'
import { buildAvatarUrl, formatNumber } from '@/lib/utils'
import type { LeaderboardEntry } from '@/types'

export const metadata: Metadata = {
  title: 'Classement - AlgoCodeBF',
  description:
    "Classement des membres les plus actifs d'AlgoCodeBF : publications, formations, commentaires, likes reçus.",
}

export const revalidate = 300

interface PageProps {
  searchParams: Promise<{ period?: LeaderboardPeriod }>
}

const PERIOD_LABEL: Record<LeaderboardPeriod, string> = {
  week: 'Cette semaine',
  month: 'Ce mois',
  all: 'Tout temps',
}

const PERIOD_DESC: Record<LeaderboardPeriod, string> = {
  week: 'Contributions des 7 derniers jours',
  month: 'Contributions des 30 derniers jours',
  all: 'Score global (toutes périodes)',
}

function podiumVariantClass(rank: 1 | 2 | 3) {
  if (rank === 1) return 'lb-podium-card lb-podium-card--gold'
  if (rank === 2) return 'lb-podium-card lb-podium-card--silver'
  return 'lb-podium-card lb-podium-card--bronze'
}

function PodiumSlot({ rank, user }: { rank: 1 | 2 | 3; user: LeaderboardEntry }) {
  const initial = user.prenom.charAt(0).toUpperCase()
  const isGold = rank === 1
  return (
    <article className={podiumVariantClass(rank)}>
      {isGold && (
        <div className="lb-crown" aria-hidden>
          <i className="fas fa-crown" />
        </div>
      )}
      <div className={`lb-podium-rank${isGold ? ' lb-podium-rank--gold' : ''}`}>
        <i className={`fas ${isGold ? 'fa-trophy' : 'fa-medal'}`} aria-hidden />
        <span>{rank}</span>
      </div>
      <div className={`lb-podium-avatar${isGold ? ' lb-podium-avatar--gold' : ''}`}>
        {user.photo_path ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={buildAvatarUrl(user.photo_path)} alt="" />
        ) : (
          <div className="lb-podium-placeholder">{initial}</div>
        )}
      </div>
      <h3 className="lb-podium-name">
        {user.prenom} {user.nom}
      </h3>
      <p className="lb-podium-meta">{user.university || user.city || 'Communauté AlgoCodeBF'}</p>
      <div className="lb-podium-score">
        <i className="fas fa-bolt" aria-hidden />
        {formatNumber(user.score ?? 0)} pts
      </div>
      <Link href={`/user/${user.id}`} className="lb-podium-cta">
        Profil <i className="fas fa-arrow-right" aria-hidden />
      </Link>
    </article>
  )
}

export default async function LeaderboardPage({ searchParams }: PageProps) {
  const sp = await searchParams
  const period: LeaderboardPeriod = sp.period ?? 'month'
  const [entries, me] = await Promise.all([getLeaderboard(period, 100), currentProfile()])

  const top = entries.slice(0, 3)

  return (
    <div className="lb-saas">
      <section className="lb-hero" aria-labelledby="lb-heading">
        <div className="container">
          <div className="lb-hero-inner">
            <p className="lb-eyebrow">
              <i className="fas fa-trophy" aria-hidden />
              Communauté
            </p>
            <h1 id="lb-heading" className="lb-title">
              Classement
            </h1>
            <p className="lb-subtitle">
              Les membres les plus actifs : forum, formations, échanges — mis à jour selon la période choisie.
            </p>
            <div className="lb-hero-meta">
              <span className="lb-hero-chip">
                <i className="fas fa-calendar-alt" aria-hidden />
                {PERIOD_LABEL[period]}
              </span>
              <span className="lb-hero-chip">
                <i className="fas fa-users" aria-hidden />
                {formatNumber(entries.length)} classé{entries.length !== 1 ? 's' : ''}
              </span>
            </div>
          </div>
        </div>
      </section>

      <section className="lb-main" aria-label="Classement détaillé">
        <div className="container">
          <div className="lb-toolbar">
            <p className="lb-period-hint">{PERIOD_DESC[period]}</p>
            <nav className="lb-tabs" aria-label="Période du classement">
              <Link
                href="/user/classement?period=week"
                className={`lb-tab${period === 'week' ? ' lb-tab--active' : ''}`}
              >
                <i className="fas fa-calendar-week" aria-hidden />
                Semaine
              </Link>
              <Link
                href="/user/classement?period=month"
                className={`lb-tab${period === 'month' ? ' lb-tab--active' : ''}`}
              >
                <i className="fas fa-calendar-alt" aria-hidden />
                Mois
              </Link>
              <Link
                href="/user/classement?period=all"
                className={`lb-tab${period === 'all' ? ' lb-tab--active' : ''}`}
              >
                <i className="fas fa-infinity" aria-hidden />
                Tout temps
              </Link>
            </nav>
          </div>

          {top.length >= 3 && (
            <div className="lb-podium">
              <PodiumSlot rank={2} user={top[1]} />
              <PodiumSlot rank={1} user={top[0]} />
              <PodiumSlot rank={3} user={top[2]} />
            </div>
          )}

          <div className="lb-table-card">
            <div className="lb-table-head">
              <h2 className="lb-table-title">
                <i className="fas fa-list-ol" aria-hidden />
                Classement complet
              </h2>
              <span className="lb-table-count">{entries.length} entrée{entries.length !== 1 ? 's' : ''}</span>
            </div>

            <div className="lb-table-scroll">
              <table className="lb-table">
                <thead>
                  <tr>
                    <th className="lb-th-num" scope="col">
                      Rang
                    </th>
                    <th scope="col">Membre</th>
                    <th className="lb-th-hide-sm" scope="col">
                      Posts
                    </th>
                    <th className="lb-th-hide-sm" scope="col">
                      Formations
                    </th>
                    <th className="lb-th-hide-sm" scope="col">
                      Commentaires
                    </th>
                    <th className="lb-th-hide-sm" scope="col">
                      J&apos;aime
                    </th>
                    <th className="lb-th-score" scope="col">
                      Score
                    </th>
                    <th className="lb-th-action" scope="col">
                      <span className="sr-only">Voir</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {entries.length > 0 ? (
                    entries.map((user, index) => {
                      const rank = index + 1
                      const isCurrentUser = me && me.id === user.id
                      const topRankClass =
                        rank === 1 ? ' lb-rank-pill--gold' : rank <= 3 ? ' lb-rank-pill--top' : ''
                      return (
                        <tr key={user.id} className={`lb-row${isCurrentUser ? ' lb-row--me' : ''}`}>
                          <td className="lb-td-num">
                            <span className={`lb-rank-pill${topRankClass}`}>
                              {rank <= 3 && <i className={`fas ${rank === 1 ? 'fa-trophy' : 'fa-medal'}`} aria-hidden />}
                              {rank}
                            </span>
                          </td>
                          <td>
                            <div className="lb-user-cell">
                              <div className="lb-avatar">
                                {user.photo_path ? (
                                  // eslint-disable-next-line @next/next/no-img-element
                                  <img src={buildAvatarUrl(user.photo_path)} alt="" />
                                ) : (
                                  <div className="lb-avatar-ph">{user.prenom.charAt(0).toUpperCase()}</div>
                                )}
                              </div>
                              <div className="lb-user-text">
                                <Link href={`/user/${user.id}`} className="lb-user-name">
                                  {user.prenom} {user.nom}
                                  {isCurrentUser ? ' · vous' : ''}
                                </Link>
                                {user.city && (
                                  <span className="lb-user-loc">
                                    <i className="fas fa-map-marker-alt" aria-hidden />
                                    {user.city}
                                  </span>
                                )}
                              </div>
                            </div>
                          </td>
                          <td className="lb-td-hide-sm">
                            <span className="lb-stat">{user.posts_count ?? 0}</span>
                          </td>
                          <td className="lb-td-hide-sm">
                            <span className="lb-stat">{user.tutorials_count ?? 0}</span>
                          </td>
                          <td className="lb-td-hide-sm">
                            <span className="lb-stat">{user.comments_count ?? 0}</span>
                          </td>
                          <td className="lb-td-hide-sm">
                            <span className="lb-stat">{user.likes_received ?? 0}</span>
                          </td>
                          <td className="lb-td-score">
                            <span className="lb-score-total">{formatNumber(user.score ?? 0)}</span>
                          </td>
                          <td className="lb-td-action">
                            <Link href={`/user/${user.id}`} className="lb-btn-icon" aria-label={`Profil de ${user.prenom}`}>
                              <i className="fas fa-arrow-right" aria-hidden />
                            </Link>
                          </td>
                        </tr>
                      )
                    })
                  ) : (
                    <tr>
                      <td colSpan={8}>
                        <div className="lb-empty">
                          <i className="fas fa-chart-line" aria-hidden />
                          <p>Aucune donnée pour cette période. Revenez plus tard ou élargissez à « Tout temps ».</p>
                        </div>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>

          <div className="lb-rules">
            <h3>
              <i className="fas fa-calculator" aria-hidden />
              Comment sont calculés les points ?
            </h3>
            <div className="lb-rules-grid">
              <div className="lb-rule">
                <div className="lb-rule-icon">
                  <i className="fas fa-comment" aria-hidden />
                </div>
                <div>
                  <strong>5 pts</strong>
                  <span>Par discussion forum publiée</span>
                </div>
              </div>
              <div className="lb-rule">
                <div className="lb-rule-icon">
                  <i className="fas fa-book-open" aria-hidden />
                </div>
                <div>
                  <strong>10 pts</strong>
                  <span>Par formation publiée</span>
                </div>
              </div>
              <div className="lb-rule">
                <div className="lb-rule-icon">
                  <i className="fas fa-reply" aria-hidden />
                </div>
                <div>
                  <strong>2 pts</strong>
                  <span>Par commentaire</span>
                </div>
              </div>
              <div className="lb-rule">
                <div className="lb-rule-icon">
                  <i className="fas fa-heart" aria-hidden />
                </div>
                <div>
                  <strong>1 pt</strong>
                  <span>Par j&apos;aime reçu sur vos posts</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
