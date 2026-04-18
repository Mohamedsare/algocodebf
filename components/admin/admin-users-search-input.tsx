'use client'

import { DebouncedUrlSearchInput } from '@/components/search/debounced-url-search-input'

export function AdminUsersSearchInput({ initialQ }: { initialQ: string }) {
  return (
    <DebouncedUrlSearchInput
      paramName="q"
      basePath="/admin/users"
      initialValue={initialQ}
      name="q"
      placeholder="Rechercher (prénom, nom, université)…"
      suggestScopes="members"
      inputClassName="flex-1 min-w-[220px] h-9 px-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm"
      wrapperClassName="flex-1 min-w-[220px] live-url-search"
    >
      {input => input}
    </DebouncedUrlSearchInput>
  )
}
