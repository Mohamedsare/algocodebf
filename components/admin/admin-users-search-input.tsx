'use client'

import { DebouncedUrlSearchInput } from '@/components/search/debounced-url-search-input'
import { ADMIN_CONSOLE_PATH } from '@/lib/routes'

export function AdminUsersSearchInput({ initialQ }: { initialQ: string }) {
  return (
    <DebouncedUrlSearchInput
      paramName="q"
      basePath={`${ADMIN_CONSOLE_PATH}/users`}
      initialValue={initialQ}
      name="q"
      placeholder="Rechercher un utilisateur…"
      suggestScopes="members"
      inputClassName="admin-users-search-field"
      wrapperClassName="admin-users-search-wrap live-url-search"
    >
      {input => input}
    </DebouncedUrlSearchInput>
  )
}
