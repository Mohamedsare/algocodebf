import type { Metadata } from 'next'
import Link from 'next/link'
import { LoginForm } from './login-form'

export const metadata: Metadata = {
  title: 'Connexion',
  description: 'Connectez-vous à votre compte AlgoCodeBF.',
}

export default function LoginPage() {
  return (
    <div className="auth-saas">
      <div className="au-shell">
        <div className="au-brand-row">
          <Link href="/register" className="au-back">
            <i className="fas fa-user-plus" aria-hidden />
            Créer un compte
          </Link>
          <span className="au-logo-mark">
            <span aria-hidden />
            AlgoCodeBF
          </span>
        </div>

        <div className="au-card">
          <div className="au-card-head">
            <div className="au-eyebrow" style={{ marginBottom: 12 }}>
              <i className="fas fa-shield-halved" aria-hidden />
              Connexion sécurisée
            </div>
            <h1>Heureux de vous revoir</h1>
          </div>

          <LoginForm />

          <p className="au-footer-text">
            Pas encore de compte ? <Link href="/register">S’inscrire</Link>
          </p>
        </div>
      </div>
    </div>
  )
}
