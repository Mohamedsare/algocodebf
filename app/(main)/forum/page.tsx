import type { Metadata } from 'next'
import Link from 'next/link'
import { listForumPosts, getForumCategories, getForumStats } from '@/lib/queries/forum'
import { createClient } from '@/lib/supabase/server'
import { getCurrentProfile } from '@/lib/auth'
import { ForumListLive } from '@/components/forum/forum-list-live'
import { ForumSidebarLive } from '@/components/forum/forum-sidebar-live'

export const metadata: Metadata = {
  title: 'Forum — Discussions en temps réel',
  description:
    'Le forum ultra-réactif d\'AlgoCodeBF : posez vos questions, partagez vos projets, discutez des dernières tendances tech avec la communauté burkinabè en temps réel.',
}

interface ForumPageProps {
  searchParams: Promise<{ category?: string; page?: string; q?: string }>
}

async function getTrendingTopics() {
  const supabase = await createClient()
  const d7 = new Date(Date.now() - 7 * 24 * 3600 * 1000).toISOString()
  const { data } = await supabase
    .from('posts')
    .select('id, title, views')
    .eq('status', 'active')
    .gte('created_at', d7)
    .order('views', { ascending: false })
    .limit(5)
  return (data ?? []).map((t, i) => ({
    ...(t as { id: number; title: string; views: number }),
    rank: i + 1,
  }))
}

async function getTopContributors() {
  const supabase = await createClient()
  const { data: posts } = await supabase
    .from('posts')
    .select('user_id')
    .eq('status', 'active')

  const counts = new Map<string, number>()
  for (const p of posts ?? []) {
    const uid = (p as { user_id: string | null }).user_id
    if (!uid) continue
    counts.set(uid, (counts.get(uid) ?? 0) + 1)
  }
  const topIds = [...counts.entries()]
    .sort((a, b) => b[1] - a[1])
    .slice(0, 5)
    .map(([uid]) => uid)
  if (topIds.length === 0) return []

  const { data: profiles } = await supabase
    .from('profiles')
    .select('id, prenom, nom, photo_path')
    .in('id', topIds)

  return (profiles ?? [])
    .map((p) => {
      const row = p as { id: string; prenom: string; nom: string; photo_path: string | null }
      return { ...row, posts_count: counts.get(row.id) ?? 0 }
    })
    .sort((a, b) => b.posts_count - a.posts_count)
}

async function getCategoryCounts(): Promise<Record<string, number>> {
  const supabase = await createClient()
  const { data } = await supabase.from('posts').select('category').eq('status', 'active')
  const counts: Record<string, number> = {}
  for (const row of data ?? []) {
    const c = (row as { category: string | null }).category
    if (!c) continue
    counts[c] = (counts[c] ?? 0) + 1
  }
  return counts
}

export default async function ForumPage({ searchParams }: ForumPageProps) {
  const params = await searchParams
  const page = Math.max(1, Number(params.page ?? '1'))
  const category = params.category?.trim() || null
  const search = params.q?.trim() || ''

  const [{ posts, total, totalPages }, categories, stats, trending, contributors, profile, categoryCounts] =
    await Promise.all([
      listForumPosts({ category, search, page, pageSize: 20 }),
      getForumCategories(),
      getForumStats(),
      getTrendingTopics(),
      getTopContributors(),
      getCurrentProfile(),
      getCategoryCounts(),
    ])

  return (
    <div className="forum-saas">
      {/* Hero */}
      <section className="forum-hero-saas">
        <div className="container-xl">
          <div className="forum-hero-inner">
            <div>
              <h1 className="forum-hero-title">
                <span className="title-badge">
                  <i className="fas fa-comments"></i>
                </span>
                <span>
                  Forum
                  <span className="forum-hero-live forum-hero-live--title">
                    <span className="live-dot"></span>
                    LIVE
                  </span>
                </span>
              </h1>
              <p className="forum-hero-sub">
                Le lieu où la communauté tech du Burkina Faso échange, apprend et avance ensemble.
                Posez vos questions, partagez vos projets, suivez les discussions en temps réel.
              </p>
            </div>
            <div className="forum-hero-actions">
              {profile ? (
                <Link href="/forum/creer" className="btn-saas primary">
                  <i className="fas fa-plus"></i>
                  Nouvelle discussion
                </Link>
              ) : (
                <Link href="/login?redirect=/forum/creer" className="btn-saas primary">
                  <i className="fas fa-sign-in-alt"></i>
                  Rejoindre la discussion
                </Link>
              )}
            </div>
          </div>

          {/* Stats bar */}
          <div className="forum-stats-bar">
            <div className="forum-stat-chip posts">
              <span className="chip-icon">
                <i className="fas fa-comment-dots"></i>
              </span>
              <div className="chip-body">
                <span className="chip-value">{stats.total_posts.toLocaleString('fr-FR')}</span>
                <span className="chip-label">Discussions</span>
              </div>
            </div>
            <div className="forum-stat-chip members">
              <span className="chip-icon">
                <i className="fas fa-users"></i>
              </span>
              <div className="chip-body">
                <span className="chip-value">{stats.active_members.toLocaleString('fr-FR')}</span>
                <span className="chip-label">Membres actifs</span>
              </div>
            </div>
            <div className="forum-stat-chip trending">
              <span className="chip-icon">
                <i className="fas fa-fire"></i>
              </span>
              <div className="chip-body">
                <span className="chip-value">{stats.trending_topics.toLocaleString('fr-FR')}</span>
                <span className="chip-label">Tendances</span>
              </div>
            </div>
            <div className="forum-stat-chip today">
              <span className="chip-icon">
                <i className="fas fa-bolt"></i>
              </span>
              <div className="chip-body">
                <span className="chip-value">{stats.today_posts.toLocaleString('fr-FR')}</span>
                <span className="chip-label">Aujourd&apos;hui</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Main grid */}
      <section className="container-xl forum-page-main">
        <div className="forum-grid">
          <main>
            <ForumListLive
              initialPosts={posts}
              total={total}
              page={page}
              totalPages={totalPages}
              categories={categories}
              categoryCounts={categoryCounts}
              currentCategory={category}
              initialQuery={search}
              isAuthenticated={Boolean(profile)}
            />
          </main>

          <aside className="forum-sidebar-saas">
            <ForumSidebarLive
              initialTrending={trending}
              initialContributors={contributors}
              profile={profile}
            />
          </aside>
        </div>
      </section>
    </div>
  )
}
