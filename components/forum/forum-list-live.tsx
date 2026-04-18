'use client'

import { useEffect, useMemo, useRef, useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter, useSearchParams } from 'next/navigation'
import { createClient } from '@/lib/supabase/client'
import { buildAvatarUrl, timeAgo, truncate } from '@/lib/utils'
import { SearchSuggestPanel } from '@/components/search/search-suggest-panel'
import { useSearchSuggest } from '@/components/search/use-search-suggest'
import type { ForumPostListItem } from '@/lib/queries/forum'
import type { ForumCategory } from '@/types'

interface Props {
  initialPosts: ForumPostListItem[]
  total: number
  page: number
  totalPages: number
  categories: ForumCategory[]
  categoryCounts: Record<string, number>
  currentCategory: string | null
  initialQuery: string
  isAuthenticated: boolean
}

const SORTS = [
  { id: 'recent', label: 'Récents', icon: 'fa-clock' },
  { id: 'hot', label: 'Tendances', icon: 'fa-fire' },
  { id: 'top', label: 'Populaires', icon: 'fa-star' },
] as const

type SortKey = (typeof SORTS)[number]['id']

export function ForumListLive({
  initialPosts,
  total,
  page,
  totalPages,
  categories,
  categoryCounts,
  currentCategory,
  initialQuery,
  isAuthenticated,
}: Props) {
  const router = useRouter()
  const searchParams = useSearchParams()
  const [, startTransition] = useTransition()

  const [posts, setPosts] = useState<ForumPostListItem[]>(initialPosts)
  const [newIds, setNewIds] = useState<Set<number>>(new Set())
  const [pendingNew, setPendingNew] = useState<ForumPostListItem[]>([])
  const [search, setSearch] = useState(initialQuery)
  const [sort, setSort] = useState<SortKey>('recent')

  const listTopRef = useRef<HTMLDivElement>(null)
  const searchDebounceRef = useRef<ReturnType<typeof setTimeout> | null>(null)

  const { data: forumSuggest, loading: forumSuggestLoading } = useSearchSuggest(search, {
    scopes: 'posts',
    limit: 6,
    debounceMs: 200,
  })

  // Sync avec les nouvelles données SSR (après navigation)
  useEffect(() => {
    setPosts(initialPosts)
    setPendingNew([])
    setNewIds(new Set())
  }, [initialPosts])

  // Debounced search -> URL
  useEffect(() => {
    if (searchDebounceRef.current) clearTimeout(searchDebounceRef.current)
    if (search === initialQuery) return
    searchDebounceRef.current = setTimeout(() => {
      const params = new URLSearchParams(Array.from(searchParams.entries()))
      if (search.trim()) params.set('q', search.trim())
      else params.delete('q')
      params.delete('page')
      startTransition(() => {
        router.replace(`/forum?${params.toString()}`, { scroll: false })
      })
    }, 350)
    return () => {
      if (searchDebounceRef.current) clearTimeout(searchDebounceRef.current)
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search])

  // Realtime : écoute des INSERT sur posts
  useEffect(() => {
    const supabase = createClient()
    const channel = supabase
      .channel('forum:posts:list', { config: { broadcast: { self: false } } })
      .on(
        'postgres_changes',
        { event: 'INSERT', schema: 'public', table: 'posts' },
        async (payload) => {
          const row = payload.new as {
            id: number
            user_id: string | null
            title: string
            body: string
            category: string | null
            views: number
            is_pinned: boolean
            status: string
            created_at: string
            updated_at: string
          }
          if (row.status !== 'active') return
          // Si on filtre par catégorie et que le post ne correspond pas → ignore
          if (currentCategory && row.category !== currentCategory) return

          // Récupère le profil de l'auteur
          let author: ForumPostListItem['author'] = null
          if (row.user_id) {
            const { data } = await supabase
              .from('profiles')
              .select('id, prenom, nom, photo_path, university')
              .eq('id', row.user_id)
              .maybeSingle()
            if (data) author = data as ForumPostListItem['author']
          }

          const item: ForumPostListItem = {
            id: row.id,
            user_id: row.user_id,
            title: row.title,
            body: row.body,
            category: row.category,
            views: row.views ?? 0,
            is_pinned: row.is_pinned ?? false,
            status: 'active',
            created_at: row.created_at,
            updated_at: row.updated_at,
            author,
            comments_count: 0,
            likes_count: 0,
          }

          // Si l'utilisateur est tout en haut, on l'insère directement
          const scrollY = window.scrollY
          if (scrollY < 240 && !search) {
            setPosts((prev) => [item, ...prev.filter((p) => p.id !== item.id)])
            setNewIds((prev) => {
              const next = new Set(prev)
              next.add(item.id)
              return next
            })
            setTimeout(() => {
              setNewIds((prev) => {
                const next = new Set(prev)
                next.delete(item.id)
                return next
              })
            }, 6000)
          } else {
            setPendingNew((prev) => {
              if (prev.some((p) => p.id === item.id)) return prev
              return [item, ...prev]
            })
          }
        },
      )
      .on(
        'postgres_changes',
        { event: 'UPDATE', schema: 'public', table: 'posts' },
        (payload) => {
          const row = payload.new as { id: number; title: string; body: string; views: number; is_pinned: boolean }
          setPosts((prev) =>
            prev.map((p) =>
              p.id === row.id
                ? { ...p, title: row.title, body: row.body, views: row.views, is_pinned: row.is_pinned }
                : p,
            ),
          )
        },
      )
      .on(
        'postgres_changes',
        { event: 'INSERT', schema: 'public', table: 'likes', filter: 'likeable_type=eq.post' },
        (payload) => {
          const row = payload.new as { likeable_id: number }
          setPosts((prev) =>
            prev.map((p) => (p.id === row.likeable_id ? { ...p, likes_count: (p.likes_count ?? 0) + 1 } : p)),
          )
        },
      )
      .on(
        'postgres_changes',
        { event: 'DELETE', schema: 'public', table: 'likes', filter: 'likeable_type=eq.post' },
        (payload) => {
          const row = payload.old as { likeable_id: number }
          setPosts((prev) =>
            prev.map((p) =>
              p.id === row.likeable_id ? { ...p, likes_count: Math.max(0, (p.likes_count ?? 0) - 1) } : p,
            ),
          )
        },
      )
      .on(
        'postgres_changes',
        { event: 'INSERT', schema: 'public', table: 'comments', filter: 'commentable_type=eq.post' },
        (payload) => {
          const row = payload.new as { commentable_id: number }
          setPosts((prev) =>
            prev.map((p) =>
              p.id === row.commentable_id ? { ...p, comments_count: (p.comments_count ?? 0) + 1 } : p,
            ),
          )
        },
      )
      .subscribe()

    return () => {
      supabase.removeChannel(channel)
    }
  }, [currentCategory, search])

  const revealNewPosts = () => {
    setPosts((prev) => {
      const ids = new Set(prev.map((p) => p.id))
      const toAdd = pendingNew.filter((p) => !ids.has(p.id))
      return [...toAdd, ...prev]
    })
    const addedIds = pendingNew.map((p) => p.id)
    setNewIds((prev) => {
      const next = new Set(prev)
      addedIds.forEach((id) => next.add(id))
      return next
    })
    setPendingNew([])
    setTimeout(() => {
      setNewIds((prev) => {
        const next = new Set(prev)
        addedIds.forEach((id) => next.delete(id))
        return next
      })
    }, 6000)
    listTopRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  // Tri client-side (sur l'ensemble visible)
  const sortedPosts = useMemo(() => {
    const arr = [...posts]
    if (sort === 'hot') {
      arr.sort(
        (a, b) =>
          b.views * 2 +
          (b.comments_count ?? 0) * 3 +
          (b.likes_count ?? 0) * 5 -
          (a.views * 2 + (a.comments_count ?? 0) * 3 + (a.likes_count ?? 0) * 5),
      )
    } else if (sort === 'top') {
      arr.sort((a, b) => (b.likes_count ?? 0) - (a.likes_count ?? 0) || b.views - a.views)
    } else {
      arr.sort((a, b) => {
        if (a.is_pinned && !b.is_pinned) return -1
        if (!a.is_pinned && b.is_pinned) return 1
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      })
    }
    return arr
  }, [posts, sort])

  const buildUrl = (override: Record<string, string | null>): string => {
    const p = new URLSearchParams(Array.from(searchParams.entries()))
    for (const [k, v] of Object.entries(override)) {
      if (v === null || v === '') p.delete(k)
      else p.set(k, v)
    }
    const q = p.toString()
    return q ? `/forum?${q}` : '/forum'
  }

  return (
    <>
      {/* Toolbar : recherche + tri */}
      <div className="forum-toolbar-saas">
        <div className="search-wrap">
          <i className="fas fa-search search-icon"></i>
          <input
            type="text"
            placeholder="Rechercher une discussion, un sujet, un mot-clé…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label="Rechercher dans le forum"
          />
          {search && (
            <button
              type="button"
              className="search-clear"
              onClick={() => setSearch('')}
              aria-label="Effacer la recherche"
            >
              <i className="fas fa-times"></i>
            </button>
          )}
          <SearchSuggestPanel
            data={forumSuggest}
            loading={forumSuggestLoading}
            variant="inline"
            showAllHref={
              search.trim().length >= 2
                ? `/search?q=${encodeURIComponent(search.trim())}`
                : undefined
            }
            allLabel="Recherche globale"
          />
        </div>

        <div className="toolbar-actions">
          <div className="segment-group" role="tablist" aria-label="Tri des discussions">
            {SORTS.map((s) => (
              <button
                key={s.id}
                type="button"
                role="tab"
                aria-selected={sort === s.id}
                className={`segment-btn${sort === s.id ? ' is-active' : ''}`}
                onClick={() => setSort(s.id)}
              >
                <i className={`fas ${s.icon}`}></i>
                <span>{s.label}</span>
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Chips catégories */}
      <div className="category-chips" role="tablist" aria-label="Catégories">
        <Link
          href={buildUrl({ category: null, page: null })}
          className={`cat-chip${!currentCategory ? ' is-active' : ''}`}
          role="tab"
          aria-selected={!currentCategory}
        >
          <i className="fas fa-layer-group"></i>
          <span>Toutes</span>
          <span className="cat-count">{total}</span>
        </Link>
        {categories.map((cat) => {
          const active = currentCategory === cat.slug
          const count = categoryCounts[cat.slug] ?? 0
          return (
            <Link
              key={cat.id}
              href={buildUrl({ category: cat.slug, page: null })}
              className={`cat-chip${active ? ' is-active' : ''}`}
              role="tab"
              aria-selected={active}
            >
              <span>{cat.name}</span>
              {count > 0 && <span className="cat-count">{count}</span>}
            </Link>
          )
        })}
      </div>

      <div ref={listTopRef} style={{ scrollMarginTop: '80px' }} />

      {/* Pill "nouveaux posts" */}
      {pendingNew.length > 0 && (
        <div className="new-posts-pill">
          <button type="button" onClick={revealNewPosts}>
            <span className="pill-dot"></span>
            <i className="fas fa-arrow-up" style={{ fontSize: 11 }}></i>
            {pendingNew.length} nouvelle{pendingNew.length > 1 ? 's' : ''} discussion
            {pendingNew.length > 1 ? 's' : ''}
          </button>
        </div>
      )}

      {/* Liste des posts */}
      {sortedPosts.length === 0 ? (
        <div className="empty-state-saas">
          <div className="empty-icon">
            <i className="fas fa-comments"></i>
          </div>
          <h3>Aucune discussion trouvée</h3>
          <p>
            {search
              ? `Pas de résultat pour "${search}". Essayez d'autres mots-clés.`
              : 'Soyez le premier à démarrer une discussion dans cette catégorie.'}
          </p>
          {isAuthenticated && !search && (
            <Link href="/forum/creer" className="btn-saas primary">
              <i className="fas fa-plus"></i>
              Nouvelle discussion
            </Link>
          )}
        </div>
      ) : (
        <>
          <div className="forum-posts-stream">
            {sortedPosts.map((post) => {
              const isNew = newIds.has(post.id)
              const body = truncate((post.body ?? '').replace(/<[^>]*>/g, ''), 180)
              return (
                <Link
                  key={post.id}
                  href={`/forum/${post.id}`}
                  className={`forum-post-saas${isNew ? ' is-new' : ''}`}
                >
                  <div className="post-avatar-wrap">
                    {post.author?.photo_path ? (
                      <img
                        src={buildAvatarUrl(post.author.photo_path)}
                        alt={`${post.author.prenom} ${post.author.nom}`}
                      />
                    ) : (
                      <span>{(post.author?.prenom ?? 'U').charAt(0).toUpperCase()}</span>
                    )}
                  </div>

                  <div className="post-body">
                    <div className="post-top-row">
                      {post.is_pinned && (
                        <span className="post-pin">
                          <i className="fas fa-thumbtack" style={{ fontSize: 9 }}></i>
                          Épinglé
                        </span>
                      )}
                      {post.category && <span className="post-cat">{post.category}</span>}
                      {post.author && (
                        <span className="post-author">
                          {post.author.prenom} {post.author.nom}
                        </span>
                      )}
                      <span className="post-dot">·</span>
                      <span className="post-time">{timeAgo(post.created_at)}</span>
                    </div>

                    <h3 className="post-title-saas">{post.title}</h3>
                    {body && <p className="post-excerpt">{body}</p>}

                    <div className="post-meta-row">
                      <span className="meta-item replies">
                        <i className="fas fa-comment"></i>
                        {post.comments_count ?? 0}
                      </span>
                      <span className={`meta-item likes${(post.likes_count ?? 0) > 0 ? ' is-active' : ''}`}>
                        <i className={`fas fa-heart`}></i>
                        {post.likes_count ?? 0}
                      </span>
                      <span className="meta-item">
                        <i className="fas fa-eye"></i>
                        {post.views ?? 0}
                      </span>
                    </div>
                  </div>
                </Link>
              )
            })}
          </div>

          {totalPages > 1 && (
            <div className="pagination-saas">
              {page > 1 && (
                <Link href={buildUrl({ page: String(page - 1) })} className="page-btn">
                  <i className="fas fa-chevron-left" style={{ fontSize: 11 }}></i>
                  <span className="sr-only">Précédent</span>
                </Link>
              )}
              {Array.from({ length: totalPages }, (_, i) => i + 1).map((i) => {
                const isCurrent = i === page
                const distance = Math.abs(i - page)
                if (totalPages > 7 && distance > 2 && i !== 1 && i !== totalPages) {
                  if (i === 2 || i === totalPages - 1) {
                    return <span key={`dot-${i}`} className="page-btn" style={{ border: 0, background: 'transparent' }}>…</span>
                  }
                  return null
                }
                return (
                  <Link
                    key={i}
                    href={buildUrl({ page: i === 1 ? null : String(i) })}
                    className={`page-btn${isCurrent ? ' is-active' : ''}`}
                    aria-current={isCurrent ? 'page' : undefined}
                  >
                    {i}
                  </Link>
                )
              })}
              {page < totalPages && (
                <Link href={buildUrl({ page: String(page + 1) })} className="page-btn">
                  <i className="fas fa-chevron-right" style={{ fontSize: 11 }}></i>
                  <span className="sr-only">Suivant</span>
                </Link>
              )}
            </div>
          )}
        </>
      )}
    </>
  )
}
