'use client'

import Link from 'next/link'
import { useState } from 'react'
import { createClient } from '@/lib/supabase/client'

const CITIES = [
  'Ouagadougou',
  'Bobo-Dioulasso',
  'Koudougou',
  'Ouahigouya',
  'Banfora',
  'Dédougou',
  'Kaya',
  'Tenkodogo',
  "Fada N'Gourma",
  'Autre',
]

export type RegisterKindSlug = 'etudiant' | 'professionnel' | 'entreprise'

const KIND_META: Record<
  RegisterKindSlug,
  { pill: string; title: string; subtitle: string; accountKind: 'student' | 'professional' | 'enterprise' }
> = {
  etudiant: {
    pill: 'Profil étudiant',
    title: 'Créer un compte étudiant',
    subtitle: 'Accédez aux formations, au forum et aux projets collaboratifs.',
    accountKind: 'student',
  },
  professionnel: {
    pill: 'Profil professionnel',
    title: 'Créer un compte professionnel',
    subtitle: 'Échangez avec la communauté, postulez et mettez en avant votre expertise.',
    accountKind: 'professional',
  },
  entreprise: {
    pill: 'Compte entreprise',
    title: 'Inscrire votre organisation',
    subtitle: 'Compte recruteur pour publier des offres et gérer vos candidatures.',
    accountKind: 'enterprise',
  },
}

function validatePassword(p: string): string | null {
  if (p.length < 8) return 'Au moins 8 caractères'
  if (!/[A-Z]/.test(p)) return 'Une majuscule requise'
  if (!/[a-z]/.test(p)) return 'Une minuscule requise'
  if (!/[0-9]/.test(p)) return 'Un chiffre requis'
  return null
}

