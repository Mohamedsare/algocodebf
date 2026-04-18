import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { currentProfile } from '@/lib/auth'
import { buildFileUrl, timeAgo } from '@/lib/utils'
import { NewsletterSaas } from '@/components/blog/newsletter-saas'
import { JobLiveSearchField } from '@/components/job/job-live-search-field'

export const metadata: Metadata = {
  title: 'Opportunités - AlgoCodeBF',
  description: 'Découvrez stages, emplois, hackathons et formations au Burkina Faso',
}

const PAGE_SIZE = 12

const TYPES: Array<{ value: string; label: string; icon: string }> = [
  { value: '', label: 'Toutes', icon: 'fas fa-globe' },
  { value: 'stage', label: 'Stages', icon: 'fas fa-user-graduate' },
  { value: 'emploi', label: 'Emplois', icon: 'fas fa-briefcase' },
  { value: 'freelance', label: 'Freelance', icon: 'fas fa-laptop-code' },
  { value: 'hackathon', label: 'Hackathons', icon: 'fas fa-trophy' },
  { value: 'formation', label: 'Formations', icon: 'fas fa-chalkboard-teacher' },
]

const CITIES = ['Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Ouahigouya', 'Autre']

interface SearchParams {
  page?: string
  type?: string
  city?: string
  q?: string
}

function parseSkills(raw?: string | null): string[] {
  if (!raw) return []
  const trimmed = raw.trim()
  if (!trimmed) return []
  if (trimmed.startsWith('[')) {
    try {
      const arr = JSON.parse(trimmed)
      if (Array.isArray(arr)) return arr.map(String).map(s => s.trim()).filter(Boolean)
    } catch {
      // fallthrough
    }
  }
  return trimmed.split(',').map(s => s.trim()).filter(Boolean)
}

