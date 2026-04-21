import { buildFileUrl, formatNumber } from '@/lib/utils'

type VideoRow = {
  id: number
  title: string | null
  description: string | null
  file_path: string | null
  file_size: number | null
  views: number | null
}

export function FormationLessonVideos({
  videos,
  title,
  hint,
}: {
  videos: VideoRow[]
  title: string
  hint: string
}) {
  return (
    <div className="tutorial-videos-section" id="ft-videos">
      <h3 className="videos-section-title">
        <i className="fas fa-video" aria-hidden /> {title}
        <span className="ft-section-count">{videos.length}</span>
      </h3>
      <p className="ft-section-hint">{hint}</p>
      <div className="videos-list-container">
        {videos.map((v, idx) => (
          <div key={v.id} id={`video-${v.id}`} className="video-item-card ft-lesson-card">
            <div className="video-header">
              <div className="video-number">{idx + 1}</div>
              <div className="video-info">
                <h4 className="video-title">{v.title}</h4>
                <div className="video-meta-info">
                  {v.description && <p className="video-description">{v.description}</p>}
                  <div className="video-stats">
                    <span>
                      <i className="fas fa-eye"></i> {formatNumber(v.views ?? 0)} vues
                    </span>
                    {v.file_size != null && (
                      <span>
                        <i className="fas fa-file"></i> {(v.file_size / 1024 / 1024).toFixed(2)} MB
                      </span>
                    )}
                  </div>
                </div>
              </div>
            </div>
            <div className="video-player-container">
              <video controls className="tutorial-video-player" preload="metadata">
                <source src={buildFileUrl(v.file_path)} type="video/mp4" />
                Votre navigateur ne supporte pas la lecture vidéo.
              </video>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
