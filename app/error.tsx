'use client'

import { useEffect } from 'react'
import Link from 'next/link'
import { AlertTriangle, Home, RefreshCcw } from 'lucide-react'

export default function GlobalError({
  error,
  reset,
}: {
  error: Error & { digest?: string }
  reset: () => void
}) {
  useEffect(() => {
    console.error('App error:', error)
  }, [error])

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-white dark:from-gray-950 dark:to-gray-900 px-4">
      <div className="max-w-md w-full text-center">
        <div className="w-20 h-20 rounded-2xl bg-red-100 dark:bg-red-900/20 flex items-center justify-center mx-auto mb-6">
          <AlertTriangle size={40} className="text-[#C8102E]" />
        </div>

        <h1 className="text-2xl font-black text-gray-900 dark:text-white mb-3">
          Une erreur est survenue
        </h1>
        <p className="text-gray-500 dark:text-gray-400 mb-2 max-w-sm mx-auto">
          Nous rencontrons un problème technique. Nos équipes ont été notifiées.
        </p>
        {error.digest && (
          <p className="text-xs text-gray-400 mb-6 font-mono">
            Référence : {error.digest}
          </p>
        )}

        <div className="flex flex-col sm:flex-row items-center justify-center gap-3 mt-6">
          <button
            type="button"
            onClick={() => reset()}
            className="inline-flex items-center gap-2 px-6 h-11 rounded-full bg-[#C8102E] text-white font-semibold hover:bg-[#a00d24] transition-colors cursor-pointer"
          >
            <RefreshCcw size={16} />
            Réessayer
          </button>
          <Link
            href="/"
            className="inline-flex items-center gap-2 px-6 h-11 rounded-full border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
          >
            <Home size={16} />
            Accueil
          </Link>
        </div>
      </div>
    </div>
  )
}
