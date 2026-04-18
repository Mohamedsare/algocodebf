'use client'

import { useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { Badge } from '@/components/ui/badge'
import { ModeratePostRow } from '@/components/admin/moderate-post-row'
import {
  createForumCategoryAction,
  updateForumCategoryAction,
  deleteForumCategoryAction,
} from '@/app/actions/admin'
import type { ForumCategory } from '@/types'
import { useToast } from '@/components/ui/toast-provider'

type PostRow = {
  id: number
  title: string
  category: string | null
  status: 'active' | 'inactive'
  is_pinned: boolean
  views: number
  created_at: string
  profiles: { prenom: string; nom: string } | null
}

interface Props {
  categories: ForumCategory[]
  posts: PostRow[]
}

export function ForumAdminClient({ categories: initialCategories, posts }: Props) {
  const router = useRouter()
  const toast = useToast()
  const [tab, setTab] = useState<'categories' | 'posts'>('categories')
  const [pending, start] = useTransition()
  const [createName, setCreateName] = useState('')
  const [createDesc, setCreateDesc] = useState('')
  const [editingId, setEditingId] = useState<number | null>(null)
  const [editName, setEditName] = useState('')
  const [editDesc, setEditDesc] = useState('')

  function refresh() {
    router.refresh()
  }

  function runCreate(e: React.FormEvent) {
    e.preventDefault()
    start(async () => {
      const r = await createForumCategoryAction(createName, createDesc)
      if (r.ok) {
        setCreateName('')
        setCreateDesc('')
        refresh()
      } else toast.error(r.message ?? 'Erreur')
    })
  }

  function startEdit(c: ForumCategory) {
    setEditingId(c.id)
    setEditName(c.name)
    setEditDesc(c.description ?? '')
  }

  function saveEdit(e: React.FormEvent) {
    e.preventDefault()
    if (editingId == null) return
    start(async () => {
      const r = await updateForumCategoryAction(editingId, editName, editDesc)
      if (r.ok) {
        setEditingId(null)
        refresh()
      } else toast.error(r.message ?? 'Erreur')
    })
  }

  function remove(id: number) {
    if (!window.confirm('Supprimer cette catégorie ?')) return
    start(async () => {
      const r = await deleteForumCategoryAction(id)
      if (r.ok) refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  return (
    <div className="space-y-4">
      <div className="forum-tabs">
        <button
          type="button"
          className={`forum-tab-btn${tab === 'categories' ? ' active' : ''}`}
          onClick={() => setTab('categories')}
        >
          <i className="fas fa-tags"></i> Catégories
        </button>
        <button
          type="button"
          className={`forum-tab-btn${tab === 'posts' ? ' active' : ''}`}
          onClick={() => setTab('posts')}
        >
          <i className="fas fa-comments"></i> Discussions
        </button>
      </div>

      {tab === 'categories' && (
        <div className="forum-tab-content active">
          <div className="section-subheader">
            <h3>
              <i className="fas fa-tags"></i> Gestion des catégories
            </h3>
          </div>

          <form onSubmit={runCreate} className="category-card mb-4" style={{ maxWidth: 520 }}>
            <h4 className="mt-0 text-[var(--dark-color)]">Nouvelle catégorie</h4>
            <div className="flex flex-col gap-2">
              <input
                className="filter-select-admin w-full px-3 py-2 rounded-lg border border-gray-200"
                placeholder="Nom *"
                value={createName}
                onChange={e => setCreateName(e.target.value)}
                disabled={pending}
              />
              <textarea
                className="filter-select-admin w-full px-3 py-2 rounded-lg border border-gray-200 min-h-[72px]"
                placeholder="Description"
                value={createDesc}
                onChange={e => setCreateDesc(e.target.value)}
                disabled={pending}
              />
              <button
                type="submit"
                className="btn-primary-admin w-fit"
                style={{ border: 'none', cursor: 'pointer' }}
                disabled={pending}
              >
                <i className="fas fa-plus"></i> Créer
              </button>
            </div>
          </form>

          <div className="categories-grid">
            {initialCategories.map(c => (
              <div key={c.id} className="category-card">
                {editingId === c.id ? (
                  <form onSubmit={saveEdit}>
                    <div className="category-card-header">
                      <div
                        className="category-icon"
                        style={{ background: 'linear-gradient(135deg, var(--primary-color), var(--secondary-color))' }}
                      >
                        <i className="fas fa-folder"></i>
                      </div>
                      <div className="category-info">
                        <input
                          className="font-semibold w-full border rounded px-2 py-1"
                          value={editName}
                          onChange={e => setEditName(e.target.value)}
                        />
                        <code className="text-xs text-gray-500">{c.slug}</code>
                      </div>
                    </div>
                    <textarea
                      className="category-description w-full border rounded px-2 py-1 text-sm"
                      value={editDesc}
                      onChange={e => setEditDesc(e.target.value)}
                    />
                    <div className="category-stats">
                      <button type="submit" className="btn-view-all text-sm" disabled={pending}>
                        Enregistrer
                      </button>
                      <button
                        type="button"
                        className="text-sm text-gray-600"
                        onClick={() => setEditingId(null)}
                      >
                        Annuler
                      </button>
                    </div>
                  </form>
                ) : (
                  <>
                    <div className="category-card-header">
                      <div
                        className="category-icon"
                        style={{ background: 'linear-gradient(135deg, var(--primary-color), var(--secondary-color))' }}
                      >
                        <i className="fas fa-folder"></i>
                      </div>
                      <div className="category-info">
                        <h4>{c.name}</h4>
                        <p>
                          <code>{c.slug}</code>
                        </p>
                      </div>
                    </div>
                    {c.description && <div className="category-description">{c.description}</div>}
                    <div className="category-stats">
                      <button type="button" className="btn-view-all text-sm" onClick={() => startEdit(c)}>
                        <i className="fas fa-edit"></i> Modifier
                      </button>
                      <button
                        type="button"
                        className="text-sm text-red-600"
                        onClick={() => remove(c.id)}
                        disabled={pending}
                      >
                        <i className="fas fa-trash"></i> Supprimer
                      </button>
                    </div>
                  </>
                )}
              </div>
            ))}
          </div>
        </div>
      )}

      {tab === 'posts' && (
        <div className="forum-tab-content active">
          <div className="section-subheader">
            <h3>
              <i className="fas fa-comments"></i> Discussions
            </h3>
          </div>
          <div className="recent-section p-0 border-0 shadow-none bg-transparent">
            <div className="table-responsive">
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>Sujet</th>
                    <th className="hidden md:table-cell">Auteur</th>
                    <th>Statut</th>
                    <th className="text-right">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {posts.map(p => (
                    <tr key={p.id}>
                      <td>
                        <Link href={`/forum/${p.id}`} className="font-semibold hover:underline">
                          {p.title}
                        </Link>
                        <div className="text-xs text-gray-500">
                          {p.category ?? '—'} · {p.views} vues
                        </div>
                      </td>
                      <td className="hidden md:table-cell text-gray-600">
                        {p.profiles ? `${p.profiles.prenom} ${p.profiles.nom}` : '—'}
                      </td>
                      <td>
                        {p.is_pinned && <Badge variant="accent" className="mr-1">Épinglé</Badge>}
                        {p.status === 'active' ? (
                          <Badge variant="success">Actif</Badge>
                        ) : (
                          <Badge variant="danger">Masqué</Badge>
                        )}
                      </td>
                      <td className="text-right">
                        <ModeratePostRow id={p.id} isPinned={p.is_pinned} status={p.status} />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
