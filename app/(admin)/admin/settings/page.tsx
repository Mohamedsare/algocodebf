import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'

export const metadata: Metadata = { title: 'Paramètres' }
export const dynamic = 'force-dynamic'

export default async function AdminSettingsPage() {
  const supabase = await createClient()
  const { data } = await supabase
    .from('system_settings')
    .select('id, key, value, updated_at')
    .order('key')

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Paramètres système</h1>
      <p className="text-sm text-gray-500">
        Les valeurs sont stockées dans <code>system_settings</code>. L&apos;édition est désactivée pour l&apos;instant
        (sensibilité) — utilisez le SQL Editor Supabase.
      </p>
      <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 dark:bg-gray-800/40 text-xs uppercase text-gray-500">
            <tr>
              <th className="text-left p-3">Clé</th>
              <th className="text-left p-3">Valeur</th>
              <th className="text-left p-3">Mis à jour</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
            {(data ?? []).map(s => (
              <tr key={s.id}>
                <td className="p-3 font-mono text-xs">{s.key}</td>
                <td className="p-3 text-gray-700 dark:text-gray-300 break-all">{s.value ?? '—'}</td>
                <td className="p-3 text-xs text-gray-500">{new Date(s.updated_at as string).toLocaleString('fr-FR')}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
