import { redirect } from 'next/navigation'
import { requireLogin } from '@/lib/auth'
import { getMySpacePath } from '@/lib/my-space'

export default async function EspaceIndexPage() {
  const profile = await requireLogin('/espace')
  redirect(getMySpacePath(profile))
}
