import type { Metadata } from 'next'
import Link from 'next/link'
import { Suspense } from 'react'
import { createClient, getProfile } from '@/lib/supabase/server'
import {
  FormationCatalogCard,
  type FormationCatalogTutorial,
} from '@/components/tutorial/formation-catalog-card'
import { FormationsLiveSearch } from '@/components/tutorial/formations-live-search'
import { FORMATIONS_PATH } from '@/lib/routes'

export const metadata: Metadata = {
  title: 'Formations',
  description:
    'Parcours professionnels : vidéos, supports et contenus structurés par des formateurs de la communauté tech du Burkina Faso. Inscription payante et suivi apprenant à venir.',
}

const PAGE_SIZE = 24

/** Anciens filtres URL (libellés FR / types PHP) → valeurs DB. */
function normalizeLevelParam(raw: string): string {
  const m: Record<string, string> = {
    Débutant: 'beginner',
    débutant: 'beginner',
    Intermédiaire: 'intermediate',
    intermédiaire: 'intermediate',
    Avancé: 'advanced',
    avancé: 'advanced',
  }
  const t = raw.trim()
  return m[t] ?? t
}

function normalizeTypeParam(raw: string): string {
  const m: Record<string, string> = {
    pdf: 'text',
    code: 'mixed',
    article: 'text',
  }
  const t = raw.trim()
  return m[t] ?? t
}

interface SearchParams {
  page?: string
  category?: string
  type?: string
  level?: string
  sort?: string
  search?: string
}

/* Valeurs = colonnes DB (`level`, `type`), pas les libellés français. */
const LEVELS = [
  { value: 'beginner', label: '⭐ Débutant' },
  { value: 'intermediate', label: '⭐⭐ Intermédiaire' },
  { value: 'advanced', label: '⭐⭐⭐ Avancé' },
]

const TYPES = [
  { value: 'video', label: 'Vidéo', icon: 'fa-video' },
  { value: 'text', label: 'Texte / PDF / lecture', icon: 'fa-align-left' },
  { value: 'mixed', label: 'Mixte (vidéo + texte, code…)', icon: 'fa-layer-group' },
]

/** Supabase peut typer `profiles` en objet ou en tableau selon la génération des types. */
function normalizeCatalogProfile(raw: unknown): FormationCatalogTutorial['profiles'] {
  if (raw == null) return null
  type Row = { prenom?: string | null; nom?: string | null; photo_path?: string | null }
  const pick = (o: Row): NonNullable<FormationCatalogTutorial['profiles']> => ({
    prenom: o.prenom ?? null,
    nom: o.nom ?? null,
    photo_path: o.photo_path ?? null,
  })
  if (Array.isArray(raw)) {
    const first = raw[0] as Row | undefined
    return first ? pick(first) : null
  }
  return pick(raw as Row)
}

function normalizeCatalogVideos(raw: unknown): FormationCatalogTutorial['tutorial_videos'] {
  if (raw == null) return null
  if (!Array.isArray(raw)) return null
  return raw.map(v => {
    const o = v as { file_path?: string | null; order_index?: number | null }
    return { file_path: o.file_path ?? null, order_index: o.order_index ?? null }
  })
}

export default async function TutorialPage({
  searchParams,
}: {
  searchParams: Promise<SearchParams>
}) {
  const params = await searchParams
  const pageNum = parseInt(params.page ?? '1', 10)
  const page = Math.max(1, Number.isFinite(pageNum) ? pageNum : 1)
  const category = (params.category ?? '').trim()
  const type = normalizeTypeParam(params.type ?? '')
  const level = normalizeLevelParam(params.level ?? '')
  const sort = params.sort ?? 'recent'
  const search = (params.search ?? '').trim()
  const offset = (page - 1) * PAGE_SIZE

  const [supabase, profile] = await Promise.all([createClient(), getProfile()])

  /* Jointure profil en LEFT : évite d’exclure une formation si le profil auteur manque (données legacy, etc.). */
  let query = supabase
    .from('tutorials')
    /* Pas de `file_path` sur `tutorials` (schéma Supabase) : seulement sur `tutorial_videos`. Le demander casse toute la requête. */
    .select(
      'id, title, description, type, level, category, views, likes_count, thumbnail, created_at, user_id, profiles(prenom, nom, photo_path), tutorial_videos(file_path, order_index)',
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
          <Suspense
            fallback={
              <div className="search-form-tutorials live-url-search" aria-hidden>
                <div className="search-input-wrapper">
                  <input
                    type="search"
                    readOnly
                    tabIndex={-1}
                    className="opacity-60"
                    placeholder="Rechercher une formation…"
                    defaultValue={search}
                  />
                  <button type="button" className="search-btn" tabIndex={-1}>
                    <i className="fas fa-search" />
                  </button>
                </div>
              </div>
            }
          >
            <FormationsLiveSearch initialSearch={search} />
          </Suspense>
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

        <div className="home-saas ft-catalog-home-scope">
          <div className="hm-grid hm-grid-tuto">
            {!tutorials || tutorials.length === 0 ? (
              <div className="hm-empty">
                <i className="fas fa-graduation-cap" aria-hidden />
                <p>
                  {search
                    ? 'Aucune formation ne correspond à votre recherche.'
                    : 'Aucune formation pour le moment. Soyez le premier à publier un parcours structuré pour la communauté.'}
                </p>
                {canCreate && (
                  <Link href={`${FORMATIONS_PATH}/creer`} className="btn-create-empty">
                    <i className="fas fa-plus"></i> Publier une formation
                  </Link>
                )}
              </div>
            ) : (
              tutorials.map(tuto => (
                <FormationCatalogCard
                  key={tuto.id}
                  tuto={{
                    id: tuto.id,
                    title: tuto.title,
                    description: tuto.description,
                    type: tuto.type ?? 'video',
                    views: tuto.views ?? 0,
                    likes_count: tuto.likes_count ?? 0,
                    thumbnail: tuto.thumbnail,
                    tutorial_videos: normalizeCatalogVideos(tuto.tutorial_videos),
                    profiles: normalizeCatalogProfile(tuto.profiles),
                  }}
                />
              ))
            )}
          </div>
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
