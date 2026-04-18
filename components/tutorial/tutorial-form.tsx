'use client'

import { useState, useTransition, useRef } from 'react'
import Image from 'next/image'
import { useRouter } from 'next/navigation'
import { Save, Upload, X } from 'lucide-react'
import { Input } from '@/components/ui/input'
import { Select } from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { MarkdownEditor } from '@/components/shared/markdown-editor'
import { createTutorialAction, updateTutorialAction } from '@/app/actions/tutorial'
import { FORMATIONS_PATH } from '@/lib/routes'
import { buildFileUrl } from '@/lib/utils'

interface TutorialFormProps {
  mode: 'create' | 'edit'
  tutorialId?: number
  categoryOptions: Array<{ value: string; label: string }>
  initial?: {
    title: string
    description: string
    content: string
    category: string
    type: 'video' | 'text' | 'mixed'
    level: 'beginner' | 'intermediate' | 'advanced'
    thumbnail?: string | null
    tags?: string
  }
}

export function TutorialForm({ mode, tutorialId, categoryOptions, initial }: TutorialFormProps) {
  const router = useRouter()
  const [pending, startTransition] = useTransition()
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [globalError, setGlobalError] = useState<string | null>(null)
  const thumbRef = useRef<HTMLInputElement>(null)
  const [preview, setPreview] = useState<string | null>(
    initial?.thumbnail ? buildFileUrl(initial.thumbnail) : null
  )
  const [content, setContent] = useState(initial?.content ?? '')
  const [form, setForm] = useState({
    title: initial?.title ?? '',
    description: initial?.description ?? '',
    category: initial?.category ?? '',
    type: (initial?.type ?? 'video') as 'video' | 'text' | 'mixed',
    level: (initial?.level ?? 'beginner') as 'beginner' | 'intermediate' | 'advanced',
    tags: initial?.tags ?? '',
  })

  const handleThumb = (e: React.ChangeEvent<HTMLInputElement>) => {
    const f = e.target.files?.[0]
    if (!f) return
    const r = new FileReader()
    r.onload = () => setPreview(r.result as string)
    r.readAsDataURL(f)
  }

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setErrors({})
    setGlobalError(null)
    const fd = new FormData(e.currentTarget)
    fd.set('content', content)

    startTransition(async () => {
      const res =
        mode === 'create'
          ? await createTutorialAction(fd)
          : await updateTutorialAction(tutorialId as number, fd)
      if (res.ok) {
        const id = (res as { data?: { id: number } }).data?.id ?? tutorialId
        router.push(id ? `${FORMATIONS_PATH}/${id}/modifier` : FORMATIONS_PATH)
        router.refresh()
      } else {
        setErrors(res.errors ?? {})
        setGlobalError(res.message ?? 'Une erreur est survenue.')
      }
    })
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-5">
      {globalError && (
        <div className="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">{globalError}</div>
      )}

      <Input
        label="Titre"
        name="title"
        value={form.title}
        onChange={e => setForm(p => ({ ...p, title: e.target.value }))}
        error={errors.title}
        required
      />

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Select
          label="Catégorie"
          name="category"
          options={categoryOptions}
          value={form.category}
          onChange={e => setForm(p => ({ ...p, category: e.target.value }))}
          placeholder="Choisir"
          error={errors.category}
          required
        />
        <Select
          label="Type"
          name="type"
          value={form.type}
          onChange={e => setForm(p => ({ ...p, type: e.target.value as 'video' | 'text' | 'mixed' }))}
          options={[
            { value: 'video', label: 'Vidéo' },
            { value: 'text', label: 'Texte' },
            { value: 'mixed', label: 'Mixte' },
          ]}
        />
        <Select
          label="Niveau"
          name="level"
          value={form.level}
          onChange={e => setForm(p => ({ ...p, level: e.target.value as 'beginner' | 'intermediate' | 'advanced' }))}
          options={[
            { value: 'beginner', label: 'Débutant' },
            { value: 'intermediate', label: 'Intermédiaire' },
            { value: 'advanced', label: 'Avancé' },
          ]}
        />
      </div>

      <div className="flex flex-col gap-1.5">
        <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
          Description <span className="text-red-500">*</span>
        </label>
        <textarea
          name="description"
          value={form.description}
          onChange={e => setForm(p => ({ ...p, description: e.target.value }))}
          rows={4}
          maxLength={1000}
          className="w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-3 text-sm"
        />
        {errors.description && <p className="text-xs text-red-500">{errors.description}</p>}
      </div>

      <div>
        <label className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Miniature</label>
        {preview && (
          <div className="relative w-64 h-36 mb-2 rounded-xl overflow-hidden">
            <Image src={preview} alt="aperçu" fill className="object-cover" />
            <button
              type="button"
              onClick={() => {
                setPreview(null)
                if (thumbRef.current) thumbRef.current.value = ''
              }}
              className="absolute top-2 right-2 bg-white/90 rounded-full p-1.5"
            >
              <X size={14} />
            </button>
          </div>
        )}
        <input
          ref={thumbRef}
          type="file"
          name="thumbnail"
          accept="image/jpeg,image/png,image/webp"
          onChange={handleThumb}
          className="hidden"
        />
        <Button type="button" variant="outline" size="sm" onClick={() => thumbRef.current?.click()}>
          <Upload size={14} /> {preview ? 'Changer' : 'Ajouter'} une image
        </Button>
      </div>

      <div>
        <label className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
          Contenu (optionnel)
        </label>
        <MarkdownEditor name="content" value={content} onChange={setContent} rows={14} />
      </div>

      <Input
        label="Tags (séparés par virgule)"
        name="tags"
        value={form.tags}
        onChange={e => setForm(p => ({ ...p, tags: e.target.value }))}
        placeholder="python, algo, tri"
      />

      <div className="flex gap-3 justify-end pt-3 border-t border-gray-100 dark:border-gray-800">
        <Button type="button" variant="outline" onClick={() => router.back()}>
          Annuler
        </Button>
        <Button type="submit" loading={pending}>
          <Save size={16} />
          {mode === 'create' ? 'Publier la formation' : 'Enregistrer'}
        </Button>
      </div>
    </form>
  )
}
