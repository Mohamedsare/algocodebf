import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient, getProfile } from '@/lib/supabase/server'
import { buildAvatarUrl, buildFileUrl, timeAgo, readingTime, formatNumber } from '@/lib/utils'
import { AutoSubmitSelect } from '@/components/blog/auto-submit-select'
import { BlogLiveSearch } from '@/components/blog/blog-live-search'
import { BlogListLive } from '@/components/blog/blog-list-live'
import { NewsletterSaas } from '@/components/blog/newsletter-saas'
import type { BlogPostRow } from '@/components/blog/blog-list-live'

export const metadata: Metadata = {
  title: 'Blog',
  description: 'Actualités tech, formations, conseils carrière et événements du Burkina Faso',
}

const PAGE_SIZE = 12

interface SearchParams {
  page?: string
  category?: string
  sort?: string
  q?: string
}

function slugifyCat(s: string): string {
  return s
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

export default async function BlogPage({
  searchParams,
}: {
  searchParams: Promise<SearchParams>
}) {
  const params = await searchParams
  const page = Math.max(1, parseInt(params.page ?? '1'))
  const category = (params.category ?? '').trim()
  const sort = params.sort ?? 'recent'
  const search = (params.q ?? '').trim()
  const offset = (page - 1) * PAGE_SIZE

  const [supabase, profile] = await Promise.all([createClient(), getProfile()])

  let query = supabase
    .from('blog_posts')
    .select(
      'id, title, slug, excerpt, featured_image, category, views, likes_count, content, created_at, published_at, profiles!inner(id, prenom, nom, photo_path)',
      { count: 'exact' }
    )
    .eq('status', 'published')

  if (category) query = query.eq('category', category)
  if (search) query = query.or(`title.ilike.%${search}%,excerpt.ilike.%${search}%`)

  const sortMap: Record<string, { column: string; ascending: boolean }> = {
    recent: { column: 'published_at', ascending: false },
    views: { column: 'views', ascending: false },
    popular: { column: 'likes_count', ascending: false },
  }
  const { column, ascending } = sortMap[sort] ?? sortMap.recent
  query = query.order(column, { ascending }).range(offset, offset + PAGE_SIZE - 1)

  const { data: postsRaw, count } = await query

  const { data: featured } = await supabase
    .from('blog_posts')
    .select(
      'id, title, slug, excerpt, featured_image, category, content, created_at, profiles!inner(prenom, nom, photo_path)'
    )
    .eq('status', 'published')
    .order('views', { ascending: false })
    .limit(1)
    .maybeSingle()

  const { data: popular } = await supabase
    .from('blog_posts')
    .select('id, title, slug, views, likes_count')
    .eq('status', 'published')
    .order('views', { ascending: false })
    .limit(5)

  const { data: cats } = await supabase
    .from('blog_categories')
    .select('name, slug, icon, color')
    .order('name')

  const baseCats = ['Actualités', 'Tutoriels', 'Carrière', 'Startups', 'Événements']
  const extraCats = (cats ?? []).filter(c => !baseCats.includes(c.name))

  const totalPages = Math.max(1, Math.ceil((count ?? 0) / PAGE_SIZE))
  const isFirstPage = page === 1 && !category && !search

  const buildHref = (overrides: Partial<SearchParams>) => {
    const sp = new URLSearchParams()
    const merged: SearchParams = { ...params, ...overrides }
    if (merged.q) sp.set('q', merged.q)
    if (merged.category) sp.set('category', merged.category)
    if (merged.sort && merged.sort !== 'recent') sp.set('sort', merged.sort)
    if (merged.page && merged.page !== '1') sp.set('page', merged.page)
    const qs = sp.toString()
    return qs ? `/blog?${qs}` : '/blog'
  }

  const initialPosts: BlogPostRow[] = (postsRaw ?? []).map(p => {
    const a = p.profiles as unknown as {
      id: string
      prenom: string | null
      nom: string | null
      photo_path: string | null
    } | null
    return {
      id: p.id,
      title: p.title,
      slug: p.slug,
      excerpt: p.excerpt,
      featured_image: p.featured_image,
      category: p.category,
      views: p.views,
      likes_count: p.likes_count,
      content: p.content,
      created_at: p.created_at,
      published_at: p.published_at,
      author: a
        ? { id: a.id, prenom: a.prenom, nom: a.nom, photo_path: a.photo_path }
        : null,
    }
  })

  return (
    <div className="blog-saas">
      {/* Hero */}
      <section className="bs-hero">
        <div className="container">
          <div className="bs-hero-inner">
            <span className="bs-eyebrow">
              <span className="dot" /> BLOG TECH BURKINA FASO
            </span>
            <h1 className="bs-hero-title">
              Idées, formations &amp; <em>success stories</em> de la tech burkinabè.
            </h1>
            <p className="bs-hero-sub">
              Toute l&apos;actualité, les tendances et les retours d&apos;expérience de la
              communauté des développeurs du Burkina Faso.
            </p>

            <BlogLiveSearch initialQ={search} />
          </div>
        </div>
      </section>

      {/* Featured */}
      {featured && isFirstPage && (
        <section className="bs-featured-section">
          <div className="container">
            <Link
              href={`/blog/${featured.slug ?? featured.id}`}
              className="bs-featured"
            >
              <div className="bs-featured-image">
                <img
                  src={
                    featured.featured_image
                      ? buildFileUrl(featured.featured_image)
                      : '/images/im1.png'
                  }
                  alt={featured.title}
                />
                <span className="bs-featured-badge">
                  <i className="fas fa-fire"></i> À la une
                </span>
              </div>
              <div className="bs-featured-body">
                <div className="bs-featured-meta">
                  <span
                    className={`bs-card-cat cat-${slugifyCat(featured.category ?? 'actualites')}`}
                    style={{ position: 'static' }}
                  >
                    {featured.category ?? 'Actualités'}
                  </span>
                  <span>
                    <i className="far fa-clock"></i> {readingTime(featured.content ?? '')} min
                  </span>
                  <span>·</span>
                  <span>{timeAgo(featured.created_at)}</span>
                </div>
                <h2 className="bs-featured-title">{featured.title}</h2>
                <p className="bs-featured-excerpt">{featured.excerpt ?? ''}</p>
                <div className="bs-featured-footer">
                  <div className="bs-featured-author">
                    <div className="bs-avatar">
                      {(() => {
                        const a = featured.profiles as unknown as {
                          prenom: string | null
                          nom: string | null
                          photo_path: string | null
                        } | null
                        const name = a
                          ? `${a.prenom ?? ''} ${a.nom ?? ''}`.trim() || 'Auteur'
                          : 'Auteur'
                        const initial = name.charAt(0).toUpperCase() || 'A'
                        return a?.photo_path ? (
                          // eslint-disable-next-line @next/next/no-img-element
                          <img src={buildAvatarUrl(a.photo_path)} alt={name} />
                        ) : (
                          <span>{initial}</span>
                        )
                      })()}
                    </div>
                    <div className="bs-author-text">
                      <strong>
                        {(() => {
                          const a = featured.profiles as unknown as {
                            prenom: string | null
                            nom: string | null
                          } | null
                          return a
                            ? `${a.prenom ?? ''} ${a.nom ?? ''}`.trim() || 'Auteur'
                            : 'Auteur'
                        })()}
                      </strong>
                      <span>Auteur · {timeAgo(featured.created_at)}</span>
                    </div>
                  </div>
                  <span className="bs-featured-cta">
                    Lire l&apos;article <i className="fas fa-arrow-right"></i>
                  </span>
                </div>
              </div>
            </Link>
          </div>
        </section>
      )}

      {/* Filters */}
      <section className="bs-filters">
        <div className="container">
          <div className="bs-filters-inner">
            <div className="bs-pills">
              <Link
                href={buildHref({ category: '', page: '1' })}
                className={`bs-pill${!category ? ' active' : ''}`}
              >
                <i className="fas fa-th-large"></i> Tout
              </Link>
              <Link
                href={buildHref({ category: 'Actualités', page: '1' })}
                className={`bs-pill${category === 'Actualités' ? ' active' : ''}`}
              >
                <i className="fas fa-newspaper"></i> Actualités
              </Link>
              <Link
                href={buildHref({ category: 'Tutoriels', page: '1' })}
                className={`bs-pill${category === 'Tutoriels' ? ' active' : ''}`}
              >
                <i className="fas fa-graduation-cap"></i> Formations
              </Link>
              <Link
                href={buildHref({ category: 'Carrière', page: '1' })}
                className={`bs-pill${category === 'Carrière' ? ' active' : ''}`}
              >
                <i className="fas fa-briefcase"></i> Carrière
              </Link>
              <Link
                href={buildHref({ category: 'Startups', page: '1' })}
                className={`bs-pill${category === 'Startups' ? ' active' : ''}`}
              >
                <i className="fas fa-rocket"></i> Startups
              </Link>
              <Link
                href={buildHref({ category: 'Événements', page: '1' })}
                className={`bs-pill${category === 'Événements' ? ' active' : ''}`}
              >
                <i className="fas fa-calendar"></i> Événements
              </Link>
              {extraCats.map(c => (
                <Link
                  key={c.name}
                  href={buildHref({ category: c.name, page: '1' })}
                  className={`bs-pill${category === c.name ? ' active' : ''}`}
                >
                  <i className={c.icon ?? 'fas fa-folder'}></i> {c.name}
                </Link>
              ))}
            </div>

            <div className="bs-sort">
              <form method="get" action="/blog">
                {search && <input type="hidden" name="q" value={search} />}
                {category && <input type="hidden" name="category" value={category} />}
                <AutoSubmitSelect
                  value={sort}
                  options={[
                    { value: 'recent', label: 'Plus récents' },
                    { value: 'popular', label: 'Plus populaires' },
                    { value: 'views', label: 'Plus lus' },
                  ]}
                />
              </form>
            </div>
          </div>
        </div>
      </section>

      {/* Main grid + sidebar */}
      <section className="bs-main">
        <div className="container">
          <div className="bs-layout">
            <div>
              <BlogListLive
                initialPosts={initialPosts}
                category={category}
                sort={sort}
                search={search}
                currentPage={page}
                totalPages={totalPages}
              />
            </div>

            <aside className="bs-aside">
              {profile?.role === 'admin' && (
                <div className="bs-widget bs-widget-cta">
                  <div className="bs-cta-icon">
                    <i className="fas fa-pen-nib"></i>
                  </div>
                  <h3>Nouvel article</h3>
                  <p>Partagez votre expertise avec la communauté.</p>
                  <Link href="/blog/creer" className="bs-btn">
                    <i className="fas fa-plus-circle"></i> Rédiger
                  </Link>
                </div>
              )}

              {popular && popular.length > 0 && (
                <div className="bs-widget">
                  <h3 className="bs-widget-title">
                    <i className="fas fa-fire-alt"></i> Les plus lus
                  </h3>
                  <div className="bs-popular">
                    {popular.slice(0, 5).map((p, i) => (
                      <Link key={p.id} href={`/blog/${p.slug ?? p.id}`} className="bs-popular-item">
                        <div className="bs-rank">{i + 1}</div>
                        <div className="bs-popular-content">
                          <h4>{p.title}</h4>
                          <div className="bs-popular-meta">
                            <span>
                              <i className="far fa-eye"></i> {formatNumber(p.views ?? 0)}
                            </span>
                            <span>
                              <i className="far fa-heart"></i> {p.likes_count ?? 0}
                            </span>
                          </div>
                        </div>
                      </Link>
                    ))}
                  </div>
                </div>
              )}

              <NewsletterSaas />

              <div className="bs-widget">
                <h3 className="bs-widget-title">
                  <i className="fas fa-hashtag"></i> Explorer
                </h3>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                  {['React', 'Next.js', 'Laravel', 'Python', 'Design', 'Mobile', 'DevOps', 'IA'].map(
                    tag => (
                      <Link
                        key={tag}
                        href={`/blog?q=${encodeURIComponent(tag)}`}
                        className="bs-tag"
                      >
                        {tag}
                      </Link>
                    )
                  )}
                </div>
              </div>
            </aside>
          </div>
        </div>
      </section>
    </div>
  )
}
