'use client'

import { useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { incrementPostViews } from '@/app/actions/forum'

const storageKey = (postId: number) => `bf_forum_view_${postId}`

/**
 * Compte une vue uniquement lorsque la page est réellement affichée dans le navigateur
 * (pas lors du prefetch RSC des liens). Une fois par onglet / session (sessionStorage).
 */
export function ForumThreadViewTracker({ postId }: { postId: number }) {
  const router = useRouter()

  useEffect(() => {
    if (typeof window === 'undefined') return
    const key = storageKey(postId)
    if (sessionStorage.getItem(key)) return
    sessionStorage.setItem(key, '1')

    void (async () => {
      await incrementPostViews(postId)
      router.refresh()
    })()
  }, [postId, router])

  return null
}
