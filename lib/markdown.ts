import { highlightCodeFence } from '@/lib/code-highlight'

/**
 * Mini convertisseur markdown -> HTML, équivalent allégé du helper PHP `markdownToHtml`.
 * Ne gère que les cas usuels : titres, gras/italique, code inline, code block, liens,
 * listes, paragraphes et retours à la ligne.
 *
 * Les blocs ``` bénéficient de la coloration syntaxique (highlight.js).
 *
 * Si le contenu ressemble déjà à du HTML (articles PHP importés), il est renvoyé tel quel.
 * Détection prudente : une mention de `</p>` dans un cours sur le HTML, ou dans un bloc
 * ``` ```, ne doit pas court-circuiter tout le Markdown (sinon affichage brut des #, **, etc.).
 */
function stripCodeFencesForDetection(s: string): string {
  return s.replace(/```[\w-]*\s*\n?[\s\S]*?```/g, '')
}

function hasMarkdownStructure(full: string, withoutFences: string): boolean {
  if (/```/.test(full)) return true
  return (
    /(^|\n)#{1,6}\s/m.test(withoutFences) ||
    /(^|\n)\s{0,3}[-*+]\s+\S/m.test(withoutFences) ||
    /(^|\n)\s{0,3}\d+\.\s+\S/m.test(withoutFences) ||
    /\*\*[^*\n]+\*\*/.test(withoutFences) ||
    /(^|\n)\s{0,3}>\s/m.test(withoutFences)
  )
}

function isLikelyPreRenderedHtml(src: string): boolean {
  const probe = stripCodeFencesForDetection(src)
  const hasClosingBlock = /<\/(p|h[1-6]|ul|ol|li|blockquote|pre|table|div|img|figure)>/i.test(
    probe
  )
  if (!hasClosingBlock) return false
  /* Markdown détecté : on convertit malgré `</p>` cité dans le texte ou dans un exemple. */
  if (hasMarkdownStructure(src, probe)) return false
  return true
}

export function markdownToHtml(src: string): string {
  if (!src) return ''

  if (isLikelyPreRenderedHtml(src)) return src

  let md = src.replace(/\r\n/g, '\n')

  const codeBlocks: string[] = []
  /* Blocs ``` ou ```lang (langue optionnelle, saut de ligne optionnel) + coloration syntaxique. */
  md = md.replace(/```(\w+)?\s*\n?([\s\S]*?)```/g, (_m, _lang: string | undefined, code: string) => {
    codeBlocks.push(highlightCodeFence(code, _lang))
    return `@@CODEBLOCK${codeBlocks.length - 1}@@`
  })

  md = md.replace(/^\s*(?:---|\*\*\*)\s*$/gm, '<hr />')

  md = md.replace(/`([^`\n]+)`/g, (_m, c) => `<code>${escapeHtml(c)}</code>`)

  md = md.replace(/^######\s+(.+)$/gm, '<h6>$1</h6>')
  md = md.replace(/^#####\s+(.+)$/gm, '<h5>$1</h5>')
  md = md.replace(/^####\s+(.+)$/gm, '<h4>$1</h4>')
  md = md.replace(/^###\s+(.+)$/gm, '<h3>$1</h3>')
  md = md.replace(/^##\s+(.+)$/gm, '<h2>$1</h2>')
  md = md.replace(/^#\s+(.+)$/gm, '<h1>$1</h1>')

  md = md.replace(/^\s*>\s?(.+)$/gm, '<blockquote>$1</blockquote>')

  md = md.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
  md = md.replace(/__(.+?)__/g, '<strong>$1</strong>')
  md = md.replace(/\*(.+?)\*/g, '<em>$1</em>')
  md = md.replace(/_(.+?)_/g, '<em>$1</em>')

  md = md.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" />')

  md = md.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>')

  md = md.replace(/((?:^\s*[-*+]\s+.+\n?)+)/gm, block => {
    const items = block
      .trim()
      .split('\n')
      .map(l => l.replace(/^\s*[-*+]\s+/, ''))
      .map(l => `<li>${l}</li>`)
      .join('')
    return `<ul>${items}</ul>`
  })

  md = md.replace(/((?:^\s*\d+\.\s+.+\n?)+)/gm, block => {
    const items = block
      .trim()
      .split('\n')
      .map(l => l.replace(/^\s*\d+\.\s+/, ''))
      .map(l => `<li>${l}</li>`)
      .join('')
    return `<ol>${items}</ol>`
  })

  md = md
    .split(/\n{2,}/)
    .map(para => {
      if (/^\s*<(h[1-6]|ul|ol|blockquote|pre|img|figure|div|table|hr)/i.test(para)) return para
      if (/^@@CODEBLOCK\d+@@/.test(para.trim())) return para
      return `<p>${para.replace(/\n/g, '<br />')}</p>`
    })
    .join('\n')

  md = md.replace(/@@CODEBLOCK(\d+)@@/g, (_m, i) => codeBlocks[Number(i)] ?? '')

  return md
}

function escapeHtml(s: string): string {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}
