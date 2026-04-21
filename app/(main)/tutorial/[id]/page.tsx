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
  levelLabel,
} from '@/lib/utils'
import { FORMATIONS_PATH } from '@/lib/routes'
import { FormationLearnShell } from '@/components/tutorial/formation-learn-shell'
import { markdownToHtml } from '@/lib/markdown'
import { splitTutorialHtmlIntoSections } from '@/lib/tutorial-content-sections'
import { FormationContentPager } from '@/components/tutorial/formation-content-pager'
import { FormationLessonVideos } from '@/components/tutorial/formation-lesson-videos'
import {
  formationReadingHeading,
  formationReadingIntro,
  formationTypeBadge,
  formationVideoBlockHint,
  formationVideoBlockTitle,
  normalizeFormationFormat,
  readingSectionBeforeVideos,
} from '@/lib/formation-course'

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
      .select('*, profiles(id, prenom, nom, photo_path, bio, university)')
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

  type AuthorRow = {
    id: string
    prenom: string
    nom: string
    photo_path: string | null
    bio: string | null
    university: string | null
  }
  const authorRow = tuto.profiles as unknown as AuthorRow | null
  const author: AuthorRow = authorRow ?? {
    id: (tuto.user_id as string) ?? '',
    prenom: 'Auteur',
    nom: '',
    photo_path: null,
    bio: null,
    university: null,
  }
  const authorName = `${author.prenom} ${author.nom}`.trim() || 'Auteur'
  const initial = authorName.charAt(0).toUpperCase()
  const canEdit =
    profile &&
    tuto.user_id &&
    (profile.id === tuto.user_id || profile.role === 'admin')
  const authorProfileId = authorRow?.id ?? tuto.user_id ?? ''

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

  const formationType = normalizeFormationFormat(tuto.type)
  const typeInfo = formationTypeBadge(formationType)
  const textFirst = readingSectionBeforeVideos(formationType)

  const lectureSections = tuto.content
    ? splitTutorialHtmlIntoSections(markdownToHtml(tuto.content))
    : []
  const showLecture = Boolean(tuto.content && lectureSections.length > 0)
  const showVideos = videoList.length > 0
  const hasAttachments = Boolean(
    (!videoList.length && tuto.file_path) || tuto.external_link
  )
  const readingIntro = formationReadingIntro(formationType)

  const chapterHref = (targetVideo: number | null) => {
    if (targetVideo) return `#video-${targetVideo}`
    if (showLecture) return '#ft-lecture'
    return null
  }

  const heroCover = tuto.thumbnail ? buildFileUrl(tuto.thumbnail) : null
  const levelKey = (tuto.level ?? 'beginner') as string

  return (
    <FormationLearnShell>
      <div className="formation-saas ft-course ft-course-pro">
        <header
          className={`ft-course-hero${heroCover ? ' ft-course-hero--cover' : ''}`}
          style={
            heroCover
              ? {
                  backgroundImage: `linear-gradient(105deg, rgba(10, 11, 15, 0.93) 0%, rgba(10, 11, 15, 0.78) 42%, rgba(0, 106, 78, 0.5) 100%), url(${heroCover})`,
                }
              : undefined
          }
        >
          <div className="ft-course-hero-shade" aria-hidden />
          <div className="container ft-course-hero-inner">
            <nav className="ft-course-breadcrumb" aria-label="Fil d&apos;Ariane">
              <Link href="/">
                <i className="fas fa-home" aria-hidden /> Accueil
              </Link>
              <i className="fas fa-chevron-right" aria-hidden />
              <Link href={FORMATIONS_PATH}>Formations</Link>
              <i className="fas fa-chevron-right" aria-hidden />
              <span className="ft-course-breadcrumb-current">{tuto.title}</span>
            </nav>

            <div className="ft-course-hero-meta">
              <span className="ft-hero-pill ft-hero-pill--type">
                <i className={`fas ${typeInfo.icon}`} aria-hidden />
                {typeInfo.label}
              </span>
              <span className="ft-hero-pill ft-hero-pill--cat">
                <i className="fas fa-folder-open" aria-hidden />
                {tuto.category ?? 'Général'}
              </span>
              <span className="ft-hero-pill ft-hero-pill--level">
                <i className="fas fa-signal" aria-hidden />
                {levelLabel(levelKey)}
              </span>
            </div>

            <h1 className="ft-course-hero-title">{tuto.title}</h1>

            {tuto.description && (
              <p className="ft-course-hero-lead">{tuto.description}</p>
            )}

            <nav className="ft-course-jump" aria-label="Raccourcis dans la formation">
              {chapterList.length > 0 && (
                <a className="ft-jump-link" href="#sommaire-section">
                  <i className="fas fa-list-ol" aria-hidden /> Sommaire
                </a>
              )}
              {textFirst ? (
                <>
                  {showLecture && (
                    <a className="ft-jump-link" href="#ft-lecture">
                      <i className="fas fa-book-open" aria-hidden /> Lecture
                    </a>
                  )}
                  {showVideos && (
                    <a className="ft-jump-link" href="#ft-videos">
                      <i className="fas fa-play-circle" aria-hidden /> Vidéos
                    </a>
                  )}
                </>
              ) : (
                <>
                  {showVideos && (
                    <a className="ft-jump-link" href="#ft-videos">
                      <i className="fas fa-play-circle" aria-hidden /> Vidéos
                    </a>
                  )}
                  {showLecture && (
                    <a className="ft-jump-link" href="#ft-lecture">
                      <i className="fas fa-book-open" aria-hidden /> Lecture
                    </a>
                  )}
                </>
              )}
              {hasAttachments && (
                <a className="ft-jump-link" href="#ft-ressources">
                  <i className="fas fa-paperclip" aria-hidden /> Ressources
                </a>
              )}
              <a className="ft-jump-link" href="#comments">
                <i className="fas fa-comments" aria-hidden /> Discussions
              </a>
            </nav>

            <div className="ft-course-hero-footer">
              <div className="ft-hero-author">
                <div className="ft-hero-avatar">
                  {author.photo_path ? (
                    <img src={buildAvatarUrl(author.photo_path)} alt="" />
                  ) : (
                    <span aria-hidden>{initial}</span>
                  )}
                </div>
                <div>
                  <div className="ft-hero-author-name">{authorName}</div>
                  <div className="ft-hero-author-meta">
                    <span>
                      <i className="far fa-calendar" aria-hidden /> {formatDate(tuto.created_at)}
                    </span>
                    {author.university && (
                      <span>
                        <i className="fas fa-university" aria-hidden /> {author.university}
                      </span>
                    )}
                  </div>
                </div>
              </div>
              <div className="ft-hero-stats" role="group" aria-label="Statistiques">
                <span title="Vues">
                  <i className="fas fa-eye" aria-hidden />
                  {formatNumber((tuto.views ?? 0) + 1)}
                </span>
                <span title="J&apos;aime">
                  <i className="fas fa-heart" aria-hidden />
                  {formatNumber(tuto.likes_count ?? 0)}
                </span>
                <span title="Commentaires">
                  <i className="fas fa-comment" aria-hidden />
                  {formatNumber(tuto.comments_count ?? 0)}
                </span>
              </div>
            </div>
          </div>
        </header>

        <section className="tutorial-show-section ft-course-body">
          <div className="container">
            {chapterList.length > 0 && (
              <details className="ft-toc-mobile">
                <summary>
                  <i className="fas fa-stream" aria-hidden />
                  Plan du cours
                  <span className="ft-toc-mobile-badge">{chapterList.length}</span>
                </summary>
                <ol className="ft-toc-mobile-list">
                  {chapterList.map((ch, idx) => {
                    const targetVideo =
                      (ch as { video_id?: number }).video_id ??
                      videoList[idx]?.id ??
                      videoList[(ch.chapter_number ?? 1) - 1]?.id ??
                      null
                    const href = chapterHref(targetVideo)
                    const label = (
                      <>
                        <span className="ft-toc-m-num">{ch.chapter_number}</span>
                        <span className="ft-toc-m-title">{ch.title}</span>
                      </>
                    )
                    return href ? (
                      <li key={ch.id}>
                        <a href={href}>{label}</a>
                      </li>
                    ) : (
                      <li key={ch.id}>
                        <span className="ft-toc-m-disabled">{label}</span>
                      </li>
                    )
                  })}
                </ol>
              </details>
            )}

            <div className="tutorial-layout" id="formation-contenu-principal">
              <div className="tutorial-main">
                <div className="ft-course-toolbar">
                  <TutorialLikeButton
                    tutorialId={tutorialId}
                    initialLikes={tuto.likes_count ?? 0}
                    initialLiked={liked}
                    isAuthenticated={!!profile}
                  />
                  <a href="#comments" className="ft-toolbar-btn ft-toolbar-btn--ghost">
                    <i className="far fa-comment" aria-hidden />
                    <span>Discuter</span>
                  </a>
                  {canEdit && (
                    <Link
                      href={`${FORMATIONS_PATH}/${tutorialId}/modifier`}
                      className="ft-toolbar-btn ft-toolbar-btn--primary"
                    >
                      <i className="fas fa-edit" aria-hidden />
                      <span>Modifier</span>
                    </Link>
                  )}
                </div>

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
                    const href = chapterHref(targetVideo)
                    const item = (
                      <>
                        <div className="chapter-number">{ch.chapter_number}</div>
                        <div className="chapter-content">
                          <h4 className="chapter-title">{ch.title}</h4>
                          {ch.description && (
                            <p className="chapter-description">{ch.description}</p>
                          )}
                        </div>
                        {href && (
                          <div className="btn-play-chapter">
                            <i className={`fas ${targetVideo ? 'fa-play' : 'fa-book-open'}`}></i>
                          </div>
                        )}
                      </>
                    )
                    return href ? (
                      <a
                        key={ch.id}
                        href={href}
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

            {textFirst ? (
              <>
                {showLecture && (
                  <div className="tutorial-content" id="ft-lecture">
                    <h3 className="ft-lecture-heading">
                      <i className="fas fa-book-reader" aria-hidden />{' '}
                      {formationReadingHeading(formationType)}
                    </h3>
                    {readingIntro && <p className="ft-section-hint ft-lecture-intro">{readingIntro}</p>}
                    <FormationContentPager tutorialId={tutorialId} sections={lectureSections} />
                  </div>
                )}
                {showVideos && (
                  <FormationLessonVideos
                    videos={videoList}
                    title={formationVideoBlockTitle(formationType)}
                    hint={formationVideoBlockHint(formationType)}
                  />
                )}
              </>
            ) : (
              <>
                {showVideos && (
                  <FormationLessonVideos
                    videos={videoList}
                    title={formationVideoBlockTitle(formationType)}
                    hint={formationVideoBlockHint(formationType)}
                  />
                )}
                {showLecture && (
                  <div className="tutorial-content" id="ft-lecture">
                    <h3 className="ft-lecture-heading">
                      <i className="fas fa-book-reader" aria-hidden />{' '}
                      {formationReadingHeading(formationType)}
                    </h3>
                    {readingIntro && <p className="ft-section-hint ft-lecture-intro">{readingIntro}</p>}
                    <FormationContentPager tutorialId={tutorialId} sections={lectureSections} />
                  </div>
                )}
              </>
            )}

            {hasAttachments && (
              <section id="ft-ressources" className="ft-ressources-stack" aria-label="Ressources téléchargeables ou externes">
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
                          <p>{(tuto.file_path ?? '').split('/').pop()}</p>
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
                      <i className="fas fa-external-link-alt" aria-hidden />
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
                      <i className="fas fa-arrow-right" aria-hidden /> Ouvrir
                    </a>
                  </div>
                )}
              </section>
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

            <div className="sidebar-card author-card ft-author-card">
              <h4 className="ft-author-card-title">
                <i className="fas fa-user-circle" aria-hidden />
                À propos de l&apos;auteur
              </h4>
              <div className="author-full-info ft-author-card-body">
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
                {authorProfileId ? (
                  <Link href={`/user/${authorProfileId}`} className="btn-view-profile ft-author-card-cta">
                    <span>Voir le profil</span>
                    <i className="fas fa-arrow-right" aria-hidden />
                  </Link>
                ) : (
                  <span className="btn-view-profile ft-author-card-cta" style={{ opacity: 0.55 }} aria-disabled>
                    Profil indisponible
                  </span>
                )}
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
                    const href = chapterHref(targetVideo)
                    return href ? (
                      <a
                        key={ch.id}
                        href={href}
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
                        {(tuto.file_path ?? '').split('/').pop()}
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
    </FormationLearnShell>
  )
}
