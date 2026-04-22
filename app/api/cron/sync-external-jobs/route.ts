import { NextResponse } from 'next/server'
import { revalidatePath } from 'next/cache'
import { syncExternalJobOffers } from '@/lib/jobs/sync-scraped-jobs'

/** Vercel / plateforme : jusqu’à 5 min pour scraping + upserts */
export const maxDuration = 300

/** Nettoie les valeurs .env (Windows \r, guillemets, espaces invisibles). */
function normalizeCronSecret(raw: string | undefined): string {
  if (!raw) return ''
  let s = raw.replace(/\r/g, '').replace(/\uFEFF/g, '').trim()
  if ((s.startsWith('"') && s.endsWith('"')) || (s.startsWith("'") && s.endsWith("'"))) {
    s = s.slice(1, -1).trim()
  }
  return s
}

function normalizeIncomingToken(raw: string | null | undefined): string {
  if (!raw) return ''
  return raw.replace(/\r/g, '').replace(/\uFEFF/g, '').trim()
}

function extractBearerToken(authorization: string | null): string | null {
  if (!authorization) return null
  const m = authorization.replace(/\r/g, '').trim().match(/^Bearer\s+(.+)$/i)
  return m?.[1] ? normalizeIncomingToken(m[1]) : null
}

/**
 * Synchronisation planifiée des offres externes (EmploiBurkina IT).
 *
 * Auth :
 * - `Authorization: Bearer <CRON_SECRET>` (Vercel Cron)
 * - `x-cron-secret: <CRON_SECRET>` (curl)
 * - **Dev uniquement** : `?cron_secret=<CRON_SECRET>` (test navigateur — jamais en prod / partage)
 *
 * Paramètre : `?pages=1` à `5` (défaut 3). Redémarrer `npm run dev` après changement de `.env.local`.
 */
export async function GET(request: Request) {
  const secret = normalizeCronSecret(process.env.CRON_SECRET)
  if (!secret) {
    return NextResponse.json(
      { ok: false, error: 'unauthorized', hint: 'CRON_SECRET manquant dans les variables d’environnement.' },
      { status: 401 }
    )
  }

  const bearer = extractBearerToken(request.headers.get('authorization'))
  const headerSecret = normalizeIncomingToken(request.headers.get('x-cron-secret'))
  const urlForQuery = new URL(request.url)
  const querySecret =
    process.env.NODE_ENV === 'development'
      ? normalizeIncomingToken(urlForQuery.searchParams.get('cron_secret'))
      : ''

  const token = bearer || headerSecret || querySecret || null
  if (!token || token !== secret) {
    const body: Record<string, unknown> = {
      ok: false,
      error: 'unauthorized',
      hint:
        'Le secret envoyé ne correspond pas à CRON_SECRET côté serveur. Vérifie .env.local + redémarrage de `npm run dev`, ou que tu appelles le même hôte que ce serveur.',
      hintPowerShell:
        'Dans PowerShell, $env:CRON_SECRET n’est pas lu depuis .env.local (c’est Next qui le charge). Colle la valeur dans la commande, ou fais : $env:CRON_SECRET="ta-valeur-exacte"',
      hintDevBrowser:
        process.env.NODE_ENV === 'development'
          ? 'En local uniquement : ouvre http://localhost:3000/api/cron/sync-external-jobs?cron_secret=COLLE_TON_SECRET (ne partage jamais cette URL, n’utilise pas en prod).'
          : undefined,
    }
    if (process.env.NODE_ENV === 'development') {
      body.diagnostic = {
        receivedToken: Boolean(token),
        tokenLength: token?.length ?? 0,
        secretLength: secret.length,
      }
    }
    return NextResponse.json(body, { status: 401 })
  }

  const pages = Math.min(5, Math.max(1, parseInt(urlForQuery.searchParams.get('pages') ?? '3', 10) || 3))

  const result = await syncExternalJobOffers({ pages })

  if (result.ok) {
    revalidatePath('/job')
    revalidatePath('/search')
    revalidatePath('/sitemap.xml')
  }

  return NextResponse.json(result)
}
