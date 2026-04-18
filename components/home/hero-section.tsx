'use client'

import Link from 'next/link'
import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { ChevronLeft, ChevronRight, Code, GraduationCap, Briefcase, Users } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { formatNumber } from '@/lib/utils'
import { FORMATIONS_PATH } from '@/lib/routes'
import type { Profile } from '@/types'

const slides = [
  {
    badge: '🇧🇫 Plateforme Burkinabè',
    title: 'Apprenez, Codez,',
    highlight: 'Innovez',
    description: 'Rejoignez la plus grande communauté de développeurs du Burkina Faso. Partagez vos connaissances et collaborez sur des projets innovants.',
    primaryCta: { label: 'Rejoindre la communauté', href: '/register' },
    secondaryCta: { label: 'Explorer les formations', href: FORMATIONS_PATH },
    gradient: 'from-[#C8102E] via-[#8b0018] to-black',
    icon: Code,
  },
  {
    badge: '🎓 Formations',
    title: 'Parcours vidéo',
    highlight: 'niveau pro',
    description:
      'Des parcours structurés en français : web, mobile, data et plus. Bientôt : inscription payante et suivi pour les apprenants.',
    primaryCta: { label: 'Voir le catalogue', href: FORMATIONS_PATH },
    secondaryCta: { label: 'Publier une formation', href: `${FORMATIONS_PATH}/creer` },
    gradient: 'from-[#006A4E] via-[#004d39] to-black',
    icon: GraduationCap,
  },
  {
    badge: '💼 Opportunités',
    title: 'Emplois et stages',
    highlight: 'au Burkina Faso',
    description: 'Trouvez les meilleures opportunités tech au Burkina Faso. Emplois, stages, hackathons — tout est répertorié pour vous.',
    primaryCta: { label: 'Voir les offres', href: '/job' },
    secondaryCta: { label: 'Rejoindre un projet', href: '/project' },
    gradient: 'from-[#1a1a2e] via-[#16213e] to-black',
    icon: Briefcase,
  },
]

interface HeroSectionProps {
  profile: Profile | null
  stats: { users: number; posts: number; tutorials: number; projects: number }
}

export function HeroSection({ profile, stats }: HeroSectionProps) {
  const [current, setCurrent] = useState(0)
  const [autoPlay, setAutoPlay] = useState(true)

  useEffect(() => {
    if (!autoPlay) return
    const timer = setInterval(() => setCurrent(c => (c + 1) % slides.length), 5000)
    return () => clearInterval(timer)
  }, [autoPlay])

  const go = (dir: 'prev' | 'next') => {
    setAutoPlay(false)
    setCurrent(c => dir === 'next' ? (c + 1) % slides.length : (c - 1 + slides.length) % slides.length)
  }

  const slide = slides[current]

  return (
    <section className={`relative min-h-[90vh] flex items-center overflow-hidden bg-gradient-to-br ${slide.gradient} transition-all duration-700`}>
      {/* Animated particles */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        {[...Array(8)].map((_, i) => (
          <div
            key={i}
            className="absolute w-1 h-1 rounded-full bg-white/20"
            style={{
              left: `${10 + i * 12}%`,
              animationDelay: `${i * 0.8}s`,
              animation: `particleFloat ${8 + i * 2}s linear infinite`,
            }}
          />
        ))}
      </div>

      {/* Decorative circles */}
      <div className="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5 blur-3xl" />
      <div className="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-white/5 blur-3xl" />

      <div className="relative z-10 max-w-7xl mx-auto px-4 w-full py-20">
        <div className="max-w-3xl">
          <AnimatePresence mode="wait">
            <motion.div
              key={current}
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -20 }}
              transition={{ duration: 0.5, ease: 'easeOut' }}
            >
              {/* Badge */}
              <div className="inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-4 py-1.5 text-white/90 text-sm font-medium mb-6 backdrop-blur-sm">
                <span className="w-2 h-2 rounded-full bg-[#FFD100] animate-pulse" />
                {slide.badge}
              </div>

              {/* Title */}
              <h1 className="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-tight mb-4">
                {slide.title}{' '}
                <span className="text-[#FFD100]">{slide.highlight}</span>
              </h1>

              {/* Description */}
              <p className="text-lg text-white/70 max-w-xl leading-relaxed mb-8">
                {slide.description}
              </p>

              {/* CTAs */}
              <div className="flex flex-wrap gap-4 mb-10">
                {!profile ? (
                  <>
                    <Link href={slide.primaryCta.href}>
                      <Button size="xl" variant="accent" className="rounded-2xl font-bold shadow-lg shadow-black/30">
                        {slide.primaryCta.label}
                      </Button>
                    </Link>
                    <Link href={slide.secondaryCta.href}>
                      <Button size="xl" variant="outline" className="rounded-2xl border-white/30 text-white hover:bg-white/10 hover:text-white">
                        {slide.secondaryCta.label}
                      </Button>
                    </Link>
                  </>
                ) : (
                  <>
                    <Link href={FORMATIONS_PATH}>
                      <Button size="xl" variant="accent" className="rounded-2xl font-bold">
                        <GraduationCap size={20} />
                        Formations
                      </Button>
                    </Link>
                    <Link href="/forum">
                      <Button size="xl" variant="outline" className="rounded-2xl border-white/30 text-white hover:bg-white/10 hover:text-white">
                        <Users size={20} />
                        Forum
                      </Button>
                    </Link>
                  </>
                )}
              </div>

              {/* Quick stats */}
              <div className="flex flex-wrap gap-6">
                {[
                  { value: formatNumber(stats.users), label: 'Membres' },
                  { value: formatNumber(stats.posts), label: 'Discussions' },
                  { value: formatNumber(stats.tutorials), label: 'Formations' },
                  { value: formatNumber(stats.projects), label: 'Projets' },
                ].map(({ value, label }) => (
                  <div key={label} className="text-white">
                    <div className="text-2xl font-black">{value}</div>
                    <div className="text-white/60 text-sm">{label}</div>
                  </div>
                ))}
              </div>
            </motion.div>
          </AnimatePresence>
        </div>
      </div>

      {/* Carousel controls */}
      <div className="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-4 z-20">
        <button
          onClick={() => go('prev')}
          className="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white transition-colors backdrop-blur-sm"
        >
          <ChevronLeft size={18} />
        </button>
        <div className="flex gap-2">
          {slides.map((_, i) => (
            <button
              key={i}
              onClick={() => { setAutoPlay(false); setCurrent(i) }}
              className={`h-2 rounded-full transition-all ${i === current ? 'w-6 bg-[#FFD100]' : 'w-2 bg-white/40'}`}
            />
          ))}
        </div>
        <button
          onClick={() => go('next')}
          className="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white transition-colors backdrop-blur-sm"
        >
          <ChevronRight size={18} />
        </button>
      </div>
    </section>
  )
}
