'use client'

import { useState, useTransition, useRef } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { buildAvatarUrl, buildCvUrl } from '@/lib/utils'
import {
  updateProfileAction,
  uploadAvatarAction,
  uploadCvAction,
  deleteCvAction,
  updateSkillsAction,
  type SkillEntry,
} from '@/app/actions/users'
import { useToast } from '@/components/ui/toast-provider'
import type { Profile } from '@/types'

type DbLevel = 'beginner' | 'intermediate' | 'advanced'
type PhpLevel = 'débutant' | 'intermédiaire' | 'avancé' | 'expert'

const PHP_CITIES = [
  'Ouagadougou',
  'Bobo-Dioulasso',
  'Koudougou',
  'Ouahigouya',
  'Banfora',
  'Dédougou',
  'Kaya',
  'Autre',
]

const dbToPhp: Record<DbLevel, PhpLevel> = {
  beginner: 'débutant',
  intermediate: 'intermédiaire',
  advanced: 'avancé',
}

const phpToDb = (v: PhpLevel): DbLevel => {
  switch (v) {
    case 'débutant':
      return 'beginner'
    case 'avancé':
    case 'expert':
      return 'advanced'
    default:
      return 'intermediate'
  }
}

interface Props {
  profile: Profile
  email: string
  allSkills: Array<{ id: number; name: string; category: string | null }>
  initialSkills: Record<number, DbLevel>
}

