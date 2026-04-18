import Link from 'next/link'
import type { AccountKind, Profile } from '@/types'
import { FORMATIONS_PATH } from '@/lib/routes'
import { getAccountKindLabel } from '@/lib/my-space'
import { formatNumber } from '@/lib/utils'

const copy: Record<
  AccountKind,
  { title: string; lead: string; icon: string }
> = {
  student: {
    title: 'Votre espace apprentissage',
    lead:
      'Suivez votre progression, accédez aux formations et échangez avec la communauté — tout est regroupé ici.',
    icon: 'fa-graduation-cap',
  },
  professional: {
    title: 'Votre espace formateur',
    lead:
      'Publiez du contenu pédagogique, animez la communauté et développez votre visibilité sur AlgoCodeBF.',
    icon: 'fa-chalkboard-user',
  },
  enterprise: {
    title: 'Votre espace entreprise',
    lead:
      'Diffusez vos opportunités, touchez les talents IT du Burkina Faso et pilotez vos recrutements.',
    icon: 'fa-building',
  },
}

export function EspaceHome({ profile, kind }: { profile: Profile; kind: AccountKind }) {
  const c = copy[kind]
  const label = getAccountKindLabel(kind)
  const points = profile.points ?? 0
  const posts = profile.posts_count ?? 0
  const tutorials = profile.tutorials_count ?? 0

  const statSecondary =
    kind === 'enterprise'
      ? { label: 'Visibilité', value: '—', hint: 'Statistiques bientôt disponibles' }
      : kind === 'professional'
        ? {
            label: 'Formations',
            value: String(tutorials),
            hint: tutorials === 1 ? 'tutoriel publié' : 'tutoriels publiés',
          }
        : {
            label: 'Publications',
            value: String(posts),
            hint: posts === 1 ? 'post sur le forum' : 'posts sur le forum',
          }

  const actions =
    kind === 'student'
      ? [
          {
            href: FORMATIONS_PATH,
            icon: 'fa-graduation-cap',
            title: 'Catalogue formations',
            desc: 'Parcourir et suivre les contenus pédagogiques de la communauté.',
          },
          {
            href: '/forum',
            icon: 'fa-comments',
            title: 'Forum',
            desc: 'Poser vos questions et partager vos retours d’expérience.',
          },
          {
            href: '/project',
            icon: 'fa-code-branch',
            title: 'Projets',
            desc: 'Découvrir ou rejoindre des projets collaboratifs.',
          },
          {
            href: '/user/classement',
            icon: 'fa-trophy',
            title: 'Classement',
            desc: 'Voir votre position et celle des membres les plus actifs.',
          },
        ]
      : kind === 'professional'
        ? [
            {
              href: '/tutorial/creer',
              icon: 'fa-plus-circle',
              title: 'Créer une formation',
              desc: 'Publier un tutoriel vidéo, texte ou mixte pour la communauté.',
            },
            {
              href: FORMATIONS_PATH,
              icon: 'fa-layer-group',
              title: 'Catalogue',
              desc: 'Voir les formations existantes et vous inspirer des meilleures pratiques.',
            },
            {
              href: '/forum',
              icon: 'fa-comments',
              title: 'Animer le forum',
              desc: 'Répondre aux questions et renforcer votre expertise visible.',
            },
            {
              href: '/project/creer',
              icon: 'fa-diagram-project',
              title: 'Lancer un projet',
              desc: 'Proposer un projet ouvert aux contributeurs.',
            },
          ]
        : [
            {
              href: '/job/creer',
              icon: 'fa-briefcase',
              title: 'Publier une offre',
              desc: 'Stages, emplois, missions — touchez les profils IT qualifiés.',
            },
            {
              href: '/job',
              icon: 'fa-list',
              title: 'Mes opportunités',
              desc: 'Consulter et gérer les offres liées à votre organisation.',
            },
            {
              href: '/user',
              icon: 'fa-users',
              title: 'Talents',
              desc: 'Explorer les profils membres et repérer des candidats.',
            },
            {
              href: '/message',
              icon: 'fa-envelope',
              title: 'Messages',
              desc: 'Échanger avec les candidats et l’équipe AlgoCodeBF.',
            },
          ]

  return (
    <div className="espace-saas">
      <header className="espace-saas-hero">
        <div className="espace-saas-hero-inner">
          <div className="espace-saas-badge">
            <i className={`fas ${c.icon}`} aria-hidden />
            Mon espace · {label}
          </div>
          <h1>
            Bonjour, {profile.prenom || 'membre'} — {c.title}
          </h1>
          <p className="espace-saas-lead">{c.lead}</p>
        </div>
      </header>

      <div className="espace-saas-main">
        <div className="espace-saas-grid">
          <div className="espace-saas-card">
            <p className="espace-saas-card-label">Points communauté</p>
            <p className="espace-saas-card-value">{formatNumber(points)}</p>
            <p className="espace-saas-card-hint">Gagnez-en en participant sur la plateforme.</p>
          </div>
          <div className="espace-saas-card">
            <p className="espace-saas-card-label">{statSecondary.label}</p>
            <p className="espace-saas-card-value">{statSecondary.value}</p>
            <p className="espace-saas-card-hint">{statSecondary.hint}</p>
          </div>
          <div className="espace-saas-card">
            <p className="espace-saas-card-label">Profil</p>
            <p className="espace-saas-card-value" style={{ fontSize: '1.05rem', fontWeight: 700 }}>
              {profile.organization_name || profile.university || '—'}
            </p>
            <p className="espace-saas-card-hint">
              <Link href="/user/modifier" style={{ color: '#006a4e', fontWeight: 600 }}>
                Compléter mon profil →
              </Link>
            </p>
          </div>
        </div>

        <h2 className="espace-saas-section-title">Accès rapides</h2>
        <div className="espace-saas-actions">
          {actions.map(a => (
            <Link key={a.href} href={a.href} className="espace-saas-action">
              <span className="espace-saas-action-icon" aria-hidden>
                <i className={`fas ${a.icon}`} />
              </span>
              <span>
                <h3>{a.title}</h3>
                <p>{a.desc}</p>
              </span>
            </Link>
          ))}
        </div>
      </div>
    </div>
  )
}
