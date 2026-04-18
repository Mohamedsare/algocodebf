'use client'

import { DebouncedUrlSearchInput } from '@/components/search/debounced-url-search-input'

export function ProjectLiveSearchField({ initialQ }: { initialQ: string }) {
  return (
    <DebouncedUrlSearchInput
      paramName="q"
      basePath="/project"
      initialValue={initialQ}
      name="q"
      placeholder="Nom, techno, mot-clé…"
      suggestScopes="projects"
      inputId="project-search-q"
      wrapperClassName="live-url-search"
      aria-label="Rechercher un projet"
    >
      {input => input}
    </DebouncedUrlSearchInput>
  )
}
