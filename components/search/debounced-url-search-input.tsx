'use client'

import { useRouter, useSearchParams } from 'next/navigation'
import { useEffect, useState, type Ref } from 'react'
import { useSearchSuggest } from '@/components/search/use-search-suggest'
import { SearchSuggestPanel } from '@/components/search/search-suggest-panel'

interface Props {
  paramName: string
  basePath: string
  initialValue: string
  placeholder?: string
  suggestScopes?: string
  /** Debounce avant mise à jour de l’URL (liste / filtres). */
  navigateDebounceMs?: number
  /** Debounce plus court pour l’API suggest (interne au hook). */
  suggestDebounceMs?: number
  inputClassName?: string
  wrapperClassName?: string
  inputId?: string
  name?: string
  inputRef?: Ref<HTMLInputElement>
  'aria-label'?: string
  children?: (input: React.ReactNode) => React.ReactNode
}

export function DebouncedUrlSearchInput({
  paramName,
  basePath,
  initialValue,
  placeholder,
  suggestScopes,
  navigateDebounceMs = 320,
  suggestDebounceMs = 200,
  inputClassName = '',
  wrapperClassName = '',
  inputId,
  name,
  inputRef,
  'aria-label': ariaLabel,
  children,
}: Props) {
  const router = useRouter()
  const params = useSearchParams()
  const [val, setVal] = useState(initialValue)

  useEffect(() => {
    setVal(initialValue)
  }, [initialValue])

  useEffect(() => {
    const timer = window.setTimeout(() => {
      const sp = new URLSearchParams(params.toString())
      const v = val.trim()
      if (v) sp.set(paramName, v)
      else sp.delete(paramName)
      sp.delete('page')
      const qs = sp.toString()
      const next = qs ? `${basePath}?${qs}` : basePath
      const curQs = params.toString()
      const cur = curQs ? `${basePath}?${curQs}` : basePath
      if (next !== cur) router.replace(next, { scroll: false })
    }, navigateDebounceMs)
    return () => window.clearTimeout(timer)
  }, [val, paramName, basePath, navigateDebounceMs, router, params])

  const { data, loading } = useSearchSuggest(val, {
    scopes: suggestScopes,
    limit: 8,
    debounceMs: suggestDebounceMs,
    enabled: Boolean(suggestScopes),
  })

  const viewAll =
    val.trim().length >= 2 ? `/search?q=${encodeURIComponent(val.trim())}` : undefined

  const input = (
    <input
      ref={inputRef}
      id={inputId}
      name={name}
      type="search"
      value={val}
      onChange={e => setVal(e.target.value)}
      onKeyDown={e => {
        if (e.key === 'Enter') e.preventDefault()
      }}
      placeholder={placeholder}
      autoComplete="off"
      className={inputClassName}
      aria-label={ariaLabel ?? placeholder ?? 'Recherche'}
    />
  )

  return (
    <div className={[wrapperClassName, 'live-url-search'].filter(Boolean).join(' ')} role="search">
      {children ? children(input) : input}
      {suggestScopes ? (
        <SearchSuggestPanel
          data={data}
          loading={loading}
          variant="inline"
          showAllHref={viewAll}
          allLabel="Recherche globale"
        />
      ) : null}
    </div>
  )
}
