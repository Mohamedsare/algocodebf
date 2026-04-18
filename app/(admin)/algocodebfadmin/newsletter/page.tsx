import type { Metadata } from 'next'
import { createClient } from '@/lib/supabase/server'
import {
  NewsletterAdminClient,
  type NewsletterSubscriberRow,
} from '@/components/admin/newsletter-admin-client'

export const metadata: Metadata = { title: 'Newsletter (admin)' }
export const dynamic = 'force-dynamic'

export default async function AdminNewsletterPage() {
  const supabase = await createClient()

  const [
    { count: activeCount },
    { count: unsubCount },
    { count: bouncedCount },
    { data: rows, error },
  ] = await Promise.all([
    supabase.from('newsletter_subscribers').select('*', { count: 'exact', head: true }).eq('status', 'active'),
    supabase.from('newsletter_subscribers').select('*', { count: 'exact', head: true }).eq('status', 'unsubscribed'),
    supabase.from('newsletter_subscribers').select('*', { count: 'exact', head: true }).eq('status', 'bounced'),
    supabase
      .from('newsletter_subscribers')
      .select('id, email, status, subscribed_at, unsubscribed_at, total_sent')
      .order('subscribed_at', { ascending: false })
      .limit(500),
  ])

  if (error) {
    return (
      <div className="space-y-2">
        <h1 className="text-2xl font-bold m-0">Newsletter</h1>
        <p className="text-sm text-red-600 m-0">Impossible de charger les abonnés : {error.message}</p>
      </div>
    )
  }

  const subscribers = (rows ?? []) as NewsletterSubscriberRow[]

  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold m-0">Newsletter</h1>
      <p className="text-sm text-gray-600 m-0">
        Abonnés et statistiques globales : filtres locaux, tableau compact, export CSV complet via le bouton (toutes les
        lignes, pas seulement l’aperçu).
      </p>
      <NewsletterAdminClient
        subscribers={subscribers}
        counts={{
          active: activeCount ?? 0,
          unsubscribed: unsubCount ?? 0,
          bounced: bouncedCount ?? 0,
        }}
        exportUrl="/api/admin/newsletter-export"
      />
    </div>
  )
}
