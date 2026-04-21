'use client'

import { useState, useTransition, useRef } from 'react'
import { Video, Upload, Trash2, PlayCircle, GripVertical, Link2 } from 'lucide-react'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  addTutorialVideoAction,
  deleteTutorialVideoAction,
} from '@/app/actions/tutorial'
import { buildFileUrl } from '@/lib/utils'

interface VideoRow {
  id: number
  title: string | null
  file_path: string | null
  external_url: string | null
  file_name: string | null
  file_size: number | null
  order_index: number
}

interface TutorialVideoManagerProps {
  tutorialId: number
  initialVideos: VideoRow[]
}

/**
 * Gestion des vidéos d'un tutoriel — upload, lien externe (YouTube, Vimeo, fichier .mp4…), suppression.
 */
export function TutorialVideoManager({ tutorialId, initialVideos }: TutorialVideoManagerProps) {
  const [videos, setVideos] = useState(initialVideos)
  const [pending, startTransition] = useTransition()
  const [uploadName, setUploadName] = useState('')
  const [externalUrl, setExternalUrl] = useState('')
  const [error, setError] = useState<string | null>(null)
  const fileRef = useRef<HTMLInputElement>(null)

  const onFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    setError(null)
    const fd = new FormData()
    fd.append('video', file)
    fd.append('title', uploadName || file.name)
    startTransition(async () => {
      const res = await addTutorialVideoAction(tutorialId, fd)
      if (res.ok && res.data) {
        setVideos(v => [
          ...v,
          {
            id: res.data!.id,
            title: uploadName || file.name,
            file_path: res.data!.path,
            external_url: null,
            file_name: file.name,
            file_size: file.size,
            order_index: v.length,
          },
        ])
        setUploadName('')
        if (fileRef.current) fileRef.current.value = ''
      } else {
        setError(res.message ?? 'Échec de l\'upload.')
      }
    })
  }

  const onAddExternal = () => {
    const u = externalUrl.trim()
    if (!u) {
      setError('Collez d’abord un lien (YouTube, Vimeo ou fichier .mp4 / .webm en https).')
      return
    }
    setError(null)
    const fd = new FormData()
    fd.append('external_url', u)
    fd.append('title', uploadName || 'Vidéo (lien externe)')
    startTransition(async () => {
      const res = await addTutorialVideoAction(tutorialId, fd)
      if (res.ok && res.data) {
        setVideos(v => [
          ...v,
          {
            id: res.data!.id,
            title: uploadName || 'Vidéo (lien externe)',
            file_path: null,
            external_url: res.data!.external_url,
            file_name: 'Lien externe',
            file_size: null,
            order_index: v.length,
          },
        ])
        setExternalUrl('')
        setUploadName('')
      } else {
        setError(res.message ?? 'Échec de l’enregistrement du lien.')
      }
    })
  }

  const onDelete = (id: number) => {
    if (!confirm('Supprimer cette vidéo ?')) return
    startTransition(async () => {
      const res = await deleteTutorialVideoAction(id)
      if (res.ok) {
        setVideos(v => v.filter(x => x.id !== id))
      } else {
        setError(res.message ?? 'Échec de la suppression.')
      }
    })
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center gap-2">
        <Video size={18} className="text-[#C8102E]" />
        <h3 className="font-bold text-gray-900 dark:text-white">Vidéos du parcours</h3>
      </div>

      {error && <p className="text-sm text-red-500 bg-red-50 dark:bg-red-900/20 p-3 rounded-xl">{error}</p>}

      {videos.length === 0 ? (
        <p className="text-sm text-gray-400 italic">Aucune vidéo pour l&apos;instant.</p>
      ) : (
        <ul className="space-y-2">
          {videos.map(v => (
            <li
              key={v.id}
              className="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900"
            >
              <GripVertical size={14} className="text-gray-300 cursor-grab" />
              {v.external_url ? <Link2 size={20} className="text-[#C8102E] shrink-0" /> : <PlayCircle size={20} className="text-[#C8102E] shrink-0" />}
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 dark:text-white truncate">{v.title ?? v.file_name}</p>
                <p className="text-xs text-gray-400">
                  {v.external_url
                    ? 'Lien externe'
                    : v.file_size
                      ? `${Math.round(v.file_size / 1024 / 1024)} MB`
                      : ''}
                </p>
              </div>
              {v.external_url && (
                <a
                  href={v.external_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-xs text-[#C8102E] hover:underline shrink-0"
                >
                  Ouvrir
                </a>
              )}
              {v.file_path && (
                <a
                  href={buildFileUrl(v.file_path)}
                  target="_blank"
                  rel="noopener"
                  className="text-xs text-[#C8102E] hover:underline shrink-0"
                >
                  Aperçu
                </a>
              )}
              <button
                onClick={() => onDelete(v.id)}
                disabled={pending}
                className="p-2 rounded-lg text-gray-400 hover:text-red-500"
                aria-label="Supprimer"
              >
                <Trash2 size={14} />
              </button>
            </li>
          ))}
        </ul>
      )}

      <div className="rounded-xl border border-dashed border-gray-200 dark:border-gray-800 p-4 space-y-3">
        <Input
          label="Titre de la vidéo (optionnel)"
          value={uploadName}
          onChange={e => setUploadName(e.target.value)}
          placeholder="Ex. : Introduction au tri"
        />
        <div className="space-y-2">
          <label className="text-sm font-medium text-gray-700 dark:text-gray-200">Lien externe (optionnel)</label>
          <div className="flex flex-col sm:flex-row gap-2">
            <input
              type="url"
              value={externalUrl}
              onChange={e => setExternalUrl(e.target.value)}
              placeholder="https://www.youtube.com/watch?v=… ou https://vimeo.com/… ou .mp4"
              className="flex-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm"
              disabled={pending}
            />
            <Button type="button" variant="outline" onClick={onAddExternal} disabled={pending || !externalUrl.trim()}>
              <Link2 size={14} /> Ajouter le lien
            </Button>
          </div>
          <p className="text-xs text-gray-400">
            YouTube, Vimeo, ou URL HTTPS directe vers un fichier .mp4, .webm, .ogg ou .mov. Pas d’upload en même temps
            qu’un lien.
          </p>
        </div>
        <div className="pt-1 border-t border-gray-100 dark:border-gray-800" />
        <input
          ref={fileRef}
          type="file"
          accept="video/mp4,video/webm,video/ogg,video/quicktime"
          onChange={onFileChange}
          className="hidden"
        />
        <Button type="button" onClick={() => fileRef.current?.click()} disabled={pending || !!externalUrl.trim()}>
          <Upload size={14} /> {pending ? 'Envoi en cours…' : 'Téléverser un fichier vidéo'}
        </Button>
        <p className="text-xs text-gray-400">MP4, WebM ou MOV • Max 500 MB (désactivez l’autre zone si vous collez un lien)</p>
      </div>
    </div>
  )
}
