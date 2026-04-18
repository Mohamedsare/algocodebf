// Extrait tous les <style>...</style> inline des vues PHP
// et les concatène dans /public/css/pages.css
import fs from 'node:fs'
import path from 'node:path'

const VIEWS = path.resolve('algocodebf-php/app/Views')
const OUT = path.resolve('public/css/pages.css')

function walk(dir) {
  const out = []
  for (const name of fs.readdirSync(dir)) {
    const p = path.join(dir, name)
    const st = fs.statSync(p)
    if (st.isDirectory()) out.push(...walk(p))
    else if (p.endsWith('.php')) out.push(p)
  }
  return out
}

const files = walk(VIEWS)
let buffer = `/* Styles inline extraits des vues PHP (algocodebf-php/app/Views/**).\n   Générés par scripts/extract-page-styles.mjs */\n\n`

for (const f of files) {
  const rel = path.relative(VIEWS, f).replace(/\\/g, '/')
  // Ignorer le header/footer (déjà couverts par style.css)
  if (rel.startsWith('layouts/')) continue
  const src = fs.readFileSync(f, 'utf8')
  const styleMatches = [...src.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)]
  if (styleMatches.length === 0) continue
  for (const m of styleMatches) {
    const body = m[1].trim()
    if (!body) continue
    buffer += `\n/* === ${rel} === */\n`
    buffer += body
    buffer += '\n'
  }
}

fs.writeFileSync(OUT, buffer, 'utf8')
console.log('Wrote', OUT, buffer.length, 'bytes')
