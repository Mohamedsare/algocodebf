/** Nettoie la saisie pour limiter les injections de motif ILIKE et casser les `.or()` PostgREST. */
export function sanitizeSearchQuery(raw: string, maxLen = 80): string {
  return raw
    .trim()
    .slice(0, maxLen)
    .replace(/%/g, ' ')
    .replace(/_/g, ' ')
    .replace(/,/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
}
