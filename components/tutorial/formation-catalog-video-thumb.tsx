'use client'

/**
 * Aperçu vidéo pour les cartes catalogue : rendu client pour éviter les soucis RSC / hydration.
 */
export function FormationCatalogVideoThumb({
  src,
  className,
}: {
  src: string
  className?: string
}) {
  return (
    <video
      className={className}
      muted
      playsInline
      preload="metadata"
      src={`${src}#t=0.001`}
      aria-hidden
    />
  )
}
