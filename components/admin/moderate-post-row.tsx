'use client'

import { useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { Button } from '@/components/ui/button'
import { togglePinPostAction, hidePostAction, restorePostAction } from '@/app/actions/admin'
import { Pin, EyeOff, Eye } from 'lucide-react'

interface Props {
  id: number
  isPinned: boolean
  status: 'active' | 'inactive'
}

export function ModeratePostRow({ id, isPinned, status }: Props) {
  const router = useRouter()
  const [pending, startTransition] = useTransition()

  function run(fn: () => Promise<{ ok: boolean; message?: string }>) {
    startTransition(async () => {
      const res = await fn()
      if (res.ok) router.refresh()
      else alert(res.message)
    })
  }

  return (
    <div className="flex justify-end gap-1">
      <Button size="sm" variant="ghost" onClick={() => run(() => togglePinPostAction(id))} loading={pending} title={isPinned ? 'Désépingler' : 'Épingler'}>
        <Pin size={14} />
      </Button>
      {status === 'active' ? (
        <Button size="sm" variant="danger" onClick={() => run(() => hidePostAction(id))} loading={pending} title="Masquer">
          <EyeOff size={14} />
        </Button>
      ) : (
        <Button size="sm" variant="secondary" onClick={() => run(() => restorePostAction(id))} loading={pending} title="Restaurer">
          <Eye size={14} />
        </Button>
      )}
    </div>
  )
}
