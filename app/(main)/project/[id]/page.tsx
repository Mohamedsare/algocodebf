import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { getProjectDetail } from '@/lib/queries/projects'
import { currentProfile } from '@/lib/auth'
import { CommentsSection } from '@/components/shared/comments-section'
import { JoinProjectButton } from '@/components/project/join-project-button'
import { ShareButton } from '@/components/shared/share-button'
import { buildAvatarUrl, timeAgo } from '@/lib/utils'

const STATUS_LABELS: Record<string, string> = {
  planning: '📋 Planification',
  in_progress: '🚀 En cours',
  'in-progress': '🚀 En cours',
  active: '🚀 Actif',
  completed: '✅ Terminé',
  'on-hold': '⏸️ En pause',
  paused: '⏸️ En pause',
}

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
  const foundTechs = TECH_KEYWORDS.filter(t =>
    desc.toLowerCase().includes(t.toLowerCase())
  )
  const ownerName = owner ? `${owner.prenom} ${owner.nom}` : 'Utilisateur'
  const ownerInitial = ownerName.charAt(0).toUpperCase()
  const statusLabel =
    STATUS_LABELS[project.status ?? 'planning'] ??
    `📋 ${project.status ?? 'Planification'}`

  return (
    <section className="project-show-section">
      <div className="container">
        <div className="breadcrumb-nav">
          <Link href="/">
            <i className="fas fa-home"></i> Accueil
          </Link>
          <i className="fas fa-chevron-right"></i>
          <Link href="/project">Projets</Link>
          <i className="fas fa-chevron-right"></i>
          <span>{project.title}</span>
        </div>

        <div className="project-layout">
          <div className="project-main">
            <article className="project-header">
              <div className="project-status-top">
                <span
                  className={`project-status status-${(project.status ?? 'active').toLowerCase()}`}
                >
                  {statusLabel}
                </span>
                {project.looking_for_members && (
                  <span className="badge-recruiting">
                    <i className="fas fa-user-plus"></i> Recherche de membres
                  </span>
                )}
              </div>

              <h1 className="project-title">{project.title}</h1>
              <p className="project-description">
                {project.description || 'Aucune description'}
              </p>

              <div className="owner-section">
                <div className="owner-info">
                  <div className="owner-avatar">
                    {owner?.photo_path ? (
                      <img src={buildAvatarUrl(owner.photo_path)} alt={ownerName} />
                    ) : (
                      <div className="avatar-placeholder">{ownerInitial}</div>
                    )}
                  </div>
                  <div className="owner-details">
                    <p className="owner-label">Créé par</p>
                    <p className="owner-name">
                      <strong>{ownerName}</strong>
                    </p>
                    <p className="project-date">
                      <i className="far fa-clock"></i> {timeAgo(project.created_at)}
                    </p>
                  </div>
                </div>

                <div className="project-stats-quick">
                  <div className="stat-item-quick">
                    <i className="fas fa-users"></i>
                    <span>
                      {activeMembers.length} membre
                      {activeMembers.length > 1 ? 's' : ''}
                    </span>
                  </div>
                </div>
              </div>

              <div className="project-actions">
                {profile && (
                  <>
                    {isOwner ? (
                      <Link
                        href={`/project/${project.id}/modifier`}
                        className="btn-action btn-edit"
                      >
                        <i className="fas fa-edit"></i> Modifier
                      </Link>
                    ) : !myMembership && project.looking_for_members ? (
                      <JoinProjectButton projectId={project.id} hasPending={false} />
                    ) : myMembership?.status === 'pending' ? (
                      <button className="btn-action" disabled>
                        <i className="fas fa-hourglass-half"></i> Demande en attente…
                      </button>
                    ) : null}
                  </>
                )}
                <ShareButton
                  className="btn-action"
                  title={project.title}
                  text={desc.slice(0, 120)}
                >
                  <i className="fas fa-share-alt"></i> Partager
                </ShareButton>
              </div>
            </article>

            {foundTechs.length > 0 && (
              <div className="project-technologies">
                <h3>
                  <i className="fas fa-tools"></i> Technologies détectées
                </h3>
                <div className="tech-tags">
                  {foundTechs.map(t => (
                    <span key={t} className="tech-tag">
                      {t}
                    </span>
                  ))}
                </div>
              </div>
            )}

            {(project.github_link || project.demo_link) && (
              <div className="project-links">
                <h3>
                  <i className="fas fa-link"></i> Liens du projet
                </h3>
                <div className="links-grid">
                  {project.github_link && (
                    <a
                      href={project.github_link}
                      className="link-card github"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <i className="fab fa-github"></i>
                      <div>
                        <strong>GitHub Repository</strong>
                        <small>Code source</small>
                      </div>
                      <i className="fas fa-external-link-alt"></i>
                    </a>
                  )}
                  {project.demo_link && (
                    <a
                      href={project.demo_link}
                      className="link-card demo"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <i className="fas fa-rocket"></i>
                      <div>
                        <strong>Démo en ligne</strong>
                        <small>Voir le projet</small>
                      </div>
                      <i className="fas fa-external-link-alt"></i>
                    </a>
                  )}
                </div>
              </div>
            )}

            <div className="members-section">
              <h3>
                <i className="fas fa-users"></i> Membres du projet (
                {activeMembers.length})
              </h3>
              {activeMembers.length === 0 ? (
                <div className="empty-state">
                  <i className="fas fa-users-slash"></i>
                  <p>Aucun membre pour le moment</p>
                </div>
              ) : (
                <div className="members-grid">
                  {activeMembers.map(m => {
                    const mName = m.member
                      ? `${m.member.prenom} ${m.member.nom}`
                      : 'Anonyme'
                    const mInitial = (m.member?.prenom ?? 'U')
                      .charAt(0)
                      .toUpperCase()
                    return (
                      <div key={m.id} className="member-card">
                        <div className="member-avatar-container">
                          {m.member?.photo_path ? (
                            <img
                              src={buildAvatarUrl(m.member.photo_path)}
                              alt={mName}
                              className="member-avatar"
                            />
                          ) : (
                            <div className="member-avatar-placeholder">
                              {mInitial}
                            </div>
                          )}
                          {m.user_id === project.owner_id && (
                            <span
                              className="owner-badge"
                              title="Créateur du projet"
                            >
                              👑
                            </span>
                          )}
                        </div>
                        <div className="member-info">
                          <h5>
                            {m.member ? (
                              <Link href={`/user/${m.member.id}`}>{mName}</Link>
                            ) : (
                              mName
                            )}
                          </h5>
                          <p className="member-role">{m.role}</p>
                          <p className="member-joined">
                            <i className="fas fa-calendar"></i> Rejoint{' '}
                            {m.joined_at ? timeAgo(m.joined_at) : '—'}
                          </p>
                        </div>
                      </div>
                    )
                  })}
                </div>
              )}
            </div>

            <div id="comments" className="comments-section">
              <CommentsSection
                commentableType="project"
                commentableId={project.id}
                profile={profile}
              />
            </div>
          </div>

          <aside className="project-sidebar">
            <div className="sidebar-card info-card">
              <h4>
                <i className="fas fa-info-circle"></i> Informations
              </h4>
              <div className="info-list">
                <div className="info-item">
                  <span className="info-label">
                    <i className="fas fa-flag"></i> Statut
                  </span>
                  <span className="info-value">
                    {(project.status ?? 'planning').charAt(0).toUpperCase() +
                      (project.status ?? 'planning').slice(1)}
                  </span>
                </div>
                <div className="info-item">
                  <span className="info-label">
                    <i className="fas fa-calendar"></i> Créé
                  </span>
                  <span className="info-value">
                    {new Date(project.created_at).toLocaleDateString('fr-FR')}
                  </span>
                </div>
                <div className="info-item">
                  <span className="info-label">
                    <i className="fas fa-users"></i> Membres
                  </span>
                  <span className="info-value">{activeMembers.length}</span>
                </div>
                <div className="info-item">
                  <span className="info-label">
                    <i className="fas fa-eye"></i> Visibilité
                  </span>
                  <span className="info-value">
                    {(project.visibility ?? 'public').charAt(0).toUpperCase() +
                      (project.visibility ?? 'public').slice(1)}
                  </span>
                </div>
              </div>
            </div>

            {project.looking_for_members && (
              <div className="sidebar-card recruiting-card">
                <h4>
                  <i className="fas fa-bullhorn"></i> On recrute !
                </h4>
                <p>
                  Ce projet recherche activement de nouveaux membres pour
                  contribuer.
                </p>
                {!myMembership && profile && !isOwner && (
                  <JoinProjectButton projectId={project.id} hasPending={false} />
                )}
              </div>
            )}
          </aside>
        </div>
      </div>
    </section>
  )
}
