import type { Metadata } from 'next'
import { requireLogin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { getInbox, getMessage, unreadCount } from '@/lib/queries/messages'
import { MessagingShell } from '@/components/message/messaging-shell'
import { MessageViewPane } from '@/components/message/message-view-pane'

export const metadata: Metadata = { title: 'Messagerie - AlgoCodeBF' }

interface Props {
  searchParams: Promise<{ show?: string }>
}

export default async function InboxPage({ searchParams }: Props) {
  const profile = await requireLogin()
  const params = await searchParams
  const showId = params.show ? Number(params.show) : null

  if (showId && !Number.isNaN(showId)) {
    const supabase = await createClient()
    await supabase
      .from('messages')
      .update({ is_read: true })
      .eq('id', showId)
      .eq('receiver_id', profile.id)
      .eq('is_read', false)
  }

  const [messages, unread, selected] = await Promise.all([
    getInbox(profile.id),
    unreadCount(profile.id),
    showId && !Number.isNaN(showId)
      ? getMessage(showId, profile.id)
      : Promise.resolve(null),
  ])

  return (
    <MessagingShell
      mode="inbox"
      messages={messages}
      unreadCount={unread}
      currentUserId={profile.id}
      selectedMessage={selected}
    >
      {selected && (
        <MessageViewPane
          message={selected}
          mode="inbox"
          currentUserId={profile.id}
        />
      )}
    </MessagingShell>
  )
}
