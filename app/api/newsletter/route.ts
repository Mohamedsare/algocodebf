import { NextResponse } from 'next/server'
import { createClient } from '@/lib/supabase/server'
import { newsletterSchema } from '@/lib/validation'
import crypto from 'node:crypto'

/**
 * API Newsletter — POST : inscription.
 * Body JSON : { email: string }
 * Équivalent du NewsletterController@subscribe en PHP.
 */
export async function POST(request: Request) {
  try {
    const body = await request.json().catch(() => null)
    const parsed = newsletterSchema.safeParse(body)

    if (!parsed.success) {
      return NextResponse.json(
        { ok: false, message: parsed.error.issues[0]?.message ?? 'Email invalide' },
        { status: 400 }
      )
    }

    const email = parsed.data.email.trim().toLowerCase()
    const supabase = await createClient()
    const headers = request.headers
    const ip = headers.get('x-forwarded-for')?.split(',')[0]?.trim() ?? null
    const userAgent = headers.get('user-agent') ?? null

    const { data: existing } = await supabase
      .from('newsletter_subscribers')
      .select('id, status')
      .eq('email', email)
      .maybeSingle()

    if (existing) {
      if (existing.status !== 'active') {
        await supabase
          .from('newsletter_subscribers')
          .update({ status: 'active', unsubscribed_at: null })
          .eq('id', existing.id)
        return NextResponse.json({ ok: true, message: 'Votre abonnement a été réactivé.' })
      }
      return NextResponse.json({ ok: true, message: 'Vous êtes déjà abonné(e).' })
    }

    const unsubscribeToken = crypto.randomBytes(24).toString('hex')
    const { error } = await supabase.from('newsletter_subscribers').insert({
      email,
      status: 'active',
      unsubscribe_token: unsubscribeToken,
      ip_address: ip,
      user_agent: userAgent,
    })

    if (error) {
      return NextResponse.json(
        { ok: false, message: error.message },
        { status: 500 }
      )
    }

    return NextResponse.json({ ok: true, message: 'Inscription confirmée.' })
  } catch {
    return NextResponse.json(
      { ok: false, message: 'Erreur serveur.' },
      { status: 500 }
    )
  }
}
