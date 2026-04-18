'use client'

import { useEffect, useRef, useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { sendMessageAction } from '@/app/actions/messages'
import { useToast } from '@/components/ui/toast-provider'
import { buildAvatarUrl } from '@/lib/utils'

interface Receiver {
  id: string
  prenom: string
  nom: string
  photo_path: string | null
  university: string | null
}

interface Props {
  receiver?: Receiver | null
  replyTo?: number | null
  defaultSubject?: string
}

interface UserSearchResult {
  id: string
  name: string
  prenom: string
  nom: string
  photo: string
  role: string
  university: string
}

export function ComposeClient({ receiver, replyTo, defaultSubject }: Props) {
  const router = useRouter()
  const toast = useToast()
  const [pending, start] = useTransition()

  const [subject, setSubject] = useState(defaultSubject ?? '')
  const [body, setBody] = useState('')
  const [selectedUser, setSelectedUser] = useState<UserSearchResult | null>(
    null
  )
  const [query, setQuery] = useState('')
  const [searching, setSearching] = useState(false)
  const [results, setResults] = useState<UserSearchResult[]>([])
  const [dropdownOpen, setDropdownOpen] = useState(false)
  const textareaRef = useRef<HTMLTextAreaElement | null>(null)
  const searchContainerRef = useRef<HTMLDivElement | null>(null)

  useEffect(() => {
    if (!query || query.length < 2) {
      setResults([])
      setDropdownOpen(false)
      return
    }
    setSearching(true)
    setDropdownOpen(true)
    const t = setTimeout(() => {
      fetch(`/api/users/search?q=${encodeURIComponent(query)}`)
        .then(r => r.json())
        .then((data: UserSearchResult[]) => {
          setResults(data ?? [])
          setSearching(false)
        })
        .catch(() => setSearching(false))
    }, 250)
    return () => clearTimeout(t)
  }, [query])

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (!searchContainerRef.current) return
      if (!searchContainerRef.current.contains(e.target as Node))
        setDropdownOpen(false)
    }
    document.addEventListener('click', onClick)
    return () => document.removeEventListener('click', onClick)
  }, [])

  const selectUser = (u: UserSearchResult) => {
    setSelectedUser(u)
    setQuery(u.name)
    setDropdownOpen(false)
  }

  const insertText = (before: string, after: string) => {
    const textarea = textareaRef.current
    if (!textarea) return
    const start = textarea.selectionStart
    const end = textarea.selectionEnd
    const selected = textarea.value.substring(start, end)
    const replacement = before + (selected || 'texte') + after
    const next =
      textarea.value.substring(0, start) +
      replacement +
      textarea.value.substring(end)
    setBody(next)
    setTimeout(() => {
      textarea.focus()
      textarea.selectionStart = start + before.length
      textarea.selectionEnd =
        start + before.length + (selected || 'texte').length
    }, 0)
  }

  const onSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const receiverId = receiver?.id ?? selectedUser?.id ?? ''
    if (!receiverId) {
      toast.error('Veuillez sélectionner un destinataire.')
      return
    }
    if (!subject.trim()) {
      toast.error('Le sujet est requis.')
      return
    }
    if (body.trim().length < 10) {
      toast.error('Le message doit contenir au moins 10 caractères.')
      return
    }
    const fd = new FormData()
    fd.set('receiver_id', receiverId)
    fd.set('subject', subject.trim())
    fd.set('body', body.trim())
    start(async () => {
      const res = await sendMessageAction(fd)
      if (!res.ok) {
        toast.error(res.message ?? 'Envoi impossible.')
        return
      }
      try {
        localStorage.removeItem('message_draft')
      } catch {
        /* ignore */
      }
      router.push('/message/envoyes')
      router.refresh()
    })
  }

  const saveDraft = () => {
    try {
      localStorage.setItem(
        'message_draft',
        JSON.stringify({ subject, body })
      )
      toast.info('Brouillon sauvegardé.')
    } catch {
      /* ignore */
    }
  }

  const receiverName = receiver
    ? `${receiver.prenom} ${receiver.nom}`.trim()
    : ''
  const receiverInitial = (receiver?.prenom ?? 'U').charAt(0).toUpperCase()

  return (
    <section className="compose-message-section">
      <div className="container">
        <div className="compose-container">
          <div className="compose-header">
            <h1>
              {replyTo ? (
                <>
                  <i className="fas fa-reply"></i> Répondre au Message
                </>
              ) : (
                <>
                  <i className="fas fa-pen"></i> Nouveau Message
                </>
              )}
            </h1>
            <Link href="/message" className="btn-back-inbox">
              <i className="fas fa-arrow-left"></i> Retour
            </Link>
          </div>

          {replyTo && receiver && (
            <div className="reply-indicator">
              <i className="fas fa-info-circle"></i>
              <span>
                Vous répondez à un message de{' '}
                <strong>{receiverName}</strong>
              </span>
            </div>
          )}

          <form onSubmit={onSubmit} className="compose-form">
            <div className="compose-card">
              <div className="form-group-compose">
                <label htmlFor="receiver_id">
                  <i className="fas fa-user"></i> Destinataire *
                </label>
                {receiver ? (
                  <div className="selected-receiver">
                    <div className="receiver-avatar">
                      {receiver.photo_path ? (
                        <img
                          src={buildAvatarUrl(receiver.photo_path)}
                          alt={receiverName}
                        />
                      ) : (
                        <div className="avatar-placeholder-compose">
                          {receiverInitial}
                        </div>
                      )}
                    </div>
                    <div className="receiver-info">
                      <strong>{receiverName}</strong>
                      <span>{receiver.university ?? ''}</span>
                    </div>
                  </div>
                ) : (
                  <div
                    className="receiver-selector"
                    ref={searchContainerRef}
                  >
                    <i className="fas fa-search"></i>
                    <input
                      type="text"
                      placeholder="Rechercher un utilisateur..."
                      autoComplete="off"
                      value={query}
                      onChange={e => {
                        setQuery(e.target.value)
                        if (selectedUser) setSelectedUser(null)
                      }}
                    />
                    <div
                      className={`users-dropdown${dropdownOpen ? ' show' : ''}`}
                    >
                      {searching && (
                        <div
                          style={{
                            padding: 15,
                            textAlign: 'center',
                            color: '#6c757d',
                          }}
                        >
                          <i className="fas fa-spinner fa-spin"></i>{' '}
                          Recherche en cours...
                        </div>
                      )}
                      {!searching && results.length === 0 && (
                        <div
                          style={{
                            padding: 15,
                            textAlign: 'center',
                            color: '#6c757d',
                          }}
                        >
                          <i className="fas fa-search"></i> Aucun
                          utilisateur trouvé
                        </div>
                      )}
                      {!searching &&
                        results.map(u => (
                          <div
                            key={u.id}
                            className="user-option"
                            onClick={() => selectUser(u)}
                          >
                            {u.photo ? (
                              <img
                                src={buildAvatarUrl(u.photo)}
                                alt={u.name}
                                style={{
                                  width: 45,
                                  height: 45,
                                  borderRadius: '50%',
                                  objectFit: 'cover',
                                  flexShrink: 0,
                                }}
                              />
                            ) : (
                              <div
                                style={{
                                  width: 45,
                                  height: 45,
                                  borderRadius: '50%',
                                  background:
                                    'linear-gradient(135deg, #c8102e, #006a4e)',
                                  color: 'white',
                                  display: 'flex',
                                  alignItems: 'center',
                                  justifyContent: 'center',
                                  fontWeight: 700,
                                  fontSize: '1.2rem',
                                  flexShrink: 0,
                                }}
                              >
                                {(u.prenom || u.name).charAt(0).toUpperCase()}
                              </div>
                            )}
                            <div style={{ flex: 1, minWidth: 0 }}>
                              <div
                                style={{
                                  fontWeight: 600,
                                  color: '#2c3e50',
                                  fontSize: '0.95rem',
                                }}
                              >
                                {u.name}
                              </div>
                              {u.university && (
                                <div
                                  style={{
                                    fontSize: '0.85rem',
                                    color: '#6c757d',
                                    whiteSpace: 'nowrap',
                                    overflow: 'hidden',
                                    textOverflow: 'ellipsis',
                                  }}
                                >
                                  {u.university}
                                </div>
                              )}
                            </div>
                            {u.role === 'admin' && (
                              <span
                                style={{
                                  background:
                                    'linear-gradient(135deg, #ff6b6b, #ee5a6f)',
                                  color: 'white',
                                  padding: '3px 8px',
                                  borderRadius: 10,
                                  fontSize: '0.7rem',
                                  fontWeight: 600,
                                }}
                              >
                                Admin
                              </span>
                            )}
                          </div>
                        ))}
                    </div>
                  </div>
                )}
              </div>

              <div className="form-group-compose">
                <label htmlFor="subject">
                  <i className="fas fa-heading"></i> Sujet *
                </label>
                <input
                  type="text"
                  id="subject"
                  name="subject"
                  className="form-input-compose"
                  placeholder="De quoi voulez-vous parler ?"
                  value={subject}
                  onChange={e => setSubject(e.target.value)}
                  required
                  maxLength={200}
                />
              </div>

              <div className="form-group-compose">
                <label htmlFor="body">
                  <i className="fas fa-comment-alt"></i> Message *
                </label>
                <textarea
                  id="body"
                  name="body"
                  ref={textareaRef}
                  className="form-textarea-compose"
                  rows={12}
                  placeholder="Écrivez votre message ici..."
                  value={body}
                  onChange={e => setBody(e.target.value)}
                  required
                />
                <div className="textarea-footer">
                  <span className="char-count">
                    {body.length} caractères
                  </span>
                  <div className="formatting-tips">
                    <button
                      type="button"
                      className="tip-btn"
                      onClick={() => insertText('**', '**')}
                      title="Gras"
                    >
                      <i className="fas fa-bold"></i>
                    </button>
                    <button
                      type="button"
                      className="tip-btn"
                      onClick={() => insertText('*', '*')}
                      title="Italique"
                    >
                      <i className="fas fa-italic"></i>
                    </button>
                    <button
                      type="button"
                      className="tip-btn"
                      onClick={() => insertText('`', '`')}
                      title="Code"
                    >
                      <i className="fas fa-code"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div className="form-actions-compose">
                <button
                  type="submit"
                  className="btn-send-message"
                  disabled={pending}
                >
                  <i className="fas fa-paper-plane"></i>{' '}
                  {pending ? 'Envoi…' : 'Envoyer le message'}
                </button>
                <button
                  type="button"
                  className="btn-save-draft"
                  onClick={saveDraft}
                >
                  <i className="fas fa-save"></i> Sauvegarder brouillon
                </button>
                <Link href="/message" className="btn-cancel-compose">
                  <i className="fas fa-times"></i> Annuler
                </Link>
              </div>
            </div>
          </form>

          <div className="compose-tips">
            <div className="tips-card">
              <h3>
                <i className="fas fa-lightbulb"></i> Conseils
              </h3>
              <ul>
                <li>
                  <i className="fas fa-check"></i> Soyez clair et concis
                </li>
                <li>
                  <i className="fas fa-check"></i> Restez respectueux
                </li>
                <li>
                  <i className="fas fa-check"></i> Vérifiez l&apos;orthographe
                </li>
                <li>
                  <i className="fas fa-check"></i> Répondez rapidement
                </li>
              </ul>
            </div>
            <div className="tips-card">
              <h3>
                <i className="fas fa-shield-alt"></i> Sécurité
              </h3>
              <p>
                Ne partagez jamais vos informations personnelles sensibles
                par message.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
