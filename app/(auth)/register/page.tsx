import type { Metadata } from 'next'
import Link from 'next/link'
import { RegisterTypeSelector } from '@/components/auth/register-type-selector'

export const metadata: Metadata = {
  title: 'Inscription',
  description:
    'Choisissez votre profil — étudiant, professionnel ou entreprise — et créez votre compte AlgoCodeBF.',
}

export default function RegisterPage() {
  return (
    <div className="auth-saas">
      <div className="au-shell">
        <div className="au-brand-row">
          <Link href="/" className="au-back">
            <i className="fas fa-home" aria-hidden />
            Accueil
          </Link>
          <span className="au-logo-mark">
            <span aria-hidden />
            AlgoCodeBF
          </span>
        </div>

        <div className="au-hero">
          <div className="au-eyebrow">
            <i className="fas fa-user-plus" aria-hidden />
            Nouveau compte
          </div>
          <h1>Choisissez le profil qui vous convient le mieux</h1>
        </div>

        <RegisterTypeSelector />

        <p className="au-footer-text" style={{ marginTop: 28 }}>
          Vous avez déjà un compte ? <Link href="/login">Se connecter</Link>
        </p>
      </div>
    </div>
  )
}
