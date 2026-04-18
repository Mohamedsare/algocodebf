'use client'

import { useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { respondJoinRequestAction } from '@/app/actions/projects'

interface Props {
  messageId: number
  actionType: string | null
  actionStatus: string | null
}

export function MessageActionPanel({ messageId, actionType, actionStatus }: Props) {
  const router = useRouter()
  const [pending, start] = useTransition()
  const [status, setStatus] = useState<string | null>(actionStatus)

  if (actionType !== 'project_join_request') return null

  if (status === 'accepted') {
    return (
      <div className="message-actions">
        <div className="action-card action-accepted">
          <h4>
            <i className="fas fa-check-circle"></i>
          </h4>
          <p>Cette demande a été acceptée.</p>
        </div>
      </div>
    )
  }

  if (status === 'rejected') {
    return (
      <div className="message-actions">
        <div className="action-card action-rejected">
          <h4>
            <i className="fas fa-times-circle"></i>
          </h4>
          <p>Cette demande a été refusée.</p>
        </div>
      </div>
    )
  }

  const onRespond = (decision: 'accepted' | 'rejected') => {
    const msg =
      decision === 'accepted'
        ? 'Voulez-vous accepter ce membre dans votre projet ?'
        : 'Voulez-vous refuser cette demande ?'
    if (!confirm(msg)) return
    start(async () => {
      const res = await respondJoinRequestAction(messageId, decision)
      if (!res.ok) {
        alert(res.message ?? 'Erreur.')
        return
      }
      setStatus(decision)
      router.refresh()
    })
  }

  return (
    <div className="message-actions" style={{ display: 'block' }}>
      <div className="action-card">
        <h4>
          <i className="fas fa-hand-pointer"></i> Actions requises
        </h4>
        <p>
          L&apos;expéditeur souhaite rejoindre votre projet. Voulez-vous
          accepter cette demande ?
        </p>
        <div className="action-buttons">
          <button
            type="button"
            className="btn-action-accept"
            disabled={pending}
            onClick={() => onRespond('accepted')}
          >
            <i className="fas fa-check"></i> Accepter
          </button>
          <button
            type="button"
            className="btn-action-reject"
            disabled={pending}
            onClick={() => onRespond('rejected')}
          >
            <i className="fas fa-times"></i> Refuser
          </button>
        </div>
      </div>
    </div>
  )
}
