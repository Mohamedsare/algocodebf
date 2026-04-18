import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { ADMIN_CONSOLE_PATH } from '@/lib/routes'
import { formatNumber } from '@/lib/utils'

export const metadata: Metadata = { title: 'Statistiques (admin)' }
export const dynamic = 'force-dynamic'

const ADMIN_SHORTCUTS: { href: string; label: string; icon: string }[] = [
  { href: `${ADMIN_CONSOLE_PATH}/users`, label: 'Utilisateurs', icon: 'fa-users' },
  { href: `${ADMIN_CONSOLE_PATH}/forum`, label: 'Forum', icon: 'fa-comments' },
  { href: `${ADMIN_CONSOLE_PATH}/tutorials`, label: 'Formations', icon: 'fa-book-open' },
  { href: `${ADMIN_CONSOLE_PATH}/projects`, label: 'Projets', icon: 'fa-project-diagram' },
  { href: `${ADMIN_CONSOLE_PATH}/jobs`, label: 'Opportunités', icon: 'fa-briefcase' },
  { href: `${ADMIN_CONSOLE_PATH}/blog`, label: 'Blog', icon: 'fa-blog' },
  { href: `${ADMIN_CONSOLE_PATH}/comments`, label: 'Commentaires', icon: 'fa-comment-dots' },
  { href: `${ADMIN_CONSOLE_PATH}/reports`, label: 'Signalements', icon: 'fa-flag' },
  { href: `${ADMIN_CONSOLE_PATH}/newsletter`, label: 'Newsletter', icon: 'fa-envelope-open-text' },
]

