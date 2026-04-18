<?php
$pageTitle = 'Opportunités - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Hero Section -->
<section class="jobs-hero">
    <div class="container">
        <div class="hero-content">
            <h1><i class="fas fa-briefcase"></i> Opportunités & Emplois</h1>
            <p>Découvrez stages, emplois, hackathons et formations au Burkina Faso</p>
            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'company'): ?>
                <a href="<?= BASE_URL ?>/job/create" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Publier une Offre
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Search & Filter Bar -->
<section class="jobs-search-section">
    <div class="container">
        <div class="search-filter-card">
            <form method="GET" class="jobs-search-form">
                <div class="search-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           name="q" 
                           placeholder="Rechercher une opportunité..." 
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                
                <div class="filter-group">
                    <i class="fas fa-map-marker-alt"></i>
                    <select name="city">
                        <option value="">Toutes les villes</option>
                        <option value="Ouagadougou" <?= ($current_city ?? '') === 'Ouagadougou' ? 'selected' : '' ?>>Ouagadougou</option>
                        <option value="Bobo-Dioulasso" <?= ($current_city ?? '') === 'Bobo-Dioulasso' ? 'selected' : '' ?>>Bobo-Dioulasso</option>
                        <option value="Koudougou" <?= ($current_city ?? '') === 'Koudougou' ? 'selected' : '' ?>>Koudougou</option>
                        <option value="Ouahigouya" <?= ($current_city ?? '') === 'Ouahigouya' ? 'selected' : '' ?>>Ouahigouya</option>
                        <option value="Autre" <?= ($current_city ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <i class="fas fa-briefcase"></i>
                    <select name="type">
                        <option value="">Tous les types</option>
                        <option value="stage" <?= ($current_type ?? '') === 'stage' ? 'selected' : '' ?>>Stage</option>
                        <option value="emploi" <?= ($current_type ?? '') === 'emploi' ? 'selected' : '' ?>>Emploi</option>
                        <option value="freelance" <?= ($current_type ?? '') === 'freelance' ? 'selected' : '' ?>>Freelance</option>
                        <option value="hackathon" <?= ($current_type ?? '') === 'hackathon' ? 'selected' : '' ?>>Hackathon</option>
                        <option value="formation" <?= ($current_type ?? '') === 'formation' ? 'selected' : '' ?>>Formation</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="jobs-stats">
    <div class="container">
        <div class="stats-row-4">
            <div class="stat-box-job">
                <i class="fas fa-briefcase"></i>
                <h3><?= $stats['total_jobs'] ?? 0 ?></h3>
                <p>Offres Disponibles</p>
            </div>
            <div class="stat-box-job">
                <i class="fas fa-building"></i>
                <h3><?= $stats['companies'] ?? 0 ?></h3>
                <p>Entreprises</p>
            </div>
            <div class="stat-box-job">
                <i class="fas fa-user-check"></i>
                <h3><?= $stats['hired'] ?? 0 ?></h3>
                <p>Candidats Recrutés</p>
            </div>
            <div class="stat-box-job">
                <i class="fas fa-fire"></i>
                <h3><?= $stats['new_this_week'] ?? 0 ?></h3>
                <p>Cette Semaine</p>
            </div>
        </div>
    </div>
</section>

<!-- Jobs Content -->
<section class="jobs-content">
    <div class="container">
        <!-- Category Tabs -->
        <div class="category-tabs">
            <button class="tab-btn active" data-type="all">
                <i class="fas fa-globe"></i> Toutes
            </button>
            <button class="tab-btn" data-type="stage">
                <i class="fas fa-user-graduate"></i> Stages
            </button>
            <button class="tab-btn" data-type="emploi">
                <i class="fas fa-briefcase"></i> Emplois
            </button>
            <button class="tab-btn" data-type="freelance">
                <i class="fas fa-laptop-code"></i> Freelance
            </button>
            <button class="tab-btn" data-type="hackathon">
                <i class="fas fa-trophy"></i> Hackathons
            </button>
            <button class="tab-btn" data-type="formation">
                <i class="fas fa-chalkboard-teacher"></i> Formations
            </button>
        </div>

        <!-- Jobs List -->
        <div class="jobs-layout">
            <!-- Main Jobs List -->
            <div class="jobs-list">
                <?php if (empty($jobs)): ?>
                    <div class="empty-jobs">
                        <i class="fas fa-briefcase"></i>
                        <h3>Aucune opportunité disponible</h3>
                        <p>Revenez bientôt pour découvrir de nouvelles offres !</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-card" data-type="<?= htmlspecialchars($job['type']) ?>">
                            <div class="job-company-logo">
                                <?php if (!empty($job['company_logo'])): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($job['company_logo']) ?>" 
                                         alt="<?= htmlspecialchars($job['company_name'] ?? 'Entreprise') ?>">
                                <?php else: ?>
                                    <div class="logo-placeholder">
                                        <?= strtoupper(substr($job['company_name'] ?? 'EN', 0, 2)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="job-info">
                                <div class="job-header">
                                    <h3 class="job-title">
                                        <a href="<?= BASE_URL ?>/job/show/<?= $job['id'] ?>">
                                            <?= htmlspecialchars($job['title']) ?>
                                        </a>
                                    </h3>
                                    <span class="job-type type-<?= htmlspecialchars($job['type']) ?>">
                                        <?= ucfirst(htmlspecialchars($job['type'])) ?>
                                    </span>
                                </div>

                                <div class="job-company">
                                    <i class="fas fa-building"></i>
                                    <?= htmlspecialchars($job['company_name'] ?? 'Entreprise') ?>
                                </div>

                                <p class="job-description">
                                    <?= htmlspecialchars(substr($job['description'], 0, 150)) ?>...
                                </p>

                                <div class="job-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($job['city']) ?>
                                    </span>
                                    <?php if (!empty($job['salary'])): ?>
                                        <span class="meta-item">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <?= htmlspecialchars($job['salary']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <?= timeAgo($job['created_at']) ?>
                                    </span>
                                </div>

                                <div class="job-skills">
                                    <?php 
                                    $skills = [];
                                    if (!empty($job['skills_required'])) {
                                        // Les compétences peuvent être en JSON ou en chaîne séparée par virgules
                                        $decoded = json_decode($job['skills_required'], true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                            $skills = $decoded;
                                        } else {
                                            $skills = array_filter(array_map('trim', explode(',', $job['skills_required'])));
                                        }
                                    }
                                    if (!empty($skills)): 
                                        foreach (array_slice($skills, 0, 3) as $skill): 
                                    ?>
                                        <span class="skill-badge"><?= htmlspecialchars($skill) ?></span>
                                    <?php 
                                        endforeach; 
                                    endif; 
                                    ?>
                                </div>
                            </div>

                            <div class="job-actions">
                                <a href="<?= BASE_URL ?>/job/show/<?= $job['id'] ?>" class="btn-view-job">
                                    Voir l'offre
                                </a>
                                <?php if ($job['is_new'] ?? false): ?>
                                    <span class="badge-new">Nouveau</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if (isset($total_pages) && $total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <nav class="pagination">
                        <?php
                        $currentPage = $page ?? 1;
                        $totalPages = $total_pages ?? 1;
                        $queryParams = $_GET;
                        
                        // Fonction pour générer l'URL de pagination
                        $getPageUrl = function($pageNum) use ($queryParams) {
                            $queryParams['page'] = $pageNum;
                            return BASE_URL . '/job/index?' . http_build_query($queryParams);
                        };
                        
                        // Bouton Précédent
                        if ($currentPage > 1):
                        ?>
                            <a href="<?= $getPageUrl($currentPage - 1) ?>" class="pagination-btn prev">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        <?php endif; ?>
                        
                        <!-- Numéros de page -->
                        <div class="pagination-numbers">
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            
                            // Première page
                            if ($startPage > 1):
                            ?>
                                <a href="<?= $getPageUrl(1) ?>" class="pagination-number <?= $currentPage == 1 ? 'active' : '' ?>">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="pagination-dots">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="<?= $getPageUrl($i) ?>" class="pagination-number <?= $currentPage == $i ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php
                            // Dernière page
                            if ($endPage < $totalPages):
                            ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="pagination-dots">...</span>
                                <?php endif; ?>
                                <a href="<?= $getPageUrl($totalPages) ?>" class="pagination-number <?= $currentPage == $totalPages ? 'active' : '' ?>">
                                    <?= $totalPages ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Bouton Suivant -->
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= $getPageUrl($currentPage + 1) ?>" class="pagination-btn next">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                    
                    <!-- Info pagination -->
                    <div class="pagination-info">
                        <p>
                            Affichage de <strong><?= min(($currentPage - 1) * ($jobs_per_page ?? 12) + 1, $total_jobs ?? 0) ?></strong> 
                            à <strong><?= min($currentPage * ($jobs_per_page ?? 12), $total_jobs ?? 0) ?></strong> 
                            sur <strong><?= $total_jobs ?? 0 ?></strong> offres
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="jobs-sidebar">
                <!-- Featured Companies -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-star"></i> Entreprises Partenaires</h3>
                    <div class="featured-companies">
                        <?php if (!empty($featured_companies)): ?>
                            <?php foreach ($featured_companies as $company): ?>
                                <div class="company-item">
                                    <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($company['logo']) ?>" 
                                         alt="<?= htmlspecialchars($company['name']) ?>">
                                    <div class="company-info">
                                        <h4><?= htmlspecialchars($company['name']) ?></h4>
                                        <p><?= $company['jobs_count'] ?> offres</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="sidebar-widget tips-widget">
                    <h3><i class="fas fa-lightbulb"></i> Conseils</h3>
                    <ul class="tips-list">
                        <li><i class="fas fa-check"></i> Complétez votre profil</li>
                        <li><i class="fas fa-check"></i> Ajoutez votre CV</li>
                        <li><i class="fas fa-check"></i> Soyez réactif</li>
                        <li><i class="fas fa-check"></i> Personnalisez vos candidatures</li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="sidebar-widget newsletter-widget">
                    <h3><i class="fas fa-envelope"></i> Alertes Email</h3>
                    <p>Recevez les nouvelles offres</p>
                    <form class="newsletter-form" onsubmit="subscribeNewsletter(event)">
                        <input type="email" placeholder="Votre email" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-bell"></i> S'abonner
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
.jobs-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.jobs-hero::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
}

.hero-content {
    position: relative;
    z-index: 1;
}

.hero-content h1 {
    font-size: 3rem;
    margin-bottom: 20px;
    font-weight: 700;
}

.jobs-search-section {
    padding: 40px 0;
    background: #f8f9fa;
    margin-top: -30px;
    position: relative;
    z-index: 10;
}

.search-filter-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.jobs-search-form {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 15px;
    align-items: center;
}

.search-input-group {
    position: relative;
}

.search-input-group i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-input-group input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
}

.filter-group {
    position: relative;
}

.filter-group i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
}

.filter-group select {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    appearance: none;
    background: white url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%236c757d"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
    background-size: 20px;
}

.jobs-stats {
    padding: 60px 0;
    background: white;
}

.stats-row-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.stat-box-job {
    text-align: center;
    padding: 30px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    border-radius: 15px;
    transition: all 0.3s ease;
}

.stat-box-job:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.stat-box-job i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.stat-box-job h3 {
    font-size: 2.5rem;
    margin: 0;
    color: var(--dark-color);
}

.stat-box-job p {
    margin: 10px 0 0;
    color: #6c757d;
}

.jobs-content {
    padding: 60px 0 80px;
    background: #f8f9fa;
}

.category-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 40px;
    flex-wrap: wrap;
    justify-content: center;
}

