'use client'

import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { useEffect, useRef, useState } from 'react'
import { SearchSuggestPanel } from '@/components/search/search-suggest-panel'
import { useSearchSuggest } from '@/components/search/use-search-suggest'

export default function NotFound() {
  const router = useRouter()
  const [q, setQ] = useState('')
  const inputRef = useRef<HTMLInputElement>(null)
  const { data: nfSuggest, loading: nfLoading } = useSearchSuggest(q, { limit: 6, debounceMs: 260 })

  useEffect(() => {
    const id = setTimeout(() => inputRef.current?.focus(), 2000)
    return () => clearTimeout(id)
  }, [])

  const onKey = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      const v = q.trim()
      if (v) router.push(`/search?q=${encodeURIComponent(v)}`)
    }
  }

  return (
    <>
      <style>{notFoundStyles}</style>
      <div className="nf-body">
        <div className="floating-elements">
          <div className="floating-element"><i className="fas fa-code"></i></div>
          <div className="floating-element"><i className="fas fa-laptop-code"></i></div>
          <div className="floating-element"><i className="fas fa-terminal"></i></div>
          <div className="floating-element"><i className="fas fa-bug"></i></div>
          <div className="floating-element"><i className="fas fa-server"></i></div>
          <div className="floating-element"><i className="fas fa-database"></i></div>
          <div className="floating-element"><i className="fas fa-cogs"></i></div>
        </div>

        <div className="error-container">
          <div className="error-icon"><i className="fas fa-robot"></i></div>
          <div className="error-code">404</div>
          <h1 className="error-title">Oups ! Page Non Trouvée</h1>
          <p className="error-message">
            La page que vous recherchez semble avoir disparu dans le cyberespace.<br />
            Elle a peut-être été déplacée, supprimée ou n&apos;a jamais existé.
          </p>

          <div className="search-box nf-search-live">
            <input
              ref={inputRef}
              type="text"
              className="search-input"
              placeholder="Rechercher sur AlgoCodeBF..."
              value={q}
              onChange={e => setQ(e.target.value)}
              onKeyDown={onKey}
              autoComplete="off"
            />
            <SearchSuggestPanel
              data={nfSuggest}
              loading={nfLoading}
              variant="inline"
              showAllHref={q.trim().length >= 2 ? `/search?q=${encodeURIComponent(q.trim())}` : undefined}
              allLabel="Tous les résultats"
            />
          </div>

          <div className="error-actions">
            <Link href="/" className="nf-btn nf-btn-primary">
              <i className="fas fa-home"></i> Retour à l&apos;Accueil
            </Link>
            <Link href="/forum" className="nf-btn nf-btn-secondary">
              <i className="fas fa-comments"></i> Aller au Forum
            </Link>
          </div>
        </div>
      </div>
    </>
  )
}

const notFoundStyles = `
.nf-body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg,#3498db 0%, #2ecc71 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; position: relative; overflow: hidden; color: white; }
.nf-body::before { content: ''; position: absolute; width: 100%; height: 100%; background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>'); opacity: 0.3; }
.error-container { position: relative; z-index: 1; text-align: center; max-width: 700px; }
.error-code { font-size: 12rem; font-weight: 700; line-height: 1; margin-bottom: 20px; background: linear-gradient(45deg,#fff, rgba(255,255,255,.5)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: nf-float 3s ease-in-out infinite; }
@keyframes nf-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
.error-icon { font-size: 8rem; margin-bottom: 30px; animation: nf-bounce 2s ease-in-out infinite; }
@keyframes nf-bounce { 0%,20%,50%,80%,100% { transform: translateY(0);} 40% { transform: translateY(-30px);} 60% { transform: translateY(-15px);} }
.error-title { font-size: 2.5rem; margin-bottom: 20px; font-weight: 700; }
.error-message { font-size: 1.2rem; margin-bottom: 40px; opacity: .9; line-height: 1.6; }
.error-actions { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
.nf-btn { padding: 15px 35px; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 1.1rem; transition: all .3s ease; display: inline-flex; align-items: center; gap: 10px; }
.nf-btn-primary { background: white; color: #3498db; box-shadow: 0 10px 30px rgba(0,0,0,.2); }
.nf-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(0,0,0,.3); }
.nf-btn-secondary { background: rgba(255,255,255,.2); color: white; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,.3); }
.nf-btn-secondary:hover { background: rgba(255,255,255,.3); transform: translateY(-3px); }
.floating-elements { position: absolute; width: 100%; height: 100%; top: 0; left: 0; pointer-events: none; overflow: hidden; }
.floating-element { position: absolute; font-size: 2rem; opacity: .1; animation: nf-float-random 10s linear infinite; }
@keyframes nf-float-random { from { transform: translateY(100vh) rotate(0deg);} to { transform: translateY(-100px) rotate(360deg);} }
.floating-element:nth-child(1){ left:10%; animation-delay:0s; animation-duration:15s; }
.floating-element:nth-child(2){ left:20%; animation-delay:2s; animation-duration:12s; }
.floating-element:nth-child(3){ left:30%; animation-delay:4s; animation-duration:18s; }
.floating-element:nth-child(4){ left:50%; animation-delay:1s; animation-duration:14s; }
.floating-element:nth-child(5){ left:70%; animation-delay:3s; animation-duration:16s; }
.floating-element:nth-child(6){ left:80%; animation-delay:5s; animation-duration:13s; }
.floating-element:nth-child(7){ left:90%; animation-delay:6s; animation-duration:17s; }
.search-box { margin-top: 40px; max-width: 500px; margin-left: auto; margin-right: auto; }
.search-input { width: 100%; padding: 15px 20px; border-radius: 30px; border: 2px solid rgba(255,255,255,.3); background: rgba(255,255,255,.1); backdrop-filter: blur(10px); color: white; font-size: 1rem; transition: all .3s ease; }
.search-input::placeholder { color: rgba(255,255,255,.7); }
.search-input:focus { outline: none; background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.5); }
.nf-search-live { text-align: left; }
.nf-search-live .ss-panel--inline { margin-top: 12px; background: rgba(255,255,255,.96); border-radius: 16px; color: #222; box-shadow: 0 12px 40px rgba(0,0,0,.15); }
@media (max-width: 768px) {
  .error-code { font-size: 8rem; }
  .error-icon { font-size: 5rem; }
  .error-title { font-size: 1.8rem; }
  .error-message { font-size: 1rem; }
  .error-actions { flex-direction: column; align-items: stretch; }
  .nf-btn { justify-content: center; }
}
`
