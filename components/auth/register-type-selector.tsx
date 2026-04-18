import Link from 'next/link'

const CARDS = [
  {
    href: '/register/etudiant',
    tone: 'student' as const,
    icon: 'fa-graduation-cap',
    title: 'Étudiant·e',
    desc: 'Formations, forum et projets : apprenez avec la communauté tech du Burkina Faso.',
  },
  {
    href: '/register/professionnel',
    tone: 'pro' as const,
    icon: 'fa-briefcase',
    title: 'Professionnel·le',
    desc: 'Développez votre réseau, candidatez et partagez votre expertise.',
  },
  {
    href: '/register/entreprise',
    tone: 'org' as const,
    icon: 'fa-building',
    title: 'Entreprise',
    desc: 'Publiez des offres et recrutez des talents (compte recruteur).',
  },
]

export function RegisterTypeSelector() {
  return (
    <div className="au-type-grid">
      {CARDS.map(c => (
        <Link key={c.href} href={c.href} className="au-type-card" data-tone={c.tone}>
          <div className="au-type-icon" aria-hidden>
            <i className={`fas ${c.icon}`} />
          </div>
          <h2>{c.title}</h2>
          <p>{c.desc}</p>
          <span className="au-type-cta">
            Continuer <i className="fas fa-arrow-right" aria-hidden />
          </span>
        </Link>
      ))}
    </div>
  )
}
