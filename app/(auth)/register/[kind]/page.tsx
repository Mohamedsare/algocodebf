import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { RegisterDynamicForm, RegisterFormHeader, type RegisterKindSlug } from '@/components/auth/register-dynamic-form'

const VALID: RegisterKindSlug[] = ['etudiant', 'professionnel', 'entreprise']

export async function generateMetadata({
  params,
}: {
  params: Promise<{ kind: string }>
}): Promise<Metadata> {
  const { kind } = await params
  if (!VALID.includes(kind as RegisterKindSlug)) return { title: 'Inscription' }
  const titles: Record<RegisterKindSlug, string> = {
    etudiant: 'Inscription étudiant',
    professionnel: 'Inscription professionnel',
    entreprise: 'Inscription entreprise',
  }
  return {
    title: titles[kind as RegisterKindSlug],
    description: 'Créez votre compte AlgoCodeBF — un seul login pour tous les profils.',
  }
}

export default async function RegisterKindPage({ params }: { params: Promise<{ kind: string }> }) {
  const { kind } = await params
  if (!VALID.includes(kind as RegisterKindSlug)) notFound()
  const k = kind as RegisterKindSlug

  return (
    <div className="auth-saas">
      <div className="au-shell">
        <div className="au-brand-row">
          <Link href="/register" className="au-back">
            <i className="fas fa-arrow-left" aria-hidden />
            Changer de profil
          </Link>
          <span className="au-logo-mark">
            <span aria-hidden />
            AlgoCodeBF
          </span>
        </div>

        <div className="au-card au-card-wide">
          <RegisterFormHeader kind={k} />
          <RegisterDynamicForm kind={k} />
        </div>
      </div>
    </div>
  )
}
