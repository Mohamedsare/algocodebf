'use client'

import { useState, useTransition } from 'react'
import { toggleFollowAction } from '@/app/actions/users'

interface Props {
  targetId: string
  initialFollowing: boolean
}

export function FollowButtonPhp({ targetId, initialFollowing }: Props) {
  const [following, setFollowing] = useState(initialFollowing)
  const [pending, start] = useTransition()

  const onClick = () => {
    start(async () => {
      const res = await toggleFollowAction(targetId)
      if (res.ok && res.data) setFollowing(res.data.following)
    })
  }

  return (
    <button
      type="button"
      className={`btn-action ${following ? 'btn-primary-action' : 'btn-secondary-action'}`}
      onClick={onClick}
      disabled={pending}
    >
      <i className={`fas ${following ? 'fa-user-minus' : 'fa-user-plus'}`}></i>
      <span>
        {pending ? '...' : following ? 'Ne plus suivre' : 'Suivre'}
      </span>
    </button>
  )
}
