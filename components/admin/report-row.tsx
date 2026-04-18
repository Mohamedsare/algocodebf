'use client'

import { useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { resolveReportAction } from '@/app/actions/admin'
import type { ReportStatus } from '@/types'

interface Props {
  id: number
  reportableType: string
  reportableId: number | null
  reason: string
  details: string | null
  status: ReportStatus
  createdAt: string
  reporter: { prenom: string; nom: string } | null
}

export function ReportRow(props: Props) {
  const router = useRouter()
  const [pending, startTransition] = useTransition()

  function decide(status: ReportStatus) {
    startTransition(async () => {
      const res = await resolveReportAction(props.id, status)
      if (res.ok) router.refresh()
      else alert(res.message)
    })
  }

  const target = props.reportableType === 'post' ? `/forum/${props.reportableId}`
    : props.reportableType === 'blog' ? `/blog/${props.reportableId}`
    : props.reportableType === 'tutorial' ? `/formations/${props.reportableId}`
    : props.reportableType === 'project' ? `/project/${props.reportableId}`
    : '#'

  return (
    <li className="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
      <div className="flex items-start justify-between gap-3 flex-wrap">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <Badge variant="outline">{props.reportableType}</Badge>
            <a href={target} className="text-sm font-semibold hover:text-[#C8102E]">
              #{props.reportableId}
            </a>
            {props.status === 'pending' && <Badge variant="warning">Ouvert</Badge>}
            {props.status === 'reviewed' && <Badge variant="default">Examiné</Badge>}
            {props.status === 'resolved' && <Badge variant="success">Résolu</Badge>}
            {props.status === 'dismissed' && <Badge variant="default">Rejeté</Badge>}
          </div>
          <div className="text-sm font-medium">{props.reason}</div>
          {props.details && <div className="text-xs text-gray-500 mt-1 whitespace-pre-wrap">{props.details}</div>}
          <div className="text-xs text-gray-400 mt-2">
            Signalé par {props.reporter ? `${props.reporter.prenom} ${props.reporter.nom}` : 'Anonyme'} —{' '}
            {new Date(props.createdAt).toLocaleString('fr-FR')}
          </div>
        </div>
        {props.status === 'pending' && (
          <div className="flex items-center gap-2">
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
