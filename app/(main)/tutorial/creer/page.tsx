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
      <div className="formation-saas">
      <section className="create-tutorial-section">
        <div className="container">
          <div
            className="alert alert-danger"
            style={{ textAlign: 'center', padding: 48 }}
          >
            <i className="fas fa-lock" style={{ fontSize: 48, marginBottom: 20 }}></i>
            <h2 style={{ marginBottom: 12 }}>Permission requise</h2>
            <p>
              Vous devez demander à un administrateur l&apos;autorisation de publier
              des formations.
            </p>
            <Link
              href={FORMATIONS_PATH}
              className="btn-back"
              style={{ marginTop: 20, display: 'inline-flex' }}
            >
              <i className="fas fa-arrow-left"></i> Retour au catalogue
            </Link>
          </div>
        </div>
      </section>
      </div>
    )
  }

  const supabase = await createClient()
  const { data: cats } = await supabase
    .from('tutorial_categories')
    .select('name')
    .order('name')
  const dbCats = ((cats ?? []) as Array<{ name: string }>).map(c => c.name)
  const categories = Array.from(new Set([...DEFAULT_CATEGORIES, ...dbCats]))

  return (
    <div className="formation-saas">
      <TutorialCreateClient mode="create" categories={categories} />
    </div>
  )
}
