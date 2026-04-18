'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { z } from 'zod'
import { createClient } from '@/lib/supabase/server'
import { requireLogin, requirePermission } from '@/lib/auth'
import { setFlash } from '@/lib/flash'
import type { ActionResult } from '@/app/actions/users'

const projectSchema = z.object({
  title: z.string().min(5, 'Titre trop court (5 car. min)').max(200),
  description: z.string().min(20, 'Description trop courte (20 car. min)'),
  github_link: z.string().url('URL GitHub invalide').optional().or(z.literal('')),
  demo_link: z.string().url('URL Démo invalide').optional().or(z.literal('')),
  status: z.enum(['planning', 'active', 'in_progress', 'completed', 'paused', 'archived']),
  visibility: z.enum(['public', 'private']),
  looking_for_members: z.boolean().optional(),
})

function parse(fd: FormData) {
  return projectSchema.safeParse({
    title: fd.get('title')?.toString() ?? '',
    description: fd.get('description')?.toString() ?? '',
    github_link: fd.get('github_link')?.toString() ?? '',
    demo_link: fd.get('demo_link')?.toString() ?? '',
    status: fd.get('status')?.toString() ?? 'planning',
    visibility: fd.get('visibility')?.toString() ?? 'public',
    looking_for_members: fd.get('looking_for_members') !== null,
  })
}

function issuesToErrors(err: z.ZodError) {
  const errs: Record<string, string> = {}
  for (const i of err.issues) if (i.path[0]) errs[String(i.path[0])] = i.message
  return errs
}

export async function createProjectAction(formData: FormData): Promise<ActionResult<{ id: number }>> {
  const profile = await requirePermission('can_create_project')
  const supabase = await createClient()
  const parsed = parse(formData)
  if (!parsed.success) return { ok: false, errors: issuesToErrors(parsed.error), message: 'Formulaire invalide.' }

  const { data, error } = await supabase
    .from('projects')
    .insert({
      owner_id: profile.id,
      title: parsed.data.title,
      description: parsed.data.description,
      github_link: parsed.data.github_link || null,
      demo_link: parsed.data.demo_link || null,
      status: parsed.data.status,
      visibility: parsed.data.visibility,
      looking_for_members: parsed.data.looking_for_members ?? false,
    })
    .select('id')
    .single()

  if (error || !data) return { ok: false, message: error?.message ?? 'Création impossible.' }

  // L'owner est automatiquement ajouté comme membre actif
  await supabase
    .from('project_members')
    .insert({
      project_id: data.id,
      user_id: profile.id,
      role: 'Porteur de projet',
      status: 'active',
      joined_at: new Date().toISOString(),
    })
    .then(() => null)

  revalidatePath('/project')
  await setFlash('success', 'Projet créé.')
  return { ok: true, data: { id: data.id } }
}

export async function updateProjectAction(id: number, formData: FormData): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: existing } = await supabase.from('projects').select('owner_id').eq('id', id).maybeSingle()
  if (!existing) return { ok: false, message: 'Projet introuvable.' }
  if (existing.owner_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const parsed = parse(formData)
  if (!parsed.success) return { ok: false, errors: issuesToErrors(parsed.error), message: 'Formulaire invalide.' }

  const { error } = await supabase
    .from('projects')
    .update({
      title: parsed.data.title,
      description: parsed.data.description,
      github_link: parsed.data.github_link || null,
      demo_link: parsed.data.demo_link || null,
      status: parsed.data.status,
      visibility: parsed.data.visibility,
      looking_for_members: parsed.data.looking_for_members ?? false,
    })
    .eq('id', id)

  if (error) return { ok: false, message: error.message }

  revalidatePath(`/project/${id}`)
  revalidatePath('/project')
  await setFlash('success', 'Projet mis à jour.')
  return { ok: true }
}

export async function deleteProjectAction(id: number): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()
  const { data: existing } = await supabase.from('projects').select('owner_id').eq('id', id).maybeSingle()
  if (!existing) return { ok: false, message: 'Projet introuvable.' }
  if (existing.owner_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const { error } = await supabase.from('projects').update({ status: 'archived' }).eq('id', id)
  if (error) return { ok: false, message: error.message }

  revalidatePath('/project')
  await setFlash('success', 'Projet archivé.')
  redirect('/project')
}

