'use client'

import { useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { ThumbsUp } from 'lucide-react'
import { cn } from '@/lib/utils'
import { toggleLikeAction } from '@/app/actions/forum'

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
  const [pending, startTransition] = useTransition()

  const toggle = () => {
    if (!isAuthenticated) {
      router.push('/login')
      return
    }
    if (pending) return

    const prevLiked = liked
    const prevCount = count
    setLiked(!liked)
    setCount(c => (prevLiked ? c - 1 : c + 1))

    startTransition(async () => {
      const res = await toggleLikeAction('tutorial', tutorialId)
      if (res.ok && res.data) {
        setLiked(res.data.liked)
        setCount(res.data.count)
        router.refresh()
      } else {
        setLiked(prevLiked)
        setCount(prevCount)
      }
    })
  }

  return (
    <button
      type="button"
      onClick={toggle}
      disabled={pending}
      className={cn(
        'flex items-center gap-2 px-5 py-2.5 rounded-full border-2 font-semibold text-sm transition-all',
        liked
          ? 'bg-[#FFD100] border-[#FFD100] text-gray-900'
          : 'border-gray-700 text-gray-400 hover:border-[#FFD100] hover:text-[#FFD100]'
      )}
    >
      <ThumbsUp size={16} />
      <span>
        {count} J&apos;aime{count > 1 ? 's' : ''}
      </span>
    </button>
  )
}
