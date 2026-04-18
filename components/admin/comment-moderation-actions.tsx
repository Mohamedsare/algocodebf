'use client'

import { useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { setCommentStatusAction } from '@/app/actions/admin'
import { useToast } from '@/components/ui/toast-provider'

interface Props {
  commentId: number
  status: string
}

export function CommentModerationActions({ commentId, status }: Props) {
  const router = useRouter()
  const toast = useToast()
  const [pending, start] = useTransition()

  const run = (next: 'active' | 'deleted') => {
    start(async () => {
      const r = await setCommentStatusAction(commentId, next)
      if (r.ok) router.refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  return (
    <div className="action-buttons">
      {status === 'active' && (
        <button
          type="button"
          className="btn-action btn-delete"
          title="Masquer (supprimer)"
          disabled={pending}
          onClick={() => run('deleted')}
        >
          <i className="fas fa-eye-slash"></i>
        </button>
      )}
      {status === 'deleted' && (
        <button
          type="button"
          className="btn-action btn-success"
          title="Rétablir"
          disabled={pending}
          onClick={() => run('active')}
        >
          <i className="fas fa-undo"></i>
        </button>
      )}
    </div>
  )
}
