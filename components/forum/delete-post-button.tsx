'use client'

import { useTransition } from 'react'
import { deletePostAction } from '@/app/actions/forum'

interface DeletePostButtonProps {
  postId: number
  className?: string
  label?: string
}

export function DeletePostButton({ postId, className, label = 'Supprimer' }: DeletePostButtonProps) {
  const [pending, startTransition] = useTransition()

  const handleClick = () => {
    if (!confirm('Supprimer définitivement cette discussion ?')) return
    startTransition(async () => {
      await deletePostAction(postId)
    })
  }

  return (
    <button
      type="button"
      onClick={handleClick}
      disabled={pending}
      className={className ?? 'btn-action'}
      title={label}
    >
      <i className="fas fa-trash"></i>
    </button>
  )
}
