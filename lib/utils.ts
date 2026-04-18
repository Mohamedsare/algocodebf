import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatDate(date: string | Date, locale = 'fr-FR'): string {
  return new Intl.DateTimeFormat(locale, {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(new Date(date))
}

export function formatDateShort(date: string | Date, locale = 'fr-FR'): string {
  return new Intl.DateTimeFormat(locale, {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}

export function formatRelativeTime(date: string | Date): string {
  const now = new Date()
  const d = new Date(date)
  const diff = now.getTime() - d.getTime()
  const seconds = Math.floor(diff / 1000)
  const minutes = Math.floor(seconds / 60)
  const hours = Math.floor(minutes / 60)
  const days = Math.floor(hours / 24)

  if (seconds < 60) return "À l'instant"
  if (minutes < 60) return `Il y a ${minutes} min`
  if (hours < 24) return `Il y a ${hours}h`
  if (days < 7) return `Il y a ${days} jour${days > 1 ? 's' : ''}`
  return formatDateShort(date)
}

/** Équivalent PHP helpers::timeAgo. */
export function timeAgo(date: string | Date): string {
  return formatRelativeTime(date)
}

export function formatNumber(n: number): string {
  if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`
  if (n >= 1_000) return `${(n / 1_000).toFixed(1)}k`
  return n.toString()
}

export function generateSlug(title: string): string {
  return title
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim()
}

export function truncate(text: string, length: number): string {
  if (text.length <= length) return text
  return text.slice(0, length).trimEnd() + '…'
}

export function readingTime(content: string): number {
  const words = content.trim().split(/\s+/).length
  return Math.max(1, Math.ceil(words / 200))
}

export function getInitials(prenom: string, nom: string): string {
  return `${prenom.charAt(0)}${nom.charAt(0)}`.toUpperCase()
}

export function levelLabel(level: string): string {
  const labels: Record<string, string> = {
    beginner: 'Débutant',
    intermediate: 'Intermédiaire',
    advanced: 'Avancé',
  }
  return labels[level] ?? level
}

export function levelColor(level: string): string {
  const colors: Record<string, string> = {
    beginner: 'text-emerald-600 bg-emerald-50',
    intermediate: 'text-amber-600 bg-amber-50',
    advanced: 'text-red-600 bg-red-50',
  }
  return colors[level] ?? 'text-gray-600 bg-gray-50'
}

export function jobTypeLabel(type: string): string {
  const labels: Record<string, string> = {
    job: 'Emploi',
    internship: 'Stage',
    hackathon: 'Hackathon',
  }
  return labels[type] ?? type
}

export function jobTypeColor(type: string): string {
  const colors: Record<string, string> = {
    job: 'text-blue-700 bg-blue-50',
    internship: 'text-purple-700 bg-purple-50',
    hackathon: 'text-orange-700 bg-orange-50',
  }
  return colors[type] ?? 'text-gray-700 bg-gray-50'
}

/**
 * Résolution d'un path stocké en DB (relatif à un bucket Supabase Storage) en URL publique.
 *
 * Convention du projet :
 *   - bucket unique `uploads` pour tout ce qui est public lisible (avatars, blog, tutorials, forum).
 *     Les paths sont préfixés par le type de ressource : `avatars/…`, `blog/…`, `tutorials/…`.
 *   - bucket séparé `cvs` (privé) pour les CV ; lecture via signed URL côté serveur.
 *
 * Si le path contient déjà http(s), renvoyé tel quel (URL externe).
 */
export function buildStorageUrl(
  bucket: string,
  path: string | null | undefined
): string {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  const clean = path.replace(/^\/+/, '')
  return `${process.env.NEXT_PUBLIC_SUPABASE_URL}/storage/v1/object/public/${bucket}/${clean}`
}

/** URL publique générique pour un fichier stocké dans le bucket `uploads`. */
export function buildFileUrl(path: string | null | undefined): string {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return buildStorageUrl('uploads', path)
}

export function buildAvatarUrl(path: string | null | undefined): string {
  if (!path) return '/images/default-avatar.svg'
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  // Support deux conventions : path déjà préfixé par `avatars/…` ou pas.
  const key = path.startsWith('avatars/') ? path : `avatars/${path}`
  return buildStorageUrl('uploads', key)
}

export function buildCvUrl(path: string | null | undefined): string {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return buildStorageUrl('cvs', path)
}

export function buildBlogImageUrl(path: string | null | undefined): string {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  const key = path.startsWith('blog/') ? path : `blog/${path}`
  return buildStorageUrl('uploads', key)
}

export function buildTutorialFileUrl(path: string | null | undefined): string {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  const key = path.startsWith('tutorials/') ? path : `tutorials/${path}`
  return buildStorageUrl('uploads', key)
}
