'use client'

import { useState } from 'react'

interface Props {
  postId: number
  title: string
}

export function SharePostButton({ postId, title }: Props) {
  const [copied, setCopied] = useState(false)

  const handleShare = async () => {
    const url = `${window.location.origin}/forum/${postId}`
    try {
      if (navigator.share) {
        await navigator.share({ title, url })
        return
      }
      await navigator.clipboard.writeText(url)
      setCopied(true)
      setTimeout(() => setCopied(false), 1800)
    } catch {
      /* ignore (l'utilisateur a annulé le share sheet, etc.) */
    }
  }

  return (
    <button type="button" className="react-btn" onClick={handleShare} title="Partager ce lien">
      <i className={`fas ${copied ? 'fa-check' : 'fa-link'}`}></i>
      <span style={{ opacity: 0.7, fontWeight: 500 }}>
        {copied ? 'Lien copié !' : 'Partager'}
      </span>
    </button>
  )
}
