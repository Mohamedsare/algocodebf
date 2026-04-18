import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient, getProfile } from '@/lib/supabase/server'
import { FormationsLiveSearch } from '@/components/tutorial/formations-live-search'
import { FORMATIONS_PATH } from '@/lib/routes'
import { buildAvatarUrl, buildFileUrl, formatNumber, timeAgo } from '@/lib/utils'

export const metadata: Metadata = {
  title: 'Formations',
  description:
    'Parcours professionnels : vidéos, supports et contenus structurés par des formateurs de la communauté tech du Burkina Faso. Inscription payante et suivi apprenant à venir.',
}

const PAGE_SIZE = 24

interface SearchParams {
  page?: string
  category?: string
  type?: string
  level?: string
  sort?: string
  search?: string
}

const LEVELS = [
  { value: 'Débutant', label: '⭐ Débutant' },
  { value: 'Intermédiaire', label: '⭐⭐ Intermédiaire' },
  { value: 'Avancé', label: '⭐⭐⭐ Avancé' },
]

const TYPES = [
  { value: 'video', label: 'Vidéo', icon: 'fa-video' },
  { value: 'pdf', label: 'PDF', icon: 'fa-file-pdf' },
  { value: 'code', label: 'Code', icon: 'fa-code' },
]

const TYPE_ICONS: Record<string, string> = {
  video: 'fa-play-circle',
  pdf: 'fa-file-pdf',
  code: 'fa-code',
  article: 'fa-file-alt',
}

