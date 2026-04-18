'use client'

import { useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { applyToJobAction } from '@/app/actions/jobs'

interface Props {
  jobId: number
  externalLink?: string | null
  hasApplied: boolean
  isLoggedIn: boolean
  isDeadlinePassed: boolean
  isClosed: boolean
  hasCv: boolean
}

export function JobApplyCard({
  jobId,
  externalLink,
  hasApplied,
  isLoggedIn,
  isDeadlinePassed,
  isClosed,
  hasCv,
}: Props) {
  const router = useRouter()
  const [coverLetter, setCoverLetter] = useState('')
  const [pending, startTransition] = useTransition()
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState(false)

  if (isDeadlinePassed || isClosed) {
    return (
      <div className="apply-card">
        <h3>Candidatures fermées</h3>
        <p className="apply-description">
          Cette offre n&apos;accepte plus de candidatures.
        </p>
      </div>
    )
  }

  if (externalLink) {
    return (
      <div className="apply-card">
        <h3>Postuler à cette offre</h3>
        <p className="apply-description">
          Pour postuler, visitez le site officiel de l&apos;offre.
        </p>
        <a
          href={externalLink}
          target="_blank"
          rel="noopener noreferrer"
          className="btn-apply"
        >
          <i className="fas fa-external-link-alt"></i> Postuler sur le site officiel
        </a>
      </div>
    )
  }

  if (!isLoggedIn) {
    return (
      <div className="login-prompt-card">
        <i className="fas fa-user-lock"></i>
        <h3>Connexion requise</h3>
        <p>Connectez-vous pour postuler à cette offre.</p>
        <Link
          href={`/login?redirect=/job/${jobId}`}
          className="btn-login"
        >
          <i className="fas fa-sign-in-alt"></i> Se connecter
        </Link>
      </div>
    )
  }

  if (hasApplied || success) {
    return (
      <div className="applied-card">
        <i className="fas fa-check-circle"></i>
        <h3>Candidature envoyée</h3>
        <p className="apply-description">
          Vous serez notifié par message des suites données.
        </p>
      </div>
    )
  }

  if (!hasCv) {
    return (
      <div className="login-prompt-card">
        <i className="fas fa-file-alt" style={{ color: '#ffc107' }}></i>
        <h3>CV requis</h3>
        <p>Ajoutez un CV à votre profil avant de postuler.</p>
        <Link href="/user/modifier" className="btn-login">
          <i className="fas fa-user-edit"></i> Compléter mon profil
        </Link>
      </div>
    )
  }

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const letter = coverLetter.trim()
    if (letter.length < 50) {
      setError('Lettre de motivation trop courte (50 caractères minimum).')
      return
    }
    setError(null)
    const fd = new FormData()
    fd.set('cover_letter', letter)
    startTransition(async () => {
      const res = await applyToJobAction(jobId, fd)
      if (res.ok) {
        setSuccess(true)
        router.refresh()
      } else {
        setError(res.message ?? 'Une erreur est survenue.')
      }
    })
  }

  return (
    <div className="apply-card">
      <h3>Postuler à cette offre</h3>
      <p className="apply-description">
        Rédigez une lettre de motivation personnalisée. Votre CV actuel sera
        automatiquement joint.
      </p>
      <form onSubmit={handleSubmit}>
        {error && (
          <div
            style={{
              background: '#f8d7da',
              color: '#842029',
              padding: 10,
              borderRadius: 8,
              marginBottom: 10,
              fontSize: '0.85rem',
            }}
          >
            <i className="fas fa-exclamation-circle"></i> {error}
          </div>
        )}
        <div className="form-group">
          <label htmlFor="cover_letter">Lettre de motivation</label>
          <textarea
            id="cover_letter"
            name="cover_letter"
            rows={6}
            placeholder="Présentez votre parcours, vos motivations pour ce poste..."
            value={coverLetter}
            onChange={e => setCoverLetter(e.target.value)}
            required
          ></textarea>
          <div
            style={{
              textAlign: 'right',
              fontSize: '0.8rem',
              color: '#6c757d',
              marginTop: 4,
            }}
          >
            {coverLetter.length} caractères (min: 50)
          </div>
        </div>
        <button type="submit" className="btn-apply" disabled={pending}>
          <i className="fas fa-paper-plane"></i>{' '}
          {pending ? 'Envoi…' : 'Envoyer ma candidature'}
        </button>
      </form>
    </div>
  )
}
