'use client'

import { ReactNode } from 'react'

interface Props {
  className?: string
  title?: string
  text?: string
  url?: string
  children: ReactNode
}

export function ShareButton({ className, title, text, url, children }: Props) {
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
        alert('Lien copié dans le presse-papiers !')
      } catch {
        // ignore
      }
    }
  }

  return (
    <button type="button" className={className} onClick={onClick}>
      {children}
    </button>
  )
}
