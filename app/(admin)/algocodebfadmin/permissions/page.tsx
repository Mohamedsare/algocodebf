import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { PermissionsManager } from '@/components/admin/permissions-manager'
import { ADMIN_CONSOLE_PATH } from '@/lib/routes'

export const metadata: Metadata = {
  title: 'Permissions (admin)',
}

export const dynamic = 'force-dynamic'

export default async function PermissionsPage() {
  const supabase = await createClient()
  const { data: users, error } = await supabase
    .from('profiles')
    .select('id, prenom, nom, status, role, can_create_tutorial, can_create_project, created_at')
    .neq('role', 'admin')
    .order('created_at', { ascending: false })
    .limit(600)

  if (error) {
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Permissions</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger les profils : {error.message}</p>
      </div>
    )
  }

  const rows = (users ?? []).map(u => ({
    id: u.id as string,
    full_name: `${u.prenom ?? ''} ${u.nom ?? ''}`.trim() || 'Utilisateur',
    status: (u.status as 'active' | 'pending' | 'suspended' | 'banned') ?? 'active',
    can_create_tutorial: !!u.can_create_tutorial,
    can_create_project: !!u.can_create_project,
  }))

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold m-0">Permissions</h1>
      <p className="text-sm text-gray-600 m-0">
        Accorder la création de <strong>formations</strong> et de <strong>projets</strong> aux comptes non-admin. Les
        administrateurs ont déjà tous les droits. Pour le rôle et le statut du compte, utilisez{' '}
        <Link href={`${ADMIN_CONSOLE_PATH}/users`} className="font-semibold text-[#C8102E] hover:underline">
          Utilisateurs
        </Link>
        .
      </p>
      <PermissionsManager initialUsers={rows} />
    </div>
  )
}
