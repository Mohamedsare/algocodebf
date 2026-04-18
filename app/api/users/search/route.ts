import { NextResponse } from 'next/server'
import { requireLogin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'

interface ProfileRow {
  id: string
  prenom: string | null
  nom: string | null
  photo_path: string | null
  role: string | null
  university: string | null
}

export async function GET(request: Request) {
  const profile = await requireLogin()
  const { searchParams } = new URL(request.url)
  const q = (searchParams.get('q') ?? '').trim()

  if (q.length < 2) return NextResponse.json([])

  const supabase = await createClient()
  const { data } = await supabase
    .from('profiles')
    .select('id, prenom, nom, photo_path, role, university')
    .eq('status', 'active')
    .neq('id', profile.id)
    .or(`prenom.ilike.%${q}%,nom.ilike.%${q}%,email.ilike.%${q}%`)
    .limit(10)

  const rows = (data ?? []) as unknown as ProfileRow[]

  const result = rows.map(u => ({
    id: u.id,
    name: `${u.prenom ?? ''} ${u.nom ?? ''}`.trim() || 'Utilisateur',
    prenom: u.prenom ?? '',
    nom: u.nom ?? '',
    photo: u.photo_path ?? '',
    role: u.role ?? 'student',
    university: u.university ?? '',
  }))

  return NextResponse.json(result)
}
