<?php
$pageTitle = 'Bienvenue sur AlgoCodeBF - HubTech';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- CSS spécifique pour la landing page -->
<style>
.landing-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 100px 0 80px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.landing-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="30" cy="90" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    opacity: 0.3;
    pointer-events: none;
}

.landing-hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.landing-hero h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    line-height: 1.2;
}

.landing-hero .subtitle {
    font-size: 1.4rem;
    margin-bottom: 30px;
    opacity: 0.95;
    font-weight: 300;
}

.landing-hero .description {
    font-size: 1.1rem;
    margin-bottom: 40px;
    opacity: 0.9;
    line-height: 1.6;
}

.landing-cta {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 50px;
}

.landing-btn {
    padding: 15px 30px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    min-width: 200px;
    justify-content: center;
    border: none;
    cursor: pointer;
}

.landing-btn-primary {
    background: white;
    color: var(--primary-color);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.landing-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    color: var(--primary-color);
}

.landing-btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.landing-btn-secondary:hover {
    background: white;
    color: var(--primary-color);
    transform: translateY(-2px);
}

.features-section {
    padding: 80px 0;
    background: white;
}

.features-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    margin-top: 60px;
}

.feature-card {
    text-align: center;
    padding: 40px 20px;
    border-radius: 15px;
    background: #f8f9fa;
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2rem;
    color: white;
}

.feature-card h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: var(--dark-color);
}

.feature-card p {
    color: #666;
    line-height: 1.6;
}

.stats-section {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
    color: white;
    padding: 60px 0;
}

.stats-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    text-align: center;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
    margin-top: 40px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 10px;
    display: block;
}

.stat-label {
    font-size: 1.1rem;
    opacity: 0.9;
}

.why-join {
    padding: 80px 0;
    background: #f8f9fa;
}

.why-join-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
    text-align: center;
}

.why-join h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: var(--dark-color);
}

.why-join p {
    font-size: 1.2rem;
    color: #666;
    line-height: 1.6;
    margin-bottom: 40px;
}

.benefits-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.benefit-item {
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.benefit-item h4 {
    color: var(--primary-color);
    margin-bottom: 10px;
    font-size: 1.2rem;
}

.benefit-item p {
    color: #666;
    font-size: 0.95rem;
    margin: 0;
}

.final-cta {
    background: var(--dark-color);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.final-cta-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 0 20px;
}

.final-cta h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.final-cta p {
    font-size: 1.2rem;
    margin-bottom: 40px;
    opacity: 0.9;
}

.final-cta .landing-cta {
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .landing-hero {
        padding: 60px 0 40px;
    }

    .landing-hero h1 {
        font-size: 2.5rem;
    }

    .landing-hero .subtitle {
        font-size: 1.2rem;
    }

    .landing-cta {
        flex-direction: column;
        align-items: center;
    }

    .landing-btn {
        width: 100%;
        max-width: 300px;
    }

    .features-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
    }

    .stat-number {
        font-size: 2.5rem;
    }
}
</style>

<!-- Section Hero -->
<section class="landing-hero">
    <div class="landing-hero-content">
        <h1>Bienvenue sur AlgoCodeBF</h1>
        <p class="subtitle">La plateforme des étudiants informaticiens et professionnels du Burkina Faso</p>
        <p class="description">
            Rejoignez la communauté des étudiants informaticiens et professionnels du Burkina Faso.
            Partagez vos connaissances, collaborez sur des projets et développez votre réseau professionnel.
        </p>
        <div class="landing-cta">
            <a href="<?= BASE_URL ?>/auth/register" class="landing-btn landing-btn-primary">
                <i class="fas fa-user-plus"></i>
                Créer mon compte
            </a>
            <a href="<?= BASE_URL ?>/auth/login" class="landing-btn landing-btn-secondary">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </a>
        </div>
    </div>
</section>

