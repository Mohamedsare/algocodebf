import { cookies } from 'next/headers'

/**
 * Système de messages flash simple via cookie httpOnly court.
 * Équivalent des flash messages `$_SESSION['flash']` du projet PHP d'origine.
 *
 * Les messages sont consommés une seule fois : après affichage, un composant
 * client appelle `consumeFlashCookie` depuis `app/actions/flash.ts`, car Next.js
 * n’autorise pas la modification des cookies pendant le rendu d’un Server Component.
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

/** Lecture seule pour le rendu RSC (aucune mutation du cookie). */
export async function getFlashMessagesForRender(): Promise<{
  messages: FlashMessage[]
  shouldClearCookie: boolean
}> {
  const store = await cookies()
  const raw = store.get(COOKIE_NAME)?.value
  if (!raw) return { messages: [], shouldClearCookie: false }
  try {
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) {
      return { messages: [], shouldClearCookie: true }
    }
    return { messages: parsed as FlashMessage[], shouldClearCookie: true }
  } catch {
    return { messages: [], shouldClearCookie: true }
  }
}
