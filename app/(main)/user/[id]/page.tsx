import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import {
  getPublicProfile,
  getUserPosts,
  getUserTutorials,
  getUserProjects,
  isFollowing,
} from '@/lib/queries/users'
import { currentProfile } from '@/lib/auth'
import { buildAvatarUrl, timeAgo } from '@/lib/utils'
import { FollowButtonPhp } from '@/components/user/follow-button-php'
import { ProfileTabs } from '@/components/user/profile-tabs'

interface PageProps {
  params: Promise<{ id: string }>
}

export async function generateMetadata({
  params,
}: PageProps): Promise<Metadata> {
  const { id } = await params
  const data = await getPublicProfile(id)
  if (!data) return { title: 'Profil introuvable' }
  const { profile } = data
  return {
    title: `${profile.prenom} ${profile.nom} - Profil AlgoCodeBF`,
    description:
      profile.bio ||
      `${profile.prenom} ${profile.nom} — ${profile.university ?? 'Membre de AlgoCodeBF'}`,
  }
}

function formatMonthYear(iso: string): string {
  return new Date(iso).toLocaleDateString('fr-FR', {
    month: 'short',
    year: 'numeric',
  })
}

export default async function UserProfilePage({ params }: PageProps) {
  const { id } = await params
  const data = await getPublicProfile(id)
  if (!data) notFound()

  const { profile, counts, badges, skills } = data
  const me = await currentProfile()
  const isOwnProfile = me?.id === profile.id
  const initialFollowing =
    me && me.id !== profile.id ? await isFollowing(me.id, profile.id) : false

  const [posts, tutorials, projects] = await Promise.all([
    getUserPosts(profile.id, 12),
    getUserTutorials(profile.id, 12),
    getUserProjects(profile.id, 12),
  ])

  const initials =
    (profile.prenom?.charAt(0) ?? '').toUpperCase() +
    (profile.nom?.charAt(0) ?? '').toUpperCase()

  const tabs = [
    {
      id: 'posts',
      label: 'Discussions',
      icon: 'fa-comments',
      content:
        posts.length > 0 ? (
          <div className="activity-list">
            {posts.map(p => (
              <div key={p.id} className="activity-card">
                <div className="activity-icon">
                  <i className="fas fa-comment-dots"></i>
                </div>
                <div className="activity-content">
                  <h4 className="activity-title">
                    <Link href={`/forum/${p.id}`}>{p.title}</Link>
                  </h4>
                  <p className="activity-excerpt">
                    {(p.body ?? '').slice(0, 120)}
                    {(p.body ?? '').length > 120 ? '...' : ''}
                  </p>
                  <div className="activity-meta">
                    {p.category && (
                      <span className="category-badge">{p.category}</span>
                    )}
                    <span>
                      <i className="fas fa-clock"></i> {timeAgo(p.created_at)}
                    </span>
                    <span>
                      <i className="fas fa-eye"></i> {p.views ?? 0} vues
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="empty-state-activity">
            <i className="fas fa-comments"></i>
            <h3>Aucune discussion</h3>
            <p>Cet utilisateur n&apos;a pas encore publié de discussion</p>
          </div>
        ),
    },
    {
      id: 'tutorials',
      label: 'Formations',
      icon: 'fa-book',
      content:
        tutorials.length > 0 ? (
          <div className="activity-list">
            {tutorials.map(t => (
              <div key={t.id} className="activity-card">
                <div className="activity-icon tutorial-icon">
                  <i className="fas fa-graduation-cap"></i>
                </div>
                <div className="activity-content">
                  <h4 className="activity-title">
                    <Link href={`/formations/${t.id}`}>{t.title}</Link>
                  </h4>
                  <p className="activity-excerpt">
                    {(t.description ?? '').slice(0, 120)}
                    {(t.description ?? '').length > 120 ? '...' : ''}
                  </p>
                  <div className="activity-meta">
                    <span>
                      <i className="fas fa-eye"></i> {t.views ?? 0} vues
                    </span>
                    <span>
                      <i className="fas fa-heart"></i> {t.likes_count ?? 0}{' '}
                      likes
                    </span>
                    <span>
                      <i className="fas fa-clock"></i>{' '}
                      {timeAgo(t.created_at)}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="empty-state-activity">
            <i className="fas fa-book"></i>
            <h3>Aucune formation</h3>
            <p>Cet utilisateur n&apos;a pas encore publié de formation</p>
          </div>
        ),
    },
    {
      id: 'projects',
      label: 'Projets',
      icon: 'fa-project-diagram',
      content:
        projects.length > 0 ? (
          <div className="activity-list">
            {projects.map(pr => (
              <div key={pr.id} className="activity-card">
                <div className="activity-icon project-icon">
                  <i className="fas fa-folder-open"></i>
                </div>
                <div className="activity-content">
                  <h4 className="activity-title">
                    <Link href={`/project/${pr.id}`}>{pr.title}</Link>
                  </h4>
                  <p className="activity-excerpt">
                    {(pr.description ?? '').slice(0, 120)}
                    {(pr.description ?? '').length > 120 ? '...' : ''}
                  </p>
                  <div className="activity-meta">
                    <span
                      className={`status-badge-profile status-${pr.status}`}
                    >
                      {pr.status.charAt(0).toUpperCase() + pr.status.slice(1)}
                    </span>
                    <span>
                      <i className="fas fa-clock"></i>{' '}
                      {timeAgo(pr.created_at)}
                    </span>
                    {pr.looking_for_members && (
                      <span
                        style={{
                          color: 'var(--secondary-color)',
                          fontWeight: 600,
                        }}
                      >
                        <i className="fas fa-user-plus"></i> Recrute
                      </span>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="empty-state-activity">
            <i className="fas fa-project-diagram"></i>
            <h3>Aucun projet</h3>
            <p>Cet utilisateur n&apos;a pas encore de projet</p>
          </div>
        ),
    },
  ]

  return (
    <section className="profile-section">
      <div className="container">
        <div className="profile-header-wrapper">
          <div className="profile-cover">
            <div className="cover-pattern"></div>
          </div>

          <div className="profile-header-content">
            <div className="profile-avatar-wrapper">
              <div className="profile-avatar">
                {profile.photo_path ? (
                  <img
                    src={buildAvatarUrl(profile.photo_path)}
                    alt={`${profile.prenom} ${profile.nom}`}
                  />
                ) : (
                  <div className="avatar-placeholder-profile">
                    {initials}
                  </div>
                )}
              </div>
            </div>

            <div className="profile-info-main">
              <h1 className="profile-name">
                {profile.prenom} {profile.nom}
                {badges.length > 0 && (
                  <span className="verified-badge" title="Membre vérifié">
                    <i className="fas fa-check-circle"></i>
                  </span>
                )}
              </h1>
              <p className="profile-role">{profile.faculty ?? 'Membre'}</p>

              <div className="profile-meta-info">
                {profile.university && (
                  <span className="meta-item">
                    <i className="fas fa-university"></i>
                    {profile.university}
                  </span>
                )}
                {profile.city && (
                  <span className="meta-item">
                    <i className="fas fa-map-marker-alt"></i>
                    {profile.city}
                  </span>
                )}
                <span className="meta-item">
                  <i className="fas fa-calendar-alt"></i>
                  Membre depuis {formatMonthYear(profile.created_at)}
                </span>
              </div>
            </div>

            <div className="profile-actions-wrapper">
              {isOwnProfile ? (
                <Link
                  href="/user/modifier"
                  className="btn-action btn-primary-action"
                >
                  <i className="fas fa-edit"></i>
                  <span>Modifier</span>
                </Link>
              ) : (
                me && (
                  <>
                    <Link
                      href={`/message/composer?receiver=${profile.id}`}
                      className="btn-action btn-primary-action"
                    >
                      <i className="fas fa-envelope"></i>
                      <span>Message</span>
                    </Link>
                    <FollowButtonPhp
                      targetId={profile.id}
                      initialFollowing={initialFollowing}
                    />
                  </>
                )
              )}
            </div>
          </div>
        </div>

        <div className="stats-grid-profile">
          <div className="stat-card-profile stat-posts">
            <div className="stat-icon-wrapper">
              <i className="fas fa-comments"></i>
            </div>
            <div className="stat-content">
              <div className="stat-number">{counts.posts}</div>
              <div className="stat-label">Discussions</div>
            </div>
          </div>
          <div className="stat-card-profile stat-tutorials">
            <div className="stat-icon-wrapper">
              <i className="fas fa-book"></i>
            </div>
            <div className="stat-content">
              <div className="stat-number">{counts.tutorials}</div>
              <div className="stat-label">Formations</div>
            </div>
          </div>
          <div className="stat-card-profile stat-likes">
            <div className="stat-icon-wrapper">
              <i className="fas fa-users"></i>
            </div>
            <div className="stat-content">
              <div className="stat-number">{counts.followers}</div>
              <div className="stat-label">Abonnés</div>
            </div>
          </div>
          <div className="stat-card-profile stat-reputation">
            <div className="stat-icon-wrapper">
              <i className="fas fa-project-diagram"></i>
            </div>
            <div className="stat-content">
              <div className="stat-number">{counts.projects}</div>
              <div className="stat-label">Projets</div>
            </div>
          </div>
        </div>

        <div className="profile-content-grid">
          <aside className="profile-sidebar-modern">
            <div className="sidebar-card">
              <div className="card-header-modern">
                <i className="fas fa-user-circle"></i>
                <h3>À propos</h3>
              </div>
              <div className="card-body-modern">
                {profile.bio ? (
                  <p
                    className="bio-text"
                    style={{ whiteSpace: 'pre-wrap' }}
                  >
                    {profile.bio}
                  </p>
                ) : (
                  <p className="text-muted" style={{ color: '#6c757d' }}>
                    Aucune bio disponible
                  </p>
                )}
              </div>
            </div>

            {skills.length > 0 && (
              <div className="sidebar-card">
                <div className="card-header-modern">
                  <i className="fas fa-code"></i>
                  <h3>Compétences</h3>
                </div>
                <div className="card-body-modern">
                  <div className="skills-grid-modern">
                    {skills.map((s, i) => {
                      const row = s as unknown as {
                        level: string | null
                        skills: { name: string } | null
                      }
                      if (!row.skills) return null
                      const level = (row.level ?? 'debutant').toLowerCase()
                      return (
                        <div key={i} className="skill-badge-modern">
                          <span className="skill-name-modern">
                            {row.skills.name}
                          </span>
                          <span
                            className={`skill-level-modern level-${level}`}
                          >
                            {row.level ?? 'Débutant'}
                          </span>
                        </div>
                      )
                    })}
                  </div>
                </div>
              </div>
            )}

            {badges.length > 0 && (
              <div className="sidebar-card">
                <div className="card-header-modern">
                  <i className="fas fa-certificate"></i>
                  <h3>Badges</h3>
                </div>
                <div className="card-body-modern">
                  <div className="badges-grid-modern">
                    {badges.map((b, i) => {
                      const row = b as unknown as {
                        badges: {
                          name: string
                          description: string | null
                          icon: string | null
                        } | null
                      }
                      if (!row.badges) return null
                      return (
                        <div
                          key={i}
                          className="badge-card-modern"
                          title={row.badges.description ?? ''}
                        >
                          <div className="badge-icon-large">
                            {row.badges.icon ?? '🏆'}
                          </div>
                          <div className="badge-name-modern">
                            {row.badges.name}
                          </div>
                        </div>
                      )
                    })}
                  </div>
                </div>
              </div>
            )}

            {profile.cv_path && (
              <div className="sidebar-card cv-card">
                <div className="card-header-modern">
                  <i className="fas fa-file-pdf"></i>
                  <h3>Curriculum Vitae</h3>
                </div>
                <div className="card-body-modern">
                  <a
                    href={buildAvatarUrl(profile.cv_path)}
                    target="_blank"
                    rel="noreferrer"
                    className="btn-download-cv"
                  >
                    <i className="fas fa-download"></i>
                    <span>Télécharger le CV</span>
                  </a>
                </div>
              </div>
            )}

            <div className="sidebar-card">
              <div className="card-header-modern">
                <i className="fas fa-address-card"></i>
                <h3>Contact</h3>
              </div>
              <div className="card-body-modern">
                <div className="contact-info-list">
                  {profile.phone && (
                    <div className="contact-item">
                      <i className="fas fa-phone"></i>
                      <span>{profile.phone}</span>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </aside>

          <ProfileTabs tabs={tabs} />
        </div>
      </div>
    </section>
  )
}
