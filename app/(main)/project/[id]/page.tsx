import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { getProjectDetail } from '@/lib/queries/projects'
import { currentProfile } from '@/lib/auth'
import { CommentsSection } from '@/components/shared/comments-section'
import { JoinProjectButton } from '@/components/project/join-project-button'
import { ShareButton } from '@/components/shared/share-button'
import { buildAvatarUrl, formatNumber, timeAgo } from '@/lib/utils'

const TECH_KEYWORDS = [
  'PHP',
  'JavaScript',
  'React',
  'Vue',
  'Angular',
  'Node',
  'Python',
  'Java',
  'Flutter',
  'Dart',
  'MySQL',
  'PostgreSQL',
  'MongoDB',
  'Firebase',
  'Laravel',
  'Django',
  'Spring',
  'Next.js',
  'TypeScript',
  'Supabase',
]

const STATUS_LABELS: Record<string, { icon: string; label: string }> = {
  planning: { icon: 'fas fa-clipboard-list', label: 'Planification' },
  in_progress: { icon: 'fas fa-spinner', label: 'En cours' },
  'in-progress': { icon: 'fas fa-spinner', label: 'En cours' },
  completed: { icon: 'fas fa-check-circle', label: 'Terminé' },
  'on-hold': { icon: 'fas fa-pause-circle', label: 'En pause' },
  paused: { icon: 'fas fa-pause-circle', label: 'En pause' },
  active: { icon: 'fas fa-rocket', label: 'Actif' },
}

function statusCssClass(status: string | null | undefined): string {
  const s = (status ?? 'planning').toLowerCase().replace(/_/g, '-')
  return `pd-status pd-status--${s}`
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ id: string }>
}): Promise<Metadata> {
  const { id } = await params
  const detail = await getProjectDetail(Number(id))
  if (!detail) return { title: 'Projet introuvable' }
  return {
    title: `${detail.project.title} - Projets`,
    description: detail.project.description?.slice(0, 160) ?? undefined,
  }
}

