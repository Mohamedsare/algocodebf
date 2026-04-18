'use client'

import { useEffect, useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { toggleLikeAction } from '@/app/actions/forum'
import { createClient } from '@/lib/supabase/client'

interface Props {
  type: 'post' | 'comment'
  id: number
  initialLiked: boolean
  initialCount: number
  isAuthenticated: boolean
  className?: string
  label?: string
  variant?: 'default' | 'saas'
}

/**
 * Bouton "j'aime" avec :
 * - optimistic UI (toggle immédiat)
 * - synchronisation realtime (le compteur change si d'autres utilisateurs likent)
 * - rollback en cas d'erreur
 */
export function ForumLikeButton({
  type,
  id,
  initialLiked,
  initialCount,
  isAuthenticated,
  className,
  label = "J'aime",
  variant = 'default',
}: Props) {
  const router = useRouter()
  const [liked, setLiked] = useState(initialLiked)
  const [count, setCount] = useState(initialCount)
  const [pending, startTransition] = useTransition()

  // Realtime : écoute les INSERT/DELETE sur likes pour cet élément
  useEffect(() => {
    const supabase = createClient()
    const channel = supabase
      .channel(`likes:${type}:${id}`)
      .on(
        'postgres_changes',
        {
          event: 'INSERT',
          schema: 'public',
          table: 'likes',
          filter: `likeable_id=eq.${id}`,
        },
        (payload) => {
          const row = payload.new as { likeable_type: string }
          if (row.likeable_type !== type) return
          setCount((c) => c + 1)
        },
      )
      .on(
        'postgres_changes',
        {
          event: 'DELETE',
          schema: 'public',
          table: 'likes',
          filter: `likeable_id=eq.${id}`,
        },
        (payload) => {
          const row = payload.old as { likeable_type: string }
          if (row.likeable_type !== type) return
          setCount((c) => Math.max(0, c - 1))
        },
      )
      .subscribe()

    return () => {
      supabase.removeChannel(channel)
    }
  }, [type, id])

  const handleClick = () => {
    if (!isAuthenticated) {
      router.push('/login')
      return
    }
    const wasLiked = liked
    // Optimistic
    setLiked(!wasLiked)
    setCount((c) => c + (wasLiked ? -1 : 1))

    startTransition(async () => {
      const res = await toggleLikeAction(type, id)
      if (!res.ok || !res.data) {
        // rollback
        setLiked(wasLiked)
        setCount((c) => c + (wasLiked ? 1 : -1))
        return
      }
      // On aligne avec le serveur (au cas où plusieurs clics rapides)
      setLiked(res.data.liked)
      setCount(res.data.count)
    })
  }

  if (variant === 'saas') {
    return (
      <button
        type="button"
        onClick={handleClick}
        disabled={pending}
        className={`react-btn${liked ? ' is-liked' : ''}${className ? ' ' + className : ''}`}
        aria-pressed={liked}
        title={liked ? 'Je n’aime plus' : label}
      >
        <i className={`${liked ? 'fas' : 'far'} fa-heart`}></i>
        <span>{count}</span>
        <span style={{ opacity: 0.7, fontWeight: 500 }}>{liked ? 'Aimé' : label}</span>
      </button>
    )
  }

  const cls = type === 'post' ? 'btn-like' : 'btn-like-comment'
  return (
    <button
      type="button"
      onClick={handleClick}
      disabled={pending}
      className={`${cls}${liked ? ' liked' : ''}${className ? ' ' + className : ''}`}
      title={liked ? 'Je n’aime plus' : "J’aime"}
    >
      <i className={`${liked ? 'fas' : 'far'} fa-heart`}></i>
      <span>{count}</span>
    </button>
  )
}
