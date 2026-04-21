'use client'

import { useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { incrementJobViews } from '@/app/actions/forum'

const key = (id: number) => `bf_job_view_${id}`

/** Compte une vue d’offre d’emploi (session), avec RPC contournant la RLS. */
export function JobViewTracker({ jobId }: { jobId: number }) {
  const router = useRouter()

  useEffect(() => {
    if (typeof window === 'undefined') return
    const k = key(jobId)
    if (sessionStorage.getItem(k)) return
    sessionStorage.setItem(k, '1')
    void (async () => {
      await incrementJobViews(jobId)
      router.refresh()
    })()
  }, [jobId, router])

  return null
}
