import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import { SignalementsAdminClient, type SignalementRow } from '@/components/admin/signalements-admin-client'
import type { Report, ReportableType } from '@/types'

export const metadata: Metadata = { title: 'Signalements (admin)' }
export const dynamic = 'force-dynamic'

function targetForReport(
  r: Report,
  slugByBlogId: Map<number, string>,
  commentMeta: Map<number, { t: string; id: number }>
): { href: string; label: string } {
  switch (r.reportable_type) {
    case 'post':
      return r.reportable_id != null
        ? { href: `/forum/${r.reportable_id}`, label: `Sujet #${r.reportable_id}` }
        : { href: '#', label: '—' }
    case 'tutorial':
      return r.reportable_id != null
        ? { href: `/formations/${r.reportable_id}`, label: `Formation #${r.reportable_id}` }
        : { href: '#', label: '—' }
    case 'blog':
      if (r.reportable_id == null) return { href: '#', label: '—' }
      {
        const slug = slugByBlogId.get(r.reportable_id)
        return slug
          ? { href: `/blog/${slug}`, label: slug }
          : { href: '#', label: `Article #${r.reportable_id}` }
      }
    case 'project':
      return r.reportable_id != null
        ? { href: `/project/${r.reportable_id}`, label: `Projet #${r.reportable_id}` }
        : { href: '#', label: '—' }
    case 'user':
      return r.reportable_uuid
        ? { href: `/user/${r.reportable_uuid}`, label: `Profil ${r.reportable_uuid.slice(0, 8)}…` }
        : { href: '#', label: '—' }
    case 'comment': {
      const meta = r.reportable_id != null ? commentMeta.get(r.reportable_id) : undefined
      if (!meta) return { href: '#', label: `Commentaire #${r.reportable_id ?? '?'}` }
      const id = meta.id
      switch (meta.t as ReportableType) {
        case 'post':
          return { href: `/forum/${id}`, label: `Commentaire → forum #${id}` }
        case 'tutorial':
          return { href: `/formations/${id}`, label: `Commentaire → formation #${id}` }
        case 'blog': {
          const slug = slugByBlogId.get(id)
          return slug
            ? { href: `/blog/${slug}`, label: `Commentaire → ${slug}` }
            : { href: '#', label: `Commentaire → blog #${id}` }
        }
        case 'project':
          return { href: `/project/${id}`, label: `Commentaire → projet #${id}` }
        default:
          return { href: '#', label: `Commentaire #${r.reportable_id}` }
      }
    }
    default:
      return { href: '#', label: '—' }
  }
}

export default async function AdminReportsPage() {
  const supabase = await createClient()
  const { data, error } = await supabase
    .from('reports')
    .select(
      `id, reporter_id, reportable_type, reportable_id, reportable_uuid,
       reason, details, status, reviewed_by, reviewed_at, created_at,
       profiles!reports_reporter_id_fkey(prenom, nom)`
    )
    .order('created_at', { ascending: false })
    .limit(400)

  if (error) {
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Signalements</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger les signalements : {error.message}</p>
      </div>
    )
  }

  type Raw = Report & {
    profiles: { prenom: string; nom: string } | { prenom: string; nom: string }[] | null
  }

  const normalized = (data ?? []).map((row): Report & { profiles: { prenom: string; nom: string } | null } => {
    const r = row as Raw
    const p = r.profiles
    const profiles = Array.isArray(p) ? p[0] ?? null : p ?? null
    return { ...r, profiles }
  })

  const blogIds = new Set<number>()
  for (const r of normalized) {
    if (r.reportable_type === 'blog' && r.reportable_id != null) blogIds.add(r.reportable_id)
  }

  const commentIds = [
    ...new Set(
      normalized
        .filter(r => r.reportable_type === 'comment' && r.reportable_id != null)
        .map(r => r.reportable_id as number)
    ),
  ]

  let commentMeta = new Map<number, { t: string; id: number }>()
  if (commentIds.length > 0) {
    const { data: crows } = await supabase
      .from('comments')
      .select('id, commentable_type, commentable_id')
      .in('id', commentIds)
    for (const c of crows ?? []) {
      const id = c.id as number
      const ct = c.commentable_type as string
      const cid = c.commentable_id as number
      commentMeta.set(id, { t: ct, id: cid })
      if (ct === 'blog') blogIds.add(cid)
    }
  }

  let slugByBlogId = new Map<number, string>()
  if (blogIds.size > 0) {
    const { data: posts } = await supabase.from('blog_posts').select('id, slug').in('id', [...blogIds])
    slugByBlogId = new Map((posts ?? []).map(p => [p.id as number, p.slug as string]))
  }

  const reports: SignalementRow[] = normalized.map(r => {
    const { href, label } = targetForReport(r, slugByBlogId, commentMeta)
    return {
      id: r.id,
      reportable_type: r.reportable_type,
      reportable_id: r.reportable_id,
      reportable_uuid: r.reportable_uuid,
      reason: r.reason,
      details: r.details,
      status: r.status,
      created_at: r.created_at,
      profiles: r.profiles,
      contentHref: href,
      targetLabel: label,
    }
  })

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold m-0">Signalements</h1>
      <p className="text-sm text-gray-600 m-0">
        Filtres locaux, liens corrects vers le contenu (blog par slug, commentaires vers la cible). Masquer, résoudre ou
        rejeter depuis chaque fiche.
      </p>
      <SignalementsAdminClient reports={reports} />
    </div>
  )
}
