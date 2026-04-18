import type { Metadata } from 'next'
import Link from 'next/link'
import { listProjects } from '@/lib/queries/projects'
import { currentProfile } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { ProjectLiveSearchField } from '@/components/project/project-live-search-field'
import { buildAvatarUrl, timeAgo } from '@/lib/utils'
import type { ProjectStatus } from '@/types'

export const metadata: Metadata = {
  title: 'Projets collaboratifs',
  description:
    'Rejoignez des projets innovants ou lancez le vôtre et collaborez avec la communauté tech burkinabè.',
}

interface PageProps {
  searchParams: Promise<{ page?: string; status?: string; q?: string; recruiting?: string }>
}

const STATUS_LABELS: Record<string, { icon: string; label: string }> = {
  planning: { icon: 'fas fa-clipboard-list', label: 'Planification' },
  in_progress: { icon: 'fas fa-spinner', label: 'En cours' },
  'in-progress': { icon: 'fas fa-spinner', label: 'En cours' },
  completed: { icon: 'fas fa-check-circle', label: 'Terminé' },
  'on-hold': { icon: 'fas fa-pause-circle', label: 'En pause' },
  paused: { icon: 'fas fa-pause-circle', label: 'En pause' },
  active: { icon: 'fas fa-rocket', label: 'Actif' },
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
  'Swift',
  'Kotlin',
  'Next.js',
  'Supabase',
  'TypeScript',
]

function statusCssClass(status: string | null | undefined): string {
  const s = (status ?? 'planning').toLowerCase().replace(/_/g, '-')
  return `status-${s}`
}

