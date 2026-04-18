import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import { ContenuAdminClient, type ContenuBundle } from '@/components/admin/contenu-admin-client'

export const metadata: Metadata = { title: 'Contenus (admin)' }
export const dynamic = 'force-dynamic'

function mapProfiles(rows: Record<string, unknown>[]) {
  return rows.map(r => {
    const p = r.profiles as
      | { prenom: string; nom: string }
      | { prenom: string; nom: string }[]
      | null
      | undefined
    const profiles = Array.isArray(p) ? p[0] ?? null : p ?? null
    return { ...r, profiles }
  })
}

export default async function AdminContentPage() {
  const supabase = await createClient()

  const [postsR, tutorialsR, jobsR, blogR] = await Promise.all([
    supabase
      .from('posts')
      .select('id, title, category, status, created_at, profiles!posts_user_id_fkey(prenom, nom)')
      .order('created_at', { ascending: false })
      .limit(280),
    supabase
      .from('tutorials')
      .select('id, title, category, status, created_at, profiles!tutorials_user_id_fkey(prenom, nom)')
      .order('created_at', { ascending: false })
      .limit(280),
    supabase
      .from('jobs')
      .select(
        'id, title, type, status, company_name, created_at, profiles!jobs_company_id_fkey(prenom, nom)'
      )
      .order('created_at', { ascending: false })
      .limit(280),
    supabase
      .from('blog_posts')
      .select('id, title, slug, category, status, created_at, profiles!blog_posts_author_id_fkey(prenom, nom)')
      .order('created_at', { ascending: false })
      .limit(280),
  ])

  const err = postsR.error ?? tutorialsR.error ?? jobsR.error ?? blogR.error
  if (err) {
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Gestion des contenus</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger les contenus : {err.message}</p>
      </div>
    )
  }

  const data: ContenuBundle = {
    posts: mapProfiles((postsR.data ?? []) as Record<string, unknown>[]) as ContenuBundle['posts'],
    tutorials: mapProfiles((tutorialsR.data ?? []) as Record<string, unknown>[]) as ContenuBundle['tutorials'],
    jobs: mapProfiles((jobsR.data ?? []) as Record<string, unknown>[]) as ContenuBundle['jobs'],
    blog: mapProfiles((blogR.data ?? []) as Record<string, unknown>[]) as ContenuBundle['blog'],
  }

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold m-0">Gestion des contenus</h1>
      <p className="text-sm text-gray-600 m-0">
        Vue unifiée des derniers contenus par type (chargement unique). Filtres locaux, lien public et raccourci vers
        la page de modération dédiée.
      </p>
      <ContenuAdminClient data={data} />
    </div>
  )
}
