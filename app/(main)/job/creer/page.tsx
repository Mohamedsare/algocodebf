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
      <div className="job-create-saas">
        <div className="container">
          <div className="jc-gate">
            <div className="jc-gate-icon jc-gate-icon--lock" aria-hidden>
              <i className="fas fa-lock" />
            </div>
            <h1 className="jc-gate-title">Connexion requise</h1>
            <p className="jc-gate-text">
              Connectez-vous avec un compte entreprise ou administrateur pour publier une opportunité sur AlgoCodeBF.
            </p>
            <div className="jc-gate-actions">
              <Link href="/login?redirect=/job/creer" className="jc-gate-btn jc-gate-btn--primary">
                <i className="fas fa-sign-in-alt" aria-hidden />
                Se connecter
              </Link>
              <Link href="/job" className="jc-gate-btn jc-gate-btn--ghost">
                <i className="fas fa-briefcase" aria-hidden />
                Voir les offres
              </Link>
            </div>
          </div>
        </div>
      </div>
    )
  }

  if (!canPublish) {
    return (
      <div className="job-create-saas">
        <div className="container">
          <div className="jc-gate">
            <div className="jc-gate-icon jc-gate-icon--deny" aria-hidden>
              <i className="fas fa-building" />
            </div>
            <h1 className="jc-gate-title">Compte entreprise requis</h1>
            <p className="jc-gate-text">
              Seuls les profils <strong>entreprise</strong> et les <strong>administrateurs</strong> peuvent publier une
              offre. Demandez le passage en compte recruteur ou contactez l&apos;équipe.
            </p>
            <div className="jc-gate-actions">
              <Link href="/job" className="jc-gate-btn jc-gate-btn--primary">
                <i className="fas fa-arrow-left" aria-hidden />
                Retour aux opportunités
              </Link>
              <a href="mailto:contact@algocodebf.bf" className="jc-gate-btn jc-gate-btn--ghost">
                <i className="fas fa-envelope" aria-hidden />
                Écrire à l&apos;équipe
              </a>
            </div>
          </div>
        </div>
      </div>
    )
  }

  return <JobCreateClient mode="create" />
}