export default async function ProjectsPage({ searchParams }: PageProps) {
  const params = await searchParams
  const page = Math.max(1, Number(params.page ?? 1))
  const status = (params.status as ProjectStatus | '' | undefined) || null
  const search = params.q?.trim() || undefined
  const recruiting = params.recruiting === '1'

  const [profile, { projects, totalPages }, supabase] = await Promise.all([
    currentProfile(),
    listProjects({
      status: status || undefined,
      search,
      recruiting,
      page,
      pageSize: 12,
    }),
    createClient(),
  ])

  const [
    { count: totalProjects },
    { count: totalMembers },
    { count: completed },
  ] = await Promise.all([
    supabase.from('projects').select('id', { count: 'exact', head: true }).eq('visibility', 'public'),
    supabase.from('project_members').select('id', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('projects').select('id', { count: 'exact', head: true }).eq('status', 'completed').eq('visibility', 'public'),
  ])

  const canCreate = Boolean(profile?.can_create_project) || profile?.role === 'admin'

  const buildHref = (
    overrides: Partial<{ page: string; status: string; q: string; recruiting: string }> = {}
  ) => {
    const merged = {
      q: overrides.q !== undefined ? overrides.q : params.q,
      status: overrides.status !== undefined ? overrides.status : params.status,
      recruiting:
        overrides.recruiting !== undefined ? overrides.recruiting : params.recruiting,
      page: overrides.page !== undefined ? overrides.page : params.page,
    }
    const sp = new URLSearchParams()
    if (merged.q) sp.set('q', merged.q)
    if (merged.status) sp.set('status', merged.status)
    if (merged.recruiting === '1') sp.set('recruiting', '1')
    if (merged.page && merged.page !== '1') sp.set('page', merged.page)
    const qs = sp.toString()
    return qs ? `/project?${qs}` : '/project'
  }

  return (
    <div className="project-saas">
      <section className="pr-hero">
        <div className="container">
          <div className="pr-hero-inner">
            <p className="pr-eyebrow">
              <i className="fas fa-code-branch" aria-hidden="true"></i> Open collab · BF
            </p>
            <h1>
              Projets <span>collaboratifs</span>
            </h1>
            <p>
              Lancez une idée, rejoignez une équipe ou recrutez des contributeurs — la communauté tech
              burkinabè au centre.
            </p>
            <div className="pr-hero-actions">
              {canCreate && (
                <Link href="/project/creer" className="pr-btn-primary">
                  <i className="fas fa-plus" aria-hidden="true"></i> Créer un projet
                </Link>
              )}
              <Link href="/forum" className="pr-btn-secondary">
                <i className="fas fa-comments" aria-hidden="true"></i> Échanger sur le forum
              </Link>
            </div>
          </div>
        </div>
      </section>

      <section className="pr-stats" aria-label="Statistiques projets">
        <div className="container">
          <div className="pr-stats-grid">
            <div className="pr-stat">
              <i className="fas fa-folder-open" aria-hidden="true"></i>
              <strong>{totalProjects ?? 0}</strong>
              <span>Projets publics</span>
            </div>
            <div className="pr-stat">
              <i className="fas fa-users" aria-hidden="true"></i>
              <strong>{totalMembers ?? 0}</strong>
              <span>Collaborateurs</span>
            </div>
            <div className="pr-stat">
              <i className="fas fa-check-circle" aria-hidden="true"></i>
              <strong>{completed ?? 0}</strong>
              <span>Terminés</span>
            </div>
            <div className="pr-stat">
              <i className="fas fa-layer-group" aria-hidden="true"></i>
              <strong>{TECH_KEYWORDS.length}+</strong>
              <span>Technos listées</span>
            </div>
          </div>
        </div>
      </section>

      <section className="pr-main">
        <div className="container">
          <div className="pr-results-meta" role="status">
            <span className="pr-results-count">
              <strong>{projects.length}</strong>
              <span>
                projet{projects.length !== 1 ? 's' : ''}
                {totalPages > 1 ? ` · page ${page} / ${totalPages}` : ''}
              </span>
            </span>
            {(status || recruiting || search) && (
              <Link href="/project" className="pr-results-reset">
                Réinitialiser les filtres
              </Link>
            )}
          </div>

          <div className="pr-tabs-wrap">
            <nav className="pr-tabs" aria-label="Filtres rapides">
              <Link
                href={buildHref({ status: '', recruiting: '', page: '1' })}
                className={`pr-tab${!status && !recruiting ? ' active' : ''}`}
              >
                <i className="fas fa-globe" aria-hidden="true"></i> Tous
              </Link>
              <Link
                href={buildHref({ recruiting: '1', status: '', page: '1' })}
                className={`pr-tab${recruiting ? ' active' : ''}`}
              >
                <i className="fas fa-user-plus" aria-hidden="true"></i> Recrutent
              </Link>
              <Link
                href={buildHref({ status: 'in_progress', recruiting: '', page: '1' })}
                className={`pr-tab${status === 'in_progress' && !recruiting ? ' active' : ''}`}
              >
                <i className="fas fa-spinner" aria-hidden="true"></i> En cours
              </Link>
              <Link
                href={buildHref({ status: 'completed', recruiting: '', page: '1' })}
                className={`pr-tab${status === 'completed' && !recruiting ? ' active' : ''}`}
              >
                <i className="fas fa-check" aria-hidden="true"></i> Terminés
              </Link>
            </nav>
          </div>

          <div className="pr-search">
            <div className="pr-search-card">
              <form method="GET" action="/project" className="pr-search-form">
                <ProjectLiveSearchField initialQ={search ?? ''} />
                {status && <input type="hidden" name="status" value={status} />}
                {recruiting && <input type="hidden" name="recruiting" value="1" />}
                <button type="submit" className="pr-search-submit">
                  <i className="fas fa-search" aria-hidden="true"></i> Rechercher
                </button>
              </form>
            </div>
          </div>

          <div className="pr-grid">
            {projects.length === 0 ? (
              <div className="pr-empty">
                <i className="fas fa-project-diagram" aria-hidden="true"></i>
                <h3>Aucun projet pour ces critères</h3>
                <p>Élargissez la recherche ou soyez le premier à lancer un projet collaboratif.</p>
                {canCreate && (
                  <Link href="/project/creer" className="pr-btn-primary">
                    <i className="fas fa-plus" aria-hidden="true"></i> Créer un projet
                  </Link>
                )}
              </div>
            ) : (
              projects.map(project => {
                const statusInfo = STATUS_LABELS[project.status ?? 'planning'] ?? {
                  icon: 'fas fa-clipboard-list',
                  label: project.status ?? 'Planification',
                }
                const desc = project.description ?? ''
                const foundTechs = TECH_KEYWORDS.filter(t =>
                  desc.toLowerCase().includes(t.toLowerCase())
                )
                const owner = project.owner
                const ownerName = owner ? `${owner.prenom} ${owner.nom}` : 'Utilisateur'
                const ownerInitial = ownerName.charAt(0).toUpperCase()
                const stClass = statusCssClass(project.status)

                return (
                  <article key={project.id} className="pr-card">
                    {project.looking_for_members && (
                      <div className="pr-ribbon">
                        <i className="fas fa-bullhorn" aria-hidden="true"></i> On recrute
                      </div>
                    )}

                    <div className="pr-card-top">
                      <span className={`pr-status ${stClass}`}>
                        <i className={statusInfo.icon} aria-hidden="true"></i> {statusInfo.label}
                      </span>
                      <span className="pr-vis" title={project.visibility === 'public' ? 'Public' : 'Privé'}>
                        <i
                          className={`fas fa-${project.visibility === 'public' ? 'globe' : 'lock'}`}
                          aria-hidden="true"
                        ></i>
                      </span>
                    </div>

                    <div className="pr-card-body">
                      <h2 className="pr-title">
                        <Link href={`/project/${project.id}`}>{project.title}</Link>
                      </h2>
                      <p className="pr-desc">
                        {desc.slice(0, 140)}
                        {desc.length > 140 ? '…' : ''}
                      </p>

                      {foundTechs.length > 0 && (
                        <div className="pr-techs">
                          {foundTechs.slice(0, 4).map(t => (
                            <span key={t} className="pr-tech">
                              {t}
                            </span>
                          ))}
                          {foundTechs.length > 4 && (
                            <span className="pr-tech more">+{foundTechs.length - 4}</span>
                          )}
                        </div>
                      )}
                    </div>

                    <div className="pr-owner-row">
                      <div className="pr-owner">
                        <div className="pr-avatar">
                          {owner?.photo_path ? (
                            // eslint-disable-next-line @next/next/no-img-element
                            <img src={buildAvatarUrl(owner.photo_path)} alt="" />
                          ) : (
                            <span aria-hidden="true">{ownerInitial}</span>
                          )}
                        </div>
                        <div className="pr-owner-text">
                          <small>Créé par</small>
                          <strong>{ownerName}</strong>
                        </div>
                      </div>
                      <span className="pr-date">
                        <i className="far fa-clock" aria-hidden="true"></i> {timeAgo(project.created_at)}
                      </span>
                    </div>

                    <div className="pr-footer">
                      <div className="pr-badges">
                        <span className="pr-badge" title="Membres">
                          <i className="fas fa-users" aria-hidden="true"></i> {project.members_count}
                        </span>
                        {project.github_link && (
                          <span className="pr-badge github" title="GitHub">
                            <i className="fab fa-github" aria-hidden="true"></i>
                          </span>
                        )}
                        {project.demo_link && (
                          <span className="pr-badge demo" title="Démo">
                            <i className="fas fa-rocket" aria-hidden="true"></i>
                          </span>
                        )}
                      </div>
                      <Link href={`/project/${project.id}`} className="pr-btn-view">
                        Découvrir <i className="fas fa-arrow-right" aria-hidden="true"></i>
                      </Link>
                    </div>
                  </article>
                )
              })
            )}
          </div>

          {totalPages > 1 && (
            <nav className="pr-pagination" aria-label="Pagination">
              {page > 1 ? (
                <Link href={buildHref({ page: String(page - 1) })} className="pr-page-link">
                  <i className="fas fa-chevron-left" aria-hidden="true"></i> Précédent
                </Link>
              ) : null}
              <span className="pr-page-info">
                Page {page} / {totalPages}
              </span>
              {page < totalPages ? (
                <Link href={buildHref({ page: String(page + 1) })} className="pr-page-link">
                  Suivant <i className="fas fa-chevron-right" aria-hidden="true"></i>
                </Link>
              ) : null}
            </nav>
          )}
        </div>
      </section>
    </div>
  )
}
