/**
 * URL publique du catalogue formations.
 * Les pages restent sous `app/(main)/tutorial/` ; Next.js réécrit `/formations` → `/tutorial`.
 */
export const FORMATIONS_PATH = '/formations' as const

/**
 * Console d’administration (URL non évidente — ne pas exposer dans la doc publique).
 * Ancien préfixe `/admin` n’existe plus comme route.
 */
export const ADMIN_CONSOLE_PATH = '/algocodebfadmin' as const
