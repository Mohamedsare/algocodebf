'use client'

import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import type { TutorialContentSection } from '@/lib/tutorial-content-sections'

const storageKey = (tutorialId: number) => `algocodebf-formation-page-${tutorialId}`

interface Props {
  tutorialId: number
  sections: TutorialContentSection[]
}

export function FormationContentPager({ tutorialId, sections }: Props) {
  const [index, setIndex] = useState(0)
  const hydrated = useRef(false)
  const articleRef = useRef<HTMLElement>(null)

  const total = sections.length
  const multi = total > 1

  useEffect(() => {
    if (!multi || typeof window === 'undefined') return
    try {
      const raw = window.localStorage.getItem(storageKey(tutorialId))
      if (raw == null) return
      const n = Number.parseInt(raw, 10)
      if (Number.isFinite(n) && n >= 0 && n < total) setIndex(n)
    } catch {
      /* ignore */
    } finally {
      hydrated.current = true
    }
  }, [tutorialId, total, multi])

  useEffect(() => {
    if (!multi || !hydrated.current || typeof window === 'undefined') return
    try {
      window.localStorage.setItem(storageKey(tutorialId), String(index))
    } catch {
      /* ignore */
    }
  }, [tutorialId, index, multi])

  const scrollPanelTop = useCallback(() => {
    const el = articleRef.current
    if (!el) return
    const rect = el.getBoundingClientRect()
    const top = rect.top + window.scrollY - 12
    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' })
  }, [])

  const go = useCallback(
    (next: number) => {
      const clamped = Math.max(0, Math.min(total - 1, next))
      setIndex(clamped)
      requestAnimationFrame(() => scrollPanelTop())
    },
    [total, scrollPanelTop]
  )

  useEffect(() => {
    if (!multi) return
    const onKey = (e: KeyboardEvent) => {
      const t = e.target as HTMLElement | null
      if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return
      if (e.key === 'ArrowRight') {
        e.preventDefault()
        go(index + 1)
      } else if (e.key === 'ArrowLeft') {
        e.preventDefault()
        go(index - 1)
      }
    }
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [multi, index, go])

  const current = sections[index] ?? sections[0]
  const pct = total > 0 ? ((index + 1) / total) * 100 : 100

  const dots = useMemo(
    () =>
      sections.map((s, i) => (
        <li key={s.id}>
          <button
            type="button"
            className={`ft-pager-dot${i === index ? ' is-active' : ''}`}
            aria-current={i === index ? 'step' : undefined}
            aria-label={`Aller à : ${s.title}`}
            title={s.title}
            onClick={() => go(i)}
          />
        </li>
      )),
    [sections, index, go]
  )

  if (!current) return null

  return (
    <div className={`ft-content-pager${multi ? ' ft-content-pager--multi' : ''}`}>
      {multi && (
        <div className="ft-pager-head">
          <div className="ft-pager-meta">
            <span className="ft-pager-label">
              Partie <strong>{index + 1}</strong> / {total}
            </span>
            <span className="ft-pager-hint" aria-hidden>
              ← →
            </span>
          </div>
          <div className="ft-pager-track" aria-hidden>
            <div className="ft-pager-track-fill" style={{ width: `${pct}%` }} />
          </div>
          <label className="ft-pager-select-wrap">
            <span className="sr-only">Choisir une partie du cours</span>
            <select
              className="ft-pager-select"
              value={index}
              onChange={e => go(Number(e.target.value))}
            >
              {sections.map((s, i) => (
                <option key={s.id} value={i}>
                  {i + 1}. {s.title}
                </option>
              ))}
            </select>
          </label>
          <ol className="ft-pager-dots" aria-label="Parties du cours">
            {dots}
          </ol>
        </div>
      )}

      <article
        ref={articleRef}
        className="ft-pager-article content-wrapper ft-prose"
        aria-live="polite"
        dangerouslySetInnerHTML={{ __html: current.html }}
      />

      {multi && (
        <nav className="ft-pager-nav" aria-label="Navigation entre les parties">
          <button
            type="button"
            className="ft-pager-btn ft-pager-btn--prev"
            disabled={index <= 0}
            onClick={() => go(index - 1)}
          >
            <i className="fas fa-arrow-left" aria-hidden />
            <span>Précédent</span>
          </button>
          <span className="ft-pager-nav-title">{current.title}</span>
          <button
            type="button"
            className="ft-pager-btn ft-pager-btn--next"
            disabled={index >= total - 1}
            onClick={() => go(index + 1)}
          >
            <span>Suivant</span>
            <i className="fas fa-arrow-right" aria-hidden />
          </button>
        </nav>
      )}
    </div>
  )
}
