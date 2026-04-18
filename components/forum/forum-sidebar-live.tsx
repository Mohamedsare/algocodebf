'use client'

import { useEffect, useState } from 'react'
import Link from 'next/link'
import { createClient } from '@/lib/supabase/client'
import { buildAvatarUrl } from '@/lib/utils'
import type { Profile } from '@/types'

interface TrendingItem {
  id: number
  title: string
  views: number
  rank: number
}

interface Contributor {
  id: string
  prenom: string
  nom: string
  photo_path: string | null
  posts_count: number
}

interface Props {
  initialTrending: TrendingItem[]
  initialContributors: Contributor[]
  profile: Profile | null
}

export function ForumSidebarLive({ initialTrending, initialContributors, profile }: Props) {
  const [onlineCount, setOnlineCount] = useState(1)

  useEffect(() => {
    const supabase = createClient()
    const channel = supabase.channel('forum:presence', {
      config: { presence: { key: profile?.id ?? `anon-${Math.random().toString(36).slice(2)}` } },
    })

    channel
      .on('presence', { event: 'sync' }, () => {
        const state = channel.presenceState()
        setOnlineCount(Object.keys(state).length)
      })
      .subscribe(async (status) => {
        if (status === 'SUBSCRIBED') {
          await channel.track({
            user_id: profile?.id ?? null,
            name: profile ? `${profile.prenom} ${profile.nom}` : 'Visiteur',
            photo: profile?.photo_path ?? null,
            joined_at: new Date().toISOString(),
          })
        }
      })

    return () => {
      supabase.removeChannel(channel)
    }
  }, [profile])

  return (
    <>
      {/* Présence live */}
      <div className="sidebar-card-saas">
        <h3 className="card-title">
          <i className="fas fa-circle" style={{ fontSize: 7, color: '#10B981' }}></i>
          En direct
        </h3>
        <div className="presence-group" style={{ fontSize: 13 }}>
          <span className="presence-dot"></span>
          <strong style={{ color: 'var(--f-text)', fontWeight: 700 }}>{onlineCount}</strong>
          <span>membre{onlineCount > 1 ? 's' : ''} en ligne maintenant</span>
        </div>
      </div>

      {/* Trending */}
      <div className="sidebar-card-saas">
        <h3 className="card-title">
          <i className="fas fa-fire"></i>
          Topics tendances
        </h3>
        {initialTrending.length === 0 ? (
          <p style={{ fontSize: 13, color: 'var(--f-text-muted)', margin: 0 }}>
            Aucun topic tendance cette semaine.
          </p>
        ) : (
          <div className="trending-list-saas">
            {initialTrending.map((t) => (
              <Link key={t.id} href={`/forum/${t.id}`} prefetch={false} className="trending-item-saas">
                <span className="trending-rank">0{t.rank}</span>
                <span className="trending-title-saas">{t.title}</span>
                <span className="trending-views">
                  <i className="fas fa-eye" style={{ fontSize: 10 }}></i>
                  {t.views}
                </span>
              </Link>
            ))}
          </div>
        )}
      </div>

      {/* Top contributeurs */}
      <div className="sidebar-card-saas">
        <h3 className="card-title">
          <i className="fas fa-trophy"></i>
          Top contributeurs
        </h3>
        {initialContributors.length === 0 ? (
          <p style={{ fontSize: 13, color: 'var(--f-text-muted)', margin: 0 }}>
            Aucun contributeur pour le moment.
          </p>
        ) : (
          <div className="contributors-list-saas">
            {initialContributors.map((c, i) => (
              <Link key={c.id} href={`/user/${c.id}`} className="contributor-item-saas">
                <div className="contributor-avatar-saas">
                  {c.photo_path ? (
                    <img src={buildAvatarUrl(c.photo_path)} alt={`${c.prenom} ${c.nom}`} />
                  ) : (
                    <span>{(c.prenom ?? 'U').charAt(0).toUpperCase()}</span>
                  )}
                </div>
                <div className="contributor-info-saas">
                  <div className="contributor-name-saas">
                    {c.prenom} {c.nom}
                  </div>
                  <div className="contributor-meta-saas">{c.posts_count} discussion{c.posts_count > 1 ? 's' : ''}</div>
                </div>
                <span className="contributor-badge">#{i + 1}</span>
              </Link>
            ))}
          </div>
        )}
      </div>

      {/* Règles */}
      <div className="sidebar-card-saas">
        <h3 className="card-title">
          <i className="fas fa-shield-alt"></i>
          Règles du forum
        </h3>
        <ul className="rules-list-saas">
          <li><i className="fas fa-check-circle"></i>Soyez respectueux et bienveillant envers les autres membres</li>
          <li><i className="fas fa-check-circle"></i>Pas de spam, pas de publicité non sollicitée</li>
          <li><i className="fas fa-check-circle"></i>Partagez du contenu pertinent et de qualité</li>
          <li><i className="fas fa-check-circle"></i>Aucun harcèlement ou discours haineux toléré</li>
          <li><i className="fas fa-check-circle"></i>Entraide et partage de connaissances avant tout</li>
        </ul>
      </div>
    </>
  )
}
