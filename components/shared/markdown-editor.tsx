'use client'

import { useState } from 'react'
import { Bold, Italic, List, Link as LinkIcon, Code, Image as ImageIcon, Eye, Pencil } from 'lucide-react'
import { cn } from '@/lib/utils'

interface MarkdownEditorProps {
  name: string
  defaultValue?: string
  value?: string
  onChange?: (value: string) => void
  placeholder?: string
  rows?: number
  className?: string
  required?: boolean
}

/**
 * Éditeur markdown minimaliste — en attendant une intégration Tiptap complète.
 * Fournit les principaux raccourcis (gras, italique, liste, lien, code, image).
 * Sérialise simplement le markdown tel quel ; le rendu côté lecture le convertit.
 */
export function MarkdownEditor({
  name,
  defaultValue = '',
  value,
  onChange,
  placeholder = "Écrivez votre contenu ici…",
  rows = 12,
  className,
  required,
}: MarkdownEditorProps) {
  const [text, setText] = useState(defaultValue)
  const [mode, setMode] = useState<'write' | 'preview'>('write')
  const current = value ?? text
  const set = (v: string) => {
    if (onChange) onChange(v)
    else setText(v)
  }

  const wrap = (before: string, after = before) => {
    const ta = document.getElementById(`md-${name}`) as HTMLTextAreaElement | null
    if (!ta) return
    const start = ta.selectionStart
    const end = ta.selectionEnd
    const selected = current.slice(start, end)
    const inserted = `${before}${selected || 'texte'}${after}`
    const next = current.slice(0, start) + inserted + current.slice(end)
    set(next)
    requestAnimationFrame(() => {
      ta.focus()
      ta.selectionStart = start + before.length
      ta.selectionEnd = start + before.length + (selected || 'texte').length
    })
  }

  const tools = [
    { icon: Bold, label: 'Gras', onClick: () => wrap('**') },
    { icon: Italic, label: 'Italique', onClick: () => wrap('*') },
    { icon: Code, label: 'Code', onClick: () => wrap('`') },
    { icon: List, label: 'Liste', onClick: () => wrap('\n- ', '') },
    {
      icon: LinkIcon,
      label: 'Lien',
      onClick: () => {
        const url = prompt('URL :')
        if (url) wrap('[', `](${url})`)
      },
    },
    {
      icon: ImageIcon,
      label: 'Image',
      onClick: () => {
        const url = prompt('URL de l\'image :')
        if (url) wrap(`![`, `](${url})`)
      },
    },
  ]

  return (
    <div className={cn('rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden', className)}>
      <div className="flex items-center gap-1 px-2 py-1.5 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        {tools.map(t => (
          <button
            key={t.label}
            type="button"
            onClick={t.onClick}
            className="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300"
            aria-label={t.label}
          >
            <t.icon size={14} />
          </button>
        ))}
        <div className="ml-auto flex items-center gap-1">
          <button
            type="button"
            onClick={() => setMode('write')}
            className={cn(
              'px-3 py-1 rounded-md text-xs font-semibold flex items-center gap-1',
              mode === 'write' ? 'bg-[#C8102E] text-white' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-800'
            )}
          >
            <Pencil size={12} /> Écrire
          </button>
          <button
            type="button"
            onClick={() => setMode('preview')}
            className={cn(
              'px-3 py-1 rounded-md text-xs font-semibold flex items-center gap-1',
              mode === 'preview' ? 'bg-[#C8102E] text-white' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-800'
            )}
          >
            <Eye size={12} /> Aperçu
          </button>
        </div>
      </div>

      {mode === 'write' ? (
        <textarea
          id={`md-${name}`}
          name={name}
          value={current}
          onChange={e => set(e.target.value)}
          rows={rows}
          placeholder={placeholder}
          required={required}
          className="w-full p-4 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 focus:outline-none resize-y"
        />
      ) : (
        <div
          className="prose prose-sm max-w-none dark:prose-invert p-4 min-h-[200px] bg-white dark:bg-gray-900"
          dangerouslySetInnerHTML={{ __html: renderMarkdown(current) }}
        />
      )}
    </div>
  )
}

/** Rendu markdown ultra-simple (gras, italique, code, liens, images, listes, titres). */
function renderMarkdown(src: string): string {
  const esc = (s: string) =>
    s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
  let s = esc(src)

  s = s.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="rounded-lg my-3 max-w-full" />')
  s = s.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener" class="text-[#C8102E] hover:underline">$1</a>')
  s = s.replace(/`([^`]+)`/g, '<code class="bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-xs">$1</code>')
  s = s.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
  s = s.replace(/\*([^*]+)\*/g, '<em>$1</em>')
  s = s.replace(/^###\s+(.+)$/gm, '<h3>$1</h3>')
  s = s.replace(/^##\s+(.+)$/gm, '<h2>$1</h2>')
  s = s.replace(/^#\s+(.+)$/gm, '<h1>$1</h1>')
  s = s.replace(/(?:^|\n)- (.+)/g, '\n<li>$1</li>')
  s = s.replace(/(<li>[\s\S]+?<\/li>(?:\n?<li>[\s\S]+?<\/li>)*)/g, '<ul class="list-disc pl-5 my-2">$&</ul>')
  s = s.replace(/\n{2,}/g, '</p><p>')
  return `<p>${s}</p>`
}