<!-- Section Fonctionnalités -->
<section class="features-section">
    <div class="features-container">
        <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 20px; color: var(--dark-color);">
            Ce qui vous attend sur AlgoCodeBF
        </h2>
        <p style="text-align: center; font-size: 1.2rem; color: #666; margin-bottom: 0; font-family: 'verdana';">
            Une plateforme complète pour acquerir des compétences réelles en informatique !
        </p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Forum Communautaire</h3>
                <p>Discutez, posez des questions et partagez vos connaissances avec la communauté tech du Burkina Faso.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>Tutoriels & Formation</h3>
                <p>Apprenez et enseignez à travers des tutoriels détaillés et des ressources pédagogiques de qualité.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-code-branch"></i>
                </div>
                <h3>Projets Collaboratifs</h3>
                <p>Collaborez sur des projets innovants et développez votre portfolio avec d'autres développeurs.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Opportunités d'Emploi</h3>
                <p>Découvrez les dernières offres d'emploi et de stage dans le secteur tech au Burkina Faso.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Réseau Professionnel</h3>
                <p>Connectez-vous avec des professionnels, étudiants et entreprises du secteur technologique.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>Système de Réputation</h3>
                <p>Gagnez des points et des badges en contribuant activement à la communauté.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Statistiques -->
<section class="stats-section">
    <div class="stats-container">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;">Notre Communauté en Chiffres</h2>
        <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 0;">
            Rejoignez des milliers de développeurs passionnés
        </p>

        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number"><?= $stats['total_users'] ?? '0' ?>+</span>
                <span class="stat-label">Membres Actifs</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= $stats['total_posts'] ?? '0' ?>+</span>
                <span class="stat-label">Discussions</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= $stats['total_tutorials'] ?? '0' ?>+</span>
                <span class="stat-label">Tutoriels</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= $stats['total_projects'] ?? '0' ?>+</span>
                <span class="stat-label">Projets</span>
            </div>
        </div>
    </div>
</section>

<!-- Section Pourquoi nous rejoindre -->
<section class="why-join">
    <div class="why-join-container">
        <h2>Pourquoi rejoindre AlgoCodeBF ?</h2>
        <p>
            AlgoCodeBF n'est pas qu'une simple plateforme, c'est un écosystème complet
            dédié au développement de la communauté tech burkinabè.
        </p>

        <div class="benefits-list">
            <div class="benefit-item">
                <h4>🇧🇫 100% Burkinabè</h4>
                <p>Une plateforme créée par et pour les développeurs du Burkina Faso</p>
            </div>

            <div class="benefit-item">
                <h4>🚀 Développement Personnel</h4>
                <p>Améliorez vos compétences techniques et votre réseau professionnel</p>
            </div>

            <div class="benefit-item">
                <h4>💡 Innovation & Créativité</h4>
                <p>Participez à des projets innovants qui transforment le Burkina Faso</p>
            </div>

            <div class="benefit-item">
                <h4>🎓 Formation Continue</h4>
                <p>Accédez à du contenu éducatif de qualité et partagez vos connaissances</p>
            </div>

            <div class="benefit-item">
                <h4>🤝 Collaboration</h4>
                <p>Travaillez en équipe sur des projets qui ont du sens</p>
            </div>

            <div class="benefit-item">
                <h4>💼 Opportunités</h4>
                <p>Découvrez des emplois et stages dans le secteur tech local</p>
            </div>
        </div>
    </div>
</section>

<!-- Section CTA Final -->
<section class="final-cta">
    <div class="final-cta-container">
        <h2>Prêt à rejoindre la communauté ?</h2>
        <p>
            Créez votre compte gratuitement et commencez votre aventure dans l'écosystème tech du Burkina Faso !
        </p>
        <div class="landing-cta">
            <a href="<?= BASE_URL ?>/auth/register" class="landing-btn landing-btn-primary">
                <i class="fas fa-rocket"></i>
                Commencer maintenant
            </a>
            <a href="<?= BASE_URL ?>/auth/login" class="landing-btn landing-btn-secondary">
                <i class="fas fa-sign-in-alt"></i>
                J'ai déjà un compte
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>