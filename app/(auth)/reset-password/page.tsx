import type { Metadata } from 'next'
import { ResetPasswordForm } from './reset-password-form'

export const metadata: Metadata = { title: 'Réinitialiser le mot de passe' }

export default function ResetPasswordPage() {
  return (
    <section className="auth-section">
      <div className="container">
        <div className="auth-wrapper">
          <div className="auth-card">
            <div className="auth-header">
              <h1>Nouveau mot de passe</h1>
              <p>Choisissez un mot de passe sécurisé</p>
            </div>
            <ResetPasswordForm />
          </div>
        </div>
      </div>
    </section>
  )
}
