import type { SupabaseClient } from '@supabase/supabase-js'

/**
 * Helpers centralisés pour les uploads vers Supabase Storage.
 *
 * Conventions de nommage (mirroring du PHP `public/uploads/`) :
 *   bucket `uploads` (public)   → avatars/{uid}/..., blog/..., tutorials/{id}/..., forum/...
 *   bucket `cvs`     (privé)    → {uid}/{file}.pdf    (signed URL requis)
 *
 * `validateUpload()` applique les mêmes contraintes que `FileValidator.php` côté PHP :
 *   - avatar : image ≤ 5 MB, types image/*
 *   - cv     : pdf ≤ 2 MB
 *   - video  : vidéo ≤ 500 MB, types vidéo
 *   - image  : image ≤ 5 MB
 */

export const BUCKETS = {
  public: 'uploads' as const,
  avatars: 'uploads' as const,
  blog: 'uploads' as const,
  tutorials: 'uploads' as const,
  forum: 'uploads' as const,
  cvs: 'cvs' as const,
}

export type UploadKind = 'avatar' | 'cv' | 'video' | 'image' | 'attachment'

const LIMITS: Record<UploadKind, { maxBytes: number; mimes: RegExp[] }> = {
  avatar: { maxBytes: 5 * 1024 * 1024, mimes: [/^image\/(jpeg|jpg|png|webp|gif)$/] },
  cv: { maxBytes: 2 * 1024 * 1024, mimes: [/^application\/pdf$/] },
  video: { maxBytes: 500 * 1024 * 1024, mimes: [/^video\/(mp4|webm|ogg|quicktime)$/] },
  image: { maxBytes: 5 * 1024 * 1024, mimes: [/^image\/(jpeg|jpg|png|webp|gif)$/] },
  attachment: {
    maxBytes: 10 * 1024 * 1024,
    mimes: [
      /^image\/(jpeg|jpg|png|webp|gif)$/,
      /^application\/pdf$/,
      /^application\/(msword|vnd\.openxmlformats-officedocument\.wordprocessingml\.document)$/,
      /^text\/plain$/,
    ],
  },
}

export function validateUpload(file: File, kind: UploadKind): void {
  const rule = LIMITS[kind]
  if (file.size > rule.maxBytes) {
    throw new Error(`Fichier trop volumineux (max ${Math.round(rule.maxBytes / 1024 / 1024)} MB).`)
  }
  if (!rule.mimes.some(r => r.test(file.type))) {
    throw new Error(`Type de fichier non autorisé (${file.type}).`)
  }
}

/**
 * Génère une clé unique type `{prefix}/{timestamp}-{random}.{ext}`.
 * - `folder` ex: `avatars/{uid}`, `tutorials/{id}`, `blog`.
 */
export function buildObjectKey(folder: string, filename: string): string {
  const ext = filename.split('.').pop()?.toLowerCase() ?? 'bin'
  const rand = Math.random().toString(36).slice(2, 10)
  return `${folder.replace(/^\/+|\/+$/g, '')}/${Date.now()}-${rand}.${ext}`
}

type UploadFolderSpec =
  | { kind: 'avatar'; userId: string }
  | { kind: 'cv'; userId: string }
  | { kind: 'blog' }
  | { kind: 'tutorial'; tutorialId: number | string }
  | { kind: 'forum'; postId: number | string }
  | { kind: 'custom'; folder: string }

function resolveFolder(spec: UploadFolderSpec): string {
  switch (spec.kind) {
    case 'avatar':
      return `avatars/${spec.userId}`
    case 'cv':
      return spec.userId
    case 'blog':
      return 'blog'
    case 'tutorial':
      return `tutorials/${spec.tutorialId}`
    case 'forum':
      return `forum/${spec.postId}`
    case 'custom':
      return spec.folder
  }
}

function resolveBucket(spec: UploadFolderSpec): string {
  return spec.kind === 'cv' ? BUCKETS.cvs : BUCKETS.public
}

/**
 * Simple upload helper (server-side). Accepte un File (Web API) venant d'un FormData.
 * Retourne la clé stockée dans le bucket ; persister cette valeur en DB.
 */
export async function uploadFile(
  supabase: SupabaseClient,
  bucket: string,
  file: File,
  userIdOrFolder: string,
  kind: UploadKind = 'image'
): Promise<{ path: string }> {
  const spec: UploadFolderSpec =
    kind === 'avatar' ? { kind: 'avatar', userId: userIdOrFolder }
    : kind === 'cv' ? { kind: 'cv', userId: userIdOrFolder }
    : { kind: 'custom', folder: userIdOrFolder }

  const folder = resolveFolder(spec)
  const key = buildObjectKey(folder, file.name)
  const arrayBuffer = await file.arrayBuffer()
  const { error } = await supabase.storage.from(bucket).upload(key, arrayBuffer, {
    contentType: file.type,
    upsert: false,
  })
  if (error) throw new Error(error.message)
  return { path: key }
}

export async function uploadWithSpec(
  supabase: SupabaseClient,
  file: File,
  spec: UploadFolderSpec,
  kind: UploadKind
): Promise<{ bucket: string; path: string }> {
  validateUpload(file, kind)
  const bucket = resolveBucket(spec)
  const folder = resolveFolder(spec)
  const key = buildObjectKey(folder, file.name)
  const arrayBuffer = await file.arrayBuffer()
  const { error } = await supabase.storage.from(bucket).upload(key, arrayBuffer, {
    contentType: file.type,
    upsert: false,
  })
  if (error) throw new Error(error.message)
  return { bucket, path: key }
}

export async function deleteFile(
  supabase: SupabaseClient,
  bucket: string,
  path: string
): Promise<void> {
  if (!path) return
  const { error } = await supabase.storage.from(bucket).remove([path])
  if (error) throw new Error(error.message)
}

export async function createSignedUrl(
  supabase: SupabaseClient,
  bucket: string,
  path: string,
  expiresInSeconds = 3600
): Promise<string> {
  const { data, error } = await supabase.storage.from(bucket).createSignedUrl(path, expiresInSeconds)
  if (error) throw new Error(error.message)
  return data.signedUrl
}
