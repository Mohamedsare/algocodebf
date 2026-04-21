import type { Metadata, Viewport } from 'next'
import Script from 'next/script'
import { ToastProvider } from '@/components/ui/toast-provider'
import './globals.css'
import 'highlight.js/styles/github-dark.css'

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? 'https://algocodebf.vercel.app'

export const viewport: Viewport = {
  width: 'device-width',
  initialScale: 1,
  maximumScale: 5,
  viewportFit: 'cover',
  themeColor: [
    { media: '(prefers-color-scheme: light)', color: '#ffffff' },
    { media: '(prefers-color-scheme: dark)', color: '#2c3e50' },
  ],
  colorScheme: 'light',
}

export const metadata: Metadata = {
  metadataBase: new URL(SITE_URL),
  title: {
    default: 'AlgoCodeBF - Hub numérique des informaticiens du Burkina Faso',
    template: '%s | AlgoCodeBF',
  },
  description:
    'AlgoCodeBF est la plateforme communautaire dédiée aux développeurs, étudiants et innovateurs du Burkina Faso. Apprenez, collaborez et innovez ensemble.',
  keywords: ['Burkina Faso', 'développeurs', 'technologie', 'formation', 'programmation', 'communauté tech'],
  authors: [{ name: 'AlgoCodeBF' }],
  openGraph: {
    type: 'website',
    locale: 'fr_FR',
    siteName: 'AlgoCodeBF',
    url: SITE_URL,
    title: 'AlgoCodeBF - Hub numérique des informaticiens du Burkina Faso',
    description: 'Plateforme communautaire pour les développeurs burkinabè',
  },
  twitter: {
    card: 'summary_large_image',
    title: 'AlgoCodeBF',
    description: 'La plateforme tech du Burkina Faso',
  },
  alternates: { canonical: SITE_URL },
  icons: {
    icon: [
      { url: '/favicon.svg', type: 'image/svg+xml' },
    ],
    shortcut: '/favicon.svg',
  },
  manifest: '/manifest.json',
  appleWebApp: {
    capable: true,
    statusBarStyle: 'default',
    title: 'AlgoCodeBF',
  },
  formatDetection: {
    telephone: false,
  },
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="fr" data-scroll-behavior="smooth">
      <head>
        {/* Feuilles de style principales (portées du PHP à l'identique) */}
        <link rel="stylesheet" href="/css/style.css" />
        <link rel="stylesheet" href="/css/footer-legal.css" />
        <link rel="stylesheet" href="/css/burkinabe-theme.css" />
        <link rel="stylesheet" href="/css/mobile-nav-fix.css" />
        {/* Styles extraits des pages PHP (forum, blog, tutorial, job, user, admin, ...) */}
        <link rel="stylesheet" href="/css/pages.css" />
        {/* Polish mobile-first + UX (touch targets, safe-area, anti-zoom iOS, skeletons, ...) */}
        <link rel="stylesheet" href="/css/mobile-first-ux.css" />
        <link rel="stylesheet" href="/css/toast-stack.css" />
        {/* Console admin : échelle dense (après pages + mobile-first) */}
        <link rel="stylesheet" href="/css/admin-console.css" />
        {/* Forum SaaS-grade (Linear / Slack / Discord look & feel + realtime) */}
        <link rel="stylesheet" href="/css/forum-saas.css" />
        {/* Blog SaaS-grade (Medium / Substack / Ghost / Vercel blog + realtime) */}
        <link rel="stylesheet" href="/css/blog-saas.css" />
        {/* Jobs SaaS-grade (mobile-first, palette BF) */}
        <link rel="stylesheet" href="/css/job-saas.css" />
        {/* Projets + membres — même ligne directrice SaaS */}
        <link rel="stylesheet" href="/css/project-saas.css" />
        <link rel="stylesheet" href="/css/members-saas.css" />
        <link rel="stylesheet" href="/css/leaderboard-saas.css" />
        <link rel="stylesheet" href="/css/messaging-saas.css" />
        <link rel="stylesheet" href="/css/profile-edit-saas.css" />
        <link rel="stylesheet" href="/css/user-profile-saas.css" />
        <link rel="stylesheet" href="/css/home-saas.css" />
        <link rel="stylesheet" href="/css/formation-saas.css" />
        {/* Tableaux de bord « Mon Espace » (étudiant / formateur / entreprise) */}
        <link rel="stylesheet" href="/css/espace-saas.css" />
        <link rel="stylesheet" href="/css/auth-saas.css" />
        <link rel="stylesheet" href="/css/nav-burger-saas.css" />
        <link rel="stylesheet" href="/css/mobile-dock-saas.css" />
        <link rel="stylesheet" href="/css/header-authed-saas.css" />
        <link rel="stylesheet" href="/css/header-chrome.css" />
        <link rel="stylesheet" href="/css/search-live.css" />
        {/* Google Fonts : Inter (UI) + Newsreader (serif editoriale blog) */}
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link
          rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Newsreader:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap"
        />
        {/* FontAwesome (icônes utilisées partout) */}
        <link
          rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        />
      </head>
      <body>
        <ToastProvider>{children}</ToastProvider>

        {/* Scripts décoratifs (scroll-to-top). L'animation du drapeau burkinabè
            est maintenant gérée directement par <CtaSection /> côté React. */}
        <Script src="/js/scroll-to-top.js" strategy="afterInteractive" />
      </body>
    </html>
  )
}
