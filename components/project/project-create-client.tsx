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
  const [lookingForMembers, setLookingForMembers] = useState(
    project?.looking_for_members ?? false
  )

  const charCount = description.length

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
        mode === 'edit' && project
          ? await updateProjectAction(project.id, fd)
          : await createProjectAction(fd)

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
    <section className="create-project-section">
      <div className="container">
        <div className="page-header-create">
          <div className="header-content">
            <h1>
              <i className="fas fa-rocket"></i>{' '}
              {mode === 'edit' ? 'Modifier le projet' : 'Créer un Projet'}
            </h1>
            <p>
              {mode === 'edit'
                ? 'Mettez à jour les informations de votre projet'
                : 'Lancez votre projet et trouvez des collaborateurs talentueux'}
            </p>
          </div>
          <Link href="/project" className="btn-back">
            <i className="fas fa-arrow-left"></i> Retour aux projets
          </Link>
        </div>

        <div className="create-project-wrapper">
          <div className="form-main">
            <form onSubmit={onSubmit} className="project-form">
              {generalError && (
                <div
                  style={{
                    background: '#f8d7da',
                    color: '#842029',
                    padding: 12,
                    borderRadius: 10,
                    marginBottom: 16,
                    fontSize: '0.9rem',
                  }}
                >
                  <i className="fas fa-exclamation-circle"></i> {generalError}
                </div>
              )}

              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-info-circle"></i>
                  <h3>Informations de base</h3>
                </div>

                <div className="form-group">
                  <label htmlFor="title">
                    Titre du projet *
                    <span className="field-hint">Un titre clair et accrocheur</span>
                  </label>
                  <input
                    type="text"
                    id="title"
                    name="title"
                    className={`form-control${errors.title ? ' is-invalid' : ''}`}
                    placeholder="Ex: Plateforme e-commerce pour l'artisanat burkinabè"
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
                    Description *
                    <span className="field-hint">Décrivez votre projet en détail</span>
                  </label>
                  <textarea
                    id="description"
                    name="description"
                    className={`form-control textarea-control${errors.description ? ' is-invalid' : ''}`}
                    rows={6}
                    placeholder="Décrivez l'objectif, les technologies utilisées, les fonctionnalités principales..."
                    value={description}
                    onChange={e => setDescription(e.target.value)}
                    required
                  ></textarea>
                  <div className="textarea-footer">
                    <span className="char-counter">
                      {charCount} caractères (min: 20)
                    </span>
                  </div>
                  {errors.description && (
                    <span className="error-message">
                      <i className="fas fa-exclamation-circle"></i>{' '}
                      {errors.description}
                    </span>
                  )}
                </div>
              </div>

              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-link"></i>
                  <h3>Liens du projet</h3>
                </div>

                <div className="form-row">
                  <div className="form-group">
                    <label htmlFor="github_link">
                      <i className="fab fa-github"></i> Lien GitHub
                      <span className="field-hint">Repository du code source</span>
                    </label>
                    <input
                      type="url"
                      id="github_link"
                      name="github_link"
                      className="form-control"
                      placeholder="https://github.com/username/repo"
                      value={githubLink}
                      onChange={e => setGithubLink(e.target.value)}
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="demo_link">
                      <i className="fas fa-external-link-alt"></i> Lien Démo
                      <span className="field-hint">Site web ou démo en ligne</span>
                    </label>
                    <input
                      type="url"
                      id="demo_link"
                      name="demo_link"
                      className="form-control"
                      placeholder="https://demo.example.com"
                      value={demoLink}
                      onChange={e => setDemoLink(e.target.value)}
                    />
                  </div>
                </div>
              </div>

              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-cog"></i>
                  <h3>Paramètres du projet</h3>
                </div>

                <div className="form-row">
                  <div className="form-group">
                    <label htmlFor="status">
                      <i className="fas fa-tasks"></i> Statut du projet
                    </label>
                    <select
                      id="status"
                      name="status"
                      className="form-control select-control"
                      value={status}
                      onChange={e => setStatus(e.target.value as ProjectStatus)}
                    >
                      <option value="planning">📋 Planification</option>
                      <option value="in_progress">🚀 En cours</option>
                      <option value="active">🚀 Actif</option>
                      <option value="paused">⏸️ En pause</option>
                      <option value="completed">✅ Terminé</option>
                    </select>
                  </div>

                  <div className="form-group">
                    <label htmlFor="visibility">
                      <i className="fas fa-eye"></i> Visibilité
                    </label>
                    <select
                      id="visibility"
                      name="visibility"
                      className="form-control select-control"
                      value={visibility}
                      onChange={e => setVisibility(e.target.value as ProjectVisibility)}
                    >
                      <option value="public">🌍 Public</option>
                      <option value="private">🔒 Privé</option>
                    </select>
                  </div>
                </div>

                <div className="form-group">
                  <div className="checkbox-card">
                    <input
                      type="checkbox"
                      id="looking_for_members"
                      name="looking_for_members"
                      checked={lookingForMembers}
                      onChange={e => setLookingForMembers(e.target.checked)}
                    />
                    <label htmlFor="looking_for_members" className="checkbox-label">
                      <div className="checkbox-icon">
                        <i className="fas fa-users"></i>
                      </div>
                      <div className="checkbox-content">
                        <strong>Recherche de membres</strong>
                        <p>Je recherche des collaborateurs pour ce projet</p>
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <div className="form-actions">
                <button type="submit" className="btn-submit" disabled={pending}>
                  <i className="fas fa-rocket"></i>{' '}
                  {pending
                    ? 'Enregistrement…'
                    : mode === 'edit'
                      ? 'Enregistrer les modifications'
                      : 'Créer le projet'}
                </button>
                <Link href="/project" className="btn-cancel">
                  <i className="fas fa-times"></i> Annuler
                </Link>
              </div>
            </form>
          </div>

          <aside className="tips-sidebar">
            <div className="tip-card">
              <div className="tip-icon">
                <i className="fas fa-lightbulb"></i>
              </div>
              <h3>Conseils pour réussir</h3>
              <ul className="tips-list">
                <li>
                  <i className="fas fa-check-circle"></i> Choisissez un titre clair et
                  descriptif
                </li>
                <li>
                  <i className="fas fa-check-circle"></i> Détaillez les technologies
                  utilisées
                </li>
                <li>
                  <i className="fas fa-check-circle"></i> Précisez les compétences
                  recherchées
                </li>
                <li>
                  <i className="fas fa-check-circle"></i> Ajoutez des liens vers GitHub
                  et démo
                </li>
                <li>
                  <i className="fas fa-check-circle"></i> Mettez à jour le statut
                  régulièrement
                </li>
              </ul>
            </div>

            <div className="tip-card stats-card">
              <div className="tip-icon">
                <i className="fas fa-chart-line"></i>
              </div>
              <h3>Statistiques</h3>
              <div className="stats-info">
                <div className="stat-item">
                  <span className="stat-value">+50%</span>
                  <span className="stat-label">Plus de visibilité</span>
                </div>
                <div className="stat-item">
                  <span className="stat-value">3x</span>
                  <span className="stat-label">Plus de collaborations</span>
                </div>
              </div>
              <p className="stats-note">
                Les projets bien documentés attirent plus de collaborateurs
              </p>
            </div>

            <div className="tip-card">
              <div className="tip-icon">
                <i className="fas fa-trophy"></i>
              </div>
              <h3>Gagnez des badges</h3>
              <p>
                Créez des projets pour débloquer des badges et améliorer votre profil !
              </p>
            </div>
          </aside>
        </div>
      </div>
    </section>
  )
}
