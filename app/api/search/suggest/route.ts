import { NextResponse } from 'next/server'
import { createClient } from '@/lib/supabase/server'
import { parseSuggestScopes, runSearchSuggest } from '@/lib/search/run-suggest'
import { sanitizeSearchQuery } from '@/lib/search/sanitize'

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url)
  const qRaw = searchParams.get('q') ?? ''
  const q = sanitizeSearchQuery(qRaw)
  if (q.length < 2) {
    return NextResponse.json({
      members: [],
      posts: [],
      tutorials: [],
      blog: [],
      projects: [],
      jobs: [],
    })
  }

  const limit = Math.min(12, Math.max(1, parseInt(searchParams.get('limit') ?? '6', 10) || 6))
  const scopes = parseSuggestScopes(searchParams.get('scopes'))

  const supabase = await createClient()
  const data = await runSearchSuggest(supabase, qRaw, { scopes, limit })
  return NextResponse.json(data)
}
