'use client'

import { useState } from 'react'
import { Play, ChevronLeft, ChevronRight } from 'lucide-react'
import { buildFileUrl } from '@/lib/utils'

interface Video {
  id: number
  title: string | null
  file_path: string | null
  duration: number | null
  order_index: number
}

interface TutorialVideoPlayerProps {
  videos: Video[]
  tutorialTitle: string
}

export function TutorialVideoPlayer({ videos, tutorialTitle }: TutorialVideoPlayerProps) {
  const [currentIndex, setCurrentIndex] = useState(0)
  const current = videos[currentIndex]

  if (!videos.length) {
    return (
      <div className="aspect-video bg-gray-900 rounded-2xl flex items-center justify-center border border-gray-800">
        <div className="text-center text-gray-500">
          <Play size={48} className="mx-auto mb-2 opacity-30" />
          <p className="text-sm">Aucune vidéo disponible</p>
        </div>
      </div>
    )
  }

  const videoUrl = current?.file_path ? buildFileUrl(current.file_path) : null

  return (
    <div>
      {/* Video player */}
      <div className="aspect-video bg-black rounded-2xl overflow-hidden border border-gray-800">
        {videoUrl ? (
          <video
            key={current.id}
            controls
            className="w-full h-full"
            preload="metadata"
          >
            <source src={videoUrl} />
            Votre navigateur ne supporte pas la lecture vidéo.
          </video>
        ) : (
          <div className="w-full h-full flex items-center justify-center text-gray-500">
            <div className="text-center">
              <Play size={48} className="mx-auto mb-2 opacity-30" />
              <p className="text-sm">Vidéo non disponible</p>
            </div>
          </div>
        )}
      </div>

      {/* Current video title */}
      {videos.length > 1 && (
        <div className="flex items-center justify-between mt-3">
          <div>
            <p className="text-sm text-gray-400">Vidéo {currentIndex + 1} / {videos.length}</p>
            <p className="text-white font-medium text-sm">{current?.title ?? `Partie ${currentIndex + 1}`}</p>
          </div>
          <div className="flex gap-2">
            <button
              onClick={() => setCurrentIndex(i => Math.max(0, i - 1))}
              disabled={currentIndex === 0}
              className="w-9 h-9 rounded-xl bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white disabled:opacity-30 flex items-center justify-center transition-colors"
            >
              <ChevronLeft size={18} />
            </button>
            <button
              onClick={() => setCurrentIndex(i => Math.min(videos.length - 1, i + 1))}
              disabled={currentIndex === videos.length - 1}
              className="w-9 h-9 rounded-xl bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white disabled:opacity-30 flex items-center justify-center transition-colors"
            >
              <ChevronRight size={18} />
            </button>
          </div>
        </div>
      )}

      {/* Video list */}
      {videos.length > 1 && (
        <div className="mt-4 space-y-1">
          {videos.map((v, i) => (
            <button
              key={v.id}
              onClick={() => setCurrentIndex(i)}
              className={`w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-left transition-colors ${
                i === currentIndex
                  ? 'bg-[#C8102E]/20 text-[#C8102E] border border-[#C8102E]/30'
                  : 'hover:bg-gray-800 text-gray-400 hover:text-white'
              }`}
            >
              <div className={`w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold ${
                i === currentIndex ? 'bg-[#C8102E] text-white' : 'bg-gray-700 text-gray-400'
              }`}>
                {i + 1}
              </div>
              <span className="text-sm truncate">{v.title ?? `Vidéo ${i + 1}`}</span>
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
