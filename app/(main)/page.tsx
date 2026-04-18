import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient, getProfile } from '@/lib/supabase/server'
import { buildAvatarUrl, buildBlogImageUrl, truncate } from '@/lib/utils'
import { HeroCarousel } from '@/components/home/hero-carousel'
import { HomeFlagBand } from '@/components/home/home-flag-band'
import { HomeValuesSection } from '@/components/home/home-values-section'
import { FORMATIONS_PATH } from '@/lib/routes'

export const metadata: Metadata = {
  title: 'Accueil - AlgoCodeBF',
  description:
    'Bienvenue sur AlgoCodeBF, la plateforme tech du Burkina Faso. Apprenez, collaborez et innovez avec la communauté.',
}

export const revalidate = 120

interface Stats {
  total_users: number
  total_posts: number
  total_tutorials: number
  total_projects: number
}

type JoinedAuthor = { prenom: string | null; nom: string | null; photo_path: string | null }

interface RecentPost {
  id: number
  title: string
  body: string
  category: string
  views: number
  comments_count: number
  likes_count: number
  created_at: string
  profiles: JoinedAuthor | null
}

interface PopularTutorial {
  id: number
  title: string
  description: string
  type: string
  views: number
  likes_count: number
  profiles: JoinedAuthor | null
}

interface RecentBlog {
  id: number
  title: string
  slug: string
  excerpt: string | null
  featured_image: string | null
  category: string
  published_at: string
  profiles: JoinedAuthor | null
}

function stripHtml(s: string) {
  return s.replace(/<[^>]*>/g, '')
}

function formatDateFR(d: string, withTime = false) {
  const date = new Date(d)
  const dd = String(date.getDate()).padStart(2, '0')
  const mm = String(date.getMonth() + 1).padStart(2, '0')
  const yyyy = date.getFullYear()
  if (!withTime) return `${dd}/${mm}/${yyyy}`
  const hh = String(date.getHours()).padStart(2, '0')
  const mi = String(date.getMinutes()).padStart(2, '0')
  return `${dd}/${mm}/${yyyy} ${hh}:${mi}`
}

function formatDateShortFR(d: string) {
  const date = new Date(d)
  const dd = String(date.getDate()).padStart(2, '0')
  const months = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.']
  return `${dd} ${months[date.getMonth()]} ${date.getFullYear()}`
}

async function getHomeData() {
  const supabase = await createClient()

  const [
    { count: usersCount },
    { count: postsCount },
    { count: tutorialsCount },
    { count: projectsCount },
    { data: recentPosts },
    { data: popularTutorials },
    { data: recentBlogs },
  ] = await Promise.all([
    supabase.from('profiles').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('posts').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('tutorials').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('projects').select('*', { count: 'exact', head: true }).eq('visibility', 'public'),
    supabase
      .from('posts')
      .select(
        'id, title, body, category, views, comments_count, likes_count, created_at, profiles!inner(prenom, nom, photo_path)'
      )
      .eq('status', 'active')
      .order('created_at', { ascending: false })
      .limit(5),
    supabase
      .from('tutorials')
      .select('id, title, description, type, views, likes_count, profiles!inner(prenom, nom, photo_path)')
      .eq('status', 'active')
      .order('views', { ascending: false })
      .limit(5),
    supabase
      .from('blog_posts')
      .select(
        'id, title, slug, excerpt, featured_image, category, published_at, profiles!inner(prenom, nom, photo_path)'
      )
      .eq('status', 'published')
      .order('published_at', { ascending: false })
      .limit(3),
  ])

  const stats: Stats = {
    total_users: usersCount ?? 0,
    total_posts: postsCount ?? 0,
    total_tutorials: tutorialsCount ?? 0,
    total_projects: projectsCount ?? 0,
  }

  return {
    stats,
    recentPosts: (recentPosts ?? []) as unknown as RecentPost[],
    popularTutorials: (popularTutorials ?? []) as unknown as PopularTutorial[],
    recentBlogs: (recentBlogs ?? []) as unknown as RecentBlog[],
  }
}

/** Logos / liens : à adapter quand les partenariats sont officialisés. */
const HOME_PARTNERS: { name: string; href?: string }[] = [
  { name: 'Université partenaire' },
  { name: 'Incubateur & innovation' },
  { name: 'Institution publique' },
  { name: 'Média & éducation' },
  { name: 'Entreprise tech' },
  { name: 'Association professionnelle' },
]

function tutorialIcon(type: string) {
  switch (type) {
    case 'video':
      return 'fa-video'
    case 'pdf':
      return 'fa-file-pdf'
    case 'code':
      return 'fa-code'
    case 'article':
      return 'fa-newspaper'
    default:
      return 'fa-book'
  }
}

