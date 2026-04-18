'use client'

import { useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { ThumbsUp } from 'lucide-react'
import { cn } from '@/lib/utils'
import { toggleLikeAction } from '@/app/actions/forum'
import type { LikeableType } from '@/types'

interface LikeButtonProps {
  type: LikeableType
  id: number
  initialLiked: boolean
  initialCount: number
  isAuthenticated: boolean
  variant?: 'default' | 'yellow' | 'green'
  size?: 'sm' | 'md'
}

/**
 * Bouton « J'aime » polymorphe (post/blog/tutorial/project/comment).
 * Équivalent de ForumController::toggleLike + BlogLikeButton + TutorialLikeButton.
 */
export function LikeButton({
  type,
  id,
  initialLiked,
  initialCount,
  isAuthenticated,
  variant = 'default',
  size = 'md',
}: LikeButtonProps) {
  const router = useRouter()
  const [liked, setLiked] = useState(initialLiked)
  const [count, setCount] = useState(initialCount)
  const [pending, startTransition] = useTransition()

  const handleClick = () => {
    if (!isAuthenticated) {
      router.push('/login')
      return
    }
    // Optimistic
    const prev = liked
    const prevCount = count
    setLiked(!liked)
    setCount(c => (liked ? c - 1 : c + 1))

    startTransition(async () => {
      const result = await toggleLikeAction(type, id)
      if (!result.ok || !result.data) {
        // Rollback
        setLiked(prev)
        setCount(prevCount)
      } else {
        setLiked(result.data.liked)
        setCount(result.data.count)
      }
    })
  }

  const colorClasses: Record<NonNullable<LikeButtonProps['variant']>, { on: string; off: string }> = {
    default: {
      on: 'bg-[#C8102E] border-[#C8102E] text-white',
      off: 'border-gray-200 text-gray-600 hover:border-[#C8102E] hover:text-[#C8102E] dark:border-gray-700 dark:text-gray-400',
    },
    yellow: {
      on: 'bg-[#FFD100] border-[#FFD100] text-gray-900',
      off: 'border-gray-700 text-gray-400 hover:border-[#FFD100] hover:text-[#FFD100]',
    },
    green: {
      on: 'bg-[#006A4E] border-[#006A4E] text-white',
      off: 'border-gray-200 text-gray-600 hover:border-[#006A4E] hover:text-[#006A4E] dark:border-gray-700 dark:text-gray-400',
    },
  }

  const { on, off } = colorClasses[variant]

  return (
    <button
      type="button"
      onClick={handleClick}
      disabled={pending}
      className={cn(
        'flex items-center gap-2 rounded-full border-2 font-semibold transition-all',
        size === 'sm' ? 'px-3 py-1.5 text-xs' : 'px-5 py-2.5 text-sm',
        liked ? on : off,
        pending && 'opacity-70'
      )}
    >
      <ThumbsUp size={size === 'sm' ? 13 : 16} className={cn('transition-transform', liked && 'scale-110')} />
      <span>
        {count} J&apos;aime{count > 1 ? 's' : ''}
      </span>
    </button>
  )
}
