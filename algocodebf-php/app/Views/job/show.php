<?php
// Initialiser les variables avec des valeurs par défaut
$has_applied = $has_applied ?? false;
$csrf_token = $csrf_token ?? '';
$skills = $skills ?? [];

$pageTitle = htmlspecialchars($job['title'] ?? 'Offre') . ' - Opportunités - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="job-show-section">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-nav">
            <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= BASE_URL ?>/job/index">Opportunités</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= htmlspecialchars($job['title'] ?? 'Offre') ?></span>
        </div>

        <div class="job-show-layout">
            <!-- Main Content -->
            <div class="job-show-main">
                <!-- Header -->
                <div class="job-show-header">
                    <div class="job-header-top">
                        <span class="job-type-badge type-<?= htmlspecialchars($job['type'] ?? 'emploi') ?>">
                            <?= ucfirst(htmlspecialchars($job['type'] ?? 'emploi')) ?>
                        </span>
                        <?php if ($job['is_new'] ?? false): ?>
                            <span class="badge-new">Nouveau</span>
                        <?php endif; ?>
                    </div>

                    <h1 class="job-show-title"><?= htmlspecialchars($job['title'] ?? 'Offre sans titre') ?></h1>
                    
                    <div class="job-meta-header">
                        <div class="meta-item">
                            <i class="fas fa-building"></i>
                            <span><?= htmlspecialchars($job['company_name'] ?? 'Entreprise') ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($job['city'] ?? 'Non spécifié') ?></span>
                        </div>
                        <?php if (!empty($job['salary_range'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span><?= htmlspecialchars($job['salary_range']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <i class="fas fa-eye"></i>
                            <span><?= number_format($job['views'] ?? 0) ?> vues</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>Publié <?= timeAgo($job['created_at'] ?? '') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="job-description-section">
                    <h2><i class="fas fa-file-alt"></i> Description de l'offre</h2>
                    <div class="job-description-content">
                        <?= nl2br(htmlspecialchars($job['description'] ?? 'Aucune description disponible.')) ?>
                    </div>
                </div>

                <!-- Skills -->
                <?php if (!empty($skills)): ?>
                <div class="job-skills-section">
                    <h2><i class="fas fa-tools"></i> Compétences requises</h2>
                    <div class="skills-list">
                        <?php foreach ($skills as $skill): ?>
                            <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Contact Info -->
                <div class="job-contact-section">
                    <h2><i class="fas fa-envelope"></i> Informations de contact</h2>
                    <div class="contact-info">
                        <?php if (!empty($job['contact_email'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?= htmlspecialchars($job['contact_email']) ?>">
                                <?= htmlspecialchars($job['contact_email']) ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($job['contact_phone'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:<?= htmlspecialchars($job['contact_phone']) ?>">
                                <?= htmlspecialchars($job['contact_phone']) ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($job['external_link'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-external-link-alt"></i>
                            <a href="<?= htmlspecialchars($job['external_link']) ?>" target="_blank" rel="noopener">
                                Voir l'offre sur le site source
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Deadline -->
                <?php if (!empty($job['deadline'])): ?>
                <div class="job-deadline-section">
                    <div class="deadline-alert">
                        <i class="fas fa-calendar-times"></i>
                        <div>
                            <strong>Date limite de candidature :</strong>
                            <span><?= date('d/m/Y', strtotime($job['deadline'])) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="job-show-sidebar">
                <!-- Apply Button -->
                <?php if (!empty($job['external_link'])): ?>
                    <div class="apply-card">
                        <h3>Postuler à cette offre</h3>
                        <p class="apply-description">Pour postuler, visitez le site officiel de l'offre.</p>
                        <a href="<?= htmlspecialchars($job['external_link']) ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="btn-apply">
                            <i class="fas fa-external-link-alt"></i> Postuler sur le site officiel
                        </a>
                    </div>
                <?php else: ?>
                    <div class="apply-card">
                        <h3>Postuler à cette offre</h3>
                        <p class="apply-description">Lien de candidature non disponible.</p>
                    </div>
                <?php endif; ?>

                <!-- Company Info -->
                <div class="company-card">
                    <h3><i class="fas fa-building"></i> Entreprise</h3>
                    <div class="company-info">
                        <p class="company-name"><?= htmlspecialchars($job['company_name'] ?? 'Entreprise') ?></p>
                        <?php if (!empty($job['city'])): ?>
                        <p class="company-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($job['city']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Share -->
                <div class="share-card">
                    <h3><i class="fas fa-share-alt"></i> Partager cette offre</h3>
                    <div class="share-buttons">
                        <button onclick="shareOnFacebook()" class="btn-share facebook">
                            <i class="fab fa-facebook"></i> Facebook
                        </button>
                        <button onclick="shareOnTwitter()" class="btn-share twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </button>
                        <button onclick="copyLink()" class="btn-share link">
                            <i class="fas fa-link"></i> Copier le lien
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
.job-show-section {
    padding: 40px 0 80px;
    background: #f8f9fa;
    min-height: calc(100vh - 200px);
}

.breadcrumb-nav {
    margin-bottom: 30px;
    font-size: 0.9rem;
    color: #6c757d;
}

.breadcrumb-nav a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb-nav a:hover {
    text-decoration: underline;
}

.breadcrumb-nav i {
    margin: 0 8px;
    font-size: 0.8rem;
}

.job-show-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

.job-show-main {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.job-show-header {
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 25px;
    margin-bottom: 30px;
}

.job-header-top {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    align-items: center;
}

.job-type-badge {
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.type-stage { background: #e8f5e9; color: #2e7d32; }
.type-emploi { background: #e3f2fd; color: #1976d2; }
.type-freelance { background: #f3e5f5; color: #7b1fa2; }
.type-hackathon { background: #fff3e0; color: #e65100; }
.type-formation { background: #ffebee; color: #c62828; }

.badge-new {
    padding: 6px 15px;
    background: #ff5252;
    color: white;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.job-show-title {
    font-size: 2rem;
    margin: 15px 0;
    color: var(--dark-color);
    line-height: 1.3;
}

.job-meta-header {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 20px;
    color: #6c757d;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
}

.meta-item i {
    color: var(--primary-color);
}

.job-description-section,
.job-skills-section,
.job-contact-section,
.job-deadline-section {
    margin-bottom: 40px;
}

.job-description-section h2,
.job-skills-section h2,
.job-contact-section h2 {
    font-size: 1.3rem;
    margin-bottom: 20px;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.job-description-content {
    line-height: 1.8;
    color: #444;
    font-size: 1rem;
}

.skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.skill-tag {
    padding: 10px 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 500;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.contact-item i {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.contact-item a {
    color: var(--dark-color);
    text-decoration: none;
}

.contact-item a:hover {
    color: var(--primary-color);
    text-decoration: underline;
}

.deadline-alert {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 8px;
}

.deadline-alert i {
    font-size: 1.5rem;
    color: #ffc107;
}

.deadline-alert strong {
    display: block;
    margin-bottom: 5px;
    color: var(--dark-color);
}

.job-show-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.apply-card,
.applied-card,
.login-prompt-card,
.company-card,
.share-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.apply-card h3,
.company-card h3,
.share-card h3 {
    margin: 0 0 20px;
    font-size: 1.1rem;
    color: var(--dark-color);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark-color);
}

.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-family: inherit;
    resize: vertical;
}

.btn-apply {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
}

.btn-apply:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    color: white;
    text-decoration: none;
}

.apply-description {
    color: #6c757d;
    margin-bottom: 20px;
    font-size: 0.95rem;
    line-height: 1.6;
}

.applied-card,
.login-prompt-card {
    text-align: center;
}

.applied-card i {
    font-size: 3rem;
    color: #28a745;
    margin-bottom: 15px;
}

.login-prompt-card i {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.btn-login {
    display: inline-block;
    padding: 12px 30px;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    margin-top: 15px;
    transition: all 0.3s ease;
}

.btn-login:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
}

.company-name {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--dark-color);
}

.company-location {
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 8px;
}

.share-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-share {
    padding: 12px 20px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.btn-share:hover {
    border-color: var(--primary-color);
    transform: translateX(5px);
}

.btn-share.facebook:hover { border-color: #1877f2; color: #1877f2; }
.btn-share.twitter:hover { border-color: #1da1f2; color: #1da1f2; }
.btn-share.link:hover { border-color: var(--primary-color); color: var(--primary-color); }

@media (max-width: 992px) {
    .job-show-layout {
        grid-template-columns: 1fr;
    }
    
    .job-show-sidebar {
        order: -1;
    }
}

@media (max-width: 768px) {
    .job-show-title {
        font-size: 1.5rem;
    }
    
    .job-meta-header {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<script>
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent('<?= addslashes($job['title']) ?>');
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=600,height=400');
}

function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Lien copié dans le presse-papiers !');
    }).catch(() => {
        // Fallback pour les navigateurs plus anciens
        const textarea = document.createElement('textarea');
        textarea.value = url;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Lien copié dans le presse-papiers !');
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

