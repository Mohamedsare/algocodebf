'use client'

import { useEffect, useMemo, useRef, useState } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { createClient } from '@/lib/supabase/client'
import { buildAvatarUrl, buildFileUrl, readingTime, timeAgo, formatNumber } from '@/lib/utils'

interface Author {
  id: string
  prenom: string | null
  nom: string | null
  photo_path: string | null
}

export interface BlogPostRow {
  id: number
  title: string
  slug: string | null
  excerpt: string | null
  featured_image: string | null
  category: string | null
  views: number | null
  likes_count: number | null
  comments_count?: number | null
  content: string | null
  created_at: string
  published_at: string | null
  author?: Author | null
  _new?: boolean
}

interface Props {
  initialPosts: BlogPostRow[]
  category: string
  sort: string
  search: string
  currentPage: number
  totalPages: number
}

function buildBlogHref(params: {
  q?: string
  category?: string
  sort?: string
  page?: string
}): string {
  const sp = new URLSearchParams()
  if (params.q) sp.set('q', params.q)
  if (params.category) sp.set('category', params.category)
  if (params.sort && params.sort !== 'recent') sp.set('sort', params.sort)
  if (params.page && params.page !== '1') sp.set('page', params.page)
  const qs = sp.toString()
  return qs ? `/blog?${qs}` : '/blog'
}

