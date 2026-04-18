'use client'

import Link from 'next/link'
import { usePathname, useSearchParams } from 'next/navigation'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { cn } from '@/lib/utils'

interface PaginationProps {
  currentPage: number
  totalPages: number
  pageParamName?: string
}

export function Pagination({ currentPage, totalPages, pageParamName = 'page' }: PaginationProps) {
  const pathname = usePathname()
  const searchParams = useSearchParams()

  if (totalPages <= 1) return null

  const buildUrl = (page: number) => {
    const params = new URLSearchParams(searchParams.toString())
    params.set(pageParamName, String(page))
    return `${pathname}?${params.toString()}`
  }

  const pages = generatePages(currentPage, totalPages)

  return (
    <nav className="flex items-center justify-center gap-2" aria-label="Pagination">
      {/* Previous */}
      {currentPage > 1 ? (
        <Link
          href={buildUrl(currentPage - 1)}
          className="flex items-center gap-1 px-3 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors"
        >
          <ChevronLeft size={16} />
          Précédent
        </Link>
      ) : (
        <span className="flex items-center gap-1 px-3 py-2 rounded-xl text-sm text-gray-300 dark:text-gray-600 cursor-not-allowed">
          <ChevronLeft size={16} />
          Précédent
        </span>
      )}

      {/* Page numbers */}
      <div className="flex items-center gap-1">
        {pages.map((page, idx) =>
          page === '...' ? (
            <span key={`ellipsis-${idx}`} className="px-3 py-2 text-sm text-gray-400">…</span>
          ) : (
            <Link
              key={page}
              href={buildUrl(page as number)}
              className={cn(
                'w-9 h-9 flex items-center justify-center rounded-xl text-sm font-medium transition-colors',
                page === currentPage
                  ? 'bg-[#C8102E] text-white'
                  : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'
              )}
            >
              {page}
            </Link>
          )
        )}
      </div>

      {/* Next */}
      {currentPage < totalPages ? (
        <Link
          href={buildUrl(currentPage + 1)}
          className="flex items-center gap-1 px-3 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors"
        >
          Suivant
          <ChevronRight size={16} />
        </Link>
      ) : (
        <span className="flex items-center gap-1 px-3 py-2 rounded-xl text-sm text-gray-300 dark:text-gray-600 cursor-not-allowed">
          Suivant
          <ChevronRight size={16} />
        </span>
      )}
    </nav>
  )
}

function generatePages(current: number, total: number): (number | '...')[] {
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

  const pages: (number | '...')[] = []
  pages.push(1)

  if (current > 3) pages.push('...')

  const start = Math.max(2, current - 1)
  const end = Math.min(total - 1, current + 1)

  for (let i = start; i <= end; i++) pages.push(i)

  if (current < total - 2) pages.push('...')

  pages.push(total)

  return pages
}