export function RegisterDynamicForm({ kind }: { kind: RegisterKindSlug }) {
  const meta = KIND_META[kind]
  const [email, setEmail] = useState('')
  const [prenom, setPrenom] = useState('')
  const [nom, setNom] = useState('')
  const [phone, setPhone] = useState('')
  const [city, setCity] = useState('')
  const [password, setPassword] = useState('')
  const [password2, setPassword2] = useState('')
  // student
  const [university, setUniversity] = useState('')
  const [faculty, setFaculty] = useState('')
  // professional
  const [jobTitle, setJobTitle] = useState('')
  const [employer, setEmployer] = useState('')
  const [expertise, setExpertise] = useState('')
  // enterprise
  const [orgName, setOrgName] = useState('')
  const [sector, setSector] = useState('')

  const [errors, setErrors] = useState<Record<string, string>>({})
  const [serverError, setServerError] = useState('')
  const [loading, setLoading] = useState(false)
  const [success, setSuccess] = useState(false)

  const submit = async (e: React.FormEvent) => {
    e.preventDefault()
    setErrors({})
    setServerError('')
    const eMap: Record<string, string> = {}

    if (prenom.trim().length < 2) eMap.prenom = 'Prénom requis (2 car. min)'
    if (nom.trim().length < 2) eMap.nom = 'Nom requis (2 car. min)'
    if (!email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) eMap.email = 'Email invalide'
    if (!phone.trim()) eMap.phone = 'Téléphone requis'
    if (!city) eMap.city = 'Ville requise'

    if (kind === 'etudiant') {
      if (!university.trim()) eMap.university = 'Établissement requis'
      if (!faculty.trim()) eMap.faculty = 'Filière / parcours requis'
    } else if (kind === 'professionnel') {
      if (!jobTitle.trim()) eMap.jobTitle = 'Métier ou titre requis'
      if (!employer.trim()) eMap.employer = 'Structure ou « Indépendant·e »'
      if (!expertise.trim()) eMap.expertise = 'Domaine d’expertise requis'
    } else {
      if (!orgName.trim()) eMap.orgName = 'Raison sociale requise'
      if (!sector.trim()) eMap.sector = 'Secteur d’activité requis'
    }

    const pe = validatePassword(password)
    if (pe) eMap.password = pe
    if (password !== password2) eMap.password2 = 'Les mots de passe diffèrent'

    if (Object.keys(eMap).length) {
      setErrors(eMap)
      return
    }

    let universityMeta = ''
    let facultyMeta = ''
    let organizationName: string | undefined
    let jobTitleMeta: string | undefined

    if (kind === 'etudiant') {
      universityMeta = university.trim()
      facultyMeta = faculty.trim()
    } else if (kind === 'professionnel') {
      universityMeta = employer.trim()
      facultyMeta = expertise.trim()
      jobTitleMeta = jobTitle.trim()
    } else {
      organizationName = orgName.trim()
      universityMeta = orgName.trim()
      facultyMeta = sector.trim()
    }

    const userMeta: Record<string, string> = {
      prenom: prenom.trim(),
      nom: nom.trim(),
      phone: `+226 ${phone.trim()}`,
      university: universityMeta,
      faculty: facultyMeta,
      city,
      account_kind: meta.accountKind,
    }
    if (organizationName) userMeta.organization_name = organizationName
    if (jobTitleMeta) userMeta.job_title = jobTitleMeta

    setLoading(true)
    try {
      const supabase = createClient()
      const { error } = await supabase.auth.signUp({
        email: email.trim().toLowerCase(),
        password,
        options: {
          data: userMeta,
          emailRedirectTo: `${window.location.origin}/api/auth/callback`,
        },
      })
      if (error) {
        if (error.message.includes('already registered') || error.message.includes('already in use')) {
          setServerError('Cette adresse email est déjà utilisée.')
        } else {
          setServerError(error.message)
        }
        return
      }
      setSuccess(true)
    } catch {
      setServerError('Une erreur est survenue. Veuillez réessayer.')
    } finally {
      setLoading(false)
    }
  }

  if (success) {
    return (
      <div className="au-success">
        <div className="au-success-icon" aria-hidden>
          <i className="fas fa-check" />
        </div>
        <h2>Compte créé</h2>
        <p>
          Un email de confirmation a été envoyé à <strong>{email}</strong>. Après validation, connectez-vous avec la
          même page de connexion que tout le monde.
        </p>
        <Link href="/login" className="au-link-btn">
          Se connecter
        </Link>
      </div>
    )
  }

  return (
    <form className="au-form" onSubmit={submit} noValidate>
      {serverError && (
        <div className="au-alert" role="alert">
          <i className="fas fa-exclamation-circle" aria-hidden />
          <span>{serverError}</span>
        </div>
      )}

      <div className="au-row au-row-2">
        <div className="au-field">
          <label htmlFor="prenom">Prénom *</label>
          <input
            id="prenom"
            className={`au-input${errors.prenom ? ' au-invalid' : ''}`}
            value={prenom}
            onChange={e => setPrenom(e.target.value)}
            autoComplete="given-name"
          />
          {errors.prenom && <div className="au-error">{errors.prenom}</div>}
        </div>
        <div className="au-field">
          <label htmlFor="nom">Nom *</label>
          <input
            id="nom"
            className={`au-input${errors.nom ? ' au-invalid' : ''}`}
            value={nom}
            onChange={e => setNom(e.target.value)}
            autoComplete="family-name"
          />
          {errors.nom && <div className="au-error">{errors.nom}</div>}
        </div>
      </div>

      {kind === 'etudiant' && (
        <div className="au-row au-row-2">
          <div className="au-field">
            <label htmlFor="university">Université / école *</label>
            <input
              id="university"
              className={`au-input${errors.university ? ' au-invalid' : ''}`}
              value={university}
              onChange={e => setUniversity(e.target.value)}
              placeholder="Ex. Université Joseph Ki-Zerbo"
            />
            {errors.university && <div className="au-error">{errors.university}</div>}
          </div>
          <div className="au-field">
            <label htmlFor="faculty">Filière / niveau *</label>
            <input
              id="faculty"
              className={`au-input${errors.faculty ? ' au-invalid' : ''}`}
              value={faculty}
              onChange={e => setFaculty(e.target.value)}
              placeholder="Ex. Génie logiciel — Master 1"
            />
            {errors.faculty && <div className="au-error">{errors.faculty}</div>}
          </div>
        </div>
      )}

      {kind === 'professionnel' && (
        <>
          <div className="au-field">
            <label htmlFor="jobTitle">Métier ou titre *</label>
            <span className="au-hint">Visible sur votre profil public</span>
            <input
              id="jobTitle"
              className={`au-input${errors.jobTitle ? ' au-invalid' : ''}`}
              value={jobTitle}
              onChange={e => setJobTitle(e.target.value)}
              placeholder="Ex. Développeuse full-stack"
            />
            {errors.jobTitle && <div className="au-error">{errors.jobTitle}</div>}
          </div>
          <div className="au-row au-row-2">
            <div className="au-field">
              <label htmlFor="employer">Structure *</label>
              <input
                id="employer"
                className={`au-input${errors.employer ? ' au-invalid' : ''}`}
                value={employer}
                onChange={e => setEmployer(e.target.value)}
                placeholder="Employeur, cabinet ou Indépendant·e"
              />
              {errors.employer && <div className="au-error">{errors.employer}</div>}
            </div>
            <div className="au-field">
              <label htmlFor="expertise">Domaine *</label>
              <input
                id="expertise"
                className={`au-input${errors.expertise ? ' au-invalid' : ''}`}
                value={expertise}
                onChange={e => setExpertise(e.target.value)}
                placeholder="Ex. Web, Data, Cybersécurité"
              />
              {errors.expertise && <div className="au-error">{errors.expertise}</div>}
            </div>
          </div>
        </>
      )}

      {kind === 'entreprise' && (
        <>
          <div className="au-field">
            <label htmlFor="orgName">Raison sociale *</label>
            <input
              id="orgName"
              className={`au-input${errors.orgName ? ' au-invalid' : ''}`}
              value={orgName}
              onChange={e => setOrgName(e.target.value)}
              placeholder="Nom légal de l’organisation"
            />
            {errors.orgName && <div className="au-error">{errors.orgName}</div>}
          </div>
          <div className="au-field">
            <label htmlFor="sector">Secteur d’activité *</label>
            <input
              id="sector"
              className={`au-input${errors.sector ? ' au-invalid' : ''}`}
              value={sector}
              onChange={e => setSector(e.target.value)}
              placeholder="Ex. Fintech, SaaS, ESN, ONG…"
            />
            {errors.sector && <div className="au-error">{errors.sector}</div>}
          </div>
          <p className="au-hint" style={{ margin: 0 }}>
            Le compte sera rattaché à <strong>{prenom || '…'} {nom || '…'}</strong> comme contact principal.
          </p>
        </>
      )}

      <div className="au-field">
        <label htmlFor="email">Email *</label>
        <input
          id="email"
          type="email"
          className={`au-input${errors.email ? ' au-invalid' : ''}`}
          value={email}
          onChange={e => setEmail(e.target.value)}
          autoComplete="email"
        />
        {errors.email && <div className="au-error">{errors.email}</div>}
      </div>

      <div className="au-field">
        <label htmlFor="phone">Téléphone *</label>
        <div className={`au-phone-wrap${errors.phone ? ' au-invalid' : ''}`}>
          <span className="au-phone-prefix">+226</span>
          <input
            id="phone"
            className="au-input"
            value={phone}
            onChange={e => setPhone(e.target.value)}
            placeholder="XX XX XX XX"
            autoComplete="tel-national"
          />
        </div>
        {errors.phone && <div className="au-error">{errors.phone}</div>}
      </div>

      <div className="au-field">
        <label htmlFor="city">Ville *</label>
        <select
          id="city"
          className={`au-select${errors.city ? ' au-invalid' : ''}`}
          value={city}
          onChange={e => setCity(e.target.value)}
        >
          <option value="">Choisir…</option>
          {CITIES.map(c => (
            <option key={c} value={c}>
              {c}
            </option>
          ))}
        </select>
        {errors.city && <div className="au-error">{errors.city}</div>}
      </div>

      <div className="au-row au-row-2">
        <div className="au-field">
          <label htmlFor="password">Mot de passe *</label>
          <span className="au-hint">8+ caractères, majuscule, minuscule, chiffre</span>
          <input
            id="password"
            type="password"
            className={`au-input${errors.password ? ' au-invalid' : ''}`}
            value={password}
            onChange={e => setPassword(e.target.value)}
            autoComplete="new-password"
          />
          {errors.password && <div className="au-error">{errors.password}</div>}
        </div>
        <div className="au-field">
          <label htmlFor="password2">Confirmation *</label>
          <input
            id="password2"
            type="password"
            className={`au-input${errors.password2 ? ' au-invalid' : ''}`}
            value={password2}
            onChange={e => setPassword2(e.target.value)}
            autoComplete="new-password"
          />
          {errors.password2 && <div className="au-error">{errors.password2}</div>}
        </div>
      </div>

      <button type="submit" className="au-btn au-btn-primary" disabled={loading}>
        {loading ? (
          <>
            <i className="fas fa-spinner fa-spin" aria-hidden />
            Création…
          </>
        ) : (
          <>
            <i className="fas fa-user-plus" aria-hidden />
            Finaliser l’inscription
          </>
        )}
      </button>

      <p className="au-footer-text">
        Déjà inscrit·e ? <Link href="/login">Se connecter</Link>
      </p>
    </form>
  )
}

export function RegisterFormHeader({ kind }: { kind: RegisterKindSlug }) {
  const meta = KIND_META[kind]
  return (
    <div className="au-card-head">
      <div className="au-kind-pill">{meta.pill}</div>
      <h1>{meta.title}</h1>
      <p>{meta.subtitle}</p>
    </div>
  )
}
