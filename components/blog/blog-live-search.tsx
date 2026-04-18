'use client'

import { useRouter } from 'next/navigation'
import { useRef } from 'react'
import { DebouncedUrlSearchInput } from '@/components/search/debounced-url-search-input'

export function BlogLiveSearch({ initialQ }: { initialQ: string }) {
  const router = useRouter()
  const inputRef = useRef<HTMLInputElement>(null)

  const goGlobal = () => {
    const q = inputRef.current?.value?.trim() ?? ''
    if (q) router.push(`/search?q=${encodeURIComponent(q)}`)
  }

  return (
    <DebouncedUrlSearchInput
      paramName="q"
      basePath="/blog"
      initialValue={initialQ}
      placeholder="Un sujet, un auteur, une technologie…"
      suggestScopes="blog"
      inputRef={inputRef}
      inputId="blog-live-q"
      wrapperClassName="bs-search bs-search--live live-url-search"
    >
      {input => (
        <>
          <i className="fas fa-search bs-search-icon" aria-hidden />
          {input}
          <button type="button" aria-label="Recherche globale sur tout le site" onClick={goGlobal}>
            <span className="bs-hide-sm">Rechercher</span>
            <i className="fas fa-arrow-right" aria-hidden />
          </button>
        </>
      )}
    </DebouncedUrlSearchInput>
  )
}
