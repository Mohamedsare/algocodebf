'use client'

import { useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { Avatar } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { AdminUserDetailModal, type AdminUserDetail } from '@/components/admin/admin-user-detail-modal'
import { setUserStatusAction } from '@/app/actions/admin'
import type { UserStatus } from '@/types'
import { useToast } from '@/components/ui/toast-provider'

export type AdminConsoleUserRow = AdminUserDetail & {
  can_create_tutorial: boolean
  can_create_project: boolean
}

interface Props {
  users: AdminConsoleUserRow[]
  currentUserId: string | null
  emailColumnAvailable: boolean
}

export function AdminUsersConsole({ users, currentUserId, emailColumnAvailable }: Props) {
  const router = useRouter()
  const toast = useToast()
  const [detail, setDetail] = useState<AdminConsoleUserRow | null>(null)
  const [pending, start] = useTransition()

  function quickStatus(userId: string, status: UserStatus, confirmMsg?: string) {
    if (confirmMsg && !window.confirm(confirmMsg)) return
    start(async () => {
      const r = await setUserStatusAction(userId, status)
      if (r.ok) router.refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  const protectedUser = (u: AdminConsoleUserRow) => u.role === 'admin'

  return (
    <>
      {detail && (
        <AdminUserDetailModal user={detail} currentUserId={currentUserId} onClose={() => setDetail(null)} />
      )}

      <div className="admin-users-table-card">
        <div className="table-responsive">
          <table className="admin-table admin-users-table" id="usersTable">
            <thead>
              <tr>
                <th className="admin-users-col-user" scope="col">
                  Utilisateur
                </th>
                <th className="admin-users-col-email" scope="col">
                  Email
                </th>
                <th className="admin-users-col-uni hidden lg:table-cell" scope="col">
                  Université
                </th>
                <th scope="col">Rôle</th>
                <th scope="col">Statut</th>
                <th className="admin-users-col-actions-date-head" scope="col">
                  <div className="admin-users-date-actions-head">
                    <span>Inscrit</span>
                    <span>Actions</span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              {users.map(u => {
                const name = `${u.prenom} ${u.nom}`.trim() || 'Utilisateur'
                const isMe = u.id === currentUserId
                const prot = protectedUser(u)
                return (
                  <tr key={u.id} data-user-id={u.id}>
                    <td className="admin-users-col-user">
                      <div className="user-cell admin-users-user-cell">
                        <Avatar src={u.photo_path} prenom={u.prenom} nom={u.nom} size="md" />
                        <span className="admin-users-name-wrap">
                          <span className="admin-users-name-text font-semibold">{name}</span>
                          {isMe && (
                            <Badge variant="accent" className="admin-users-badge-me text-[10px] shrink-0">
                              Vous
                            </Badge>
                          )}
                        </span>
                      </div>
                    </td>
                    <td className="admin-users-email-cell admin-users-col-email">
                      {emailColumnAvailable ? (
                        <span className="admin-users-ellipsis">{u.email || '—'}</span>
                      ) : (
                        <span className="admin-users-email-muted">—</span>
                      )}
                    </td>
                    <td className="hidden lg:table-cell admin-users-col-uni">
                      <span className="admin-users-ellipsis">{u.university ?? '—'}</span>
                    </td>
                    <td className="admin-users-col-role">
                      <span className={`role-badge role-${u.role}`}>{u.role}</span>
                    </td>
                    <td className="admin-users-col-status">
                      <span className={`status-badge ${u.status}`}>{statusFr(u.status)}</span>
                    </td>
                    <td className="admin-users-col-actions-date">
                      <div className="admin-users-date-actions-row">
                        <span className="admin-users-row-date">
                          {u.created_at
                            ? new Date(u.created_at).toLocaleDateString('fr-FR')
                            : '—'}
                        </span>
                        <div className="action-buttons admin-users-action-buttons">
                          <button
                            type="button"
                            className="btn-action btn-view"
                            title="Voir détails"
                            onClick={() => setDetail(u)}
                          >
                            <i className="fas fa-eye" aria-hidden />
                          </button>
                          {!prot && u.status === 'active' && (
                            <>
                              <button
                                type="button"
                                className="btn-action btn-warning"
                                title="Suspendre"
                                disabled={pending}
                                onClick={() =>
                                  quickStatus(u.id, 'suspended', `Suspendre ${name} ?`)
                                }
                              >
                                <i className="fas fa-pause" aria-hidden />
                              </button>
                              <button
                                type="button"
                                className="btn-action btn-delete"
                                title="Désactiver le compte (blocage)"
                                disabled={pending}
                                onClick={() =>
                                  quickStatus(u.id, 'inactive', `Désactiver le compte de ${name} ?`)
                                }
                              >
                                <i className="fas fa-ban" aria-hidden />
                              </button>
                              <button
                                type="button"
                                className="btn-action btn-users-trash"
                                title="Suppression définitive (bientôt)"
                                disabled={pending}
                                onClick={() =>
                                  toast.info(
                                    'La suppression définitive du compte n’est pas encore disponible. Utilisez le bouton interdit pour désactiver le compte.'
                                  )
                                }
                              >
                                <i className="fas fa-trash-alt" aria-hidden />
                              </button>
                            </>
                          )}
                          {!prot && u.status === 'suspended' && (
                            <>
                              <button
                                type="button"
                                className="btn-action btn-success"
                                title="Réactiver"
                                disabled={pending}
                                onClick={() => quickStatus(u.id, 'active')}
                              >
                                <i className="fas fa-undo" aria-hidden />
                              </button>
                              <button
                                type="button"
                                className="btn-action btn-delete"
                                title="Désactiver le compte"
                                disabled={pending}
                                onClick={() =>
                                  quickStatus(u.id, 'inactive', `Désactiver le compte de ${name} ?`)
                                }
                              >
                                <i className="fas fa-ban" aria-hidden />
                              </button>
                            </>
                          )}
                          {!prot && u.status === 'inactive' && (
                            <button
                              type="button"
                              className="btn-action btn-success"
                              title="Réactiver"
                              disabled={pending}
                              onClick={() => quickStatus(u.id, 'active')}
                            >
                              <i className="fas fa-check" aria-hidden />
                            </button>
                          )}
                          {prot && (
                            <button
                              type="button"
                              className="btn-action btn-users-protected"
                              disabled
                              title="Compte admin protégé"
                            >
                              <i className="fas fa-shield-alt" aria-hidden />
                            </button>
                          )}
                        </div>
                      </div>
                    </td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        </div>
      </div>
    </>
  )
}

function statusFr(s: string) {
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
