import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import Link from 'next/link'
import { createClient, getProfile } from '@/lib/supabase/server'
import { CommentsSection } from '@/components/shared/comments-section'
import { TutorialLikeButton } from '@/components/tutorial/tutorial-like-button'
import {
  buildAvatarUrl,
  buildFileUrl,
  formatDate,
  formatNumber,
} from '@/lib/utils'
import { FORMATIONS_PATH } from '@/lib/routes'

interface Props {
  params: Promise<{ id: string }>
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { id } = await params
  const supabase = await createClient()
  const { data } = await supabase
    .from('tutorials')
    .select('title, description')
    .eq('id', parseInt(id))
    .maybeSingle()
  return {
    title: data?.title ?? 'Formation',
    description: data?.description ?? undefined,
  }
}

const TYPE_DISPLAY: Record<string, { icon: string; label: string }> = {
  video: { icon: 'fa-video', label: 'Vidéo' },
  text: { icon: 'fa-align-left', label: 'Texte' },
  pdf: { icon: 'fa-file-pdf', label: 'PDF' },
  code: { icon: 'fa-code', label: 'Code' },
  mixed: { icon: 'fa-layer-group', label: 'Mixte' },
}

function fileIcon(ext: string): { icon: string; color: string } {
  if (ext === 'pdf') return { icon: 'fa-file-pdf', color: '#dc3545' }
  if (['doc', 'docx'].includes(ext)) return { icon: 'fa-file-word', color: '#2b579a' }
  if (['xls', 'xlsx'].includes(ext)) return { icon: 'fa-file-excel', color: '#28a745' }
  if (['ppt', 'pptx'].includes(ext)) return { icon: 'fa-file-powerpoint', color: '#f39c12' }
  if (['zip', 'rar', '7z'].includes(ext)) return { icon: 'fa-file-archive', color: '#f39c12' }
  if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext))
    return { icon: 'fa-file-image', color: 'var(--secondary-color)' }
  if (['mp4', 'avi', 'mov', 'webm'].includes(ext))
    return { icon: 'fa-file-video', color: 'var(--primary-color)' }
  return { icon: 'fa-file-code', color: 'var(--primary-color)' }
}

