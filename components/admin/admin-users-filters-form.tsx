'use client'

import { AdminUsersSearchInput } from '@/components/admin/admin-users-search-input'

interface Props {
  initialQ: string
  role: string
  status: string
}

export function AdminUsersFiltersForm({ initialQ, role, status }: Props) {
  return (
    <form method="GET" className="header-actions admin-users-filters-form">
      <div className="search-box-admin">
        <i className="fas fa-search" aria-hidden />
        <AdminUsersSearchInput initialQ={initialQ} />
      </div>
      <select
        name="role"
        defaultValue={role}
        className="filter-select-admin"
        aria-label="Filtrer par rôle"
        onChange={e => e.currentTarget.form?.requestSubmit()}
      >
        <option value="">Tous les rôles</option>
        <option value="user">Utilisateur</option>
        <option value="company">Entreprise</option>
        <option value="admin">Admin</option>
      </select>
      <select
        name="status"
        defaultValue={status}
        className="filter-select-admin"
        aria-label="Filtrer par statut"
        onChange={e => e.currentTarget.form?.requestSubmit()}
      >
        <option value="">Tous les statuts</option>
        <option value="active">Actif</option>
        <option value="inactive">Inactif</option>
        <option value="suspended">Suspendu</option>
      </select>
    </form>
  )
}
