'use client'

import { useEffect, useRef, useState } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { createClient } from '@/lib/supabase/client'
import { buildAvatarUrl, formatRelativeTime } from '@/lib/utils'
import type { Profile } from '@/types'

interface Props {
  postId: number
  profile: Profile | null
}

interface CommentRow {
  id: number
  body: string
  created_at: string
  user_id: string | null
  profiles: {
    prenom: string | null
    nom: string | null
    photo_path: string | null
    university?: string | null
  } | null
  _new?: boolean
}

export function BlogCommentsLive({ postId, profile }: Props) {
  const router = useRouter()
  const [comments, setComments] = useState<CommentRow[]>([])
  const [fetching, setFetching] = useState(true)
  const [body, setBody] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const newIdsRef = useRef<Set<number>>(new Set())

  // Initial fetch
  useEffect(() => {
    let cancelled = false
    ;(async () => {
      const supabase = createClient()
      const { data } = await supabase
        .from('comments')
        .select(
          'id, body, created_at, user_id, profiles!left(prenom, nom, photo_path, university)'
        )
        .eq('commentable_type', 'blog')
        .eq('commentable_id', postId)
        .eq('status', 'active')
        .order('created_at', { ascending: true })
      if (!cancelled) {
        setComments((data ?? []) as unknown as CommentRow[])
        setFetching(false)
      }
    })()
    return () => {
      cancelled = true
    }
  }, [postId])

  // Realtime INSERT / UPDATE / DELETE
  useEffect(() => {
    const supabase = createClient()
    const channel = supabase
      .channel(`blog-comments-${postId}`)
      .on(
        'postgres_changes',
        {
          event: 'INSERT',
          schema: 'public',
          table: 'comments',
          filter: `commentable_id=eq.${postId}`,
        },
        async payload => {
          const row = payload.new as CommentRow & {
            commentable_type?: string
            status?: string
          }
          if (row.commentable_type !== 'blog' || row.status !== 'active') return

          // Fetch profile
          let profileRow: CommentRow['profiles'] = null
          if (row.user_id) {
            const { data } = await supabase
              .from('profiles')
              .select('prenom, nom, photo_path, university')
              .eq('id', row.user_id)
              .maybeSingle()
            profileRow = data as CommentRow['profiles']
          }

          setComments(prev => {
            // Si déjà présent (optimistic replace déjà fait), ignore
            if (prev.some(c => c.id === row.id)) return prev
            // Replace optimistic (id < 0) si body match
            const optIdx = prev.findIndex(c => c.id < 0 && c.body === row.body)
            const newComment: CommentRow = {
              id: row.id,
              body: row.body,
              created_at: row.created_at,
              user_id: row.user_id,
              profiles: profileRow,
              _new: true,
            }
            if (optIdx !== -1) {
              const copy = [...prev]
              copy[optIdx] = newComment
              return copy
            }
            newIdsRef.current.add(row.id)
            return [...prev, newComment]
          })
        }
      )
      .on(
        'postgres_changes',
        {
          event: 'UPDATE',
          schema: 'public',
          table: 'comments',
          filter: `commentable_id=eq.${postId}`,
        },
        payload => {
          const row = payload.new as CommentRow & { status: string }
          if (row.status === 'deleted') {
            setComments(prev => prev.filter(c => c.id !== row.id))
          } else {
            setComments(prev =>
              prev.map(c => (c.id === row.id ? { ...c, body: row.body } : c))
            )
          }
        }
      )
      .on(
        'postgres_changes',
        {
          event: 'DELETE',
          schema: 'public',
          table: 'comments',
          filter: `commentable_id=eq.${postId}`,
        },
        payload => {
          const row = payload.old as { id: number } | null
          if (!row) return
          setComments(prev => prev.filter(c => c.id !== row.id))
        }
      )
      .subscribe()

    return () => {
      supabase.removeChannel(channel)
    }
  }, [postId])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!profile) {
      router.push('/login')
      return
    }
    const text = body.trim()
    if (text.length < 5 || submitting) return

    setSubmitting(true)
    const optimisticId = -(Date.now() + Math.random())
    const optimistic: CommentRow = {
      id: optimisticId,
      body: text,
      created_at: new Date().toISOString(),
      user_id: profile.id,
      profiles: {
        prenom: profile.prenom,
        nom: profile.nom,
        photo_path: profile.photo_path,
        university: profile.university,
      },
      _new: true,
    }
    setComments(prev => [...prev, optimistic])
    setBody('')

    try {
      const supabase = createClient()
      const { data, error } = await supabase
        .from('comments')
        .insert({
          user_id: profile.id,
          commentable_type: 'blog',
          commentable_id: postId,
          body: text,
        })
        .select('id')
        .maybeSingle()

      if (error || !data) throw error ?? new Error('insert failed')

      // Replace optimistic with real id (if realtime didn't already)
      setComments(prev => {
        const hasReal = prev.some(c => c.id === data.id)
        if (hasReal) return prev.filter(c => c.id !== optimisticId)
        return prev.map(c => (c.id === optimisticId ? { ...c, id: data.id } : c))
      })
    } catch {
      // Rollback
      setComments(prev => prev.filter(c => c.id !== optimisticId))
      setBody(text)
    } finally {
      setSubmitting(false)
    }
  }

  const handleDelete = async (id: number) => {
    if (id < 0) return
    const prev = comments
    setComments(p => p.filter(c => c.id !== id))
    try {
      const supabase = createClient()
      await supabase.from('comments').update({ status: 'deleted' }).eq('id', id)
    } catch {
      setComments(prev)
    }
  }

  return (
    <section className="bs-comments" id="comments">
      <div className="bs-comments-header">
        <h2>Commentaires</h2>
        <span className="bs-comments-count">{comments.length}</span>
        <span className="bs-live-dot">En direct</span>
      </div>

      {profile ? (
        <form className="bs-comment-form" onSubmit={handleSubmit}>
          <div className="bs-avatar">
            {profile.photo_path ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img
                src={buildAvatarUrl(profile.photo_path)}
                alt={`${profile.prenom} ${profile.nom}`}
              />
            ) : (
              <span>{(profile.prenom?.charAt(0) ?? 'U').toUpperCase()}</span>
            )}
          </div>
          <div className="bs-comment-form-body">
            <textarea
              value={body}
              onChange={e => setBody(e.target.value)}
              placeholder="Partagez votre avis sur cet article…"
              maxLength={2000}
              disabled={submitting}
            />
            <div className="bs-comment-form-actions">
              <span className="bs-comment-form-hint">
                Soyez respectueux &amp; constructif.
              </span>
              <button
                type="submit"
                className="bs-comment-submit"
                disabled={body.trim().length < 5 || submitting}
              >
                {submitting ? (
                  <>
                    <i className="fas fa-spinner fa-spin"></i> Envoi…
                  </>
                ) : (
                  <>
                    <i className="fas fa-paper-plane"></i> Publier
                  </>
                )}
              </button>
            </div>
          </div>
        </form>
      ) : (
        <div className="bs-comment-login">
          <p>Rejoignez la conversation</p>
          <Link href="/login">
            <i className="fas fa-sign-in-alt"></i> Se connecter pour commenter
          </Link>
        </div>
      )}

      {fetching ? (
        <div
          style={{
            padding: '30px 0',
            textAlign: 'center',
            color: 'var(--bsaas-text-subtle)',
            fontSize: '.88rem',
          }}
        >
          <i className="fas fa-spinner fa-spin"></i> Chargement…
        </div>
      ) : comments.length === 0 ? (
        <div
          style={{
            padding: '30px 0',
            textAlign: 'center',
            color: 'var(--bsaas-text-subtle)',
            fontSize: '.92rem',
          }}
        >
          Soyez le premier à commenter cet article.
        </div>
      ) : (
        <div className="bs-comments-list">
          {comments.map(c => {
            const a = c.profiles
            const name = a ? `${a.prenom ?? ''} ${a.nom ?? ''}`.trim() || 'Utilisateur' : 'Utilisateur'
            const initial = (name.charAt(0) || 'U').toUpperCase()
            const canDelete =
              profile && (profile.id === c.user_id || profile.role === 'admin') && c.id > 0
            return (
              <div
                key={c.id}
                className={`bs-comment${c._new || newIdsRef.current.has(c.id) ? ' new-comment' : ''}`}
              >
                <div className="bs-avatar">
                  {a?.photo_path ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img src={buildAvatarUrl(a.photo_path)} alt={name} />
                  ) : (
                    <span>{initial}</span>
                  )}
                </div>
                <div className="bs-comment-body">
                  <div className="bs-comment-head">
                    <span className="bs-comment-author">{name}</span>
                    <span className="bs-comment-date">{formatRelativeTime(c.created_at)}</span>
                    {canDelete && (
                      <button
                        type="button"
                        className="bs-comment-delete"
                        onClick={() => handleDelete(c.id)}
                        title="Supprimer"
                      >
                        <i className="fas fa-trash-alt"></i>
                      </button>
                    )}
                  </div>
                  <p className="bs-comment-text">{c.body}</p>
                </div>
              </div>
            )
          })}
        </div>
      )}
    </section>
  )
}
