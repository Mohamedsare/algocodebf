'use client'

import { useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { deleteMessageAction } from '@/app/actions/messages'

interface Props {
  messageId: number
  mode: 'inbox' | 'sent'
}

export function DeleteMessageButton({ messageId, mode }: Props) {
  const router = useRouter()
  const [pending, start] = useTransition()

  const onClick = () => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) return
    start(async () => {
      const res = await deleteMessageAction(messageId)
      if (!res.ok) {
        alert(res.message ?? 'Erreur lors de la suppression.')
        return
      }
      router.push(mode === 'inbox' ? '/message' : '/message/envoyes')
      router.refresh()
    })
  }

  return (
    <button
      type="button"
      className="btn-icon-msg"
      title="Supprimer"
      onClick={onClick}
      disabled={pending}
    >
      <i className="fas fa-trash"></i>
    </button>
  )
}
