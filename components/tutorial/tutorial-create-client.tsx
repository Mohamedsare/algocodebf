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

/** Valeurs alignées sur `tutorials.type` / Zod (`app/actions/tutorial.ts`). */
const TYPES: Array<{ value: 'video' | 'text' | 'mixed'; label: string }> = [
  { value: 'video', label: 'Vidéo' },
  { value: 'text', label: 'Texte / PDF / lecture' },
  { value: 'mixed', label: 'Mixte (vidéo + texte, code…)' },
]

/** Valeurs alignées sur `tutorials.level` / Zod — pas les libellés français en `value`. */
const LEVELS: Array<{ value: 'beginner' | 'intermediate' | 'advanced'; label: string }> = [
  { value: 'beginner', label: 'Débutant' },
  { value: 'intermediate', label: 'Intermédiaire' },
  { value: 'advanced', label: 'Avancé' },
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
        if (mode === 'create' && id) {
          const needsVideoStep = type === 'video' || type === 'mixed'
          router.push(
            needsVideoStep ? `${FORMATIONS_PATH}/${id}/modifier` : `${FORMATIONS_PATH}/${id}`
          )
        } else {
          router.push(id ? `${FORMATIONS_PATH}/${id}` : FORMATIONS_PATH)
        }
        router.refresh()
      } else {
        setErrors(res.errors ?? {})
        setGlobalError(res.message ?? 'Une erreur est survenue.')
      }
    })
  }

  return (
    <>
      <section className="fc-hero">
        <div className="container">
          <nav className="fc-breadcrumb" aria-label="Fil d'Ariane">
            <Link href="/" className="fc-crumb">
              Accueil
            </Link>
            <span className="fc-crumb-sep" aria-hidden>
              /
            </span>
            <Link href={FORMATIONS_PATH} className="fc-crumb">
              Formations
            </Link>
            <span className="fc-crumb-sep" aria-hidden>
              /
            </span>
            <span className="fc-crumb fc-crumb--current">{mode === 'edit' ? 'Modifier' : 'Publier'}</span>
          </nav>

          <header className="fc-header">
            <div className="fc-title-block">
              <p className="fc-eyebrow">
                <i className="fas fa-graduation-cap" aria-hidden />
                {mode === 'edit' ? 'Édition' : 'Contenu pédagogique'}
              </p>
              <h1 className="fc-page-title">
                {mode === 'edit' ? 'Modifier la formation' : 'Publier une formation'}
              </h1>
              <p className="fc-lead">
                {mode === 'edit'
                  ? 'Mettez à jour le parcours et les métadonnées. Les vidéos se gèrent dans la section dédiée sous ce formulaire.'
                  : 'Structurez un parcours clair : objectifs, format, niveau et contenu — les apprenants parcourent tout sur mobile comme sur bureau.'}
              </p>
            </div>
            <Link href={FORMATIONS_PATH} className="fc-back">
              <i className="fas fa-arrow-left" aria-hidden />
              Catalogue
            </Link>
          </header>
        </div>
      </section>

      <div className="fc-body">
        <div className="container">
          <div className="fc-layout">
            <div className="fc-form-shell">
              <form className="fc-form" onSubmit={handleSubmit} encType="multipart/form-data" noValidate>
                {globalError && (
                  <div className="fc-alert fc-alert--error" role="alert">
                    <i className="fas fa-exclamation-circle" aria-hidden />
                    <span>{globalError}</span>
                  </div>
                )}

                <section className="fc-section" aria-labelledby="fc-base-title">
                  <div className="fc-section-head">
                    <span className="fc-section-icon" aria-hidden>
                      <i className="fas fa-info-circle" />
                    </span>
                    <div>
                      <h2 className="fc-section-title" id="fc-base-title">
                        Informations de base
                      </h2>
                      <p className="fc-section-desc">Titre et accroche visibles sur le catalogue.</p>
                    </div>
                  </div>

                  <div className="fc-field">
                    <label htmlFor="title" className="fc-label">
                      Titre de la formation <abbr title="obligatoire">*</abbr>
                    </label>
                    <span className="fc-hint">Court, précis, sans jargon inutile.</span>
                    <input
                      type="text"
                      id="title"
                      name="title"
                      className={`fc-input${errors.title ? ' is-invalid' : ''}`}
                      placeholder="Ex. React.js : fondamentaux et premiers composants"
                      value={title}
                      onChange={e => setTitle(e.target.value)}
                      required
                    />
                    {errors.title && (
                      <span className="fc-field-error" role="alert">
                        <i className="fas fa-exclamation-circle" aria-hidden /> {errors.title}
                      </span>
                    )}
                  </div>

                  <div className="fc-field">
                    <label htmlFor="description" className="fc-label">
                      Description courte <abbr title="obligatoire">*</abbr>
                    </label>
                    <span className="fc-hint">Public visé, objectifs, prérequis en quelques phrases.</span>
                    <textarea
                      id="description"
                      name="description"
                      className={`fc-textarea fc-textarea--short${errors.description ? ' is-invalid' : ''}`}
                      rows={4}
                      value={description}
                      onChange={e => setDescription(e.target.value)}
                      placeholder="Ce que les apprenants sauront faire à la fin du parcours…"
                      required
                    />
                    <div className="fc-counter">
                      <span>{description.length}</span> caractères · min. 20
                    </div>
                    {errors.description && (
                      <span className="fc-field-error" role="alert">
                        <i className="fas fa-exclamation-circle" aria-hidden /> {errors.description}
                      </span>
                    )}
                  </div>
                </section>

                <section className="fc-section" aria-labelledby="fc-class-title">
                  <div className="fc-section-head">
                    <span className="fc-section-icon" aria-hidden>
                      <i className="fas fa-th-large" />
                    </span>
                    <div>
                      <h2 className="fc-section-title" id="fc-class-title">
                        Classification
                      </h2>
                      <p className="fc-section-desc">Format, thème et niveau pour le filtrage du catalogue.</p>
                    </div>
                  </div>

                  <div className="fc-row">
                    <div className="fc-field">
                      <label htmlFor="type" className="fc-label">
                        Format principal <abbr title="obligatoire">*</abbr>
                      </label>
                      <div className="fc-select-wrap">
                        <select
                          id="type"
                          name="type"
                          className="fc-select"
                          value={type}
                          onChange={e => setType(e.target.value)}
                          required
                        >
                          <option value="">Choisir un format</option>
                          {TYPES.map(t => (
                            <option key={t.value} value={t.value}>
                              {t.label}
                            </option>
                          ))}
                        </select>
                      </div>
                      {errors.type && (
                        <span className="fc-field-error" role="alert">
                          {errors.type}
                        </span>
                      )}
                      <p className="fc-hint fc-hint--block">
                        <strong>Vidéo</strong> : parcours avant tout en leçons filmées.{' '}
                        <strong>Texte / PDF / lecture</strong> : contenu écrit ou documents, vidéos optionnelles en
                        complément. <strong>Mixte</strong> : les deux au même niveau (démos vidéo + texte, code, etc.).
                      </p>
                      {mode === 'create' && (type === 'video' || type === 'mixed') && (
                        <p className="fc-hint fc-hint--video-step">
                          <i className="fas fa-info-circle" aria-hidden /> Après publication, vous serez redirigé·e
                          vers la page d&apos;édition pour <strong>ajouter les fichiers vidéo</strong> (le formulaire
                          de création n&apos;enregistre que la fiche et le texte).
                        </p>
                      )}
                    </div>

                    <div className="fc-field">
                      <label htmlFor="category" className="fc-label">
                        Catégorie <abbr title="obligatoire">*</abbr>
                      </label>
                      <div className="fc-select-wrap">
                        <select
                          id="category"
                          name="category"
                          className="fc-select"
                          value={category}
                          onChange={e => setCategory(e.target.value)}
                          required
                        >
                          <option value="">Choisir une catégorie</option>
                          {categories.map(c => (
                            <option key={c} value={c}>
                              {c}
                            </option>
                          ))}
                        </select>
                      </div>
                      {errors.category && (
                        <span className="fc-field-error" role="alert">
                          {errors.category}
                        </span>
                      )}
                    </div>
                  </div>

                  <div className="fc-field">
                    <label htmlFor="level" className="fc-label">
                      Niveau <abbr title="obligatoire">*</abbr>
                    </label>
                    <span className="fc-hint">Niveau attendu au début du parcours.</span>
                    <div className="fc-select-wrap">
                      <select
                        id="level"
                        name="level"
                        className="fc-select"
                        value={level}
                        onChange={e => setLevel(e.target.value)}
                        required
                      >
                        <option value="">Choisir un niveau</option>
                        {LEVELS.map(l => (
                          <option key={l.value} value={l.value}>
                            {l.label}
                          </option>
                        ))}
                      </select>
                    </div>
                    {errors.level && (
                      <span className="fc-field-error" role="alert">
                        {errors.level}
                      </span>
                    )}
                  </div>
                </section>

                <section className="fc-section" aria-labelledby="fc-content-title">
                  <div className="fc-section-head">
                    <span className="fc-section-icon" aria-hidden>
                      <i className="fas fa-align-left" />
                    </span>
                    <div>
                      <h2 className="fc-section-title" id="fc-content-title">
                        Contenu pédagogique
                      </h2>
                      <p className="fc-section-desc">Plan de cours, chapitres, texte — Markdown pris en charge.</p>
                    </div>
                  </div>

                  <div className="fc-field">
                    <label htmlFor="content" className="fc-label">
                      Contenu détaillé
                    </label>
                    <textarea
                      id="content"
                      name="content"
                      className="fc-textarea"
                      rows={14}
                      value={content}
                      onChange={e => setContent(e.target.value)}
                      placeholder="Objectifs, plan par section, encadrés, exemples de code…"
                    />
                    <p className="fc-markdown-hint">
                      <i className="fas fa-code" aria-hidden />
                      Titres, listes et blocs de code avec la syntaxe Markdown.
                    </p>
                  </div>
                </section>

                <section className="fc-section" aria-labelledby="fc-thumb-title">
                  <div className="fc-section-head">
                    <span className="fc-section-icon" aria-hidden>
                      <i className="fas fa-image" />
                    </span>
                    <div>
                      <h2 className="fc-section-title" id="fc-thumb-title">
                        Miniature
                      </h2>
                      <p className="fc-section-desc">Image affichée sur les cartes du catalogue (optionnel).</p>
                    </div>
                  </div>

                  <div className="fc-field">
                    {preview && (
                      <div className="fc-thumb-preview">
                        {/* eslint-disable-next-line @next/next/no-img-element */}
                        <img src={preview} alt="Aperçu de la miniature" className="fc-thumb-img" />
                        <button
                          type="button"
                          className="fc-thumb-remove"
                          onClick={() => {
                            setPreview(null)
                            if (thumbRef.current) thumbRef.current.value = ''
                          }}
                          aria-label="Retirer l’image"
                        >
                          <i className="fas fa-times" aria-hidden />
                        </button>
                      </div>
                    )}
                    <input
                      ref={thumbRef}
                      type="file"
                      name="thumbnail"
                      accept="image/*"
                      onChange={handleThumb}
                      className="fc-file"
                    />
                  </div>
                </section>

                <section className="fc-section fc-section--last" aria-labelledby="fc-extra-title">
                  <div className="fc-section-head">
                    <span className="fc-section-icon" aria-hidden>
                      <i className="fas fa-paperclip" />
                    </span>
                    <div>
                      <h2 className="fc-section-title" id="fc-extra-title">
                        Ressources & tags
                      </h2>
                      <p className="fc-section-desc">Lien externe et mots-clés pour améliorer la découvrabilité.</p>
                    </div>
                  </div>

                  <div className="fc-field">
                    <label htmlFor="external_link" className="fc-label">
                      Lien externe
                    </label>
                    <span className="fc-hint">Playlist, repo GitHub, support PDF hébergé ailleurs…</span>
                    <input
                      type="url"
                      id="external_link"
                      name="external_link"
                      className="fc-input"
                      placeholder="https://…"
                      value={externalLink}
                      onChange={e => setExternalLink(e.target.value)}
                      inputMode="url"
                    />
                  </div>

                  <div className="fc-field">
                    <label htmlFor="tags" className="fc-label">
                      Tags
                    </label>
                    <span className="fc-hint">Séparés par des virgules.</span>
                    <input
                      type="text"
                      id="tags"
                      name="tags"
                      className="fc-input"
                      placeholder="react, hooks, api, débutant…"
                      value={tags}
                      onChange={e => setTags(e.target.value)}
                    />
                  </div>
                </section>

                <div className="fc-actions">
                  <button type="submit" className="fc-submit" disabled={pending}>
                    {pending ? (
                      <>
                        <i className="fas fa-spinner fa-spin" aria-hidden />
                        {mode === 'edit' ? 'Enregistrement…' : 'Publication…'}
                      </>
                    ) : (
                      <>
                        <i className="fas fa-paper-plane" aria-hidden />
                        {mode === 'edit' ? 'Enregistrer' : 'Publier la formation'}
                      </>
                    )}
                  </button>
                  <Link href={FORMATIONS_PATH} className="fc-cancel">
                    <i className="fas fa-times" aria-hidden />
                    Annuler
                  </Link>
                </div>
              </form>
            </div>

            <aside className="fc-aside" aria-label="Conseils">
              <div className="fc-tip">
                <div className="fc-tip-icon" aria-hidden>
                  <i className="fas fa-lightbulb" />
                </div>
                <h3 className="fc-tip-title">Parcours efficace</h3>
                <ul className="fc-tip-list">
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Un titre qui résume le sujet et le niveau
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Description orientée bénéfices pour l&apos;apprenant
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Contenu découpé en sections numérotées
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Miniature lisible en petit format carte
                  </li>
                </ul>
              </div>

              <div className="fc-tip fc-tip--accent">
                <div className="fc-tip-icon fc-tip-icon--secondary" aria-hidden>
                  <i className="fas fa-mobile-alt" />
                </div>
                <h3 className="fc-tip-title">Mobile first</h3>
                <p className="fc-tip-text">
                  Beaucoup d&apos;apprenants consultent les formations sur téléphone : phrases courtes, listes à puces
                  et extraits de code courts améliorent la lisibilité.
                </p>
              </div>
            </aside>
          </div>
        </div>
      </div>
    </>
  )
}
