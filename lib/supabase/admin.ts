import { createClient, type SupabaseClient } from '@supabase/supabase-js'

/** Lit le champ `role` du JWT Supabase (sans vérifier la signature — suffisant pour distinguer anon vs service_role). */
export function readSupabaseJwtRole(apiKey: string): string | null {
  try {
    const parts = apiKey.trim().split('.')
    if (parts.length < 2) return null
    const b64 = parts[1].replace(/-/g, '+').replace(/_/g, '/')
    const pad = b64.length % 4 ? '='.repeat(4 - (b64.length % 4)) : ''
    const json = Buffer.from(b64 + pad, 'base64').toString('utf8')
    const payload = JSON.parse(json) as { role?: string }
    return typeof payload.role === 'string' ? payload.role : null
  } catch {
    return null
  }
}

export type AdminClientResult =
  | { ok: true; client: SupabaseClient }
  | { ok: false; message: string }

/**
 * Client service role — contourne la RLS (import offres scrapées, crons).
 * Refuse une clé `anon` / `authenticated` (sinon insert → erreur RLS sur `jobs`).
 */
export function createAdminClient(): AdminClientResult {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL?.trim()
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY?.trim()
  if (!url) return { ok: false, message: 'NEXT_PUBLIC_SUPABASE_URL manquant.' }
  if (!key) return { ok: false, message: 'SUPABASE_SERVICE_ROLE_KEY manquant.' }

  const role = readSupabaseJwtRole(key)
  if (role !== 'service_role') {
    return {
      ok: false,
      message:
        `SUPABASE_SERVICE_ROLE_KEY doit être la clé secrète « service_role » (dashboard Supabase → Settings → API). ` +
        `Le JWT actuel a role="${role ?? 'inconnu'}" — souvent la clé « anon » a été collée par erreur. ` +
        `Avec la bonne clé, la RLS est contournée et l’import fonctionne.`,
    }
  }

  return {
    ok: true,
    client: createClient(url, key, {
      auth: { persistSession: false, autoRefreshToken: false },
    }),
  }
}
