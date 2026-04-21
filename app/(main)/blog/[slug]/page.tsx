import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import Link from 'next/link'
import { createClient, getProfile } from '@/lib/supabase/server'
import { BlogActionsRail } from '@/components/blog/blog-actions-rail'
import { BlogCommentsLive } from '@/components/blog/blog-comments-live'
import { BlogReadingProgress } from '@/components/blog/blog-reading-progress'
import { BlogToc } from '@/components/blog/blog-toc'
import {
  buildAvatarUrl,
  buildFileUrl,
  readingTime,
  timeAgo,
  formatNumber,
} from '@/lib/utils'
import { markdownToHtml } from '@/lib/markdown'

interface Props {
  params: Promise<{ slug: string }>
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params
  const supabase = await createClient()
  const { data } = await supabase
    .from('blog_posts')
    .select('title, excerpt, featured_image')
    .or(`slug.eq.${slug},id.eq.${Number.isFinite(Number(slug)) ? Number(slug) : -1}`)
    .maybeSingle()
  return {
    title: data?.title ?? 'Article',
    description: data?.excerpt ?? undefined,
    openGraph: data?.featured_image
      ? { images: [{ url: buildFileUrl(data.featured_image) }] }
      : undefined,
  }
}

function slugifyCat(s: string): string {
  return s
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

export default async function BlogArticlePage({ params }: Props) {
  const { slug } = await params
  const [supabase, profile] = await Promise.all([createClient(), getProfile()])

  const numericId = Number(slug)
  const filter = Number.isFinite(numericId)
    ? `slug.eq.${slug},id.eq.${numericId}`
    : `slug.eq.${slug}`

  const { data: post } = await supabase
    .from('blog_posts')
    .select('*, profiles!inner(id, prenom, nom, photo_path, university, bio)')
    .or(filter)
    .eq('status', 'published')
    .maybeSingle()

  if (!post) notFound()

  // Increment views via RPC (atomic, bypasses RLS). Fire-and-forget.
  void supabase.rpc('increment_blog_views', { p_id: post.id }).then(() => {})

  const { data: popular } = await supabase
    .from('blog_posts')
    .select('id, title, slug, featured_image, views, likes_count, category')
    .eq('status', 'published')
    .neq('id', post.id)
    .order('views', { ascending: false })
    .limit(3)

  const { data: related } = await supabase
    .from('blog_posts')
    .select('id, title, slug, featured_image, category, content, published_at, created_at')
    .eq('status', 'published')
    .eq('category', post.category ?? '')
    .neq('id', post.id)
    .limit(3)

  // Count comments
  const { count: commentsCount } = await supabase
    .from('comments')
    .select('id', { count: 'exact', head: true })
    .eq('commentable_type', 'blog')
    .eq('commentable_id', post.id)
    .eq('status', 'active')

  let liked = false
  if (profile) {
    const { data: like } = await supabase
      .from('likes')
      .select('id')
      .eq('user_id', profile.id)
      .eq('likeable_type', 'blog')
      .eq('likeable_id', post.id)
      .maybeSingle()
    liked = !!like
  }

  const author = post.profiles as unknown as {
    id: string
    prenom: string
    nom: string
    photo_path: string | null
    university: string | null
    bio: string | null
  }
  const authorName = `${author.prenom} ${author.nom}`.trim() || 'Auteur'
  const authorInitial = (authorName.charAt(0) || 'A').toUpperCase()
  const authorPhoto = buildAvatarUrl(author.photo_path)

  const heroImage = post.featured_image ? buildFileUrl(post.featured_image) : null

  const contentHtml = markdownToHtml(post.content ?? '')
  const canEdit = profile && (profile.role === 'admin' || profile.id === author.id)

  const tags: string[] = (post.tags ?? '')
    .split(',')
    .map((t: string) => t.trim())
    .filter((t: string) => t !== '')

  const cat = post.category ?? 'Actualités'

  return (
    <div className="blog-saas">
      <BlogReadingProgress />

      {/* Article */}
      <article className="bs-article">
        <header className="bs-article-header">
          <nav className="bs-breadcrumb" aria-label="Fil d'ariane">
            <Link href="/">Accueil</Link>
            <i className="fas fa-chevron-right"></i>
            <Link href="/blog">Blog</Link>
            <i className="fas fa-chevron-right"></i>
            <Link href={`/blog?category=${encodeURIComponent(cat)}`}>{cat}</Link>
          </nav>

          <span className={`bs-article-cat`} style={{
            background: 'var(--bsaas-red-soft)',
            color: 'var(--bsaas-red)',
          }}>
            <i className="fas fa-tag"></i> {cat}
          </span>

          <h1 className="bs-article-title">{post.title}</h1>

          {post.excerpt && <p className="bs-article-excerpt">{post.excerpt}</p>}

          <div className="bs-article-meta">
            <div className="bs-author-block">
              <div className="bs-avatar-lg">
                {author.photo_path ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img src={authorPhoto} alt={authorName} />
                ) : (
                  <span>{authorInitial}</span>
                )}
              </div>
              <div className="bs-author-text">
                <strong>{authorName}</strong>
                <span>{timeAgo(post.published_at ?? post.created_at)}</span>
              </div>
            </div>
            <div className="bs-stats">
              <span>
                <i className="far fa-clock"></i> {readingTime(post.content ?? '')} min de lecture
              </span>
              <span>
                <i className="far fa-eye"></i> {formatNumber(post.views ?? 0)}
              </span>
            </div>
          </div>
        </header>

        {heroImage && (
          <div className="bs-article-cover">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={heroImage} alt={post.title} />
          </div>
        )}

        <div className="bs-article-body">
          <aside className="bs-article-toc">
            <BlogToc />
          </aside>

          <main className="bs-article-main">
            <div
              className="bs-prose"
              dangerouslySetInnerHTML={{ __html: contentHtml }}
            />

            {tags.length > 0 && (
              <div className="bs-tags">
                {tags.map(tag => (
                  <Link
                    key={tag}
                    href={`/blog?q=${encodeURIComponent(tag)}`}
                    className="bs-tag"
                  >
                    {tag}
                  </Link>
                ))}
              </div>
            )}

            {/* Author box */}
            <div className="bs-author-box">
              <div className="bs-author-avatar">
                {author.photo_path ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img src={authorPhoto} alt={authorName} />
                ) : (
                  <span>{authorInitial}</span>
                )}
              </div>
              <div className="bs-author-box-body">
                <span className="bs-eyebrow">
                  <span className="dot" /> Écrit par
                </span>
                <h3>{authorName}</h3>
                <p>
                  {author.bio
                    ? author.bio.slice(0, 140)
                    : `Membre de la communauté AlgoCodeBF${
                        author.university ? ` · ${author.university}` : ''
                      }.`}
                </p>
              </div>
              <Link href={`/user/${author.id}`} className="bs-btn bs-btn-ghost bs-btn-sm">
                <i className="fas fa-user"></i> Voir le profil
              </Link>
              {canEdit && (
                <Link
                  href={`/blog/modifier/${post.slug ?? post.id}`}
                  className="bs-btn bs-btn-sm"
                >
                  <i className="fas fa-edit"></i> Modifier
                </Link>
              )}
            </div>

            {/* Related */}
            {related && related.length > 0 && (
              <section className="bs-related">
                <div className="bs-section-header">
                  <h2 className="bs-section-title">Articles similaires</h2>
                  <Link
                    href={`/blog?category=${encodeURIComponent(cat)}`}
                    className="bs-section-link"
                  >
                    Voir tout <i className="fas fa-arrow-right"></i>
                  </Link>
                </div>
                <div className="bs-grid">
                  {related.map(r => (
                    <article key={r.id} className="bs-card">
                      <Link
                        href={`/blog/${r.slug ?? r.id}`}
                        style={{
                          display: 'flex',
                          flexDirection: 'column',
                          textDecoration: 'none',
                          color: 'inherit',
                          height: '100%',
                        }}
                      >
                        <div className="bs-card-img">
                          <img
                            src={
                              r.featured_image
                                ? buildFileUrl(r.featured_image)
                                : '/images/im1.png'
                            }
                            alt={r.title}
                            loading="lazy"
                          />
                          <span
                            className={`bs-card-cat cat-${slugifyCat(r.category ?? 'actualites')}`}
                          >
                            {r.category ?? 'Actualités'}
                          </span>
                        </div>
                        <div className="bs-card-body">
                          <div className="bs-card-meta">
                            <span>
                              <i className="far fa-clock"></i>{' '}
                              {readingTime(r.content ?? '')} min
                            </span>
                            <span className="bs-sep" />
                            <span>{timeAgo(r.published_at ?? r.created_at)}</span>
                          </div>
                          <h3 className="bs-card-title">{r.title}</h3>
                        </div>
                      </Link>
                    </article>
                  ))}
                </div>
              </section>
            )}

            {/* Comments */}
            <BlogCommentsLive postId={post.id} profile={profile} />
          </main>

          <aside className="bs-article-rail">
            <BlogActionsRail
              postId={post.id}
              postTitle={post.title}
              postExcerpt={post.excerpt}
              initialLiked={liked}
              initialLikes={post.likes_count ?? 0}
              commentsCount={commentsCount ?? 0}
              isAuthenticated={!!profile}
            />

            {popular && popular.length > 0 && (
              <div
                className="bs-widget"
                style={{ marginTop: 24 }}
              >
                <h3 className="bs-widget-title">
                  <i className="fas fa-fire-alt"></i> Tendances
                </h3>
                <div className="bs-popular">
                  {popular.map((p, i) => (
                    <Link
                      key={p.id}
                      href={`/blog/${p.slug ?? p.id}`}
                      className="bs-popular-item"
                    >
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
          </aside>
        </div>
      </article>
    </div>
  )
}
