'use client'

import { useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { createJobAction, updateJobAction } from '@/app/actions/jobs'
import type { Job } from '@/types'

interface Props {
  mode: 'create' | 'edit'
  job?: Job
}

const TYPES: Array<{ value: string; label: string }> = [
  { value: 'stage', label: '🎓 Stage' },
  { value: 'emploi', label: '💼 Emploi' },
  { value: 'freelance', label: '💻 Freelance' },
  { value: 'hackathon', label: '🏆 Hackathon' },
  { value: 'formation', label: '📚 Formation' },
]

const CITIES = ['Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Ouahigouya', 'Autre']

export function JobCreateClient({ mode, job }: Props) {
  const router = useRouter()
  const [pending, startTransition] = useTransition()
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [generalError, setGeneralError] = useState<string | null>(null)

  const [type, setType] = useState<string>(job?.type ?? 'emploi')
  const [title, setTitle] = useState(job?.title ?? '')
  const [companyName, setCompanyName] = useState(job?.company_name ?? '')
  const [city, setCity] = useState(job?.city ?? '')
  const [salary, setSalary] = useState(job?.salary ?? '')
  const [deadline, setDeadline] = useState(job?.deadline ?? '')
  const [description, setDescription] = useState(job?.description ?? '')
  const [skills, setSkills] = useState(job?.skills_required ?? '')
  const [externalLink, setExternalLink] = useState(job?.external_link ?? '')

  const onSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setGeneralError(null)
    setErrors({})
    const fd = new FormData()
    fd.set('type', type)
    fd.set('title', title)
    fd.set('company_name', companyName)
    fd.set('city', city)
    fd.set('salary', salary)
    fd.set('deadline', deadline)
    fd.set('description', description)
    fd.set('skills_required', skills)
    fd.set('external_link', externalLink)

    startTransition(async () => {
      const res =
        mode === 'edit' && job
          ? await updateJobAction(job.id, fd)
          : await createJobAction(fd)
      if (res.ok) {
        const newId =
          res.data && typeof res.data === 'object' && 'id' in res.data
            ? (res.data as { id: number }).id
            : null
        router.push(`/job/${newId ?? job?.id ?? ''}`)
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
              <i className="fas fa-briefcase"></i>{' '}
              {mode === 'edit' ? "Modifier l'offre" : 'Publier une Offre'}
            </h1>
            <p>
              {mode === 'edit'
                ? "Mettez à jour les informations de l'offre"
                : 'Publiez une opportunité pour toucher la communauté tech burkinabè'}
            </p>
          </div>
          <Link href="/job" className="btn-back">
            <i className="fas fa-arrow-left"></i> Retour aux offres
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
                  <h3>Informations principales</h3>
                </div>

                <div className="form-row">
                  <div className="form-group">
                    <label htmlFor="type">
                      <i className="fas fa-tags"></i> Type d&apos;offre *
                    </label>
                    <select
                      id="type"
                      className="form-control select-control"
                      value={type}
                      onChange={e => setType(e.target.value)}
                      required
                    >
                      {TYPES.map(t => (
                        <option key={t.value} value={t.value}>
                          {t.label}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="form-group">
                    <label htmlFor="city">
                      <i className="fas fa-map-marker-alt"></i> Ville *
                    </label>
                    <select
                      id="city"
                      className="form-control select-control"
                      value={city}
                      onChange={e => setCity(e.target.value)}
                      required
                    >
                      <option value="">— Sélectionner —</option>
                      {CITIES.map(c => (
                        <option key={c} value={c}>
                          {c}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>

                <div className="form-group">
                  <label htmlFor="title">
                    Titre de l&apos;offre *
                    <span className="field-hint">Un titre clair et accrocheur</span>
                  </label>
                  <input
                    type="text"
                    id="title"
                    className={`form-control${errors.title ? ' is-invalid' : ''}`}
                    placeholder="Ex : Développeur Full-Stack Junior"
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
                  <label htmlFor="company_name">
                    <i className="fas fa-building"></i> Entreprise / Organisation
                    <span className="field-hint">
                      Par défaut : votre nom d&apos;utilisateur
                    </span>
                  </label>
                  <input
                    type="text"
                    id="company_name"
                    className="form-control"
                    placeholder="Nom de l'entreprise"
                    value={companyName}
                    onChange={e => setCompanyName(e.target.value)}
                  />
                </div>
              </div>

              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-file-alt"></i>
                  <h3>Description de l&apos;offre</h3>
                </div>

                <div className="form-group">
                  <label htmlFor="description">
                    Description détaillée *
                    <span className="field-hint">
                      Missions, profil recherché, compétences, conditions…
                    </span>
                  </label>
                  <textarea
                    id="description"
                    className={`form-control textarea-control${errors.description ? ' is-invalid' : ''}`}
                    rows={10}
                    placeholder="Décrivez la mission, les responsabilités, les conditions..."
                    value={description}
                    onChange={e => setDescription(e.target.value)}
                    required
                  ></textarea>
                  <div className="textarea-footer">
                    <span className="char-counter">
                      {description.length} caractères (min: 50)
                    </span>
                  </div>
                  {errors.description && (
                    <span className="error-message">
                      <i className="fas fa-exclamation-circle"></i>{' '}
                      {errors.description}
                    </span>
                  )}
                </div>

                <div className="form-group">
                  <label htmlFor="skills_required">
                    <i className="fas fa-tools"></i> Compétences requises
                    <span className="field-hint">
                      Séparez par des virgules (ex : React, Node.js, MongoDB)
                    </span>
                  </label>
                  <input
                    type="text"
                    id="skills_required"
                    className="form-control"
                    placeholder="React, Node.js, PostgreSQL, Git..."
                    value={skills}
                    onChange={e => setSkills(e.target.value)}
                  />
                </div>
              </div>

              <div className="form-section">
                <div className="section-title">
                  <i className="fas fa-cog"></i>
                  <h3>Conditions & contact</h3>
                </div>

                <div className="form-row">
                  <div className="form-group">
                    <label htmlFor="salary">
                      <i className="fas fa-money-bill-wave"></i> Rémunération
                      <span className="field-hint">Exemple : 200 000 - 400 000 FCFA</span>
                    </label>
                    <input
                      type="text"
                      id="salary"
                      className="form-control"
                      placeholder="250 000 FCFA / mois"
                      value={salary}
                      onChange={e => setSalary(e.target.value)}
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="deadline">
                      <i className="fas fa-calendar-alt"></i> Date limite
                    </label>
                    <input
                      type="date"
                      id="deadline"
                      className="form-control"
                      value={deadline ?? ''}
                      onChange={e => setDeadline(e.target.value)}
                    />
                  </div>
                </div>

                <div className="form-group">
                  <label htmlFor="external_link">
                    <i className="fas fa-external-link-alt"></i> Lien externe
                    <span className="field-hint">
                      Optionnel : site officiel où postuler
                    </span>
                  </label>
                  <input
                    type="url"
                    id="external_link"
                    className={`form-control${errors.external_link ? ' is-invalid' : ''}`}
                    placeholder="https://entreprise.com/offre"
                    value={externalLink}
                    onChange={e => setExternalLink(e.target.value)}
                  />
                  {errors.external_link && (
                    <span className="error-message">
                      <i className="fas fa-exclamation-circle"></i>{' '}
                      {errors.external_link}
                    </span>
                  )}
                </div>
              </div>

              <div className="form-actions">
                <button type="submit" className="btn-submit" disabled={pending}>
                  <i className="fas fa-paper-plane"></i>{' '}
                  {pending
                    ? 'Enregistrement…'
                    : mode === 'edit'
                      ? 'Enregistrer les modifications'
                      : "Publier l'offre"}
                </button>
                <Link href="/job" className="btn-cancel">
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
              <h3>Conseils pour une offre réussie</h3>
              <ul className="tips-list">
                <li>
                  <i className="fas fa-check-circle"></i> Titre clair et précis
                </li>
                <li>
                  <i className="fas fa-check-circle"></i> Missions bien décrites
                </li>
                <li>
                  <i className="fas fa-check-circle"></i> Compétences précises
                </li>
                <li>
                  <i className="fas fa-check-circle"></i> Conditions transparentes
                </li>
                <li>
                  <i className="fas fa-check-circle"></i> Date limite réaliste
                </li>
              </ul>
            </div>

            <div className="tip-card stats-card">
              <div className="tip-icon">
                <i className="fas fa-chart-line"></i>
              </div>
              <h3>Visibilité</h3>
              <div className="stats-info">
                <div className="stat-item">
                  <span className="stat-value">500+</span>
                  <span className="stat-label">Étudiants actifs</span>
                </div>
                <div className="stat-item">
                  <span className="stat-value">24h</span>
                  <span className="stat-label">Temps de publication</span>
                </div>
              </div>
              <p className="stats-note">
                Les offres bien rédigées reçoivent jusqu&apos;à 3× plus de candidatures.
              </p>
            </div>
          </aside>
        </div>
      </div>
    </section>
  )
}
