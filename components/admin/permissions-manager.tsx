'use client'

import { useMemo, useState, useTransition } from 'react'
import Link from 'next/link'
import { setUserPermissionAction } from '@/app/actions/admin'
import { useToast } from '@/components/ui/toast-provider'

type Status = 'active' | 'pending' | 'suspended' | 'banned'

interface UserRow {
  id: string
  full_name: string
  status: Status
  can_create_tutorial: boolean
  can_create_project: boolean
}

interface Props {
  initialUsers: UserRow[]
}

const STATUS_LABEL: Record<Status, string> = {
  active: 'Actif',
  pending: 'En attente',
  suspended: 'Suspendu',
  banned: 'Banni',
}

export function PermissionsManager({ initialUsers }: Props) {
  const toast = useToast()
  const [users, setUsers] = useState<UserRow[]>(initialUsers)
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState<'' | Status>('')
  const [permFilter, setPermFilter] = useState<'' | 'both' | 'tutorial' | 'project' | 'none'>('')
  const [, startTransition] = useTransition()

  const showMsg = (type: 'success' | 'error', msg: string) => {
    if (type === 'success') toast.success(msg)
    else toast.error(msg)
  }

  const togglePermission = (
    userId: string,
    permission: 'can_create_tutorial' | 'can_create_project',
    next: boolean
  ) => {
    setUsers(prev =>
      prev.map(u => (u.id === userId ? { ...u, [permission]: next } : u))
    )
    startTransition(async () => {
      const res = await setUserPermissionAction(userId, permission, next)
      if (!res.ok) {
        setUsers(prev =>
          prev.map(u => (u.id === userId ? { ...u, [permission]: !next } : u))
        )
        showMsg('error', res.message ?? 'Erreur lors de la mise à jour.')
      } else {
        showMsg('success', 'Permission mise à jour.')
      }
    })
  }

  const setBoth = (userId: string, value: boolean) => {
    setUsers(prev =>
      prev.map(u =>
        u.id === userId
          ? { ...u, can_create_tutorial: value, can_create_project: value }
          : u
      )
    )
    startTransition(async () => {
      const r1 = await setUserPermissionAction(userId, 'can_create_tutorial', value)
      const r2 = await setUserPermissionAction(userId, 'can_create_project', value)
      if (!r1.ok || !r2.ok) {
        showMsg('error', "Erreur lors de la mise à jour.")
      } else {
        showMsg('success', value ? 'Toutes les permissions accordées.' : 'Toutes les permissions retirées.')
      }
    })
  }

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return users.filter(u => {
      if (q && !u.full_name.toLowerCase().includes(q)) return false
      if (statusFilter && u.status !== statusFilter) return false
      if (permFilter === 'both' && !(u.can_create_tutorial && u.can_create_project)) return false
      if (permFilter === 'tutorial' && !(u.can_create_tutorial && !u.can_create_project)) return false
      if (permFilter === 'project' && !(!u.can_create_tutorial && u.can_create_project)) return false
      if (permFilter === 'none' && (u.can_create_tutorial || u.can_create_project)) return false
      return true
    })
  }, [users, search, statusFilter, permFilter])

  const totalTutorial = users.filter(u => u.can_create_tutorial).length
  const totalProject = users.filter(u => u.can_create_project).length

  return (
    <>
      <div className="stats-grid-admin">
        <div className="stat-card-admin card-users">
          <div className="stat-icon-admin">
            <i className="fas fa-users" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{users.length}</h3>
            <p>Comptes (hors admin)</p>
            <span className="stat-trend positive">
              <i className="fas fa-user-check" aria-hidden /> Liste chargée
            </span>
          </div>
        </div>
        <div className="stat-card-admin card-tutorials">
          <div className="stat-icon-admin">
            <i className="fas fa-book-open" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{totalTutorial}</h3>
            <p>Création formations</p>
            <span className="stat-trend positive">Permission accordée</span>
          </div>
        </div>
        <div className="stat-card-admin card-posts">
          <div className="stat-icon-admin">
            <i className="fas fa-project-diagram" aria-hidden />
          </div>
          <div className="stat-data">
            <h3>{totalProject}</h3>
            <p>Création projets</p>
            <span className="stat-trend positive">Permission accordée</span>
          </div>
        </div>
      </div>

      <div className="header-actions admin-users-filters-form flex-wrap mb-2">
        <div className="search-box-admin min-w-[200px] flex-1">
          <i className="fas fa-search" aria-hidden />
          <input
            type="search"
            className="filter-select-admin border-0 bg-transparent shadow-none flex-1 min-w-0"
            placeholder="Rechercher un utilisateur…"
            value={search}
            onChange={e => setSearch(e.target.value)}
            aria-label="Rechercher"
          />
        </div>
        <select
          className="filter-select-admin"
          value={statusFilter}
          onChange={e => setStatusFilter(e.target.value as '' | Status)}
          aria-label="Filtrer par statut"
        >
          <option value="">Tous les statuts</option>
          <option value="active">Actifs</option>
          <option value="pending">En attente</option>
          <option value="suspended">Suspendus</option>
          <option value="banned">Bannis</option>
        </select>
        <select
          className="filter-select-admin"
          value={permFilter}
          onChange={e =>
            setPermFilter(e.target.value as '' | 'both' | 'tutorial' | 'project' | 'none')
          }
          aria-label="Filtrer par permissions"
        >
          <option value="">Toutes les permissions</option>
          <option value="both">Toutes accordées</option>
          <option value="tutorial">Formations uniquement</option>
          <option value="project">Projets uniquement</option>
          <option value="none">Aucune</option>
        </select>
      </div>

      <p className="text-sm text-gray-600 m-0 mb-2">
        {filtered.length === users.length
          ? `${users.length} utilisateur${users.length === 1 ? '' : 's'}.`
          : `${filtered.length} sur ${users.length} (filtres actifs).`}
      </p>

      <div className="permissions-table-wrapper recent-section p-0 shadow-none bg-transparent">
        <table className="permissions-table">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Statut</th>
              <th>
                <i className="fas fa-book"></i> Formations
              </th>
              <th>
                <i className="fas fa-project-diagram"></i> Projets
              </th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {filtered.length === 0 ? (
              <tr>
                <td colSpan={5} className="text-center">
                  Aucun utilisateur trouvé
                </td>
              </tr>
            ) : (
              filtered.map(u => (
                <tr key={u.id}>
                  <td>
                    <div className="user-info">
                      <i className="fas fa-user-circle user-icon"></i>
                      <Link href={`/user/${u.id}`} className="user-name hover:underline text-[#C8102E]">
                        {u.full_name}
                      </Link>
                    </div>
                  </td>
                  <td>
                    <span className={`status-badge status-${u.status}`}>
                      {STATUS_LABEL[u.status]}
                    </span>
                  </td>
                  <td>
                    <label className="toggle-switch">
                      <input
                        type="checkbox"
                        checked={u.can_create_tutorial}
                        onChange={e =>
                          togglePermission(u.id, 'can_create_tutorial', e.target.checked)
                        }
                      />
                      <span className="slider"></span>
                    </label>
                  </td>
                  <td>
                    <label className="toggle-switch">
                      <input
                        type="checkbox"
                        checked={u.can_create_project}
                        onChange={e =>
                          togglePermission(u.id, 'can_create_project', e.target.checked)
                        }
                      />
                      <span className="slider"></span>
                    </label>
                  </td>
                  <td>
                    <div className="action-buttons">
                      <button
                        type="button"
                        className="btn-small btn-success"
                        onClick={() => setBoth(u.id, true)}
                        title="Tout accorder"
                      >
                        <i className="fas fa-check-double"></i>
                      </button>
                      <button
                        type="button"
                        className="btn-small btn-danger"
                        onClick={() => setBoth(u.id, false)}
                        title="Tout retirer"
                      >
                        <i className="fas fa-times"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      <div className="permissions-legend">
        <h3>
          <i className="fas fa-info-circle"></i> Légende
        </h3>
        <ul>
          <li>
            <strong>Formations :</strong> Permet à l&apos;utilisateur de publier des parcours sur le catalogue
          </li>
          <li>
            <strong>Projets :</strong> Permet à l&apos;utilisateur de créer et gérer des projets collaboratifs
          </li>
          <li>
            <strong>Actions :</strong> Boutons pour accorder ou retirer toutes les permissions en un clic
          </li>
        </ul>
        <p className="note">
          <i className="fas fa-shield-alt"></i> Les administrateurs ont automatiquement toutes les
          permissions.
        </p>
      </div>
    </>
  )
}
