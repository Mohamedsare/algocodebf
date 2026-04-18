import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { currentProfile } from '@/lib/auth'
import { JobApplyCard } from '@/components/job/job-apply-card'
import { ShareJobButtons } from '@/components/job/share-job-buttons'
import { timeAgo } from '@/lib/utils'

interface Props {
  params: Promise<{ id: string }>
}

const TYPE_LABELS: Record<string, string> = {
  stage: 'Stage',
  emploi: 'Emploi',
  freelance: 'Freelance',
  hackathon: 'Hackathon',
  formation: 'Formation',
  job: 'Emploi',
  internship: 'Stage',
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { id } = await params
  const supabase = await createClient()
  const { data } = await supabase
    .from('jobs')
    .select('title')
    .eq('id', parseInt(id))
    .maybeSingle()
  return { title: data?.title ? `${data.title} - Opportunités - AlgoCodeBF` : 'Offre' }
}

function parseSkills(raw?: string | null): string[] {
  if (!raw) return []
  const t = raw.trim()
  if (!t) return []
  if (t.startsWith('[')) {
    try {
      const arr = JSON.parse(t)
      if (Array.isArray(arr)) return arr.map(String).map(s => s.trim()).filter(Boolean)
    } catch {}
  }
  return t.split(',').map(s => s.trim()).filter(Boolean)
}

export default async function JobDetailPage({ params }: Props) {
  const { id } = await params
  const jobId = parseInt(id)
  if (Number.isNaN(jobId)) notFound()

  const [supabase, profile] = await Promise.all([createClient(), currentProfile()])

  const { data: job } = await supabase
    .from('jobs')
    .select(
      `id, title, description, type, city, salary, deadline, external_link, status,
       company_name, company_logo, skills_required, views, created_at, company_id`
    )
    .eq('id', jobId)
    .maybeSingle()

  if (!job) notFound()

  supabase
    .from('jobs')
    .update({ views: (job.views ?? 0) + 1 })
    .eq('id', jobId)
    .then(() => {})

  let alreadyApplied = false
  if (profile) {
    const { data: app } = await supabase
      .from('applications')
      .select('id')
      .eq('job_id', jobId)
      .eq('user_id', profile.id)
      .maybeSingle()
    alreadyApplied = !!app
  }

  const isDeadlinePassed = job.deadline && new Date(job.deadline) < new Date()
  const isNew =
    job.created_at &&
    new Date(job.created_at).getTime() > Date.now() - 3 * 24 * 60 * 60 * 1000
  const skills = parseSkills(job.skills_required)
  const companyName = job.company_name ?? 'Entreprise'
  const typeLabel = TYPE_LABELS[job.type ?? 'emploi'] ?? job.type
  const isOwner = profile && job.company_id === profile.id
  const canManage = isOwner || profile?.role === 'admin'

  return (
    <section className="job-show-section">
      <div className="container">
        <div className="breadcrumb-nav">
          <Link href="/">
            <i className="fas fa-home"></i> Accueil
          </Link>
          <i className="fas fa-chevron-right"></i>
          <Link href="/job">Opportunités</Link>
          <i className="fas fa-chevron-right"></i>
          <span>{job.title}</span>
        </div>

        <div className="job-show-layout">
          <div className="job-show-main">
            <div className="job-show-header">
              <div className="job-header-top">
                <span className={`job-type-badge type-${job.type ?? 'emploi'}`}>
                  {typeLabel}
                </span>
                {isNew && <span className="badge-new">Nouveau</span>}
                {isDeadlinePassed && (
                  <span
                    className="badge-new"
                    style={{ background: '#6c757d' }}
                  >
                    Expirée
                  </span>
                )}
                {canManage && (
                  <div style={{ marginLeft: 'auto', display: 'flex', gap: 8 }}>
                    <Link
                      href={`/job/${jobId}/modifier`}
                      className="btn btn-outline btn-sm"
                    >
                      <i className="fas fa-edit"></i> Modifier
                    </Link>
                    <Link
                      href={`/job/${jobId}/candidatures`}
                      className="btn btn-primary btn-sm"
                    >
                      <i className="fas fa-users"></i> Candidatures
                    </Link>
                  </div>
                )}
              </div>

              <h1 className="job-show-title">{job.title}</h1>

              <div className="job-meta-header">
                <div className="meta-item">
                  <i className="fas fa-building"></i>
                  <span>{companyName}</span>
                </div>
                <div className="meta-item">
                  <i className="fas fa-map-marker-alt"></i>
                  <span>{job.city || 'Non spécifié'}</span>
                </div>
                {job.salary && (
                  <div className="meta-item">
                    <i className="fas fa-money-bill-wave"></i>
                    <span>{job.salary}</span>
                  </div>
                )}
                <div className="meta-item">
                  <i className="fas fa-eye"></i>
                  <span>{(job.views ?? 0).toLocaleString('fr-FR')} vues</span>
                </div>
                <div className="meta-item">
                  <i className="fas fa-clock"></i>
                  <span>Publié {timeAgo(job.created_at)}</span>
                </div>
              </div>
            </div>

            <div className="job-description-section">
              <h2>
                <i className="fas fa-file-alt"></i> Description de l&apos;offre
              </h2>
              <div
                className="job-description-content"
                style={{ whiteSpace: 'pre-wrap' }}
              >
                {job.description || 'Aucune description disponible.'}
              </div>
            </div>

            {skills.length > 0 && (
              <div className="job-skills-section">
                <h2>
                  <i className="fas fa-tools"></i> Compétences requises
                </h2>
                <div className="skills-list">
                  {skills.map(s => (
                    <span key={s} className="skill-tag">
                      {s}
                    </span>
                  ))}
                </div>
              </div>
            )}

            {job.external_link && (
              <div className="job-contact-section">
                <h2>
                  <i className="fas fa-envelope"></i> Informations de contact
                </h2>
                <div className="contact-info">
                  <div className="contact-item">
                    <i className="fas fa-external-link-alt"></i>
                    <a
                      href={job.external_link}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      Voir l&apos;offre sur le site source
                    </a>
                  </div>
                </div>
              </div>
            )}

            {job.deadline && (
              <div className="job-deadline-section">
                <div className="deadline-alert">
                  <i className="fas fa-calendar-times"></i>
                  <div>
                    <strong>Date limite de candidature :</strong>
                    <span>
                      {new Date(job.deadline).toLocaleDateString('fr-FR')}
                    </span>
                  </div>
                </div>
              </div>
            )}
          </div>

          <aside className="job-show-sidebar">
            <JobApplyCard
              jobId={jobId}
              externalLink={job.external_link}
              hasApplied={alreadyApplied}
              isLoggedIn={!!profile}
              isDeadlinePassed={!!isDeadlinePassed}
              isClosed={job.status !== 'active'}
              hasCv={!!profile?.cv_path}
            />

            <div className="company-card">
              <h3>
                <i className="fas fa-building"></i> Entreprise
              </h3>
              <div className="company-info">
                <p className="company-name">{companyName}</p>
                {job.city && (
                  <p className="company-location">
                    <i className="fas fa-map-marker-alt"></i> {job.city}
                  </p>
                )}
              </div>
            </div>

            <div className="share-card">
              <h3>
                <i className="fas fa-share-alt"></i> Partager cette offre
              </h3>
              <ShareJobButtons title={job.title} />
            </div>
          </aside>
        </div>
      </div>
    </section>
  )
}
