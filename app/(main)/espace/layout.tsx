import { getProfile } from '@/lib/supabase/server'
import { EspaceAppShell } from '@/components/espace/espace-app-shell'
import type { AccountKind } from '@/types'

export default async function EspaceLayout({ children }: { children: React.ReactNode }) {
  const profile = await getProfile()
  if (!profile) {
    return <>{children}</>
  }
  const kind: AccountKind = profile.account_kind ?? 'student'
  return <EspaceAppShell profile={profile} kind={kind}>{children}</EspaceAppShell>
}
