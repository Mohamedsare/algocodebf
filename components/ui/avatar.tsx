import Image from 'next/image'
import { cn } from '@/lib/utils'
import { getInitials, buildAvatarUrl } from '@/lib/utils'

interface AvatarProps {
  src?: string | null
  prenom?: string
  nom?: string
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl'
  className?: string
}

const sizes = {
  xs: { dim: 24, text: 'text-[10px]' },
  sm: { dim: 32, text: 'text-xs' },
  md: { dim: 40, text: 'text-sm' },
  lg: { dim: 56, text: 'text-lg' },
  xl: { dim: 80, text: 'text-2xl' },
}

export function Avatar({ src, prenom = '', nom = '', size = 'md', className }: AvatarProps) {
  const { dim, text } = sizes[size]
  const initials = getInitials(prenom || '?', nom || '')
  const imgSrc = buildAvatarUrl(src)

  return (
    <div
      className={cn(
        'relative rounded-full overflow-hidden flex-shrink-0 bg-gradient-to-br from-[#C8102E] to-[#006A4E]',
        'flex items-center justify-center',
        className
      )}
      style={{ width: dim, height: dim }}
    >
      {src ? (
        <Image
          src={imgSrc}
          alt={`${prenom} ${nom}`}
          fill
          className="object-cover"
          sizes={`${dim}px`}
        />
      ) : (
        <span className={cn('font-bold text-white select-none', text)}>
          {initials}
        </span>
      )}
    </div>
  )
}
