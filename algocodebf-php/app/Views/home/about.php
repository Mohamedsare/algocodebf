<?php require_once VIEWS . '/layouts/header.php'; ?>

<!-- Section Hero avec PC 3D -->
<section class="hero-carousel">
    <div class="carousel-container" style="min-height: 550px;">
        <div class="carousel-slide active" style="position: relative; min-height: 550px;">
            <div class="slide-content" style="grid-template-columns: 1fr 1fr; padding: 60px 40px; gap: 60px;">
                <!-- Texte à gauche -->
                <div class="slide-left" style="padding-right: 0; display: flex; align-items: center;">
                    <div class="slide-text">
                        <span class="slide-badge">🇧🇫 À propos</span>
                        <h1 class="slide-title">AlgoCodeBF</h1>
                        <p class="slide-description">Le hub numérique qui rassemble et valorise tous les informaticiens du Burkina Faso</p>
                    </div>
                </div>
                
                <!-- Écran large avec code HTML -->
                <div class="slide-right" style="display: flex; align-items: center; justify-content: center;">
                    <div class="monitor-scene">
                        <div class="monitor">
                            <!-- Écran large -->
                            <div class="monitor-screen">
                                <div class="screen-content">
                                    <div class="screen-header">
                                        <div class="window-buttons">
                                            <span class="dot red"></span>
                                            <span class="dot yellow"></span>
                                            <span class="dot green"></span>
                                        </div>
                                        <div class="url-bar">hubtech.bf - Code Editor</div>
                                    </div>
                                    <div class="html-code">
                                        <div class="code-line"><span class="code-purple">&lt;html&gt;</span></div>
                                        <div class="code-line">  <span class="code-purple">&lt;head&gt;</span></div>
                                        <div class="code-line">    <span class="code-purple">&lt;title&gt;</span>AlgoCodeBF - Burkina Faso<span class="code-purple">&lt;/title&gt;</span></div>
                                        <div class="code-line">  <span class="code-purple">&lt;/head&gt;</span></div>
                                        <div class="code-line">  <span class="code-purple">&lt;body&gt;</span></div>
                                        <div class="code-line">    <span class="code-purple">&lt;header&gt;</span></div>
                                        <div class="code-line">      <span class="code-purple">&lt;h1&gt;</span>🇧🇫 AlgoCodeBF<span class="code-purple">&lt;/h1&gt;</span></div>
                                        <div class="code-line">    <span class="code-purple">&lt;/header&gt;</span></div>
                                        <div class="code-line">    <span class="code-purple">&lt;main&gt;</span></div>
                                        <div class="code-line">      <span class="code-purple">&lt;p&gt;</span>Communauté Tech du Burkina Faso<span class="code-purple">&lt;/p&gt;</span></div>
                                        <div class="code-line">    <span class="code-purple">&lt;/main&gt;</span></div>
                                        <div class="code-line">  <span class="code-purple">&lt;/body&gt;</span></div>
                                        <div class="code-line"><span class="code-purple">&lt;/html&gt;</span></div>
                                    </div>
                                    <div class="screen-shine"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Éléments flottants décoratifs -->
                        <div class="floating-icon icon-1">💻</div>
                        <div class="floating-icon icon-2">🚀</div>
                        <div class="floating-icon icon-3">⚡</div>
                        <div class="floating-icon icon-4">🌟</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Scène de l'écran large */
.monitor-scene {
    position: relative;
    width: 100%;
    height: 450px;
    perspective: 1400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.monitor {
    position: relative;
    width: 500px;
    height: 350px;
    transform-style: preserve-3d;
    animation: monitorFloat 6s ease-in-out infinite;
}

@keyframes monitorFloat {
    0%, 100% { transform: translateY(0) rotateY(-15deg) rotateX(5deg); }
    50% { transform: translateY(-20px) rotateY(-15deg) rotateX(5deg); }
}

/* Écran large */
.monitor-screen {
    position: relative;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
    border-radius: 15px;
    box-shadow: 
        0 -10px 40px rgba(0, 0, 0, 0.6),
        inset 0 0 0 12px #0a0a0a,
        inset 0 0 0 13px #1a1a1a;
    padding: 18px;
    transform-style: preserve-3d;
}

/* Contenu de l'écran */
.screen-content {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0f1419 0%, #1a1f2e 100%);
    border-radius: 6px;
    overflow: hidden;
    position: relative;
    box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.5);
}

