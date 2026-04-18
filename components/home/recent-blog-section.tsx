import Link from 'next/link'
import Image from 'next/image'
import { ArrowRight, Clock } from 'lucide-react'
import { Badge } from '@/components/ui/badge'
import { Avatar } from '@/components/ui/avatar'
import { formatDateShort, readingTime, buildFileUrl } from '@/lib/utils'

interface RecentBlogSectionProps {
  articles: {
    id: number
    title: string
    slug: string
    excerpt: string | null
    featured_image: string | null
    category: string | null
    views: number
    published_at: string | null
    profiles: { prenom: string; nom: string; photo_path: string | null } | null
  }[]
}

export function RecentBlogSection({ articles }: RecentBlogSectionProps) {
  if (!articles.length) return null

  const [featured, ...rest] = articles

  return (
    <section className="bg-gray-50 dark:bg-gray-900/50 py-16">
      <div className="max-w-7xl mx-auto px-4">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h2 className="text-2xl font-black text-gray-900 dark:text-white">Articles récents</h2>
            <p className="text-gray-500 text-sm mt-1">Les dernières publications du blog</p>
          </div>
          <Link
            href="/blog"
            className="flex items-center gap-1.5 text-[#C8102E] text-sm font-semibold hover:gap-3 transition-all"
          >
            Voir tout <ArrowRight size={16} />
          </Link>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Featured article */}
          <Link
            href={`/blog/${featured.slug}`}
            className="lg:col-span-2 group relative rounded-3xl overflow-hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all"
          >
            <div className="relative h-64 lg:h-80 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700">
              {featured.featured_image && (
                <Image
                  src={buildFileUrl(featured.featured_image)}
                  alt={featured.title}
                  fill
                  className="object-cover group-hover:scale-105 transition-transform duration-500"
                  sizes="(max-width: 1024px) 100vw, 66vw"
                />
              )}
              <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
              {featured.category && (
                <div className="absolute top-4 left-4">
                  <Badge variant="primary">{featured.category}</Badge>
                </div>
              )}
            </div>
            <div className="p-6">
              <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-[#C8102E] transition-colors line-clamp-2">
                {featured.title}
              </h3>
              {featured.excerpt && (
                <p className="text-gray-500 text-sm line-clamp-2 mb-4">{featured.excerpt}</p>
              )}
              <div className="flex items-center gap-3">
                <Avatar src={featured.profiles?.photo_path} prenom={featured.profiles?.prenom} nom={featured.profiles?.nom} size="sm" />
                <div>
                  <p className="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {featured.profiles?.prenom} {featured.profiles?.nom}
                  </p>
                  <div className="flex items-center gap-2 text-xs text-gray-400">
                    {featured.published_at && <span>{formatDateShort(featured.published_at)}</span>}
                    {featured.excerpt && (
                      <>
                        <span>·</span>
                        <span className="flex items-center gap-1">
                          <Clock size={11} />
                          {readingTime(featured.excerpt)} min de lecture
                        </span>
                      </>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </Link>

          {/* Other articles */}
          <div className="flex flex-col gap-4">
            {rest.map(article => (
              <Link
                key={article.id}
                href={`/blog/${article.slug}`}
                className="group flex gap-4 bg-white dark:bg-gray-900 rounded-2xl p-4 border border-gray-100 dark:border-gray-800 hover:border-[#C8102E]/30 hover:shadow-md transition-all"
              >
                <div className="relative w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden bg-gray-200 dark:bg-gray-800">
                  {article.featured_image && (
                    <Image
                      src={buildFileUrl(article.featured_image)}
                      alt={article.title}
                      fill
                      className="object-cover"
                      sizes="80px"
                    />
                  )}
                </div>
                <div className="flex-1 min-w-0">
                  {article.category && (
                    <Badge variant="default" className="text-xs mb-1">{article.category}</Badge>
                  )}
                  <h3 className="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2 group-hover:text-[#C8102E] transition-colors">
                    {article.title}
                  </h3>
                  {article.published_at && (
                    <p className="text-xs text-gray-400 mt-1">{formatDateShort(article.published_at)}</p>
                  )}
                </div>
              </Link>
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
