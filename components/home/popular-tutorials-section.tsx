import Link from 'next/link'
import Image from 'next/image'
import { Play, Eye, ThumbsUp, ArrowRight } from 'lucide-react'
import { Avatar } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { formatNumber, formatRelativeTime, levelLabel, levelColor, buildFileUrl } from '@/lib/utils'
import { FORMATIONS_PATH } from '@/lib/routes'

interface PopularTutorialsSectionProps {
  tutorials: {
    id: number
    title: string
    description: string | null
    type: string
    level: string
    category: string | null
    views: number
    likes_count: number
    thumbnail: string | null
    created_at: string
    profiles: { prenom: string; nom: string; photo_path: string | null } | null
  }[]
}

export function PopularTutorialsSection({ tutorials }: PopularTutorialsSectionProps) {
  if (!tutorials.length) return null

  return (
    <section className="bg-white dark:bg-gray-950 py-16">
      <div className="max-w-7xl mx-auto px-4">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h2 className="text-2xl font-black text-gray-900 dark:text-white">Formations populaires</h2>
            <p className="text-gray-500 text-sm mt-1">Les parcours les plus consultés</p>
          </div>
          <Link
            href={FORMATIONS_PATH}
            className="flex items-center gap-1.5 text-[#006A4E] text-sm font-semibold hover:gap-3 transition-all"
          >
            Voir tout <ArrowRight size={16} />
          </Link>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5">
          {tutorials.map(tuto => (
            <Link
              key={tuto.id}
              href={`${FORMATIONS_PATH}/${tuto.id}`}
              className="group bg-gray-50 dark:bg-gray-900 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-800 hover:border-[#006A4E]/30 hover:shadow-lg transition-all card-hover"
            >
              {/* Thumbnail */}
              <div className="relative aspect-video bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700">
                {tuto.thumbnail ? (
                  <Image
                    src={buildFileUrl(tuto.thumbnail)}
                    alt={tuto.title}
                    fill
                    className="object-cover"
                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 20vw"
                  />
                ) : (
                  <div className="absolute inset-0 flex items-center justify-center">
                    <Play size={32} className="text-gray-400" />
                  </div>
                )}
                {tuto.type === 'video' && (
                  <div className="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity">
                    <div className="w-12 h-12 rounded-full bg-[#C8102E]/90 flex items-center justify-center">
                      <Play size={20} className="text-white ml-1" />
                    </div>
                  </div>
                )}
                <div className="absolute top-2 left-2">
                  <span className={`text-xs font-semibold px-2 py-0.5 rounded-full ${levelColor(tuto.level)}`}>
                    {levelLabel(tuto.level)}
                  </span>
                </div>
              </div>

              {/* Info */}
              <div className="p-3">
                <div className="flex items-center gap-2 mb-1.5">
                  <Avatar src={tuto.profiles?.photo_path} prenom={tuto.profiles?.prenom} nom={tuto.profiles?.nom} size="xs" />
                  <span className="text-xs text-gray-500 truncate">
                    {tuto.profiles?.prenom} {tuto.profiles?.nom}
                  </span>
                </div>
                <h3 className="font-semibold text-gray-900 dark:text-white text-sm leading-snug line-clamp-2 mb-2 group-hover:text-[#006A4E] transition-colors">
                  {tuto.title}
                </h3>
                <div className="flex items-center gap-3 text-xs text-gray-400">
                  <span className="flex items-center gap-1">
                    <Eye size={11} />
                    {formatNumber(tuto.views)}
                  </span>
                  <span className="flex items-center gap-1">
                    <ThumbsUp size={11} />
                    {formatNumber(tuto.likes_count)}
                  </span>
                  <span>{formatRelativeTime(tuto.created_at)}</span>
                </div>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  )
}
