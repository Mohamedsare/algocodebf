import type { Metadata } from 'next'
import { notFound, redirect } from 'next/navigation'
import { requireLogin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { JobCreateClient } from '@/components/job/job-create-client'
import type { Job } from '@/types'

export const metadata: Metadata = { title: "Modifier l'offre - AlgoCodeBF" }

export default async function EditJobPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const jobId = Number(id)
  if (Number.isNaN(jobId)) notFound()
  const profile = await requireLogin()
  const supabase = await createClient()
  const { data: job } = await supabase
    .from('jobs')
    .select('*')
    .eq('id', jobId)
    .maybeSingle()
  if (!job) notFound()
  if (
    (job as { company_id: string | null }).company_id !== profile.id &&
    profile.role !== 'admin'
  ) {
    redirect(`/job/${jobId}`)
  }

  return <JobCreateClient mode="edit" job={job as Job} />
}
