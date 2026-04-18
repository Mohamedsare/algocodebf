'use client'

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import { ThumbsUp } from 'lucide-react'
import { cn } from '@/lib/utils'
import { createClient } from '@/lib/supabase/client'

interface TutorialLikeButtonProps {
  tutorialId: number
  initialLikes: number
  initialLiked: boolean
  isAuthenticated: boolean
}

export function TutorialLikeButton({ tutorialId, initialLikes, initialLiked, isAuthenticated }: TutorialLikeButtonProps) {
  const router = useRouter()
  const [liked, setLiked] = useState(initialLiked)
  const [count, setCount] = useState(initialLikes)
  const [loading, setLoading] = useState(false)

  const toggle = async () => {
    if (!isAuthenticated) { router.push('/login'); return }
    if (loading) return

    setLoading(true)
    const prev = liked
    const prevCount = count
    setLiked(!liked)
    setCount(c => liked ? c - 1 : c + 1)

    try {
      const supabase = createClient()
      const { data: { user } } = await supabase.auth.getUser()
      if (!user) { router.push('/login'); return }

      if (prev) {
        await supabase.from('likes').delete().match({ user_id: user.id, likeable_type: 'tutorial', likeable_id: tutorialId })
        await supabase.from('tutorials').update({ likes_count: prevCount - 1 }).eq('id', tutorialId)
      } else {
        await supabase.from('likes').insert({ user_id: user.id, likeable_type: 'tutorial', likeable_id: tutorialId })
        await supabase.from('tutorials').update({ likes_count: prevCount + 1 }).eq('id', tutorialId)
      }
    } catch {
      setLiked(prev)
      setCount(prevCount)
    } finally {
      setLoading(false)
    }
  }

  return (
    <button
      onClick={toggle}
      disabled={loading}
      className={cn(
        'flex items-center gap-2 px-5 py-2.5 rounded-full border-2 font-semibold text-sm transition-all',
        liked
          ? 'bg-[#FFD100] border-[#FFD100] text-gray-900'
          : 'border-gray-700 text-gray-400 hover:border-[#FFD100] hover:text-[#FFD100]'
      )}
    >
      <ThumbsUp size={16} />
      <span>{count} J'aime{count > 1 ? 's' : ''}</span>
    </button>
  )
}
