import type { Metadata } from 'next'
import Link from 'next/link'
import { currentProfile } from '@/lib/auth'
import { JobCreateClient } from '@/components/job/job-create-client'

export const metadata: Metadata = { title: 'Publier une offre - AlgoCodeBF' }

export default async function CreateJobPage() {
  const profile = await currentProfile()
  const canPublish = profile?.role === 'company' || profile?.role === 'admin'

  if (!profile) {
    return (
      <section className="create-project-section">
        <div className="container">
          <div
            style={{
              maxWidth: 560,
              margin: '80px auto',
              background: '#fff',
              padding: 40,
              borderRadius: 20,
              textAlign: 'center',
              boxShadow: '0 5px 25px rgba(0,0,0,0.08)',
            }}
          >
            <i
              className="fas fa-lock"
              style={{ fontSize: '2.5rem', color: '#CE1126', marginBottom: 16 }}
            ></i>
            <h2 style={{ margin: '0 0 12px' }}>Connexion requise</h2>
            <p style={{ color: '#6c757d', marginBottom: 20 }}>
              Vous devez être connecté pour publier une offre.
            </p>
            <Link href="/login?redirect=/job/creer" className="btn btn-primary">
              <i className="fas fa-sign-in-alt"></i> Se connecter
            </Link>
          </div>
        </div>
      </section>
    )
  }

  if (!canPublish) {
    return (
      <section className="create-project-section">
        <div className="container">
          <div
            style={{
              maxWidth: 560,
              margin: '80px auto',
              background: '#fff',
              padding: 40,
              borderRadius: 20,
              textAlign: 'center',
              boxShadow: '0 5px 25px rgba(0,0,0,0.08)',
            }}
          >
            <i
              className="fas fa-ban"
              style={{ fontSize: '2.5rem', color: '#CE1126', marginBottom: 16 }}
            ></i>
            <h2 style={{ margin: '0 0 12px' }}>Accès réservé</h2>
            <p style={{ color: '#6c757d', marginBottom: 20 }}>
              Seuls les comptes entreprises (ou administrateurs) peuvent publier
              une offre. Passez votre profil en &laquo; entreprise &raquo; pour
              publier des opportunités.
            </p>
            <Link href="/job" className="btn btn-outline">
              <i className="fas fa-arrow-left"></i> Retour aux offres
            </Link>
          </div>
        </div>
      </section>
    )
  }

  return <JobCreateClient mode="create" />
}
