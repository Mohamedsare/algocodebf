'use client'

import { useTransition, useState } from 'react'
import { useRouter } from 'next/navigation'
import { setUserRoleAction, setUserStatusAction, setUserPermissionAction } from '@/app/actions/admin'
import type { UserRole, UserStatus } from '@/types'

interface Props {
  userId: string
  role: UserRole
  status: UserStatus
  canTutorial: boolean
  canProject: boolean
  disabled?: boolean
}

export function UserAdminControls(props: Props) {
  const router = useRouter()
  const [pending, startTransition] = useTransition()
  const [err, setErr] = useState<string | null>(null)

  function upd(fn: () => Promise<{ ok: boolean; message?: string }>) {
    setErr(null)
    startTransition(async () => {
      const res = await fn()
      if (res.ok) router.refresh()
      else setErr(res.message ?? 'Erreur')
    })
  }

  return (
    <div className="flex items-center gap-2 flex-wrap">
      <select
        defaultValue={props.role}
        onChange={e => upd(() => setUserRoleAction(props.userId, e.target.value as UserRole))}
        disabled={pending || props.disabled}
        className="text-xs h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-2"
      >
        <option value="user">Utilisateur</option>
        <option value="company">Entreprise</option>
        <option value="admin">Admin</option>
      </select>
      <select
        defaultValue={props.status}
        onChange={e => upd(() => setUserStatusAction(props.userId, e.target.value as UserStatus))}
        disabled={pending || props.disabled}
        className="text-xs h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-2"
      >
        <option value="active">Actif</option>
        <option value="inactive">Inactif</option>
        <option value="suspended">Suspendu</option>
      </select>
      <label className="flex items-center gap-1 text-xs">
        <input
          type="checkbox"
          defaultChecked={props.canTutorial}
          onChange={e => upd(() => setUserPermissionAction(props.userId, 'can_create_tutorial', e.target.checked))}
          disabled={pending}
        />
        Tuto
      </label>
      <label className="flex items-center gap-1 text-xs">
        <input
          type="checkbox"
          defaultChecked={props.canProject}
          onChange={e => upd(() => setUserPermissionAction(props.userId, 'can_create_project', e.target.checked))}
          disabled={pending}
        />
        Projet
      </label>
      {err && <span className="text-xs text-red-500">{err}</span>}
    </div>
  )
}
