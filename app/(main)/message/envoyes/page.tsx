import type { Metadata } from 'next'
import { requireLogin } from '@/lib/auth'
import { getSent, getMessage, unreadCount } from '@/lib/queries/messages'
import { MessagingShell } from '@/components/message/messaging-shell'
import { MessageViewPane } from '@/components/message/message-view-pane'

export const metadata: Metadata = { title: 'Messages envoyés - AlgoCodeBF' }

interface Props {
  searchParams: Promise<{ show?: string }>
}

export default async function SentPage({ searchParams }: Props) {
  const profile = await requireLogin()
  const params = await searchParams

  const [messages, unread] = await Promise.all([
    getSent(profile.id),
    unreadCount(profile.id),
  ])

  const showId = params.show ? Number(params.show) : null
  const selected =
    showId && !Number.isNaN(showId)
      ? await getMessage(showId, profile.id)
      : null

  return (
    <MessagingShell
      mode="sent"
      messages={messages}
      unreadCount={unread}
      currentUserId={profile.id}
      selectedMessage={selected}
    >
      {selected && (
        <MessageViewPane
          message={selected}
          mode="sent"
          currentUserId={profile.id}
        />
      )}
    </MessagingShell>
  )
}