export default async function AdminStatisticsPage() {
  const supabase = await createClient()

  const [
    { count: profilesTotal },
    { count: profilesActive },
    { count: postsActive },
    { count: postsInactive },
    { count: tutorialsActive },
    { count: tutorialsInactive },
    { count: projectsTotal },
    { count: jobsActive },
    { count: jobsExpired },
    { count: jobsClosed },
    { count: blogPublished },
    { count: blogDraft },
    { count: blogArchived },
    { count: commentsActive },
    { count: commentsDeleted },
    { count: likesTotal },
    { count: applicationsTotal },
    { count: applicationsPending },
    { count: reportsPending },
    { count: reportsResolved },
    { count: subscribersActive },
  ] = await Promise.all([
    supabase.from('profiles').select('*', { count: 'exact', head: true }),
    supabase.from('profiles').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('posts').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('posts').select('*', { count: 'exact', head: true }).eq('status', 'inactive'),
    supabase.from('tutorials').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('tutorials').select('*', { count: 'exact', head: true }).eq('status', 'inactive'),
    supabase.from('projects').select('*', { count: 'exact', head: true }),
    supabase.from('jobs').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('jobs').select('*', { count: 'exact', head: true }).eq('status', 'expired'),
    supabase.from('jobs').select('*', { count: 'exact', head: true }).eq('status', 'closed'),
    supabase.from('blog_posts').select('*', { count: 'exact', head: true }).eq('status', 'published'),
    supabase.from('blog_posts').select('*', { count: 'exact', head: true }).eq('status', 'draft'),
    supabase.from('blog_posts').select('*', { count: 'exact', head: true }).eq('status', 'archived'),
    supabase.from('comments').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('comments').select('*', { count: 'exact', head: true }).eq('status', 'deleted'),
    supabase.from('likes').select('*', { count: 'exact', head: true }),
    supabase.from('applications').select('*', { count: 'exact', head: true }),
    supabase.from('applications').select('*', { count: 'exact', head: true }).eq('status', 'pending'),
    supabase.from('reports').select('*', { count: 'exact', head: true }).eq('status', 'pending'),
    supabase.from('reports').select('*', { count: 'exact', head: true }).eq('status', 'resolved'),
    supabase.from('newsletter_subscribers').select('*', { count: 'exact', head: true }).eq('status', 'active'),
  ])

  const { data: topProfiles } = await supabase
    .from('profiles')
    .select('id, prenom, nom, points, role')
    .eq('status', 'active')
    .order('points', { ascending: false })
    .limit(12)

  const contentTotal =
    (postsActive ?? 0) + (tutorialsActive ?? 0) + (blogPublished ?? 0) + (projectsTotal ?? 0)
  const mix = [
    { label: 'Forum (sujets actifs)', value: postsActive ?? 0, color: '#3498db' },
    { label: 'Formations publiées', value: tutorialsActive ?? 0, color: '#ffc107' },
    { label: 'Articles blog', value: blogPublished ?? 0, color: '#e74c3c' },
    { label: 'Projets', value: projectsTotal ?? 0, color: '#27ae60' },
  ]

  return (
    <div className="space-y-6">
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Statistiques</h1>
        <p className="text-sm text-gray-600 m-0">
          Vue agrégée de la plateforme (équivalent partiel du dashboard PHP). Les métriques « visiteurs / pays /
          appareils » ne sont pas dans ce schéma Supabase.
        </p>
      </div>

      <div>
        <h2 className="text-sm font-bold uppercase tracking-wide text-gray-500 m-0 mb-2">Raccourcis console</h2>
        <div className="flex flex-wrap gap-2">
          {ADMIN_SHORTCUTS.map(s => (
            <Link
              key={s.href}
              href={s.href}
              className="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm font-semibold text-[var(--dark-color,#2c3e50)] shadow-sm hover:border-[#C8102E] hover:text-[#C8102E] no-underline transition-colors"
            >
              <i className={`fas ${s.icon}`} aria-hidden />
              {s.label}
            </Link>
          ))}
        </div>
      </div>

      <div className="stats-grid-admin">
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-users"></i>
          </div>
          <div className="stat-data">
            <h3>{formatNumber(profilesActive ?? 0)}</h3>
            <p>Profils actifs</p>
            <span className="stat-trend">{formatNumber(profilesTotal ?? 0)} au total</span>
          </div>
        </div>
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-heart"></i>
          </div>
          <div className="stat-data">
            <h3>{formatNumber(likesTotal ?? 0)}</h3>
            <p>J’aime</p>
          </div>
        </div>
        <div className="stat-card-admin card-tutorials">
          <div className="stat-icon-admin">
            <i className="fas fa-comment-dots"></i>
          </div>
          <div className="stat-data">
            <h3>{formatNumber(commentsActive ?? 0)}</h3>
            <p>Commentaires visibles</p>
            <span className="stat-trend warning">{formatNumber(commentsDeleted ?? 0)} supprimés</span>
          </div>
        </div>
        <div className="stat-card-admin card-reports">
          <div className="stat-icon-admin">
            <i className="fas fa-paper-plane"></i>
          </div>
          <div className="stat-data">
            <h3>{formatNumber(applicationsPending ?? 0)}</h3>
            <p>Candidatures en attente</p>
            <span className="stat-trend">{formatNumber(applicationsTotal ?? 0)} au total</span>
          </div>
        </div>
      </div>

      <div className="charts-row">
        <div className="chart-card">
          <h3>
            <i className="fas fa-layer-group"></i> Répartition du contenu public
          </h3>
          {contentTotal === 0 ? (
            <p className="text-sm text-gray-500">Pas encore de contenu.</p>
          ) : (
            <div className="admin-content-mix">
              {mix.map(row => {
                const pct = Math.round((row.value / contentTotal) * 1000) / 10
                return (
                  <div key={row.label} className="admin-content-mix-row">
                    <span className="w-[160px] shrink-0 text-gray-700">{row.label}</span>
                    <div className="admin-content-mix-barwrap">
                      <div
                        className="admin-content-mix-bar"
                        style={{ width: `${pct}%`, background: row.color }}
                      />
                    </div>
                    <span className="w-14 text-right text-xs font-semibold text-gray-600">{pct}%</span>
                    <span className="w-10 text-right text-xs text-gray-500">{row.value}</span>
                  </div>
                )
              })}
            </div>
          )}
        </div>
        <div className="chart-card">
          <h3>
            <i className="fas fa-clipboard-list"></i> Modération & signalements
          </h3>
          <ul className="list-none m-0 p-0 space-y-2 text-sm">
            <li className="flex justify-between border-b border-gray-100 pb-2">
              <span>Signalements en attente</span>
              <strong>{reportsPending ?? 0}</strong>
            </li>
            <li className="flex justify-between border-b border-gray-100 pb-2">
              <span>Signalements résolus</span>
              <strong>{reportsResolved ?? 0}</strong>
            </li>
            <li className="flex justify-between border-b border-gray-100 pb-2">
              <span>Sujets forum inactifs</span>
              <strong>{postsInactive ?? 0}</strong>
            </li>
            <li className="flex justify-between border-b border-gray-100 pb-2">
              <span>Formations inactives</span>
              <strong>{tutorialsInactive ?? 0}</strong>
            </li>
            <li className="flex justify-between border-b border-gray-100 pb-2">
              <span>Offres expirées</span>
              <strong>{jobsExpired ?? 0}</strong>
            </li>
            <li className="flex justify-between border-b border-gray-100 pb-2">
              <span>Offres fermées</span>
              <strong>{jobsClosed ?? 0}</strong>
            </li>
            <li className="flex justify-between border-b border-gray-100 pb-2">
              <span>Brouillons blog</span>
              <strong>{blogDraft ?? 0}</strong>
            </li>
            <li className="flex justify-between pb-1">
              <span>Articles blog archivés</span>
              <strong>{blogArchived ?? 0}</strong>
            </li>
            <li className="flex justify-between pt-2 border-t border-gray-100">
              <span>Abonnés newsletter actifs</span>
              <strong>{subscribersActive ?? 0}</strong>
            </li>
            <li className="flex justify-between">
              <span>Offres actives</span>
              <strong>{jobsActive ?? 0}</strong>
            </li>
          </ul>
        </div>
      </div>

      <div className="recent-section">
        <div className="section-header">
          <h2>
            <i className="fas fa-trophy"></i> Top profils par points
          </h2>
        </div>
        <div className="table-responsive">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Utilisateur</th>
                <th>Rôle</th>
                <th>Points</th>
              </tr>
            </thead>
            <tbody>
              {(topProfiles ?? []).length === 0 ? (
                <tr>
                  <td colSpan={3} className="text-center text-gray-500 py-6">
                    Aucun profil.
                  </td>
                </tr>
              ) : (
                (topProfiles ?? []).map(p => {
                  const name = `${p.prenom ?? ''} ${p.nom ?? ''}`.trim() || 'Utilisateur'
                  return (
                    <tr key={p.id}>
                      <td>
                        <Link href={`/user/${p.id}`} className="font-semibold hover:underline">
                          {name}
                        </Link>
                      </td>
                      <td className="uppercase text-xs">{p.role}</td>
                      <td className="font-bold">{formatNumber(p.points ?? 0)}</td>
                    </tr>
                  )
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
