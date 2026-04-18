'use client'

import Link from 'next/link'
import { useEffect, useRef } from 'react'

export function CtaSection() {
  const sectionRef = useRef<HTMLElement | null>(null)
  const canvasRef = useRef<HTMLCanvasElement | null>(null)
  const contentRef = useRef<HTMLDivElement | null>(null)

  useEffect(() => {
    const section = sectionRef.current
    const canvas = canvasRef.current
    const content = contentRef.current
    if (!section || !canvas) return

    const ctx = canvas.getContext('2d')
    if (!ctx) return

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches

    const resizeCanvas = () => {
      canvas.width = section.offsetWidth
      canvas.height = section.offsetHeight
    }
    resizeCanvas()
    const onResize = () => resizeCanvas()
    window.addEventListener('resize', onResize)

    class Star {
      x = 0
      y = 0
      size = 1
      speedX = 0
      speedY = 0
      opacity = 0
      fadeSpeed = 0.01
      growing = true

      constructor() {
        this.reset(true)
      }
      reset(initial = false) {
        this.x = Math.random() * canvas!.width
        this.y = Math.random() * canvas!.height
        this.size = Math.random() * 2 + 1
        this.speedX = (Math.random() - 0.5) * 0.5
        this.speedY = (Math.random() - 0.5) * 0.5
        this.opacity = initial ? Math.random() : 0
        this.fadeSpeed = Math.random() * 0.02 + 0.01
        this.growing = Math.random() > 0.5
      }
      update() {
        this.x += this.speedX
        this.y += this.speedY
        if (this.growing) {
          this.opacity += this.fadeSpeed
          if (this.opacity >= 1) this.growing = false
        } else {
          this.opacity -= this.fadeSpeed
          if (this.opacity <= 0) this.reset()
        }
        if (
          this.x < 0 ||
          this.x > canvas!.width ||
          this.y < 0 ||
          this.y > canvas!.height
        ) {
          this.reset()
        }
      }
      draw() {
        ctx!.save()
        ctx!.globalAlpha = this.opacity * 0.6
        ctx!.fillStyle = '#FFD100'
        ctx!.shadowBlur = 10
        ctx!.shadowColor = '#FFD100'
        ctx!.beginPath()
        for (let i = 0; i < 5; i++) {
          const angle = (Math.PI * 2 * i) / 5 - Math.PI / 2
          const x = this.x + Math.cos(angle) * this.size
          const y = this.y + Math.sin(angle) * this.size
          if (i === 0) ctx!.moveTo(x, y)
          else ctx!.lineTo(x, y)
        }
        ctx!.closePath()
        ctx!.fill()
        ctx!.restore()
      }
    }

    class GoldenParticle {
      x = 0
      y = 0
      size = 1
      speedX = 0
      speedY = 0
      opacity = 0

      constructor() {
        this.reset()
      }
      reset() {
        this.x = Math.random() * canvas!.width
        this.y = canvas!.height + 10
        this.size = Math.random() * 3 + 1
        this.speedY = -(Math.random() * 2 + 1)
        this.speedX = (Math.random() - 0.5) * 0.5
        this.opacity = Math.random() * 0.5 + 0.5
      }
      update() {
        this.y += this.speedY
        this.x += this.speedX
        this.opacity -= 0.005
        if (this.y < -10 || this.opacity <= 0) {
          this.reset()
        }
      }
      draw() {
        ctx!.save()
        ctx!.globalAlpha = this.opacity
        ctx!.fillStyle = '#FFD100'
        ctx!.shadowBlur = 5
        ctx!.shadowColor = '#FFD100'
        ctx!.beginPath()
        ctx!.arc(this.x, this.y, this.size, 0, Math.PI * 2)
        ctx!.fill()
        ctx!.restore()
      }
    }

    const starCount = window.innerWidth < 768 ? 30 : 50
    const stars: Star[] = Array.from({ length: starCount }, () => new Star())

    const particleCount = window.innerWidth < 768 ? 20 : 40
    const particles: GoldenParticle[] = Array.from(
      { length: particleCount },
      () => new GoldenParticle(),
    )

    let time = 0
    let rafId = 0

    const drawWaves = () => {
      ctx.save()
      const waveCount = 8
      const amplitude = 15
      const frequency = 0.02
      for (let i = 0; i < waveCount; i++) {
        ctx.beginPath()
        ctx.strokeStyle = `rgba(255, 255, 255, ${0.1 - i * 0.01})`
        ctx.lineWidth = 2
        const yOffset = (canvas.height / waveCount) * i
        for (let x = 0; x < canvas.width; x += 5) {
          const y =
            yOffset + Math.sin(x * frequency + time + i * 0.5) * amplitude
          if (x === 0) ctx.moveTo(x, y)
          else ctx.lineTo(x, y)
        }
        ctx.stroke()
      }
      ctx.restore()
    }

    const animate = () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height)
      time += 0.05
      drawWaves()
      stars.forEach((s) => {
        s.update()
        s.draw()
      })
      particles.forEach((p) => {
        p.update()
        p.draw()
      })
      rafId = requestAnimationFrame(animate)
    }

    if (!prefersReduced) animate()

    let contentTime = 0
    let floatId = 0
    const floatContent = () => {
      contentTime += 0.02
      if (content) {
        content.style.transform = `translateY(${Math.sin(contentTime) * 5}px)`
      }
      floatId = requestAnimationFrame(floatContent)
    }
    if (!prefersReduced && content) floatContent()

    let ticking = false
    const onScroll = () => {
      if (ticking) return
      ticking = true
      window.requestAnimationFrame(() => {
        const scrolled = window.pageYOffset
        const offsetTop = section.offsetTop
        const h = section.offsetHeight
        if (scrolled + window.innerHeight > offsetTop && scrolled < offsetTop + h) {
          canvas.style.transform = `translateY(${(scrolled - offsetTop) * 0.3}px)`
        }
        ticking = false
      })
    }
    if (!prefersReduced) window.addEventListener('scroll', onScroll, { passive: true })

    const starBurstOnButton = (btn: HTMLElement) => {
      const onEnter = () => {
        for (let i = 0; i < 10; i++) {
          const s = new Star()
          const rect = btn.getBoundingClientRect()
          const sectionRect = section.getBoundingClientRect()
          s.x = rect.left - sectionRect.left + rect.width / 2
          s.y = rect.top - sectionRect.top + rect.height / 2
          s.speedX = (Math.random() - 0.5) * 5
          s.speedY = (Math.random() - 0.5) * 5
          stars.push(s)
        }
      }
      btn.addEventListener('mouseenter', onEnter)
      return () => btn.removeEventListener('mouseenter', onEnter)
    }
    const cleanups: Array<() => void> = []
    section.querySelectorAll<HTMLElement>('.btn').forEach((btn) => {
      cleanups.push(starBurstOnButton(btn))
    })

    return () => {
      cancelAnimationFrame(rafId)
      cancelAnimationFrame(floatId)
      window.removeEventListener('resize', onResize)
      window.removeEventListener('scroll', onScroll)
      cleanups.forEach((fn) => fn())
    }
  }, [])

  return (
    <section ref={sectionRef} className="cta-section">
      <canvas
        ref={canvasRef}
        className="flag-canvas"
        style={{
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          pointerEvents: 'none',
          zIndex: 1,
        }}
      />
      <div className="star-halo" aria-hidden="true" />
      <div className="container">
        <div className="cta-content" ref={contentRef}>
          <h2>Prêt à rejoindre la communauté tech du Burkina Faso ?</h2>
          <p>
            Connectez-vous avec des développeurs, designers, et professionnels de l'IT de tout le pays
          </p>
          <Link href="/register" className="btn btn-primary btn-lg">
            REJOIGNEZ NOUS MAINTENANT
          </Link>
        </div>
      </div>
    </section>
  )
}