export function ProfileEditorPhp({ profile, email, allSkills, initialSkills }: Props) {
  const router = useRouter()
  const toast = useToast()
  const [pending, startTransition] = useTransition()

  const [form, setForm] = useState({
    prenom: profile.prenom ?? '',
    nom: profile.nom ?? '',
    email,
    phone: profile.phone ?? '',
    university: profile.university ?? '',
    faculty: profile.faculty ?? '',
    city: profile.city ?? '',
    bio: profile.bio ?? '',
  })

  const [avatarPath, setAvatarPath] = useState(profile.photo_path)
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null)
  const [cvPath, setCvPath] = useState(profile.cv_path)
  const avatarInput = useRef<HTMLInputElement>(null)
  const cvInput = useRef<HTMLInputElement>(null)

  // Skills state: skillId -> { selected, level }
  const initialStateEntries: Record<number, { selected: boolean; level: PhpLevel }> = {}
  for (const s of allSkills) {
    const current = initialSkills[s.id]
    initialStateEntries[s.id] = {
      selected: !!current,
      level: current ? dbToPhp[current] : 'intermédiaire',
    }
  }
  const [skillState, setSkillState] = useState(initialStateEntries)

  const showMsg = (type: 'success' | 'error', msg: string) => {
    if (type === 'success') {
      toast.success(msg)
      setTimeout(() => window.scrollTo({ top: 0, behavior: 'smooth' }), 50)
    } else {
      toast.error(msg)
    }
  }

  const handlePhotoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = ev => setAvatarPreview(String(ev.target?.result ?? ''))
    reader.readAsDataURL(file)

    const fd = new FormData()
    fd.append('avatar', file)
    startTransition(async () => {
      const r = await uploadAvatarAction(fd)
      if (r.ok && r.data) {
        setAvatarPath(r.data.path)
        setAvatarPreview(null)
        showMsg('success', 'Photo mise à jour.')
      } else {
        setAvatarPreview(null)
        showMsg('error', r.message ?? "Erreur lors de l'upload.")
      }
      if (avatarInput.current) avatarInput.current.value = ''
    })
  }

  const handleCvChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    const fd = new FormData()
    fd.append('cv', file)
    startTransition(async () => {
      const r = await uploadCvAction(fd)
      if (r.ok && r.data) {
        setCvPath(r.data.path)
        showMsg('success', 'CV mis à jour.')
      } else {
        showMsg('error', r.message ?? "Erreur lors de l'upload du CV.")
      }
      if (cvInput.current) cvInput.current.value = ''
    })
  }

  const handleCvDelete = () => {
    if (!confirm('Supprimer votre CV ?')) return
    startTransition(async () => {
      const r = await deleteCvAction()
      if (r.ok) {
        setCvPath(null)
        showMsg('success', 'CV supprimé.')
      } else {
        showMsg('error', r.message ?? 'Erreur.')
      }
    })
  }

  const toggleSkillSelect = (id: number) => {
    setSkillState(s => ({
      ...s,
      [id]: { ...s[id], selected: !s[id].selected },
    }))
  }

  const setSkillLevel = (id: number, level: PhpLevel) => {
    setSkillState(s => ({
      ...s,
      [id]: { ...s[id], level },
    }))
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    if (!form.university.trim() || !form.faculty.trim() || !form.city) {
      showMsg('error', 'Veuillez remplir tous les champs obligatoires')
      return
    }

    const fd = new FormData()
    Object.entries(form).forEach(([k, v]) => fd.append(k, v))

    const entries: SkillEntry[] = Object.entries(skillState)
      .filter(([, v]) => v.selected)
      .map(([id, v]) => ({
        id: Number(id),
        level: phpToDb(v.level),
      }))

    startTransition(async () => {
      const r1 = await updateProfileAction(fd)
      if (!r1.ok) {
        showMsg('error', r1.message ?? 'Erreur lors de la mise à jour.')
        return
      }
      const r2 = await updateSkillsAction(entries)
      if (!r2.ok) {
        showMsg('error', r2.message ?? 'Erreur lors de la mise à jour des compétences.')
        return
      }
      showMsg('success', 'Profil mis à jour avec succès.')
      router.refresh()
    })
  }

  const categories = Array.from(
    new Set(allSkills.map(s => s.category ?? 'Autre'))
  )

  const currentAvatarUrl = avatarPreview
    ? avatarPreview
    : avatarPath
      ? buildAvatarUrl(avatarPath)
      : null

  const initials =
    (profile.prenom?.charAt(0) ?? '').toUpperCase() +
    (profile.nom?.charAt(0) ?? '').toUpperCase()

  const bioRemaining = 500 - form.bio.length

  return (
    <section className="upe-saas edit-profile-section">
      <div className="container">
        <div className="page-header-edit">
          <div className="header-content">
            <h1>
              <i className="fas fa-user-edit"></i> Modifier mon Profil
            </h1>
            <p>Mettez à jour vos informations et personnalisez votre profil</p>
          </div>
          <Link href={`/user/${profile.id}`} className="btn-back">
            <i className="fas fa-arrow-left"></i> Retour au profil
          </Link>
        </div>

        <form onSubmit={handleSubmit} className="edit-profile-form">
          <div className="edit-grid">
            <div className="edit-sidebar">
              {/* Photo */}
              <div className="edit-card">
                <div className="card-header-edit">
                  <i className="fas fa-camera"></i>
                  <h3>Photo de Profil</h3>
                </div>
                <div className="card-body-edit">
                  <div className="photo-upload-area">
                    <div className="current-photo">
                      {currentAvatarUrl ? (
                        // eslint-disable-next-line @next/next/no-img-element
                        <img
                          src={currentAvatarUrl}
                          alt="Photo actuelle"
                          id="photoPreview"
                          className="upe-avatar-preview"
                        />
                      ) : (
                        <div className="avatar-placeholder-edit" id="photoPreview">
                          {initials || '?'}
                        </div>
                      )}
                    </div>
                    <input
                      ref={avatarInput}
                      type="file"
                      id="photoInput"
                      name="photo"
                      accept="image/*"
                      className="file-input-hidden"
                      onChange={handlePhotoChange}
                    />
                    <label htmlFor="photoInput" className="btn-upload">
                      <i className="fas fa-upload"></i> Changer la photo
                    </label>
                    <small className="upload-hint">JPG, PNG ou GIF. Max 5MB</small>
                  </div>
                </div>
              </div>

              {/* CV */}
              <div className="edit-card">
                <div className="card-header-edit">
                  <i className="fas fa-file-pdf"></i>
                  <h3>Curriculum Vitae</h3>
                </div>
                <div className="card-body-edit">
                  {cvPath && (
                    <div className="current-cv">
                      <i className="fas fa-file-pdf"></i>
                      <div className="cv-info">
                        <strong>CV actuel</strong>
                        <a href={buildCvUrl(cvPath)} target="_blank" rel="noopener noreferrer">
                          Voir le CV
                        </a>
                      </div>
                      <button
                        type="button"
                        onClick={handleCvDelete}
                        disabled={pending}
                        className="upe-cv-delete"
                        aria-label="Supprimer le CV"
                      >
                        <i className="fas fa-trash"></i>
                      </button>
                    </div>
                  )}
                  <input
                    ref={cvInput}
                    type="file"
                    id="cvInput"
                    name="cv"
                    accept=".pdf"
                    className="file-input-hidden"
                    onChange={handleCvChange}
                  />
                  <label htmlFor="cvInput" className="btn-upload">
                    <i className="fas fa-upload"></i>{' '}
                    {cvPath ? 'Remplacer le CV' : 'Ajouter un CV'}
                  </label>
                  <small className="upload-hint">PDF uniquement. Max 2MB</small>
                </div>
              </div>
            </div>

            <div className="edit-main">
              {/* Informations personnelles */}
              <div className="edit-card">
                <div className="card-header-edit">
                  <i className="fas fa-user"></i>
                  <h3>Informations Personnelles</h3>
                </div>
                <div className="card-body-edit">
                  <div className="form-row-edit">
                    <div className="form-group-edit">
                      <label htmlFor="prenom">Prénom *</label>
                      <input
                        type="text"
                        id="prenom"
                        name="prenom"
                        className="form-input-edit disabled-input"
                        value={form.prenom}
                        readOnly
                      />
                      <small className="form-hint">Non modifiable</small>
                    </div>
                    <div className="form-group-edit">
                      <label htmlFor="nom">Nom *</label>
                      <input
                        type="text"
                        id="nom"
                        name="nom"
                        className="form-input-edit disabled-input"
                        value={form.nom}
                        readOnly
                      />
                      <small className="form-hint">Non modifiable</small>
                    </div>
                  </div>

                  <div className="form-group-edit">
                    <label htmlFor="email">Email *</label>
                    <input
                      type="email"
                      id="email"
                      name="email"
                      className="form-input-edit disabled-input"
                      value={form.email}
                      readOnly
                    />
                    <small className="form-hint">Non modifiable</small>
                  </div>

                  <div className="form-group-edit">
                    <label htmlFor="phone">Téléphone *</label>
                    <input
                      type="tel"
                      id="phone"
                      name="phone"
                      className="form-input-edit disabled-input"
                      value={form.phone}
                      readOnly
                    />
                    <small className="form-hint">Non modifiable</small>
                  </div>
                </div>
              </div>

              {/* Informations académiques */}
              <div className="edit-card">
                <div className="card-header-edit">
                  <i className="fas fa-graduation-cap"></i>
                  <h3>Informations Académiques</h3>
                </div>
                <div className="card-body-edit">
                  <div className="form-group-edit">
                    <label htmlFor="university">Université / École *</label>
                    <input
                      type="text"
                      id="university"
                      name="university"
                      className="form-input-edit"
                      value={form.university}
                      onChange={e => setForm(p => ({ ...p, university: e.target.value }))}
                      required
                    />
                  </div>

                  <div className="form-group-edit">
                    <label htmlFor="faculty">Filière / Spécialité *</label>
                    <input
                      type="text"
                      id="faculty"
                      name="faculty"
                      className="form-input-edit"
                      value={form.faculty}
                      onChange={e => setForm(p => ({ ...p, faculty: e.target.value }))}
                      required
                    />
                  </div>

                  <div className="form-group-edit">
                    <label htmlFor="city">Ville *</label>
                    <select
                      id="city"
                      name="city"
                      className="form-input-edit"
                      value={form.city}
                      onChange={e => setForm(p => ({ ...p, city: e.target.value }))}
                      required
                    >
                      <option value="">Sélectionnez une ville</option>
                      {PHP_CITIES.map(c => (
                        <option key={c} value={c}>
                          {c}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>
              </div>

              {/* Bio */}
              <div className="edit-card">
                <div className="card-header-edit">
                  <i className="fas fa-quote-right"></i>
                  <h3>Biographie</h3>
                </div>
                <div className="card-body-edit">
                  <div className="form-group-edit">
                    <label htmlFor="bio">À propos de vous</label>
                    <textarea
                      id="bio"
                      name="bio"
                      className="form-textarea-edit"
                      rows={6}
                      maxLength={500}
                      placeholder="Parlez de vous, vos passions, vos objectifs..."
                      value={form.bio}
                      onChange={e => setForm(p => ({ ...p, bio: e.target.value }))}
                    />
                    <small
                      className={`form-hint${bioRemaining < 50 ? ' upe-hint-warn' : ''}`}
                      id="bioCounter"
                    >
                      {bioRemaining} caractères restants
                    </small>
                  </div>
                </div>
              </div>

              {/* Compétences */}
              <div className="edit-card">
                <div className="card-header-edit">
                  <i className="fas fa-code"></i>
                  <h3>Mes Compétences</h3>
                </div>
                <div className="card-body-edit">
                  <p className="info-text">
                    <i className="fas fa-info-circle"></i>
                    Sélectionnez vos compétences et indiquez votre niveau pour chacune
                  </p>
                  <div className="skills-selector">
                    {allSkills.length > 0 ? (
                      categories.map(cat => (
                        <div key={cat} className="skill-category-group">
                          <h4 className="category-title">
                            <i className="fas fa-folder"></i> {cat}
                          </h4>
                          <div className="skills-checkboxes">
                            {allSkills
                              .filter(s => (s.category ?? 'Autre') === cat)
                              .map(s => {
                                const state = skillState[s.id]
                                return (
                                  <div key={s.id} className="skill-checkbox-wrapper">
                                    <input
                                      type="checkbox"
                                      id={`skill_${s.id}`}
                                      checked={state?.selected ?? false}
                                      onChange={() => toggleSkillSelect(s.id)}
                                    />
                                    <label htmlFor={`skill_${s.id}`} className="skill-label">
                                      {s.name}
                                    </label>
                                    <select
                                      id={`level_${s.id}`}
                                      className="skill-level-select"
                                      disabled={!state?.selected}
                                      value={state?.level ?? 'intermédiaire'}
                                      onChange={e =>
                                        setSkillLevel(s.id, e.target.value as PhpLevel)
                                      }
                                    >
                                      <option value="débutant">Débutant</option>
                                      <option value="intermédiaire">Intermédiaire</option>
                                      <option value="avancé">Avancé</option>
                                      <option value="expert">Expert</option>
                                    </select>
                                  </div>
                                )
                              })}
                          </div>
                        </div>
                      ))
                    ) : (
                      <p className="text-muted">Aucune compétence disponible</p>
                    )}
                  </div>
                </div>
              </div>

              <div className="form-actions-edit">
                <button type="submit" className="btn-save-changes" disabled={pending}>
                  <i className="fas fa-check-circle"></i>{' '}
                  {pending ? 'Enregistrement...' : 'Enregistrer les modifications'}
                </button>
                <Link href={`/user/${profile.id}`} className="btn-cancel-changes">
                  <i className="fas fa-times"></i> Annuler
                </Link>
              </div>
            </div>
          </div>
        </form>
      </div>
    </section>
  )
}
