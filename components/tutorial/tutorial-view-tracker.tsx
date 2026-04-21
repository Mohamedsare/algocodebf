'use client'

import { useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { incrementTutorialViews } from '@/app/actions/forum'

const key = (id: number) => `bf_formation_view_${id}`

/**
 * Une vue formation = visite effective dans l’onglet (pas le prefetch RSC), au plus une fois par session.
 */
export function TutorialViewTracker({ tutorialId }: { tutorialId: number }) {
  const router = useRouter()

  useEffect(() => {
    if (typeof window === 'undefined') return
    const k = key(tutorialId)
    if (sessionStorage.getItem(k)) return
    sessionStorage.setItem(k, '1')
    void (async () => {
      await incrementTutorialViews(tutorialId)
      router.refresh()
    })()
  }, [tutorialId, router])

  return null
}
