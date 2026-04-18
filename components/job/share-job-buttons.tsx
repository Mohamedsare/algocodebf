'use client'

interface Props {
  title: string
}

export function ShareJobButtons({ title }: Props) {
  const onFacebook = () => {
    if (typeof window === 'undefined') return
    const url = encodeURIComponent(window.location.href)
    window.open(
      `https://www.facebook.com/sharer/sharer.php?u=${url}`,
      '_blank',
      'width=600,height=400'
    )
  }

  const onTwitter = () => {
    if (typeof window === 'undefined') return
    const url = encodeURIComponent(window.location.href)
    const text = encodeURIComponent(title)
    window.open(
      `https://twitter.com/intent/tweet?url=${url}&text=${text}`,
      '_blank',
      'width=600,height=400'
    )
  }

  const onCopy = async () => {
    if (typeof window === 'undefined') return
    try {
      await navigator.clipboard.writeText(window.location.href)
      alert('Lien copié dans le presse-papiers !')
    } catch {
      // ignore
    }
  }

  return (
    <div className="share-buttons">
      <button type="button" onClick={onFacebook} className="btn-share facebook">
        <i className="fab fa-facebook"></i> Facebook
      </button>
      <button type="button" onClick={onTwitter} className="btn-share twitter">
        <i className="fab fa-twitter"></i> Twitter
      </button>
      <button type="button" onClick={onCopy} className="btn-share link">
        <i className="fas fa-link"></i> Copier le lien
      </button>
    </div>
  )
}
