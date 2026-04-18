'use client'

import { useRef, useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { createPostAction, updatePostAction } from '@/app/actions/forum'
import type { ForumCategory } from '@/types'

interface Props {
  categories: ForumCategory[]
  mode: 'create' | 'edit'
  initial?: {
    id: number
    title: string
    category: string | null
    body: string
  }
}

function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 10) / 10 + ' ' + sizes[i]
}

function renderMarkdownPreview(md: string): string {
  return md
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/^### (.*)$/gim, '<h3>$1</h3>')
    .replace(/^## (.*)$/gim, '<h2>$1</h2>')
    .replace(/^# (.*)$/gim, '<h1>$1</h1>')
    .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/gim, '<em>$1</em>')
    .replace(/`([^`]+)`/gim, '<code>$1</code>')
    .replace(/\[([^\]]+)\]\(([^)]+)\)/gim, '<a href="$2" target="_blank" rel="noopener">$1</a>')
    .replace(/\n/gim, '<br>')
}

const TOOLBAR = [
  { action: 'bold', icon: 'fa-bold', title: 'Gras (Ctrl+B)' },
  { action: 'italic', icon: 'fa-italic', title: 'Italique (Ctrl+I)' },
  { action: 'heading', icon: 'fa-heading', title: 'Titre' },
  { action: 'list', icon: 'fa-list-ul', title: 'Liste' },
  { action: 'code', icon: 'fa-code', title: 'Code' },
  { action: 'link', icon: 'fa-link', title: 'Lien' },
  { action: 'quote', icon: 'fa-quote-right', title: 'Citation' },
] as const

export function ForumCreateClient({ categories, mode, initial }: Props) {
  const router = useRouter()
  const textareaRef = useRef<HTMLTextAreaElement>(null)
  const fileInputRef = useRef<HTMLInputElement>(null)

  const [title, setTitle] = useState(initial?.title ?? '')
  const [category, setCategory] = useState(initial?.category ?? '')
  const [body, setBody] = useState(initial?.body ?? '')
  const [files, setFiles] = useState<File[]>([])
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [globalError, setGlobalError] = useState<string | null>(null)
  const [showPreview, setShowPreview] = useState(false)
  const [pending, startTransition] = useTransition()

  const applyToolbar = (action: string) => {
    const ta = textareaRef.current
    if (!ta) return
    const start = ta.selectionStart
    const end = ta.selectionEnd
    const selected = ta.value.substring(start, end)
    let insert = ''
    switch (action) {
      case 'bold':
        insert = `**${selected || 'texte en gras'}**`
        break
      case 'italic':
        insert = `*${selected || 'texte en italique'}*`
        break
      case 'heading':
        insert = `\n## ${selected || 'Titre'}\n`
        break
      case 'list':
        insert = `\n- ${selected || 'Élément'}\n- Autre élément\n`
        break
      case 'code':
        insert = `\`${selected || 'code'}\``
        break
      case 'link':
        insert = `[${selected || 'texte du lien'}](https://)`
        break
      case 'quote':
        insert = `\n> ${selected || 'Citation'}\n`
        break
    }
    const newValue = ta.value.substring(0, start) + insert + ta.value.substring(end)
    setBody(newValue)
    requestAnimationFrame(() => {
      ta.focus()
      ta.setSelectionRange(start + insert.length, start + insert.length)
    })
  }

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const arr = e.target.files ? Array.from(e.target.files) : []
    setFiles((prev) => [...prev, ...arr])
  }

  const removeFile = (index: number) => {
    setFiles((prev) => prev.filter((_, i) => i !== index))
    if (fileInputRef.current) fileInputRef.current.value = ''
  }

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setErrors({})
    setGlobalError(null)

    const nextErrors: Record<string, string> = {}
    if (!category) nextErrors.category = 'Veuillez choisir une catégorie'
    if (title.trim().length < 5) nextErrors.title = 'Le titre doit contenir au moins 5 caractères'
    if (body.trim().length < 20) nextErrors.body = 'Le contenu doit contenir au moins 20 caractères'
    if (Object.keys(nextErrors).length > 0) {
      setErrors(nextErrors)
      return
    }

    const fd = new FormData()
    fd.set('title', title.trim())
    fd.set('category', category)
    fd.set('body', body.trim())
    files.forEach((f) => fd.append('attachments', f))

    startTransition(async () => {
      const res =
        mode === 'create' ? await createPostAction(fd) : await updatePostAction(initial!.id, fd)
      if (!res.ok) {
        setGlobalError(res.message ?? 'Action impossible.')
        if (res.errors) setErrors(res.errors)
        return
      }
      const newId =
        res.data && typeof res.data === 'object' && 'id' in res.data
          ? (res.data as { id: number }).id
          : null
      if (mode === 'create' && newId) router.push(`/forum/${newId}`)
      else if (initial) router.push(`/forum/${initial.id}`)
      router.refresh()
    })
  }

  const charsRemaining = {
    title: Math.max(0, 200 - title.length),
    body: body.length,
  }

  return (
    <div className="create-page-saas">
      <div className="container-xl" style={{ maxWidth: 880 }}>
        {/* Breadcrumb */}
        <nav className="breadcrumb-saas" aria-label="Fil d'ariane">
          <Link href="/">
            <i className="fas fa-home" style={{ fontSize: 11 }}></i> Accueil
          </Link>
          <i className="fas fa-chevron-right separator"></i>
          <Link href="/forum">
            <i className="fas fa-comments" style={{ fontSize: 11 }}></i> Forum
          </Link>
          <i className="fas fa-chevron-right separator"></i>
          <span>{mode === 'edit' ? 'Modifier' : 'Nouvelle discussion'}</span>
        </nav>

        <div className="create-card">
          <h1>
            {mode === 'edit' ? 'Modifier la discussion' : 'Nouvelle discussion'}
          </h1>
          <p className="subtitle">
            Partagez vos questions, idées ou projets avec la communauté. Un bon post est clair,
            précis, et invite à la conversation.
          </p>

          {globalError && (
            <div className="alert-saas">
              <i className="fas fa-exclamation-triangle"></i>
              <span>{globalError}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} noValidate>
            {/* Catégorie */}
            <div className="field-saas">
              <label htmlFor="category">
                <i className="fas fa-tag" style={{ color: 'var(--f-primary)' }}></i>
                Catégorie
              </label>
              <select
                id="category"
                value={category}
                onChange={(e) => setCategory(e.target.value)}
                required
              >
                <option value="">— Choisir une catégorie —</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.slug}>
                    {c.name}
                  </option>
                ))}
              </select>
              {errors.category && <span className="error">{errors.category}</span>}
            </div>

            {/* Titre */}
            <div className="field-saas">
              <label htmlFor="title">
                <i className="fas fa-heading" style={{ color: 'var(--f-primary)' }}></i>
                Titre
                <span style={{ marginLeft: 'auto', fontSize: 11.5, color: 'var(--f-text-subtle)', fontWeight: 500 }}>
                  {charsRemaining.title} caractère{charsRemaining.title > 1 ? 's' : ''} restants
                </span>
              </label>
              <input
                id="title"
                type="text"
                placeholder="Ex. Comment structurer un projet Next.js à grande échelle ?"
                value={title}
                onChange={(e) => setTitle(e.target.value.slice(0, 200))}
                maxLength={200}
                required
              />
              {errors.title && <span className="error">{errors.title}</span>}
              {!errors.title && (
                <span className="hint">
                  Un titre clair et spécifique attire plus de réponses utiles.
                </span>
              )}
            </div>

            {/* Body + Toolbar */}
            <div className="field-saas">
              <label htmlFor="body" style={{ justifyContent: 'space-between' }}>
                <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}>
                  <i className="fas fa-align-left" style={{ color: 'var(--f-primary)' }}></i>
                  Contenu
                </span>
                <span style={{ display: 'inline-flex', gap: 4 }}>
                  <button
                    type="button"
                    className={`btn-saas sm${!showPreview ? ' ' : ' ghost'}`}
                    onClick={() => setShowPreview(false)}
                    style={{ minHeight: 28, padding: '4px 10px', fontSize: 12 }}
                  >
                    <i className="fas fa-pen" style={{ fontSize: 10 }}></i>
                    Écrire
                  </button>
                  <button
                    type="button"
                    className={`btn-saas sm${showPreview ? ' ' : ' ghost'}`}
                    onClick={() => setShowPreview(true)}
                    style={{ minHeight: 28, padding: '4px 10px', fontSize: 12 }}
                  >
                    <i className="fas fa-eye" style={{ fontSize: 10 }}></i>
                    Aperçu
                  </button>
                </span>
              </label>

              {!showPreview ? (
                <>
                  <div className="toolbar-saas">
                    {TOOLBAR.map((b) => (
                      <button
                        key={b.action}
                        type="button"
                        className="tb-btn"
                        title={b.title}
                        onClick={() => applyToolbar(b.action)}
                      >
                        <i className={`fas ${b.icon}`}></i>
                      </button>
                    ))}
                  </div>
                  <textarea
                    ref={textareaRef}
                    id="body"
                    rows={14}
                    placeholder={`Décrivez votre sujet de manière claire…\n\nAstuce : utilisez **gras**, *italique*, \`code\` et [liens](url) pour structurer.`}
                    value={body}
                    onChange={(e) => setBody(e.target.value)}
                    required
                  />
                </>
              ) : (
                <div
                  style={{
                    border: '1px solid var(--f-border)',
                    borderRadius: 10,
                    padding: '16px 18px',
                    minHeight: 240,
                    background: 'var(--f-surface)',
                    lineHeight: 1.7,
                    fontSize: 14.5,
                  }}
                  className="thread-body-saas"
                  dangerouslySetInnerHTML={{
                    __html: body.trim()
                      ? renderMarkdownPreview(body)
                      : '<p style="color: var(--f-text-subtle); font-style: italic;">Votre aperçu apparaîtra ici…</p>',
                  }}
                />
              )}

              {errors.body && <span className="error">{errors.body}</span>}
              {!errors.body && (
                <span className="hint">
                  Minimum 20 caractères · Markdown supporté ({charsRemaining.body} / 20+)
                </span>
              )}
            </div>

            {/* Pièces jointes */}
            <div className="field-saas">
              <label>
                <i className="fas fa-paperclip" style={{ color: 'var(--f-primary)' }}></i>
                Pièces jointes <span style={{ color: 'var(--f-text-subtle)', fontWeight: 500 }}>(optionnel)</span>
              </label>
              <label htmlFor="attachments" className="file-drop-saas">
                <div className="drop-icon">
                  <i className="fas fa-cloud-upload-alt"></i>
                </div>
                <div className="drop-text">Cliquez ou déposez vos fichiers ici</div>
                <div className="drop-sub">Images, PDF, ZIP, documents · 5 MB max / fichier</div>
                <input
                  ref={fileInputRef}
                  id="attachments"
                  type="file"
                  multiple
                  accept="image/*,.pdf,.doc,.docx,.zip,.txt"
                  onChange={handleFileChange}
                  style={{ display: 'none' }}
                />
              </label>

              {files.length > 0 && (
                <div className="file-list-saas">
                  {files.map((f, i) => (
                    <div key={`${f.name}-${i}`} className="file-row-saas">
                      <div className="f-icon">
                        <i className="fas fa-file"></i>
                      </div>
                      <div className="f-info">
                        <div className="f-name">{f.name}</div>
                        <div className="f-size">{formatFileSize(f.size)}</div>
                      </div>
                      <button
                        type="button"
                        className="f-remove"
                        onClick={() => removeFile(i)}
                        aria-label={`Retirer ${f.name}`}
                      >
                        <i className="fas fa-times"></i>
                      </button>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Actions */}
            <div className="form-actions-saas">
              <Link href="/forum" className="btn-saas">
                <i className="fas fa-arrow-left" style={{ fontSize: 11 }}></i>
                Annuler
              </Link>
              <button type="submit" className="btn-saas primary lg" disabled={pending}>
                <i className={`fas ${pending ? 'fa-spinner fa-spin' : mode === 'edit' ? 'fa-save' : 'fa-paper-plane'}`}></i>
                {pending
                  ? 'Publication…'
                  : mode === 'edit'
                  ? 'Enregistrer les modifications'
                  : 'Publier la discussion'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}
