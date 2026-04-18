'use client'

import { useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { createProjectAction, updateProjectAction } from '@/app/actions/projects'
import type { Project, ProjectStatus, ProjectVisibility } from '@/types'

interface Props {
  mode: 'create' | 'edit'
  project?: Project
}

const STATUS_OPTIONS: Array<{ value: ProjectStatus; label: string }> = [
  { value: 'planning', label: 'Planification' },
  { value: 'in_progress', label: 'En cours' },
  { value: 'active', label: 'Actif' },
  { value: 'paused', label: 'En pause' },
  { value: 'completed', label: 'Terminé' },
  { value: 'archived', label: 'Archivé' },
]

function statusOptionsForMode(mode: 'create' | 'edit') {
  return mode === 'create' ? STATUS_OPTIONS.filter(s => s.value !== 'archived') : STATUS_OPTIONS
}

export function ProjectCreateClient({ mode, project }: Props) {
  const router = useRouter()
  const [pending, startTransition] = useTransition()
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [generalError, setGeneralError] = useState<string | null>(null)

  const [title, setTitle] = useState(project?.title ?? '')
  const [description, setDescription] = useState(project?.description ?? '')
  const [githubLink, setGithubLink] = useState(project?.github_link ?? '')
  const [demoLink, setDemoLink] = useState(project?.demo_link ?? '')
  const [status, setStatus] = useState<ProjectStatus>(project?.status ?? 'planning')
  const [visibility, setVisibility] = useState<ProjectVisibility>(
    (project?.visibility as ProjectVisibility) ?? 'public'
  )
  const [lookingForMembers, setLookingForMembers] = useState(project?.looking_for_members ?? false)

  const charCount = description.length
  const statusChoices = statusOptionsForMode(mode)

  const onSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setGeneralError(null)
    setErrors({})
    const fd = new FormData()
    fd.set('title', title)
    fd.set('description', description)
    fd.set('github_link', githubLink)
    fd.set('demo_link', demoLink)
    fd.set('status', status)
    fd.set('visibility', visibility)
    if (lookingForMembers) fd.set('looking_for_members', 'on')

    startTransition(async () => {
      const res =
        mode === 'edit' && project ? await updateProjectAction(project.id, fd) : await createProjectAction(fd)

      if (res.ok) {
        const newId =
          res.data && typeof res.data === 'object' && 'id' in res.data
            ? (res.data as { id: number }).id
            : null
        if (newId) router.push(`/project/${newId}`)
        else if (project) router.push(`/project/${project.id}`)
        else router.push('/project')
        router.refresh()
      } else {
        setGeneralError(res.message ?? 'Une erreur est survenue.')
        setErrors(res.errors ?? {})
      }
    })
  }

  return (
    <>
      <section className="pc-hero">
        <div className="container">
          <nav className="pc-breadcrumb" aria-label="Fil d'Ariane">
            <Link href="/" className="pc-crumb">
              Accueil
            </Link>
            <span className="pc-crumb-sep" aria-hidden>
              /
            </span>
            <Link href="/project" className="pc-crumb">
              Projets
            </Link>
            <span className="pc-crumb-sep" aria-hidden>
              /
            </span>
            <span className="pc-crumb pc-crumb--current">{mode === 'edit' ? 'Modifier' : 'Créer'}</span>
          </nav>

          <header className="pc-header">
            <div className="pc-title-block">
              <p className="pc-eyebrow">
                <i className="fas fa-code-branch" aria-hidden />
                Collaboration
              </p>
              <h1 className="pc-page-title">{mode === 'edit' ? 'Modifier le projet' : 'Créer un projet'}</h1>
              <p className="pc-lead">
                {mode === 'edit'
                  ? 'Actualisez la fiche, les liens et la visibilité pour votre équipe et les visiteurs.'
                  : 'Présentez l&apos;idée, la stack et vos besoins en contributeurs — la communauté tech burkinabè peut vous rejoindre.'}
              </p>
            </div>
            <Link href="/project" className="pc-back">
              <i className="fas fa-arrow-left" aria-hidden />
              Liste des projets
            </Link>
          </header>
        </div>
      </section>

      <div className="pc-body">
        <div className="container">
          <div className="pc-layout">
            <div className="pc-form-shell">
              <form className="pc-form" onSubmit={onSubmit} noValidate>
                {generalError && (
                  <div className="pc-alert pc-alert--error" role="alert">
                    <i className="fas fa-exclamation-circle" aria-hidden />
                    <span>{generalError}</span>
                  </div>
                )}

                <section className="pc-section" aria-labelledby="pc-base-title">
                  <div className="pc-section-head">
                    <span className="pc-section-icon" aria-hidden>
                      <i className="fas fa-info-circle" />
                    </span>
                    <div>
                      <h2 className="pc-section-title" id="pc-base-title">
                        Informations de base
                      </h2>
                      <p className="pc-section-desc">Titre et description visibles sur la fiche publique.</p>
                    </div>
                  </div>

                  <div className="pc-field">
                    <label htmlFor="title" className="pc-label">
                      Titre du projet <abbr title="obligatoire">*</abbr>
                    </label>
                    <span className="pc-hint">Court, mémorable, orienté produit ou impact.</span>
                    <input
                      type="text"
                      id="title"
                      name="title"
                      className={`pc-input${errors.title ? ' is-invalid' : ''}`}
                      placeholder="Ex. Marketplace artisanat — vitrine & paiement mobile"
                      value={title}
                      onChange={e => setTitle(e.target.value)}
                      required
                    />
                    {errors.title && (
                      <span className="pc-field-error" role="alert">
                        <i className="fas fa-exclamation-circle" aria-hidden /> {errors.title}
                      </span>
                    )}
                  </div>

                  <div className="pc-field">
                    <label htmlFor="description" className="pc-label">
                      Description <abbr title="obligatoire">*</abbr>
                    </label>
                    <span className="pc-hint">Objectifs, périmètre, technologies, besoins en compétences.</span>
                    <textarea
                      id="description"
                      name="description"
                      className={`pc-textarea pc-textarea--mid${errors.description ? ' is-invalid' : ''}`}
                      rows={6}
                      placeholder="Contexte, problème résolu, stack, état d&apos;avancement, profils recherchés…"
                      value={description}
                      onChange={e => setDescription(e.target.value)}
                      required
                    />
                    <div className="pc-counter">
                      <span>{charCount}</span> caractères · min. 20
                    </div>
                    {errors.description && (
                      <span className="pc-field-error" role="alert">
                        <i className="fas fa-exclamation-circle" aria-hidden /> {errors.description}
                      </span>
                    )}
                  </div>
                </section>

                <section className="pc-section" aria-labelledby="pc-links-title">
                  <div className="pc-section-head">
                    <span className="pc-section-icon" aria-hidden>
                      <i className="fas fa-link" />
                    </span>
                    <div>
                      <h2 className="pc-section-title" id="pc-links-title">
                        Liens
                      </h2>
                      <p className="pc-section-desc">GitHub et démo renforcent la crédibilité du projet.</p>
                    </div>
                  </div>

                  <div className="pc-row">
                    <div className="pc-field">
                      <label htmlFor="github_link" className="pc-label">
                        <i className="fab fa-github" aria-hidden /> GitHub
                      </label>
                      <span className="pc-hint">Dépôt public ou organisation.</span>
                      <input
                        type="url"
                        id="github_link"
                        name="github_link"
                        className={`pc-input${errors.github_link ? ' is-invalid' : ''}`}
                        placeholder="https://github.com/…"
                        value={githubLink}
                        onChange={e => setGithubLink(e.target.value)}
                        inputMode="url"
                      />
                      {errors.github_link && (
                        <span className="pc-field-error" role="alert">{errors.github_link}</span>
                      )}
                    </div>

                    <div className="pc-field">
                      <label htmlFor="demo_link" className="pc-label">
                        Démo en ligne
                      </label>
                      <span className="pc-hint">Site, prototype ou vidéo.</span>
                      <input
                        type="url"
                        id="demo_link"
                        name="demo_link"
                        className={`pc-input${errors.demo_link ? ' is-invalid' : ''}`}
                        placeholder="https://…"
                        value={demoLink}
                        onChange={e => setDemoLink(e.target.value)}
                        inputMode="url"
                      />
                      {errors.demo_link && (
                        <span className="pc-field-error" role="alert">{errors.demo_link}</span>
                      )}
                    </div>
                  </div>
                </section>

                <section className="pc-section pc-section--last" aria-labelledby="pc-settings-title">
                  <div className="pc-section-head">
                    <span className="pc-section-icon" aria-hidden>
                      <i className="fas fa-sliders-h" />
                    </span>
                    <div>
                      <h2 className="pc-section-title" id="pc-settings-title">
                        Paramètres
                      </h2>
                      <p className="pc-section-desc">Statut, visibilité et recrutement.</p>
                    </div>
                  </div>

                  <div className="pc-row">
                    <div className="pc-field">
                      <label htmlFor="status" className="pc-label">
                        Statut
                      </label>
                      <div className="pc-select-wrap">
                        <select
                          id="status"
                          name="status"
                          className="pc-select"
                          value={status}
                          onChange={e => setStatus(e.target.value as ProjectStatus)}
                        >
                          {statusChoices.map(o => (
                            <option key={o.value} value={o.value}>
                              {o.label}
                            </option>
                          ))}
                        </select>
                      </div>
                    </div>

                    <div className="pc-field">
                      <label htmlFor="visibility" className="pc-label">
                        Visibilité
                      </label>
                      <div className="pc-select-wrap">
                        <select
                          id="visibility"
                          name="visibility"
                          className="pc-select"
                          value={visibility}
                          onChange={e => setVisibility(e.target.value as ProjectVisibility)}
                        >
                          <option value="public">Public (catalogue)</option>
                          <option value="private">Privé</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div className="pc-field pc-field--flush">
                    <label className="pc-switch" htmlFor="looking_for_members">
                      <input
                        type="checkbox"
                        id="looking_for_members"
                        className="pc-checkbox"
                        checked={lookingForMembers}
                        onChange={e => setLookingForMembers(e.target.checked)}
                      />
                      <span className="pc-switch-body">
                        <strong>Recruter des membres</strong>
                        <span className="pc-switch-desc">
                          Affiche l&apos;étiquette « On recrute » et autorise les demandes d&apos;adhésion.
                        </span>
                      </span>
                    </label>
                  </div>
                </section>

                <div className="pc-actions">
                  <button type="submit" className="pc-submit" disabled={pending}>
                    <i className="fas fa-rocket" aria-hidden />
                    {pending
                      ? 'Enregistrement…'
                      : mode === 'edit'
                        ? 'Enregistrer les modifications'
                        : 'Créer le projet'}
                  </button>
                  <Link href="/project" className="pc-cancel">
                    <i className="fas fa-times" aria-hidden />
                    Annuler
                  </Link>
                </div>
              </form>
            </div>

            <aside className="pc-aside" aria-label="Conseils">
              <div className="pc-tip">
                <div className="pc-tip-icon" aria-hidden>
                  <i className="fas fa-lightbulb" />
                </div>
                <h3 className="pc-tip-title">Fiche attractive</h3>
                <ul className="pc-tip-list">
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Titre = produit ou mission, pas seulement un nom de code
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Mentionnez la stack et ce qui est déjà fait / à faire
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Liens GitHub & démo à jour
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Activez le recrutement seulement si vous pouvez répondre aux demandes
                  </li>
                </ul>
              </div>

              <div className="pc-tip pc-tip--accent">
                <div className="pc-tip-icon pc-tip-icon--alt" aria-hidden>
                  <i className="fas fa-users" />
                </div>
                <h3 className="pc-tip-title">Collaboration</h3>
                <p className="pc-tip-text">
                  Les projets avec description structurée et statut à jour reçoivent davantage de sollicitations
                  pertinentes depuis le catalogue mobile.
                </p>
              </div>
            </aside>
          </div>
        </div>
      </div>
    </>
  )
}
