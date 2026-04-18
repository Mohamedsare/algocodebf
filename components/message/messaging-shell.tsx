import Link from 'next/link'
import { buildAvatarUrl, timeAgo } from '@/lib/utils'
import type { MessageWithParties } from '@/lib/queries/messages'

interface Props {
  mode: 'inbox' | 'sent'
  messages: MessageWithParties[]
  unreadCount: number
  currentUserId: string
  selectedMessage?: MessageWithParties | null
  children?: React.ReactNode
}

export function MessagingShell({
  mode,
  messages,
  unreadCount,
  selectedMessage,
  children,
}: Props) {
  const basePath = mode === 'inbox' ? '/message' : '/message/envoyes'

  return (
    <section className="messaging-section">
      <div className="container-fluid">
        <div className="messaging-wrapper">
          <aside
            className={`messages-sidebar${selectedMessage ? ' mobile-hide' : ''}`}
          >
            <div className="sidebar-header-msg">
              <h2>
                <i className="fas fa-envelope"></i> Messagerie
              </h2>
              <Link href="/message/composer" className="btn-new-msg">
                <i className="fas fa-plus"></i>
              </Link>
            </div>

            <div className="msg-tabs">
              <Link
                href="/message"
                className={`msg-tab${mode === 'inbox' ? ' active' : ''}`}
              >
                <i className="fas fa-inbox"></i> Reçus
                {unreadCount > 0 && mode !== 'inbox' && (
                  <span className="badge-count">{unreadCount}</span>
                )}
                {unreadCount > 0 && mode === 'inbox' && (
                  <span className="badge-count">{unreadCount}</span>
                )}
              </Link>
              <Link
                href="/message/envoyes"
                className={`msg-tab${mode === 'sent' ? ' active' : ''}`}
              >
                <i className="fas fa-paper-plane"></i> Envoyés
              </Link>
            </div>

            <div className="messages-list">
              {messages.length === 0 ? (
                <div className="no-messages">
                  <i className="fas fa-inbox"></i>
                  <h4>Aucun message</h4>
                  <p>
                    {mode === 'inbox'
                      ? 'Votre boîte de réception est vide'
                      : 'Vous n’avez envoyé aucun message'}
                  </p>
                  <Link href="/message/composer" className="btn-compose-msg">
                    <i className="fas fa-pen"></i> Nouveau message
                  </Link>
                </div>
              ) : (
                messages.map(msg => {
                  const other =
                    mode === 'inbox' ? msg.sender : msg.receiver
                  const otherName = other
                    ? `${other.prenom} ${other.nom}`
                    : 'Utilisateur'
                  const initial = (other?.prenom ?? 'U')
                    .charAt(0)
                    .toUpperCase()
                  const unread = mode === 'inbox' && !msg.is_read
                  const isActive = selectedMessage?.id === msg.id
                  return (
                    <Link
                      key={msg.id}
                      href={`${basePath}?show=${msg.id}`}
                      scroll={false}
                      className={`message-item${unread ? ' unread' : ''}${isActive ? ' active' : ''}`}
                      style={{ textDecoration: 'none', color: 'inherit' }}
                    >
                      <div className="msg-avatar">
                        {other?.photo_path ? (
                          <img
                            src={buildAvatarUrl(other.photo_path)}
                            alt={otherName}
                          />
                        ) : (
                          <div className="avatar-placeholder-msg">
                            {initial}
                          </div>
                        )}
                      </div>
                      <div className="msg-content">
                        <div className="msg-header">
                          <h4 className="msg-sender">{otherName}</h4>
                          <span className="msg-time">
                            {timeAgo(msg.created_at)}
                          </span>
                        </div>
                        <p className="msg-subject">
                          {msg.subject || 'Sans sujet'}
                        </p>
                        <p className="msg-preview">
                          {(msg.body ?? '').slice(0, 80)}
                          {(msg.body ?? '').length > 80 ? '...' : ''}
                        </p>
                      </div>
                      {unread && <div className="unread-dot"></div>}
                    </Link>
                  )
                })
              )}
            </div>
          </aside>

          <main
            className={`messages-main${selectedMessage ? ' mobile-show' : ''}`}
          >
            {children ?? (
              <div className="message-placeholder">
                <div className="placeholder-content">
                  <i className="fas fa-envelope-open"></i>
                  <h3>Bienvenue dans votre messagerie</h3>
                  <p>
                    Sélectionnez un message dans la liste ou rédigez-en un
                    nouveau.
                  </p>
                  <Link
                    href="/message/composer"
                    className="btn btn-primary btn-lg"
                  >
                    <i className="fas fa-pen"></i> Nouveau Message
                  </Link>
                </div>
              </div>
            )}
          </main>
        </div>
      </div>
    </section>
  )
}
