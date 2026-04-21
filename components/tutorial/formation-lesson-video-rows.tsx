'use client'

import { useCallback, useRef } from 'react'
import { useRouter } from 'next/navigation'
import { buildFileUrl, formatNumber } from '@/lib/utils'
import { parseVideoEmbedUrl } from '@/lib/video-embed'
import { incrementTutorialVideoView } from '@/app/actions/forum'

type VideoRow = {
  id: number
  title: string | null
  description: string | null
  file_path: string | null
  external_url: string | null
  file_size: number | null
  views: number | null
}

function useCountViewOnce(videoId: number) {
  const router = useRouter()
  const done = useRef(false)
  return useCallback(() => {
    if (done.current) return
    if (typeof window !== 'undefined') {
      const k = `bf_tutorial_video_view_${videoId}`
      if (sessionStorage.getItem(k)) return
      sessionStorage.setItem(k, '1')
    }
    done.current = true
    void (async () => {
      await incrementTutorialVideoView(videoId)
      router.refresh()
    })()
  }, [videoId, router])
}

function LessonPlayerTracked({ v }: { v: VideoRow }) {
  const title = v.title ?? 'Vidéo'
  const onCounted = useCountViewOnce(v.id)
  const fromUrl = v.external_url?.trim()

  if (fromUrl) {
    const embed = parseVideoEmbedUrl(fromUrl)
    if (embed?.kind === 'youtube' || embed?.kind === 'vimeo') {
      return (
        <div className="ft-video-embed" title={title}>
          <iframe
            src={embed.embedUrl}
            title={title}
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowFullScreen
            onLoad={onCounted}
          />
        </div>
      )
    }
    if (embed?.kind === 'html5') {
      return (
        <video
          controls
          className="tutorial-video-player"
          preload="metadata"
          onPlay={onCounted}
        >
          <source src={embed.src} />
          Votre navigateur ne supporte pas la lecture vidéo.
        </video>
      )
    }
  }

  if (v.file_path) {
    return (
      <video controls className="tutorial-video-player" preload="metadata" onPlay={onCounted}>
        <source src={buildFileUrl(v.file_path)} type="video/mp4" />
        Votre navigateur ne supporte pas la lecture vidéo.
      </video>
    )
  }

  return null
}

export function FormationLessonVideoRows({
  videos,
}: {
  videos: VideoRow[]
}) {
  return (
    <div className="videos-list-container">
      {videos.map((v, idx) => (
        <div key={v.id} id={`video-${v.id}`} className="video-item-card ft-lesson-card">
          <div className="video-header">
            <div className="video-number">{idx + 1}</div>
            <div className="video-info">
              <h4 className="video-title">{v.title ?? `Leçon ${idx + 1}`}</h4>
              <div className="video-meta-info">
                {v.description && <p className="video-description">{v.description}</p>}
                <div className="video-stats">
                  <span>
                    <i className="fas fa-eye"></i> {formatNumber(v.views ?? 0)} vues
                  </span>
                  {v.file_size != null && v.file_size > 0 && (
                    <span>
                      <i className="fas fa-file"></i> {(v.file_size / 1024 / 1024).toFixed(2)} MB
                    </span>
                  )}
                  {v.external_url?.trim() && (
                    <a
                      className="ft-external-lesson-link"
                      href={v.external_url}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <i className="fas fa-external-link-alt" aria-hidden /> Ouvrir le lien
                    </a>
                  )}
                </div>
              </div>
            </div>
          </div>
          <div className="video-player-container">
            <LessonPlayerTracked v={v} />
          </div>
        </div>
      ))}
    </div>
  )
}
