'use client'

import { useEffect } from 'react'
import Link from 'next/link'
import { buildAvatarUrl, buildCvUrl, formatDate } from '@/lib/utils'
import { UserAdminControls } from '@/components/admin/user-admin-controls'
import type { UserRole, UserStatus } from '@/types'

export interface AdminUserDetail {
  id: string
  email: string
  email_verified: boolean
  prenom: string
  nom: string
  phone: string | null
  university: string | null
  faculty: string | null
  city: string | null
  bio: string | null
  photo_path: string | null
  cv_path: string | null
  role: string
  status: string
  last_login: string | null
  created_at: string
  updated_at: string
  points: number
  account_kind?: string | null
  organization_name?: string | null
  job_title?: string | null
  can_create_tutorial?: boolean
  can_create_project?: boolean
}

interface Props {
  user: AdminUserDetail
  onClose: () => void
  currentUserId?: string | null
}

export function AdminUserDetailModal({ user, onClose, currentUserId = null }: Props) {
  const name = `${user.prenom ?? ''} ${user.nom ?? ''}`.trim() || 'Utilisateur'
  const initial = (user.prenom?.charAt(0) ?? 'U').toUpperCase()
  const showAdminTools =
    typeof user.can_create_tutorial === 'boolean' && typeof user.can_create_project === 'boolean'
  const isMe = user.id === currentUserId
  const prot = user.role === 'admin'

  useEffect(() => {
    const prev = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose()
    }
    window.addEventListener('keydown', onKey)
    return () => {
      document.body.style.overflow = prev
      window.removeEventListener('keydown', onKey)
    }
  }, [onClose])

  return (
    <div
      className="user-modal-overlay"
      role="dialog"
      aria-modal="true"
      aria-labelledby="admin-user-modal-title"
      onClick={e => {
        if (e.target === e.currentTarget) onClose()
      }}
    >
      <div className="user-modal-container" style={{ maxWidth: 640 }}>
        <div className="user-modal-header">
          <h3 id="admin-user-modal-title">
            <i className="fas fa-user"></i> Détails de l&apos;utilisateur
          </h3>
          <button type="button" className="btn-close-modal" onClick={onClose} aria-label="Fermer">
            <i className="fas fa-times"></i>
          </button>
        </div>
        <div className="user-modal-body">
          <div className="user-profile-section">
            {user.photo_path ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img
                src={buildAvatarUrl(user.photo_path)}
                alt={name}
                style={{ width: 80, height: 80, borderRadius: '50%', objectFit: 'cover' }}
              />
            ) : (
              <div
                style={{
                  width: 80,
                  height: 80,
                  borderRadius: '50%',
                  background: 'linear-gradient(135deg, #667eea, #764ba2)',
                  color: 'white',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: '2rem',
                  fontWeight: 800,
                }}
              >
                {initial}
              </div>
            )}
            <div className="user-profile-info">
              <h2 style={{ margin: '0 0 4px 0' }}>{name}</h2>
              <p style={{ margin: 0, color: '#6c757d' }}>{user.email || '—'}</p>
              <div className="user-badges" style={{ marginTop: 8 }}>
                <span className={`role-badge role-${user.role}`}>{user.role}</span>
                <span className={`status-badge ${user.status}`}>{statusLabel(user.status)}</span>
              </div>
            </div>
          </div>

          <div className="user-details-grid">
            <div className="detail-item">
              <i className="fas fa-envelope"></i>
              <div>
                <strong>Email vérifié</strong>
                <span>{user.email_verified ? '✅ Oui' : '❌ Non'}</span>
              </div>
            </div>
            <div className="detail-item">
              <i className="fas fa-star"></i>
              <div>
                <strong>Points</strong>
                <span>{user.points}</span>
              </div>
            </div>
            <div className="detail-item">
              <i className="fas fa-university"></i>
              <div>
                <strong>Université</strong>
                <span>{user.university ?? '—'}</span>
              </div>
            </div>
            <div className="detail-item">
              <i className="fas fa-building"></i>
              <div>
                <strong>Faculté</strong>
                <span>{user.faculty ?? '—'}</span>
              </div>
            </div>
            <div className="detail-item">
              <i className="fas fa-map-marker-alt"></i>
              <div>
                <strong>Ville</strong>
                <span>{user.city ?? '—'}</span>
              </div>
            </div>
            <div className="detail-item">
              <i className="fas fa-phone"></i>
              <div>
                <strong>Téléphone</strong>
                <span>{user.phone ?? '—'}</span>
              </div>
            </div>
            {user.account_kind && (
              <div className="detail-item">
                <i className="fas fa-id-badge"></i>
                <div>
                  <strong>Type de compte</strong>
                  <span>{user.account_kind}</span>
                </div>
              </div>
            )}
            {(user.organization_name || user.job_title) && (
              <div className="detail-item">
                <i className="fas fa-briefcase"></i>
                <div>
                  <strong>Organisation / Poste</strong>
                  <span>
                    {[user.organization_name, user.job_title].filter(Boolean).join(' · ') || '—'}
                  </span>
                </div>
              </div>
            )}
            <div className="detail-item">
              <i className="fas fa-calendar-plus"></i>
              <div>
                <strong>Inscription</strong>
                <span>
                  {user.created_at
                    ? new Date(user.created_at).toLocaleDateString('fr-FR', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                      })
                    : '—'}
                </span>
              </div>
            </div>
            <div className="detail-item">
              <i className="fas fa-sign-in-alt"></i>
              <div>
                <strong>Dernière connexion</strong>
                <span>
                  {user.last_login
                    ? new Date(user.last_login).toLocaleString('fr-FR', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                      })
                    : 'Jamais'}
                </span>
              </div>
            </div>
            <div className="detail-item">
              <i className="fas fa-edit"></i>
              <div>
                <strong>Dernière modification</strong>
                <span>{user.updated_at ? formatDate(user.updated_at) : '—'}</span>
              </div>
            </div>
          </div>

          {user.cv_path && (
            <div className="user-documents-section">
              <h4>
                <i className="fas fa-file-alt"></i> Documents
              </h4>
              <div className="documents-grid">
                <a href={buildCvUrl(user.cv_path)} target="_blank" rel="noopener noreferrer" className="document-item">
                  <i className="fas fa-file-pdf"></i>
                  <span>CV</span>
                  <i className="fas fa-external-link-alt"></i>
                </a>
              </div>
            </div>
          )}

          {user.bio && (
            <div className="user-bio-section">
              <h4>
                <i className="fas fa-info-circle"></i> Bio
              </h4>
              <p>{user.bio}</p>
            </div>
          )}

          {showAdminTools && (
            <div className="user-modal-admin-panel">
              <h4>
                <i className="fas fa-user-shield"></i> Administration
              </h4>
              <p className="user-modal-admin-hint">Rôle, statut et permissions de publication</p>
              <UserAdminControls
                userId={user.id}
                role={user.role as UserRole}
                status={user.status as UserStatus}
                canTutorial={user.can_create_tutorial ?? false}
                canProject={user.can_create_project ?? false}
                disabled={isMe || prot}
              />
            </div>
          )}
        </div>
        <div className="user-modal-footer">
          <Link href={`/user/${user.id}`} className="btn-modal-profile" target="_blank" rel="noopener noreferrer">
            <i className="fas fa-external-link-alt"></i> Voir le profil public
          </Link>
          <button type="button" className="btn-modal-close" onClick={onClose}>
            Fermer
          </button>
        </div>
      </div>
    </div>
  )
}

function statusLabel(s: string) {
  switch (s) {
    case 'active':
      return 'Actif'
    case 'inactive':
      return 'Inactif'
    case 'suspended':
      return 'Suspendu'
    default:
      return s
  }
}