export default async function TutorialPage({
  searchParams,
}: {
  searchParams: Promise<SearchParams>
}) {
  const params = await searchParams
  const page = Math.max(1, parseInt(params.page ?? '1'))
  const category = (params.category ?? '').trim()
  const type = (params.type ?? '').trim()
  const level = (params.level ?? '').trim()
  const sort = params.sort ?? 'recent'
  const search = (params.search ?? '').trim()
  const offset = (page - 1) * PAGE_SIZE

  const [supabase, profile] = await Promise.all([createClient(), getProfile()])

  let query = supabase
    .from('tutorials')
    .select(
      'id, title, description, type, level, category, views, likes_count, thumbnail, file_path, created_at, user_id, profiles!inner(prenom, nom, photo_path)',
      { count: 'exact' }
    )
    .eq('status', 'active')

  if (category) query = query.eq('category', category)
  if (type) query = query.eq('type', type)
  if (level) query = query.eq('level', level)
  if (search) query = query.ilike('title', `%${search}%`)

  const sortMap: Record<string, { column: string; ascending: boolean }> = {
    recent: { column: 'created_at', ascending: false },
    views: { column: 'views', ascending: false },
    popular: { column: 'likes_count', ascending: false },
    likes: { column: 'likes_count', ascending: false },
  }
  const { column, ascending } = sortMap[sort] ?? sortMap.recent
  query = query.order(column, { ascending }).range(offset, offset + PAGE_SIZE - 1)

  const { data: tutorials, count } = await query

  const { data: cats } = await supabase
    .from('tutorial_categories')
    .select('name')
    .order('name')
  const categories = (cats ?? []).map(c => (c as { name: string }).name)

  const totalPages = Math.max(1, Math.ceil((count ?? 0) / PAGE_SIZE))
  const canCreate = Boolean(profile?.can_create_tutorial) || profile?.role === 'admin'

  const buildHref = (overrides: Partial<SearchParams> = {}) => {
    const merged: SearchParams = { ...params, ...overrides }
    const sp = new URLSearchParams()
    if (merged.search) sp.set('search', merged.search)
    if (merged.category) sp.set('category', merged.category)
    if (merged.type) sp.set('type', merged.type)
    if (merged.level) sp.set('level', merged.level)
    if (merged.sort && merged.sort !== 'recent') sp.set('sort', merged.sort)
    if (merged.page && merged.page !== '1') sp.set('page', merged.page)
    const qs = sp.toString()
    return qs ? `${FORMATIONS_PATH}?${qs}` : FORMATIONS_PATH
  }

  return (
    <div className="formation-saas">
      <section className="ft-cat-hero" aria-label="Présentation du catalogue">
        <div className="container ft-cat-hero-inner">
          <span className="ft-cat-eyebrow">
            <i className="fas fa-certificate" aria-hidden />
            Catalogue
          </span>
          <h1>
            Formations <em>professionnelles</em>
          </h1>
          <p className="ft-cat-lead">
            Des parcours complets, pensés comme des cursus : chapitres, vidéos et ressources au même endroit. Les
            formateurs publient ici leurs formations ; très bientôt, les étudiants pourront s&apos;inscrire, payer en
            ligne et suivre leur progression sans friction.
          </p>
        </div>
      </section>

      <div className="tutorials-page ft-cat-body">
        <div className="tutorials-container">
        <div className="tutorials-search-bar">
          <FormationsLiveSearch initialSearch={search} />
        </div>

        <div className="tutorials-filters">
          <div className="filters-scroll">
            <Link
              href={FORMATIONS_PATH}
              className={`filter-chip${
                !category && !type && !level ? ' active' : ''
              }`}
            >
              <i className="fas fa-th"></i> Tous
            </Link>
            {categories.map(cat => (
              <Link
                key={cat}
                href={buildHref({ category: cat, page: '1' })}
                className={`filter-chip${category === cat ? ' active' : ''}`}
              >
                {cat}
              </Link>
            ))}
            {TYPES.map(t => (
              <Link
                key={t.value}
                href={buildHref({ type: t.value, page: '1' })}
                className={`filter-chip${type === t.value ? ' active' : ''}`}
              >
                <i className={`fas ${t.icon}`}></i> {t.label}
              </Link>
            ))}
            {LEVELS.map(l => (
              <Link
                key={l.value}
                href={buildHref({ level: l.value, page: '1' })}
                className={`filter-chip${level === l.value ? ' active' : ''}`}
              >
                {l.label}
              </Link>
            ))}
          </div>
        </div>

        <div className="tutorials-sort-bar">
          <div className="sort-options">
            <span className="sort-label">Trier par :</span>
            <Link
              href={buildHref({ sort: 'recent' })}
              className={`sort-option${sort === 'recent' ? ' active' : ''}`}
            >
              Plus récents
            </Link>
            <Link
              href={buildHref({ sort: 'popular' })}
              className={`sort-option${sort === 'popular' ? ' active' : ''}`}
            >
              Plus populaires
            </Link>
            <Link
              href={buildHref({ sort: 'views' })}
              className={`sort-option${sort === 'views' ? ' active' : ''}`}
            >
              Plus vus
            </Link>
            <Link
              href={buildHref({ sort: 'likes' })}
              className={`sort-option${sort === 'likes' ? ' active' : ''}`}
            >
              Plus aimés
            </Link>
          </div>

          {canCreate && (
            <Link href={`${FORMATIONS_PATH}/creer`} className="btn-create-tutorial">
              <i className="fas fa-plus"></i> Publier une formation
            </Link>
          )}
        </div>

        {search && (
          <div className="search-results-info">
            <p>
              <strong>{count ?? 0}</strong> formation(s) trouvée(s) pour &quot;
              <strong>{search}</strong>&quot;
            </p>
          </div>
        )}

        <div className="tutorials-grid">
          {!tutorials || tutorials.length === 0 ? (
            <div className="empty-tutorials">
              <div className="empty-icon">
                <i className="fas fa-graduation-cap"></i>
              </div>
              <h3>Aucune formation dans le catalogue</h3>
              <p>
                {search
                  ? 'Aucune formation ne correspond à votre recherche.'
                  : 'Soyez le premier à publier un parcours structuré pour la communauté.'}
              </p>
              {canCreate && (
                <Link href={`${FORMATIONS_PATH}/creer`} className="btn-create-empty">
                  <i className="fas fa-plus"></i> Publier une formation
                </Link>
              )}
            </div>
          ) : (
            tutorials.map(tuto => {
              const author = tuto.profiles as unknown as {
                prenom: string
                nom: string
                photo_path: string | null
              } | null
              const authorName = author ? `${author.prenom} ${author.nom}` : 'Utilisateur'
              const initial = authorName.charAt(0).toUpperCase()
              const fileExt = (tuto.file_path ?? '').split('.').pop()?.toLowerCase() ?? ''
              const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)
              const icon = TYPE_ICONS[tuto.type ?? 'article'] ?? 'fa-book'
              const thumbSrc = tuto.thumbnail
                ? buildFileUrl(tuto.thumbnail)
                : tuto.file_path && isImage
                  ? buildFileUrl(tuto.file_path)
                  : null

              return (
                <div key={tuto.id} className="tutorial-card-youtube">
                  <Link
                    href={`${FORMATIONS_PATH}/${tuto.id}`}
                    className="tutorial-thumbnail-link"
                  >
                    <div className="tutorial-thumbnail-container">
                      {thumbSrc ? (
                        tuto.type === 'video' ? (
                          <div className="tutorial-thumbnail-video">
                            <i className="fas fa-play-circle"></i>
                            <img
                              src={thumbSrc}
                              alt={tuto.title}
                              className="tutorial-thumbnail-img"
                            />
                          </div>
                        ) : (
                          <img
                            src={thumbSrc}
                            alt={tuto.title}
                            className="tutorial-thumbnail-img"
                          />
                        )
                      ) : (
                        <div className="tutorial-thumbnail-placeholder">
                          <i className={`fas ${icon}`}></i>
                        </div>
                      )}
                      {tuto.type === 'video' && (
                        <div className="tutorial-type-badge">
                          <i className="fas fa-video"></i>
                        </div>
                      )}
                    </div>
                  </Link>

                  <div className="tutorial-info">
                    <div className="tutorial-meta">
                      <Link
                        href={`/user/${tuto.user_id}`}
                        className="tutorial-author-avatar"
                      >
                        {author?.photo_path ? (
                          <img
                            src={buildAvatarUrl(author.photo_path)}
                            alt={authorName}
                            className="tutorial-avatar-img"
                          />
                        ) : (
                          <div className="avatar-placeholder">{initial}</div>
                        )}
                      </Link>

                      <div className="tutorial-details">
                        <h3 className="tutorial-title">
                          <Link href={`${FORMATIONS_PATH}/${tuto.id}`}>{tuto.title}</Link>
                        </h3>
                        <Link
                          href={`/user/${tuto.user_id}`}
                          className="tutorial-author-name"
                        >
                          {authorName}
                        </Link>
                        <div className="tutorial-stats">
                          <span className="stat-item">
                            <i className="fas fa-eye"></i>{' '}
                            {formatNumber(tuto.views ?? 0)} vues
                          </span>
                          <span className="stat-separator">•</span>
                          <span className="stat-item">{timeAgo(tuto.created_at)}</span>
                          {(tuto.likes_count ?? 0) > 0 && (
                            <>
                              <span className="stat-separator">•</span>
                              <span className="stat-item">
                                <i className="fas fa-heart"></i>{' '}
                                {formatNumber(tuto.likes_count)}
                              </span>
                            </>
                          )}
                        </div>
                        {tuto.level && (
                          <div className="tutorial-level-badge">{tuto.level}</div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              )
            })
          )}
        </div>

        {totalPages > 1 && (
          <div className="tutorials-pagination">
            {page > 1 && (
              <Link
                href={buildHref({ page: String(page - 1) })}
                className="pagination-btn"
              >
                <i className="fas fa-chevron-left"></i> Précédent
              </Link>
            )}
            <div className="pagination-info">
              Page {page} sur {totalPages}
            </div>
            {page < totalPages && (
              <Link
                href={buildHref({ page: String(page + 1) })}
                className="pagination-btn"
              >
                Suivant <i className="fas fa-chevron-right"></i>
              </Link>
            )}
          </div>
        )}
        </div>
      </div>
    </div>
  )
}
