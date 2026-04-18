'use client'

import { useCallback, useEffect, useMemo, useRef, useState, useTransition } from 'react'
import Link from 'next/link'
import { addCommentAction, deleteCommentAction } from '@/app/actions/forum'
import { createClient } from '@/lib/supabase/client'
import { buildAvatarUrl, timeAgo } from '@/lib/utils'
import type { Profile } from '@/types'

interface CommentItem {
  id: number
  body: string
  created_at: string
  user_id: string | null
  author: { id: string; prenom: string; nom: string; photo_path: string | null } | null
}

interface TypingUser {
  id: string
  name: string
  photo: string | null
  until: number
}

interface Props {
  postId: number
  initialComments: CommentItem[]
  profile: Profile | null
}

export function ForumCommentsLive({ postId, initialComments, profile }: Props) {
  const [comments, setComments] = useState<CommentItem[]>(initialComments)
  const [newIds, setNewIds] = useState<Set<number>>(new Set())
  const [body, setBody] = useState('')
  const [pending, startTransition] = useTransition()
  const [error, setError] = useState<string | null>(null)
  const [typing, setTyping] = useState<TypingUser[]>([])
  const [onlineCount, setOnlineCount] = useState<number>(1)
  const [onlinePreview, setOnlinePreview] = useState<
    Array<{ id: string; name: string; photo: string | null }>
  >([])

  const textareaRef = useRef<HTMLTextAreaElement>(null)
  const typingSentAt = useRef<number>(0)
  const channelRef = useRef<ReturnType<ReturnType<typeof createClient>['channel']> | null>(null)

  // Nettoyage auto du "typing" expiré
  useEffect(() => {
    const id = setInterval(() => {
      setTyping((prev) => prev.filter((t) => t.until > Date.now()))
    }, 800)
    return () => clearInterval(id)
  }, [])

  // Channel realtime : commentaires + typing + presence
  useEffect(() => {
    const supabase = createClient()
    const channel = supabase.channel(`forum:thread:${postId}`, {
      config: {
        broadcast: { self: false },
        presence: { key: profile?.id ?? `anon-${Math.random().toString(36).slice(2)}` },
      },
    })
    channelRef.current = channel

    channel
      // Nouveau commentaire (par un autre utilisateur ou à la racine)
      .on(
        'postgres_changes',
        {
          event: 'INSERT',
          schema: 'public',
          table: 'comments',
          filter: `commentable_id=eq.${postId}`,
        },
        async (payload) => {
          const row = payload.new as {
            id: number
            user_id: string | null
            commentable_type: string
            body: string
            status: string
            created_at: string
          }
          if (row.commentable_type !== 'post' || row.status !== 'active') return
          // On ignore si c'est notre propre insertion (déjà ajoutée en optimistic UI)
          if (row.user_id && row.user_id === profile?.id) {
            // On met à jour l'ID du commentaire optimistic (remplace id temporaire)
            setComments((prev) => {
              const hasReal = prev.some((c) => c.id === row.id)
              if (hasReal) return prev
              // Remplace le dernier optimistic avec body identique
              const idx = prev.findIndex((c) => c.id < 0 && c.body === row.body)
              if (idx === -1) return prev
              const next = [...prev]
              next[idx] = { ...next[idx], id: row.id, created_at: row.created_at }
              return next
            })
            return
          }

          let author: CommentItem['author'] = null
          if (row.user_id) {
            const { data } = await supabase
              .from('profiles')
              .select('id, prenom, nom, photo_path')
              .eq('id', row.user_id)
              .maybeSingle()
            if (data) author = data as CommentItem['author']
          }
          setComments((prev) => {
            if (prev.some((c) => c.id === row.id)) return prev
            return [
              ...prev,
              {
                id: row.id,
                body: row.body,
                created_at: row.created_at,
                user_id: row.user_id,
                author,
              },
            ]
          })
          setNewIds((prev) => {
            const next = new Set(prev)
            next.add(row.id)
            return next
          })
          setTimeout(() => {
            setNewIds((prev) => {
              const next = new Set(prev)
              next.delete(row.id)
              return next
            })
          }, 4000)
        },
      )
      // Suppression (soft) d'un commentaire
      .on(
        'postgres_changes',
        {
          event: 'UPDATE',
          schema: 'public',
          table: 'comments',
          filter: `commentable_id=eq.${postId}`,
        },
        (payload) => {
          const row = payload.new as { id: number; status: string }
          if (row.status === 'deleted' || row.status === 'inactive') {
            setComments((prev) => prev.filter((c) => c.id !== row.id))
          }
        },
      )
      .on(
        'postgres_changes',
        {
          event: 'DELETE',
          schema: 'public',
          table: 'comments',
          filter: `commentable_id=eq.${postId}`,
        },
        (payload) => {
          const row = payload.old as { id: number }
          setComments((prev) => prev.filter((c) => c.id !== row.id))
        },
      )
      // Typing indicator (broadcast)
      .on('broadcast', { event: 'typing' }, ({ payload }) => {
        const data = payload as TypingUser
        if (!data || data.id === profile?.id) return
        setTyping((prev) => {
          const filtered = prev.filter((t) => t.id !== data.id)
          return [...filtered, { ...data, until: Date.now() + 3000 }]
        })
      })
      // Presence (compteur + avatars)
      .on('presence', { event: 'sync' }, () => {
        const state = channel.presenceState() as Record<
          string,
          Array<{ user_id: string | null; name: string; photo: string | null }>
        >
        const users: Array<{ id: string; name: string; photo: string | null }> = []
        const seen = new Set<string>()
        for (const [key, metas] of Object.entries(state)) {
          const meta = metas?.[0]
          if (!meta) continue
          const id = meta.user_id ?? key
          if (seen.has(id)) continue
          seen.add(id)
          users.push({ id, name: meta.name, photo: meta.photo })
        }
        setOnlineCount(users.length || 1)
        setOnlinePreview(users.slice(0, 4))
      })
      .subscribe(async (status) => {
        if (status === 'SUBSCRIBED') {
          await channel.track({
            user_id: profile?.id ?? null,
            name: profile ? `${profile.prenom} ${profile.nom}` : 'Visiteur',
            photo: profile?.photo_path ?? null,
          })
        }
      })

    return () => {
      supabase.removeChannel(channel)
      channelRef.current = null
    }
  }, [postId, profile])

  // Envoi d'événement "typing" throttlé à 1s
  const broadcastTyping = useCallback(() => {
    if (!profile || !channelRef.current) return
    const now = Date.now()
    if (now - typingSentAt.current < 1200) return
    typingSentAt.current = now
    channelRef.current.send({
      type: 'broadcast',
      event: 'typing',
      payload: {
        id: profile.id,
        name: profile.prenom,
        photo: profile.photo_path ?? null,
        until: now + 3000,
      },
    })
  }, [profile])

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setError(null)
    const text = body.trim()
    if (text.length < 5) {
      setError('Le commentaire doit contenir au moins 5 caractères.')
      return
    }

    // Optimistic UI : on ajoute immédiatement (id négatif temporaire)
    const tempId = -Date.now()
    const optimistic: CommentItem = {
      id: tempId,
      body: text,
      created_at: new Date().toISOString(),
      user_id: profile?.id ?? null,
      author: profile
        ? {
            id: profile.id,
            prenom: profile.prenom,
            nom: profile.nom,
            photo_path: profile.photo_path,
          }
        : null,
    }
    setComments((prev) => [...prev, optimistic])
    setBody('')

    const fd = new FormData()
    fd.set('type', 'post')
    fd.set('id', String(postId))
    fd.set('body', text)

    startTransition(async () => {
      const res = await addCommentAction(fd)
      if (!res.ok) {
        setError(res.message ?? 'Impossible d’envoyer la réponse.')
        // rollback optimistic
        setComments((prev) => prev.filter((c) => c.id !== tempId))
        return
      }
      // Remplace l'optimistic avec l'id réel (le realtime le fera aussi mais on anticipe)
      setComments((prev) =>
        prev.map((c) => (c.id === tempId ? { ...c, id: res.data!.id } : c)),
      )
    })
  }

  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
      e.preventDefault()
      const form = (e.target as HTMLTextAreaElement).form
      form?.requestSubmit()
    }
  }

  const handleDelete = (commentId: number) => {
    if (!confirm('Supprimer ce commentaire ?')) return
    // Optimistic : on retire tout de suite
    const backup = comments
    setComments((prev) => prev.filter((c) => c.id !== commentId))
    startTransition(async () => {
      const res = await deleteCommentAction(commentId)
      if (!res.ok) {
        setComments(backup)
        alert(res.message ?? 'Suppression impossible.')
      }
    })
  }

  const focusComposer = () => {
    textareaRef.current?.focus()
    textareaRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' })
  }

  const typingLabel = useMemo(() => {
    if (typing.length === 0) return ''
    if (typing.length === 1) return `${typing[0].name} est en train d'écrire`
    if (typing.length === 2) return `${typing[0].name} et ${typing[1].name} écrivent`
    return `${typing[0].name} et ${typing.length - 1} autres écrivent`
  }, [typing])

  return (
    <div className="comments-section-saas" id="reponses">
      <div className="comments-head">
        <h2 className="comments-title-saas">
          <i className="fas fa-comments" style={{ color: 'var(--f-primary)' }}></i>
          <span>Réponses</span>
          <span className="count-badge">{comments.length}</span>
        </h2>
        <div className="presence-group">
          <span className="presence-dot"></span>
          <span>
            <strong style={{ color: 'var(--f-text)' }}>{onlineCount}</strong> en ligne
          </span>
          {onlinePreview.length > 0 && (
            <span className="presence-avatars" aria-hidden="true">
              {onlinePreview.map((u) => (
                <span key={u.id} className="p-avatar" title={u.name}>
                  {u.photo ? (
                    <img src={buildAvatarUrl(u.photo)} alt="" />
                  ) : (
                    <span>{(u.name ?? 'U').charAt(0).toUpperCase()}</span>
                  )}
                </span>
              ))}
              {onlineCount > onlinePreview.length && (
                <span className="p-more">+{onlineCount - onlinePreview.length}</span>
              )}
            </span>
          )}
        </div>
      </div>

      {/* Composer */}
      {profile ? (
        <form onSubmit={handleSubmit} className="comment-composer">
          <div className="composer-avatar">
            {profile.photo_path ? (
              <img src={buildAvatarUrl(profile.photo_path)} alt="Vous" />
            ) : (
              <span>{(profile.prenom ?? 'U').charAt(0).toUpperCase()}</span>
            )}
          </div>
          <div className="composer-main">
            <textarea
              ref={textareaRef}
              value={body}
              onChange={(e) => {
                setBody(e.target.value)
                broadcastTyping()
              }}
              onKeyDown={handleKeyDown}
              placeholder="Écrire une réponse… (Ctrl+Entrée pour publier)"
              aria-label="Votre réponse"
              required
            />
            {error && <div className="composer-error">{error}</div>}
            <div className="composer-footer">
              <span className="composer-hint">
                <kbd>Ctrl</kbd> + <kbd>⏎</kbd> pour publier
              </span>
              <button
                type="submit"
                className="btn-saas primary sm"
                disabled={pending || body.trim().length < 5}
              >
                <i className="fas fa-paper-plane" style={{ fontSize: 11 }}></i>
                {pending ? 'Envoi…' : 'Publier'}
              </button>
            </div>
          </div>
        </form>
      ) : (
        <div className="login-prompt-saas">
          <i className="fas fa-lock"></i>
          <span>
            <Link href="/login">Connectez-vous</Link> pour rejoindre la conversation.
          </span>
        </div>
      )}

      {/* Typing indicator */}
      <div className={`typing-indicator${typing.length > 0 ? ' is-active' : ''}`}>
        <span className="typing-dots" aria-hidden="true">
          <span></span>
          <span></span>
          <span></span>
        </span>
        <span>{typingLabel}</span>
      </div>

      {/* Liste */}
      <div className="comments-list-saas">
        {comments.length === 0 ? (
          <div
            style={{
              padding: '32px 20px',
              textAlign: 'center',
              color: 'var(--f-text-muted)',
              fontSize: 14,
            }}
          >
            <i className="fas fa-comment-slash" style={{ fontSize: 24, marginBottom: 10, opacity: 0.6 }}></i>
            <p style={{ margin: 0 }}>Aucune réponse pour l'instant. Soyez le premier à répondre !</p>
          </div>
        ) : (
          comments.map((c) => {
            const isOwn = profile?.id && profile.id === c.user_id
            const canDelete = profile && (isOwn || profile.role === 'admin')
            const isNew = newIds.has(c.id)
            const isOptimistic = c.id < 0
            return (
              <div
                key={c.id}
                id={`comment-${c.id}`}
                className={`comment-item-saas${isNew ? ' is-new' : ''}${isOwn ? ' is-own' : ''}`}
              >
                {c.author ? (
                  <Link href={`/user/${c.author.id}`} className="c-avatar">
                    {c.author.photo_path ? (
                      <img
                        src={buildAvatarUrl(c.author.photo_path)}
                        alt={`${c.author.prenom} ${c.author.nom}`}
                      />
                    ) : (
                      <span>{(c.author.prenom ?? 'U').charAt(0).toUpperCase()}</span>
                    )}
                  </Link>
                ) : (
                  <div className="c-avatar">?</div>
                )}

                <div className="c-content">
                  <div className="c-head">
                    {c.author ? (
                      <Link href={`/user/${c.author.id}`} className="c-author">
                        {c.author.prenom} {c.author.nom}
                      </Link>
                    ) : (
                      <span className="c-author">Anonyme</span>
                    )}
                    {isOwn && <span className="c-own-badge">Vous</span>}
                    <span className="c-time">
                      {isOptimistic ? 'Envoi…' : timeAgo(c.created_at)}
                    </span>
                  </div>

                  <div className="c-body">{c.body}</div>

                  <div className="c-actions">
                    {profile && !isOptimistic && (
                      <button
                        type="button"
                        className="c-action-btn"
                        onClick={focusComposer}
                      >
                        <i className="fas fa-reply" style={{ fontSize: 10 }}></i>
                        Répondre
                      </button>
                    )}
                    {canDelete && !isOptimistic && (
                      <button
                        type="button"
                        className="c-action-btn danger"
                        onClick={() => handleDelete(c.id)}
                      >
                        <i className="fas fa-trash" style={{ fontSize: 10 }}></i>
                        Supprimer
                      </button>
                    )}
                  </div>
                </div>
              </div>
            )
          })
        )}
      </div>
    </div>
  )
}
