import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { SystemSettingsEditor } from '@/components/admin/system-settings-editor'
import { ADMIN_CONSOLE_PATH } from '@/lib/routes'
import { formatNumber } from '@/lib/utils'

export const metadata: Metadata = { title: 'Paramètres (admin)' }
export const dynamic = 'force-dynamic'

const EDITABLE_UI_KEYS = [
  'site_name',
  'site_description',
  'maintenance_mode',
  'allow_registration',
  'default_user_role',
] as const

type SettingRow = { id: number; key: string; value: string | null; updated_at: string }

export default async function AdminSettingsPage() {
  const supabase = await createClient()
  const { data, error } = await supabase
    .from('system_settings')
    .select('id, key, value, updated_at')
    .order('key')

  if (error) {
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Paramètres</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger les paramètres : {error.message}</p>
      </div>
    )
  }

  const rows = (data ?? []) as SettingRow[]
  const byKey = new Map(rows.map(r => [r.key, r]))
  const maintenanceOn = byKey.get('maintenance_mode')?.value === 'true'
  const registrationOpen = byKey.get('allow_registration')?.value === 'true'
  const editablePresent = EDITABLE_UI_KEYS.filter(k => byKey.has(k)).length

  return (
    <div className="space-y-6">
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Paramètres</h1>
        <p className="text-sm text-gray-600 m-0">
          Valeurs globales du site (nom, description, maintenance, inscriptions). Les autres clés restent visibles
          ci-dessous en lecture seule ; les changements sensibles se font aussi dans le tableau{' '}
          <code className="text-xs bg-gray-100 px-1 rounded">system_settings</code> côté Supabase si besoin.
        </p>
      </div>

      <div className="stats-grid-admin">
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-sliders-h" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(rows.length)}</h3>
            <p>Clés en base</p>
            <span className="stat-trend">{formatNumber(editablePresent)} exposées dans le formulaire</span>
          </div>
        </div>
        <div className={`stat-card-admin ${maintenanceOn ? 'card-reports' : 'card-posts'}`}>
          <div className="stat-icon-admin">
            <i className={`fas ${maintenanceOn ? 'fa-exclamation-triangle' : 'fa-check-circle'}`} aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{maintenanceOn ? 'Oui' : 'Non'}</h3>
            <p>Mode maintenance</p>
            <span className={`stat-trend${maintenanceOn ? '' : ' positive'}`}>
              {maintenanceOn ? 'Le site peut être restreint' : 'Public normal'}
            </span>
          </div>
        </div>
        <div className={`stat-card-admin ${registrationOpen ? 'card-posts' : 'card-reports'}`}>
          <div className="stat-icon-admin">
            <i className={`fas ${registrationOpen ? 'fa-user-plus' : 'fa-user-lock'}`} aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{registrationOpen ? 'Ouvertes' : 'Fermées'}</h3>
            <p>Inscriptions</p>
            <span className={`stat-trend${registrationOpen ? ' positive' : ''}`}>
              {registrationOpen ? 'Nouveaux comptes possibles' : 'Création de compte désactivée'}
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-projects">
          <div className="stat-icon-admin">
            <i className="fas fa-compass" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>
              <Link href={`${ADMIN_CONSOLE_PATH}/statistics`} className="text-[#C8102E] hover:underline font-bold">
                Statistiques
              </Link>
            </h3>
            <p>Console</p>
            <span className="stat-trend">
              <Link href={`${ADMIN_CONSOLE_PATH}/content`} className="text-[#C8102E] hover:underline">
                Contenus
              </Link>
            </span>
          </div>
        </div>
      </div>

      <SystemSettingsEditor rows={rows} />

      <div className="recent-section">
        <h3 className="mt-0 text-base font-bold">Toutes les clés (lecture seule)</h3>
        <div className="table-responsive">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Clé</th>
                <th>Valeur</th>
                <th>Mis à jour</th>
              </tr>
            </thead>
            <tbody>
              {rows.map(s => (
                <tr key={s.id}>
                  <td className="font-mono text-xs">{s.key}</td>
                  <td className="break-all text-gray-700">{s.value ?? '—'}</td>
                  <td className="text-xs text-gray-500">
                    {new Date(s.updated_at).toLocaleString('fr-FR')}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
