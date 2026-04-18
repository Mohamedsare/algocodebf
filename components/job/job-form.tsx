'use client'

import { useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { createJobAction, updateJobAction } from '@/app/actions/jobs'
import type { Job } from '@/types'
import { AlertCircle } from 'lucide-react'

interface Props { job?: Job }

export function JobForm({ job }: Props) {
  const router = useRouter()
  const [pending, startTransition] = useTransition()
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [error, setError] = useState<string | null>(null)

  function onSubmit(fd: FormData) {
    setError(null); setErrors({})
    startTransition(async () => {
      const res = job ? await updateJobAction(job.id, fd) : await createJobAction(fd)
      if (res.ok) {
        const newId = (res.data && typeof res.data === 'object' && 'id' in res.data)
          ? (res.data as { id: number }).id
          : null
        router.push(`/job/${newId ?? job?.id ?? ''}`)
        router.refresh()
      } else {
        setError(res.message ?? 'Erreur')
        setErrors(res.errors ?? {})
      }
    })
  }

  return (
    <form action={onSubmit} className="space-y-5">
      {error && (
        <div className="rounded-xl bg-red-50 border border-red-200 p-3 text-sm text-red-700 flex items-start gap-2">
          <AlertCircle size={16} /> {error}
        </div>
      )}

      <div className="grid md:grid-cols-2 gap-4">
        <div>
          <label className="text-sm font-medium mb-1.5 block">Type d&apos;offre *</label>
          <select
            name="type"
            defaultValue={job?.type ?? 'job'}
            className="h-11 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-sm"
          >
            <option value="job">Emploi</option>
            <option value="internship">Stage</option>
            <option value="hackathon">Hackathon</option>
          </select>
        </div>
        <Input
          name="city"
          label="Ville *"
          defaultValue={job?.city ?? ''}
          error={errors.city}
          placeholder="Ouagadougou, Bobo-Dioulasso…"
          required
        />
      </div>

      <Input
        name="title"
        label="Titre de l'offre *"
        defaultValue={job?.title ?? ''}
        error={errors.title}
        placeholder="Ex : Développeur Full-Stack Junior"
        required
      />
      <Input
        name="company_name"
        label="Nom de l'entreprise (optionnel)"
        defaultValue={job?.company_name ?? ''}
        placeholder="Par défaut : votre nom"
      />

      <Textarea
        name="description"
        label="Description du poste *"
        defaultValue={job?.description ?? ''}
        error={errors.description}
        rows={10}
        placeholder="Missions, profil recherché, compétences requises, conditions…"
        required
      />

      <div className="grid md:grid-cols-2 gap-4">
        <Input name="salary" label="Salaire (optionnel)" defaultValue={job?.salary ?? ''} placeholder="200 000 - 400 000 FCFA" />
        <Input
          name="deadline"
          type="date"
          label="Date limite (optionnel)"
          defaultValue={job?.deadline ?? ''}
        />
      </div>

      <Input
        name="external_link"
        label="Lien externe (optionnel)"
        type="url"
        defaultValue={job?.external_link ?? ''}
        error={errors.external_link}
        placeholder="https://entreprise.com/offre"
        helper="Si renseigné, les candidats seront redirigés vers ce lien."
      />

      <div className="flex gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
        <Button type="submit" size="lg" loading={pending}>
          {job ? 'Enregistrer' : 'Publier l\'offre'}
        </Button>
        <Button type="button" variant="ghost" size="lg" onClick={() => router.back()}>
          Annuler
        </Button>
      </div>
    </form>
  )
}
