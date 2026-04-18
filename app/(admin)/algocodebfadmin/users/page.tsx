import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import { getCurrentProfile } from '@/lib/auth'
import { AdminUsersFiltersForm } from '@/components/admin/admin-users-filters-form'
import { AdminUsersConsole, type AdminConsoleUserRow } from '@/components/admin/admin-users-console'

export const metadata: Metadata = { title: 'Utilisateurs' }
export const dynamic = 'force-dynamic'

interface SP {
  q?: string
  role?: string
  status?: string
}

export default async function AdminUsersPage({ searchParams }: { searchParams: Promise<SP> }) {
  const params = await searchParams
  const supabase = await createClient()
  const me = await getCurrentProfile()

  let users: AdminConsoleUserRow[] = []
  let emailColumnAvailable = false

  const { data: rpcRows, error: rpcError } = await supabase.rpc('admin_list_profiles_for_console', {
    search_q: params.q?.trim() || null,
    role_filter: params.role?.trim() || null,
    status_filter: params.status?.trim() || null,
    max_rows: 100,
  })

  if (!rpcError && rpcRows && Array.isArray(rpcRows)) {
    emailColumnAvailable = true
    users = (rpcRows as Record<string, unknown>[]).map(row => ({
      id: String(row.id),
      email: String(row.email ?? ''),
      email_verified: Boolean(row.email_verified),
      prenom: String(row.prenom ?? ''),
      nom: String(row.nom ?? ''),
      phone: (row.phone as string | null) ?? null,
      university: (row.university as string | null) ?? null,
      faculty: (row.faculty as string | null) ?? null,
      city: (row.city as string | null) ?? null,
      bio: (row.bio as string | null) ?? null,
      photo_path: (row.photo_path as string | null) ?? null,
      cv_path: (row.cv_path as string | null) ?? null,
      role: String(row.role ?? 'user'),
      status: String(row.status ?? 'active'),
      last_login: (row.last_login as string | null) ?? null,
      created_at: String(row.created_at ?? ''),
      updated_at: String(row.updated_at ?? ''),
      points: Number(row.points ?? 0),
      account_kind: (row.account_kind as string | null) ?? null,
      organization_name: (row.organization_name as string | null) ?? null,
      job_title: (row.job_title as string | null) ?? null,
      can_create_tutorial: Boolean(row.can_create_tutorial),
      can_create_project: Boolean(row.can_create_project),
    }))
  } else {
    let q = supabase
      .from('profiles')
      .select(
        'id, prenom, nom, phone, university, faculty, city, bio, photo_path, cv_path, role, status, can_create_tutorial, can_create_project, last_login, points, created_at, updated_at, account_kind, organization_name, job_title'
      )
      .order('created_at', { ascending: false })
      .limit(100)

    if (params.q) {
      const pat = `%${params.q}%`
      q = q.or(`prenom.ilike.${pat},nom.ilike.${pat},university.ilike.${pat},city.ilike.${pat}`)
    }
    if (params.role) q = q.eq('role', params.role)
    if (params.status) q = q.eq('status', params.status)

    const { data } = await q
    users = ((data ?? []) as Array<Record<string, unknown>>).map(p => ({
      id: String(p.id),
      email: '',
      email_verified: false,
      prenom: String(p.prenom ?? ''),
      nom: String(p.nom ?? ''),
      phone: (p.phone as string | null) ?? null,
      university: (p.university as string | null) ?? null,
      faculty: (p.faculty as string | null) ?? null,
      city: (p.city as string | null) ?? null,
      bio: (p.bio as string | null) ?? null,
      photo_path: (p.photo_path as string | null) ?? null,
      cv_path: (p.cv_path as string | null) ?? null,
      role: String(p.role ?? 'user'),
      status: String(p.status ?? 'active'),
      last_login: (p.last_login as string | null) ?? null,
      created_at: String(p.created_at ?? ''),
      updated_at: String(p.updated_at ?? ''),
      points: Number(p.points ?? 0),
      account_kind: (p.account_kind as string | null) ?? null,
      organization_name: (p.organization_name as string | null) ?? null,
      job_title: (p.job_title as string | null) ?? null,
      can_create_tutorial: Boolean(p.can_create_tutorial),
      can_create_project: Boolean(p.can_create_project),
    }))
  }

  return (
    <div className="admin-users-page space-y-5">
      {!emailColumnAvailable && (
        <div className="admin-users-migration-hint">
          Colonnes e-mail et vérification indisponibles : appliquez la migration Supabase{' '}
          <code>0008_admin_list_profiles.sql</code> pour activer la fonction{' '}
          <code>admin_list_profiles_for_console</code>.
        </div>
      )}

      <AdminUsersFiltersForm
        initialQ={params.q ?? ''}
        role={params.role ?? ''}
        status={params.status ?? ''}
      />

      <AdminUsersConsole
        users={users}
        currentUserId={me?.id ?? null}
        emailColumnAvailable={emailColumnAvailable}
      />
    </div>
  )
}
