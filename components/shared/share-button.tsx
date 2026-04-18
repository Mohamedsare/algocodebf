'use client'

import { ReactNode } from 'react'
import { useToast } from '@/components/ui/toast-provider'

interface Props {
  className?: string
  title?: string
  text?: string
  url?: string
  children: ReactNode
}

export function ShareButton({ className, title, text, url, children }: Props) {
  const toast = useToast()

  const onClick = async () => {
    if (typeof window === 'undefined') return
    const target = url ?? window.location.href
    const nav = window.navigator as Navigator & {
      share?: (data: { title?: string; text?: string; url?: string }) => Promise<void>
    }
    if (typeof nav.share === 'function') {
      try {
        await nav.share({ title, text, url: target })
        return
      } catch {
        // cancelled
      }
    }
    if (nav.clipboard) {
      try {
        await nav.clipboard.writeText(target)
        toast.success('Lien copié dans le presse-papiers.')
      } catch {
        toast.error('Impossible de copier le lien.')
      }
    }
  }

  return (
    <button type="button" className={className} onClick={onClick}>
      {children}
    </button>
  )
}
