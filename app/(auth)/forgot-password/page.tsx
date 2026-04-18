import type { Metadata } from 'next'
import { ForgotPasswordForm } from './forgot-password-form'

export const metadata: Metadata = { title: 'Mot de passe oublié' }

export default function ForgotPasswordPage() {
  return (
    <section className="auth-section">
      <div className="container">
        <div className="auth-wrapper">
          <div className="auth-card">
            <div className="auth-header">
              <h1>Mot de passe oublié</h1>
              <p>Saisissez votre email pour recevoir un lien de réinitialisation</p>
            </div>
            <ForgotPasswordForm />
          </div>
        </div>
      </div>
    </section>
  )
}
