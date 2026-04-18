'use client'

import { useEffect, useState } from 'react'

export function BlogReadingProgress() {
  const [progress, setProgress] = useState(0)

  useEffect(() => {
    let ticking = false

    const compute = () => {
      const scrollTop = window.scrollY
      const docHeight = document.documentElement.scrollHeight - window.innerHeight
      if (docHeight <= 0) {
        setProgress(0)
        return
      }
      const pct = Math.min(100, Math.max(0, (scrollTop / docHeight) * 100))
      setProgress(pct)
    }

    const onScroll = () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          compute()
          ticking = false
        })
        ticking = true
      }
    }

    compute()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', compute)
    return () => {
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', compute)
    }
  }, [])

  return (
    <div
      className="bs-reading-progress"
      style={{ width: `${progress}%` }}
      aria-hidden="true"
    />
  )
}
