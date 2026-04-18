import { createServerClient } from '@supabase/ssr'
import { NextResponse, type NextRequest } from 'next/server'

/**
 * URL scheme (fidèle au PHP, en gardant la base anglaise + actions en français) :
 * - Bases publiques : /, /blog, /tutorial, /forum, /project, /job, /user, /about,
 *   /politique/*, /search, /newsletter/unsubscribe
 * - Actions protégées : /creer, /modifier, /message, /candidater, etc.
 * - Console admin : /algocodebfadmin (rôle admin uniquement, traité à part).
 */

const PROTECTED_PREFIXES = [
  '/user/modifier',
  '/message',
  '/forum/creer',
  '/tutorial/creer',
  '/formations/creer',
  '/project/creer',
  '/blog/creer',
  '/job/creer',
]

const PROTECTED_SUFFIXES = ['/modifier', '/candidater', '/candidatures']

const ADMIN_PREFIXES = ['/algocodebfadmin']

const AUTH_ROUTES = ['/login', '/register', '/forgot-password', '/reset-password']

function isProtected(pathname: string): boolean {
  if (PROTECTED_PREFIXES.some(p => pathname.startsWith(p))) return true
  if (PROTECTED_SUFFIXES.some(s => pathname.endsWith(s))) return true
  return false
}

export async function proxy(request: NextRequest) {
  let supabaseResponse = NextResponse.next({ request })

  const supabase = createServerClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL!,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!,
    {
      cookies: {
        getAll() {
          return request.cookies.getAll()
        },
        setAll(cookiesToSet) {
          cookiesToSet.forEach(({ name, value }) =>
            request.cookies.set(name, value)
          )
          supabaseResponse = NextResponse.next({ request })
          cookiesToSet.forEach(({ name, value, options }) =>
            supabaseResponse.cookies.set(name, value, options)
          )
        },
      },
    }
  )

  const { data: { user } } = await supabase.auth.getUser()
  const { pathname } = request.nextUrl

  if (user && AUTH_ROUTES.some(r => pathname.startsWith(r))) {
    return NextResponse.redirect(new URL('/', request.url))
  }

  if (!user && isProtected(pathname)) {
    const url = request.nextUrl.clone()
    url.pathname = '/login'
    url.searchParams.set('redirect', pathname)
    return NextResponse.redirect(url)
  }

  if (ADMIN_PREFIXES.some(r => pathname.startsWith(r))) {
    if (!user) {
      const url = request.nextUrl.clone()
      url.pathname = '/login'
      url.searchParams.set('redirect', pathname)
      return NextResponse.redirect(url)
    }
    const { data: profile } = await supabase
      .from('profiles')
      .select('role')
      .eq('id', user.id)
      .single()

    if (profile?.role !== 'admin') {
      return NextResponse.redirect(new URL('/', request.url))
    }
  }

  return supabaseResponse
}

export const config = {
  matcher: [
    '/((?!_next/static|_next/image|favicon.ico|images|icons|.*\\.(?:svg|png|jpg|jpeg|gif|webp|ico)$).*)',
  ],
}
