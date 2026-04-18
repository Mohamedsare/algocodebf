import type { NextConfig } from 'next'

const nextConfig: NextConfig = {
  async redirects() {
    return [
      { source: '/tutorial', destination: '/formations', permanent: true },
      { source: '/tutorial/:path*', destination: '/formations/:path*', permanent: true },
    ]
  },
  async rewrites() {
    return {
      beforeFiles: [],
      afterFiles: [
        { source: '/formations', destination: '/tutorial' },
        { source: '/formations/creer', destination: '/tutorial/creer' },
        { source: '/formations/:id/modifier', destination: '/tutorial/:id/modifier' },
        { source: '/formations/:id', destination: '/tutorial/:id' },
      ],
      fallback: [],
    }
  },
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: '*.supabase.co',
        pathname: '/storage/v1/object/public/**',
      },
    ],
    localPatterns: [
      {
        pathname: '/images/**',
      },
    ],
  },
  experimental: {
    serverActions: {
      bodySizeLimit: '10mb',
    },
  },
}

export default nextConfig
