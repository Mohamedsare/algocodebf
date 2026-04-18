'use client'

import { useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { requestJoinProjectAction } from '@/app/actions/projects'

interface Props {
  projectId: number
  hasPending: boolean
  variant?: 'action' | 'full'
}

export function JoinProjectButton({ projectId, hasPending, variant = 'action' }: Props) {
  const router = useRouter()
  const [open, setOpen] = useState(false)
  const [pending, startTransition] = useTransition()
  const [error, setError] = useState<string | null>(null)
  const [role, setRole] = useState('Contributeur')
  const [motivation, setMotivation] = useState('')

  if (hasPending) {
    return (
      <button type="button" className="btn-action" disabled>
        <i className="fas fa-hourglass-half"></i> Demande en attente…
      </button>
    )
  }

  const triggerClass = variant === 'full' ? 'btn-join-full' : 'btn-action btn-join'

  function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setError(null)
    const fd = new FormData()
    fd.set('role', role)
    fd.set('motivation', motivation)
    startTransition(async () => {
      const res = await requestJoinProjectAction(projectId, fd)
      if (res.ok) {
        setOpen(false)
        router.refresh()
      } else {
        setError(res.message ?? 'Erreur lors de l\'envoi.')
      }
    })
  }

  return (
    <>
      <button type="button" className={triggerClass} onClick={() => setOpen(true)}>
        <i className="fas fa-user-plus"></i> Rejoindre le projet
      </button>

      {open && (
        <div
          style={{
            position: 'fixed',
            inset: 0,
            zIndex: 1000,
            background: 'rgba(0,0,0,0.5)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: 16,
          }}
          onClick={() => setOpen(false)}
        >
          <div
            style={{
              background: '#fff',
              borderRadius: 16,
              width: '100%',
              maxWidth: 520,
              padding: 28,
              boxShadow: '0 25px 50px -12px rgba(0,0,0,0.25)',
            }}
            onClick={e => e.stopPropagation()}
          >
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: 16,
              }}
            >
              <h3 style={{ margin: 0, fontSize: '1.25rem', fontWeight: 700 }}>
                <i className="fas fa-user-plus" style={{ marginRight: 8, color: '#009E60' }}></i>
                Rejoindre le projet
              </h3>
              <button
                type="button"
                onClick={() => setOpen(false)}
                style={{
                  background: 'none',
                  border: 'none',
                  fontSize: '1.5rem',
                  cursor: 'pointer',
                  color: '#6c757d',
                }}
              >
                ×
              </button>
            </div>
            <form onSubmit={onSubmit}>
              {error && (
                <div
                  style={{
                    background: '#f8d7da',
                    color: '#842029',
                    padding: 10,
                    borderRadius: 8,
                    marginBottom: 12,
                    fontSize: '0.9rem',
                  }}
                >
                  {error}
                </div>
              )}
              <div style={{ marginBottom: 14 }}>
                <label style={{ display: 'block', fontWeight: 600, marginBottom: 6, fontSize: '0.9rem' }}>
                  Rôle souhaité
                </label>
                <input
                  type="text"
                  value={role}
                  onChange={e => setRole(e.target.value)}
                  required
                  style={{
                    width: '100%',
                    padding: '10px 14px',
                    border: '2px solid #e9ecef',
                    borderRadius: 10,
                    fontSize: '0.95rem',
                  }}
                />
              </div>
              <div style={{ marginBottom: 14 }}>
                <label style={{ display: 'block', fontWeight: 600, marginBottom: 6, fontSize: '0.9rem' }}>
                  Message au porteur (optionnel)
                </label>
                <textarea
                  value={motivation}
                  onChange={e => setMotivation(e.target.value)}
                  rows={5}
                  placeholder="Présentez brièvement votre motivation et vos compétences."
                  style={{
                    width: '100%',
                    padding: '10px 14px',
                    border: '2px solid #e9ecef',
                    borderRadius: 10,
                    fontSize: '0.95rem',
                    fontFamily: 'inherit',
                    resize: 'vertical',
                  }}
                />
              </div>
              <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end' }}>
                <button
                  type="button"
                  className="btn btn-outline"
                  onClick={() => setOpen(false)}
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                  disabled={pending}
                >
                  {pending ? 'Envoi…' : 'Envoyer la demande'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  )
}