export default async function JobPage({
  searchParams,
}: {
  searchParams: Promise<SearchParams>
}) {
  const params = await searchParams
  const page = Math.max(1, parseInt(params.page ?? '1'))
  const type = params.type ?? ''
  const city = params.city ?? ''
  const search = params.q ?? ''
  const offset = (page - 1) * PAGE_SIZE

  const [supabase, profile] = await Promise.all([createClient(), currentProfile()])

  let query = supabase
    .from('jobs')
    .select(
      `id, title, description, type, city, salary, deadline, external_link,
       company_name, company_logo, skills_required, created_at, company_id`,
      { count: 'exact' }
    )
    .eq('status', 'active')

  if (type) query = query.eq('type', type)
  if (city) query = query.eq('city', city)
  if (search) {
    const p = `%${search}%`
    query = query.or(`title.ilike.${p},description.ilike.${p},company_name.ilike.${p}`)
  }

  query = query.order('created_at', { ascending: false }).range(offset, offset + PAGE_SIZE - 1)
  const { data: jobs, count } = await query

  const [
    { count: totalJobs },
    { count: companies },
    { count: hired },
    { count: newThisWeek },
  ] = await Promise.all([
    supabase.from('jobs').select('id', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('profiles').select('id', { count: 'exact', head: true }).eq('role', 'company'),
    supabase.from('applications').select('id', { count: 'exact', head: true }).eq('status', 'accepted'),
    supabase
      .from('jobs')
      .select('id', { count: 'exact', head: true })
      .eq('status', 'active')
      .gte('created_at', new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString()),
  ])

  const totalPages = Math.max(1, Math.ceil((count ?? 0) / PAGE_SIZE))
  const totalJobsValue = count ?? 0
  const canPublish = profile?.role === 'company' || profile?.role === 'admin'

  const buildHref = (overrides: Partial<SearchParams> = {}) => {
    const merged = { ...params, ...overrides }
    const sp = new URLSearchParams()
    if (merged.q) sp.set('q', merged.q)
    if (merged.type) sp.set('type', merged.type)
    if (merged.city) sp.set('city', merged.city)
    if (merged.page && merged.page !== '1') sp.set('page', merged.page)
    const qs = sp.toString()
    return qs ? `/job?${qs}` : '/job'
  }

  const startPage = Math.max(1, page - 2)
  const endPage = Math.min(totalPages, page + 2)
  const pageNumbers: number[] = []
  for (let i = startPage; i <= endPage; i++) pageNumbers.push(i)

  return (
    <div className="job-saas">
      <section className="js-hero">
        <div className="container">
          <div className="js-hero-inner">
            <p className="js-eyebrow">
              <i className="fas fa-rocket"></i> Carrière tech · Burkina Faso
            </p>
            <h1>
              Opportunités <span>qui comptent</span>
            </h1>
            <p>
              Stages, emplois, freelance, hackathons et formations — tout au même endroit, pensé pour
              la communauté tech burkinabè.
            </p>
            {canPublish && (
              <Link href="/job/creer" className="js-btn-primary">
                <i className="fas fa-plus"></i> Publier une offre
              </Link>
            )}
          </div>
        </div>
      </section>

      <section className="js-search" aria-label="Recherche d&apos;offres">
        <div className="container">
          <div className="js-search-card">
            <form method="GET" action="/job" className="js-search-form">
              <div className="js-field">
                <label htmlFor="job-search-q">Recherche</label>
                <JobLiveSearchField initialQ={search} />
              </div>

              <div className="js-field">
                <label htmlFor="job-search-city">Ville</label>
                <div className="js-input-wrap">
                  <i className="fas fa-map-marker-alt" aria-hidden="true"></i>
                  <select id="job-search-city" name="city" defaultValue={city}>
                    <option value="">Toutes les villes</option>
                    {CITIES.map(c => (
                      <option key={c} value={c}>
                        {c}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="js-field">
                <label htmlFor="job-search-type">Type</label>
                <div className="js-input-wrap">
                  <i className="fas fa-briefcase" aria-hidden="true"></i>
                  <select id="job-search-type" name="type" defaultValue={type}>
                    <option value="">Tous les types</option>
                    {TYPES.filter(t => t.value).map(t => (
                      <option key={t.value} value={t.value}>
                        {t.label}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <button type="submit" className="js-search-submit">
                <i className="fas fa-search"></i> Rechercher
              </button>
            </form>
          </div>
        </div>
      </section>

      <section className="js-stats" aria-label="Statistiques">
        <div className="container">
          <div className="js-stats-grid">
            <div className="js-stat">
              <i className="fas fa-briefcase" aria-hidden="true"></i>
              <strong>{totalJobs ?? 0}</strong>
              <span>Offres actives</span>
            </div>
            <div className="js-stat">
              <i className="fas fa-building" aria-hidden="true"></i>
              <strong>{companies ?? 0}</strong>
              <span>Entreprises</span>
            </div>
            <div className="js-stat">
              <i className="fas fa-user-check" aria-hidden="true"></i>
              <strong>{hired ?? 0}</strong>
              <span>Recrutements</span>
            </div>
            <div className="js-stat">
              <i className="fas fa-fire" aria-hidden="true"></i>
              <strong>{newThisWeek ?? 0}</strong>
              <span>Cette semaine</span>
            </div>
          </div>
        </div>
      </section>

      <div className="container">
        <div className="js-tabs-wrap">
          <nav className="js-tabs" aria-label="Types d&apos;opportunités">
            {TYPES.map(t => (
              <Link
                key={t.value || 'all'}
                href={buildHref({ type: t.value, page: '1' })}
                className={`js-tab${type === t.value || (!type && !t.value) ? ' active' : ''}`}
              >
                <i className={t.icon} aria-hidden="true"></i> {t.label}
              </Link>
            ))}
          </nav>
        </div>

        <div className="js-layout">
          <div className="js-list">
            {!jobs || jobs.length === 0 ? (
              <div className="js-empty">
                <i className="fas fa-briefcase" aria-hidden="true"></i>
                <h3>Aucune offre pour ces critères</h3>
                <p>Élargissez la recherche ou revenez bientôt pour de nouvelles opportunités.</p>
              </div>
            ) : (
              jobs.map(job => {
                const skills = parseSkills(job.skills_required).slice(0, 3)
                const companyName = job.company_name || 'Entreprise'
                const isNew =
                  job.created_at &&
                  new Date(job.created_at).getTime() > Date.now() - 3 * 24 * 60 * 60 * 1000
                const typeLabel = TYPES.find(tt => tt.value === job.type)?.label ?? job.type
                const typeKey = (job.type ?? 'emploi').replace(/[^a-z]/gi, '') || 'emploi'
                return (
                  <article key={job.id} className="js-card">
                    <div className="js-card-logo">
                      {job.company_logo ? (
                        // eslint-disable-next-line @next/next/no-img-element
                        <img src={buildFileUrl(job.company_logo)} alt="" />
                      ) : (
                        <span aria-hidden="true">{companyName.slice(0, 2).toUpperCase()}</span>
                      )}
                    </div>

                    <div className="js-card-body">
                      <div className="js-card-head">
                        <h2 className="js-card-title">
                          <Link href={`/job/${job.id}`}>{job.title}</Link>
                        </h2>
                        <span className={`js-type type-${typeKey}`}>{typeLabel}</span>
                      </div>

                      <div className="js-company">
                        <i className="fas fa-building" aria-hidden="true"></i>
                        {companyName}
                      </div>

                      <p className="js-desc">
                        {(job.description ?? '').slice(0, 160)}
                        {(job.description ?? '').length > 160 ? '…' : ''}
                      </p>

                      <div className="js-meta">
                        {job.city && (
                          <span>
                            <i className="fas fa-map-marker-alt" aria-hidden="true"></i>
                            {job.city}
                          </span>
                        )}
                        {job.salary && (
                          <span>
                            <i className="fas fa-money-bill-wave" aria-hidden="true"></i>
                            {job.salary}
                          </span>
                        )}
                        <span>
                          <i className="far fa-clock" aria-hidden="true"></i>
                          {timeAgo(job.created_at)}
                        </span>
                      </div>

                      {skills.length > 0 && (
                        <div className="js-skills">
                          {skills.map(s => (
                            <span key={s} className="js-skill">
                              {s}
                            </span>
                          ))}
                        </div>
                      )}
                    </div>

                    <div className="js-card-cta">
                      <div className="js-card-cta-inner">
                        <Link href={`/job/${job.id}`} className="js-btn-view">
                          Voir l&apos;offre
                        </Link>
                        {isNew && <span className="js-badge-new">Nouveau</span>}
                      </div>
                    </div>
                  </article>
                )
              })
            )}

            {totalPages > 1 && (
              <div className="js-pagination">
                <nav className="js-pagination-nav" aria-label="Pagination">
                  {page > 1 && (
                    <Link href={buildHref({ page: String(page - 1) })} className="js-page-link">
                      <i className="fas fa-chevron-left"></i> Préc.
                    </Link>
                  )}
                  {startPage > 1 && (
                    <>
                      <Link
                        href={buildHref({ page: '1' })}
                        className={`js-page-link${page === 1 ? ' active' : ''}`}
                      >
                        1
                      </Link>
                      {startPage > 2 && <span className="js-page-dots">…</span>}
                    </>
                  )}
                  {pageNumbers.map(i => (
                    <Link
                      key={i}
                      href={buildHref({ page: String(i) })}
                      className={`js-page-link${page === i ? ' active' : ''}`}
                    >
                      {i}
                    </Link>
                  ))}
                  {endPage < totalPages && (
                    <>
                      {endPage < totalPages - 1 && <span className="js-page-dots">…</span>}
                      <Link
                        href={buildHref({ page: String(totalPages) })}
                        className={`js-page-link${page === totalPages ? ' active' : ''}`}
                      >
                        {totalPages}
                      </Link>
                    </>
                  )}
                  {page < totalPages && (
                    <Link href={buildHref({ page: String(page + 1) })} className="js-page-link">
                      Suiv. <i className="fas fa-chevron-right"></i>
                    </Link>
                  )}
                </nav>
                <p className="js-pagination-info">
                  <strong>
                    {Math.min((page - 1) * PAGE_SIZE + 1, totalJobsValue)}–
                    {Math.min(page * PAGE_SIZE, totalJobsValue)}
                  </strong>{' '}
                  sur <strong>{totalJobsValue}</strong> offre{totalJobsValue > 1 ? 's' : ''}
                </p>
              </div>
            )}
          </div>

          <aside className="js-aside">
            <div className="js-widget">
              <h3>
                <i className="fas fa-lightbulb"></i> Conseils
              </h3>
              <ul className="js-tips">
                <li>
                  <i className="fas fa-check"></i>
                  Complétez votre profil avec vos compétences clés
                </li>
                <li>
                  <i className="fas fa-check"></i>
                  Ajoutez un CV à jour (PDF)
                </li>
                <li>
                  <i className="fas fa-check"></i>
                  Répondez rapidement aux offres qui vous correspondent
                </li>
                <li>
                  <i className="fas fa-check"></i>
                  Personnalisez votre message de candidature
                </li>
              </ul>
            </div>

            <NewsletterSaas variant="job" />
          </aside>
        </div>
      </div>
    </div>
  )
}
