'use client'

import { useState } from 'react'

export function NewsletterWidgetUltra() {
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
      setMessage(data.message ?? 'Merci !')
      setEmail('')
    } catch (err) {
      setStatus('error')
      setMessage(err instanceof Error ? err.message : 'Erreur')
    }
  }

  return (
    <div className="widget-ultra widget-newsletter-ultra">
      <div className="newsletter-glow"></div>
      <div className="newsletter-icon-ultra">
        <i className="fas fa-envelope-open-text"></i>
      </div>
      <h3>Restez connecté</h3>
      <p>Recevez nos meilleurs articles chaque semaine</p>
      <form onSubmit={handleSubmit} className="newsletter-form-ultra">
        <input
          type="email"
          placeholder="votre@email.com"
          value={email}
          onChange={e => setEmail(e.target.value)}
          required
        />
        <button type="submit" disabled={status === 'loading'}>
          {status === 'loading' ? (
            <i className="fas fa-spinner fa-spin"></i>
          ) : status === 'success' ? (
            <i className="fas fa-check"></i>
          ) : (
            <i className="fas fa-paper-plane"></i>
          )}
        </button>
      </form>
      {message && (
        <div
          className="newsletter-count"
          style={{ color: status === 'error' ? '#ffdddd' : undefined }}
        >
          {message}
        </div>
      )}
      {!message && (
        <div className="newsletter-count">
          <i className="fas fa-users"></i> Rejoignez 500+ abonnés
        </div>
      )}
    </div>
  )
}
