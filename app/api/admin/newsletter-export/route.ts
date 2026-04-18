import { requireAdmin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'

export async function GET() {
  await requireAdmin()
  const supabase = await createClient()
  const { data, error } = await supabase
    .from('newsletter_subscribers')
    .select('email, status, subscribed_at, unsubscribed_at, total_sent')
    .order('subscribed_at', { ascending: false })

  if (error) {
    return new Response(error.message, { status: 500 })
  }

  const esc = (s: string | number | null | undefined) => {
    if (s === null || s === undefined) return ''
    const t = String(s)
    if (/[",\n]/.test(t)) return `"${t.replace(/"/g, '""')}"`
    return t
  }

  const header = 'email,status,subscribed_at,unsubscribed_at,total_sent\n'
  const body = (data ?? [])
    .map(
      r =>
        [
          esc(r.email),
          esc(r.status),
          esc(r.subscribed_at),
          esc(r.unsubscribed_at),
          esc(r.total_sent),
        ].join(',') + '\n'
    )
    .join('')

  return new Response(header + body, {
    headers: {
      'Content-Type': 'text/csv; charset=utf-8',
      'Content-Disposition': 'attachment; filename="newsletter-algocodebf.csv"',
    },
  })
}