function AuthorAvatar({ author, size = 'xs' }: { author: JoinedAuthor | null; size?: 'xs' | 'sm' }) {
  const p = author?.prenom ?? 'U'
  const sz = size === 'sm' ? 'hm-avatar--sm' : 'hm-avatar--xs'
  const ph = size === 'sm' ? 'hm-avatar-ph--sm' : 'hm-avatar-ph--xs'
  if (author?.photo_path) {
    return (
      <img
        src={buildAvatarUrl(author.photo_path)}
        alt={`${author.prenom ?? ''} ${author.nom ?? ''}`}
        className={`hm-avatar ${sz}`}
      />
    )
  }
  return (
    <div className={`hm-avatar-ph ${ph}`} aria-hidden>
      {p.charAt(0).toUpperCase()}
    </div>
  )
}

export default async function HomePage() {
  const [profile, data] = await Promise.all([getProfile(), getHomeData()])
  const { stats, recentPosts, popularTutorials, recentBlogs } = data
  const isLogged = !!profile

  return (
    <div className="home-saas">
      <HeroCarousel
        isLogged={isLogged}
        stats={{
          users: stats.total_users,
          posts: stats.total_posts,
          tutorials: stats.total_tutorials,
          projects: stats.total_projects,
        }}
      />

      <section className="hm-stats-strip" aria-label="Statistiques de la plateforme">
        <div className="container">
          <div className="hm-stats-grid">
            <div className="hm-stat-card">
              <div className="hm-stat-ico" aria-hidden>
                <i className="fas fa-users" />
              </div>
              <div>
                <div className="hm-stat-num">{stats.total_users}</div>
                <div className="hm-stat-lbl">Membres</div>
              </div>
            </div>
            <div className="hm-stat-card">
              <div className="hm-stat-ico" aria-hidden>
                <i className="fas fa-comments" />
              </div>
              <div>
                <div className="hm-stat-num">{stats.total_posts}</div>
                <div className="hm-stat-lbl">Discussions</div>
              </div>
            </div>
            <div className="hm-stat-card">
              <div className="hm-stat-ico" aria-hidden>
                <i className="fas fa-book" />
              </div>
              <div>
                <div className="hm-stat-num">{stats.total_tutorials}</div>
                <div className="hm-stat-lbl">Formations</div>
              </div>
            </div>
            <div className="hm-stat-card">
              <div className="hm-stat-ico" aria-hidden>
                <i className="fas fa-project-diagram" />
              </div>
              <div>
                <div className="hm-stat-num">{stats.total_projects}</div>
                <div className="hm-stat-lbl">Projets</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="hm-block hm-block--muted">
        <div className="container">
          <div className="hm-head">
            <h2>
              <i className="fas fa-book" aria-hidden />
              Formations populaires
            </h2>
            <Link href={FORMATIONS_PATH} className="hm-btn hm-btn-outline">
              Voir tout
            </Link>
          </div>

          <div className="hm-grid hm-grid-tuto">
            {popularTutorials.length === 0 ? (
              <div className="hm-empty">
                <i className="fas fa-book" aria-hidden />
                <p>Aucune formation pour le moment.</p>
              </div>
            ) : (
              popularTutorials.map(tuto => (
                <article key={tuto.id} className="hm-tuto">
                  <div className="hm-tuto-type" aria-hidden>
                    <i className={`fas ${tutorialIcon(tuto.type)}`} />
                  </div>
                  <h3>
                    <Link href={`${FORMATIONS_PATH}/${tuto.id}`}>{tuto.title}</Link>
                  </h3>
                  <p>{truncate(stripHtml(tuto.description ?? ''), 100)}…</p>
                  <div className="hm-tuto-foot">
                    <div className="hm-tuto-author">
                      <AuthorAvatar author={tuto.profiles} size="sm" />
                      <span>
                        {tuto.profiles?.prenom ?? ''} {tuto.profiles?.nom ?? ''}
                      </span>
                    </div>
                    <div className="hm-tuto-metrics">
                      <span>
                        <i className="fas fa-eye" aria-hidden />
                        {tuto.views}
                      </span>
                      <span>
                        <i className="fas fa-heart" aria-hidden />
                        {tuto.likes_count}
                      </span>
                    </div>
                  </div>
                  <Link href={`${FORMATIONS_PATH}/${tuto.id}`} className="hm-btn hm-btn-cta hm-btn-lg">
                    <i className="fas fa-arrow-right" />
                    Voir la formation
                  </Link>
                </article>
              ))
            )}
          </div>
        </div>
      </section>

      <section className="hm-block">
        <div className="container">
          <div className="hm-head">
            <h2>
              <i className="fas fa-comments" aria-hidden />
              Discussions récentes
            </h2>
            <Link href="/forum" className="hm-btn hm-btn-outline">
              Voir tout
            </Link>
          </div>

          <div className="hm-grid hm-grid-posts">
            {recentPosts.length === 0 ? (
              <div className="hm-empty">
                <i className="fas fa-comments" aria-hidden />
                <p>Aucune discussion pour le moment. Soyez le premier à en créer une&nbsp;!</p>
              </div>
            ) : (
              recentPosts.map(post => (
                <article key={post.id} className="hm-post">
                  <div className="hm-post-top">
                    <div className="hm-post-author">
                      <AuthorAvatar author={post.profiles} />
                      <div>
                        <strong>
                          {post.profiles?.prenom ?? ''} {post.profiles?.nom ?? ''}
                        </strong>
                        <span className="hm-post-date">{formatDateFR(post.created_at, true)}</span>
                      </div>
                    </div>
                    <span className="hm-tag hm-tag--accent">{post.category}</span>
                  </div>
                  <h3>
                    <Link href={`/forum/${post.id}`}>{post.title}</Link>
                  </h3>
                  <p>{truncate(stripHtml(post.body ?? ''), 150)}…</p>
                  <div className="hm-post-foot">
                    <div className="hm-post-metrics">
                      <span>
                        <i className="fas fa-eye" aria-hidden />
                        {post.views}
                      </span>
                      <span>
                        <i className="fas fa-comments" aria-hidden />
                        {post.comments_count}
                      </span>
                      <span>
                        <i className="fas fa-heart" aria-hidden />
                        {post.likes_count}
                      </span>
                    </div>
                    <Link href={`/forum/${post.id}`} className="hm-icon-btn" title="Répondre">
                      <i className="fas fa-reply" />
                    </Link>
                  </div>
                </article>
              ))
            )}
          </div>
        </div>
      </section>

      <HomeValuesSection />

      {recentBlogs.length > 0 && (
        <section className="hm-block">
          <div className="container">
            <div className="hm-head">
              <h2>
                <i className="fas fa-newspaper" aria-hidden />
                Actualités &amp; blog
              </h2>
              <Link href="/blog" className="hm-btn hm-btn-outline">
                Voir tout
              </Link>
            </div>

            <div className="hm-grid hm-grid-blog">
              {recentBlogs.map(blog => (
                <article key={blog.id} className="hm-blog">
                  {blog.featured_image && (
                    <div className="hm-blog-img">
                      <img src={buildBlogImageUrl(blog.featured_image)} alt={blog.title} />
                    </div>
                  )}
                  <div className="hm-blog-body">
                    <span className="hm-tag hm-tag--green">{blog.category}</span>
                    <h3>
                      <Link href={`/blog/${blog.slug}`}>{blog.title}</Link>
                    </h3>
                    <p>{blog.excerpt ?? ''}</p>
                    <div className="hm-blog-foot">
                      <span>
                        {blog.profiles?.prenom ?? ''} {blog.profiles?.nom ?? ''}
                      </span>
                      <span>{formatDateShortFR(blog.published_at)}</span>
                    </div>
                    <div className="hm-blog-actions">
                      <Link href={`/blog/${blog.slug}`} className="hm-btn hm-btn-text">
                        Lire la suite
                        <i className="fas fa-arrow-right" />
                      </Link>
                    </div>
                  </div>
                </article>
              ))}
            </div>
          </div>
        </section>
      )}

      <section className="hm-cta">
        <div className="container">
          <div className="hm-cta-inner">
            <h2>Prêt à rejoindre la communauté tech du Burkina Faso&nbsp;?</h2>
            <p>
              Échangez avec des développeurs, designers et professionnels de l&apos;IT partout au pays.
            </p>
            {!isLogged ? (
              <Link href="/register" className="hm-btn hm-btn-primary hm-btn-lg">
                Rejoindre la communauté
              </Link>
            ) : (
              <Link href="/project/creer" className="hm-btn hm-btn-primary hm-btn-lg">
                Rejoignez-nous
              </Link>
            )}
          </div>
        </div>
      </section>

      <section className="hm-partners" aria-labelledby="hm-partners-title">
        <div className="container">
          <div className="hm-partners-head">
            <h2 id="hm-partners-title">
              <i className="fas fa-handshake" aria-hidden />
              Nos partenaires
            </h2>
            <p className="hm-partners-lead">
              Des acteurs qui soutiennent la communauté tech et les talents du Burkina Faso.
            </p>
          </div>
          <ul className="hm-partners-grid">
            {HOME_PARTNERS.map(p => (
              <li key={p.name}>
                {p.href ? (
                  <a href={p.href} className="hm-partner-card" target="_blank" rel="noopener noreferrer">
                    <span className="hm-partner-mark" aria-hidden>
                      <i className="fas fa-building" />
                    </span>
                    <span className="hm-partner-name">{p.name}</span>
                  </a>
                ) : (
                  <div className="hm-partner-card">
                    <span className="hm-partner-mark" aria-hidden>
                      <i className="fas fa-building" />
                    </span>
                    <span className="hm-partner-name">{p.name}</span>
                  </div>
                )}
              </li>
            ))}
          </ul>
        </div>
      </section>

      <HomeFlagBand />
    </div>
  )
}
