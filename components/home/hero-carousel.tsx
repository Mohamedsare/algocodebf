'use client'

import Link from 'next/link'
import { useEffect, useRef, useState } from 'react'
import { FORMATIONS_PATH } from '@/lib/routes'

interface HeroCarouselProps {
  isLogged: boolean
  stats: {
    users: number
    posts: number
    tutorials: number
    projects: number
  }
}

/**
 * Carousel hero accueil — styles `.home-saas` / `home-saas.css` (palette BF).
 */
export function HeroCarousel({ isLogged, stats }: HeroCarouselProps) {
  const [current, setCurrent] = useState(0)
  const hovering = useRef(false)
  const touchStart = useRef<number | null>(null)

  const goTo = (idx: number) => setCurrent(((idx % 3) + 3) % 3)
  const move = (dir: number) => setCurrent(prev => ((prev + dir) % 3 + 3) % 3)

  useEffect(() => {
    const id = setInterval(() => {
      if (!hovering.current) move(1)
    }, 6000)
    return () => clearInterval(id)
  }, [])

  return (
    <section
      className="hm-hero"
      onMouseEnter={() => {
        hovering.current = true
      }}
      onMouseLeave={() => {
        hovering.current = false
      }}
      onTouchStart={e => {
        touchStart.current = e.changedTouches[0].screenX
      }}
      onTouchEnd={e => {
        if (touchStart.current == null) return
        const diff = touchStart.current - e.changedTouches[0].screenX
        if (Math.abs(diff) > 50) move(diff > 0 ? 1 : -1)
        touchStart.current = null
      }}
    >
      <div className="hm-hero-stage">
        {/* Slide 1 */}
        <div className={`hm-slide${current === 0 ? ' active' : ''}`}>
          <div className="hm-slide-grid">
            <div className="hm-slide-copy">
              <div className="hm-slide-text">
                <span className="hm-eyebrow">
                  <i className="fas fa-rocket" aria-hidden />
                  Communauté tech
                </span>
                <h1 className="hm-slide-title">Bienvenue sur AlgoCodeBF</h1>
                <p className="hm-slide-desc">
                  Le hub numérique qui rassemble informaticiens, développeurs et passionnés de technologie du Burkina
                  Faso.
                </p>
                <div className="hm-slide-actions">
                  {!isLogged ? (
                    <>
                      <Link href="/register" className="hm-btn hm-btn-primary hm-btn-lg">
                        <i className="fas fa-user-plus" />
                        Rejoindre gratuitement
                      </Link>
                      <Link href="/login" className="hm-btn hm-btn-secondary hm-btn-lg">
                        <i className="fas fa-sign-in-alt" />
                        Se connecter
                      </Link>
                    </>
                  ) : (
                    <>
                      <Link href="/forum" className="hm-btn hm-btn-primary hm-btn-lg">
                        <i className="fas fa-comments" />
                        Accéder au forum
                      </Link>
                      <Link href={FORMATIONS_PATH} className="hm-btn hm-btn-secondary hm-btn-lg">
                        <i className="fas fa-book" />
                        Voir les formations
                      </Link>
                    </>
                  )}
                </div>
                <div className="hm-inline-stats">
                  <div className="hm-inline-stat">
                    <span className="hm-inline-stat-num">{stats.users}+</span>
                    <span className="hm-inline-stat-lbl">Membres</span>
                  </div>
                  <div className="hm-inline-stat">
                    <span className="hm-inline-stat-num">{stats.posts}+</span>
                    <span className="hm-inline-stat-lbl">Discussions</span>
                  </div>
                  <div className="hm-inline-stat">
                    <span className="hm-inline-stat-num">{stats.tutorials}+</span>
                    <span className="hm-inline-stat-lbl">Formations</span>
                  </div>
                </div>
              </div>
            </div>
            <div className="hm-slide-media">
              <div className="hm-slide-figure">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src="/images/im1.png"
                  alt="Communauté tech"
                  onError={e => {
                    ;(e.target as HTMLImageElement).src =
                      'https://img.freepik.com/free-vector/teamwork-concept-landing-page_52683-20165.jpg'
                  }}
                />
              </div>
            </div>
          </div>
        </div>

        {/* Slide 2 */}
        <div className={`hm-slide${current === 1 ? ' active' : ''}`}>
          <div className="hm-slide-grid">
            <div className="hm-slide-copy">
              <div className="hm-slide-text">
                <span className="hm-eyebrow">
                  <i className="fas fa-briefcase" aria-hidden />
                  Opportunités
                </span>
                <h1 className="hm-slide-title">Boostez votre carrière</h1>
                <p className="hm-slide-desc">
                  Emplois, stages et missions freelance dans la tech au Burkina Faso — au même endroit.
                </p>
                <div className="hm-slide-actions">
                  <Link href="/job" className="hm-btn hm-btn-primary hm-btn-lg">
                    <i className="fas fa-briefcase" />
                    Voir les offres
                  </Link>
                  <Link href="/project" className="hm-btn hm-btn-secondary hm-btn-lg">
                    <i className="fas fa-project-diagram" />
                    Explorer les projets
                  </Link>
                </div>
              </div>
            </div>
            <div className="hm-slide-media">
              <div className="hm-slide-figure">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src="/images/im2.jpg"
                  alt="Carrière tech"
                  onError={e => {
                    ;(e.target as HTMLImageElement).src =
                      'https://img.freepik.com/free-vector/career-progress-concept-illustration_114360-5277.jpg'
                  }}
                />
              </div>
            </div>
          </div>
        </div>

        {/* Slide 3 */}
        <div className={`hm-slide${current === 2 ? ' active' : ''}`}>
          <div className="hm-slide-grid">
            <div className="hm-slide-copy">
              <div className="hm-slide-text">
                <span className="hm-eyebrow">
                  <i className="fas fa-graduation-cap" aria-hidden />
                  Apprentissage
                </span>
                <h1 className="hm-slide-title">Apprenez et progressez</h1>
                <p className="hm-slide-desc">
                  Formations structurées et ressources partagées par la communauté pour monter en compétences.
                </p>
                <div className="hm-slide-actions">
                  <Link href={FORMATIONS_PATH} className="hm-btn hm-btn-primary hm-btn-lg">
                    <i className="fas fa-graduation-cap" />
                    Explorer les formations
                  </Link>
                  <Link href="/blog" className="hm-btn hm-btn-secondary hm-btn-lg">
                    <i className="fas fa-newspaper" />
                    Lire le blog
                  </Link>
                </div>
              </div>
            </div>
            <div className="hm-slide-media">
              <div className="hm-slide-figure">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src="/images/im.png"
                  alt="Apprentissage"
                  onError={e => {
                    ;(e.target as HTMLImageElement).src =
                      'https://img.freepik.com/free-vector/online-tutorials-concept_52683-37481.jpg'
                  }}
                />
              </div>
            </div>
          </div>
        </div>

        <div className="hm-dots">
          {[0, 1, 2].map(i => (
            <button
              key={i}
              type="button"
              className={`hm-dot${current === i ? ' active' : ''}`}
              onClick={() => goTo(i)}
              aria-label={`Aller au slide ${i + 1}`}
            />
          ))}
        </div>
      </div>
    </section>
  )
}
