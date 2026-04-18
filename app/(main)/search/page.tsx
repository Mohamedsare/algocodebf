import type { Metadata } from 'next'
import Link from 'next/link'
import Image from 'next/image'
import { createClient } from '@/lib/supabase/server'
import { Avatar } from '@/components/ui/avatar'
import { EmptyState } from '@/components/shared/empty-state'
import {
  Search, Users, BookOpen, GraduationCap, MessageSquare, Code, Briefcase, ArrowRight,
} from 'lucide-react'
import { buildFileUrl, formatDateShort, truncate } from '@/lib/utils'

export const metadata: Metadata = {
  title: 'Recherche',
  description: 'Recherchez dans les membres, formations, articles et projets de la communauté.',
}

const PER_CATEGORY = 4

interface SearchParams {
  q?: string
}

export default async function SearchPage({
  searchParams,
}: {
  searchParams: Promise<SearchParams>
}) {
  const params = await searchParams
  const q = (params.q ?? '').trim()

  if (!q) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-950 py-16 px-4">
        <div className="max-w-2xl mx-auto">
          <EmptyState
            icon={<Search size={32} />}
            title="Recherche globale"
            description="Saisissez un terme dans la barre de recherche en haut pour explorer membres, formations, articles et projets."
          />
        </div>
      </div>
    )
  }

  const supabase = await createClient()
  const like = `%${q}%`

  const [members, posts, tutorials, articles, projects, jobs] = await Promise.all([
    supabase
      .from('profiles')
      .select('id, prenom, nom, university, faculty, photo_path, bio, points')
      .or(`prenom.ilike.${like},nom.ilike.${like},university.ilike.${like},faculty.ilike.${like}`)
      .order('points', { ascending: false })
      .limit(PER_CATEGORY),

    supabase
      .from('posts')
      .select('id, title, excerpt, views, created_at, profiles!inner(prenom, nom, photo_path)')
      .eq('status', 'active')
      .ilike('title', like)
      .order('created_at', { ascending: false })
      .limit(PER_CATEGORY),

    supabase
      .from('tutorials')
      .select('id, title, description, thumbnail, views, likes_count, profiles!inner(prenom, nom)')
      .eq('status', 'active')
      .or(`title.ilike.${like},description.ilike.${like}`)
      .order('views', { ascending: false })
      .limit(PER_CATEGORY),

    supabase
      .from('blog_posts')
      .select('id, title, slug, excerpt, featured_image, category, published_at')
      .eq('status', 'published')
      .or(`title.ilike.${like},excerpt.ilike.${like}`)
      .order('published_at', { ascending: false })
      .limit(PER_CATEGORY),

    supabase
      .from('projects')
      .select('id, title, description, status, created_at, profiles!inner(prenom, nom)')
      .or(`title.ilike.${like},description.ilike.${like}`)
      .order('created_at', { ascending: false })
      .limit(PER_CATEGORY),

    supabase
      .from('jobs')
      .select('id, title, city, type, company_name, created_at')
      .eq('status', 'active')
      .or(`title.ilike.${like},description.ilike.${like},company_name.ilike.${like}`)
      .order('created_at', { ascending: false })
      .limit(PER_CATEGORY),
  ])

  const total =
    (members.data?.length ?? 0) +
    (posts.data?.length ?? 0) +
    (tutorials.data?.length ?? 0) +
    (articles.data?.length ?? 0) +
    (projects.data?.length ?? 0) +
    (jobs.data?.length ?? 0)

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-950 py-10 px-4">
      <div className="max-w-5xl mx-auto">
        {/* Header */}
        <div className="mb-8">
          <p className="text-sm text-gray-500 mb-1">Résultats pour</p>
          <h1 className="text-3xl font-black text-gray-900 dark:text-white">
            <Search size={24} className="inline-block mr-2 text-[#C8102E]" />
            « {q} »
          </h1>
          <p className="text-gray-500 text-sm mt-1">
            {total} résultat{total > 1 ? 's' : ''} au total
          </p>
        </div>

        {total === 0 ? (
          <EmptyState
            icon={<Search size={32} />}
            title="Aucun résultat"
            description={`Aucun contenu ne correspond à « ${q} ». Essayez d'autres mots-clés.`}
          />
        ) : (
          <div className="space-y-10">
            {/* Membres */}
            {members.data && members.data.length > 0 && (
              <CategorySection
                icon={Users}
                title="Membres"
                color="text-[#C8102E]"
                more={`/user?q=${encodeURIComponent(q)}`}
              >
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {members.data.map(m => (
                    <Link
                      key={m.id}
                      href={`/user/${m.id}`}
                      className="flex items-center gap-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-3 hover:border-[#C8102E]/30 transition-colors"
                    >
                      <Avatar src={m.photo_path} prenom={m.prenom} nom={m.nom} size="md" />
                      <div className="min-w-0 flex-1">
                        <p className="font-semibold text-gray-900 dark:text-white text-sm truncate">
                          {m.prenom} {m.nom}
                        </p>
                        <p className="text-xs text-gray-500 truncate">
                          {m.university ?? m.faculty ?? '—'}
                        </p>
                      </div>
                      <span className="text-xs text-[#FFD100] font-bold">{m.points} pts</span>
                    </Link>
                  ))}
                </div>
              </CategorySection>
            )}

            {/* Articles blog */}
            {articles.data && articles.data.length > 0 && (
              <CategorySection
                icon={BookOpen}
                title="Articles"
                color="text-[#6C63FF]"
                more={`/blog?q=${encodeURIComponent(q)}`}
              >
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {articles.data.map(a => (
                    <Link
                      key={a.id}
                      href={`/blog/${a.slug}`}
                      className="flex gap-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-3 hover:border-[#C8102E]/30 transition-colors"
                    >
                      <div className="relative w-20 h-20 rounded-xl overflow-hidden bg-gray-200 dark:bg-gray-800 flex-shrink-0">
                        {a.featured_image && (
                          <Image
                            src={buildFileUrl(a.featured_image)}
                            alt={a.title}
                            fill
                            className="object-cover"
                            sizes="80px"
                          />
                        )}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2">
                          {a.title}
                        </p>
                        {a.excerpt && (
                          <p className="text-xs text-gray-500 line-clamp-2 mt-0.5">
                            {truncate(a.excerpt, 80)}
                          </p>
                        )}
                        {a.published_at && (
                          <p className="text-xs text-gray-400 mt-1">
                            {formatDateShort(a.published_at)}
                          </p>
                        )}
                      </div>
                    </Link>
                  ))}
                </div>
              </CategorySection>
            )}

            {/* Formations */}
            {tutorials.data && tutorials.data.length > 0 && (
              <CategorySection
                icon={GraduationCap}
                title="Formations"
                color="text-[#FFD100]"
                more={`/formations?search=${encodeURIComponent(q)}`}
              >
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {tutorials.data.map(t => (
                    <Link
                      key={t.id}
                      href={`/formations/${t.id}`}
                      className="flex gap-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-3 hover:border-[#FFD100]/40 transition-colors"
                    >
                      <div className="relative w-20 h-20 rounded-xl overflow-hidden bg-gray-200 dark:bg-gray-800 flex-shrink-0">
                        {t.thumbnail && (
                          <Image src={buildFileUrl(t.thumbnail)} alt={t.title} fill className="object-cover" sizes="80px" />
                        )}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2">{t.title}</p>
                        {t.description && (
                          <p className="text-xs text-gray-500 line-clamp-2 mt-0.5">
                            {truncate(t.description, 80)}
                          </p>
                        )}
                        <p className="text-xs text-gray-400 mt-1">
                          {t.views} vues · {t.likes_count} likes
                        </p>
                      </div>
                    </Link>
                  ))}
                </div>
              </CategorySection>
            )}

            {/* Discussions forum */}
            {posts.data && posts.data.length > 0 && (
              <CategorySection
                icon={MessageSquare}
                title="Discussions"
                color="text-[#006A4E]"
                more={`/forum?q=${encodeURIComponent(q)}`}
              >
                <div className="space-y-2">
                  {posts.data.map(p => {
                    const author = p.profiles as unknown as { prenom: string; nom: string; photo_path: string | null } | null
                    return (
                      <Link
                        key={p.id}
                        href={`/forum/${p.id}`}
                        className="flex items-center gap-3 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3 hover:border-[#006A4E]/30 transition-colors"
                      >
                        <Avatar src={author?.photo_path} prenom={author?.prenom} nom={author?.nom} size="sm" />
                        <div className="flex-1 min-w-0">
                          <p className="font-medium text-gray-900 dark:text-white text-sm line-clamp-1">{p.title}</p>
                          <p className="text-xs text-gray-500">
                            {author?.prenom} {author?.nom} · {p.views} vues · {formatDateShort(p.created_at)}
                          </p>
                        </div>
                      </Link>
                    )
                  })}
                </div>
              </CategorySection>
            )}

            {/* Projets */}
            {projects.data && projects.data.length > 0 && (
              <CategorySection
                icon={Code}
                title="Projets"
                color="text-[#007BFF]"
                more={`/project?q=${encodeURIComponent(q)}`}
              >
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {projects.data.map(p => {
                    const author = p.profiles as unknown as { prenom: string; nom: string } | null
                    return (
                      <Link
                        key={p.id}
                        href={`/project/${p.id}`}
                        className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-4 hover:border-[#007BFF]/30 transition-colors"
                      >
                        <p className="font-semibold text-gray-900 dark:text-white text-sm line-clamp-1">{p.title}</p>
                        {p.description && (
                          <p className="text-xs text-gray-500 line-clamp-2 mt-1">
                            {truncate(p.description, 100)}
                          </p>
                        )}
                        <p className="text-xs text-gray-400 mt-2">
                          {author?.prenom} {author?.nom} · {p.status}
                        </p>
                      </Link>
                    )
                  })}
                </div>
              </CategorySection>
            )}

            {/* Opportunités */}
            {jobs.data && jobs.data.length > 0 && (
              <CategorySection
                icon={Briefcase}
                title="Opportunités"
                color="text-orange-500"
                more={`/job?q=${encodeURIComponent(q)}`}
              >
                <div className="space-y-2">
                  {jobs.data.map(j => (
                    <Link
                      key={j.id}
                      href={`/job/${j.id}`}
                      className="flex items-center gap-3 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3 hover:border-orange-300 transition-colors"
                    >
                      <div className="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center flex-shrink-0">
                        <Briefcase size={16} />
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="font-medium text-gray-900 dark:text-white text-sm line-clamp-1">{j.title}</p>
                        <p className="text-xs text-gray-500">
                          {j.company_name ?? '—'} · {j.city ?? 'Toute ville'} · {j.type}
                        </p>
                      </div>
                    </Link>
                  ))}
                </div>
              </CategorySection>
            )}
          </div>
        )}
      </div>
    </div>
  )
}

function CategorySection({
  icon: Icon,
  title,
  color,
  more,
  children,
}: {
  icon: React.ComponentType<{ size?: number; className?: string }>
  title: string
  color: string
  more?: string
  children: React.ReactNode
}) {
  return (
    <section>
      <div className="flex items-center justify-between mb-4">
        <h2 className={`flex items-center gap-2 font-bold text-lg text-gray-900 dark:text-white`}>
          <Icon size={20} className={color} />
          {title}
        </h2>
        {more && (
          <Link
            href={more}
            className="flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-900 dark:hover:text-gray-300"
          >
            Voir tout <ArrowRight size={12} />
          </Link>
        )}
      </div>
      {children}
    </section>
  )
}
