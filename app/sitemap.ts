import type { MetadataRoute } from 'next'
import { createClient } from '@/lib/supabase/server'

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? 'https://algocodebf.vercel.app'

export const revalidate = 3600

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const now = new Date()
  const staticRoutes: MetadataRoute.Sitemap = [
    '',
    '/about',
    '/forum',
    '/formations',
    '/project',
    '/job',
    '/blog',
    '/user',
    '/user/classement',
    '/politique/confidentialite',
    '/politique/mentions-legales',
    '/politique/cgu',
  ].map(path => ({
    url: `${SITE_URL}${path}`,
    lastModified: now,
    changeFrequency: 'daily',
    priority: path === '' ? 1 : 0.7,
  }))

  let dynamicRoutes: MetadataRoute.Sitemap = []
  try {
    const supabase = await createClient()
    const [blogs, tutorials, posts, projects, jobs] = await Promise.all([
      supabase.from('blog_posts').select('slug, updated_at').eq('status', 'published').limit(1000),
      supabase.from('tutorials').select('id, updated_at').eq('status', 'active').limit(1000),
      supabase.from('posts').select('id, updated_at').eq('status', 'active').limit(1000),
      supabase.from('projects').select('id, updated_at').eq('visibility', 'public').limit(1000),
      supabase.from('jobs').select('id, updated_at').eq('status', 'active').limit(1000),
    ])

    dynamicRoutes = [
      ...(blogs.data ?? []).map((b: { slug: string; updated_at: string }) => ({
        url: `${SITE_URL}/blog/${b.slug}`,
        lastModified: new Date(b.updated_at),
        changeFrequency: 'weekly' as const,
        priority: 0.8,
      })),
      ...(tutorials.data ?? []).map((t: { id: number; updated_at: string }) => ({
        url: `${SITE_URL}/formations/${t.id}`,
        lastModified: new Date(t.updated_at),
        changeFrequency: 'weekly' as const,
        priority: 0.8,
      })),
      ...(posts.data ?? []).map((p: { id: number; updated_at: string }) => ({
        url: `${SITE_URL}/forum/${p.id}`,
        lastModified: new Date(p.updated_at),
        changeFrequency: 'daily' as const,
        priority: 0.6,
      })),
      ...(projects.data ?? []).map((p: { id: number; updated_at: string }) => ({
        url: `${SITE_URL}/project/${p.id}`,
        lastModified: new Date(p.updated_at),
        changeFrequency: 'weekly' as const,
        priority: 0.6,
      })),
      ...(jobs.data ?? []).map((j: { id: number; updated_at: string }) => ({
        url: `${SITE_URL}/job/${j.id}`,
        lastModified: new Date(j.updated_at),
        changeFrequency: 'weekly' as const,
        priority: 0.5,
      })),
    ]
  } catch {
    // Ignorer les erreurs de BDD pour ne pas casser la génération du sitemap
  }

  return [...staticRoutes, ...dynamicRoutes]
}
