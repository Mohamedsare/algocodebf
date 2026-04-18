import Link from 'next/link'
import { buildAvatarUrl } from '@/lib/utils'
import type { MessageWithParties } from '@/lib/queries/messages'
import { DeleteMessageButton } from '@/components/message/delete-message-button'
import { MessageActionPanel } from '@/components/message/message-action-panel'

interface Props {
  message: MessageWithParties
  mode: 'inbox' | 'sent'
  currentUserId: string
}

export function MessageViewPane({ message, mode, currentUserId }: Props) {
  const other = mode === 'inbox' ? message.sender : message.receiver
  const otherName = other ? `${other.prenom} ${other.nom}` : 'Utilisateur'
  const initial = (other?.prenom ?? 'U').charAt(0).toUpperCase()
  const backHref = mode === 'inbox' ? '/message' : '/message/envoyes'

  const replySubject =
    message.subject && message.subject.startsWith('Re:')
      ? message.subject
      : `Re: ${message.subject ?? 'Votre message'}`

  return (
    <div className="message-view" style={{ display: 'flex' }}>
      <div className="message-view-header">
        <Link
          href={backHref}
          className="back-btn-mobile"
          aria-label="Retour à la liste"
        >
          <i className="fas fa-arrow-left"></i>
        </Link>
        <div className="msg-view-sender">
          {other?.photo_path ? (
            <img
              src={buildAvatarUrl(other.photo_path)}
              alt={otherName}
              className="sender-avatar"
            />
          ) : (
            <div
              className="sender-avatar avatar-placeholder-msg"
              style={{ display: 'flex' }}
            >
              {initial}
            </div>
          )}
          <div className="sender-info">
            <h3>{otherName}</h3>
            <span>
              {mode === 'inbox' ? 'De' : 'À'}{' '}
              {other ? (
                <Link href={`/user/${other.id}`}>voir le profil</Link>
              ) : null}
            </span>
          </div>
        </div>
        <div className="msg-view-actions">
          {mode === 'inbox' && other && (
            <Link
              href={`/message/composer?receiver=${other.id}&subject=${encodeURIComponent(replySubject)}&reply_to=${message.id}`}
              className="btn-icon-msg"
              title="Répondre"
            >
              <i className="fas fa-reply"></i>
            </Link>
          )}
          <DeleteMessageButton messageId={message.id} mode={mode} />
        </div>
      </div>

      <div className="message-view-body">
        <div className="msg-view-subject">
          <h2>{message.subject || 'Sans sujet'}</h2>
          <span className="msg-view-date">
            <i className="fas fa-clock"></i>{' '}
            {new Date(message.created_at).toLocaleString('fr-FR')}
          </span>
        </div>
        <div className="msg-view-content" style={{ whiteSpace: 'pre-wrap' }}>
          {message.body}
        </div>

        {message.action_type &&
          mode === 'inbox' &&
          message.receiver_id === currentUserId && (
            <MessageActionPanel
              messageId={message.id}
              actionType={message.action_type}
              actionStatus={message.action_status}
            />
          )}
      </div>
    </div>
  )
}
