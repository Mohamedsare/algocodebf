import { load } from 'cheerio'

const EB_BASE = 'https://www.emploiburkina.com'

export type ScrapedJobInsert = {
  title: string
  description: string
  type: 'stage' | 'emploi' | 'freelance' | 'hackathon' | 'formation'
  city: string
  external_link: string
  company_name: string | null
  company_logo: string | null
  skills_required: string | null
}

const UA =
  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'

function resolveUrl(href: string): string {
  if (!href) return EB_BASE
  if (href.startsWith('http')) return href
  return `${EB_BASE.replace(/\/$/, '')}/${href.replace(/^\//, '')}`
}

function cleanText(s: string): string {
  return s.replace(/\s+/g, ' ').trim()
}

function extractCity(regionText: string): string {
  const t = regionText || ''
  const cities = ['Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Ouahigouya'] as const
  for (const c of cities) {
    if (t.toLowerCase().includes(c.toLowerCase())) return c
  }
  return 'Ouagadougou'
}

function skillsToJson(competencesText: string): string | null {
  const raw = cleanText(competencesText)
  if (!raw) return null
  const parts = raw.split(/\s*[-–—]\s*|[,;]|\bet\b/gi).map(x => cleanText(x))
  const skills = [...new Set(parts.filter(s => s.length > 2))]
  return skills.length ? JSON.stringify(skills) : null
}

function ensureDescription(body: string): string {
  const t = cleanText(body) || "Offre importée depuis EmploiBurkina.com — ouvrez le lien externe pour l'intégralité du descriptif."
  if (t.length >= 50) return t
  return `${t} Consultez la fiche officielle pour les missions, prérequis et modalités de candidature.`
}

function listingUrl(pageIndex: number): string {
  const q = `${EB_BASE}/recherche-jobs-burkina-faso/Ouagadougou?f%5B0%5D=im_field_offre_metiers%3A31`
  if (pageIndex <= 0) return q
  return `${q}&page=${pageIndex}`
}

async function fetchHtml(url: string): Promise<string> {
  const res = await fetch(url, {
    cache: 'no-store',
    headers: {
      'User-Agent': UA,
      Accept: 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
      'Accept-Language': 'fr-FR,fr;q=0.9,en;q=0.8',
    },
    signal: AbortSignal.timeout(25_000),
  })
  if (!res.ok) throw new Error(`HTTP ${res.status} ${url}`)
  return res.text()
}

/**
 * Extraction alignée sur {@link algocodebf-php/app/Helpers/JobScraper.php} (DOM XPath → sélecteurs cheerio).
 */
export function parseEmploiBurkinaListingHtml(html: string): ScrapedJobInsert[] {
  const $ = load(html)
  const out: ScrapedJobInsert[] = []

  $('div.card.card-job').each((_, card) => {
    const el = $(card)
    const titleA = el.find('h3 a').first()
    const title = cleanText(titleA.text())
    if (!title || title.length < 5) return

    const href = titleA.attr('href') || el.attr('data-href') || ''
    const external_link = resolveUrl(href)

    const companyEl = el.find('a.card-job-company.company-name').first()
    const company_name = companyEl.length ? cleanText(companyEl.text()) : null

    const logoSrc = el.find('picture img').first().attr('src') || el.find('img').first().attr('src')
    const company_logo = logoSrc ? resolveUrl(logoSrc) : null

    const desc = cleanText(el.find('div.card-job-description p').first().text())

    let region = 'Ouagadougou'
    let typeContrat = ''
    let competences = ''

    el.find('ul li').each((_, li) => {
      const text = cleanText($(li).text())
      const strong = cleanText($(li).find('strong').first().text())
      if (/Niveau d.*études/i.test(text)) return
      if (/Niveau d.*expérience/i.test(text)) return
      if (/Contrat proposé/i.test(text)) typeContrat = strong
      else if (/Région de/i.test(text)) region = strong || region
      else if (/Compétences clés/i.test(text)) competences = strong
    })

    let type: ScrapedJobInsert['type'] = 'emploi'
    if (/freelance/i.test(typeContrat)) type = 'freelance'
    else if (/stage/i.test(typeContrat)) type = 'stage'

    if (/stage/i.test(title)) type = 'stage'
    else if (/hackathon/i.test(title)) type = 'hackathon'
    else if (/formation/i.test(title)) type = 'formation'

    out.push({
      title,
      description: ensureDescription(desc),
      type,
      city: extractCity(region),
      external_link,
      company_name,
      company_logo,
      skills_required: skillsToJson(competences),
    })
  })

  return out
}

export async function scrapeEmploiBurkinaItPages(pages = 2): Promise<ScrapedJobInsert[]> {
  const merged: ScrapedJobInsert[] = []
  const seen = new Set<string>()

  for (let i = 0; i < pages; i++) {
    const html = await fetchHtml(listingUrl(i))
    const jobs = parseEmploiBurkinaListingHtml(html)
    for (const j of jobs) {
      if (seen.has(j.external_link)) continue
      seen.add(j.external_link)
      merged.push(j)
    }
  }

  return merged
}
