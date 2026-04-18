import type { Metadata } from 'next'
import { requireLogin } from '@/lib/auth'
import { getForumCategories } from '@/lib/queries/forum'
import { ForumCreateClient } from '@/components/forum/forum-create-client'

export const metadata: Metadata = { title: 'Nouvelle Discussion' }

export default async function ForumCreatePage() {
  await requireLogin()
  const categories = await getForumCategories()
  return <ForumCreateClient categories={categories} mode="create" />
}
