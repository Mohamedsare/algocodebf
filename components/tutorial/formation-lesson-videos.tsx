import { FormationLessonVideoRows } from '@/components/tutorial/formation-lesson-video-rows'

type VideoRow = {
  id: number
  title: string | null
  description: string | null
  file_path: string | null
  external_url: string | null
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
      <FormationLessonVideoRows videos={videos} />
    </div>
  )
}
