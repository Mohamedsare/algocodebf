import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound, redirect } from 'next/navigation'
import { requireLogin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { ApplicationRow } from '@/components/job/application-row'
import type { ApplicationStatus } from '@/types'

export const metadata: Metadata = { title: 'Candidatures - AlgoCodeBF' }

interface Row {
  id: number
  status: ApplicationStatus
  cover_letter: string | null
  cv_path: string | null
  created_at: string
  profiles: {
    id: string
    prenom: string
    nom: string
    photo_path: string | null
    university: string | null
    city: string | null
  } | null
}

export default async function ApplicationsPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const jobId = Number(id)
  if (Number.isNaN(jobId)) notFound()
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: job } = await supabase
    .from('jobs')
    .select('id, title, company_id')
    .eq('id', jobId)
    .maybeSingle()
  if (!job) notFound()
  if (
    (job as { company_id: string | null }).company_id !== profile.id &&
    profile.role !== 'admin'
  ) {
    redirect(`/job/${jobId}`)
  }

  const { data } = await supabase
    .from('applications')
    .select(
      `id, status, cover_letter, cv_path, created_at,
       profiles!applications_user_id_fkey(id, prenom, nom, photo_path, university, city)`
    )
    .eq('job_id', jobId)
    .order('created_at', { ascending: false })

  const apps = (data ?? []) as unknown as Row[]
  const grouped = {
    pending: apps.filter(a => a.status === 'pending'),
    accepted: apps.filter(a => a.status === 'accepted'),
    rejected: apps.filter(a => a.status === 'rejected'),
  }

  const sections: Array<{ key: keyof typeof grouped; label: string; color: string }> = [
    { key: 'pending', label: 'En attente', color: '#ffc107' },
    { key: 'accepted', label: 'Acceptées', color: '#28a745' },
    { key: 'rejected', label: 'Refusées', color: '#6c757d' },
  ]

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
          <Link href={`/job/${jobId}`}>
            {(job as { title: string }).title}
          </Link>
          <i className="fas fa-chevron-right"></i>
          <span>Candidatures</span>
        </div>

        <div
          className="job-show-main"
          style={{ maxWidth: 900, margin: '0 auto' }}
        >
          <div className="job-show-header">
            <h1 className="job-show-title">
              <i className="fas fa-users"></i>{' '}
              {(job as { title: string }).title} — Candidatures
            </h1>
            <p style={{ color: '#6c757d', marginTop: 10 }}>
              {apps.length} candidature{apps.length > 1 ? 's' : ''} reçue
              {apps.length > 1 ? 's' : ''}.
            </p>
          </div>

          {apps.length === 0 ? (
            <div className="empty-jobs" style={{ background: '#f8f9fa' }}>
              <i className="fas fa-inbox"></i>
              <h3>Aucune candidature pour le moment</h3>
              <p>Les candidatures apparaîtront ici dès qu&apos;elles arriveront.</p>
            </div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 32 }}>
              {sections.map(s => {
                const items = grouped[s.key]
                if (items.length === 0) return null
                return (
                  <section key={s.key}>
                    <h2
                      style={{
                        fontSize: '1.1rem',
                        marginBottom: 16,
                        color: s.color,
                        display: 'flex',
                        alignItems: 'center',
                        gap: 10,
                        textTransform: 'uppercase',
                        letterSpacing: '0.05em',
                      }}
                    >
                      <i
                        className={
                          s.key === 'pending'
                            ? 'fas fa-hourglass-half'
                            : s.key === 'accepted'
                              ? 'fas fa-check-circle'
                              : 'fas fa-times-circle'
                        }
                      ></i>{' '}
                      {s.label} ({items.length})
                    </h2>
                    <ul
                      style={{
                        display: 'flex',
                        flexDirection: 'column',
                        gap: 12,
                        listStyle: 'none',
                        padding: 0,
                        margin: 0,
                      }}
                    >
                      {items.map(a => (
                        <ApplicationRow
                          key={a.id}
                          id={a.id}
                          status={a.status}
                          coverLetter={a.cover_letter}
                          cvPath={a.cv_path}
                          createdAt={a.created_at}
                          applicant={a.profiles}
                        />
                      ))}
                    </ul>
                  </section>
                )
              })}
            </div>
          )}
        </div>
      </div>
    </section>
  )
}
