'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { z } from 'zod'
import { createClient } from '@/lib/supabase/server'
import { requireLogin, requireRole } from '@/lib/auth'
import { setFlash } from '@/lib/flash'
import type { ActionResult } from '@/app/actions/users'
import type { ApplicationStatus } from '@/types'

const jobSchema = z.object({
  title: z.string().min(5, 'Titre trop court').max(200),
  description: z.string().min(50, 'Description trop courte (50 car. min)'),
  type: z.enum(['job', 'internship', 'hackathon', 'stage', 'emploi', 'freelance', 'formation']),
  city: z.string().min(2, 'Ville requise').max(100),
  salary: z.string().max(100).optional(),
  deadline: z.string().optional(),
  external_link: z.string().url('URL invalide').optional().or(z.literal('')),
  company_name: z.string().max(150).optional(),
  skills_required: z.string().max(2000).optional(),
})

function parse(fd: FormData) {
  return jobSchema.safeParse({
    title: fd.get('title')?.toString() ?? '',
    description: fd.get('description')?.toString() ?? '',
    type: fd.get('type')?.toString() ?? 'emploi',
    city: fd.get('city')?.toString() ?? '',
    salary: fd.get('salary')?.toString() ?? '',
    deadline: fd.get('deadline')?.toString() ?? '',
    external_link: fd.get('external_link')?.toString() ?? '',
    company_name: fd.get('company_name')?.toString() ?? '',
    skills_required: fd.get('skills_required')?.toString() ?? '',
  })
}

function issuesToErrors(err: z.ZodError) {
  const errs: Record<string, string> = {}
  for (const i of err.issues) if (i.path[0]) errs[String(i.path[0])] = i.message
  return errs
}

export async function createJobAction(formData: FormData): Promise<ActionResult<{ id: number }>> {
  const profile = await requireRole(['company', 'admin'])
  const supabase = await createClient()

  const parsed = parse(formData)
  if (!parsed.success) return { ok: false, errors: issuesToErrors(parsed.error), message: 'Formulaire invalide.' }

  const payload = {
    company_id: profile.id,
    company_name: parsed.data.company_name?.trim() || `${profile.prenom} ${profile.nom}`,
    title: parsed.data.title,
    description: parsed.data.description,
    type: parsed.data.type,
    city: parsed.data.city,
    salary: parsed.data.salary || null,
    deadline: parsed.data.deadline || null,
    external_link: parsed.data.external_link || null,
    skills_required: parsed.data.skills_required?.trim() || null,
    status: 'active' as const,
    is_scraped: false,
  }

  const { data, error } = await supabase.from('jobs').insert(payload).select('id').single()
  if (error || !data) return { ok: false, message: error?.message ?? 'Création impossible.' }

  revalidatePath('/job')
  await setFlash('success', 'Offre publiée.')
  return { ok: true, data: { id: data.id } }
}

export async function updateJobAction(id: number, formData: FormData): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: existing } = await supabase.from('jobs').select('company_id').eq('id', id).maybeSingle()
  if (!existing) return { ok: false, message: 'Offre introuvable.' }
  if (existing.company_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const parsed = parse(formData)
  if (!parsed.success) return { ok: false, errors: issuesToErrors(parsed.error), message: 'Formulaire invalide.' }

  const { error } = await supabase
    .from('jobs')
    .update({
      title: parsed.data.title,
      description: parsed.data.description,
      type: parsed.data.type,
      city: parsed.data.city,
      salary: parsed.data.salary || null,
      deadline: parsed.data.deadline || null,
      external_link: parsed.data.external_link || null,
      company_name: parsed.data.company_name?.trim() || null,
      skills_required: parsed.data.skills_required?.trim() || null,
    })
    .eq('id', id)

  if (error) return { ok: false, message: error.message }

  revalidatePath(`/job/${id}`)
  revalidatePath('/job')
  await setFlash('success', 'Offre mise à jour.')
  return { ok: true }
}

