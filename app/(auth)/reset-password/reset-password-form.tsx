'use client'

import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { useState } from 'react'
import { createClient } from '@/lib/supabase/client'

export function ResetPasswordForm() {
  const router = useRouter()
  const [password, setPassword] = useState('')
  const [confirm, setConfirm] = useState('')
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [serverError, setServerError] = useState('')
  const [loading, setLoading] = useState(false)
  const [success, setSuccess] = useState(false)

  const submit = async (e: React.FormEvent) => {
    e.preventDefault()
    setErrors({})
    setServerError('')

    const errs: Record<string, string> = {}
    if (!password || password.length < 8) errs.password = 'Min. 8 caractères'
    else if (!/[A-Z]/.test(password)) errs.password = 'Doit contenir une majuscule'
    else if (!/[0-9]/.test(password)) errs.password = 'Doit contenir un chiffre'
    if (password !== confirm) errs.confirm = 'Les mots de passe ne correspondent pas'
    if (Object.keys(errs).length) {
      setErrors(errs)
      return
    }

    setLoading(true)
    try {
      const supabase = createClient()
      const { error } = await supabase.auth.updateUser({ password })
      if (error) {
        setServerError(error.message)
        return
      }
      setSuccess(true)
      setTimeout(() => router.push('/login'), 2000)
    } catch {
      setServerError('Une erreur est survenue.')
    } finally {
      setLoading(false)
    }
  }

  if (success) {
    return (
      <div style={{ textAlign: 'center', padding: '24px 0' }}>
        <div style={{ fontSize: 64, marginBottom: 20 }}>✅</div>
        <h2 style={{ marginBottom: 12 }}>Mot de passe mis à jour !</h2>
        <p>Redirection vers la connexion...</p>
      </div>
    )
  }

  return (
    <form onSubmit={submit} className="auth-form">
      {serverError && (
        <div className="alert alert-error" style={{ marginBottom: 16 }}>
          <i className="fas fa-exclamation-circle"></i> {serverError}
        </div>
      )}

      <div className="form-group">
        <label htmlFor="password">Nouveau mot de passe</label>
        <input
          type="password"
          id="password"
          value={password}
          onChange={e => setPassword(e.target.value)}
          className={`form-control${errors.password ? ' is-invalid' : ''}`}
          required
        />
        <small>Minimum 8 caractères, avec majuscule et chiffre</small>
        {errors.password && <div className="invalid-feedback">{errors.password}</div>}
      </div>

      <div className="form-group">
        <label htmlFor="confirm">Confirmer le mot de passe</label>
        <input
          type="password"
          id="confirm"
          value={confirm}
          onChange={e => setConfirm(e.target.value)}
          className={`form-control${errors.confirm ? ' is-invalid' : ''}`}
          required
        />
        {errors.confirm && <div className="invalid-feedback">{errors.confirm}</div>}
      </div>

      <button type="submit" className="btn btn-primary btn-block" disabled={loading}>
        {loading ? 'Mise à jour...' : 'Mettre à jour'}
      </button>

      <div className="form-group" style={{ textAlign: 'center', marginTop: 16 }}>
        <Link href="/login" className="text-link">
          Retour à la connexion
        </Link>
      </div>
    </form>
  )
}
