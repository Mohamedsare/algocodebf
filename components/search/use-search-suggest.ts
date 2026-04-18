'use client'

import { useEffect, useState } from 'react'
import type { SearchSuggestResponse } from '@/lib/search/run-suggest'

export function useSearchSuggest(
  q: string,
  options: {
    scopes?: string
    limit?: number
    debounceMs?: number
    enabled?: boolean
  } = {}
): { data: SearchSuggestResponse | null; loading: boolean } {
  const { scopes, limit = 6, debounceMs = 260, enabled = true } = options
  const [data, setData] = useState<SearchSuggestResponse | null>(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    const trimmed = q.trim()
    if (!enabled || trimmed.length < 2) {
      setData(null)
      setLoading(false)
      return
    }

    const ac = new AbortController()
    const timer = window.setTimeout(async () => {
      setLoading(true)
      try {
        const params = new URLSearchParams({ q: trimmed, limit: String(limit) })
        if (scopes) params.set('scopes', scopes)
        const res = await fetch(`/api/search/suggest?${params}`, { signal: ac.signal })
        if (!res.ok) throw new Error('suggest failed')
        const json = (await res.json()) as SearchSuggestResponse
        setData(json)
      } catch (e) {
        if ((e as Error).name !== 'AbortError') setData(null)
      } finally {
        setLoading(false)
      }
    }, debounceMs)

    return () => {
      window.clearTimeout(timer)
      ac.abort()
    }
  }, [q, scopes, limit, debounceMs, enabled])

  return { data, loading }
}
