/**
 * Découpe le HTML pédagogique en pages logiques pour la pagination « apprenant ».
 * — 2+ titres de niveau 2 → une page par partie h2.
 * — sinon 2+ titres h3 → une page par partie h3 (ex. un seul h2 puis plusieurs leçons en ###).
 * — sinon une seule page (ou intro + unique h2/h3 via buildSections).
 */

export type TutorialContentSection = {
  id: string
  title: string
  html: string
}

function stripTags(s: string): string {
  return s.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim()
}

function slugId(title: string, index: number): string {
  const base = stripTags(title)
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')
  return `${base || 'partie'}-${index}`
}

function findHeadingMatches(html: string, tag: 'h2' | 'h3'): Array<{ index: number; title: string }> {
  const re = new RegExp(`<${tag}(\\s[^>]*)?>([\\s\\S]*?)<\\/${tag}>`, 'gi')
  const out: Array<{ index: number; title: string }> = []
  let m: RegExpExecArray | null
  while ((m = re.exec(html)) !== null) {
    out.push({ index: m.index, title: stripTags(m[2]) || '' })
  }
  return out
}

function buildSections(
  html: string,
  matches: Array<{ index: number; title: string }>
): TutorialContentSection[] {
  if (matches.length === 0) {
    return [{ id: 'contenu', title: 'Contenu', html: html.trim() }]
  }
  const sections: TutorialContentSection[] = []
  const intro = html.slice(0, matches[0].index).trim()
  if (intro) {
    sections.push({
      id: 'introduction',
      title: 'Introduction',
      html: intro,
    })
  }
  for (let i = 0; i < matches.length; i++) {
    const start = matches[i].index
    const end = i + 1 < matches.length ? matches[i + 1].index : html.length
    const chunk = html.slice(start, end).trim()
    const title = matches[i].title || `Partie ${sections.length + 1}`
    sections.push({
      id: slugId(title, sections.length),
      title,
      html: chunk,
    })
  }
  return sections
}

/**
 * Découpe le HTML déjà rendu (ex. sortie de `markdownToHtml`).
 */
export function splitTutorialHtmlIntoSections(html: string): TutorialContentSection[] {
  const trimmed = html.trim()
  if (!trimmed) return []

  const h2 = findHeadingMatches(trimmed, 'h2')
  if (h2.length >= 2) {
    return buildSections(trimmed, h2)
  }

  const h3 = findHeadingMatches(trimmed, 'h3')
  if (h3.length >= 2) {
    return buildSections(trimmed, h3)
  }

  if (h2.length === 1) {
    return buildSections(trimmed, h2)
  }

  if (h3.length === 1) {
    return buildSections(trimmed, h3)
  }

  return [{ id: 'contenu', title: 'Contenu', html: trimmed }]
}
