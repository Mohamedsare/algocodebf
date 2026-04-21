/**
 * Parse une URL de vidéo externe (YouTube, Vimeo) ou un lien direct vers un fichier
 * (mp4, webm, ogg, mov) pour affichage côté client.
 */

export type VideoEmbedView =
  | { kind: 'youtube'; embedUrl: string; thumbnailUrl: string }
  | { kind: 'vimeo'; embedUrl: string; thumbnailUrl: null }
  | { kind: 'html5'; src: string; thumbnailUrl: null }

const YOUTUBE_ID = /^[a-zA-Z0-9_-]{6,}$/
const VIDEO_EXT = /\.(mp4|webm|ogg|mov)(\?|#|$)/i

/**
 * Renvoie les infos d’intégration, ou `null` si l’URL n’est pas autorisée / reconnue.
 */
export function parseVideoEmbedUrl(urlStr: string): VideoEmbedView | null {
  const raw = urlStr.trim()
  if (!raw) return null

  let u: URL
  try {
    u = new URL(raw)
  } catch {
    return null
  }

  if (u.protocol !== 'https:' && u.protocol !== 'http:') return null
  const host = u.hostname.toLowerCase()

  if (host === 'youtu.be') {
    const id = u.pathname.replace(/^\//, '').split('/')[0] ?? ''
    if (!YOUTUBE_ID.test(id)) return null
    return {
      kind: 'youtube',
      embedUrl: `https://www.youtube-nocookie.com/embed/${id}`,
      thumbnailUrl: `https://i.ytimg.com/vi/${id}/hqdefault.jpg`,
    }
  }

  if (host === 'youtube.com' || host === 'www.youtube.com' || host === 'm.youtube.com' || host === 'www.youtube-nocookie.com') {
    const path = u.pathname
    let id: string | null = new URLSearchParams(u.search).get('v')
    if (path.startsWith('/shorts/')) {
      id = path.split('/')[2] ?? null
    } else if (path.startsWith('/embed/')) {
      id = path.split('/')[2] ?? null
    }
    if (!id || !YOUTUBE_ID.test(id)) return null
    return {
      kind: 'youtube',
      embedUrl: `https://www.youtube-nocookie.com/embed/${id}`,
      thumbnailUrl: `https://i.ytimg.com/vi/${id}/hqdefault.jpg`,
    }
  }

  if (host === 'vimeo.com' || host === 'www.vimeo.com' || host === 'player.vimeo.com') {
    const parts = u.pathname.split('/').filter(Boolean)
    let vid: string | null = null
    if (host === 'player.vimeo.com' && parts[0] === 'video' && /^\d+$/.test(parts[1] ?? '')) {
      vid = parts[1]!
    } else {
      for (const p of parts) {
        if (/^\d{6,}$/.test(p)) {
          vid = p
          break
        }
      }
    }
    if (!vid) return null
    return {
      kind: 'vimeo',
      embedUrl: `https://player.vimeo.com/video/${vid}`,
      thumbnailUrl: null,
    }
  }

  if (VIDEO_EXT.test(u.pathname + u.search) || VIDEO_EXT.test(raw)) {
    return { kind: 'html5', src: u.toString(), thumbnailUrl: null }
  }

  return null
}

/**
 * Même règles que `parseVideoEmbedUrl` : validation serveur avant insert.
 */
export function isAllowedExternalVideoUrl(urlStr: string): boolean {
  return parseVideoEmbedUrl(urlStr) != null
}

/**
 * Aperçu catalogue : miniature YouTube (hqdefault).
 */
export function externalVideoCatalogThumb(url: string | null | undefined): string | null {
  if (!url?.trim()) return null
  const p = parseVideoEmbedUrl(url.trim())
  if (p?.kind === 'youtube') return p.thumbnailUrl
  return null
}
