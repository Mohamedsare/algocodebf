'use client'

import { useEffect, useState } from 'react'

/**
 * Enveloppe apprenant : progression de lecture + lien d’évitement (accessibilité).
 */
export function FormationLearnShell({ children }: { children: React.ReactNode }) {
  const [pct, setPct] = useState(0)

  useEffect(() => {
    const onScroll = () => {
      const el = document.documentElement
      const scrollable = el.scrollHeight - el.clientHeight
      if (scrollable <= 0) {
        setPct(0)
        return
      }
      setPct(Math.min(100, Math.max(0, (el.scrollTop / scrollable) * 100)))
    }
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)
    return () => {
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onScroll)
    }
  }, [])

  return (
    <>
      <div
        className="ft-read-progress-track"
        role="progressbar"
        aria-valuemin={0}
        aria-valuemax={100}
        aria-valuenow={Math.round(pct)}
        aria-label={`Progression de lecture : ${Math.round(pct)} pour cent`}
      >
        <div
          className="ft-read-progress-fill"
          style={{ transform: `scaleX(${pct / 100})` }}
        />
      </div>
      <a href="#formation-contenu-principal" className="ft-skip-to-content">
        Aller au contenu principal
      </a>
      {children}
    </>
  )
}
