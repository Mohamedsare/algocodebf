import type { Metadata } from 'next'
import { notFound, redirect } from 'next/navigation'
import { requireLogin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { TutorialCreateClient } from '@/components/tutorial/tutorial-create-client'
import { TutorialVideoManager } from '@/components/tutorial/tutorial-video-manager'
import { FORMATIONS_PATH } from '@/lib/routes'

export const metadata: Metadata = { title: 'Modifier la formation' }

const DEFAULT_CATEGORIES = [
  'Algorithmique',
  'Structures de données',
  'Web Frontend',
  'Web Backend',
  'Mobile',
  'Data Science',
  'DevOps',
  'Sécurité',
  'Autre',
]

interface EditProps {
  params: Promise<{ id: string }>
}

export default async function TutorialEditPage({ params }: EditProps) {
  const { id } = await params
  const numericId = Number(id)
  if (!Number.isFinite(numericId)) notFound()

  const profile = await requireLogin()
  const supabase = await createClient()

  const [{ data: tuto }, { data: tags }, { data: videos }, { data: cats }] = await Promise.all([
    supabase.from('tutorials').select('*').eq('id', numericId).maybeSingle(),
    supabase.from('tutorial_tags').select('tags(name)').eq('tutorial_id', numericId),
    supabase
      .from('tutorial_videos')
      .select('id, title, file_path, file_name, file_size, order_index')
      .eq('tutorial_id', numericId)
      .order('order_index'),
    supabase.from('tutorial_categories').select('name').order('name'),
  ])

  if (!tuto) notFound()
  if (tuto.user_id !== profile.id && profile.role !== 'admin')
    redirect(`${FORMATIONS_PATH}/${numericId}`)

  const tagsCsv = ((tags ?? []) as unknown as Array<{ tags: { name: string } | null }>)
    .map(t => t.tags?.name)
    .filter((n): n is string => Boolean(n))
    .join(', ')

  const dbCats = ((cats ?? []) as Array<{ name: string }>).map(c => c.name)
  const categories = Array.from(new Set([...DEFAULT_CATEGORIES, ...dbCats]))

  return (
    <div className="formation-create-saas">
      <TutorialCreateClient
        mode="edit"
        tutorialId={numericId}
        categories={categories}
        initial={{
          title: tuto.title,
          description: tuto.description ?? '',
          content: tuto.content ?? '',
          category: tuto.category ?? '',
          type: tuto.type,
          level: tuto.level,
          external_link: tuto.external_link,
          thumbnail: tuto.thumbnail,
          tags: tagsCsv,
        }}
      />

      <div className="fc-video-block">
        <div className="container">
          <section className="fc-panel fc-panel-video" aria-labelledby="fc-video-heading">
            <div className="fc-section-head">
              <span className="fc-section-icon" aria-hidden>
                <i className="fas fa-video" />
              </span>
              <div>
                <h2 className="fc-section-title" id="fc-video-heading">
                  Vidéos du parcours
                </h2>
                <p className="fc-section-desc">
                  Ajoutez ou retirez des fichiers vidéo associés à cette formation.
                </p>
              </div>
            </div>
            <TutorialVideoManager tutorialId={numericId} initialVideos={videos ?? []} />
          </section>
        </div>
      </div>
    </div>
  )
}
