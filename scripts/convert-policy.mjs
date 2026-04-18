// Convertit les vues PHP de policy en HTML partial statique pour Next.js
// Usage : node scripts/convert-policy.mjs
import fs from 'node:fs'
import path from 'node:path'

const SRC = path.resolve('algocodebf-php/app/Views/policy')
const OUT = path.resolve('content/policy')
fs.mkdirSync(OUT, { recursive: true })

const today = new Date()
const DD = String(today.getDate()).padStart(2, '0')
const MM = String(today.getMonth() + 1).padStart(2, '0')
const YYYY = today.getFullYear()
const dateFR = `${DD}/${MM}/${YYYY}`

const files = ['privacy.php', 'terms.php', 'legal.php']

for (const f of files) {
  const src = fs.readFileSync(path.join(SRC, f), 'utf8')
  let out = src
  // Supprimer le bloc PHP d'en-tête (require header + $pageTitle)
  out = out.replace(/<\?php[\s\S]*?\?>\s*/g, '')
  // Remplacer les sorties simples <?= ... ?>
  out = out.replace(/<\?=\s*date\([^)]*\)\s*\?>/g, dateFR)
  out = out.replace(/<\?=\s*BASE_URL\s*\?>/g, '')
  out = out.replace(/<\?=[\s\S]*?\?>/g, '')
  const name = f.replace('.php', '.html')
  fs.writeFileSync(path.join(OUT, name), out, 'utf8')
  console.log('Wrote', name, '(' + out.length + ' bytes)')
}
