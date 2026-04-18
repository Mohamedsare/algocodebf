'use client'

import { useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { Avatar } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Check, X, FileText, Mail, MapPin } from 'lucide-react'
import { buildCvUrl, formatDateShort } from '@/lib/utils'
import { updateApplicationStatusAction } from '@/app/actions/jobs'
import { useToast } from '@/components/ui/toast-provider'
import type { ApplicationStatus } from '@/types'

interface Props {
  id: number
  status: ApplicationStatus
  coverLetter: string | null
  cvPath: string | null
  createdAt: string
  applicant: {
    id: string
    prenom: string
    nom: string
    photo_path: string | null
    university: string | null
    city: string | null
  } | null
}

export function ApplicationRow(props: Props) {
  const router = useRouter()
  const toast = useToast()
  const [pending, startTransition] = useTransition()
  const [status, setStatus] = useState<ApplicationStatus>(props.status)
  const [open, setOpen] = useState(false)

  function onDecision(next: ApplicationStatus) {
    startTransition(async () => {
      const res = await updateApplicationStatusAction(props.id, next)
      if (res.ok) {
        setStatus(next)
        router.refresh()
      } else toast.error(res.message ?? 'Erreur')
    })
  }

  const applicant = props.applicant

  return (
    <li className="p-5 border border-gray-100 dark:border-gray-800 rounded-2xl bg-white dark:bg-gray-900">
      <div className="flex items-start justify-between gap-3 flex-wrap">
        <div className="flex items-start gap-3">
          <Avatar
            src={applicant?.photo_path ?? null}
            prenom={applicant?.prenom ?? ''}
            nom={applicant?.nom ?? ''}
            size="md"
          />
          <div>
            <Link href={applicant ? `/user/${applicant.id}` : '#'} className="text-sm font-semibold hover:text-[#C8102E]">
              {applicant ? `${applicant.prenom} ${applicant.nom}` : 'Candidat supprimé'}
            </Link>
            <div className="flex items-center gap-3 text-xs text-gray-500 mt-0.5 flex-wrap">
              {applicant?.university && <span>{applicant.university}</span>}
              {applicant?.city && <span className="flex items-center gap-1"><MapPin size={11} /> {applicant.city}</span>}
              <span>Postulé le {formatDateShort(props.createdAt)}</span>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-2">
          {status === 'pending' && (
            <>
              <Button size="sm" variant="secondary" onClick={() => onDecision('accepted')} loading={pending}>
                <Check size={14} /> Accepter
              </Button>
              <Button size="sm" variant="danger" onClick={() => onDecision('rejected')} loading={pending}>
                <X size={14} /> Refuser
              </Button>
            </>
          )}
          {status === 'accepted' && <Badge variant="success">Acceptée</Badge>}
          {status === 'rejected' && <Badge variant="danger">Refusée</Badge>}
        </div>
      </div>

      <div className="mt-3 flex flex-wrap gap-2">
        <button
          onClick={() => setOpen(o => !o)}
          className="inline-flex items-center gap-1 text-xs font-medium text-gray-600 hover:text-[#C8102E]"
        >
          <Mail size={12} /> {open ? 'Masquer' : 'Lire'} la lettre
        </button>
        {props.cvPath && (
          <a
            href={buildCvUrl(props.cvPath)}
            target="_blank"
            rel="noreferrer"
            className="inline-flex items-center gap-1 text-xs font-medium text-[#006A4E] hover:underline"
          >
            <FileText size={12} /> Télécharger le CV
          </a>
        )}
      </div>
      {open && (
        <div className="mt-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
          {props.coverLetter || 'Aucune lettre de motivation.'}
        </div>
      )}
    </li>
  )
}
