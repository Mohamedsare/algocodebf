import Link from 'next/link'
import { Eye, MessageSquare, ThumbsUp, ArrowRight } from 'lucide-react'
import { Avatar } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { formatRelativeTime, truncate } from '@/lib/utils'

interface RecentPostsSectionProps {
  posts: {
    id: number
    title: string
    body: string
    category: string | null
    views: number
    created_at: string
    profiles: { prenom: string; nom: string; photo_path: string | null; university: string | null } | null
  }[]
}

export function RecentPostsSection({ posts }: RecentPostsSectionProps) {
  if (!posts.length) return null

  return (
    <section className="bg-gray-50 dark:bg-gray-900/50 py-16">
      <div className="max-w-7xl mx-auto px-4">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h2 className="text-2xl font-black text-gray-900 dark:text-white">Discussions récentes</h2>
            <p className="text-gray-500 text-sm mt-1">Les derniers sujets de la communauté</p>
          </div>
          <Link
            href="/forum"
            className="flex items-center gap-1.5 text-[#C8102E] text-sm font-semibold hover:gap-3 transition-all"
          >
            Voir tout <ArrowRight size={16} />
          </Link>
        </div>

        <div className="space-y-3">
          {posts.map(post => (
            <Link
              key={post.id}
              href={`/forum/${post.id}`}
              className="flex items-start gap-4 bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 hover:border-[#C8102E]/30 hover:shadow-md transition-all group"
            >
              <Avatar
                src={post.profiles?.photo_path}
                prenom={post.profiles?.prenom}
                nom={post.profiles?.nom}
                size="md"
                className="flex-shrink-0"
              />
              <div className="flex-1 min-w-0">
                <div className="flex items-start gap-2 mb-1">
                  {post.category && (
                    <Badge variant="default" className="text-xs flex-shrink-0">{post.category}</Badge>
                  )}
                  <h3 className="font-semibold text-gray-900 dark:text-white text-sm group-hover:text-[#C8102E] transition-colors truncate">
                    {post.title}
                  </h3>
                </div>
                <p className="text-gray-500 dark:text-gray-400 text-sm line-clamp-2">
                  {truncate(post.body.replace(/<[^>]+>/g, ''), 120)}
                </p>
                <div className="flex items-center gap-4 mt-2 text-xs text-gray-400">
                  <span>
                    {post.profiles?.prenom} {post.profiles?.nom}
                    {post.profiles?.university && ` · ${post.profiles.university}`}
                  </span>
                  <span>{formatRelativeTime(post.created_at)}</span>
                  <span className="flex items-center gap-1">
                    <Eye size={12} />
                    {post.views}
                  </span>
                </div>
              </div>
              <ArrowRight size={16} className="text-gray-300 group-hover:text-[#C8102E] flex-shrink-0 mt-1 transition-colors" />
            </Link>
          ))}
        </div>
      </div>
    </section>
  )
}
