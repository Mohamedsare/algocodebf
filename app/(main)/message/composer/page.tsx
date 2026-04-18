import type { Metadata } from 'next'
import { requireLogin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { ComposeClient } from '@/components/message/compose-client'

export const metadata: Metadata = { title: 'Nouveau Message - AlgoCodeBF' }

interface Props {
  searchParams: Promise<{
    receiver?: string
    subject?: string
    reply_to?: string
    // legacy aliases
    to?: string
    re?: string
  }>
}

export default async function ComposePage({ searchParams }: Props) {
  await requireLogin()
  const params = await searchParams
  const supabase = await createClient()

  const receiverId = params.receiver ?? params.to ?? null
  const replyTo = params.reply_to ? Number(params.reply_to) : null

  let receiver = null
  if (receiverId) {
    const { data } = await supabase
      .from('profiles')
      .select('id, prenom, nom, photo_path, university')
      .eq('id', receiverId)
      .maybeSingle()
    receiver = data ?? null
  }

  const rawSubject = params.subject ?? params.re ?? ''
  const defaultSubject = rawSubject
    ? rawSubject.startsWith('Re:') || !replyTo
      ? rawSubject
      : `Re: ${rawSubject}`
    : ''

  return (
    <ComposeClient
      receiver={receiver}
      replyTo={replyTo && !Number.isNaN(replyTo) ? replyTo : null}
      defaultSubject={defaultSubject}
    />
  )
}
