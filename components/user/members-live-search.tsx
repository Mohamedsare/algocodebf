'use client'

import { DebouncedUrlSearchInput } from '@/components/search/debounced-url-search-input'

export function MembersLiveSearch({ initialQ }: { initialQ: string }) {
  return (
    <DebouncedUrlSearchInput
      paramName="q"
      basePath="/user"
      initialValue={initialQ}
      name="q"
      placeholder="Nom, compétence, université…"
      suggestScopes="members"
      wrapperClassName="mem-search live-url-search"
      aria-label="Rechercher un membre"
    >
      {input => (
        <>
          <i className="fas fa-search" aria-hidden />
          {input}
        </>
      )}
    </DebouncedUrlSearchInput>
  )
}
