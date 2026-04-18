import type { AccountKind, Profile } from '@/types'

/** Chemin public du tableau de bord selon le type de compte (inscription). */
export const MY_SPACE_PATHS: Record<AccountKind, string> = {
  student: '/espace/etudiant',
  professional: '/espace/formateur',
  enterprise: '/espace/entreprise',
}

export function getMySpacePath(profile: Pick<Profile, 'account_kind'>): string {
  const kind = profile.account_kind
  if (kind === 'professional') return MY_SPACE_PATHS.professional
  if (kind === 'enterprise') return MY_SPACE_PATHS.enterprise
  return MY_SPACE_PATHS.student
}

export function getAccountKindLabel(kind: AccountKind | undefined): string {
  switch (kind) {
    case 'professional':
      return 'Formateur'
    case 'enterprise':
      return 'Entreprise'
    default:
      return 'Étudiant'
  }
}
