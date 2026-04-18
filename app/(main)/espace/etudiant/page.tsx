import type { Metadata } from 'next'
import { redirect } from 'next/navigation'
import { requireLogin } from '@/lib/auth'
import { EspaceHome } from '@/components/espace/espace-home'
import { getMySpacePath, MY_SPACE_PATHS } from '@/lib/my-space'

export const metadata: Metadata = {
  title: 'Mon espace étudiant',
  robots: { index: false },
}

export default async function EspaceEtudiantPage() {
  const profile = await requireLogin(MY_SPACE_PATHS.student)
  if (getMySpacePath(profile) !== MY_SPACE_PATHS.student) {
    redirect(getMySpacePath(profile))
  }
  return <EspaceHome profile={profile} kind="student" />
}
