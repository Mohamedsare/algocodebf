import Link from 'next/link'
import { FileText, ChevronLeft } from 'lucide-react'

interface LegalLayoutProps {
  title: string
  updatedAt: string
  children: React.ReactNode
}

/**
 * Coquille d'affichage pour les pages légales (équivalent des views policy/* du PHP).
 * Typographie .prose-content déjà définie dans globals.css.
 */
export function LegalLayout({ title, updatedAt, children }: LegalLayoutProps) {
  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-950 py-10">
      <div className="max-w-3xl mx-auto px-4">
        <Link
          href="/"
          className="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-[#C8102E] mb-6"
        >
          <ChevronLeft size={16} />
          Retour à l&apos;accueil
        </Link>

        <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-6 sm:p-10">
          <div className="flex items-center gap-3 mb-6 pb-6 border-b border-gray-100 dark:border-gray-800">
            <div className="w-12 h-12 rounded-xl bg-[#C8102E]/10 text-[#C8102E] flex items-center justify-center">
              <FileText size={22} />
            </div>
            <div>
              <h1 className="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">
                {title}
              </h1>
              <p className="text-xs text-gray-400 mt-1">Dernière mise à jour : {updatedAt}</p>
            </div>
          </div>

          <article className="prose-content text-gray-700 dark:text-gray-300">
            {children}
          </article>

          <div className="mt-10 pt-6 border-t border-gray-100 dark:border-gray-800 flex flex-wrap gap-4 text-sm">
            <Link href="/politique/confidentialite" className="text-[#C8102E] hover:underline">
              Confidentialité
            </Link>
            <Link href="/politique/conditions" className="text-[#C8102E] hover:underline">
              Conditions
            </Link>
            <Link href="/politique/mentions" className="text-[#C8102E] hover:underline">
              Mentions légales
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}
