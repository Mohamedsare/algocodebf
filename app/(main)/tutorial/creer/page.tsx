import type { Metadata } from 'next'
import Link from 'next/link'
import { requireLogin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { TutorialCreateClient } from '@/components/tutorial/tutorial-create-client'
import { FORMATIONS_PATH } from '@/lib/routes'

export const metadata: Metadata = { title: 'Publier une formation' }

const DEFAULT_CATEGORIES = [
  'Algorithmique',
  'Structures de données',
  'Web Frontend',
  'Web Backend',
  'Mobile',
  'Data Science',
  'DevOps',
  'Sécurité',
  'Autre',
]

export default async function TutorialCreatePage() {
  const profile = await requireLogin()
  const canCreate = Boolean(profile.can_create_tutorial) || profile.role === 'admin'

  if (!canCreate) {
    return (
      <div className="formation-create-saas">
        <div className="container">
          <div className="fc-gate">
            <div className="fc-gate-icon fc-gate-icon--lock" aria-hidden>
              <i className="fas fa-graduation-cap" />
            </div>
            <h1 className="fc-gate-title">Permission requise</h1>
            <p className="fc-gate-text">
              La publication de formations est réservée aux comptes autorisés par un administrateur. Demandez
              l&apos;activation ou continuez à suivre le catalogue.
            </p>
            <div className="fc-gate-actions">
              <Link href={FORMATIONS_PATH} className="fc-gate-btn fc-gate-btn--primary">
                <i className="fas fa-book-open" aria-hidden />
                Retour au catalogue
              </Link>
              <a href="mailto:contact@algocodebf.bf" className="fc-gate-btn fc-gate-btn--ghost">
                <i className="fas fa-envelope" aria-hidden />
                Contacter l&apos;équipe
              </a>
            </div>
          </div>
        </div>
      </div>
    )
  }

  const supabase = await createClient()
  const { data: cats } = await supabase.from('tutorial_categories').select('name').order('name')
  const dbCats = ((cats ?? []) as Array<{ name: string }>).map(c => c.name)
  const categories = Array.from(new Set([...DEFAULT_CATEGORIES, ...dbCats]))

  return (
    <div className="formation-create-saas">
      <TutorialCreateClient mode="create" categories={categories} />
    </div>
  )
}
