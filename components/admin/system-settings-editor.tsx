'use client'

import { useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { updateSystemSettingAction } from '@/app/actions/admin'
import { Button } from '@/components/ui/button'
import { useToast } from '@/components/ui/toast-provider'

const EDITABLE: { key: string; label: string; hint?: string; type?: 'text' | 'textarea' | 'bool' }[] = [
  { key: 'site_name', label: 'Nom du site' },
  { key: 'site_description', label: 'Description', type: 'textarea' },
  {
    key: 'maintenance_mode',
    label: 'Mode maintenance',
    type: 'bool',
    hint: 'true / false',
  },
  {
    key: 'allow_registration',
    label: 'Inscriptions ouvertes',
    type: 'bool',
    hint: 'true / false',
  },
  { key: 'default_user_role', label: 'Rôle par défaut (user, company)', hint: 'user' },
]

interface Row {
  id: number
  key: string
  value: string | null
  updated_at: string
}

export function SystemSettingsEditor({ rows }: { rows: Row[] }) {
  const router = useRouter()
  const toast = useToast()
  const [pending, start] = useTransition()
  const byKey = new Map(rows.map(r => [r.key, r]))

  function save(key: string, value: string) {
    start(async () => {
      const r = await updateSystemSettingAction(key, value)
      if (r.ok) router.refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  return (
    <div className="space-y-6">
      <h2 className="text-base font-bold m-0 text-[var(--dark-color)]">Réglages modifiables</h2>
      <div className="space-y-4">
        {EDITABLE.map(def => {
          const row = byKey.get(def.key)
          const initial = row?.value ?? ''
          return (
            <form
              key={def.key}
              className="category-card"
              onSubmit={e => {
                e.preventDefault()
                const fd = new FormData(e.currentTarget)
                save(def.key, (fd.get('value') as string) ?? '')
              }}
            >
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <h4 className="m-0 text-[var(--dark-color)]">{def.label}</h4>
                  <code className="text-xs text-gray-500">{def.key}</code>
                  {def.hint && <p className="text-xs text-gray-500 m-0 mt-1">{def.hint}</p>}
                </div>
                <Button type="submit" size="sm" disabled={pending}>
                  Enregistrer
                </Button>
              </div>
              {def.type === 'textarea' ? (
                <textarea
                  name="value"
                  className="mt-3 w-full min-h-[80px] rounded-lg border border-gray-200 px-3 py-2 text-sm"
                  defaultValue={initial}
                  disabled={pending}
                />
              ) : def.type === 'bool' ? (
                <select
                  name="value"
                  className="mt-3 w-full max-w-xs rounded-lg border border-gray-200 px-3 py-2 text-sm"
                  defaultValue={initial === 'true' ? 'true' : 'false'}
                  disabled={pending}
                >
                  <option value="false">false</option>
                  <option value="true">true</option>
                </select>
              ) : (
                <input
                  name="value"
                  className="mt-3 w-full max-w-xl rounded-lg border border-gray-200 px-3 py-2 text-sm"
                  defaultValue={initial}
                  disabled={pending}
                />
              )}
            </form>
          )
        })}
      </div>
    </div>
  )
}
