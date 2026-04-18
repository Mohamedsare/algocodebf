import type { Metadata } from 'next'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/server'
import { CheckCircle2, AlertTriangle, Mail, ArrowLeft } from 'lucide-react'

export const metadata: Metadata = {
  title: 'Désinscription newsletter',
  description: 'Désabonnez-vous de la newsletter AlgoCodeBF en un clic.',
}

interface SearchParams {
  email?: string
  token?: string
}

export default async function UnsubscribePage({
  searchParams,
}: {
  searchParams: Promise<SearchParams>
}) {
  const params = await searchParams
  const email = (params.email ?? '').trim().toLowerCase()
  const token = (params.token ?? '').trim()

  let status: 'ok' | 'not_found' | 'invalid' | 'missing' = 'missing'

  if (email) {
    const supabase = await createClient()
    let builder = supabase
      .from('newsletter_subscribers')
      .update({ status: 'unsubscribed', unsubscribed_at: new Date().toISOString() })
      .eq('email', email)

    if (token) builder = builder.eq('unsubscribe_token', token)

    const { data, error } = await builder.select('id')

    if (error) status = 'invalid'
    else if (!data || data.length === 0) status = 'not_found'
    else status = 'ok'
  }

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-950 flex items-center justify-center px-4 py-16">
      <div className="w-full max-w-md">
        <div className="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
          <div className="bg-gradient-to-r from-[#006A4E] to-[#004d39] px-8 py-8 text-white text-center">
            <Mail size={40} className="mx-auto mb-3 text-[#FFD100]" />
            <h1 className="text-2xl font-black">Newsletter</h1>
            <p className="text-green-100 text-sm mt-1">Gestion de votre abonnement</p>
          </div>

          <div className="px-8 py-10 text-center">
            {status === 'ok' && (
              <>
                <CheckCircle2 size={48} className="mx-auto mb-4 text-emerald-500" />
                <h2 className="text-lg font-bold text-gray-900 dark:text-white mb-2">
                  Désabonnement confirmé
                </h2>
                <p className="text-gray-500 text-sm mb-6">
                  L&apos;adresse <strong>{email}</strong> ne recevra plus nos emails.
                </p>
              </>
            )}

            {status === 'not_found' && (
              <>
                <AlertTriangle size={48} className="mx-auto mb-4 text-amber-500" />
                <h2 className="text-lg font-bold text-gray-900 dark:text-white mb-2">
                  Email introuvable
                </h2>
                <p className="text-gray-500 text-sm mb-6">
                  Nous n&apos;avons trouvé aucun abonnement actif pour <strong>{email}</strong>.
                </p>
              </>
            )}

            {status === 'invalid' && (
              <>
                <AlertTriangle size={48} className="mx-auto mb-4 text-red-500" />
                <h2 className="text-lg font-bold text-gray-900 dark:text-white mb-2">Lien invalide</h2>
                <p className="text-gray-500 text-sm mb-6">
                  Le lien de désinscription est incorrect ou a expiré.
                </p>
              </>
            )}

            {status === 'missing' && (
              <>
                <Mail size={48} className="mx-auto mb-4 text-gray-400" />
                <h2 className="text-lg font-bold text-gray-900 dark:text-white mb-2">
                  Adresse manquante
                </h2>
                <p className="text-gray-500 text-sm mb-6">
                  Cliquez sur le lien contenu dans un de nos emails pour vous désabonner.
                </p>
              </>
            )}

            <Link
              href="/"
              className="inline-flex items-center gap-2 text-sm font-semibold text-[#C8102E] hover:underline"
            >
              <ArrowLeft size={14} />
              Retour à l&apos;accueil
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}
