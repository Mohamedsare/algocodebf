'use client'

import { useRef, useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { createTutorialAction, updateTutorialAction } from '@/app/actions/tutorial'
import { FORMATIONS_PATH } from '@/lib/routes'
import { buildFileUrl } from '@/lib/utils'

interface Props {
  mode: 'create' | 'edit'
  tutorialId?: number
  categories: string[]
  initial?: {
    title: string
    description: string
    content: string
    category: string
    type: string
    level: string
    external_link?: string | null
    thumbnail?: string | null
    tags?: string | null
  }
}

const TYPES: Array<{ value: string; label: string }> = [
  { value: 'video', label: 'Vidéo' },
  { value: 'text', label: 'Texte' },
  { value: 'pdf', label: 'PDF' },
  { value: 'code', label: 'Code / snippets' },
  { value: 'mixed', label: 'Mixte' },
]

const LEVELS: Array<{ value: string; label: string }> = [
  { value: 'Débutant', label: '⭐ Débutant' },
  { value: 'Intermédiaire', label: '⭐⭐ Intermédiaire' },
  { value: 'Avancé', label: '⭐⭐⭐ Avancé' },
  { value: 'Expert', label: '⭐⭐⭐⭐ Expert' },
]

export function TutorialCreateClient({ mode, tutorialId, categories, initial }: Props) {
  const router = useRouter()
  const [title, setTitle] = useState(initial?.title ?? '')
  const [description, setDescription] = useState(initial?.description ?? '')
  const [content, setContent] = useState(initial?.content ?? '')
  const [type, setType] = useState(initial?.type ?? '')
  const [category, setCategory] = useState(initial?.category ?? '')
  const [level, setLevel] = useState(initial?.level ?? '')
  const [externalLink, setExternalLink] = useState(initial?.external_link ?? '')
  const [tags, setTags] = useState(initial?.tags ?? '')
  const [preview, setPreview] = useState<string | null>(
    initial?.thumbnail ? buildFileUrl(initial.thumbnail) : null
  )
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [globalError, setGlobalError] = useState<string | null>(null)
  const [pending, startTransition] = useTransition()
  const thumbRef = useRef<HTMLInputElement>(null)

  const handleThumb = (e: React.ChangeEvent<HTMLInputElement>) => {
    const f = e.target.files?.[0]
    if (!f) return
    const r = new FileReader()
    r.onload = () => setPreview(r.result as string)
    r.readAsDataURL(f)
  }

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setErrors({})
    setGlobalError(null)

    const errs: Record<string, string> = {}
    if (title.trim().length < 5) errs.title = 'Titre trop court (5 caractères min).'
    if (description.trim().length < 20)
      errs.description = 'Description trop courte (20 caractères min).'
    if (!type) errs.type = 'Choisissez un type.'
    if (!category) errs.category = 'Choisissez une catégorie.'
    if (!level) errs.level = 'Choisissez un niveau.'
    if (Object.keys(errs).length) {
      setErrors(errs)
      return
    }

    const fd = new FormData(e.currentTarget)
    fd.set('title', title)
    fd.set('description', description)
    fd.set('content', content)
    fd.set('type', type)
    fd.set('category', category)
    fd.set('level', level)
    if (externalLink) fd.set('external_link', externalLink)
    if (tags) fd.set('tags', tags)

    startTransition(async () => {
      const res =
        mode === 'create'
          ? await createTutorialAction(fd)
          : await updateTutorialAction(tutorialId as number, fd)
      if (res.ok) {
        const data = (res as { data?: { id?: number } }).data
        const id = data?.id ?? tutorialId
        router.push(id ? `${FORMATIONS_PATH}/${id}` : FORMATIONS_PATH)
        router.refresh()
      } else {
        setErrors(res.errors ?? {})
        setGlobalError(res.message ?? 'Une erreur est survenue.')
      }
    })
  }

  return (
    <section className="create-tutorial-section">
      <div className="container">
          <div className="page-header-create">
            <div className="header-content">
              <h1>
                <i
                  className={`fas ${mode === 'edit' ? 'fa-edit' : 'fa-graduation-cap'}`}
                />{' '}
                {mode === 'edit' ? 'Modifier la formation' : 'Publier une formation'}
              </h1>
              <p>
                {mode === 'edit'
                  ? 'Mettez à jour le parcours, les chapitres et les ressources.'
                  : 'Structurez un parcours clair : objectifs, chapitres, vidéos et supports — niveau formation pro.'}
              </p>
            </div>
            <Link href={FORMATIONS_PATH} className="btn-back">
              <i className="fas fa-arrow-left" /> Retour au catalogue
            </Link>
          </div>

        {globalError && (
          <div className="alert alert-danger">
            <i className="fas fa-exclamation-triangle"></i>
            <span>{globalError}</span>
          </div>
        )}

        <div className="create-tutorial-wrapper">
          <div className="form-main">
            <form className="tutorial-form" onSubmit={handleSubmit} encType="multipart/form-data">
              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-info-circle"></i>
                  <h3>Informations de base</h3>
                </div>

                <div className="form-group">
                  <label htmlFor="title">
                    Titre de la formation *
                    <span className="field-hint">Visible sur le catalogue — soyez précis</span>
                  </label>
                  <input
                    type="text"
                    id="title"
                    name="title"
                    className={`form-control${errors.title ? ' is-invalid' : ''}`}
                    placeholder="Ex: Guide complet pour débuter avec React.js"
                    value={title}
                    onChange={e => setTitle(e.target.value)}
                    required
                  />
                  {errors.title && (
                    <span className="error-message">
                      <i className="fas fa-exclamation-circle"></i> {errors.title}
                    </span>
                  )}
                </div>

                <div className="form-group">
                  <label htmlFor="description">
                    Description courte *
                    <span className="field-hint">Accroche pour les apprenants (objectifs, public)</span>
                  </label>
                  <textarea
                    id="description"
                    name="description"
                    className={`form-control textarea-control${errors.description ? ' is-invalid' : ''}`}
                    rows={4}
                    value={description}
                    onChange={e => setDescription(e.target.value)}
                    placeholder="Décrivez brièvement ce que les utilisateurs vont apprendre..."
                    required
                  />
                  <div className="textarea-footer">
                    <span className="char-counter">
                      {description.length} caractères (min: 20)
                    </span>
                  </div>
                  {errors.description && (
                    <span className="error-message">
                      <i className="fas fa-exclamation-circle"></i> {errors.description}
                    </span>
                  )}
                </div>
              </div>

              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-th-large"></i>
                  <h3>Classification</h3>
                </div>

                <div className="form-row">
                  <div className="form-group">
                    <label htmlFor="type">
                      <i className="fas fa-file-alt" /> Format principal *
                    </label>
                    <select
                      id="type"
                      name="type"
                      className="form-control select-control"
                      value={type}
                      onChange={e => setType(e.target.value)}
                      required
                    >
                      <option value="">-- Choisir un type --</option>
                      {TYPES.map(t => (
                        <option key={t.value} value={t.value}>
                          {t.label}
                        </option>
                      ))}
                    </select>
                    {errors.type && (
                      <span className="error-message">{errors.type}</span>
                    )}
                  </div>

                  <div className="form-group">
                    <label htmlFor="category">
                      <i className="fas fa-tag"></i> Catégorie *
                    </label>
                    <select
                      id="category"
                      name="category"
                      className="form-control select-control"
                      value={category}
                      onChange={e => setCategory(e.target.value)}
                      required
                    >
                      <option value="">-- Choisir une catégorie --</option>
                      {categories.map(c => (
                        <option key={c} value={c}>
                          {c}
                        </option>
                      ))}
                    </select>
                    {errors.category && (
                      <span className="error-message">{errors.category}</span>
                    )}
                  </div>
                </div>

                <div className="form-group">
                  <label htmlFor="level">
                    <i className="fas fa-signal"></i> Niveau de difficulté *
                    <span className="field-hint">Niveau attendu des apprenants</span>
                  </label>
                  <select
                    id="level"
                    name="level"
                    className="form-control select-control"
                    value={level}
                    onChange={e => setLevel(e.target.value)}
                    required
                  >
                    <option value="">-- Choisir un niveau --</option>
                    {LEVELS.map(l => (
                      <option key={l.value} value={l.value}>
                        {l.label}
                      </option>
                    ))}
                  </select>
                  {errors.level && (
                    <span className="error-message">{errors.level}</span>
                  )}
                </div>
              </div>

              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-file-text"></i>
                  <h3>Contenu pédagogique</h3>
                </div>

                <div className="form-group">
                  <label htmlFor="content">
                    Contenu détaillé
                    <span className="field-hint">
                      Expliquez étape par étape (Markdown supporté)
                    </span>
                  </label>
                  <textarea
                    id="content"
                    name="content"
                    className="form-control"
                    rows={14}
                    value={content}
                    onChange={e => setContent(e.target.value)}
                    placeholder="Plan de cours, objectifs, texte des leçons (Markdown)…"
                  />
                  <small className="form-hint">
                    <i className="fas fa-info-circle"></i> Utilisez la syntaxe Markdown
                    pour formater le texte, ajouter des titres, des listes et du code.
                  </small>
                </div>
              </div>

              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-image"></i>
                  <h3>Miniature</h3>
                </div>

                <div className="form-group">
                  {preview && (
                    <div style={{ position: 'relative', width: 320, marginBottom: 12 }}>
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img
                        src={preview}
                        alt="aperçu"
                        style={{ width: '100%', borderRadius: 12 }}
                      />
                      <button
                        type="button"
                        onClick={() => {
                          setPreview(null)
                          if (thumbRef.current) thumbRef.current.value = ''
                        }}
                        style={{
                          position: 'absolute',
                          top: 8,
                          right: 8,
                          width: 32,
                          height: 32,
                          borderRadius: '50%',
                          border: 'none',
                          background: '#dc3545',
                          color: 'white',
                          cursor: 'pointer',
                        }}
                      >
                        <i className="fas fa-times"></i>
                      </button>
                    </div>
                  )}
                  <input
                    ref={thumbRef}
                    type="file"
                    name="thumbnail"
                    accept="image/*"
                    onChange={handleThumb}
                    className="form-control"
                  />
                </div>
              </div>

              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-paperclip"></i>
                  <h3>Ressources supplémentaires</h3>
                </div>

                <div className="form-group">
                  <label htmlFor="external_link">
                    <i className="fas fa-external-link-alt"></i> Lien externe (optionnel)
                    <span className="field-hint">
                      Lien vers une ressource externe (YouTube, GitHub, etc.)
                    </span>
                  </label>
                  <input
                    type="url"
                    id="external_link"
                    name="external_link"
                    className="form-control"
                    placeholder="https://..."
                    value={externalLink}
                    onChange={e => setExternalLink(e.target.value)}
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="tags">
                    <i className="fas fa-tags"></i> Tags (optionnel)
                    <span className="field-hint">Séparés par des virgules</span>
                  </label>
                  <input
                    type="text"
                    id="tags"
                    name="tags"
                    className="form-control"
                    placeholder="react, javascript, débutant"
                    value={tags}
                    onChange={e => setTags(e.target.value)}
                  />
                </div>
              </div>

              <div
                className="form-actions"
                style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', marginTop: 24 }}
              >
                <Link href={FORMATIONS_PATH} className="btn-back" style={{ padding: '12px 24px' }}>
                  Annuler
                </Link>
                <button
                  type="submit"
                  className="btn-create-tutorial"
                  disabled={pending}
                  style={{ padding: '12px 28px', fontSize: 15 }}
                >
                  {pending ? (
                    <>
                      <i className="fas fa-spinner fa-spin"></i>{' '}
                      {mode === 'edit' ? 'Enregistrement...' : 'Publication...'}
                    </>
                  ) : (
                    <>
                      <i className="fas fa-paper-plane"></i>{' '}
                      {mode === 'edit' ? 'Enregistrer' : 'Publier la formation'}
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  )
}
