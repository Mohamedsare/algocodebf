'use client'

import { useMemo, useState, useTransition } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { Badge } from '@/components/ui/badge'
import { Button, buttonVariants } from '@/components/ui/button'
import { cn, formatDateShort, formatNumber } from '@/lib/utils'
import { Edit, ExternalLink, Eye, EyeOff, Plus, Send } from 'lucide-react'
import {
  createBlogCategoryAction,
  updateBlogCategoryAction,
  deleteBlogCategoryAction,
  setBlogPostStatusAction,
} from '@/app/actions/admin'
import type { BlogCategory } from '@/types'
import { useToast } from '@/components/ui/toast-provider'

export type BlogPostAdminRow = {
  id: number
  title: string
  slug: string
  category: string | null
  status: 'draft' | 'published' | 'archived'
  views: number
  created_at: string
  profiles: { prenom: string; nom: string } | null
}

interface Props {
  categories: BlogCategory[]
  posts: BlogPostAdminRow[]
}

export function BlogAdminClient({ categories: initialCategories, posts }: Props) {
  const router = useRouter()
  const toast = useToast()
  const [tab, setTab] = useState<'posts' | 'categories'>('posts')
  const [pending, start] = useTransition()
  const [postPending, startPost] = useTransition()
  const [createName, setCreateName] = useState('')
  const [createDesc, setCreateDesc] = useState('')
  const [editingId, setEditingId] = useState<number | null>(null)
  const [editName, setEditName] = useState('')
  const [editDesc, setEditDesc] = useState('')
  const [q, setQ] = useState('')
  const [statusF, setStatusF] = useState<'all' | BlogPostAdminRow['status']>('all')
  const [categoryF, setCategoryF] = useState<string>('all')

  function refresh() {
    router.refresh()
  }

  function runCreate(e: React.FormEvent) {
    e.preventDefault()
    start(async () => {
      const r = await createBlogCategoryAction(createName, createDesc)
      if (r.ok) {
        setCreateName('')
        setCreateDesc('')
        refresh()
      } else toast.error(r.message ?? 'Erreur')
    })
  }

  function startEdit(c: BlogCategory) {
    setEditingId(c.id)
    setEditName(c.name)
    setEditDesc(c.description ?? '')
  }

  function saveEdit(e: React.FormEvent) {
    e.preventDefault()
    if (editingId == null) return
    start(async () => {
      const r = await updateBlogCategoryAction(editingId, editName, editDesc)
      if (r.ok) {
        setEditingId(null)
        refresh()
      } else toast.error(r.message ?? 'Erreur')
    })
  }

  function remove(id: number) {
    if (!window.confirm('Supprimer cette catégorie ?')) return
    start(async () => {
      const r = await deleteBlogCategoryAction(id)
      if (r.ok) refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  const categoryNames = useMemo(() => {
    const s = new Set<string>()
    for (const p of posts) {
      if (p.category?.trim()) s.add(p.category.trim())
    }
    return [...s].sort((a, b) => a.localeCompare(b, 'fr'))
  }, [posts])

  const stats = useMemo(() => {
    const published = posts.filter(p => p.status === 'published').length
    const draft = posts.filter(p => p.status === 'draft').length
    const archived = posts.filter(p => p.status === 'archived').length
    const views = posts.reduce((a, p) => a + (p.views ?? 0), 0)
    const offline = draft + archived
    return { total: posts.length, published, draft, archived, views, offline }
  }, [posts])

  const filteredPosts = useMemo(() => {
    const needle = q.trim().toLowerCase()
    return posts.filter(p => {
      if (statusF !== 'all' && p.status !== statusF) return false
      if (categoryF !== 'all' && (p.category ?? '').trim() !== categoryF) return false
      if (!needle) return true
      const title = p.title.toLowerCase()
      const author = p.profiles ? `${p.profiles.prenom} ${p.profiles.nom}`.toLowerCase() : ''
      return title.includes(needle) || author.includes(needle)
    })
  }, [posts, q, statusF, categoryF])

  function runPostStatus(id: number, status: BlogPostAdminRow['status']) {
    startPost(async () => {
      const r = await setBlogPostStatusAction(id, status)
      if (r.ok) refresh()
      else toast.error(r.message ?? 'Erreur')
    })
  }

  return (
    <div className="space-y-4">
      <div className="forum-tabs">
        <button
          type="button"
          className={`forum-tab-btn${tab === 'posts' ? ' active' : ''}`}
          onClick={() => setTab('posts')}
        >
          <i className="fas fa-newspaper"></i> Articles
        </button>
        <button
          type="button"
          className={`forum-tab-btn${tab === 'categories' ? ' active' : ''}`}
          onClick={() => setTab('categories')}
        >
          <i className="fas fa-tags"></i> Catégories
        </button>
      </div>

      {tab === 'posts' && (
        <div className="space-y-4">
          <div className="stats-grid-admin">
            <div className="stat-card-admin card-tutorials">
              <div className="stat-icon-admin">
                <i className="fas fa-newspaper" aria-hidden />
              </div>
              <div className="stat-data">
                <h3>{formatNumber(stats.total)}</h3>
                <p>Articles</p>
                <span className="stat-trend positive">
                  <i className="fas fa-list" aria-hidden /> Liste admin
                </span>
              </div>
            </div>
            <div className="stat-card-admin card-posts">
              <div className="stat-icon-admin">
                <i className="fas fa-check" aria-hidden />
              </div>
              <div className="stat-data">
                <h3>{formatNumber(stats.published)}</h3>
                <p>Publiés</p>
                <span className="stat-trend positive">
                  <i className="fas fa-globe" aria-hidden /> Visibles sur /blog
                </span>
              </div>
            </div>
            <div className="stat-card-admin card-reports">
              <div className="stat-icon-admin">
                <i className="fas fa-file-pen" aria-hidden />
              </div>
              <div className="stat-data">
                <h3>{formatNumber(stats.offline)}</h3>
                <p>Hors ligne</p>
                <span className="stat-trend" style={{ color: 'var(--dark-color, #334155)' }}>
                  <i className="fas fa-edit" aria-hidden /> Brouillons + archivés
                </span>
              </div>
            </div>
            <div className="stat-card-admin card-users">
              <div className="stat-icon-admin">
                <i className="fas fa-chart-line" aria-hidden />
              </div>
              <div className="stat-data">
                <h3>{formatNumber(stats.views)}</h3>
                <p>Vues cumulées</p>
                <span className="stat-trend positive">
                  <i className="fas fa-eye" aria-hidden /> Sur l’échantillon
                </span>
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div className="header-actions admin-users-filters-form flex-wrap">
              <div className="search-box-admin min-w-[200px] flex-1">
                <i className="fas fa-search" aria-hidden />
                <input
                  type="search"
                  className="filter-select-admin border-0 bg-transparent shadow-none flex-1 min-w-0"
                  placeholder="Rechercher par titre ou auteur…"
                  value={q}
                  onChange={e => setQ(e.target.value)}
                  aria-label="Rechercher un article"
                />
              </div>
              <select
                className="filter-select-admin"
                value={statusF}
                onChange={e => setStatusF(e.target.value as typeof statusF)}
                aria-label="Statut"
              >
                <option value="all">Tous les statuts</option>
                <option value="published">Publié</option>
                <option value="draft">Brouillon</option>
                <option value="archived">Archivé</option>
              </select>
              <select
                className="filter-select-admin"
                value={categoryF}
                onChange={e => setCategoryF(e.target.value)}
                aria-label="Catégorie"
              >
                <option value="all">Toutes les catégories</option>
                {categoryNames.map(c => (
                  <option key={c} value={c}>
                    {c}
                  </option>
                ))}
              </select>
            </div>
            <Link
              href="/blog/creer"
              className={cn(
                buttonVariants({ variant: 'primary', size: 'md' }),
                'admin-primary-cta shrink-0 w-fit max-w-full whitespace-nowrap px-6'
              )}
            >
              <Plus size={14} aria-hidden /> Nouvel article
            </Link>
          </div>

          <p className="text-sm text-gray-600 m-0">
            {filteredPosts.length === posts.length
              ? `${posts.length} article${posts.length === 1 ? '' : 's'}.`
              : `${filteredPosts.length} sur ${posts.length} article${posts.length === 1 ? '' : 's'} (filtres actifs).`}
          </p>

          <div className="recent-section p-0 shadow-none bg-transparent">
            <div className="table-responsive">
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>Titre</th>
                    <th className="hidden md:table-cell">Catégorie</th>
                    <th className="hidden lg:table-cell">Auteur</th>
                    <th className="hidden md:table-cell">Création</th>
                    <th>Statut</th>
                    <th className="text-right">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredPosts.length === 0 ? (
                    <tr>
                      <td colSpan={6} className="text-center text-gray-500 py-8">
                        Aucun article ne correspond aux filtres.
                      </td>
                    </tr>
                  ) : (
                    filteredPosts.map(p => (
                      <tr key={p.id}>
                        <td>
                          <Link
                            href={`/blog/${p.slug}`}
                            className="font-semibold hover:underline text-[var(--dark-color,#0f172a)]"
                          >
                            {p.title}
                          </Link>
                          <div className="text-xs text-gray-500">{formatNumber(p.views ?? 0)} vues</div>
                        </td>
                        <td className="hidden md:table-cell">{p.category ?? '—'}</td>
                        <td className="hidden lg:table-cell text-gray-600">
                          {p.profiles ? `${p.profiles.prenom} ${p.profiles.nom}` : '—'}
                        </td>
                        <td className="hidden md:table-cell text-gray-600 whitespace-nowrap text-sm">
                          {formatDateShort(p.created_at)}
                        </td>
                        <td>
                          {p.status === 'published' && <Badge variant="success">Publié</Badge>}
                          {p.status === 'draft' && <Badge variant="warning">Brouillon</Badge>}
                          {p.status === 'archived' && <Badge variant="outline">Archivé</Badge>}
                        </td>
                        <td className="text-right">
                          <div className="flex justify-end flex-wrap gap-1">
                            <Link href={`/blog/${p.slug}`} title="Voir sur le site">
                              <Button variant="ghost" size="sm" disabled={p.status !== 'published'}>
                                <ExternalLink size={14} />
                              </Button>
                            </Link>
                            <Link href={`/blog/${p.slug}/modifier`} title="Modifier">
                              <Button variant="ghost" size="sm">
                                <Edit size={14} />
                              </Button>
                            </Link>
                            {p.status === 'draft' && (
                              <Button
                                variant="secondary"
                                size="sm"
                                loading={postPending}
                                title="Publier"
                                onClick={() => runPostStatus(p.id, 'published')}
                              >
                                <Send size={14} />
                              </Button>
                            )}
                            {p.status !== 'archived' ? (
                              <Button
                                variant="danger"
                                size="sm"
                                loading={postPending}
                                title="Archiver"
                                onClick={() => {
                                  if (!window.confirm('Archiver cet article ? Il disparaîtra du blog public.')) return
                                  runPostStatus(p.id, 'archived')
                                }}
                              >
                                <EyeOff size={14} />
                              </Button>
                            ) : (
                              <Button
                                variant="secondary"
                                size="sm"
                                loading={postPending}
                                title="Republier"
                                onClick={() => runPostStatus(p.id, 'published')}
                              >
                                <Eye size={14} />
                              </Button>
                            )}
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {tab === 'categories' && (
        <div>
          <div className="section-subheader">
            <h3>
              <i className="fas fa-tags"></i> Catégories du blog
            </h3>
          </div>

          <form onSubmit={runCreate} className="category-card mb-4" style={{ maxWidth: 520 }}>
            <h4 className="mt-0">Nouvelle catégorie</h4>
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
                        style={{ background: 'linear-gradient(135deg, #e74c3c, #c0392b)' }}
                      >
                        <i className="fas fa-tag"></i>
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
                      <button type="button" className="text-sm text-gray-600" onClick={() => setEditingId(null)}>
                        Annuler
                      </button>
                    </div>
                  </form>
                ) : (
                  <>
                    <div className="category-card-header">
                      <div
                        className="category-icon"
                        style={{ background: 'linear-gradient(135deg, #e74c3c, #c0392b)' }}
                      >
                        <i className="fas fa-tag"></i>
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
    </div>
  )
}
