import type { AccountKind } from '@/types'
import { FORMATIONS_PATH } from '@/lib/routes'
import { MY_SPACE_PATHS } from '@/lib/my-space'

export interface EspaceNavItem {
  href: string
  label: string
  icon: string
  /** Ne met pas en avant comme « page courante » si on est sur un sous-chemin (ex. liens externes au hub). */
  exact?: boolean
}

export interface EspaceNavSection {
  title: string
  items: EspaceNavItem[]
}

export function getEspaceSidebarNav(kind: AccountKind, userId: string): EspaceNavSection[] {
  const dash = MY_SPACE_PATHS[kind]
  const compte: EspaceNavSection = {
    title: 'Compte',
    items: [
      { href: `/user/${userId}`, label: 'Mon profil public', icon: 'fa-user', exact: true },
      { href: '/user/modifier', label: 'Modifier le profil', icon: 'fa-pen-to-square' },
      { href: '/message', label: 'Messages', icon: 'fa-envelope' },
    ],
  }

  if (kind === 'student') {
    return [
      {
        title: 'Tableau de bord',
        items: [{ href: dash, label: 'Vue d’ensemble', icon: 'fa-table-columns', exact: true }],
      },
      {
        title: 'Apprendre & participer',
        items: [
          { href: FORMATIONS_PATH, label: 'Formations', icon: 'fa-graduation-cap' },
          { href: '/forum', label: 'Forum', icon: 'fa-comments' },
          { href: '/project', label: 'Projets', icon: 'fa-code-branch' },
          { href: '/blog', label: 'Blog', icon: 'fa-blog' },
        ],
      },
      {
        title: 'Communauté',
        items: [{ href: '/user/classement', label: 'Classement', icon: 'fa-trophy' }],
      },
      compte,
    ]
  }

  if (kind === 'professional') {
    return [
      {
        title: 'Tableau de bord',
        items: [{ href: dash, label: 'Vue d’ensemble', icon: 'fa-table-columns', exact: true }],
      },
      {
        title: 'Publier',
        items: [
          { href: '/tutorial/creer', label: 'Créer une formation', icon: 'fa-plus-circle' },
          { href: '/project/creer', label: 'Lancer un projet', icon: 'fa-diagram-project' },
        ],
      },
      {
        title: 'Communauté',
        items: [
          { href: FORMATIONS_PATH, label: 'Catalogue formations', icon: 'fa-layer-group' },
          { href: '/forum', label: 'Forum', icon: 'fa-comments' },
          { href: '/project', label: 'Projets', icon: 'fa-code-branch' },
        ],
      },
      compte,
    ]
  }

  return [
    {
      title: 'Tableau de bord',
      items: [{ href: dash, label: 'Vue d’ensemble', icon: 'fa-table-columns', exact: true }],
    },
    {
      title: 'Recrutement',
      items: [
        { href: '/job/creer', label: 'Publier une offre', icon: 'fa-briefcase' },
        { href: '/job', label: 'Opportunités', icon: 'fa-list' },
        { href: '/user', label: 'Talents & membres', icon: 'fa-users' },
      ],
    },
    compte,
  ]
}