// ------------------------------------------------------------
// Workflow : demande de participation
// ------------------------------------------------------------
const joinSchema = z.object({
  role: z.string().min(2).max(100).default('Contributeur'),
  motivation: z.string().max(2000).optional(),
})

export async function requestJoinProjectAction(
  projectId: number,
  formData: FormData
): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: project } = await supabase
    .from('projects')
    .select('id, title, owner_id')
    .eq('id', projectId)
    .maybeSingle()
  if (!project) return { ok: false, message: 'Projet introuvable.' }
  if (project.owner_id === profile.id) return { ok: false, message: 'Vous êtes déjà le porteur du projet.' }

  // Vérifie une adhésion / demande existante
  const { data: existing } = await supabase
    .from('project_members')
    .select('status')
    .eq('project_id', projectId)
    .eq('user_id', profile.id)
    .maybeSingle()
  if (existing?.status === 'active') return { ok: false, message: 'Vous êtes déjà membre.' }
  if (existing?.status === 'pending') return { ok: false, message: 'Une demande est déjà en attente.' }

  const parsed = joinSchema.safeParse({
    role: (formData.get('role')?.toString() || 'Contributeur'),
    motivation: formData.get('motivation')?.toString() ?? '',
  })
  if (!parsed.success) return { ok: false, message: 'Données invalides.' }

  // Upsert project_member en pending
  if (existing) {
    await supabase
      .from('project_members')
      .update({ status: 'pending', role: parsed.data.role })
      .eq('project_id', projectId)
      .eq('user_id', profile.id)
  } else {
    await supabase
      .from('project_members')
      .insert({ project_id: projectId, user_id: profile.id, role: parsed.data.role, status: 'pending' })
  }

  // Message au porteur avec action
  const body =
    `👋 Bonjour,\n\n` +
    `${profile.prenom} ${profile.nom} souhaite rejoindre votre projet « ${project.title} ».\n\n` +
    `📋 Rôle souhaité : ${parsed.data.role}\n\n` +
    (parsed.data.motivation ? `💬 Message :\n${parsed.data.motivation}\n\n` : '') +
    `Vous pouvez accepter ou refuser cette demande depuis votre boîte de messages.`

  await supabase.from('messages').insert({
    sender_id: profile.id,
    receiver_id: project.owner_id,
    subject: `Demande de participation — ${project.title}`,
    body,
    action_type: 'project_join_request',
    action_data: { project_id: projectId, user_id: profile.id, role: parsed.data.role },
    action_status: 'pending',
  })

  revalidatePath(`/project/${projectId}`)
  await setFlash('success', 'Demande envoyée au porteur du projet.')
  return { ok: true }
}

export async function respondJoinRequestAction(
  messageId: number,
  decision: 'accepted' | 'rejected'
): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: msg } = await supabase
    .from('messages')
    .select('id, receiver_id, sender_id, action_type, action_data, action_status')
    .eq('id', messageId)
    .maybeSingle()
  if (!msg) return { ok: false, message: 'Message introuvable.' }
  if (msg.receiver_id !== profile.id) return { ok: false, message: 'Action non autorisée.' }
  if (msg.action_type !== 'project_join_request' || msg.action_status !== 'pending')
    return { ok: false, message: 'Cette action n\'est plus disponible.' }

  const data = msg.action_data as { project_id: number; user_id: string; role: string } | null
  if (!data) return { ok: false, message: 'Données manquantes.' }

  await supabase.from('messages').update({ action_status: decision }).eq('id', messageId)

  if (decision === 'accepted') {
    await supabase
      .from('project_members')
      .update({ status: 'active', joined_at: new Date().toISOString() })
      .eq('project_id', data.project_id)
      .eq('user_id', data.user_id)
  } else {
    await supabase
      .from('project_members')
      .update({ status: 'rejected' })
      .eq('project_id', data.project_id)
      .eq('user_id', data.user_id)
  }

  // Notification retour
  await supabase.from('messages').insert({
    sender_id: profile.id,
    receiver_id: data.user_id,
    subject:
      decision === 'accepted'
        ? 'Votre demande a été acceptée !'
        : 'Votre demande a été refusée',
    body:
      decision === 'accepted'
        ? `Félicitations ! Vous avez été accepté(e) comme ${data.role} sur le projet.`
        : `Votre demande de rejoindre le projet a été refusée.`,
  })

  revalidatePath('/message/inbox')
  revalidatePath(`/project/${data.project_id}`)
  return { ok: true }
}
