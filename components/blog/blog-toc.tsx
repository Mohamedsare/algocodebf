'use client'

import { useEffect, useState } from 'react'

interface TocItem {
  id: string
  text: string
  level: number
}

export function BlogToc() {
  const [items, setItems] = useState<TocItem[]>([])
  const [active, setActive] = useState<string>('')

  useEffect(() => {
    const main = document.querySelector('.bs-prose')
    if (!main) return

    const headings = Array.from(main.querySelectorAll('h2, h3')) as HTMLElement[]
    const parsed: TocItem[] = []
    headings.forEach((h, i) => {
      if (!h.id) {
        const slug =
          (h.textContent ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/\p{Diacritic}/gu, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .slice(0, 60) || `section-${i}`
        h.id = slug
      }
      parsed.push({
        id: h.id,
        text: h.textContent ?? '',
        level: h.tagName === 'H3' ? 3 : 2,
      })
    })
    setItems(parsed)

    if (parsed.length === 0) return

    const observer = new IntersectionObserver(
      entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) setActive(entry.target.id)
        })
      },
      { rootMargin: '-100px 0px -70% 0px', threshold: 0 }
    )
    headings.forEach(h => observer.observe(h))
    return () => observer.disconnect()
  }, [])

  if (items.length < 2) return null

  return (
    <nav aria-label="Sommaire">
      <p className="bs-toc-title">
        <i className="fas fa-list"></i> Sommaire
      </p>
      <ul className="bs-toc-list">
        {items.map(item => (
          <li key={item.id} className={item.level === 3 ? 'h3' : 'h2'}>
            <a
              href={`#${item.id}`}
              className={active === item.id ? 'active' : ''}
              onClick={e => {
                e.preventDefault()
                const target = document.getElementById(item.id)
                if (target) {
                  const y = target.getBoundingClientRect().top + window.scrollY - 100
                  window.scrollTo({ top: y, behavior: 'smooth' })
                  history.replaceState(null, '', `#${item.id}`)
                }
              }}
            >
              {item.text}
            </a>
          </li>
        ))}
      </ul>
    </nav>
  )
}