.tab-btn {
    padding: 12px 25px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 25px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.tab-btn.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

.jobs-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

.jobs-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.job-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    gap: 20px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.job-card:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    border-left-color: var(--primary-color);
}

.job-company-logo {
    flex-shrink: 0;
}

.job-company-logo img,
.logo-placeholder {
    width: 70px;
    height: 70px;
    border-radius: 12px;
    object-fit: cover;
}

.logo-placeholder {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.job-info {
    flex: 1;
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
    gap: 15px;
}

.job-title {
    margin: 0;
    font-size: 1.3rem;
}

.job-title a {
    color: var(--dark-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.job-title a:hover {
    color: var(--primary-color);
}

.job-type {
    padding: 5px 15px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
}

.type-stage { background: #e8f5e9; color: #2e7d32; }
.type-emploi { background: #e3f2fd; color: #1976d2; }
.type-freelance { background: #f3e5f5; color: #7b1fa2; }
.type-hackathon { background: #fff3e0; color: #e65100; }
.type-formation { background: #ffebee; color: #c62828; }

.job-company {
    color: #6c757d;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.job-description {
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 15px;
}

.job-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    flex-wrap: wrap;
    font-size: 0.9rem;
    color: #6c757d;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.job-skills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.skill-badge {
    padding: 5px 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.job-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}

.btn-view-job {
    padding: 10px 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-view-job:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.badge-new {
    padding: 5px 12px;
    background: #ff5252;
    color: white;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.jobs-sidebar {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.sidebar-widget {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.sidebar-widget h3 {
    margin: 0 0 20px;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.featured-companies {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.company-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.company-item:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(5px);
}

.company-item img {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
}

.company-info h4 {
    margin: 0;
    font-size: 0.95rem;
}

.company-info p {
    margin: 5px 0 0;
    font-size: 0.8rem;
    opacity: 0.8;
}

.tips-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tips-list li {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tips-list li:last-child {
    border-bottom: none;
}

.tips-list i {
    color: var(--primary-color);
}

.newsletter-widget p {
    color: #6c757d;
    margin-bottom: 15px;
}

.newsletter-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.newsletter-form input {
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
}

.empty-jobs {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
}

.empty-jobs i {
    font-size: 5rem;
    color: #e9ecef;
    margin-bottom: 20px;
}

@media (max-width: 992px) {
    .jobs-layout {
        grid-template-columns: 1fr;
    }
    
    .jobs-search-form {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .stats-row-4 {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .stat-box-job {
        padding: 20px 16px;
    }
    
    .stat-box-job i {
        font-size: 2rem;
        margin-bottom: 10px;
    }
    
    .stat-box-job h3 {
        font-size: 1.8rem;
    }
    
    .stat-box-job p {
        font-size: 0.85rem;
    }
    
    .job-card {
        flex-direction: column;
    }
}

/* Pagination */
.pagination-wrapper {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #e9ecef;
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.pagination-btn {
    padding: 10px 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    color: var(--dark-color);
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.pagination-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    transform: translateY(-2px);
    text-decoration: none;
}

.pagination-numbers {
    display: flex;
    align-items: center;
    gap: 5px;
}

.pagination-number {
    min-width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    color: var(--dark-color);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination-number:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    transform: translateY(-2px);
    text-decoration: none;
}

.pagination-number.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

.pagination-dots {
    padding: 0 10px;
    color: #6c757d;
    font-weight: 600;
}

.pagination-info {
    text-align: center;
    color: #6c757d;
    font-size: 0.9rem;
}

.pagination-info strong {
    color: var(--dark-color);
}

@media (max-width: 768px) {
    .pagination {
        gap: 5px;
    }
    
    .pagination-btn {
        padding: 8px 15px;
        font-size: 0.9rem;
    }
    
    .pagination-number {
        min-width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
}
</style>

<script>
// Tab filtering avec synchronisation URL
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const type = this.dataset.type;
        
        // Mettre à jour l'URL avec le type sélectionné
        const url = new URL(window.location);
        if (type === 'all') {
            url.searchParams.delete('type');
        } else {
            url.searchParams.set('type', type);
        }
        url.searchParams.delete('page'); // Réinitialiser la pagination
        
        // Rediriger vers la nouvelle URL
        window.location.href = url.toString();
    });
});

// Activer l'onglet correspondant au type actuel
document.addEventListener('DOMContentLoaded', function() {
    const currentType = '<?= $current_type ?? '' ?>';
    const allTabs = document.querySelectorAll('.tab-btn');
    
    allTabs.forEach(tab => {
        tab.classList.remove('active');
        if (currentType === '' && tab.dataset.type === 'all') {
            tab.classList.add('active');
        } else if (tab.dataset.type === currentType) {
            tab.classList.add('active');
        }
    });
    
    // Si aucun type n'est sélectionné, activer "Toutes"
    if (!currentType) {
        const allTab = document.querySelector('.tab-btn[data-type="all"]');
        if (allTab) allTab.classList.add('active');
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php';
?>

