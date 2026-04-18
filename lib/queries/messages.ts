import { createClient } from '@/lib/supabase/server'
import type { Message } from '@/types'

export interface MessageWithParties extends Omit<Message, 'sender' | 'receiver'> {
  sender: { id: string; prenom: string; nom: string; photo_path: string | null } | null
  receiver: { id: string; prenom: string; nom: string; photo_path: string | null } | null
}

export async function getInbox(userId: string): Promise<MessageWithParties[]> {
  const supabase = await createClient()
  const { data, error } = await supabase
    .from('messages')
    .select(
      `id, sender_id, receiver_id, subject, body, action_type, action_data, action_status,
       is_read, is_deleted_by_sender, is_deleted_by_receiver, created_at,
       sender:profiles!messages_sender_id_fkey(id, prenom, nom, photo_path),
       receiver:profiles!messages_receiver_id_fkey(id, prenom, nom, photo_path)`
    )
    .eq('receiver_id', userId)
    .eq('is_deleted_by_receiver', false)
    .order('created_at', { ascending: false })
  if (error) throw error
  return (data ?? []) as unknown as MessageWithParties[]
}

export async function getSent(userId: string): Promise<MessageWithParties[]> {
  const supabase = await createClient()
  const { data, error } = await supabase
    .from('messages')
    .select(
      `id, sender_id, receiver_id, subject, body, action_type, action_data, action_status,
       is_read, is_deleted_by_sender, is_deleted_by_receiver, created_at,
       sender:profiles!messages_sender_id_fkey(id, prenom, nom, photo_path),
       receiver:profiles!messages_receiver_id_fkey(id, prenom, nom, photo_path)`
    )
    .eq('sender_id', userId)
    .eq('is_deleted_by_sender', false)
    .is('action_type', null) // masquer les messages système automatiques
    .order('created_at', { ascending: false })
  if (error) throw error
  return (data ?? []) as unknown as MessageWithParties[]
}

export async function getMessage(id: number, userId: string): Promise<MessageWithParties | null> {
  const supabase = await createClient()
  const { data } = await supabase
    .from('messages')
    .select(
      `id, sender_id, receiver_id, subject, body, action_type, action_data, action_status,
       is_read, is_deleted_by_sender, is_deleted_by_receiver, created_at,
       sender:profiles!messages_sender_id_fkey(id, prenom, nom, photo_path),
       receiver:profiles!messages_receiver_id_fkey(id, prenom, nom, photo_path)`
    )
    .eq('id', id)
    .maybeSingle()
  if (!data) return null
  const msg = data as unknown as MessageWithParties
  if (msg.sender_id !== userId && msg.receiver_id !== userId) return null
  return msg
}

export async function unreadCount(userId: string): Promise<number> {
  const supabase = await createClient()
  const { count } = await supabase
    .from('messages')
    .select('*', { count: 'exact', head: true })
    .eq('receiver_id', userId)
    .eq('is_read', false)
    .eq('is_deleted_by_receiver', false)
  return count ?? 0
}
