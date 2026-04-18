import { cookies } from 'next/headers'

/**
 * Système de messages flash simple via cookie httpOnly court.
 * Équivalent des flash messages `$_SESSION['flash']` du projet PHP d'origine.
 *
 * Les messages sont consommés une seule fois : après `getFlashes()`, ils
 * sont effacés. Utile après une server action (redirect), pour afficher
 * un message de succès/erreur dans le layout au prochain render.
 */

export type FlashType = 'success' | 'error' | 'info' | 'warning'

export interface FlashMessage {
  type: FlashType
  message: string
}

const COOKIE_NAME = 'bf_flash'

export async function setFlash(type: FlashType, message: string) {
  const store = await cookies()
  const existing = store.get(COOKIE_NAME)?.value
  let queue: FlashMessage[] = []
  if (existing) {
    try {
      const parsed = JSON.parse(existing)
      if (Array.isArray(parsed)) queue = parsed
    } catch {
      /* reset */
    }
  }
  queue.push({ type, message })
  store.set(COOKIE_NAME, JSON.stringify(queue), {
    httpOnly: true,
    sameSite: 'lax',
    path: '/',
    maxAge: 60,
  })
}

export async function getFlashes(): Promise<FlashMessage[]> {
  const store = await cookies()
  const raw = store.get(COOKIE_NAME)?.value
  if (!raw) return []
  try {
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) return []
    store.delete(COOKIE_NAME)
    return parsed
  } catch {
    store.delete(COOKIE_NAME)
    return []
  }
}
