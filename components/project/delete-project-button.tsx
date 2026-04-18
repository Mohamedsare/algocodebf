'use client'

import { useTransition } from 'react'
import { Button } from '@/components/ui/button'
import { deleteProjectAction } from '@/app/actions/projects'
import { Trash2 } from 'lucide-react'

export function DeleteProjectButton({ projectId }: { projectId: number }) {
  const [pending, startTransition] = useTransition()

  function onDelete() {
    if (!confirm('Archiver ce projet ? Cette action est réversible par un administrateur.')) return
    startTransition(async () => {
      await deleteProjectAction(projectId)
    })
  }

  return (
    <Button variant="danger" onClick={onDelete} loading={pending} size="sm">
      <Trash2 size={14} /> Archiver
    </Button>
  )
}