export async function closeJobAction(id: number): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()
  const { data: existing } = await supabase.from('jobs').select('company_id').eq('id', id).maybeSingle()
  if (!existing) return { ok: false, message: 'Offre introuvable.' }
  if (existing.company_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const { error } = await supabase.from('jobs').update({ status: 'closed' }).eq('id', id)
  if (error) return { ok: false, message: error.message }

  revalidatePath('/job')
  await setFlash('success', 'Offre fermée.')
  redirect('/job')
}

// ------------------------------------------------------------
// Candidatures
// ------------------------------------------------------------
export async function applyToJobAction(jobId: number, formData: FormData): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const coverLetter = (formData.get('cover_letter')?.toString() ?? '').trim()
  if (coverLetter.length < 50) {
    return { ok: false, message: 'Lettre de motivation trop courte (50 car. min).' }
  }

  const { data: job } = await supabase
    .from('jobs')
    .select('id, status, company_id, title')
    .eq('id', jobId)
    .maybeSingle()
  if (!job) return { ok: false, message: 'Offre introuvable.' }
  if (job.status !== 'active') return { ok: false, message: 'Cette offre n\'accepte plus de candidatures.' }
  if (!profile.cv_path) return { ok: false, message: 'Veuillez d\'abord ajouter un CV à votre profil.' }

  const { data: exists } = await supabase
    .from('applications')
    .select('id')
    .eq('job_id', jobId)
    .eq('user_id', profile.id)
    .maybeSingle()
  if (exists) return { ok: false, message: 'Vous avez déjà postulé à cette offre.' }

  const { error } = await supabase.from('applications').insert({
    job_id: jobId,
    user_id: profile.id,
    cover_letter: coverLetter,
    cv_path: profile.cv_path,
    status: 'pending',
  })
  if (error) return { ok: false, message: error.message }

  // Notification au recruteur
  if (job.company_id) {
    await supabase.from('messages').insert({
      sender_id: profile.id,
      receiver_id: job.company_id,
      subject: `Nouvelle candidature — ${job.title}`,
      body: `${profile.prenom} ${profile.nom} a postulé à votre offre « ${job.title} ».`,
    })
  }

  revalidatePath(`/job/${jobId}`)
  await setFlash('success', 'Candidature envoyée.')
  return { ok: true }
}

export async function updateApplicationStatusAction(
  applicationId: number,
  status: ApplicationStatus
): Promise<ActionResult> {
  const profile = await requireLogin()
  const supabase = await createClient()

  const { data: app } = await supabase
    .from('applications')
    .select('id, user_id, job_id, jobs!inner(company_id, title)')
    .eq('id', applicationId)
    .maybeSingle()
  if (!app) return { ok: false, message: 'Candidature introuvable.' }

  const job = (app as unknown as { jobs: { company_id: string; title: string } | { company_id: string; title: string }[] }).jobs
  const jobRow = Array.isArray(job) ? job[0] : job
  if (!jobRow) return { ok: false, message: 'Offre introuvable.' }
  if (jobRow.company_id !== profile.id && profile.role !== 'admin')
    return { ok: false, message: 'Action non autorisée.' }

  const { error } = await supabase.from('applications').update({ status }).eq('id', applicationId)
  if (error) return { ok: false, message: error.message }

  // Notifier le candidat
  await supabase.from('messages').insert({
    sender_id: profile.id,
    receiver_id: (app as { user_id: string }).user_id,
    subject:
      status === 'accepted'
        ? `Candidature acceptée — ${jobRow.title}`
        : status === 'rejected'
          ? `Candidature non retenue — ${jobRow.title}`
          : `Candidature en cours d'examen — ${jobRow.title}`,
    body:
      status === 'accepted'
        ? `Bonne nouvelle ! Votre candidature à « ${jobRow.title} » a été retenue. L'entreprise vous contactera prochainement.`
        : status === 'rejected'
          ? `Votre candidature à « ${jobRow.title} » n'a pas été retenue. Nous vous souhaitons bonne chance pour la suite.`
          : `Votre candidature à « ${jobRow.title} » est en cours d'examen.`,
  })

  revalidatePath(`/job/${(app as { job_id: number }).job_id}/candidatures`)
  return { ok: true }
}
