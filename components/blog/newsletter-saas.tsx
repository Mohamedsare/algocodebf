'use client'

import { useState } from 'react'

interface NewsletterSaasProps {
  /** blog : styles blog-saas | job : styles job-saas */
  variant?: 'blog' | 'job'
}

export function NewsletterSaas({ variant = 'blog' }: NewsletterSaasProps) {
  const [email, setEmail] = useState('')
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle')
  const [message, setMessage] = useState('')

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setStatus('loading')
    try {
      const res = await fetch('/api/newsletter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email }),
      })
      const data = await res.json()
      if (!res.ok || !data.ok) throw new Error(data?.message ?? 'Erreur')
      setStatus('success')
      setMessage(data.message ?? 'Merci ! Vous êtes inscrit.')
      setEmail('')
    } catch (err) {
      setStatus('error')
      setMessage(err instanceof Error ? err.message : 'Erreur')
    }
  }

  const isJob = variant === 'job'

  return (
    <div className={isJob ? 'js-newsletter' : 'bs-widget bs-newsletter'}>
      <div className={isJob ? 'js-nl-icon' : 'bs-nl-icon'}>
        <i className="fas fa-envelope-open-text"></i>
      </div>
      <h3>Newsletter</h3>
      <p>
        {isJob
          ? 'Recevez les nouvelles offres et événements tech du Burkina.'
          : 'Les meilleurs articles tech du Burkina, une fois par semaine.'}
      </p>
      <form onSubmit={handleSubmit}>
        <input
          type="email"
          placeholder="votre@email.com"
          value={email}
          onChange={e => setEmail(e.target.value)}
          required
          disabled={status === 'loading'}
        />
        <button type="submit" disabled={status === 'loading' || !email}>
          {status === 'loading' ? (
            <>
              <i className="fas fa-spinner fa-spin"></i> Inscription…
            </>
          ) : (
            <>
              <i className="fas fa-paper-plane"></i> S&apos;inscrire
            </>
          )}
        </button>
      </form>
      {message && (
        <p
          style={{
            marginTop: 10,
            fontSize: '.8rem',
            fontWeight: 600,
            color:
              status === 'success'
                ? isJob
                  ? 'var(--js-green)'
                  : 'var(--bsaas-green)'
                : status === 'error'
                  ? isJob
                    ? 'var(--js-red)'
                    : 'var(--bsaas-red)'
                  : isJob
                    ? 'var(--js-muted)'
                    : 'var(--bsaas-text-muted)',
          }}
        >
          {status === 'success' ? '✓ ' : status === 'error' ? '⚠ ' : ''}
          {message}
        </p>
      )}
    </div>
  )
}
