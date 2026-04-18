import type { Metadata } from 'next'
import { redirect } from 'next/navigation'
import { requireLogin } from '@/lib/auth'
import { EspaceHome } from '@/components/espace/espace-home'
import { getMySpacePath, MY_SPACE_PATHS } from '@/lib/my-space'

export const metadata: Metadata = {
  title: 'Mon espace formateur',
  robots: { index: false },
}

export default async function EspaceFormateurPage() {
  const profile = await requireLogin(MY_SPACE_PATHS.professional)
  if (getMySpacePath(profile) !== MY_SPACE_PATHS.professional) {
    redirect(getMySpacePath(profile))
  }
  return <EspaceHome profile={profile} kind="professional" />
}
