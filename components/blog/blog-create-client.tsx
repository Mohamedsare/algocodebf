'use client'

import { useRef, useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { createBlogPostAction, updateBlogPostAction } from '@/app/actions/blog'
import { buildFileUrl } from '@/lib/utils'
import { markdownToHtml } from '@/lib/markdown'

interface Category {
  value: string
  label: string
}

interface Props {
  mode: 'create' | 'edit'
  categories: Category[]
  postId?: number
  initial?: {
    title: string
    slug?: string
    excerpt: string
    content: string
    category: string
    status: 'draft' | 'published' | 'archived'
    tags?: string | null
    featured_image?: string | null
  }
}

function slugifyTitle(s: string): string {
  return s
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
}

export function BlogCreateClient({ mode, categories, postId, initial }: Props) {
  const router = useRouter()
  const [title, setTitle] = useState(initial?.title ?? '')
  const [excerpt, setExcerpt] = useState(initial?.excerpt ?? '')
  const [content, setContent] = useState(initial?.content ?? '')
  const [category, setCategory] = useState(initial?.category ?? '')
  const [status, setStatus] = useState<'draft' | 'published' | 'archived'>(
    initial?.status ?? 'published'
  )
  const [tags, setTags] = useState(initial?.tags ?? '')
  const [imagePreview, setImagePreview] = useState<string | null>(
    initial?.featured_image ? buildFileUrl(initial.featured_image) : null
  )
  const [editorMode, setEditorMode] = useState<'write' | 'preview'>('write')
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [globalError, setGlobalError] = useState<string | null>(null)
  const [pending, startTransition] = useTransition()
  const fileRef = useRef<HTMLInputElement>(null)
  const formRef = useRef<HTMLFormElement>(null)
  const editorRef = useRef<HTMLTextAreaElement>(null)

  const slugPreview = slugifyTitle(title)

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = () => setImagePreview(reader.result as string)
    reader.readAsDataURL(file)
  }

  const clearImage = () => {
    setImagePreview(null)
    if (fileRef.current) fileRef.current.value = ''
  }

  const applyInsert = (before: string, after: string = '') => {
    const ta = editorRef.current
    if (!ta) return
    const start = ta.selectionStart
    const end = ta.selectionEnd
    const selected = content.slice(start, end)
    const next = content.slice(0, start) + before + selected + after + content.slice(end)
    setContent(next)
    setTimeout(() => {
      ta.focus()
      ta.selectionStart = start + before.length
      ta.selectionEnd = start + before.length + selected.length
    }, 10)
  }

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setErrors({})
    setGlobalError(null)

    if (title.trim().length < 5) {
      setErrors(p => ({ ...p, title: 'Le titre doit contenir au moins 5 caractères.' }))
      return
    }
    if (excerpt.trim().length < 20) {
      setErrors(p => ({ ...p, excerpt: 'Le résumé doit contenir au moins 20 caractères.' }))
      return
    }
    if (content.trim().length < 100) {
      setErrors(p => ({ ...p, content: 'Le contenu doit contenir au moins 100 caractères.' }))
      return
    }
    if (!category) {
      setErrors(p => ({ ...p, category: 'Veuillez sélectionner une catégorie.' }))
      return
    }

    const fd = new FormData(e.currentTarget)
    fd.set('title', title)
    fd.set('excerpt', excerpt)
    fd.set('content', content)
    fd.set('category', category)
    fd.set('status', status)
    if (tags) fd.set('tags', tags)

    startTransition(async () => {
      const res =
        mode === 'create'
          ? await createBlogPostAction(fd)
          : await updateBlogPostAction(postId as number, fd)

      if (res.ok) {
        const data = (res as { data?: { slug?: string } }).data
        const slug = data?.slug ?? initial?.slug
        if (slug) router.push(`/blog/${slug}`)
        else router.push('/blog')
        router.refresh()
      } else {
        setErrors(res.errors ?? {})
        setGlobalError(res.message ?? 'Une erreur est survenue.')
      }
    })
  }

  const MDButton = ({
    onClick,
    label,
    children,
  }: {
    onClick: () => void
    label: string
    children: React.ReactNode
  }) => (
    <button type="button" className="bs-md-btn" onClick={onClick} title={label} aria-label={label}>
      {children}
    </button>
  )

  return (
    <div className="blog-saas">
      <section className="bs-composer">
        <div className="container">
          <div className="bs-composer-header">
            <div>
              <Link href="/blog" className="bs-btn bs-btn-ghost bs-btn-sm" style={{ marginBottom: 10 }}>
                <i className="fas fa-arrow-left"></i> Retour au blog
              </Link>
              <h1 className="bs-composer-title">
                {mode === 'edit' ? "Modifier l'article" : 'Nouvel article'}
              </h1>
              <p className="bs-composer-sub">
                {mode === 'edit'
                  ? 'Mettez à jour le contenu de votre article.'
                  : 'Partagez vos idées avec la communauté AlgoCodeBF.'}
              </p>
            </div>
          </div>

          {globalError && (
            <div
              style={{
                background: 'var(--bsaas-red-soft)',
                border: '1px solid rgba(200,16,46,.2)',
                padding: '12px 16px',
                borderRadius: 'var(--bsaas-r-md)',
                color: 'var(--bsaas-red-dark)',
                marginBottom: 20,
                display: 'flex',
                alignItems: 'center',
                gap: 8,
                fontSize: '.9rem',
              }}
            >
              <i className="fas fa-exclamation-circle"></i> {globalError}
            </div>
          )}

          <form
            ref={formRef}
            onSubmit={handleSubmit}
            encType="multipart/form-data"
            noValidate
          >
            <div className="bs-composer-grid">
              <div className="bs-composer-main">
                {/* Titre */}
                <div className="bs-composer-card">
                  <div className="bs-field">
                    <label className="bs-label required" htmlFor="f-title">
                      <i className="fas fa-heading"></i> Titre de l&apos;article
                    </label>
                    <input
                      id="f-title"
                      type="text"
                      name="title"
                      className="bs-input bs-input-lg"
                      placeholder="Un titre accrocheur…"
                      value={title}
                      onChange={e => setTitle(e.target.value)}
                      maxLength={255}
                      required
                    />
                    {slugPreview && (
                      <div className="bs-slug-preview">URL : /blog/{slugPreview}</div>
                    )}
                    {errors.title && <span className="bs-error">{errors.title}</span>}
                  </div>

                  <div className="bs-field">
                    <label className="bs-label required" htmlFor="f-excerpt">
                      <i className="fas fa-align-left"></i> Résumé
                    </label>
                    <textarea
                      id="f-excerpt"
                      name="excerpt"
                      className="bs-textarea"
                      rows={3}
                      maxLength={300}
                      placeholder="Un court résumé qui donne envie de lire…"
                      value={excerpt}
                      onChange={e => setExcerpt(e.target.value)}
                      required
                    />
                    <div className="bs-counter">{excerpt.length}/300</div>
                    {errors.excerpt && <span className="bs-error">{errors.excerpt}</span>}
                  </div>
                </div>

                {/* Contenu */}
                <div className="bs-composer-card">
                  <div
                    style={{
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      marginBottom: 12,
                      flexWrap: 'wrap',
                      gap: 10,
                    }}
                  >
                    <h4 style={{ margin: 0 }}>
                      <i className="fas fa-feather-alt"></i> Contenu
                    </h4>
                    <div className="bs-seg">
                      <button
                        type="button"
                        className={`bs-seg-btn${editorMode === 'write' ? ' active' : ''}`}
                        onClick={() => setEditorMode('write')}
                      >
                        <i className="fas fa-pen"></i> Écrire
                      </button>
                      <button
                        type="button"
                        className={`bs-seg-btn${editorMode === 'preview' ? ' active' : ''}`}
                        onClick={() => setEditorMode('preview')}
                      >
                        <i className="fas fa-eye"></i> Aperçu
                      </button>
                    </div>
                  </div>

                  {editorMode === 'write' ? (
                    <>
                      <div className="bs-md-toolbar" role="toolbar">
                        <MDButton onClick={() => applyInsert('**', '**')} label="Gras">
                          <i className="fas fa-bold"></i>
                        </MDButton>
                        <MDButton onClick={() => applyInsert('*', '*')} label="Italique">
                          <i className="fas fa-italic"></i>
                        </MDButton>
                        <span style={{ width: 1, background: 'var(--bsaas-border)', margin: '0 4px' }} />
                        <MDButton onClick={() => applyInsert('# ')} label="Titre 1">
                          H1
                        </MDButton>
                        <MDButton onClick={() => applyInsert('## ')} label="Titre 2">
                          H2
                        </MDButton>
                        <MDButton onClick={() => applyInsert('### ')} label="Titre 3">
                          H3
                        </MDButton>
                        <span style={{ width: 1, background: 'var(--bsaas-border)', margin: '0 4px' }} />
                        <MDButton onClick={() => applyInsert('- ')} label="Liste">
                          <i className="fas fa-list-ul"></i>
                        </MDButton>
                        <MDButton onClick={() => applyInsert('1. ')} label="Liste numérotée">
                          <i className="fas fa-list-ol"></i>
                        </MDButton>
                        <MDButton onClick={() => applyInsert('> ')} label="Citation">
                          <i className="fas fa-quote-right"></i>
                        </MDButton>
                        <span style={{ width: 1, background: 'var(--bsaas-border)', margin: '0 4px' }} />
                        <MDButton onClick={() => applyInsert('[', '](url)')} label="Lien">
                          <i className="fas fa-link"></i>
                        </MDButton>
                        <MDButton onClick={() => applyInsert('`', '`')} label="Code">
                          <i className="fas fa-code"></i>
                        </MDButton>
                        <MDButton onClick={() => applyInsert('\n```\n', '\n```\n')} label="Bloc de code">
                          <i className="fas fa-terminal"></i>
                        </MDButton>
                        <MDButton onClick={() => applyInsert('![alt](', ')')} label="Image">
                          <i className="fas fa-image"></i>
                        </MDButton>
                      </div>
                      <textarea
                        ref={editorRef}
                        id="contentEditor"
                        name="content"
                        className="bs-md-editor"
                        placeholder="Rédigez votre article en Markdown…"
                        value={content}
                        onChange={e => setContent(e.target.value)}
                        required
                      />
                      <div
                        style={{
                          display: 'flex',
                          justifyContent: 'space-between',
                          marginTop: 8,
                          fontSize: '.78rem',
                          color: 'var(--bsaas-text-subtle)',
                        }}
                      >
                        <span>
                          <i className="fas fa-info-circle"></i> Markdown supporté
                        </span>
                        <span>
                          {content.length} caractères · ~
                          {Math.max(1, Math.ceil(content.trim().split(/\s+/).length / 200))} min
                        </span>
                      </div>
                    </>
                  ) : (
                    <div
                      className="bs-md-preview bs-prose"
                      dangerouslySetInnerHTML={{
                        __html:
                          content.trim().length > 0
                            ? markdownToHtml(content)
                            : '<p style="color: var(--bsaas-text-subtle); font-style: italic;">Rien à prévisualiser pour le moment…</p>',
                      }}
                    />
                  )}

                  {errors.content && <span className="bs-error" style={{ marginTop: 6 }}>{errors.content}</span>}
                </div>

                {/* Cover */}
                <div className="bs-composer-card">
                  <h4>
                    <i className="fas fa-image"></i> Image à la une
                  </h4>
                  {!imagePreview ? (
                    <div className="bs-cover-drop">
                      <input
                        ref={fileRef}
                        type="file"
                        name="featured_image"
                        accept="image/*"
                        onChange={handleImageChange}
                      />
                      <i className="fas fa-cloud-upload-alt"></i>
                      <strong>Cliquez ou déposez une image</strong>
                      <small>JPG, PNG, WEBP (Max 5 Mo)</small>
                    </div>
                  ) : (
                    <div className="bs-cover-preview">
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img src={imagePreview} alt="Aperçu" />
                      <button
                        type="button"
                        className="bs-cover-remove"
                        onClick={clearImage}
                        aria-label="Retirer l'image"
                      >
                        <i className="fas fa-times"></i>
                      </button>
                      <input
                        ref={fileRef}
                        type="file"
                        name="featured_image"
                        accept="image/*"
                        onChange={handleImageChange}
                        style={{ display: 'none' }}
                      />
                    </div>
                  )}
                </div>
              </div>

              {/* Sidebar */}
              <div className="bs-composer-main">
                <div className="bs-composer-card">
                  <h4>
                    <i className="fas fa-rocket"></i> Publication
                  </h4>

                  <div className="bs-field">
                    <label className="bs-label" htmlFor="f-status">Statut</label>
                    <select
                      id="f-status"
                      name="status"
                      className="bs-select"
                      value={status}
                      onChange={e =>
                        setStatus(e.target.value as 'draft' | 'published' | 'archived')
                      }
                    >
                      <option value="draft">Brouillon</option>
                      <option value="published">Publier</option>
                      {mode === 'edit' && <option value="archived">Archiver</option>}
                    </select>
                  </div>

                  <div className="bs-field">
                    <label className="bs-label required" htmlFor="f-category">Catégorie</label>
                    <select
                      id="f-category"
                      name="category"
                      className="bs-select"
                      value={category}
                      onChange={e => setCategory(e.target.value)}
                      required
                    >
                      <option value="">Sélectionner…</option>
                      {categories.map(c => (
                        <option key={c.value} value={c.value}>
                          {c.label}
                        </option>
                      ))}
                    </select>
                    {errors.category && <span className="bs-error">{errors.category}</span>}
                  </div>

                  <button type="submit" className="bs-btn bs-publish" disabled={pending}>
                    {pending ? (
                      <>
                        <i className="fas fa-spinner fa-spin"></i>{' '}
                        {mode === 'edit' ? 'Enregistrement…' : 'Publication…'}
                      </>
                    ) : (
                      <>
                        <i className="fas fa-paper-plane"></i>{' '}
                        {mode === 'edit' ? 'Enregistrer' : "Publier l'article"}
                      </>
                    )}
                  </button>
                </div>

                <div className="bs-composer-card">
                  <h4>
                    <i className="fas fa-tags"></i> Tags
                  </h4>
                  <div className="bs-field">
                    <input
                      type="text"
                      name="tags"
                      className="bs-input"
                      placeholder="React, Next.js, Web…"
                      value={tags}
                      onChange={e => setTags(e.target.value)}
                    />
                    <span className="bs-hint">Séparez les tags par des virgules.</span>
                  </div>
                </div>

                <div className="bs-composer-card bs-tips">
                  <h4>
                    <i className="fas fa-lightbulb"></i> Conseils
                  </h4>
                  <ul>
                    <li>Un titre clair et accrocheur</li>
                    <li>Un résumé qui donne envie de lire</li>
                    <li>Structurez avec des titres H2 / H3</li>
                    <li>Ajoutez une belle image à la une</li>
                    <li>Utilisez l&apos;aperçu avant de publier</li>
                  </ul>
                </div>
              </div>
            </div>
          </form>
        </div>
      </section>
    </div>
  )
}
