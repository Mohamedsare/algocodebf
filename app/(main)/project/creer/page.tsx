import type { Metadata } from 'next'
import Link from 'next/link'
import { currentProfile } from '@/lib/auth'
import { ProjectCreateClient } from '@/components/project/project-create-client'

export const metadata: Metadata = { title: 'Créer un projet - AlgoCodeBF' }

export default async function CreateProjectPage() {
  const profile = await currentProfile()
  const canCreate = Boolean(profile?.can_create_project) || profile?.role === 'admin'

  if (!profile) {
    return (
      <div className="project-create-saas">
        <div className="container">
          <div className="pc-gate">
            <div className="pc-gate-icon pc-gate-icon--lock" aria-hidden>
              <i className="fas fa-lock" />
            </div>
            <h1 className="pc-gate-title">Connexion requise</h1>
            <p className="pc-gate-text">
              Connectez-vous pour proposer un projet collaboratif et inviter d&apos;autres membres de la communauté.
            </p>
            <div className="pc-gate-actions">
              <Link href="/login?redirect=/project/creer" className="pc-gate-btn pc-gate-btn--primary">
                <i className="fas fa-sign-in-alt" aria-hidden />
                Se connecter
              </Link>
              <Link href="/project" className="pc-gate-btn pc-gate-btn--ghost">
                <i className="fas fa-project-diagram" aria-hidden />
                Voir les projets
              </Link>
            </div>
          </div>
        </div>
      </div>
    )
  }

  if (!canCreate) {
    return (
      <div className="project-create-saas">
        <div className="container">
          <div className="pc-gate">
            <div className="pc-gate-icon pc-gate-icon--deny" aria-hidden>
              <i className="fas fa-user-shield" />
            </div>
            <h1 className="pc-gate-title">Permission requise</h1>
            <p className="pc-gate-text">
              La création de projets est activée par un administrateur. Vous pouvez explorer les projets existants ou
              demander l&apos;accès.
            </p>
            <div className="pc-gate-actions">
              <Link href="/project" className="pc-gate-btn pc-gate-btn--primary">
                <i className="fas fa-arrow-left" aria-hidden />
                Retour aux projets
              </Link>
              <a href="mailto:contact@algocodebf.bf" className="pc-gate-btn pc-gate-btn--ghost">
                <i className="fas fa-envelope" aria-hidden />
                Contacter l&apos;équipe
              </a>
            </div>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="project-create-saas">
      <ProjectCreateClient mode="create" />
    </div>
  )
}
