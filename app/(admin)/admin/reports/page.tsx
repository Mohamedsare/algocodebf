import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import { ReportRow } from '@/components/admin/report-row'
import type { Report } from '@/types'

export const metadata: Metadata = { title: 'Signalements' }
export const dynamic = 'force-dynamic'

interface Row extends Report {
  profiles: { prenom: string; nom: string } | null
}

export default async function AdminReportsPage() {
  const supabase = await createClient()
  const { data } = await supabase
    .from('reports')
    .select(
      `id, reporter_id, reportable_type, reportable_id, reportable_uuid,
       reason, details, status, reviewed_by, reviewed_at, created_at,
       profiles!reports_reporter_id_fkey(prenom, nom)`
    )
    .order('created_at', { ascending: false })
    .limit(200)
  const reports = (data ?? []) as unknown as Row[]

  const pending = reports.filter(r => r.status === 'pending')
  const done = reports.filter(r => r.status !== 'pending')

  return (
    <div className="space-y-5">
      <header>
        <h1 className="text-2xl font-bold">Signalements</h1>
        <p className="text-sm text-gray-500">
          {pending.length} ouvert{pending.length > 1 ? 's' : ''} · {done.length} traité{done.length > 1 ? 's' : ''}
        </p>
      </header>

      <section>
        <h2 className="text-sm font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-3">
          En attente
        </h2>
        {pending.length === 0 ? (
          <p className="text-sm text-gray-500">Aucun signalement en attente.</p>
        ) : (
          <ul className="space-y-3">
            {pending.map(r => (
              <ReportRow
                key={r.id}
                id={r.id}
                reportableType={r.reportable_type}
                reportableId={r.reportable_id}
                reason={r.reason}
                details={r.details}
                status={r.status}
                createdAt={r.created_at}
                reporter={r.profiles}
              />
            ))}
          </ul>
        )}
      </section>

      {done.length > 0 && (
        <section>
          <h2 className="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">
            Historique
          </h2>
          <ul className="space-y-3">
            {done.map(r => (
              <ReportRow
                key={r.id}
                id={r.id}
                reportableType={r.reportable_type}
                reportableId={r.reportable_id}
                reason={r.reason}
                details={r.details}
                status={r.status}
                createdAt={r.created_at}
                reporter={r.profiles}
              />
            ))}
          </ul>
        </section>
      )}
    </div>
  )
}
