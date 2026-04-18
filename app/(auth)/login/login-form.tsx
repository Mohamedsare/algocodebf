'use client'

import Link from 'next/link'
import { useRouter, useSearchParams } from 'next/navigation'
import { useState } from 'react'
import { createClient } from '@/lib/supabase/client'
import { useToast } from '@/components/ui/toast-provider'

export function LoginForm() {
  const router = useRouter()
  const searchParams = useSearchParams()
  const toast = useToast()
  const redirect = searchParams.get('redirect') ?? '/'

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [loading, setLoading] = useState(false)

  const submit = async (e: React.FormEvent) => {
    e.preventDefault()
    setErrors({})

    const errs: Record<string, string> = {}
    if (!email) errs.email = 'Email requis'
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errs.email = 'Email invalide'
    if (!password) errs.password = 'Mot de passe requis'
    if (Object.keys(errs).length) {
      setErrors(errs)
      return
    }

    setLoading(true)
    try {
      const supabase = createClient()
      const { error } = await supabase.auth.signInWithPassword({
        email: email.trim().toLowerCase(),
        password,
      })
      if (error) {
        if (error.message.includes('Invalid login credentials')) {
          toast.error('Email ou mot de passe incorrect.')
        } else if (error.message.includes('Email not confirmed')) {
          toast.error('Veuillez vérifier votre email avant de vous connecter.')
        } else {
          toast.error(error.message)
        }
        return
      }
      router.push(redirect)
      router.refresh()
    } catch {
      toast.error('Une erreur est survenue. Veuillez réessayer.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <form className="au-form" onSubmit={submit} noValidate>
      <div className="au-field">
        <label htmlFor="email">Adresse email</label>
        <input
          type="email"
          id="email"
          name="email"
          value={email}
          onChange={e => setEmail(e.target.value)}
          className={`au-input${errors.email ? ' au-invalid' : ''}`}
          autoComplete="email"
          required
        />
        {errors.email && <div className="au-error">{errors.email}</div>}
      </div>

      <div className="au-field">
        <label htmlFor="password">Mot de passe</label>
        <input
          type="password"
          id="password"
          name="password"
          value={password}
          onChange={e => setPassword(e.target.value)}
          className={`au-input${errors.password ? ' au-invalid' : ''}`}
          autoComplete="current-password"
          required
        />
        {errors.password && <div className="au-error">{errors.password}</div>}
      </div>

      <div className="au-field" style={{ marginTop: -4 }}>
        <Link href="/forgot-password" className="au-footer-text" style={{ margin: 0, textAlign: 'left' }}>
          Mot de passe oublié ?
        </Link>
      </div>

      <button type="submit" className="au-btn au-btn-primary" disabled={loading}>
        {loading ? (
          <>
            <i className="fas fa-spinner fa-spin" aria-hidden />
            Connexion…
          </>
        ) : (
          <>
            <i className="fas fa-right-to-bracket" aria-hidden />
            Se connecter
          </>
        )}
      </button>
    </form>
  )
}
