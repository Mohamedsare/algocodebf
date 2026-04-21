'use client'

import { useEffect, useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { createClient } from '@/lib/supabase/client'
import { toggleLikeAction } from '@/app/actions/forum'

interface Props {
  postId: number
  postTitle: string
  postExcerpt?: string | null
  initialLiked: boolean
  initialLikes: number
  commentsCount: number
  isAuthenticated: boolean
}

export function BlogActionsRail({
  postId,
  postTitle,
  postExcerpt,
  initialLiked,
  initialLikes,
  commentsCount,
  isAuthenticated,
}: Props) {
  const router = useRouter()
  const [liked, setLiked] = useState(initialLiked)
  const [count, setCount] = useState(initialLikes)
  const [bookmarked, setBookmarked] = useState(false)
  const [shareStatus, setShareStatus] = useState<'idle' | 'copied'>('idle')
  const [pending, startTransition] = useTransition()

  // Persist bookmark in localStorage (client-only feature)
  useEffect(() => {
    try {
      const raw = localStorage.getItem('acb:bookmarks:blog')
      const ids: number[] = raw ? JSON.parse(raw) : []
      setBookmarked(ids.includes(postId))
    } catch {
      /* ignore */
    }
  }, [postId])

  // Realtime sync du compteur de likes
  useEffect(() => {
    const supabase = createClient()
    const channel = supabase
      .channel(`blog-post-${postId}-likes`)
      .on(
        'postgres_changes',
        {
          event: 'INSERT',
          schema: 'public',
          table: 'likes',
          filter: `likeable_id=eq.${postId}`,
        },
        payload => {
          const row = payload.new as { likeable_type: string } | null
          if (row?.likeable_type === 'blog') setCount(c => c + 1)
        }
      )
      .on(
        'postgres_changes',
        {
          event: 'DELETE',
          schema: 'public',
          table: 'likes',
          filter: `likeable_id=eq.${postId}`,
        },
        payload => {
          const row = payload.old as { likeable_type: string } | null
          if (row?.likeable_type === 'blog') setCount(c => Math.max(0, c - 1))
        }
      )
      .subscribe()
    return () => {
      supabase.removeChannel(channel)
    }
  }, [postId])

  const handleLike = () => {
    if (!isAuthenticated) {
      router.push('/login')
      return
    }
    if (pending) return
    startTransition(async () => {
      const prevLiked = liked
      const prevCount = count
      setLiked(!prevLiked)
      setCount(prevLiked ? prevCount - 1 : prevCount + 1)
      const res = await toggleLikeAction('blog', postId)
      if (res.ok && res.data) {
        setLiked(res.data.liked)
        setCount(res.data.count)
        router.refresh()
      } else {
        setLiked(prevLiked)
        setCount(prevCount)
      }
    })
  }

  const handleShare = async () => {
    if (typeof navigator === 'undefined') return
    const url = window.location.href
    const nav = navigator as Navigator & {
      share?: (data: { title?: string; text?: string; url?: string }) => Promise<void>
    }
    try {
      if (typeof nav.share === 'function') {
        await nav.share({
          title: postTitle,
          text: (postExcerpt ?? '').slice(0, 120),
          url,
        })
        return
      }
      if (nav.clipboard) {
        await nav.clipboard.writeText(url)
        setShareStatus('copied')
        setTimeout(() => setShareStatus('idle'), 1800)
      }
    } catch {
      /* user cancel */
    }
  }

  const handleBookmark = () => {
    try {
      const raw = localStorage.getItem('acb:bookmarks:blog')
      const ids: number[] = raw ? JSON.parse(raw) : []
      const next = bookmarked ? ids.filter(i => i !== postId) : [...ids, postId]
      localStorage.setItem('acb:bookmarks:blog', JSON.stringify(next))
      setBookmarked(!bookmarked)
    } catch {
      /* ignore */
    }
  }

  const scrollToComments = () => {
    const el = document.getElementById('comments')
    if (el) {
      const y = el.getBoundingClientRect().top + window.scrollY - 80
      window.scrollTo({ top: y, behavior: 'smooth' })
    }
  }

  return (
    <>
      {/* Desktop rail */}
      <div className="bs-action-rail" aria-label="Actions">
        <button
          type="button"
          onClick={handleLike}
          disabled={pending}
          aria-label={liked ? "Retirer le j'aime" : "J'aime"}
          className={liked ? 'liked' : ''}
          title="J'aime"
        >
          <i className={`${liked ? 'fas' : 'far'} fa-heart`}></i>
          {count > 0 && <span className="bs-count">{count}</span>}
        </button>

        <button
          type="button"
          onClick={scrollToComments}
          aria-label="Voir les commentaires"
          title="Commentaires"
        >
          <i className="far fa-comment"></i>
          {commentsCount > 0 && <span className="bs-count">{commentsCount}</span>}
        </button>

        <button
          type="button"
          onClick={handleBookmark}
          aria-label={bookmarked ? 'Retirer des favoris' : 'Sauvegarder'}
          title="Sauvegarder"
          className={bookmarked ? 'liked' : ''}
        >
          <i className={`${bookmarked ? 'fas' : 'far'} fa-bookmark`}></i>
        </button>

        <button
          type="button"
          onClick={handleShare}
          aria-label="Partager"
          title={shareStatus === 'copied' ? 'Lien copié !' : 'Partager'}
        >
          <i className={`fas ${shareStatus === 'copied' ? 'fa-check' : 'fa-share-alt'}`}></i>
        </button>
      </div>

      {/* Mobile floating bar */}
      <div className="bs-mobile-bar" role="toolbar" aria-label="Actions">
        <button
          type="button"
          onClick={handleLike}
          disabled={pending}
          className={liked ? 'liked' : ''}
        >
          <i className={`${liked ? 'fas' : 'far'} fa-heart`}></i>
          <span>{count}</span>
        </button>
        <button type="button" onClick={scrollToComments}>
          <i className="far fa-comment"></i>
          <span>{commentsCount}</span>
        </button>
        <button
          type="button"
          onClick={handleBookmark}
          className={bookmarked ? 'liked' : ''}
        >
          <i className={`${bookmarked ? 'fas' : 'far'} fa-bookmark`}></i>
        </button>
        <button type="button" onClick={handleShare}>
          <i className={`fas ${shareStatus === 'copied' ? 'fa-check' : 'fa-share-alt'}`}></i>
        </button>
      </div>
    </>
  )
}
