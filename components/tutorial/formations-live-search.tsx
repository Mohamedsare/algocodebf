'use client'

import { DebouncedUrlSearchInput } from '@/components/search/debounced-url-search-input'
import { FORMATIONS_PATH } from '@/lib/routes'

export function FormationsLiveSearch({ initialSearch }: { initialSearch: string }) {
  return (
    <DebouncedUrlSearchInput
      paramName="search"
      basePath={FORMATIONS_PATH}
      initialValue={initialSearch}
      placeholder="Rechercher une formation…"
      suggestScopes="tutorials"
      wrapperClassName="search-form-tutorials live-url-search"
    >
      {input => (
        <div className="search-input-wrapper">
          {input}
          <button type="button" className="search-btn" tabIndex={-1} aria-hidden="true">
            <i className="fas fa-search" />
          </button>
        </div>
      )}
    </DebouncedUrlSearchInput>
  )
}
