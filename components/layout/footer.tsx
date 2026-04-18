import Link from 'next/link'
import { FORMATIONS_PATH } from '@/lib/routes'

/**
 * Footer reproduisant 1:1 layouts/footer.php côté PHP.
 * Même structure/classes pour que /public/css/style.css et footer-legal.css s'appliquent directement.
 */
export function Footer() {
  const year = new Date().getFullYear()
  const siteName = 'AlgoCodeBF'
  const siteDescription = 'Hub numérique des informaticiens du Burkina Faso'
  const contactEmail = 'contact@algocodebf.bf'

  return (
    <>
      <footer className="footer">
        <div className="container">
          <div className="footer-grid">
            <div className="footer-section">
              <h3>{siteName}</h3>
              <p>{siteDescription}</p>
              <div className="social-links">
                <a href="#" aria-label="Facebook">
                  <i className="fab fa-facebook"></i>
                </a>
                <a href="#" aria-label="Twitter">
                  <i className="fab fa-twitter"></i>
                </a>
                <a href="#" aria-label="LinkedIn">
                  <i className="fab fa-linkedin"></i>
                </a>
                <a href="#" aria-label="GitHub">
                  <i className="fab fa-github"></i>
                </a>
              </div>
            </div>

            <div className="footer-section">
              <h4>Navigation</h4>
              <ul>
                <li>
                  <Link href="/about">À propos</Link>
                </li>
                <li>
                  <Link href="/forum">Forum</Link>
                </li>
                <li>
                  <Link href={FORMATIONS_PATH}>Formations</Link>
                </li>
                <li>
                  <Link href="/job">Opportunités</Link>
                </li>
              </ul>
            </div>

            <div className="footer-section">
              <h4>Communauté</h4>
              <ul>
                <li>
                  <Link href="/user">Membres</Link>
                </li>
                <li>
                  <Link href="/project">Projets</Link>
                </li>
                <li>
                  <Link href="/user/classement">Classement</Link>
                </li>
                <li>
                  <Link href="/blog">Blog</Link>
                </li>
              </ul>
            </div>

            <div className="footer-section">
              <h4>Contact</h4>
              <ul>
                <li>
                  <i className="fas fa-envelope"></i> {contactEmail}
                </li>
              </ul>
            </div>
          </div>

          <div className="footer-bottom">
            <div className="footer-legal">
              <div className="legal-links">
                <Link href="/politique/confidentialite">Politique de confidentialité</Link>
                <Link href="/politique/conditions">Conditions d&apos;utilisation</Link>
                <Link href="/politique/mentions">Mentions légales</Link>
              </div>
              <p>
                &copy; {year} {siteName}. Tous droits réservés. Développé avec{' '}
                <span style={{ color: '#C8102E' }}>❤️</span> au Burkina Faso
              </p>
            </div>
          </div>
        </div>
      </footer>

      {/* Bouton Retour en Haut 🇧🇫 (piloté par /js/scroll-to-top.js) */}
      <button
        id="scrollToTop"
        className="scroll-to-top"
        aria-label="Retour en haut"
        title="Retour en haut"
        type="button"
      >
        <i className="fas fa-arrow-up"></i>
        <span className="scroll-text">Haut</span>
      </button>
    </>
  )
}
