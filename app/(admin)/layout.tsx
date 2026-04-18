import type { Metadata } from 'next'
import { requireAdmin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { AdminShell } from '@/components/admin/admin-shell'

export const metadata: Metadata = {
  title: { template: '%s · Admin', default: 'Admin - AlgoCodeBF' },
}

export default async function AdminLayout({ children }: { children: React.ReactNode }) {
  const profile = await requireAdmin()

  const supabase = await createClient()
  const [
    { count: totalUsers },
    { count: totalPosts },
    { count: totalTutorials },
    { count: totalProjects },
    { count: totalJobs },
    { count: totalSubscribers },
    { count: pendingReports },
  ] = await Promise.all([
    supabase.from('profiles').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('posts').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('tutorials').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('projects').select('*', { count: 'exact', head: true }),
    supabase.from('jobs').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('newsletter_subscribers').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('reports').select('*', { count: 'exact', head: true }).eq('status', 'pending'),
  ])

  const stats = {
    total_users: totalUsers ?? 0,
    total_posts: totalPosts ?? 0,
    total_tutorials: totalTutorials ?? 0,
    total_projects: totalProjects ?? 0,
    total_jobs: totalJobs ?? 0,
    total_subscribers: totalSubscribers ?? 0,
    pending_reports: pendingReports ?? 0,
  }

  return (
    <AdminShell profile={profile} stats={stats}>
      {children}
    </AdminShell>
  )
}
