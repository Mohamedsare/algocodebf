'use client'

import Link from 'next/link'
import { useRouter, usePathname } from 'next/navigation'
import { useEffect, useRef, useState } from 'react'
import { logoutAction } from '@/app/actions/auth'
import { FORMATIONS_PATH } from '@/lib/routes'
import { getMySpacePath } from '@/lib/my-space'
import { SearchSuggestPanel } from '@/components/search/search-suggest-panel'
import { useSearchSuggest } from '@/components/search/use-search-suggest'
import type { Profile } from '@/types'

interface HeaderProps {
  profile: Profile | null
}

/**
 * Header reproduisant 1:1 layouts/header.php côté PHP.
 * Mêmes classes/ids pour que /public/css/style.css s'applique directement.
 * Interactivité pilotée par React (hamburger, dropdown, recherche overlay).
 */
export function Header({ profile }: HeaderProps) {
  const router = useRouter()
  const pathname = usePathname()

  const [burgerActive, setBurgerActive] = useState(false)
  const [dropdownActive, setDropdownActive] = useState(false)
  const [searchActive, setSearchActive] = useState(false)
  const [scrolled, setScrolled] = useState(false)
  const [navbarHidden, setNavbarHidden] = useState(false)
  const [searchQuery, setSearchQuery] = useState('')
  const lastScrollRef = useRef(0)
  const searchInputRef = useRef<HTMLInputElement>(null)
  const rootRef = useRef<HTMLDivElement>(null)

  // Ferme tous les menus au changement de route
  useEffect(() => {
    setBurgerActive(false)
    setDropdownActive(false)
    setSearchActive(false)
  }, [pathname])

  // Fermer le burger / le menu compte en cliquant en dehors
  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (!rootRef.current) return
      const target = e.target as Node
      const toggle = document.getElementById('navToggle')
      const menu = document.getElementById('burgerMenu')
      if (toggle && menu && !toggle.contains(target) && !menu.contains(target)) {
        setBurgerActive(false)
      }
      const userDd = document.getElementById('navUserDropdown')
      const userBtn = document.getElementById('navUserMenuButton')
      if (
        dropdownActive &&
        userDd &&
        userBtn &&
        !userDd.contains(target)
      ) {
        setDropdownActive(false)
      }
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [dropdownActive])

  // Scroll: masquer/afficher la barre du haut (desktop uniquement).
  // Sur mobile le dock bas est en position: fixed : ne jamais appliquer .navbar.hidden,
  // sinon la barre entière peut bouger et le tab bar ne reste plus correctement fixe.
  useEffect(() => {
    const mq = window.matchMedia('(max-width: 768px)')
    const applyScroll = () => {
      const y = window.scrollY
      setScrolled(y > 10)
      if (mq.matches) {
        setNavbarHidden(false)
      } else if (y > lastScrollRef.current && y > 100) {
        setNavbarHidden(true)
      } else {
        setNavbarHidden(false)
      }
      lastScrollRef.current = y
    }
    const onMq = () => {
      if (mq.matches) setNavbarHidden(false)
    }
    applyScroll()
    window.addEventListener('scroll', applyScroll, { passive: true })
    mq.addEventListener('change', onMq)
    return () => {
      window.removeEventListener('scroll', applyScroll)
      mq.removeEventListener('change', onMq)
    }
  }, [])

  // Focus sur le champ de recherche à l'ouverture
  useEffect(() => {
    if (searchActive) searchInputRef.current?.focus()
  }, [searchActive])

  // Fermer overlay recherche avec Escape
  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        setSearchActive(false)
        setBurgerActive(false)
        setDropdownActive(false)
      }
    }
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [])

  const navbarClass = [
    'navbar',
    scrolled ? 'scrolled' : '',
    navbarHidden ? 'hidden' : '',
  ]
    .filter(Boolean)
    .join(' ')

  const { data: overlaySuggest, loading: overlaySuggestLoading } = useSearchSuggest(searchQuery, {
    limit: 8,
    debounceMs: 220,
    enabled: searchActive,
  })

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    const q = searchQuery.trim()
    if (!q) return
    router.push(`/search?q=${encodeURIComponent(q)}`)
    setSearchActive(false)
    setSearchQuery('')
  }

  const handleLogout = async () => {
    await logoutAction()
  }

  const logoText = 'AlgoCodeBF'

  const userMenuLabel = profile?.prenom?.trim() || profile?.nom?.trim() || 'Mon compte'

  const navIsActive = (href: string) => {
    if (href === '/') return pathname === '/'
    return pathname === href || pathname.startsWith(`${href}/`)
  }

  const espaceActive = pathname.startsWith('/espace')
  const mySpaceHref = profile ? getMySpacePath(profile) : '/espace'

  const mainNavItems = [
    { href: '/', label: 'Accueil', icon: 'fa-home' },
    { href: '/forum', label: 'Forum', icon: 'fa-comments' },
    { href: FORMATIONS_PATH, label: 'Formations', icon: 'fa-graduation-cap' },
    { href: '/job', label: 'Opportunités', icon: 'fa-briefcase' },
    { href: '/project', label: 'Projets', icon: 'fa-code-branch' },
    { href: '/user', label: 'Membres', icon: 'fa-users' },
    { href: '/blog', label: 'Blog', icon: 'fa-blog' },
  ] as const

  return (
    <div
      ref={rootRef}
      className={profile ? 'site-header-root site-header-root--authed' : 'site-header-root'}
    >
      {/* Navigation */}
      <nav className={navbarClass}>
        <div className="container">
          <div className="nav-brand">
            <Link href="/">
              <span className="logo">{logoText}</span>
            </Link>
          </div>

          <button
            className={`nav-toggle${burgerActive ? ' active' : ''}`}
            id="navToggle"
            aria-label="Menu"
            aria-expanded={burgerActive}
            type="button"
            onClick={() => setBurgerActive(v => !v)}
          >
            <div className="hamburger-box">
              <span className="hamburger-line"></span>
              <span className="hamburger-line"></span>
              <span className="hamburger-line"></span>
            </div>
          </button>

          <ul className="nav-menu" id="navMenu" aria-label="Navigation principale">
            <li>
              <Link href="/" data-label="Accueil" className={navIsActive('/') ? 'active' : undefined}>
                <i className="fas fa-home"></i>
                <span className="dock-label">Accueil</span>
              </Link>
            </li>
            <li>
              <Link href="/forum" data-label="Forum" className={navIsActive('/forum') ? 'active' : undefined}>
                <i className="fas fa-comments"></i>
                <span className="dock-label">Forum</span>
              </Link>
            </li>
            <li>
              <Link
                href={FORMATIONS_PATH}
                data-label="Formations"
                className={navIsActive(FORMATIONS_PATH) ? 'active' : undefined}
              >
                <i className="fas fa-graduation-cap"></i>
                <span className="dock-label">Formations</span>
              </Link>
            </li>
            <li>
              <Link href="/job" data-label="Opportunités" className={navIsActive('/job') ? 'active' : undefined}>
                <i className="fas fa-briefcase"></i>
                <span className="dock-label">Jobs</span>
              </Link>
            </li>
            <li className="secondary-link">
              <Link href="/project" data-label="Projets" className={navIsActive('/project') ? 'active' : undefined}>
                <i className="fas fa-code-branch"></i>
                <span className="dock-label">Projets</span>
              </Link>
            </li>
            <li className="secondary-link">
              <Link href="/user" data-label="Membres" className={navIsActive('/user') ? 'active' : undefined}>
                <i className="fas fa-users"></i>
                <span className="dock-label">Membres</span>
              </Link>
            </li>
            <li className="nav-dock-exclude">
              <Link href="/blog" data-label="Blog" className={navIsActive('/blog') ? 'active' : undefined}>
                <i className="fas fa-blog"></i>
                <span className="dock-label">Blog</span>
              </Link>
            </li>

            {profile && (
              <li className={`dropdown${dropdownActive ? ' active' : ''}`} id="navUserDropdown">
                <button
                  type="button"
                  className="dropdown-toggle"
                  id="navUserMenuButton"
                  aria-expanded={dropdownActive}
                  aria-haspopup="true"
                  aria-controls="navUserMenuPanel"
                  onClick={() => setDropdownActive(v => !v)}
                >
                  <span className="dropdown-toggle-avatar" aria-hidden>
                    <i className="fas fa-user"></i>
                  </span>
                  <span className="dropdown-toggle-label">{userMenuLabel}</span>
                  <i
                    className={`fas fa-chevron-down nav-user-chevron${dropdownActive ? ' nav-user-chevron--open' : ''}`}
                    aria-hidden
                  />
                </button>
                <ul
                  className="dropdown-menu"
                  id="navUserMenuPanel"
                  role="menu"
                  aria-labelledby="navUserMenuButton"
                  aria-hidden={!dropdownActive}
                >
                  <li>
                    <Link href={`/user/${profile.id}`}>
                      <i className="fas fa-user"></i> Mon Profil
                    </Link>
                  </li>
                  <li>
                    <Link href={mySpaceHref}>
                      <i className="fas fa-table-columns"></i> Mon Espace
                    </Link>
                  </li>
                  <li>
                    <Link href="/user/modifier">
                      <i className="fas fa-edit"></i> Modifier
                    </Link>
                  </li>
                  <li>
                    <Link href="/message">
                      <i className="fas fa-envelope"></i> Messages
                    </Link>
                  </li>
                  <li>
                    <Link href="/user/classement">
                      <i className="fas fa-trophy"></i> Classement
                    </Link>
                  </li>
                  {profile.role === 'admin' && (
                    <>
                      <li>
                        <hr />
                      </li>
                      <li>
                        <Link href="/admin">
                          <i className="fas fa-cog"></i> Administration
                        </Link>
                      </li>
                    </>
                  )}
                  <li>
                    <hr />
                  </li>
                  <li>
                    <a
                      href="#"
                      onClick={e => {
                        e.preventDefault()
                        handleLogout()
                      }}
                    >
                      <i className="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                  </li>
                </ul>
              </li>
            )}
          </ul>

          {/* Icônes desktop uniquement (utilisateur non connecté) */}
          {!profile && (
            <div className="desktop-auth-icons">
              <Link href="/register" title="Créer un compte">
                <i className="fas fa-user-circle"></i>
              </Link>
              <Link href="/login" title="Se connecter">
                <i className="fas fa-sign-in-alt"></i>
              </Link>
            </div>
          )}

          {/* Icône de recherche */}
          <button
            className="search-toggle"
            id="searchToggle"
            type="button"
            onClick={() => setSearchActive(true)}
          >
            <i className="fas fa-search"></i>
          </button>
        </div>

        {/* Barre de recherche cachée */}
        <div
          className={`search-overlay${searchActive ? ' active' : ''}`}
          id="searchOverlay"
          onClick={e => {
            if (e.target === e.currentTarget) setSearchActive(false)
          }}
        >
          <div className="search-overlay-content">
            <form onSubmit={handleSearch} className="search-form-overlay" action="/search" method="GET">
              <input
                type="text"
                name="q"
                placeholder="Rechercher des membres, posts, formations..."
                id="searchInput"
                ref={searchInputRef}
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                autoComplete="off"
                aria-autocomplete="list"
                aria-controls="global-search-suggest"
              />
              <button type="submit">
                <i className="fas fa-search"></i> Rechercher
              </button>
            </form>
            <div id="global-search-suggest">
              <SearchSuggestPanel
                data={overlaySuggest}
                loading={overlaySuggestLoading}
                variant="overlay"
                showAllHref={
                  searchQuery.trim().length >= 2
                    ? `/search?q=${encodeURIComponent(searchQuery.trim())}`
                    : undefined
                }
                allLabel="Tous les résultats"
                onPick={() => {
                  setSearchActive(false)
                  setSearchQuery('')
                }}
              />
            </div>
            <button
              className="search-close"
              id="searchClose"
              type="button"
              onClick={() => setSearchActive(false)}
            >
              <i className="fas fa-times"></i>
            </button>
          </div>
        </div>
      </nav>

      {/* Drawer mobile : hors de <nav> pour z-index / backdrop. */}
      {burgerActive && (
        <button
          type="button"
          className="nb-backdrop"
          aria-label="Fermer le menu"
          onClick={() => setBurgerActive(false)}
        />
      )}
      <div
        className={`burger-menu nav-burger-saas${burgerActive ? ' active' : ''}`}
        id="burgerMenu"
        role="dialog"
        aria-modal="true"
        aria-label="Menu de navigation"
        aria-hidden={!burgerActive}
      >
        <div className="nb-panel-head">
          <span className="nb-panel-brand">
            <em aria-hidden />
            {logoText}
          </span>
          <button
            type="button"
            className="nb-panel-close"
            aria-label="Fermer le menu"
            onClick={() => setBurgerActive(false)}
          >
            <i className="fas fa-times" aria-hidden />
          </button>
        </div>

        <div className="nb-scroll">
          <button
            type="button"
            className="nb-search-btn"
            onClick={() => {
              setBurgerActive(false)
              setSearchActive(true)
            }}
          >
            <i className="fas fa-search" aria-hidden />
            Rechercher sur AlgoCodeBF…
          </button>

          <div className="nb-section">
            <span className="nb-section-label">Navigation</span>
            <div className="nb-list">
              {mainNavItems.map(item => (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`nb-link${navIsActive(item.href) ? ' nb-link--active' : ''}`}
                  onClick={() => setBurgerActive(false)}
                >
                  <span className="nb-ico">
                    <i className={`fas ${item.icon}`} aria-hidden />
                  </span>
                  {item.label}
                </Link>
              ))}
            </div>
          </div>

          <div className="nb-section">
            <span className="nb-section-label">La plateforme</span>
            <div className="nb-list">
              <Link
                href="/about"
                className={`nb-link nb-link--muted${navIsActive('/about') ? ' nb-link--active' : ''}`}
                onClick={() => setBurgerActive(false)}
              >
                <span className="nb-ico">
                  <i className="fas fa-circle-info" aria-hidden />
                </span>
                À propos
              </Link>
            </div>
          </div>

          {profile ? (
            <div className="nb-section">
              <span className="nb-section-label">Mon compte</span>
              <div className="nb-list">
                <Link
                  href={`/user/${profile.id}`}
                  className={`nb-link${navIsActive(`/user/${profile.id}`) ? ' nb-link--active' : ''}`}
                  onClick={() => setBurgerActive(false)}
                >
                  <span className="nb-ico">
                    <i className="fas fa-user" aria-hidden />
                  </span>
                  Mon profil
                </Link>
                <Link
                  href={mySpaceHref}
                  className={`nb-link${espaceActive ? ' nb-link--active' : ''}`}
                  onClick={() => setBurgerActive(false)}
                >
                  <span className="nb-ico">
                    <i className="fas fa-table-columns" aria-hidden />
                  </span>
                  Mon Espace
                </Link>
                <Link
                  href="/user/modifier"
                  className={`nb-link${navIsActive('/user/modifier') ? ' nb-link--active' : ''}`}
                  onClick={() => setBurgerActive(false)}
                >
                  <span className="nb-ico">
                    <i className="fas fa-pen-to-square" aria-hidden />
                  </span>
                  Modifier mon profil
                </Link>
                <Link
                  href="/message"
                  className={`nb-link${navIsActive('/message') ? ' nb-link--active' : ''}`}
                  onClick={() => setBurgerActive(false)}
                >
                  <span className="nb-ico">
                    <i className="fas fa-envelope" aria-hidden />
                  </span>
                  Messages
                </Link>
                <Link
                  href="/user/classement"
                  className={`nb-link${navIsActive('/user/classement') ? ' nb-link--active' : ''}`}
                  onClick={() => setBurgerActive(false)}
                >
                  <span className="nb-ico">
                    <i className="fas fa-trophy" aria-hidden />
                  </span>
                  Classement
                </Link>
                {profile.role === 'admin' && (
                  <Link
                    href="/admin"
                    className={`nb-link${navIsActive('/admin') ? ' nb-link--active' : ''}`}
                    onClick={() => setBurgerActive(false)}
                  >
                    <span className="nb-ico">
                      <i className="fas fa-shield-halved" aria-hidden />
                    </span>
                    Administration
                  </Link>
                )}
                <button
                  type="button"
                  className="nb-link nb-link--danger"
                  onClick={() => {
                    setBurgerActive(false)
                    void handleLogout()
                  }}
                >
                  <span className="nb-ico">
                    <i className="fas fa-right-from-bracket" aria-hidden />
                  </span>
                  Déconnexion
                </button>
              </div>
            </div>
          ) : (
            <div className="nb-section">
              <span className="nb-section-label">Accès</span>
              <div className="nb-list">
                <Link
                  href="/login"
                  className="nb-link nb-link--cta"
                  onClick={() => setBurgerActive(false)}
                >
                  <span className="nb-ico">
                    <i className="fas fa-right-to-bracket" aria-hidden />
                  </span>
                  Connexion
                </Link>
                <Link
                  href="/register"
                  className="nb-link nb-link--secondary"
                  onClick={() => setBurgerActive(false)}
                >
                  <span className="nb-ico">
                    <i className="fas fa-user-plus" aria-hidden />
                  </span>
                  Créer un compte
                </Link>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
