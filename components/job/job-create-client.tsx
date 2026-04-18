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
  { value: 'stage', label: 'Stage' },
  { value: 'emploi', label: 'Emploi' },
  { value: 'freelance', label: 'Freelance' },
  { value: 'hackathon', label: 'Hackathon' },
  { value: 'formation', label: 'Formation' },
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
        mode === 'edit' && job ? await updateJobAction(job.id, fd) : await createJobAction(fd)
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
    <div className="job-create-saas">
      <section className="jc-hero">
        <div className="container">
          <nav className="jc-breadcrumb" aria-label="Fil d'Ariane">
            <Link href="/" className="jc-crumb">
              Accueil
            </Link>
            <span className="jc-crumb-sep" aria-hidden>
              /
            </span>
            <Link href="/job" className="jc-crumb">
              Opportunités
            </Link>
            <span className="jc-crumb-sep" aria-hidden>
              /
            </span>
            <span className="jc-crumb jc-crumb--current">
              {mode === 'edit' ? 'Modifier' : 'Publier'}
            </span>
          </nav>

          <header className="jc-header">
            <div className="jc-title-block">
              <p className="jc-eyebrow">
                <i className="fas fa-briefcase" aria-hidden />
                {mode === 'edit' ? 'Édition' : 'Recrutement'}
              </p>
              <h1 className="jc-page-title">{mode === 'edit' ? "Modifier l'offre" : 'Publier une offre'}</h1>
              <p className="jc-lead">
                {mode === 'edit'
                  ? 'Mettez à jour les informations ; les candidats voient les changements immédiatement.'
                  : 'Rédigez une annonce claire pour attirer les bons profils dans la communauté tech burkinabè.'}
              </p>
            </div>
            <Link href="/job" className="jc-back">
              <i className="fas fa-arrow-left" aria-hidden />
              Liste des offres
            </Link>
          </header>
        </div>
      </section>

      <div className="jc-body">
        <div className="container">
          <div className="jc-layout">
            <div className="jc-form-shell">
              <form onSubmit={onSubmit} className="jc-form" noValidate>
                {generalError && (
                  <div className="jc-alert jc-alert--error" role="alert">
                    <i className="fas fa-exclamation-circle" aria-hidden />
                    <span>{generalError}</span>
                  </div>
                )}

                <section className="jc-section" aria-labelledby="jc-main-title">
                  <div className="jc-section-head">
                    <span className="jc-section-icon" aria-hidden>
                      <i className="fas fa-info-circle" />
                    </span>
                    <div>
                      <h2 className="jc-section-title" id="jc-main-title">
                        Informations principales
                      </h2>
                      <p className="jc-section-desc">Type, lieu et accroche de l&apos;annonce.</p>
                    </div>
                  </div>

                  <div className="jc-row">
                    <div className="jc-field">
                      <label htmlFor="type" className="jc-label">
                        Type d&apos;offre <abbr title="obligatoire">*</abbr>
                      </label>
                      <div className="jc-select-wrap">
                        <select
                          id="type"
                          className="jc-select"
                          value={type}
                          onChange={e => setType(e.target.value)}
                          required
                          aria-required="true"
                        >
                          {TYPES.map(t => (
                            <option key={t.value} value={t.value}>
                              {t.label}
                            </option>
                          ))}
                        </select>
                      </div>
                    </div>

                    <div className="jc-field">
                      <label htmlFor="city" className="jc-label">
                        Ville <abbr title="obligatoire">*</abbr>
                      </label>
                      <div className="jc-select-wrap">
                        <select
                          id="city"
                          className="jc-select"
                          value={city}
                          onChange={e => setCity(e.target.value)}
                          required
                          aria-required="true"
                        >
                          <option value="">Choisir une ville</option>
                          {CITIES.map(c => (
                            <option key={c} value={c}>
                              {c}
                            </option>
                          ))}
                        </select>
                      </div>
                    </div>
                  </div>

                  <div className="jc-field">
                    <label htmlFor="title" className="jc-label">
                      Titre de l&apos;offre <abbr title="obligatoire">*</abbr>
                    </label>
                    <span className="jc-hint">Une phrase précise : rôle + niveau ou techno principale.</span>
                    <input
                      type="text"
                      id="title"
                      className={`jc-input${errors.title ? ' is-invalid' : ''}`}
                      placeholder="Ex. Développeur full-stack junior — React & Node"
                      value={title}
                      onChange={e => setTitle(e.target.value)}
                      required
                      autoComplete="off"
                    />
                    {errors.title && (
                      <span className="jc-field-error" role="alert">
                        <i className="fas fa-exclamation-circle" aria-hidden /> {errors.title}
                      </span>
                    )}
                  </div>

                  <div className="jc-field">
                    <label htmlFor="company_name" className="jc-label">
                      Entreprise ou organisation
                    </label>
                    <span className="jc-hint">Visible sur l&apos;annonce. Laissez vide pour utiliser votre nom de profil.</span>
                    <input
                      type="text"
                      id="company_name"
                      className="jc-input"
                      placeholder="Ex. Startup BF, ONG, ministère…"
                      value={companyName}
                      onChange={e => setCompanyName(e.target.value)}
                    />
                  </div>
                </section>

                <section className="jc-section" aria-labelledby="jc-desc-title">
                  <div className="jc-section-head">
                    <span className="jc-section-icon" aria-hidden>
                      <i className="fas fa-align-left" />
                    </span>
                    <div>
                      <h2 className="jc-section-title" id="jc-desc-title">
                        Description & compétences
                      </h2>
                      <p className="jc-section-desc">Soyez transparent sur les missions et le profil attendu.</p>
                    </div>
                  </div>

                  <div className="jc-field">
                    <label htmlFor="description" className="jc-label">
                      Description détaillée <abbr title="obligatoire">*</abbr>
                    </label>
                    <span className="jc-hint">Missions, stack, conditions de travail, processus de recrutement…</span>
                    <textarea
                      id="description"
                      className={`jc-textarea${errors.description ? ' is-invalid' : ''}`}
                      rows={10}
                      placeholder="Décrivez le poste comme vous le présenteriez à un candidat…"
                      value={description}
                      onChange={e => setDescription(e.target.value)}
                      required
                    />
                    <div className="jc-counter">
                      <span>{description.length}</span> caractères · minimum recommandé 50
                    </div>
                    {errors.description && (
                      <span className="jc-field-error" role="alert">
                        <i className="fas fa-exclamation-circle" aria-hidden /> {errors.description}
                      </span>
                    )}
                  </div>

                  <div className="jc-field">
                    <label htmlFor="skills_required" className="jc-label">
                      Compétences requises
                    </label>
                    <span className="jc-hint">Séparez par des virgules (ex. React, Node.js, PostgreSQL, Git).</span>
                    <input
                      type="text"
                      id="skills_required"
                      className="jc-input"
                      placeholder="React, Node.js, PostgreSQL, Git…"
                      value={skills}
                      onChange={e => setSkills(e.target.value)}
                    />
                  </div>
                </section>

                <section className="jc-section jc-section--last" aria-labelledby="jc-extra-title">
                  <div className="jc-section-head">
                    <span className="jc-section-icon" aria-hidden>
                      <i className="fas fa-sliders-h" />
                    </span>
                    <div>
                      <h2 className="jc-section-title" id="jc-extra-title">
                        Rémunération, échéance & lien
                      </h2>
                      <p className="jc-section-desc">Optionnel mais augmente la confiance des candidats.</p>
                    </div>
                  </div>

                  <div className="jc-row">
                    <div className="jc-field">
                      <label htmlFor="salary" className="jc-label">
                        Rémunération
                      </label>
                      <span className="jc-hint">Fourchette ou mention « selon profil ».</span>
                      <input
                        type="text"
                        id="salary"
                        className="jc-input"
                        placeholder="250 000 – 400 000 FCFA / mois"
                        value={salary}
                        onChange={e => setSalary(e.target.value)}
                      />
                    </div>

                    <div className="jc-field">
                      <label htmlFor="deadline" className="jc-label">
                        Date limite de candidature
                      </label>
                      <input
                        type="date"
                        id="deadline"
                        className="jc-input jc-input--date"
                        value={deadline ?? ''}
                        onChange={e => setDeadline(e.target.value)}
                      />
                    </div>
                  </div>

                  <div className="jc-field">
                    <label htmlFor="external_link" className="jc-label">
                      Lien externe (candidature)
                    </label>
                    <span className="jc-hint">ATS, site carrière ou formulaire officiel.</span>
                    <input
                      type="url"
                      id="external_link"
                      className={`jc-input${errors.external_link ? ' is-invalid' : ''}`}
                      placeholder="https://…"
                      value={externalLink}
                      onChange={e => setExternalLink(e.target.value)}
                      inputMode="url"
                    />
                    {errors.external_link && (
                      <span className="jc-field-error" role="alert">
                        <i className="fas fa-exclamation-circle" aria-hidden /> {errors.external_link}
                      </span>
                    )}
                  </div>
                </section>

                <div className="jc-actions">
                  <button type="submit" className="jc-submit" disabled={pending}>
                    <i className="fas fa-paper-plane" aria-hidden />
                    {pending
                      ? 'Enregistrement…'
                      : mode === 'edit'
                        ? 'Enregistrer les modifications'
                        : "Publier l'offre"}
                  </button>
                  <Link href="/job" className="jc-cancel">
                    <i className="fas fa-times" aria-hidden />
                    Annuler
                  </Link>
                </div>
              </form>
            </div>

            <aside className="jc-aside" aria-label="Conseils">
              <div className="jc-tip">
                <div className="jc-tip-icon" aria-hidden>
                  <i className="fas fa-lightbulb" />
                </div>
                <h3 className="jc-tip-title">Bonnes pratiques</h3>
                <ul className="jc-tip-list">
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Titre lisible en une ligne sur mobile
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Missions et livrables concrets
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Stack et niveau attendu explicites
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Fourchette salariale ou fourchette « selon expérience »
                  </li>
                  <li>
                    <i className="fas fa-check" aria-hidden />
                    Date limite réaliste
                  </li>
                </ul>
              </div>

              <div className="jc-tip jc-tip--accent">
                <div className="jc-tip-icon jc-tip-icon--chart" aria-hidden>
                  <i className="fas fa-chart-line" />
                </div>
                <h3 className="jc-tip-title">Visibilité</h3>
                <p className="jc-tip-text">
                  Les annonces structurées reçoivent davantage de candidatures qualifiées. Pensez aux mots-clés que les
                  profils tech burkinabè utilisent dans leur recherche.
                </p>
              </div>
            </aside>
          </div>
        </div>
      </div>
    </div>
  )
}