export default async function ProjectDetailPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const projectId = Number(id)
  if (Number.isNaN(projectId)) notFound()

  const profile = await currentProfile()
  const detail = await getProjectDetail(projectId, profile?.id ?? null)
  if (!detail) notFound()

  const { project, owner, members, myMembership, isOwner } = detail
  const activeMembers = members.filter(m => m.status === 'active')
  const desc = project.description ?? ''
  const foundTechs = TECH_KEYWORDS.filter(t => desc.toLowerCase().includes(t.toLowerCase()))
  const ownerName = owner ? `${owner.prenom} ${owner.nom}` : 'Utilisateur'
  const ownerInitial = ownerName.charAt(0).toUpperCase()
  const statusInfo = STATUS_LABELS[project.status ?? 'planning'] ?? {
    icon: 'fas fa-clipboard-list',
    label: project.status ?? 'Planification',
  }

  const visibilityLabel =
    project.visibility === 'public'
      ? 'Public'
      : project.visibility === 'private'
        ? 'Privé'
        : project.visibility ?? '—'

  return (
    <div className="project-detail-saas">
      <section className="pd-hero" aria-labelledby="pd-title">
        <div className="container">
          <nav className="pd-breadcrumb" aria-label="Fil d'Ariane">
            <Link href="/" className="pd-crumb">
              <i className="fas fa-home" aria-hidden />
              Accueil
            </Link>
            <span className="pd-crumb-sep" aria-hidden>
              <i className="fas fa-chevron-right" />
            </span>
            <Link href="/project" className="pd-crumb">
              Projets
            </Link>
            <span className="pd-crumb-sep" aria-hidden>
              <i className="fas fa-chevron-right" />
            </span>
            <span className="pd-crumb pd-crumb--current">{project.title}</span>
          </nav>

          <div className="pd-hero-card">
            <div className="pd-hero-top">
              <div className="pd-chips">
                <span className={statusCssClass(project.status)}>
                  <i className={statusInfo.icon} aria-hidden />
                  {statusInfo.label}
                </span>
                {project.looking_for_members && (
                  <span className="pd-chip pd-chip--recruit">
                    <i className="fas fa-user-plus" aria-hidden />
                    Recrute
                  </span>
                )}
                <span className="pd-chip pd-chip--muted" title="Visibilité">
                  <i
                    className={`fas fa-${project.visibility === 'public' ? 'globe' : 'lock'}`}
                    aria-hidden
                  />
                  {visibilityLabel}
                </span>
              </div>
            </div>

            <h1 id="pd-title" className="pd-title">
              {project.title}
            </h1>
            <p className="pd-lead">{project.description || 'Aucune description pour ce projet.'}</p>

            <div className="pd-owner-bar">
              <div className="pd-owner">
                <div className="pd-owner-avatar">
                  {owner?.photo_path ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img src={buildAvatarUrl(owner.photo_path)} alt="" />
                  ) : (
                    <span aria-hidden="true">{ownerInitial}</span>
                  )}
                </div>
                <div className="pd-owner-meta">
                  <span className="pd-owner-label">Porteur du projet</span>
                  <strong className="pd-owner-name">{ownerName}</strong>
                  <span className="pd-owner-when">
                    <i className="far fa-clock" aria-hidden />
                    {timeAgo(project.created_at)}
                  </span>
                </div>
              </div>
              <div className="pd-quick-stats">
                <div className="pd-mini-stat">
                  <i className="fas fa-users" aria-hidden />
                  <span>
                    <strong>{formatNumber(activeMembers.length)}</strong>
                    <small>membre{activeMembers.length !== 1 ? 's' : ''}</small>
                  </span>
                </div>
              </div>
            </div>

            <div className="pd-actions">
              {profile && (
                <>
                  {isOwner ? (
                    <Link href={`/project/${project.id}/modifier`} className="pd-btn pd-btn--primary">
                      <i className="fas fa-edit" aria-hidden />
                      Modifier
                    </Link>
                  ) : !myMembership && project.looking_for_members ? (
                    <JoinProjectButton
                      projectId={project.id}
                      hasPending={false}
                      triggerClassName="pd-btn pd-btn--accent"
                    />
                  ) : myMembership?.status === 'pending' ? (
                    <button type="button" className="pd-btn pd-btn--ghost" disabled>
                      <i className="fas fa-hourglass-half" aria-hidden />
                      Demande en attente…
                    </button>
                  ) : null}
                </>
              )}
              <ShareButton
                className="pd-btn pd-btn--ghost"
                title={project.title}
                text={desc.slice(0, 120)}
              >
                <i className="fas fa-share-alt" aria-hidden />
                Partager
              </ShareButton>
            </div>
          </div>
        </div>
      </section>

      <div className="pd-content">
        <div className="container">
          <div className="pd-layout">
            <div className="pd-main">
              {foundTechs.length > 0 && (
                <section className="pd-panel" aria-labelledby="pd-tech-heading">
                  <h2 id="pd-tech-heading" className="pd-panel-title">
                    <i className="fas fa-layer-group" aria-hidden />
                    Technologies
                  </h2>
                  <div className="pd-tech-grid">
                    {foundTechs.map(t => (
                      <span key={t} className="pd-tech-pill">
                        {t}
                      </span>
                    ))}
                  </div>
                </section>
              )}

              {(project.github_link || project.demo_link) && (
                <section className="pd-panel" aria-labelledby="pd-links-heading">
                  <h2 id="pd-links-heading" className="pd-panel-title">
                    <i className="fas fa-link" aria-hidden />
                    Liens & ressources
                  </h2>
                  <div className="pd-links">
                    {project.github_link && (
                      <a
                        href={project.github_link}
                        className="pd-link-card pd-link-card--github"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        <span className="pd-link-icon" aria-hidden>
                          <i className="fab fa-github" />
                        </span>
                        <span className="pd-link-text">
                          <strong>Code source</strong>
                          <small>GitHub</small>
                        </span>
                        <i className="fas fa-external-link-alt pd-link-arrow" aria-hidden />
                      </a>
                    )}
                    {project.demo_link && (
                      <a
                        href={project.demo_link}
                        className="pd-link-card pd-link-card--demo"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        <span className="pd-link-icon" aria-hidden>
                          <i className="fas fa-rocket" />
                        </span>
                        <span className="pd-link-text">
                          <strong>Démo en ligne</strong>
                          <small>Ouvrir le site / la démo</small>
                        </span>
                        <i className="fas fa-external-link-alt pd-link-arrow" aria-hidden />
                      </a>
                    )}
                  </div>
                </section>
              )}

              <section className="pd-panel" aria-labelledby="pd-members-heading">
                <h2 id="pd-members-heading" className="pd-panel-title">
                  <i className="fas fa-users" aria-hidden />
                  Équipe
                  <span className="pd-panel-count">{activeMembers.length}</span>
                </h2>
                {activeMembers.length === 0 ? (
                  <div className="pd-empty-inline">
                    <i className="fas fa-user-friends" aria-hidden />
                    <p>Aucun membre actif pour le moment. Rejoignez le projet ou invitez la communauté.</p>
                  </div>
                ) : (
                  <ul className="pd-member-list">
                    {activeMembers.map(m => {
                      const mName = m.member ? `${m.member.prenom} ${m.member.nom}` : 'Anonyme'
                      const mInitial = (m.member?.prenom ?? 'U').charAt(0).toUpperCase()
                      return (
                        <li key={m.id} className="pd-member">
                          <div className="pd-member-avatar-wrap">
                            {m.member?.photo_path ? (
                              // eslint-disable-next-line @next/next/no-img-element
                              <img
                                src={buildAvatarUrl(m.member.photo_path)}
                                alt=""
                                className="pd-member-photo"
                              />
                            ) : (
                              <span className="pd-member-placeholder" aria-hidden>
                                {mInitial}
                              </span>
                            )}
                            {m.user_id === project.owner_id && (
                              <span className="pd-member-crown" title="Créateur" aria-label="Créateur">
                                <i className="fas fa-crown" aria-hidden />
                              </span>
                            )}
                          </div>
                          <div className="pd-member-body">
                            <div className="pd-member-name">
                              {m.member ? (
                                <Link href={`/user/${m.member.id}`}>{mName}</Link>
                              ) : (
                                mName
                              )}
                            </div>
                            <div className="pd-member-role">{m.role}</div>
                            <div className="pd-member-joined">
                              <i className="fas fa-calendar-alt" aria-hidden />
                              {m.joined_at ? timeAgo(m.joined_at) : '—'}
                            </div>
                          </div>
                        </li>
                      )
                    })}
                  </ul>
                )}
              </section>

              <section id="comments" className="pd-panel pd-panel--flush" aria-label="Commentaires">
                <CommentsSection
                  commentableType="project"
                  commentableId={project.id}
                  profile={profile}
                />
              </section>
            </div>

            <aside className="pd-aside">
              <div className="pd-side-card">
                <h3 className="pd-side-title">
                  <i className="fas fa-info-circle" aria-hidden />
                  Résumé
                </h3>
                <dl className="pd-facts">
                  <div className="pd-fact">
                    <dt>Statut</dt>
                    <dd>{statusInfo.label}</dd>
                  </div>
                  <div className="pd-fact">
                    <dt>Créé le</dt>
                    <dd>{new Date(project.created_at).toLocaleDateString('fr-FR')}</dd>
                  </div>
                  <div className="pd-fact">
                    <dt>Membres</dt>
                    <dd>{formatNumber(activeMembers.length)}</dd>
                  </div>
                  <div className="pd-fact">
                    <dt>Visibilité</dt>
                    <dd>{visibilityLabel}</dd>
                  </div>
                </dl>
              </div>

              {project.looking_for_members && (
                <div className="pd-side-card pd-side-card--accent">
                  <h3 className="pd-side-title">
                    <i className="fas fa-bullhorn" aria-hidden />
                    Rejoindre l’équipe
                  </h3>
                  <p className="pd-side-text">
                    Ce projet accueille de nouveaux contributeurs. Présentez votre profil et votre motivation.
                  </p>
                  {!myMembership && profile && !isOwner && (
                    <JoinProjectButton
                      projectId={project.id}
                      hasPending={false}
                      triggerClassName="pd-btn pd-btn--accent pd-btn--block"
                    />
                  )}
                </div>
              )}

              <Link href="/project" className="pd-back-link">
                <i className="fas fa-arrow-left" aria-hidden />
                Tous les projets
              </Link>
            </aside>
          </div>
        </div>
      </div>
    </div>
  )
}
