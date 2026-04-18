import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import { PermissionsManager } from '@/components/admin/permissions-manager'

export const metadata: Metadata = {
  title: 'Gestion des Permissions',
}

export const dynamic = 'force-dynamic'

export default async function PermissionsPage() {
  const supabase = await createClient()
  const { data: users } = await supabase
    .from('profiles')
    .select('id, prenom, nom, status, role, can_create_tutorial, can_create_project, created_at')
    .neq('role', 'admin')
    .order('created_at', { ascending: false })

  const rows = (users ?? []).map(u => ({
    id: u.id as string,
    full_name: `${u.prenom ?? ''} ${u.nom ?? ''}`.trim() || 'Utilisateur',
    status: (u.status as 'active' | 'pending' | 'suspended' | 'banned') ?? 'active',
    can_create_tutorial: !!u.can_create_tutorial,
    can_create_project: !!u.can_create_project,
  }))

  return <PermissionsManager initialUsers={rows} />
}
