import type { Metadata } from 'next'
import { notFound, redirect } from 'next/navigation'
import { requireLogin } from '@/lib/auth'
import { getProjectDetail } from '@/lib/queries/projects'
import { ProjectCreateClient } from '@/components/project/project-create-client'

export const metadata: Metadata = { title: 'Modifier le projet - AlgoCodeBF' }

export default async function EditProjectPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const profile = await requireLogin()
  const pid = Number(id)
  if (Number.isNaN(pid)) notFound()

  const detail = await getProjectDetail(pid, profile.id)
  if (!detail) notFound()
  if (!detail.isOwner && profile.role !== 'admin') redirect(`/project/${pid}`)

  return <ProjectCreateClient mode="edit" project={detail.project} />
}
