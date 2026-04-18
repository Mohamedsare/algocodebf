import type { Metadata } from 'next'
import { requireAdmin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { BlogCreateClient } from '@/components/blog/blog-create-client'

export const metadata: Metadata = { title: 'Nouvel article' }

export default async function BlogCreatePage() {
  await requireAdmin()
  const supabase = await createClient()
  const { data: cats } = await supabase
    .from('blog_categories')
    .select('name, slug')
    .order('name')

  const baseCats = ['Actualités', 'Tutoriels', 'Carrière', 'Startups', 'Événements']
  const categories = [
    ...baseCats.map(c => ({ value: c, label: c })),
    ...(cats ?? [])
      .filter(c => !baseCats.includes(c.name))
      .map(c => ({ value: c.name, label: c.name })),
  ]

  return <BlogCreateClient mode="create" categories={categories} />
}
