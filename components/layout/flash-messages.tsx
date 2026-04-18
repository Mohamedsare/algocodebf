import { getFlashMessagesForRender } from '@/lib/flash'
import { FlashMessagesClient } from '@/components/layout/flash-messages-client'

/**
 * Alertes Flash au format PHP original (.alert .alert-success / .alert-error).
 * Le cookie est effacé après hydratation via Server Action (contrainte Next.js 16).
 */
export async function FlashMessages() {
  const { messages, shouldClearCookie } = await getFlashMessagesForRender()
  if (messages.length === 0 && !shouldClearCookie) return null
  return <FlashMessagesClient messages={messages} clearCookie={shouldClearCookie} />
}
