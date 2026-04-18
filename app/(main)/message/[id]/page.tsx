import { redirect } from 'next/navigation'
import { requireLogin } from '@/lib/auth'
import { getMessage } from '@/lib/queries/messages'

export default async function MessageDetailPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const profile = await requireLogin()
  const msg = await getMessage(Number(id), profile.id)
  if (!msg) redirect('/message')
  const base =
    msg.receiver_id === profile.id ? '/message' : '/message/envoyes'
  redirect(`${base}?show=${msg.id}`)
}
