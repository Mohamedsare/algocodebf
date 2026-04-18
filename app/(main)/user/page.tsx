import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { listUsers, getUserFilterOptions } from '@/lib/queries/users'
import { MembersLiveSearch } from '@/components/user/members-live-search'
import { buildAvatarUrl } from '@/lib/utils'

export const metadata: Metadata = {
  title: 'Membres - AlgoCodeBF',
  description:
    'Découvrez les membres actifs de la communauté AlgoCodeBF : développeurs, étudiants, mentors et entreprises au Burkina Faso.',
}

export const revalidate = 60

interface PageProps {
  searchParams: Promise<{
    q?: string
    university?: string
    city?: string
    sort?: 'recent' | 'name' | 'popular'
    page?: string
  }>
}

async function getStats() {
  const supabase = await createClient()
  const [
    { count: totalMembers },
    { data: unis },
    { count: skillsCount },
  ] = await Promise.all([
    supabase
      .from('profiles')
      .select('*', { count: 'exact', head: true })
      .eq('status', 'active'),
    supabase
      .from('profiles')
      .select('university')
      .eq('status', 'active')
      .not('university', 'is', null),
    supabase.from('skills').select('*', { count: 'exact', head: true }),
  ])

  const startOfMonth = new Date()
  startOfMonth.setDate(1)
  startOfMonth.setHours(0, 0, 0, 0)

  const { count: newThisMonth } = await supabase
    .from('profiles')
    .select('*', { count: 'exact', head: true })
    .eq('status', 'active')
    .gte('created_at', startOfMonth.toISOString())

  const universitiesCount = new Set(
    (unis ?? []).map(u => (u.university ?? '').trim()).filter(Boolean)
  ).size

  return {
    total_members: totalMembers ?? 0,
    universities: universitiesCount,
    skills_count: skillsCount ?? 0,
    new_this_month: newThisMonth ?? 0,
  }
}

