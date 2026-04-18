import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { getCurrentProfile } from '@/lib/auth'
import { Avatar } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { AdminUsersSearchInput } from '@/components/admin/admin-users-search-input'
import { UserAdminControls } from '@/components/admin/user-admin-controls'
import type { UserRole, UserStatus } from '@/types'

export const metadata: Metadata = { title: 'Utilisateurs' }
export const dynamic = 'force-dynamic'

interface SP { q?: string; role?: string; status?: string }

export default async function AdminUsersPage({ searchParams }: { searchParams: Promise<SP> }) {
  const params = await searchParams
  const supabase = await createClient()
  const me = await getCurrentProfile()

  let q = supabase
    .from('profiles')
    .select('id, prenom, nom, phone, university, city, role, status, can_create_tutorial, can_create_project, points, created_at')
    .order('created_at', { ascending: false })
    .limit(100)

  if (params.q) {
    const pat = `%${params.q}%`
    q = q.or(`prenom.ilike.${pat},nom.ilike.${pat},university.ilike.${pat}`)
  }
  if (params.role) q = q.eq('role', params.role)
  if (params.status) q = q.eq('status', params.status)

  const { data } = await q
  const users = (data ?? []) as Array<{
    id: string; prenom: string; nom: string; phone: string | null
    university: string | null; city: string | null
    role: UserRole; status: UserStatus
    can_create_tutorial: boolean; can_create_project: boolean
    points: number; created_at: string
  }>

  return (
    <div className="space-y-5">
      <header className="flex items-center justify-between gap-3 flex-wrap">
        <div>
          <h1 className="text-2xl font-bold">Utilisateurs</h1>
          <p className="text-sm text-gray-500">{users.length} résultat{users.length > 1 ? 's' : ''} (max 100)</p>
        </div>
      </header>

      <form method="GET" className="flex gap-2 flex-wrap bg-white dark:bg-gray-900 p-3 rounded-2xl border border-gray-200 dark:border-gray-800">
        <AdminUsersSearchInput initialQ={params.q ?? ''} />
        <select name="role" defaultValue={params.role ?? ''} className="h-9 px-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm">
          <option value="">Tous rôles</option>
          <option value="user">Utilisateur</option>
          <option value="company">Entreprise</option>
          <option value="admin">Admin</option>
        </select>
        <select name="status" defaultValue={params.status ?? ''} className="h-9 px-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm">
          <option value="">Tous statuts</option>
          <option value="active">Actif</option>
          <option value="inactive">Inactif</option>
          <option value="suspended">Suspendu</option>
        </select>
        <button className="h-9 px-4 rounded-xl bg-[#C8102E] text-white text-sm font-semibold">Filtrer</button>
      </form>

      <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 dark:bg-gray-800/40 text-xs uppercase text-gray-500">
            <tr>
              <th className="text-left p-3">Utilisateur</th>
              <th className="text-left p-3 hidden md:table-cell">Points</th>
              <th className="text-left p-3 hidden md:table-cell">Université</th>
              <th className="text-left p-3">Rôle / Statut / Permissions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
            {users.map(u => (
              <tr key={u.id}>
                <td className="p-3">
                  <Link href={`/user/${u.id}`} className="flex items-center gap-2 min-w-0 hover:text-[#C8102E]">
                    <Avatar src={null} prenom={u.prenom} nom={u.nom} size="sm" />
                    <div className="min-w-0">
                      <div className="font-semibold truncate">
                        {u.prenom} {u.nom}
                        {u.id === me?.id && <Badge variant="accent" className="ml-1 text-[10px]">Vous</Badge>}
                      </div>
                      {u.city && <div className="text-xs text-gray-500 truncate">{u.city}</div>}
                    </div>
                  </Link>
                </td>
                <td className="p-3 hidden md:table-cell font-semibold">{u.points}</td>
                <td className="p-3 hidden md:table-cell text-gray-600 dark:text-gray-400 truncate max-w-[200px]">
                  {u.university ?? '—'}
                </td>
                <td className="p-3">
                  <UserAdminControls
                    userId={u.id}
                    role={u.role}
                    status={u.status}
                    canTutorial={u.can_create_tutorial}
                    canProject={u.can_create_project}
                    disabled={u.id === me?.id}
                  />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
