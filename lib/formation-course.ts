/** Valeurs `tutorials.type` — aligné formulaire + BDD. */
export type FormationFormat = 'video' | 'text' | 'mixed'

export function normalizeFormationFormat(raw: string | null | undefined): FormationFormat {
  if (raw === 'video' || raw === 'text' || raw === 'mixed') return raw
  return 'mixed'
}

/** Pastille hero + accessibilité. */
export function formationTypeBadge(type: FormationFormat): { icon: string; label: string } {
  switch (type) {
    case 'video':
      return { icon: 'fa-video', label: 'Vidéo' }
    case 'text':
      return { icon: 'fa-align-left', label: 'Texte / PDF / lecture' }
    case 'mixed':
      return { icon: 'fa-layer-group', label: 'Mixte (vidéo + texte, code…)' }
  }
}

/** Ordre d’affichage : parcours « lecture » = contenu écrit avant les vidéos. */
export function readingSectionBeforeVideos(type: FormationFormat): boolean {
  return type === 'text'
}

export function formationVideoBlockTitle(type: FormationFormat): string {
  if (type === 'text') return 'Vidéos complémentaires'
  return 'Leçons vidéo'
}

export function formationVideoBlockHint(type: FormationFormat): string {
  if (type === 'text') {
    return 'Ressources vidéo associées au texte — visionnez-les dans l’ordre ou depuis le sommaire.'
  }
  if (type === 'mixed') {
    return 'Vidéos et démonstrations du parcours — complétées par le contenu écrit ci-dessous ou au-dessus.'
  }
  return 'Enchaînez les vidéos dans l’ordre ou accédez-y depuis le sommaire — pause, reprise et plein écran depuis le lecteur.'
}

export function formationReadingHeading(type: FormationFormat): string {
  if (type === 'video') return 'Notes et ressources écrites'
  if (type === 'text') return 'Contenu à lire'
  return 'Texte, code et exercices'
}

export function formationReadingIntro(type: FormationFormat): string | null {
  if (type === 'video') {
    return 'Complément aux vidéos : synthèses, extraits de code et liens utiles.'
  }
  if (type === 'text') {
    return 'Parcours centré sur la lecture : Markdown, exemples et pièces jointes éventuelles.'
  }
  return 'Partie écrite du parcours : théorie, pratique et code.'
}