function slugifyCat(s: string): string {
  return s
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

export function BlogListLive({
  initialPosts,
  category,
  sort,
  search,
  currentPage,
  totalPages,
}: Props) {
  const buildHref = (overrides: {
    q?: string
    category?: string
    sort?: string
    page?: string
  }) =>
    buildBlogHref({
      q: overrides.q ?? search,
      category: overrides.category ?? category,
      sort: overrides.sort ?? sort,
      page: overrides.page,
    })
  const router = useRouter()
  const [posts, setPosts] = useState<BlogPostRow[]>(initialPosts)
  const [incomingCount, setIncomingCount] = useState(0)
  const [localSearch, setLocalSearch] = useState(search)
  const pageIdsRef = useRef<Set<number>>(new Set(initialPosts.map(p => p.id)))

  // Resync on server fetch (pagination / filter changes)
  useEffect(() => {
    setPosts(initialPosts)
    pageIdsRef.current = new Set(initialPosts.map(p => p.id))
    setIncomingCount(0)
  }, [initialPosts])

  // Realtime : nouveaux articles publiés + updates compteurs
  useEffect(() => {
    const supabase = createClient()
    const channel = supabase
      .channel('blog-list')
      // Nouveau post publié
      .on(
        'postgres_changes',
        { event: 'INSERT', schema: 'public', table: 'blog_posts', filter: 'status=eq.published' },
        async payload => {
          const row = payload.new as BlogPostRow & { user_id: string; status: string }
          if (!row?.id || pageIdsRef.current.has(row.id)) return
          if (category && row.category !== category) return

          // Fetch auteur
          let author: Author | null = null
          if (row.user_id) {
            const { data } = await supabase
              .from('profiles')
              .select('id, prenom, nom, photo_path')
              .eq('id', row.user_id)
              .maybeSingle()
            author = (data as Author) ?? null
          }

          if (currentPage === 1 && (sort === 'recent' || !sort)) {
            pageIdsRef.current.add(row.id)
            setPosts(prev => [{ ...row, author, _new: true }, ...prev].slice(0, 24))
            // Pill seulement si user a scrollé
            if (window.scrollY > 200) setIncomingCount(c => c + 1)
          } else {
            setIncomingCount(c => c + 1)
          }
        }
      )
      // Updates (likes_count, comments_count, views, status)
      .on(
        'postgres_changes',
        { event: 'UPDATE', schema: 'public', table: 'blog_posts' },
        payload => {
          const row = payload.new as Partial<BlogPostRow> & { id: number; status?: string }
          if (!row?.id) return
          setPosts(prev =>
            prev.map(p =>
              p.id === row.id
                ? {
                    ...p,
                    views: row.views ?? p.views,
                    likes_count: row.likes_count ?? p.likes_count,
                    comments_count: row.comments_count ?? p.comments_count,
                  }
                : p
            )
          )
        }
      )
      .on(
        'postgres_changes',
        { event: 'DELETE', schema: 'public', table: 'blog_posts' },
        payload => {
          const row = payload.old as { id: number } | null
          if (!row?.id) return
          setPosts(prev => prev.filter(p => p.id !== row.id))
          pageIdsRef.current.delete(row.id)
        }
      )
      .subscribe()

    return () => {
      supabase.removeChannel(channel)
    }
  }, [category, sort, currentPage])

  // Client-side filter (titre/résumé) — déjà fait server-side mais ce qui arrive live est aussi filtré
  const filtered = useMemo(() => {
    const q = localSearch.trim().toLowerCase()
    if (!q) return posts
    return posts.filter(
      p =>
        (p.title ?? '').toLowerCase().includes(q) ||
        (p.excerpt ?? '').toLowerCase().includes(q)
    )
  }, [posts, localSearch])

  const handleShowNew = () => {
    setIncomingCount(0)
    window.scrollTo({ top: 0, behavior: 'smooth' })
    router.refresh()
  }

  // Sync search state
  useEffect(() => setLocalSearch(search), [search])

  return (
    <>
      {incomingCount > 0 && (
        <button type="button" className="bs-new-pill" onClick={handleShowNew}>
          <i className="fas fa-arrow-up"></i>
          {incomingCount} nouvel{incomingCount > 1 ? 's' : ''} article
          {incomingCount > 1 ? 's' : ''}
        </button>
      )}

      {filtered.length === 0 ? (
        <div className="bs-empty">
          <div className="bs-empty-icon">
            <i className="fas fa-search"></i>
          </div>
          <h3>Aucun article trouvé</h3>
          <p>Aucun contenu ne correspond à vos critères de recherche.</p>
          <Link href="/blog" className="bs-btn bs-btn-primary">
            <i className="fas fa-redo"></i> Réinitialiser
          </Link>
        </div>
      ) : (
        <>
          <div className="bs-grid">
            {filtered.map((post, i) => {
              const author = post.author
              const authorName = author
                ? `${author.prenom ?? ''} ${author.nom ?? ''}`.trim() || 'Auteur'
                : 'Auteur'
              const authorInitial = (authorName.charAt(0) || 'A').toUpperCase()
              const cat = post.category ?? 'Actualités'
              return (
                <article
                  key={post.id}
                  className={`bs-card${post._new ? ' is-new' : ''}`}
                  style={{ animationDelay: `${Math.min(i * 40, 400)}ms` }}
                >
                  <Link
                    href={`/blog/${post.slug ?? post.id}`}
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
                          post.featured_image
                            ? buildFileUrl(post.featured_image)
                            : '/images/im1.png'
                        }
                        alt={post.title}
                        loading="lazy"
                      />
                      <span className={`bs-card-cat cat-${slugifyCat(cat)}`}>{cat}</span>
                    </div>
                    <div className="bs-card-body">
                      <div className="bs-card-meta">
                        <span>
                          <i className="far fa-clock"></i> {readingTime(post.content ?? '')} min
                        </span>
                        <span className="bs-sep" />
                        <span>{timeAgo(post.published_at ?? post.created_at)}</span>
                      </div>
                      <h3 className="bs-card-title">{post.title}</h3>
                      <p className="bs-card-excerpt">{(post.excerpt ?? '').slice(0, 140)}</p>
                      <div className="bs-card-footer">
                        <div className="bs-card-author">
                          <div className="bs-avatar">
                            {author?.photo_path ? (
                              // eslint-disable-next-line @next/next/no-img-element
                              <img src={buildAvatarUrl(author.photo_path)} alt={authorName} />
                            ) : (
                              <span>{authorInitial}</span>
                            )}
                          </div>
                          <span>{authorName}</span>
                        </div>
                        <div className="bs-card-stats">
                          <span>
                            <i className="far fa-eye"></i> {formatNumber(post.views ?? 0)}
                          </span>
                          <span>
                            <i className="far fa-heart"></i> {post.likes_count ?? 0}
                          </span>
                        </div>
                      </div>
                    </div>
                  </Link>
                </article>
              )
            })}
          </div>

          {totalPages > 1 && (
            <nav className="bs-pagination" aria-label="Pagination">
              {currentPage > 1 && (
                <Link href={buildHref({ page: String(currentPage - 1) })} className="bs-page">
                  <i className="fas fa-chevron-left"></i> Précédent
                </Link>
              )}
              {Array.from(
                { length: Math.min(5, totalPages) },
                (_, i) => Math.max(1, currentPage - 2) + i
              )
                .filter(i => i >= 1 && i <= totalPages)
                .map(i => (
                  <Link
                    key={i}
                    href={buildHref({ page: String(i) })}
                    className={`bs-page${i === currentPage ? ' active' : ''}`}
                  >
                    {i}
                  </Link>
                ))}
              {currentPage < totalPages && (
                <Link href={buildHref({ page: String(currentPage + 1) })} className="bs-page">
                  Suivant <i className="fas fa-chevron-right"></i>
                </Link>
              )}
            </nav>
          )}
        </>
      )}
    </>
  )
}
