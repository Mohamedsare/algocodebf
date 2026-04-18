import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { getForumPost, getComments } from '@/lib/queries/forum'
import { getCurrentProfile } from '@/lib/auth'
import { ForumThreadViewTracker } from '@/components/forum/forum-thread-view-tracker'
import { buildAvatarUrl, buildFileUrl, timeAgo } from '@/lib/utils'
import { ForumLikeButton } from '@/components/forum/forum-like-button'
import { ForumCommentsLive } from '@/components/forum/forum-comments-live'
import { DeletePostButton } from '@/components/forum/delete-post-button'
import { SharePostButton } from '@/components/forum/share-post-button'

interface ForumShowPageProps {
  params: Promise<{ id: string }>
}

export async function generateMetadata({ params }: ForumShowPageProps): Promise<Metadata> {
  const { id } = await params
  const data = await getForumPost(Number(id))
  if (!data) return { title: 'Discussion introuvable' }
  return {
    title: `${data.post.title} — Forum`,
    description: (data.post.body ?? '').replace(/<[^>]*>/g, '').slice(0, 160),
  }
}

function fileIconFor(mime: string | null): { icon: string; color: string } {
  const m = (mime ?? '').toLowerCase()
  if (m.includes('pdf')) return { icon: 'fa-file-pdf', color: '#EF4444' }
  if (m.includes('image')) return { icon: 'fa-file-image', color: '#3B82F6' }
  if (m.includes('word')) return { icon: 'fa-file-word', color: '#2563EB' }
  if (m.includes('zip') || m.includes('archive')) return { icon: 'fa-file-archive', color: '#F59E0B' }
  if (m.includes('text')) return { icon: 'fa-file-alt', color: '#6B7280' }
  return { icon: 'fa-file', color: '#6B7280' }
}

