import type { Metadata } from 'next'
import Link from 'next/link'
import { ADMIN_CONSOLE_PATH } from '@/lib/routes'
import { formatNumber } from '@/lib/utils'

export const metadata: Metadata = { title: 'Logs (admin)' }
export const dynamic = 'force-dynamic'

const AUDIT_LINKS: { href: string; label: string; hint: string }[] = [
  {
    href: `${ADMIN_CONSOLE_PATH}/reports`,
    label: 'Signalements',
    hint: 'Traitement des contenus signalés',
  },
  {
    href: `${ADMIN_CONSOLE_PATH}/users`,
    label: 'Utilisateurs',
    hint: 'Rôles, statuts, comptes',
  },
  {
    href: `${ADMIN_CONSOLE_PATH}/comments`,
    label: 'Commentaires',
    hint: 'Modération des échanges',
  },
  {
    href: `${ADMIN_CONSOLE_PATH}/statistics`,
    label: 'Statistiques',
    hint: 'Vue agrégée de l’activité',
  },
]

export default function AdminLogsPage() {
  return (
    <div className="space-y-6">
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Journaux d&apos;activité</h1>
        <p className="text-sm text-gray-600 m-0">
          Le dashboard PHP listait une table <code className="text-xs bg-gray-100 px-1 rounded">activity_logs</code>.
          Ce schéma Supabase ne l&apos;inclut pas encore : l&apos;audit applicatif passe par le tableau de bord
          Supabase et les écrans ci-dessous.
        </p>
      </div>

      <div className="stats-grid-admin">
        <div className="stat-card-admin card-reports">
          <div className="stat-icon-admin">
            <i className="fas fa-database" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{formatNumber(0)}</h3>
            <p>Lignes locales</p>
            <span className="stat-trend" style={{ color: 'var(--dark-color, #334155)' }}>
              Table non créée
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-cloud" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>
              <i className="fas fa-external-link-alt text-lg" aria-hidden />
            </h3>
            <p>Supabase</p>
            <span className="stat-trend positive">Logs &amp; observabilité</span>
          </div>
        </div>
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-user-shield" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>
              <i className="fas fa-key text-lg" aria-hidden />
            </h3>
            <p>Auth</p>
            <span className="stat-trend">Sessions &amp; JWT</span>
          </div>
        </div>
      </div>

      <div className="charts-row">
        <div className="chart-card">
          <h3>
            <i className="fas fa-list-check" aria-hidden /> Où trouver l&apos;historique aujourd&apos;hui
          </h3>
          <ul className="list-none m-0 p-0 space-y-3 text-sm text-gray-700">
            <li className="flex gap-2">
              <i className="fas fa-check text-emerald-600 mt-0.5" aria-hidden />
              <span>
                <strong>Projet Supabase</strong> → onglets <em>Logs</em>, <em>Auth</em>, requêtes lentes et erreurs API.
              </span>
            </li>
            <li className="flex gap-2">
              <i className="fas fa-check text-emerald-600 mt-0.5" aria-hidden />
              <span>
                <strong>Base</strong> → éditeur SQL / historique des migrations pour tracer les changements de schéma.
              </span>
            </li>
            <li className="flex gap-2">
              <i className="fas fa-check text-emerald-600 mt-0.5" aria-hidden />
              <span>
                <strong>Cette console</strong> → signalements, commentaires et utilisateurs pour les actions de
                modération.
              </span>
            </li>
          </ul>
          <p className="text-xs text-gray-500 m-0 mt-4">
            Pour reproduire le PHP à l&apos;identique, ajoutez une table{' '}
            <code className="bg-gray-100 px-1 rounded">activity_logs</code> (migration + écriture côté server actions)
            puis branchez cette page sur une lecture paginée.
          </p>
        </div>
        <div className="chart-card">
          <h3>
            <i className="fas fa-link" aria-hidden /> Raccourcis audit
          </h3>
          <ul className="list-none m-0 p-0 space-y-2">
            {AUDIT_LINKS.map(item => (
              <li key={item.href}>
                <Link href={item.href} className="font-semibold text-[#C8102E] hover:underline">
                  {item.label}
                </Link>
                <span className="text-sm text-gray-600 block">{item.hint}</span>
              </li>
            ))}
            <li className="pt-2 border-t border-gray-100 mt-2">
              <Link href={ADMIN_CONSOLE_PATH} className="btn-view-all inline-flex items-center gap-2 text-sm">
                <i className="fas fa-chart-pie" aria-hidden /> Tableau de bord admin
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </div>
  )
}
