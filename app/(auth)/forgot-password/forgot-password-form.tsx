'use client'

import Link from 'next/link'
import { useState } from 'react'
import { createClient } from '@/lib/supabase/client'

export function ForgotPasswordForm() {
  const [email, setEmail] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState(false)

  const submit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      setError('Adresse email invalide')
      return
    }
    setLoading(true)
    try {
      const supabase = createClient()
      const { error: err } = await supabase.auth.resetPasswordForEmail(email.trim().toLowerCase(), {
        redirectTo: `${window.location.origin}/api/auth/callback?next=/reset-password`,
      })
      if (err) {
        setError(err.message)
        return
      }
      setSuccess(true)
    } catch {
      setError('Une erreur est survenue. Veuillez réessayer.')
    } finally {
      setLoading(false)
    }
  }

  if (success) {
    return (
      <div style={{ textAlign: 'center', padding: '24px 0' }}>
        <div style={{ fontSize: 64, marginBottom: 20 }}>📩</div>
        <h2 style={{ marginBottom: 12 }}>Email envoyé !</h2>
        <p style={{ marginBottom: 24 }}>
          Si un compte existe avec <strong>{email}</strong>, vous recevrez un lien pour réinitialiser votre mot de passe.
        </p>
        <Link href="/login" className="text-link">
          Retour à la connexion
        </Link>
      </div>
    )
  }

  return (
    <form onSubmit={submit} className="auth-form">
      {error && (
        <div className="alert alert-error" style={{ marginBottom: 16 }}>
          <i className="fas fa-exclamation-circle"></i> {error}
        </div>
      )}
      <div className="form-group">
        <label htmlFor="email">Adresse email</label>
        <input
          type="email"
          id="email"
          name="email"
          value={email}
          onChange={e => setEmail(e.target.value)}
          className="form-control"
          autoComplete="email"
          required
        />
      </div>
      <button type="submit" className="btn btn-primary btn-block" disabled={loading}>
        {loading ? 'Envoi...' : 'Envoyer le lien'}
      </button>
      <div className="form-group" style={{ textAlign: 'center', marginTop: 16 }}>
        <Link href="/login" className="text-link">
          <i className="fas fa-arrow-left"></i> Retour à la connexion
        </Link>
      </div>
    </form>
  )
}
