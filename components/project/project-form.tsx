'use client'

import { useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { createProjectAction, updateProjectAction } from '@/app/actions/projects'
import type { Project, ProjectStatus, ProjectVisibility } from '@/types'
import { AlertCircle } from 'lucide-react'

interface Props {
  project?: Project
}

const STATUS_OPTIONS: Array<{ value: ProjectStatus; label: string }> = [
  { value: 'planning', label: 'Planification' },
  { value: 'in_progress', label: 'En cours' },
  { value: 'active', label: 'Actif' },
  { value: 'paused', label: 'En pause' },
  { value: 'completed', label: 'Terminé' },
]

export function ProjectForm({ project }: Props) {
  const router = useRouter()
  const [pending, startTransition] = useTransition()
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [error, setError] = useState<string | null>(null)

  function onSubmit(fd: FormData) {
    setError(null)
    setErrors({})
    startTransition(async () => {
      const res = project
        ? await updateProjectAction(project.id, fd)
        : await createProjectAction(fd)
      if (res.ok) {
        const newId = (res.data && typeof res.data === 'object' && 'id' in res.data)
          ? (res.data as { id: number }).id
          : null
        if (newId) router.push(`/project/${newId}`)
        else if (project) router.push(`/project/${project.id}`)
        else router.push('/project')
        router.refresh()
      } else {
        setError(res.message ?? 'Une erreur est survenue.')
        setErrors(res.errors ?? {})
      }
    })
  }

  return (
    <form action={onSubmit} className="space-y-5">
      {error && (
        <div className="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300 flex items-start gap-2">
          <AlertCircle size={16} className="mt-0.5 shrink-0" />
          {error}
        </div>
      )}

      <Input
        name="title"
        label="Titre du projet *"
        defaultValue={project?.title ?? ''}
        error={errors.title}
        placeholder="Nom clair et accrocheur"
        required
      />

      <Textarea
        name="description"
        label="Description détaillée *"
        defaultValue={project?.description ?? ''}
        error={errors.description}
        rows={8}
        placeholder="Objectifs, technologies envisagées, profil recherché…"
        required
      />

      <div className="grid md:grid-cols-2 gap-4">
        <Input
          name="github_link"
          label="Lien GitHub (optionnel)"
          defaultValue={project?.github_link ?? ''}
          error={errors.github_link}
          placeholder="https://github.com/…"
          type="url"
        />
        <Input
          name="demo_link"
          label="Lien de démo (optionnel)"
          defaultValue={project?.demo_link ?? ''}
          error={errors.demo_link}
          placeholder="https://…"
          type="url"
        />
      </div>

      <div className="grid md:grid-cols-2 gap-4">
        <div>
          <label className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 block">
            Statut
          </label>
          <select
            name="status"
            defaultValue={project?.status ?? 'planning'}
            className="h-11 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-sm"
          >
            {STATUS_OPTIONS.map(o => (
              <option key={o.value} value={o.value}>{o.label}</option>
            ))}
          </select>
        </div>
        <div>
          <label className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 block">
            Visibilité
          </label>
          <select
            name="visibility"
            defaultValue={(project?.visibility ?? 'public') as ProjectVisibility}
            className="h-11 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-sm"
          >
            <option value="public">Public</option>
            <option value="private">Privé</option>
          </select>
        </div>
      </div>

      <label className="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-800 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
        <input
          type="checkbox"
          name="looking_for_members"
          defaultChecked={project?.looking_for_members ?? false}
          className="rounded"
        />
        <span className="text-sm text-gray-700 dark:text-gray-300">
          <strong>Je recherche des membres</strong> — le projet apparaîtra dans le filtre « Recrute ».
        </span>
      </label>

      <div className="flex gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
        <Button type="submit" loading={pending} size="lg">
          {project ? 'Enregistrer les modifications' : 'Créer le projet'}
        </Button>
        <Button type="button" variant="ghost" size="lg" onClick={() => router.back()}>
          Annuler
        </Button>
      </div>
    </form>
  )
}
