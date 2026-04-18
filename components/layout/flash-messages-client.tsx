'use client'

import { useEffect, useRef } from 'react'
import { consumeFlashCookie } from '@/app/actions/flash'
import { useToast } from '@/components/ui/toast-provider'
import type { FlashMessage } from '@/lib/flash'

interface Props {
  messages: FlashMessage[]
  clearCookie: boolean
}

/**
 * Pousse les messages flash (cookie) vers la pile de toasts globale, puis efface le cookie.
 */
export function FlashMessagesClient({ messages, clearCookie }: Props) {
  const { push } = useToast()
  const deliveredKey = useRef<string | null>(null)

  useEffect(() => {
    if (clearCookie) void consumeFlashCookie()
  }, [clearCookie])

  useEffect(() => {
    if (messages.length === 0) {
      deliveredKey.current = null
      return
    }
    const key = JSON.stringify(messages.map(m => `${m.type}:${m.message}`))
    if (deliveredKey.current === key) return
    deliveredKey.current = key
    messages.forEach((m, i) => {
      window.setTimeout(() => push(m.type, m.message), i * 90)
    })
  }, [messages, push])

  return null
}
