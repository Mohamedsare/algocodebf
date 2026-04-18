import type { Metadata } from 'next'
import Link from 'next/link'
import { getProfile } from '@/lib/supabase/server'

export const metadata: Metadata = {
  title: 'À propos',
  description: 'Découvrez AlgoCodeBF, le hub numérique des informaticiens du Burkina Faso.',
}

export default async function AboutPage() {
  const profile = await getProfile()
  const isLogged = !!profile

  return (
    <>
      <style>{aboutStyles}</style>

      <section className="hero-carousel">
        <div className="carousel-container" style={{ minHeight: 550 }}>
          <div className="carousel-slide active" style={{ position: 'relative', minHeight: 550 }}>
            <div
              className="slide-content"
              style={{ gridTemplateColumns: '1fr 1fr', padding: '60px 40px', gap: 60 }}
            >
              <div
                className="slide-left"
                style={{ paddingRight: 0, display: 'flex', alignItems: 'center' }}
              >
                <div className="slide-text">
                  <span className="slide-badge">🇧🇫 À propos</span>
                  <h1 className="slide-title">AlgoCodeBF</h1>
                  <p className="slide-description">
                    Le hub numérique qui rassemble et valorise tous les informaticiens du Burkina Faso
                  </p>
                </div>
              </div>

              <div
                className="slide-right"
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
              >
                <div className="monitor-scene">
                  <div className="monitor">
                    <div className="monitor-screen">
                      <div className="screen-content">
                        <div className="screen-header">
                          <div className="window-buttons">
                            <span className="dot red"></span>
                            <span className="dot yellow"></span>
                            <span className="dot green"></span>
                          </div>
                          <div className="url-bar">algocodebf.bf - Code Editor</div>
                        </div>
                        <div className="html-code">
                          <div className="code-line">
                            <span className="code-purple">&lt;html&gt;</span>
                          </div>
                          <div className="code-line">
                            {'  '}
                            <span className="code-purple">&lt;head&gt;</span>
                          </div>
                          <div className="code-line">
                            {'    '}
                            <span className="code-purple">&lt;title&gt;</span>AlgoCodeBF - Burkina Faso
                            <span className="code-purple">&lt;/title&gt;</span>
                          </div>
                          <div className="code-line">
                            {'  '}
                            <span className="code-purple">&lt;/head&gt;</span>
                          </div>
                          <div className="code-line">
                            {'  '}
                            <span className="code-purple">&lt;body&gt;</span>
                          </div>
                          <div className="code-line">
                            {'    '}
                            <span className="code-purple">&lt;header&gt;</span>
                          </div>
                          <div className="code-line">
                            {'      '}
                            <span className="code-purple">&lt;h1&gt;</span>🇧🇫 AlgoCodeBF
                            <span className="code-purple">&lt;/h1&gt;</span>
                          </div>
                          <div className="code-line">
                            {'    '}
                            <span className="code-purple">&lt;/header&gt;</span>
                          </div>
                          <div className="code-line">
                            {'    '}
                            <span className="code-purple">&lt;main&gt;</span>
                          </div>
                          <div className="code-line">
                            {'      '}
                            <span className="code-purple">&lt;p&gt;</span>Communauté Tech du Burkina Faso
                            <span className="code-purple">&lt;/p&gt;</span>
                          </div>
                          <div className="code-line">
                            {'    '}
                            <span className="code-purple">&lt;/main&gt;</span>
                          </div>
                          <div className="code-line">
                            {'  '}
                            <span className="code-purple">&lt;/body&gt;</span>
                          </div>
                          <div className="code-line">
                            <span className="code-purple">&lt;/html&gt;</span>
                          </div>
                        </div>
                        <div className="screen-shine"></div>
                      </div>
                    </div>
                  </div>

                  <div className="floating-icon icon-1">💻</div>
                  <div className="floating-icon icon-2">🚀</div>
                  <div className="floating-icon icon-3">⚡</div>
                  <div className="floating-icon icon-4">🌟</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Mission */}
      <section className="content-section">
        <div className="container">
          <div className="section-header">
            <h2>
              <i className="fas fa-bullseye"></i> Notre Mission
            </h2>
          </div>
          <div className="post-card" style={{ padding: 40, textAlign: 'center' }}>
            <p style={{ fontSize: 18, lineHeight: 1.8, marginBottom: 20 }}>
              AlgoCodeBF a été créé avec une vision claire :{' '}
              <strong>rassembler, valoriser et connecter tous les informaticiens burkinabè</strong>. Nous croyons
              en la puissance de la communauté tech du Burkina Faso et son potentiel à transformer le pays.
            </p>
            <p style={{ fontSize: 18, lineHeight: 1.8, marginBottom: 0 }}>
              Notre plateforme offre un espace où les étudiants, jeunes diplômés et professionnels de l&apos;IT
              peuvent échanger, apprendre, collaborer et saisir des opportunités.
            </p>
          </div>
        </div>
      </section>

      {/* Valeurs */}
      <section className="content-section bg-light">
        <div className="container">
          <div className="section-header">
            <h2>
              <i className="fas fa-heart"></i> Nos Valeurs
            </h2>
          </div>
          <div className="tutorials-grid">
            {[
              { emoji: '🤝', title: 'Collaboration', text: "Nous favorisons l'entraide et le partage de connaissances entre tous les membres de la communauté." },
              { emoji: '🎓', title: 'Apprentissage', text: "Nous encourageons la formation continue et le partage d'expertise à travers des parcours et ressources structurés." },
              { emoji: '🚀', title: 'Innovation', text: "Nous soutenons les idées nouvelles et les projets innovants qui peuvent faire la différence." },
              { emoji: '🌍', title: 'Inclusion', text: 'Nous accueillons tous les informaticiens du Burkina, quel que soit leur niveau ou domaine.' },
            ].map(v => (
              <div key={v.title} className="tutorial-card" style={{ textAlign: 'center' }}>
                <div style={{ fontSize: 60, marginBottom: 20 }}>{v.emoji}</div>
                <h3 style={{ fontSize: 22, color: 'var(--dark-color)', marginBottom: 15 }}>{v.title}</h3>
                <p>{v.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Fonctionnalités */}
      <section className="content-section">
        <div className="container">
          <div className="section-header">
            <h2>
              <i className="fas fa-tools"></i> Nos Fonctionnalités
            </h2>
          </div>
          <div className="posts-grid">
            {[
              { icon: 'fa-comments', title: 'Forum de Discussion', text: 'Échangez sur tous les sujets tech : programmation, réseau, cybersécurité, IA, et plus encore.' },
              { icon: 'fa-book', title: 'Formations', text: 'Parcours complets par des formateurs : vidéos, chapitres et supports — avec inscription payante à venir pour les apprenants.' },
              { icon: 'fa-briefcase', title: 'Opportunités', text: "Découvrez des offres d'emploi, stages, hackathons et formations adaptées à vos compétences." },
              { icon: 'fa-project-diagram', title: 'Projets Collaboratifs', text: "Créez ou rejoignez des projets tech avec d'autres membres de la communauté." },
              { icon: 'fa-trophy', title: 'Badges & Classements', text: 'Gagnez des badges et montez dans le classement grâce à vos contributions.' },
              { icon: 'fa-envelope', title: 'Messagerie Privée', text: 'Communiquez directement avec les autres membres pour des échanges plus personnels.' },
            ].map(f => (
              <div key={f.title} className="post-card">
                <div style={{ textAlign: 'center', marginBottom: 20 }}>
                  <i
                    className={`fas ${f.icon}`}
                    style={{
                      fontSize: 40,
                      background: 'var(--gradient-burkinabe)',
                      WebkitBackgroundClip: 'text',
                      WebkitTextFillColor: 'transparent',
                      backgroundClip: 'text',
                    }}
                  ></i>
                </div>
                <h3 style={{ textAlign: 'center', fontSize: 20, marginBottom: 15 }}>{f.title}</h3>
                <p style={{ textAlign: 'center' }}>{f.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Équipe */}
      <section className="content-section bg-light">
        <div className="container">
          <div className="section-header">
            <h2>
              <i className="fas fa-users"></i> L&apos;Équipe
            </h2>
          </div>
          <div className="post-card" style={{ padding: 40, textAlign: 'center' }}>
            <p style={{ fontSize: 18, lineHeight: 1.8, marginBottom: 30 }}>
              AlgoCodeBF a été développé par des passionnés de technologie qui croient au potentiel du Burkina Faso
              dans le domaine du numérique.
            </p>
            <div
              style={{
                background: 'var(--light-color)',
                padding: 30,
                borderRadius: 12,
                maxWidth: 600,
                margin: '0 auto',
              }}
            >
              <p style={{ fontSize: 16, marginBottom: 15 }}>
                <strong>Créateur & Développeur :</strong> Mohamed SARE
              </p>
              <p style={{ fontSize: 16, marginBottom: 15 }}>
                <strong>Date de lancement :</strong> Octobre 2025
              </p>
              <p style={{ fontSize: 16, marginBottom: 0 }}>
                <strong>Localisation :</strong> Ouagadougou, Burkina Faso 🇧🇫
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="cta-section">
        <div className="container">
          <div className="cta-content">
            <h2>Rejoignez-nous !</h2>
            <p>Faites partie de la communauté tech burkinabè et contribuez à l&apos;essor du numérique au Burkina Faso</p>
            {!isLogged ? (
              <Link href="/register" className="btn btn-primary btn-lg">
                S&apos;INSCRIRE GRATUITEMENT
              </Link>
            ) : (
              <Link href="/forum" className="btn btn-primary btn-lg">
                EXPLORER LA PLATEFORME
              </Link>
            )}
          </div>
        </div>
      </section>
    </>
  )
}

const aboutStyles = `
.monitor-scene { position: relative; width: 100%; height: 450px; perspective: 1400px; display: flex; align-items: center; justify-content: center; }
.monitor { position: relative; width: 500px; height: 350px; transform-style: preserve-3d; animation: monitorFloat 6s ease-in-out infinite; }
@keyframes monitorFloat { 0%, 100% { transform: translateY(0) rotateY(-15deg) rotateX(5deg); } 50% { transform: translateY(-20px) rotateY(-15deg) rotateX(5deg); } }
.monitor-screen { position: relative; width: 100%; height: 100%; background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%); border-radius: 15px; box-shadow: 0 -10px 40px rgba(0,0,0,.6), inset 0 0 0 12px #0a0a0a, inset 0 0 0 13px #1a1a1a; padding: 18px; transform-style: preserve-3d; }
.screen-content { width:100%; height:100%; background: linear-gradient(135deg,#0f1419 0%,#1a1f2e 100%); border-radius:6px; overflow:hidden; position:relative; box-shadow: inset 0 0 30px rgba(0,0,0,.5); }
.screen-header { display:flex; align-items:center; gap:15px; padding:12px 15px; background: rgba(0,0,0,.3); border-bottom: 1px solid rgba(255,255,255,.05); }
.window-buttons { display:flex; gap:6px; }
.dot { width:10px; height:10px; border-radius:50%; box-shadow: 0 2px 4px rgba(0,0,0,.3); }
.dot.red { background: linear-gradient(135deg,#ff5f57 0%,#ff3b30 100%); }
.dot.yellow { background: linear-gradient(135deg,#ffbd2e 0%,#ffa500 100%); }
.dot.green { background: linear-gradient(135deg,#28c840 0%,#20a030 100%); }
.url-bar { flex:1; background: rgba(255,255,255,.05); padding:6px 12px; border-radius:6px; font-size:11px; color:rgba(255,255,255,.6); font-family:'Courier New', monospace; }
.html-code { padding:25px; font-family:'Courier New', monospace; font-size:14px; line-height:1.8; white-space: pre; }
.code-line { color:#e0e0e0; text-shadow: 0 0 8px rgba(100,200,255,.3); margin-bottom:2px; }
.code-purple { color:#c792ea; } .code-blue { color:#82aaff; } .code-orange { color:#f78c6c; } .code-green { color:#c3e88d; }
.screen-shine { position:absolute; top:0; left:-100%; width:100%; height:100%; background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.1) 50%, transparent 100%); animation: shine 8s ease-in-out infinite; }
@keyframes shine { 0%,100% { left:-100%; } 50% { left:100%; } }
.floating-icon { position:absolute; font-size:32px; animation: about-float 4s ease-in-out infinite; filter: drop-shadow(0 4px 8px rgba(0,0,0,.3)); }
.icon-1 { top:10%; left:10%; animation-delay:0s; }
.icon-2 { top:20%; right:15%; animation-delay:1s; }
.icon-3 { bottom:20%; left:15%; animation-delay:2s; }
.icon-4 { bottom:15%; right:10%; animation-delay:3s; }
@keyframes about-float { 0%,100% { transform: translateY(0) rotate(0deg); opacity:.7; } 50% { transform: translateY(-20px) rotate(10deg); opacity:1; } }
@media (max-width: 768px) {
  .slide-content { grid-template-columns: 1fr !important; gap: 30px !important; padding: 40px 20px !important; }
  .slide-left { order: 2; } .slide-right { order: 1; }
  .monitor-scene { height: 320px; perspective: 1000px; }
  .monitor { width: 350px; height: 240px; transform: scale(0.85); }
  .monitor-screen { padding: 15px; } .html-code { padding: 20px; font-size: 11px; }
  .floating-icon { font-size: 24px; }
}
@media (max-width: 480px) {
  .monitor { transform: scale(0.72); } .monitor-scene { height: 280px; }
  .html-code { font-size: 9px; line-height: 1.6; }
  .floating-icon { font-size: 20px; }
}
`