export default async function TutorialShowPage({ params }: Props) {
  const { id } = await params
  const tutorialId = parseInt(id)
  if (!Number.isFinite(tutorialId)) notFound()

  const [supabase, profile] = await Promise.all([createClient(), getProfile()])

  const [{ data: tuto }, { data: videos }, { data: chapters }, { data: tags }] = await Promise.all([
    supabase
      .from('tutorials')
      .select('*, profiles!inner(id, prenom, nom, photo_path, bio, university)')
      .eq('id', tutorialId)
      .eq('status', 'active')
      .maybeSingle(),
    supabase
      .from('tutorial_videos')
      .select('*')
      .eq('tutorial_id', tutorialId)
      .order('order_index'),
    supabase
      .from('tutorial_chapters')
      .select('*')
      .eq('tutorial_id', tutorialId)
      .order('order_index'),
    supabase
      .from('tutorial_tags')
      .select('tags(name)')
      .eq('tutorial_id', tutorialId),
  ])

  if (!tuto) notFound()

  void supabase
    .from('tutorials')
    .update({ views: (tuto.views ?? 0) + 1 })
    .eq('id', tutorialId)
    .then(() => {})

  let liked = false
  if (profile) {
    const { data: like } = await supabase
      .from('likes')
      .select('id')
      .eq('user_id', profile.id)
      .eq('likeable_type', 'tutorial')
      .eq('likeable_id', tutorialId)
      .maybeSingle()
    liked = !!like
  }

  const author = tuto.profiles as unknown as {
    id: string
    prenom: string
    nom: string
    photo_path: string | null
    bio: string | null
    university: string | null
  }
  const authorName = `${author.prenom} ${author.nom}`
  const initial = authorName.charAt(0).toUpperCase()
  const canEdit = profile && (profile.id === author.id || profile.role === 'admin')

  const tagNames = ((tags ?? []) as unknown as Array<{ tags: { name: string } | null }>)
    .map(t => t.tags?.name)
    .filter((x): x is string => Boolean(x))

  const videoList = videos ?? []
  const chapterList = chapters ?? []

  const mainFileExt = (tuto.file_path ?? '').split('.').pop()?.toLowerCase() ?? ''
  const isVideo = ['mp4', 'webm', 'avi', 'mov', 'wmv', 'mpeg'].includes(mainFileExt)
  const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(mainFileExt)
  const isOtherFile = tuto.file_path && !isVideo && !isImage
  const fi = fileIcon(mainFileExt)

  const typeInfo = TYPE_DISPLAY[tuto.type ?? 'text'] ?? {
    icon: 'fa-book',
    label: (tuto.type as string) ?? 'Contenu',
  }

  return (
    <div className="formation-saas ft-course">
      <section className="tutorial-show-section">
        <div className="container">
          <nav className="breadcrumb-nav ft-breadcrumb" aria-label="Fil d&apos;Ariane">
            <Link href="/">
              <i className="fas fa-home" /> Accueil
            </Link>
            <i className="fas fa-chevron-right" aria-hidden />
            <Link href={FORMATIONS_PATH}>Formations</Link>
            <i className="fas fa-chevron-right" aria-hidden />
            <span>{tuto.title}</span>
          </nav>

          <div className="tutorial-layout">
            <div className="tutorial-main">
              <article className="tutorial-header">
                <div className="tutorial-meta-top">
                  <span className="ft-type-pill">
                    <i className={`fas ${typeInfo.icon}`} aria-hidden />
                    {typeInfo.label}
                  </span>
                  <span className="ft-cat-pill">
                    <i className="fas fa-tag" aria-hidden /> {tuto.category ?? 'Général'}
                  </span>
                </div>

              <h1 className="tutorial-title">{tuto.title}</h1>

              {tuto.description && (
                <p className="tutorial-description">{tuto.description}</p>
              )}

              <div className="author-section">
                <div className="author-info">
                  <div className="author-avatar">
                    {author.photo_path ? (
                      <img src={buildAvatarUrl(author.photo_path)} alt={authorName} />
                    ) : (
                      <div className="avatar-placeholder">{initial}</div>
                    )}
                  </div>
                  <div className="author-details">
                    <p className="author-name">
                      Par <strong>{authorName}</strong>
                    </p>
                    <p className="tutorial-date">
                      <i className="far fa-clock"></i> Publié le{' '}
                      {formatDate(tuto.created_at)}
                    </p>
                  </div>
                </div>

                <div className="tutorial-stats">
                  <div className="stat-item">
                    <i className="fas fa-eye"></i>
                    <span>{formatNumber((tuto.views ?? 0) + 1)}</span>
                  </div>
                  <div className="stat-item">
                    <i className="fas fa-heart"></i>
                    <span>{formatNumber(tuto.likes_count ?? 0)}</span>
                  </div>
                  <div className="stat-item">
                    <i className="fas fa-comment"></i>
                    <span>{formatNumber(tuto.comments_count ?? 0)}</span>
                  </div>
                </div>
              </div>

              <div className="tutorial-actions">
                <TutorialLikeButton
                  tutorialId={tutorialId}
                  initialLikes={tuto.likes_count ?? 0}
                  initialLiked={liked}
                  isAuthenticated={!!profile}
                />
                <a href="#comments" className="btn-action">
                  <i className="far fa-comment"></i>
                  <span>Commenter</span>
                </a>
                {canEdit && (
                  <Link
                    href={`${FORMATIONS_PATH}/${tutorialId}/modifier`}
                    className="btn-action"
                  >
                    <i className="fas fa-edit"></i>
                    <span>Modifier</span>
                  </Link>
                )}
              </div>
            </article>

            {chapterList.length > 0 && (
              <div className="tutorial-chapters-section" id="sommaire-section">
                <h3 className="chapters-title">
                  <i className="fas fa-list-ol"></i> Sommaire de la formation
                </h3>
                <div className="chapters-list">
                  {chapterList.map((ch, idx) => {
                    const targetVideo =
                      (ch as { video_id?: number }).video_id ??
                      videoList[idx]?.id ??
                      videoList[(ch.chapter_number ?? 1) - 1]?.id ??
                      null
                    const item = (
                      <>
                        <div className="chapter-number">{ch.chapter_number}</div>
                        <div className="chapter-content">
                          <h4 className="chapter-title">{ch.title}</h4>
                          {ch.description && (
                            <p className="chapter-description">{ch.description}</p>
                          )}
                        </div>
                        {targetVideo && (
                          <div className="btn-play-chapter">
                            <i className="fas fa-play"></i>
                          </div>
                        )}
                      </>
                    )
                    return targetVideo ? (
                      <a
                        key={ch.id}
                        href={`#video-${targetVideo}`}
                        className="chapter-item chapter-link"
                      >
                        {item}
                      </a>
                    ) : (
                      <div key={ch.id} className="chapter-item">
                        {item}
                      </div>
                    )
                  })}
                </div>
              </div>
            )}

            {videoList.length > 0 && (
              <div className="tutorial-videos-section">
                <h3 className="videos-section-title">
                  <i className="fas fa-video"></i> Vidéos de la formation ({videoList.length})
                </h3>
                <div className="videos-list-container">
                  {videoList.map((v, idx) => (
                    <div
                      key={v.id}
                      id={`video-${v.id}`}
                      className="video-item-card"
                    >
                      <div className="video-header">
                        <div className="video-number">{idx + 1}</div>
                        <div className="video-info">
                          <h4 className="video-title">{v.title}</h4>
                          <div className="video-meta-info">
                            {v.description && (
                              <p className="video-description">{v.description}</p>
                            )}
                            <div className="video-stats">
                              <span>
                                <i className="fas fa-eye"></i>{' '}
                                {formatNumber(v.views ?? 0)} vues
                              </span>
                              {v.file_size && (
                                <span>
                                  <i className="fas fa-file"></i>{' '}
                                  {(v.file_size / 1024 / 1024).toFixed(2)} MB
                                </span>
                              )}
                            </div>
                          </div>
                        </div>
                      </div>
                      <div className="video-player-container">
                        <video
                          controls
                          className="tutorial-video-player"
                          preload="metadata"
                        >
                          <source
                            src={buildFileUrl(v.file_path)}
                            type="video/mp4"
                          />
                          Votre navigateur ne supporte pas la lecture vidéo.
                        </video>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {!videoList.length && tuto.file_path && (
              <div className="tutorial-media">
                {isVideo && (
                  <div className="video-container">
                    <video controls className="tutorial-video">
                      <source
                        src={buildFileUrl(tuto.file_path)}
                        type={`video/${mainFileExt}`}
                      />
                      Votre navigateur ne supporte pas la lecture vidéo.
                    </video>
                  </div>
                )}
                {isImage && (
                  <div className="image-container">
                    <img
                      src={buildFileUrl(tuto.file_path)}
                      alt={tuto.title}
                      className="tutorial-image"
                    />
                  </div>
                )}
                {isOtherFile && (
                  <div className="file-download-box">
                    <div className="file-icon-large">
                      <i className={`fas ${fi.icon}`} style={{ color: fi.color }}></i>
                    </div>
                    <div className="file-info-large">
                      <h3>Fichier de la formation</h3>
                      <p>{tuto.file_path.split('/').pop()}</p>
                    </div>
                    <a
                      href={buildFileUrl(tuto.file_path)}
                      className="btn-download"
                      target="_blank"
                      rel="noreferrer"
                      download
                    >
                      <i className="fas fa-download"></i> Télécharger
                    </a>
                  </div>
                )}
              </div>
            )}

            {tuto.external_link && (
              <div className="external-link-box">
                <div className="external-icon">
                  <i className="fas fa-external-link-alt"></i>
                </div>
                <div className="external-info">
                  <h4>Ressource externe</h4>
                  <p>{tuto.external_link}</p>
                </div>
                <a
                  href={tuto.external_link}
                  className="btn-external"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <i className="fas fa-arrow-right"></i> Ouvrir
                </a>
              </div>
            )}

            {tuto.content && (
              <div className="tutorial-content">
                <div
                  className="content-wrapper"
                  dangerouslySetInnerHTML={{ __html: tuto.content }}
                />
              </div>
            )}

            {tagNames.length > 0 && (
              <div className="tutorial-tags">
                <h3>
                  <i className="fas fa-tags"></i> Tags
                </h3>
                <div className="tags-list">
                  {tagNames.map(tag => (
                    <Link
                      key={tag}
                      href={`${FORMATIONS_PATH}?search=${encodeURIComponent(tag)}`}
                      className="tag-item"
                    >
                      #{tag}
                    </Link>
                  ))}
                </div>
              </div>
            )}

            <div id="comments">
              <CommentsSection
                commentableType="tutorial"
                commentableId={tutorialId}
                profile={profile}
              />
            </div>
          </div>

          <aside className="tutorial-sidebar">
            <div className="ft-enroll-card">
              <div className="ft-enroll-badge">
                <i className="fas fa-graduation-cap" aria-hidden />
                Espace apprenant
              </div>
              <h4>Inscription &amp; suivi</h4>
              <p>
                Nous préparons l&apos;inscription payante, le tableau de bord étudiant et le suivi de progression pour
                que chaque parcours soit vécu comme une formation en ligne professionnelle.
              </p>
              <ul className="ft-enroll-points">
                <li>
                  <i className="fas fa-check" aria-hidden />
                  Paiement sécurisé et accès à tout le contenu
                </li>
                <li>
                  <i className="fas fa-check" aria-hidden />
                  Reprise là où vous vous êtes arrêté
                </li>
                <li>
                  <i className="fas fa-check" aria-hidden />
                  Attestation ou certificat (étape suivante produit)
                </li>
              </ul>
              <button type="button" className="ft-enroll-cta" disabled>
                Bientôt disponible
              </button>
              <p className="ft-enroll-note">
                En attendant, le contenu ci-dessous reste accessible gratuitement pour la communauté.
              </p>
            </div>

            <div className="sidebar-card author-card">
              <h4>À propos de l&apos;auteur</h4>
              <div className="author-full-info">
                {author.photo_path ? (
                  <img
                    src={buildAvatarUrl(author.photo_path)}
                    alt={authorName}
                    className="author-photo-large"
                  />
                ) : (
                  <div className="avatar-placeholder-large">{initial}</div>
                )}
                <h5>{authorName}</h5>
                {author.bio && <p className="author-bio">{author.bio}</p>}
                <Link
                  href={`/user/${author.id}`}
                  className="btn-view-profile"
                >
                  Voir le profil
                </Link>
              </div>
            </div>

            {chapterList.length > 0 && (
              <div className="sidebar-card chapters-sidebar">
                <h4>
                  <i className="fas fa-list-ol"></i> Sommaire
                </h4>
                <div className="sidebar-chapters-list">
                  {chapterList.map((ch, idx) => {
                    const targetVideo =
                      (ch as { video_id?: number }).video_id ??
                      videoList[idx]?.id ??
                      null
                    return targetVideo ? (
                      <a
                        key={ch.id}
                        href={`#video-${targetVideo}`}
                        className="sidebar-chapter-item"
                      >
                        <span className="sidebar-chapter-number">
                          {ch.chapter_number}
                        </span>
                        <span className="sidebar-chapter-title">{ch.title}</span>
                      </a>
                    ) : (
                      <div
                        key={ch.id}
                        className="sidebar-chapter-item sidebar-chapter-no-video"
                      >
                        <span className="sidebar-chapter-number">
                          {ch.chapter_number}
                        </span>
                        <span className="sidebar-chapter-title">{ch.title}</span>
                      </div>
                    )
                  })}
                </div>
              </div>
            )}

            {tuto.file_path && (
              <div className="sidebar-card files-card">
                <h4>
                  <i className="fas fa-download"></i> Fichiers de la formation
                </h4>
                <div className="files-list">
                  <a
                    href={buildFileUrl(tuto.file_path)}
                    className="file-download-item"
                    target="_blank"
                    rel="noreferrer"
                    download
                  >
                    <div
                      className="file-icon-sidebar"
                      style={{ color: fi.color }}
                    >
                      <i className={`fas ${fi.icon}`}></i>
                    </div>
                    <div className="file-info-sidebar">
                      <h5 className="file-name-sidebar">
                        {tuto.file_path.split('/').pop()}
                      </h5>
                      <div className="file-meta-sidebar">
                        <span className="file-type-sidebar">
                          {mainFileExt.toUpperCase()}
                        </span>
                      </div>
                    </div>
                    <div className="file-download-icon">
                      <i className="fas fa-download"></i>
                    </div>
                  </a>
                </div>
              </div>
            )}
          </aside>
        </div>
      </div>
    </section>
    </div>
  )
}
