/**
 * Coloration syntaxique des blocs de code (fenced) pour markdownToHtml.
 * highlight.js en mode « core » + langues courantes pour limiter le poids.
 */

import hljs from 'highlight.js/lib/core'
import bash from 'highlight.js/lib/languages/bash'
import c from 'highlight.js/lib/languages/c'
import cpp from 'highlight.js/lib/languages/cpp'
import csharp from 'highlight.js/lib/languages/csharp'
import css from 'highlight.js/lib/languages/css'
import diff from 'highlight.js/lib/languages/diff'
import go from 'highlight.js/lib/languages/go'
import java from 'highlight.js/lib/languages/java'
import javascript from 'highlight.js/lib/languages/javascript'
import json from 'highlight.js/lib/languages/json'
import kotlin from 'highlight.js/lib/languages/kotlin'
import markdown from 'highlight.js/lib/languages/markdown'
import php from 'highlight.js/lib/languages/php'
import plaintext from 'highlight.js/lib/languages/plaintext'
import python from 'highlight.js/lib/languages/python'
import ruby from 'highlight.js/lib/languages/ruby'
import rust from 'highlight.js/lib/languages/rust'
import sql from 'highlight.js/lib/languages/sql'
import swift from 'highlight.js/lib/languages/swift'
import typescript from 'highlight.js/lib/languages/typescript'
import xml from 'highlight.js/lib/languages/xml'
import yaml from 'highlight.js/lib/languages/yaml'

let initialized = false

/** Langues passées à highlightAuto quand aucune langue n’est indiquée après ``` */
const AUTO_LANG_SUBSET = [
  'javascript',
  'typescript',
  'python',
  'bash',
  'c',
  'cpp',
  'csharp',
  'java',
  'go',
  'rust',
  'sql',
  'css',
  'json',
  'yaml',
  'php',
  'markdown',
  'xml',
  'kotlin',
  'swift',
  'ruby',
] as const

function initHighlight() {
  if (initialized) return
  initialized = true

  hljs.registerLanguage('javascript', javascript)
  hljs.registerLanguage('js', javascript)
  hljs.registerLanguage('typescript', typescript)
  hljs.registerLanguage('ts', typescript)
  hljs.registerLanguage('python', python)
  hljs.registerLanguage('py', python)
  hljs.registerLanguage('bash', bash)
  hljs.registerLanguage('sh', bash)
  hljs.registerLanguage('shell', bash)
  hljs.registerLanguage('zsh', bash)
  hljs.registerLanguage('c', c)
  hljs.registerLanguage('cpp', cpp)
  hljs.registerLanguage('csharp', csharp)
  hljs.registerLanguage('cs', csharp)
  hljs.registerLanguage('java', java)
  hljs.registerLanguage('go', go)
  hljs.registerLanguage('rust', rust)
  hljs.registerLanguage('sql', sql)
  hljs.registerLanguage('html', xml)
  hljs.registerLanguage('xml', xml)
  hljs.registerLanguage('css', css)
  hljs.registerLanguage('json', json)
  hljs.registerLanguage('yaml', yaml)
  hljs.registerLanguage('yml', yaml)
  hljs.registerLanguage('php', php)
  hljs.registerLanguage('markdown', markdown)
  hljs.registerLanguage('md', markdown)
  hljs.registerLanguage('diff', diff)
  hljs.registerLanguage('kotlin', kotlin)
  hljs.registerLanguage('kt', kotlin)
  hljs.registerLanguage('swift', swift)
  hljs.registerLanguage('ruby', ruby)
  hljs.registerLanguage('rb', ruby)
  hljs.registerLanguage('plaintext', plaintext)
  hljs.registerLanguage('text', plaintext)
  hljs.registerLanguage('txt', plaintext)
}

function escapeHtml(s: string): string {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

function resolveLang(raw?: string): string | null {
  if (!raw) return null
  const l = raw.toLowerCase().trim()
  if (!l) return null
  if (hljs.getLanguage(l)) return l
  const aliases: Record<string, string> = {
    golang: 'go',
    rs: 'rust',
    jsx: 'javascript',
    tsx: 'typescript',
    vue: 'xml',
    http: 'plaintext',
  }
  const mapped = aliases[l]
  if (mapped && hljs.getLanguage(mapped)) return mapped
  return null
}

/**
 * Retourne un <pre><code class="hljs …">…</code></pre> avec surlignage.
 */
export function highlightCodeFence(code: string, langRaw?: string): string {
  initHighlight()
  const text = code.trimEnd()

  try {
    const resolved = resolveLang(langRaw)
    if (resolved) {
      const { value } = hljs.highlight(text, { language: resolved, ignoreIllegals: true })
      return `<pre class="hljs-code-block"><code class="hljs language-${resolved}">${value}</code></pre>`
    }

    if (!text) {
      return '<pre class="hljs-code-block"><code class="hljs"></code></pre>'
    }

    const auto = hljs.highlightAuto(text, [...AUTO_LANG_SUBSET])
    const lang = auto.language ?? 'plaintext'
    return `<pre class="hljs-code-block"><code class="hljs language-${lang}">${auto.value}</code></pre>`
  } catch {
    return `<pre class="hljs-code-block"><code class="hljs">${escapeHtml(text)}</code></pre>`
  }
}
