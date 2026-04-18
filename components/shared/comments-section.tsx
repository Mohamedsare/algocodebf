'use client'

import { useState, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { MessageSquare, Send, Trash2 } from 'lucide-react'
import { Avatar } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { createClient } from '@/lib/supabase/client'
import { formatRelativeTime } from '@/lib/utils'
import Link from 'next/link'
import type { Profile } from '@/types'

interface CommentsProps {
  commentableType: 'post' | 'tutorial' | 'blog' | 'project'
  commentableId: number
  profile: Profile | null
}

interface CommentWithAuthor {
  id: number
  body: string
  created_at: string
  user_id: string | null
  profiles: { prenom: string; nom: string; photo_path: string | null; university: string | null } | null
}

export function CommentsSection({ commentableType, commentableId, profile }: CommentsProps) {
  const router = useRouter()
  const [comments, setComments] = useState<CommentWithAuthor[]>([])
  const [body, setBody] = useState('')
  const [loading, setLoading] = useState(false)
  const [fetching, setFetching] = useState(true)

  const fetchComments = async () => {
    const supabase = createClient()
    const { data } = await supabase
      .from('comments')
      .select('id, body, created_at, user_id, profiles!left(prenom, nom, photo_path, university)')
      .eq('commentable_type', commentableType)
      .eq('commentable_id', commentableId)
      .eq('status', 'active')
      .order('created_at', { ascending: true })
    setComments((data ?? []) as unknown as CommentWithAuthor[])
    setFetching(false)
  }

  useEffect(() => { fetchComments() }, [commentableId])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!profile) { router.push('/login'); return }
    if (!body.trim() || body.trim().length < 5) return

    setLoading(true)
    try {
      const supabase = createClient()
      const { error } = await supabase.from('comments').insert({
        user_id: profile.id,
        commentable_type: commentableType,
        commentable_id: commentableId,
        body: body.trim(),
      })
      if (!error) {
        setBody('')
        fetchComments()
      }
    } finally {
      setLoading(false)
    }
  }

  const handleDelete = async (commentId: number) => {
    const supabase = createClient()
    await supabase.from('comments').update({ status: 'deleted' }).eq('id', commentId)
    setComments(c => c.filter(x => x.id !== commentId))
  }

  return (
    <div className="mt-12">
      <h2 className="flex items-center gap-2 text-xl font-bold text-gray-900 dark:text-white mb-6">
        <MessageSquare size={22} className="text-[#C8102E]" />
        Commentaires ({comments.length})
      </h2>

      {/* Comment form */}
      {profile ? (
        <form onSubmit={handleSubmit} className="flex gap-3 mb-8">
          <Avatar src={profile.photo_path} prenom={profile.prenom} nom={profile.nom} size="md" className="flex-shrink-0" />
          <div className="flex-1 flex gap-2">
            <textarea
              value={body}
              onChange={e => setBody(e.target.value)}
              placeholder="Partagez votre avis..."
              rows={3}
              className="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/40 focus:border-[#C8102E] resize-none"
            />
            <Button
              type="submit"
              loading={loading}
              disabled={body.trim().length < 5}
              size="icon"
              className="flex-shrink-0 self-end h-12 w-12 rounded-xl"
            >
              <Send size={18} />
            </Button>
          </div>
        </form>
      ) : (
        <div className="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 text-center mb-8">
          <p className="text-gray-500 mb-3">Connectez-vous pour laisser un commentaire</p>
          <Link href="/login" className="text-[#C8102E] font-semibold hover:underline">
            Se connecter
          </Link>
        </div>
      )}

      {/* Comments list */}
      {fetching ? (
        <div className="text-center py-6 text-gray-400 text-sm">Chargement...</div>
      ) : comments.length === 0 ? (
        <div className="text-center py-6 text-gray-400 text-sm">Soyez le premier à commenter !</div>
      ) : (
        <div className="space-y-5">
          {comments.map(comment => {
            const author = comment.profiles
            const canDelete = profile && (profile.id === comment.user_id || profile.role === 'admin')
            return (
              <div key={comment.id} className="flex gap-3">
                <Avatar src={author?.photo_path} prenom={author?.prenom} nom={author?.nom} size="sm" className="flex-shrink-0" />
                <div className="flex-1 bg-gray-50 dark:bg-gray-900 rounded-2xl px-4 py-3">
                  <div className="flex items-center justify-between mb-1">
                    <div>
                      <span className="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        {author?.prenom} {author?.nom}
                      </span>
                      {author?.university && (
                        <span className="text-xs text-gray-400 ml-2">{author.university}</span>
                      )}
                    </div>
                    <div className="flex items-center gap-2">
                      <span className="text-xs text-gray-400">{formatRelativeTime(comment.created_at)}</span>
                      {canDelete && (
                        <button
                          onClick={() => handleDelete(comment.id)}
                          className="text-gray-300 hover:text-red-500 transition-colors"
                        >
                          <Trash2 size={13} />
                        </button>
                      )}
                    </div>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{comment.body}</p>
                </div>
              </div>
            )
          })}
        </div>
      )}
    </div>
  )
}
