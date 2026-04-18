'use server'

import { revalidatePath } from 'next/cache'
import { z } from 'zod'
import { createClient } from '@/lib/supabase/server'
import { requireLogin } from '@/lib/auth'
import { setFlash } from '@/lib/flash'
import type { ActionResult } from '@/app/actions/users'

const messageSchema = z.object({
  receiver_id: z.string().uuid('Destinataire invalide'),
  subject: z.string().min(1, 'Sujet requis').max(200),
  body: z.string().min(10, 'Le message doit contenir au moins 10 caractères').max(5000),
})

export async function sendMessageAction(formData: FormData): Promise<ActionResult<{ id: number }>> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const parsed = messageSchema.safeParse({
    receiver_id: formData.get('receiver_id')?.toString() ?? '',
    subject: (formData.get('subject')?.toString() ?? '').trim() || 'Sans sujet',
    body: (formData.get('body')?.toString() ?? '').trim(),
  })
  if (!parsed.success) {
    return { ok: false, message: parsed.error.issues[0]?.message ?? 'Formulaire invalide.' }
  }
  if (parsed.data.receiver_id === profile.id) {
    return { ok: false, message: 'Vous ne pouvez pas vous envoyer un message.' }
  }

  const { data, error } = await supabase
    .from('messages')
    .insert({
      sender_id: profile.id,
      receiver_id: parsed.data.receiver_id,
      subject: parsed.data.subject,
      body: parsed.data.body,
    })
    .select('id')
    .single()

  if (error || !data) return { ok: false, message: error?.message ?? 'Envoi impossible.' }

  revalidatePath('/message')
  revalidatePath('/message/envoyes')
  await setFlash('success', 'Message envoyé.')
  return { ok: true, data: { id: data.id } }
}

export async function markAsReadAction(messageId: number): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()
  const { error } = await supabase
    .from('messages')
    .update({ is_read: true })
    .eq('id', messageId)
    .eq('receiver_id', profile.id)
  if (error) return { ok: false, message: error.message }
  revalidatePath('/message')
  return { ok: true }
}

export async function deleteMessageAction(messageId: number): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: msg } = await supabase
    .from('messages')
    .select('sender_id, receiver_id')
    .eq('id', messageId)
    .maybeSingle()
  if (!msg) return { ok: false, message: 'Message introuvable.' }

  const update: Record<string, boolean> = {}
  if (msg.sender_id === profile.id) update.is_deleted_by_sender = true
  if (msg.receiver_id === profile.id) update.is_deleted_by_receiver = true
  if (Object.keys(update).length === 0) return { ok: false, message: 'Action non autorisée.' }

  const { error } = await supabase.from('messages').update(update).eq('id', messageId)
  if (error) return { ok: false, message: error.message }

  revalidatePath('/message')
  revalidatePath('/message/envoyes')
  return { ok: true }
}