export default async function UsersPage({ searchParams }: PageProps) {
  const sp = await searchParams
  const page = Math.max(1, parseInt(sp.page ?? '1', 10) || 1)

  const [stats, { users, total, totalPages }, options] = await Promise.all([
    getStats(),
    listUsers({
      search: sp.q,
      university: sp.university,
      city: sp.city,
      sort: sp.sort,
      page,
    }),
    getUserFilterOptions(),
  ])

  const buildHref = (patch: Record<string, string | undefined>) => {
    const next = new URLSearchParams()
    const merged: Record<string, string | undefined> = {
      q: sp.q,
      university: sp.university,
      city: sp.city,
      sort: sp.sort,
      page: String(page),
      ...patch,
    }
    for (const [k, v] of Object.entries(merged)) {
      if (v && v !== '') next.set(k, v)
    }
    const qs = next.toString()
    return qs ? `/user?${qs}` : '/user'
  }

  return (
    <div className="members-saas">
      <section className="mem-hero">
        <div className="container">
          <div className="mem-hero-inner">
            <p className="mem-eyebrow">
              <i className="fas fa-users" aria-hidden="true"></i> Réseau AlgoCodeBF
            </p>
            <h1>
              La communauté <span>tech du Faso</span>
            </h1>
            <p>
              Développeurs, étudiants, mentors et entreprises — connectez-vous avec les talents du
              Burkina.
            </p>
          </div>
        </div>
      </section>

      <section className="mem-stats" aria-label="Statistiques membres">
        <div className="container">
          <div className="mem-stats-grid">
            <div className="mem-stat">
              <div className="mem-stat-icon">
                <i className="fas fa-users" aria-hidden="true"></i>
              </div>
              <div>
                <strong>{stats.total_members}</strong>
                <span>Membres actifs</span>
              </div>
            </div>
            <div className="mem-stat">
              <div className="mem-stat-icon">
                <i className="fas fa-university" aria-hidden="true"></i>
              </div>
              <div>
                <strong>{stats.universities}</strong>
                <span>Universités</span>
              </div>
            </div>
            <div className="mem-stat">
              <div className="mem-stat-icon">
                <i className="fas fa-code" aria-hidden="true"></i>
              </div>
              <div>
                <strong>{stats.skills_count}</strong>
                <span>Compétences</span>
              </div>
            </div>
            <div className="mem-stat">
              <div className="mem-stat-icon">
                <i className="fas fa-user-plus" aria-hidden="true"></i>
              </div>
              <div>
                <strong>{stats.new_this_month}</strong>
                <span>Ce mois-ci</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="mem-filters" aria-label="Recherche et filtres">
        <div className="container">
          <form method="get" className="mem-filters-card" action="/user">
            <MembersLiveSearch initialQ={sp.q ?? ''} />
            <div className="mem-filters-row">
              <div className="mem-field">
                <label htmlFor="mem-uni">
                  <i className="fas fa-university" aria-hidden="true"></i> Université
                </label>
                <select id="mem-uni" name="university" defaultValue={sp.university ?? ''}>
                  <option value="">Toutes</option>
                  {options.universities.map(u => (
                    <option key={u} value={u}>
                      {u}
                    </option>
                  ))}
                </select>
              </div>

              <div className="mem-field">
                <label htmlFor="mem-city">
                  <i className="fas fa-map-marker-alt" aria-hidden="true"></i> Ville
                </label>
                <select id="mem-city" name="city" defaultValue={sp.city ?? ''}>
                  <option value="">Toutes</option>
                  {options.cities.map(c => (
                    <option key={c} value={c}>
                      {c}
                    </option>
                  ))}
                </select>
              </div>

              <div className="mem-field">
                <label htmlFor="mem-sort">
                  <i className="fas fa-sort" aria-hidden="true"></i> Trier
                </label>
                <select id="mem-sort" name="sort" defaultValue={sp.sort ?? 'recent'}>
                  <option value="recent">Plus récents</option>
                  <option value="name">Nom (A–Z)</option>
                  <option value="popular">Plus actifs</option>
                </select>
              </div>

              <div className="mem-field mem-submit-wrap">
                <span className="mem-submit-label" aria-hidden="true">
                  .
                </span>
                <button type="submit" className="mem-filter-submit">
                  <i className="fas fa-filter" aria-hidden="true"></i> Appliquer
                </button>
              </div>
            </div>
          </form>
        </div>
      </section>

      <div className="container" style={{ paddingBottom: 'clamp(40px, 8vw, 72px)' }}>
        <div className="mem-grid">
          {users.length === 0 ? (
            <div className="mem-empty">
              <i className="fas fa-users" aria-hidden="true"></i>
              <h3>Aucun membre trouvé</h3>
              <p>Aucun profil ne correspond à ces critères.</p>
              <Link href="/user" className="mem-btn-reset">
                <i className="fas fa-times" aria-hidden="true"></i> Réinitialiser
              </Link>
            </div>
          ) : (
            users.map(u => {
              const fullName = `${u.prenom ?? ''} ${u.nom ?? ''}`.trim()
              const initial = (u.prenom ?? 'U').charAt(0).toUpperCase()
              const roleLabel =
                u.faculty ||
                (u.role === 'admin' ? 'Admin' : u.role === 'company' ? 'Entreprise' : 'Membre')
              return (
                <article key={u.id} className="mem-card">
                  <div className="mem-banner" aria-hidden="true"></div>
                  <div className="mem-avatar-wrap">
                    <div className="mem-avatar">
                      {u.photo_path ? (
                        // eslint-disable-next-line @next/next/no-img-element
                        <img src={buildAvatarUrl(u.photo_path)} alt="" />
                      ) : (
                        <span aria-hidden="true">{initial}</span>
                      )}
                    </div>
                  </div>
                  <div className="mem-body">
                    <h2 className="mem-name">
                      <Link href={`/user/${u.id}`}>{fullName || 'Membre'}</Link>
                    </h2>
                    <p className="mem-role">{roleLabel}</p>
                    <div className="mem-meta">
                      <div className="mem-meta-row">
                        <i className="fas fa-university" aria-hidden="true"></i>
                        <span>{u.university || 'Non spécifié'}</span>
                      </div>
                      <div className="mem-meta-row">
                        <i className="fas fa-map-marker-alt" aria-hidden="true"></i>
                        <span>{u.city || 'Non spécifié'}</span>
                      </div>
                    </div>
                    <div className="mem-actions">
                      <Link href={`/user/${u.id}`} className="mem-btn-profile">
                        <i className="fas fa-eye" aria-hidden="true"></i> Profil
                      </Link>
                      <Link
                        href={`/message/composer?receiver=${u.id}`}
                        className="mem-btn-msg"
                        aria-label={`Contacter ${fullName || 'ce membre'}`}
                      >
                        <i className="fas fa-envelope" aria-hidden="true"></i>
                      </Link>
                    </div>
                  </div>
                </article>
              )
            })
          )}
        </div>

        {totalPages > 1 && (
          <nav className="mem-pagination" aria-label="Pagination">
            {page > 1 && (
              <Link href={buildHref({ page: String(page - 1) })} className="mem-page-link">
                <i className="fas fa-chevron-left" aria-hidden="true"></i> Préc.
              </Link>
            )}
            {Array.from({ length: totalPages }, (_, i) => i + 1)
              .filter(p => p === 1 || p === totalPages || (p >= page - 2 && p <= page + 2))
              .map((p, idx, arr) => {
                const prev = arr[idx - 1]
                const needsEllipsis = prev !== undefined && p - prev > 1
                return (
                  <span key={p} style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}>
                    {needsEllipsis && <span className="mem-page-ellipsis">…</span>}
                    <Link
                      href={buildHref({ page: String(p) })}
                      className={`mem-page-link${p === page ? ' active' : ''}`}
                    >
                      {p}
                    </Link>
                  </span>
                )
              })}
            {page < totalPages && (
              <Link href={buildHref({ page: String(page + 1) })} className="mem-page-link">
                Suiv. <i className="fas fa-chevron-right" aria-hidden="true"></i>
              </Link>
            )}
          </nav>
        )}

        <p className="mem-footer-count">
          <strong>{total}</strong> membre{total > 1 ? 's' : ''} correspondant
          {total > 1 ? 's' : ''} à votre recherche
        </p>
      </div>
    </div>
  )
}
