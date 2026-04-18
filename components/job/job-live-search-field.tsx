'use client'

import { DebouncedUrlSearchInput } from '@/components/search/debounced-url-search-input'

export function JobLiveSearchField({ initialQ }: { initialQ: string }) {
  return (
    <DebouncedUrlSearchInput
      paramName="q"
      basePath="/job"
      initialValue={initialQ}
      name="q"
      placeholder="Titre, entreprise, mot-clé…"
      suggestScopes="jobs"
      inputId="job-search-q"
      wrapperClassName="live-url-search"
    >
      {input => (
        <div className="js-input-wrap">
          <i className="fas fa-search" aria-hidden />
          {input}
        </div>
      )}
    </DebouncedUrlSearchInput>
  )
}
