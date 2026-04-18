import type { Metadata } from 'next'
import Link from 'next/link'
import { currentProfile } from '@/lib/auth'
import { ProjectCreateClient } from '@/components/project/project-create-client'

export const metadata: Metadata = { title: 'Créer un Projet - AlgoCodeBF' }

export default async function CreateProjectPage() {
  const profile = await currentProfile()
  const canCreate = Boolean(profile?.can_create_project) || profile?.role === 'admin'

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
              Vous devez être connecté pour créer un projet collaboratif.
            </p>
            <Link href="/login" className="btn btn-primary">
              <i className="fas fa-sign-in-alt"></i> Se connecter
            </Link>
          </div>
        </div>
      </section>
    )
  }

  if (!canCreate) {
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
            <h2 style={{ margin: '0 0 12px' }}>Permission requise</h2>
            <p style={{ color: '#6c757d', marginBottom: 20 }}>
              Vous n&apos;avez pas la permission de créer un projet. Contactez un
              administrateur pour obtenir cette permission.
            </p>
            <Link href="/project" className="btn btn-outline">
              <i className="fas fa-arrow-left"></i> Retour aux projets
            </Link>
          </div>
        </div>
      </section>
    )
  }

  return <ProjectCreateClient mode="create" />
}
