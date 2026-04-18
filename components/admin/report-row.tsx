'use client'

import { useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { resolveReportAction, hideReportedContentAndResolveAction } from '@/app/actions/admin'
import { useToast } from '@/components/ui/toast-provider'
import type { ReportStatus } from '@/types'

interface Props {
  id: number
  reportableType: string
  reportableId: number | null
  contentHref: string
  targetLabel: string
  reason: string
  details: string | null
  status: ReportStatus
  createdAt: string
  reporter: { prenom: string; nom: string } | null
}

export function ReportRow(props: Props) {
  const router = useRouter()
  const toast = useToast()
  const [pending, startTransition] = useTransition()

  function decide(status: ReportStatus) {
    startTransition(async () => {
      const res = await resolveReportAction(props.id, status)
      if (res.ok) router.refresh()
      else toast.error(res.message ?? 'Erreur')
    })
  }

  const targetInner =
    props.contentHref !== '#' ? (
      <Link href={props.contentHref} className="text-sm font-semibold hover:text-[#C8102E]">
        {props.targetLabel}
      </Link>
    ) : (
      <span className="text-sm font-semibold text-gray-500">{props.targetLabel}</span>
    )

  return (
    <li className="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
      <div className="flex items-start justify-between gap-3 flex-wrap">
        <div>
          <div className="flex items-center gap-2 mb-1 flex-wrap">
            <Badge variant="outline">{props.reportableType}</Badge>
            {targetInner}
            {props.status === 'pending' && <Badge variant="warning">Ouvert</Badge>}
            {props.status === 'reviewed' && <Badge variant="default">Examiné</Badge>}
            {props.status === 'resolved' && <Badge variant="success">Résolu</Badge>}
            {props.status === 'dismissed' && <Badge variant="default">Rejeté</Badge>}
          </div>
          <div className="text-sm font-medium">{props.reason}</div>
          {props.details && (
            <div className="text-xs text-gray-500 mt-1 whitespace-pre-wrap">{props.details}</div>
          )}
          <div className="text-xs text-gray-400 mt-2">
            Signalé par {props.reporter ? `${props.reporter.prenom} ${props.reporter.nom}` : 'Anonyme'} —{' '}
            {new Date(props.createdAt).toLocaleString('fr-FR')}
          </div>
        </div>
        {props.status === 'pending' && (
          <div className="flex items-center gap-2 flex-wrap">
            {['post', 'comment', 'tutorial', 'blog', 'project'].includes(props.reportableType) && (
              <Button
                size="sm"
                variant="danger"
                onClick={() =>
                  startTransition(async () => {
                    const res = await hideReportedContentAndResolveAction(props.id)
                    if (res.ok) router.refresh()
                    else toast.error(res.message ?? 'Erreur')
                  })
                }
                loading={pending}
              >
                Masquer le contenu
              </Button>
            )}
            <Button size="sm" variant="secondary" onClick={() => decide('resolved')} loading={pending}>
              Résoudre
            </Button>
            <Button size="sm" variant="ghost" onClick={() => decide('dismissed')} loading={pending}>
              Rejeter
            </Button>
          </div>
        )}
      </div>
    </li>
  )
}
