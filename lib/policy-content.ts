import fs from 'node:fs'
import path from 'node:path'

/**
 * Lit le HTML d'une page policy portée depuis le PHP.
 * Les fichiers sont générés par `scripts/convert-policy.mjs`.
 */
export function readPolicyHtml(name: 'privacy' | 'terms' | 'legal'): string {
  const file = path.join(process.cwd(), 'content', 'policy', `${name}.html`)
  try {
    return fs.readFileSync(file, 'utf8')
  } catch {
    return ''
  }
}
