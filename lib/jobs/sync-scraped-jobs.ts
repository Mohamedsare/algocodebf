import type { SupabaseClient } from '@supabase/supabase-js'
import { createAdminClient } from '@/lib/supabase/admin'
import { scrapeEmploiBurkinaItPages, type ScrapedJobInsert } from '@/lib/jobs/scrape-emploiburkina'

const DEFAULT_PAGES = 3

function rowsForInsert(rows: ScrapedJobInsert[]) {
  return rows.map(r => ({
    company_id: null as string | null,
    company_name: r.company_name,
    company_logo: r.company_logo,
    title: r.title,
    description: r.description,
    type: r.type,
    city: r.city,
    salary: null as string | null,
    deadline: null as string | null,
    external_link: r.external_link,
    skills_required: r.skills_required,
    status: 'active' as const,
    is_scraped: true,
    views: 0,
  }))
}

async function fetchExistingExternalLinks(admin: SupabaseClient, links: string[]): Promise<Set<string>> {
  const unique = [...new Set(links.filter(Boolean))]
  const existing = new Set<string>()
  const chunk = 80
  for (let i = 0; i < unique.length; i += chunk) {
    const slice = unique.slice(i, i + chunk)
    const { data, error } = await admin.from('jobs').select('external_link').in('external_link', slice)
    if (error) throw error
    for (const row of data ?? []) {
      if (row.external_link) existing.add(row.external_link)
    }
  }
  return existing
}

export type SyncScrapedJobsResult = {
  ok: boolean
  inserted: number
  scraped: number
  message?: string
}

/**
 * Scrape EmploiBurkina (IT Ouaga) et insère les nouvelles offres dans `jobs`.
 * Dédoublonnage : lecture des `external_link` déjà en base puis `insert` (compatible sans index ON CONFLICT).
 */
export async function syncExternalJobOffers(opts?: { pages?: number }): Promise<SyncScrapedJobsResult> {
  const pages = opts?.pages ?? DEFAULT_PAGES
  const adminRes = createAdminClient()
  if (!adminRes.ok) {
    return {
      ok: false,
      inserted: 0,
      scraped: 0,
      message: adminRes.message,
    }
  }
  const admin = adminRes.client

  let scraped: ScrapedJobInsert[]
  try {
    scraped = await scrapeEmploiBurkinaItPages(pages)
  } catch (e) {
    const msg = e instanceof Error ? e.message : String(e)
    return { ok: false, inserted: 0, scraped: 0, message: msg }
  }

  if (scraped.length === 0) {
    return { ok: true, inserted: 0, scraped: 0 }
  }

  const links = scraped.map(j => j.external_link)
  let existing: Set<string>
  try {
    existing = await fetchExistingExternalLinks(admin, links)
  } catch (e) {
    const msg = e instanceof Error ? e.message : String(e)
    return { ok: false, inserted: 0, scraped: scraped.length, message: msg }
  }

  const fresh = scraped.filter(j => !existing.has(j.external_link))
  if (fresh.length === 0) {
    return { ok: true, inserted: 0, scraped: scraped.length }
  }

  const payload = rowsForInsert(fresh)
  let inserted = 0
  const insertChunk = 40

  for (let i = 0; i < payload.length; i += insertChunk) {
    const part = payload.slice(i, i + insertChunk)
    const { data, error } = await admin.from('jobs').insert(part).select('id')
    if (error) {
      return { ok: false, inserted, scraped: scraped.length, message: error.message }
    }
    inserted += data?.length ?? 0
  }

  return { ok: true, inserted, scraped: scraped.length }
}
