import type { Metadata } from 'next'
import { notFound, redirect } from 'next/navigation'
import { requireLogin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { getForumCategories } from '@/lib/queries/forum'
import { ForumCreateClient } from '@/components/forum/forum-create-client'

export const metadata: Metadata = { title: 'Modifier la discussion' }

interface EditPageProps {
  params: Promise<{ id: string }>
}

export default async function ForumEditPage({ params }: EditPageProps) {
  const { id } = await params
  const numericId = Number(id)
  if (!Number.isFinite(numericId)) notFound()

  const profile = await requireLogin()
  const supabase = await createClient()
  const { data: post } = await supabase
    .from('posts')
    .select('id, user_id, title, body, category')
    .eq('id', numericId)
    .maybeSingle()

  if (!post) notFound()
  if (post.user_id !== profile.id && profile.role !== 'admin') {
    redirect(`/forum/${numericId}`)
  }

  const categories = await getForumCategories()

  return (
    <ForumCreateClient
      categories={categories}
      mode="edit"
      initial={{
        id: post.id,
        title: post.title,
        category: post.category ?? '',
        body: post.body,
      }}
    />
  )
}