/* En-tête de l'écran */
.screen-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 15px;
    background: rgba(0, 0, 0, 0.3);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.window-buttons {
    display: flex;
    gap: 6px;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.dot.red { background: linear-gradient(135deg, #ff5f57 0%, #ff3b30 100%); }
.dot.yellow { background: linear-gradient(135deg, #ffbd2e 0%, #ffa500 100%); }
.dot.green { background: linear-gradient(135deg, #28c840 0%, #20a030 100%); }

.url-bar {
    flex: 1;
    background: rgba(255, 255, 255, 0.05);
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.6);
    font-family: 'Courier New', monospace;
}

/* Code HTML */
.html-code {
    padding: 25px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.8;
}

.code-line {
    color: #e0e0e0;
    text-shadow: 0 0 8px rgba(100, 200, 255, 0.3);
    margin-bottom: 2px;
}

.code-purple { color: #c792ea; }
.code-blue { color: #82aaff; }
.code-orange { color: #f78c6c; }
.code-green { color: #c3e88d; }

/* Effet de brillance sur l'écran */
.screen-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, 
        transparent 0%, 
        rgba(255, 255, 255, 0.1) 50%, 
        transparent 100%);
    animation: shine 8s ease-in-out infinite;
}

@keyframes shine {
    0%, 100% { left: -100%; }
    50% { left: 100%; }
}

/* Icônes flottantes décoratives */
.floating-icon {
    position: absolute;
    font-size: 32px;
    animation: float 4s ease-in-out infinite;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

.icon-1 {
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.icon-2 {
    top: 20%;
    right: 15%;
    animation-delay: 1s;
}

.icon-3 {
    bottom: 20%;
    left: 15%;
    animation-delay: 2s;
}

.icon-4 {
    bottom: 15%;
    right: 10%;
    animation-delay: 3s;
}

@keyframes float {
    0%, 100% { 
        transform: translateY(0) rotate(0deg);
        opacity: 0.7;
    }
    50% { 
        transform: translateY(-20px) rotate(10deg);
        opacity: 1;
    }
}

/* Responsive Mobile */
@media (max-width: 768px) {
    .slide-content {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
        padding: 40px 20px !important;
    }
    
    .slide-left {
        order: 2;
    }
    
    .slide-right {
        order: 1;
    }
    
    .monitor-scene {
        height: 320px;
        perspective: 1000px;
    }
    
    .monitor {
        width: 350px;
        height: 240px;
        transform: scale(0.85);
    }
    
    .monitor-screen {
        padding: 15px;
    }
    
    .html-code {
        padding: 20px;
        font-size: 11px;
    }
    
    .floating-icon {
        font-size: 24px;
    }
    
    @keyframes monitorFloat {
        0%, 100% { transform: translateY(0) rotateY(-15deg) rotateX(8deg); }
        50% { transform: translateY(-18px) rotateY(-15deg) rotateX(8deg); }
    }
}

@media (max-width: 480px) {
    .monitor {
        transform: scale(0.72);
    }
    
    .monitor-scene {
        height: 280px;
    }
    
    .html-code {
        font-size: 9px;
        line-height: 1.6;
    }
    
    .floating-icon {
        font-size: 20px;
    }
}
</style>


<!-- Section Mission -->
<section class="content-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-bullseye"></i> Notre Mission</h2>
        </div>
        
        <div class="post-card" style="padding: 40px; text-align: center;">
            <p style="font-size: 18px; line-height: 1.8; margin-bottom: 20px;">
                AlgoCodeBF a été créé avec une vision claire : <strong>rassembler, valoriser et connecter tous les informaticiens burkinabè</strong>. 
                Nous croyons en la puissance de la communauté tech du Burkina Faso et son potentiel à transformer le pays.
            </p>
            <p style="font-size: 18px; line-height: 1.8; margin-bottom: 0;">
                Notre plateforme offre un espace où les étudiants, jeunes diplômés et professionnels de l'IT peuvent échanger, apprendre, collaborer et saisir des opportunités.
            </p>
        </div>
    </div>
</section>

<!-- Section Valeurs -->
<section class="content-section bg-light">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-heart"></i> Nos Valeurs</h2>
        </div>
        
        <div class="tutorials-grid">
            <div class="tutorial-card" style="text-align: center;">
                <div style="font-size: 60px; margin-bottom: 20px;">🤝</div>
                <h3 style="font-size: 22px; color: var(--dark-color); margin-bottom: 15px;">Collaboration</h3>
                <p>Nous favorisons l'entraide et le partage de connaissances entre tous les membres de la communauté.</p>
            </div>
            <div class="tutorial-card" style="text-align: center;">
                <div style="font-size: 60px; margin-bottom: 20px;">🎓</div>
                <h3 style="font-size: 22px; color: var(--dark-color); margin-bottom: 15px;">Apprentissage</h3>
                <p>Nous encourageons la formation continue et le partage d'expertise à travers tutoriels et ressources.</p>
            </div>
            <div class="tutorial-card" style="text-align: center;">
                <div style="font-size: 60px; margin-bottom: 20px;">🚀</div>
                <h3 style="font-size: 22px; color: var(--dark-color); margin-bottom: 15px;">Innovation</h3>
                <p>Nous soutenons les idées nouvelles et les projets innovants qui peuvent faire la différence.</p>
            </div>
            <div class="tutorial-card" style="text-align: center;">
                <div style="font-size: 60px; margin-bottom: 20px;">🌍</div>
                <h3 style="font-size: 22px; color: var(--dark-color); margin-bottom: 15px;">Inclusion</h3>
                <p>Nous accueillons tous les informaticiens du Burkina, quel que soit leur niveau ou domaine.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Fonctionnalités -->
<section class="content-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-tools"></i> Nos Fonctionnalités</h2>
        </div>
        
        <div class="posts-grid">
            <div class="post-card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-comments" style="font-size: 40px; background: var(--gradient-burkinabe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                </div>
                <h3 style="text-align: center; font-size: 20px; margin-bottom: 15px;">Forum de Discussion</h3>
                <p style="text-align: center;">Échangez sur tous les sujets tech : programmation, réseau, cybersécurité, IA, et plus encore.</p>
            </div>
            
            <div class="post-card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-book" style="font-size: 40px; background: var(--gradient-burkinabe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                </div>
                <h3 style="text-align: center; font-size: 20px; margin-bottom: 15px;">Espace Tutoriels</h3>
                <p style="text-align: center;">Partagez et consultez des tutoriels vidéo, articles, et ressources pédagogiques.</p>
            </div>
            
            <div class="post-card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-briefcase" style="font-size: 40px; background: var(--gradient-burkinabe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                </div>
                <h3 style="text-align: center; font-size: 20px; margin-bottom: 15px;">Opportunités</h3>
                <p style="text-align: center;">Découvrez des offres d'emploi, stages, hackathons et formations adaptées à vos compétences.</p>
            </div>
            
            <div class="post-card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-project-diagram" style="font-size: 40px; background: var(--gradient-burkinabe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                </div>
                <h3 style="text-align: center; font-size: 20px; margin-bottom: 15px;">Projets Collaboratifs</h3>
                <p style="text-align: center;">Créez ou rejoignez des projets tech avec d'autres membres de la communauté.</p>
            </div>
            
            <div class="post-card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-trophy" style="font-size: 40px; background: var(--gradient-burkinabe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                </div>
                <h3 style="text-align: center; font-size: 20px; margin-bottom: 15px;">Badges & Classements</h3>
                <p style="text-align: center;">Gagnez des badges et montez dans le classement grâce à vos contributions.</p>
            </div>
            
            <div class="post-card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-envelope" style="font-size: 40px; background: var(--gradient-burkinabe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                </div>
                <h3 style="text-align: center; font-size: 20px; margin-bottom: 15px;">Messagerie Privée</h3>
                <p style="text-align: center;">Communiquez directement avec les autres membres pour des échanges plus personnels.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Équipe -->
<section class="content-section bg-light">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-users"></i> L'Équipe</h2>
        </div>
        
        <div class="post-card" style="padding: 40px; text-align: center;">
            <p style="font-size: 18px; line-height: 1.8; margin-bottom: 30px;">
                AlgoCodeBF a été développé par des passionnés de technologie qui croient au potentiel du Burkina Faso dans le domaine du numérique.
            </p>
            <div style="background: var(--light-color); padding: 30px; border-radius: 12px; max-width: 600px; margin: 0 auto;">
                <p style="font-size: 16px; margin-bottom: 15px;"><strong>Créateur & Développeur :</strong> Mohamed SARE</p>
                <p style="font-size: 16px; margin-bottom: 15px;"><strong>Date de lancement :</strong> Octobre 2025</p>
                <p style="font-size: 16px; margin-bottom: 0;"><strong>Localisation :</strong> Ouagadougou, Burkina Faso 🇧🇫</p>
            </div>
        </div>
    </div>
</section>

<!-- Section CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Rejoignez-nous !</h2>
            <p>Faites partie de la communauté tech burkinabè et contribuez à l'essor du numérique au Burkina Faso</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/auth/register" class="btn btn-primary btn-lg">S'INSCRIRE GRATUITEMENT</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/forum/index" class="btn btn-primary btn-lg">EXPLORER LA PLATEFORME</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once VIEWS . '/layouts/footer.php'; ?>