function formatSize(bytes: number | null | undefined): string {
  if (!bytes) return '0 B'
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

export default async function ForumShowPage({ params }: ForumShowPageProps) {
  const { id } = await params
  const numericId = Number(id)
  if (!Number.isFinite(numericId)) notFound()

  const profile = await getCurrentProfile()
  const data = await getForumPost(numericId, profile?.id ?? null)
  if (!data) notFound()

  const comments = await getComments('post', numericId)
  const { post, author, attachments, likes_count, liked_by_user } = data
  const canEdit = profile?.id === post.user_id
  const canDelete = canEdit || profile?.role === 'admin'
  const authorName = author ? `${author.prenom} ${author.nom}` : 'Anonyme'
  const initialsAuthor = (author?.prenom ?? 'U').charAt(0).toUpperCase()

  const commentItems = comments.map((c) => ({
    id: c.id,
    body: c.body,
    created_at: c.created_at,
    user_id: c.user_id ?? null,
    author: c.author,
  }))

  return (
    <div className="forum-thread-saas">
      <ForumThreadViewTracker postId={numericId} />
      <div className="container-xl">
        {/* Breadcrumb */}
        <nav className="breadcrumb-saas" aria-label="Fil d'ariane">
          <Link href="/">
            <i className="fas fa-home" style={{ fontSize: 11 }}></i> Accueil
          </Link>
          <i className="fas fa-chevron-right separator"></i>
          <Link href="/forum">
            <i className="fas fa-comments" style={{ fontSize: 11 }}></i> Forum
          </Link>
          {post.category && (
            <>
              <i className="fas fa-chevron-right separator"></i>
              <Link href={`/forum?category=${encodeURIComponent(post.category)}`}>
                {post.category}
              </Link>
            </>
          )}
        </nav>

        <div className="forum-grid">
          <main>
            <article className="thread-article">
              <header className="thread-header-saas">
                <div className="thread-meta-top">
                  {post.is_pinned && (
                    <span className="chip-meta" style={{ background: 'rgba(255, 209, 0, 0.15)', color: '#92400E', borderColor: 'rgba(255, 209, 0, 0.3)' }}>
                      <i className="fas fa-thumbtack" style={{ fontSize: 10 }}></i>
                      Épinglé
                    </span>
                  )}
                  {post.category && (
                    <span className="chip-meta primary">
                      <i className="fas fa-tag" style={{ fontSize: 10 }}></i>
                      {post.category}
                    </span>
                  )}
                  <span className="chip-meta">
                    <i className="fas fa-clock" style={{ fontSize: 10 }}></i>
                    {timeAgo(post.created_at)}
                  </span>
                  <span className="chip-meta">
                    <i className="fas fa-eye" style={{ fontSize: 10 }}></i>
                    {post.views ?? 0} vue{(post.views ?? 0) > 1 ? 's' : ''}
                  </span>
                </div>

                {(canEdit || canDelete) && (
                  <div className="thread-actions-top">
                    {canEdit && (
                      <Link href={`/forum/${post.id}/modifier`} className="btn-saas ghost sm" title="Modifier">
                        <i className="fas fa-pen"></i>
                      </Link>
                    )}
                    {canDelete && (
                      <DeletePostButton postId={post.id} className="btn-saas danger-ghost sm" />
                    )}
                  </div>
                )}
              </header>

              <h1 className="thread-title-saas">{post.title}</h1>

              {/* Carte auteur */}
              <div className="thread-author-card">
                <div className="author-ava">
                  {author?.photo_path ? (
                    <img src={buildAvatarUrl(author.photo_path)} alt={authorName} />
                  ) : (
                    <span>{initialsAuthor}</span>
                  )}
                </div>
                <div className="author-details">
                  {author ? (
                    <Link href={`/user/${author.id}`} className="author-name">
                      {authorName}
                    </Link>
                  ) : (
                    <span className="author-name">Anonyme</span>
                  )}
                  <div className="author-sub">
                    {author?.university ?? 'Membre de la communauté'}
                    {author?.city ? ` · ${author.city}` : ''}
                  </div>
                </div>
                {profile && author && profile.id !== author.id && (
                  <Link href={`/message/compose/${author.id}`} className="btn-saas sm">
                    <i className="fas fa-envelope" style={{ fontSize: 11 }}></i>
                    <span className="hide-sm">Message</span>
                  </Link>
                )}
              </div>

              {/* Corps */}
              <div className="thread-body-saas">{post.body}</div>

              {/* Pièces jointes */}
              {attachments.length > 0 && (
                <div className="thread-attachments">
                  <h3>
                    <i className="fas fa-paperclip"></i>
                    Pièces jointes ({attachments.length})
                  </h3>
                  <div className="attachments-grid-saas">
                    {attachments.map((att) => {
                      const { icon, color } = fileIconFor(att.mime_type)
                      return (
                        <a
                          key={att.id}
                          href={buildFileUrl(att.file_path)}
                          className="attachment-card-saas"
                          download={att.original_name ?? undefined}
                          target="_blank"
                          rel="noopener noreferrer"
                        >
                          <div className="att-icon" style={{ color }}>
                            <i className={`fas ${icon}`}></i>
                          </div>
                          <div className="att-info">
                            <div className="att-name">{att.original_name}</div>
                            <div className="att-size">{formatSize(att.file_size)}</div>
                          </div>
                          <i className="fas fa-arrow-down" style={{ color: 'var(--f-text-subtle)', fontSize: 12 }}></i>
                        </a>
                      )
                    })}
                  </div>
                </div>
              )}

              {/* Barre réactions */}
              <div className="thread-reactions">
                <ForumLikeButton
                  type="post"
                  id={post.id}
                  initialLiked={liked_by_user}
                  initialCount={likes_count}
                  isAuthenticated={Boolean(profile)}
                  variant="saas"
                />
                <a href="#reponses" className="react-btn">
                  <i className="far fa-comment"></i>
                  <span>{commentItems.length}</span>
                  <span style={{ opacity: 0.7, fontWeight: 500 }}>Répondre</span>
                </a>
                <SharePostButton postId={post.id} title={post.title} />
              </div>
            </article>

            {/* Commentaires realtime */}
            <ForumCommentsLive postId={post.id} initialComments={commentItems} profile={profile} />
          </main>

          <aside className="forum-sidebar-saas">
            {/* Stats de la discussion */}
            <div className="sidebar-card-saas">
              <h3 className="card-title">
                <i className="fas fa-chart-line"></i>
                Statistiques
              </h3>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                <StatRow icon="fa-eye" label="Vues" value={post.views ?? 0} color="#3B82F6" />
                <StatRow icon="fa-heart" label="Likes" value={likes_count} color="#C8102E" />
                <StatRow icon="fa-comments" label="Réponses" value={commentItems.length} color="#006A4E" />
              </div>
            </div>

            {/* Carte auteur détaillée */}
            {author && (
              <div className="sidebar-card-saas">
                <h3 className="card-title">
                  <i className="fas fa-user-circle"></i>
                  Auteur
                </h3>
                <div style={{ textAlign: 'center' }}>
                  <Link href={`/user/${author.id}`}>
                    {author.photo_path ? (
                      <img
                        src={buildAvatarUrl(author.photo_path)}
                        alt={authorName}
                        style={{
                          width: 72,
                          height: 72,
                          borderRadius: '50%',
                          objectFit: 'cover',
                          margin: '0 auto 10px',
                          display: 'block',
                          border: '3px solid var(--f-bg)',
                        }}
                      />
                    ) : (
                      <div
                        style={{
                          width: 72,
                          height: 72,
                          borderRadius: '50%',
                          background: 'linear-gradient(135deg, #FEE2E2, #FECACA)',
                          display: 'grid',
                          placeItems: 'center',
                          margin: '0 auto 10px',
                          color: 'var(--f-primary)',
                          fontWeight: 800,
                          fontSize: 26,
                        }}
                      >
                        {initialsAuthor}
                      </div>
                    )}
                  </Link>
                  <Link
                    href={`/user/${author.id}`}
                    style={{
                      display: 'block',
                      fontSize: 15,
                      fontWeight: 700,
                      color: 'var(--f-text)',
                      textDecoration: 'none',
                      marginBottom: 2,
                    }}
                  >
                    {authorName}
                  </Link>
                  <div style={{ fontSize: 12.5, color: 'var(--f-text-muted)', marginBottom: 12 }}>
                    {author.university ?? 'Membre de la communauté'}
                  </div>
                  {profile && profile.id !== author.id && (
                    <Link
                      href={`/message/compose/${author.id}`}
                      className="btn-saas primary"
                      style={{ width: '100%', justifyContent: 'center' }}
                    >
                      <i className="fas fa-envelope"></i>
                      Envoyer un message
                    </Link>
                  )}
                </div>
              </div>
            )}

            {/* Retour au forum */}
            <Link
              href="/forum"
              className="btn-saas"
              style={{ justifyContent: 'center', width: '100%' }}
            >
              <i className="fas fa-arrow-left"></i>
              Retour au forum
            </Link>
          </aside>
        </div>
      </div>
    </div>
  )
}

function StatRow({
  icon,
  label,
  value,
  color,
}: {
  icon: string
  label: string
  value: number
  color: string
}) {
  return (
    <div
      style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: '10px 12px',
        background: 'var(--f-bg)',
        borderRadius: 10,
      }}
    >
      <span style={{ display: 'inline-flex', alignItems: 'center', gap: 10, fontSize: 13.5, color: 'var(--f-text-muted)', fontWeight: 500 }}>
        <i className={`fas ${icon}`} style={{ color, fontSize: 13 }}></i>
        {label}
      </span>
      <strong style={{ fontSize: 15, color: 'var(--f-text)', fontVariantNumeric: 'tabular-nums' }}>
        {value.toLocaleString('fr-FR')}
      </strong>
    </div>
  )
}
