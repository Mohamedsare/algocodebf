import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { requireAdmin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { BlogCreateClient } from '@/components/blog/blog-create-client'

export const metadata: Metadata = { title: "Modifier l'article" }

interface EditProps {
  params: Promise<{ slug: string }>
}

export default async function BlogEditPage({ params }: EditProps) {
  const { slug } = await params
  await requireAdmin()
  const supabase = await createClient()

  const [{ data: post }, { data: cats }] = await Promise.all([
    supabase.from('blog_posts').select('*').eq('slug', slug).maybeSingle(),
    supabase.from('blog_categories').select('name').order('name'),
  ])

  if (!post) notFound()

  const baseCats = ['Actualités', 'Tutoriels', 'Carrière', 'Startups', 'Événements']
  const categories = [
    ...baseCats.map(c => ({ value: c, label: c })),
    ...((cats ?? []) as Array<{ name: string }>)
      .filter(c => !baseCats.includes(c.name))
      .map(c => ({ value: c.name, label: c.name })),
  ]

  return (
    <BlogCreateClient
      mode="edit"
      postId={post.id}
      categories={categories}
      initial={{
        title: post.title,
        slug: post.slug,
        excerpt: post.excerpt ?? '',
        content: post.content ?? '',
        category: post.category ?? '',
        status: post.status,
        tags: post.tags,
        featured_image: post.featured_image,
      }}
    />
  )
}
