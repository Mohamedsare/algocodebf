'use client'

import { useState, useTransition, useRef } from 'react'
import { Video, Upload, Trash2, PlayCircle, GripVertical } from 'lucide-react'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  addTutorialVideoAction,
  deleteTutorialVideoAction,
} from '@/app/actions/tutorial'
import { buildFileUrl } from '@/lib/utils'

interface Video {
  id: number
  title: string | null
  file_path: string | null
  file_name: string | null
  file_size: number | null
  order_index: number
}

interface TutorialVideoManagerProps {
  tutorialId: number
  initialVideos: Video[]
}

/**
 * Gestion simple des vidéos d'un tutoriel — add / delete / reorder.
 * Le réordonnement visuel est possible mais l'ordre est fixé par order_index
 * (changer l'ordre requiert un SQL update — non implémenté ici, drag & drop
 * simplifié : up/down buttons possibles via future iteration).
 */
export function TutorialVideoManager({ tutorialId, initialVideos }: TutorialVideoManagerProps) {
  const [videos, setVideos] = useState(initialVideos)
  const [pending, startTransition] = useTransition()
  const [uploadName, setUploadName] = useState('')
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

      {/* Liste */}
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
              <PlayCircle size={20} className="text-[#C8102E]" />
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 dark:text-white truncate">{v.title ?? v.file_name}</p>
                <p className="text-xs text-gray-400">
                  {v.file_size ? `${Math.round(v.file_size / 1024 / 1024)} MB` : ''}
                </p>
              </div>
              {v.file_path && (
                <a
                  href={buildFileUrl(v.file_path)}
                  target="_blank"
                  rel="noopener"
                  className="text-xs text-[#C8102E] hover:underline"
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

      {/* Upload */}
      <div className="rounded-xl border border-dashed border-gray-200 dark:border-gray-800 p-4 space-y-3">
        <Input
          label="Titre de la vidéo (optionnel)"
          value={uploadName}
          onChange={e => setUploadName(e.target.value)}
          placeholder="Ex : Introduction au tri"
        />
        <input
          ref={fileRef}
          type="file"
          accept="video/mp4,video/webm,video/ogg,video/quicktime"
          onChange={onFileChange}
          className="hidden"
        />
        <Button type="button" onClick={() => fileRef.current?.click()} disabled={pending}>
          <Upload size={14} /> {pending ? 'Upload en cours…' : 'Ajouter une vidéo'}
        </Button>
        <p className="text-xs text-gray-400">MP4, WebM ou MOV • Max 500 MB</p>
      </div>
    </div>
  )
}
