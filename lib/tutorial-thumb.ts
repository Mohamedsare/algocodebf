import { buildFileUrl } from '@/lib/utils'
import { externalVideoCatalogThumb } from '@/lib/video-embed'

const IMAGE_EXT = new Set(['jpg', 'jpeg', 'png', 'gif', 'webp'])
const VIDEO_EXT = new Set(['mp4', 'webm', 'ogg', 'mov'])

function fileKindFromPath(path: string | null | undefined): 'image' | 'video' | null {
  if (!path?.trim()) return null
  const ext = path.split('.').pop()?.toLowerCase() ?? ''
  if (IMAGE_EXT.has(ext)) return 'image'
  if (VIDEO_EXT.has(ext)) return 'video'
  return null
}

/** Miniature formation : `thumbnail`, sinon 1re entrée `tutorial_videos` (image, vidéo hébergée, ou aperçu YouTube). */
export function catalogThumbMedia(
  thumbnail: string | null | undefined,
  videos: { file_path: string | null; order_index: number | null; external_url?: string | null }[] | null | undefined
): { url: string; media: 'image' | 'video' } | null {
  const thumbUrl = thumbnail ? buildFileUrl(thumbnail) : ''
  if (thumbUrl) return { url: thumbUrl, media: 'image' }

  const list = Array.isArray(videos) ? videos : []
  const sorted = [...list].sort(
    (a, b) => (a.order_index ?? 0) - (b.order_index ?? 0)
  )
  for (const v of sorted) {
    const ext = v.external_url?.trim()
    if (ext) {
      const yt = externalVideoCatalogThumb(ext)
      if (yt) return { url: yt, media: 'image' }
    }
    const fp = v.file_path
    const kind = fileKindFromPath(fp)
    if (!kind) continue
    const url = buildFileUrl(fp)
    if (url) return { url, media: kind }
  }
  return null
}
