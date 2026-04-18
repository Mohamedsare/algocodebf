'use client'

import { useState, useTransition } from 'react'
import { UserPlus, UserCheck } from 'lucide-react'
import { toggleFollowAction } from '@/app/actions/users'
import { cn } from '@/lib/utils'

interface FollowButtonProps {
  targetId: string
  initialFollowing: boolean
}

export function FollowButton({ targetId, initialFollowing }: FollowButtonProps) {
  const [following, setFollowing] = useState(initialFollowing)
  const [pending, startTransition] = useTransition()

  const handleClick = () => {
    startTransition(async () => {
      const result = await toggleFollowAction(targetId)
      if (result.ok && result.data) {
        setFollowing(result.data.following)
      }
    })
  }

  return (
    <button
      onClick={handleClick}
      disabled={pending}
      className={cn(
        'inline-flex items-center justify-center gap-2 h-10 px-4 rounded-xl text-sm font-semibold transition-colors disabled:opacity-60',
        following
          ? 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
          : 'bg-[#C8102E] text-white hover:bg-[#a00d24]'
      )}
    >
      {following ? <UserCheck size={16} /> : <UserPlus size={16} />}
      {following ? 'Abonné' : 'Suivre'}
    </button>
  )
}
