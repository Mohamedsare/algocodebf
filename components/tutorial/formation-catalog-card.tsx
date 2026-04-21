import Link from 'next/link'
import { FormationCatalogVideoThumb } from '@/components/tutorial/formation-catalog-video-thumb'
import { catalogThumbMedia } from '@/lib/tutorial-thumb'
import { buildAvatarUrl, truncate } from '@/lib/utils'
import { FORMATIONS_PATH } from '@/lib/routes'

export type FormationCatalogAuthor = {
  prenom: string | null
  nom: string | null
  photo_path: string | null
} | null

export type FormationCatalogTutorial = {
  id: number
  title: string
  description: string | null
  type: string
  views: number
  likes_count: number
  thumbnail: string | null
  tutorial_videos: { file_path: string | null; order_index: number | null; external_url: string | null }[] | null
  profiles: FormationCatalogAuthor
}

function stripHtml(s: string) {
  return s.replace(/<[^>]*>/g, '')
}

function tutorialIcon(type: string) {
  switch (type) {
    case 'video':
      return 'fa-video'
    case 'text':
      return 'fa-align-left'
    case 'mixed':
      return 'fa-layer-group'
    case 'pdf':
      return 'fa-file-pdf'
    case 'code':
      return 'fa-code'
    case 'article':
      return 'fa-newspaper'
    default:
      return 'fa-book'
  }
}

function AuthorAvatar({ author, size = 'xs' }: { author: FormationCatalogAuthor; size?: 'xs' | 'sm' }) {
  const p = author?.prenom ?? 'U'
  const sz = size === 'sm' ? 'hm-avatar--sm' : 'hm-avatar--xs'
  const ph = size === 'sm' ? 'hm-avatar-ph--sm' : 'hm-avatar-ph--xs'
  if (author?.photo_path) {
    return (
      <img
        src={buildAvatarUrl(author.photo_path)}
        alt={`${author.prenom ?? ''} ${author.nom ?? ''}`}
        className={`hm-avatar ${sz}`}
      />
    )
  }
  return (
    <div className={`hm-avatar-ph ${ph}`} aria-hidden>
      {p.charAt(0).toUpperCase()}
    </div>
  )
}

export function FormationCatalogCard({ tuto }: { tuto: FormationCatalogTutorial }) {
  const thumb = catalogThumbMedia(tuto.thumbnail, tuto.tutorial_videos)
  const showVideoChrome = tuto.type === 'video' || thumb?.media === 'video'

  return (
    <article className="hm-tuto">
      <div className="hm-tuto-thumb">
        <Link
          href={`${FORMATIONS_PATH}/${tuto.id}`}
          className="hm-tuto-thumb-link"
          aria-label={`Voir la formation : ${tuto.title}`}
        >
          {thumb ? (
            showVideoChrome ? (
              <span className="hm-tuto-thumb-video">
                <i className="fas fa-play-circle" aria-hidden />
                {thumb.media === 'video' ? (
                  <FormationCatalogVideoThumb src={thumb.url} className="hm-tuto-thumb-media" />
                ) : (
                  <img src={thumb.url} alt="" className="hm-tuto-thumb-media" />
                )}
              </span>
            ) : (
              <img src={thumb.url} alt="" className="hm-tuto-thumb-media" />
            )
          ) : (
            <span className="hm-tuto-thumb-placeholder">
              <i className={`fas ${tutorialIcon(tuto.type)}`} aria-hidden />
            </span>
          )}
        </Link>
      </div>
      <div className="hm-tuto-body">
        <h3>
          <Link href={`${FORMATIONS_PATH}/${tuto.id}`}>{tuto.title}</Link>
        </h3>
        <p>{truncate(stripHtml(tuto.description ?? ''), 100)}…</p>
        <div className="hm-tuto-foot">
          <div className="hm-tuto-author">
            <AuthorAvatar author={tuto.profiles} size="sm" />
            <span>
              {tuto.profiles?.prenom ?? ''} {tuto.profiles?.nom ?? ''}
            </span>
          </div>
          <div className="hm-tuto-metrics">
            <span>
              <i className="fas fa-eye" aria-hidden />
              {tuto.views}
            </span>
            <span>
              <i className="fas fa-heart" aria-hidden />
              {tuto.likes_count}
            </span>
          </div>
        </div>
        <Link href={`${FORMATIONS_PATH}/${tuto.id}`} className="hm-btn hm-btn-cta hm-btn-lg">
          <i className="fas fa-arrow-right" />
          Voir la formation
        </Link>
      </div>
    </article>
  )
}
