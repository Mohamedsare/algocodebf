<?php
$pageTitle = 'Recherche - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

$query = $_GET['q'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$total_results = $total_results ?? 0;
$results_count = $results_count ?? ['users' => 0, 'forum' => 0, 'tutorials' => 0, 'projects' => 0, 'jobs' => 0];
$results = $results ?? [];
?>

<!-- Search Hero -->
<section class="search-hero">
    <div class="container">
        <h1><i class="fas fa-search"></i> Recherche</h1>
        <div class="search-form-container">
            <form method="GET" action="<?= BASE_URL ?>/home/search" class="search-form-main">
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           name="q" 
                           placeholder="Rechercher sur AlgoCodeBF..." 
                           value="<?= htmlspecialchars($query) ?>"
                           autofocus>
                    <button type="submit" class="btn-search-submit">
                        Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Search Filters & Results -->
<section class="search-results-section">
    <div class="container">
        <?php if (!empty($query)): ?>
            <div class="search-info">
                <h2>Résultats pour "<strong><?= htmlspecialchars($query) ?></strong>"</h2>
                <p><?= $total_results ?? 0 ?> résultat<?= ($total_results ?? 0) > 1 ? 's' : '' ?> trouvé<?= ($total_results ?? 0) > 1 ? 's' : '' ?></p>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="?q=<?= urlencode($query) ?>&filter=all" 
                   class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">
                    <i class="fas fa-globe"></i> Tout (<?= $total_results ?? 0 ?>)
                </a>
                <a href="?q=<?= urlencode($query) ?>&filter=users" 
                   class="filter-tab <?= $filter === 'users' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Membres (<?= $results_count['users'] ?? 0 ?>)
                </a>
                <a href="?q=<?= urlencode($query) ?>&filter=posts" 
                   class="filter-tab <?= $filter === 'posts' ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i> Forum (<?= $results_count['posts'] ?? 0 ?>)
                </a>
                <a href="?q=<?= urlencode($query) ?>&filter=tutorials" 
                   class="filter-tab <?= $filter === 'tutorials' ? 'active' : '' ?>">
                    <i class="fas fa-book"></i> Tutoriels (<?= $results_count['tutorials'] ?? 0 ?>)
                </a>
                <a href="?q=<?= urlencode($query) ?>&filter=projects" 
                   class="filter-tab <?= $filter === 'projects' ? 'active' : '' ?>">
                    <i class="fas fa-project-diagram"></i> Projets (<?= $results_count['projects'] ?? 0 ?>)
                </a>
                <a href="?q=<?= urlencode($query) ?>&filter=jobs" 
                   class="filter-tab <?= $filter === 'jobs' ? 'active' : '' ?>">
                    <i class="fas fa-briefcase"></i> Opportunités (<?= $results_count['jobs'] ?? 0 ?>)
                </a>
            </div>

            <!-- Results Container -->
            <div class="results-container">
                <?php if (empty($results) || $total_results == 0): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>Aucun résultat trouvé</h3>
                        <p>Essayez avec d'autres mots-clés ou vérifiez l'orthographe</p>
                        <div class="search-suggestions">
                            <h4>Suggestions :</h4>
                            <ul>
                                <li>Utilisez des mots-clés plus généraux</li>
                                <li>Vérifiez l'orthographe des mots</li>
                                <li>Essayez des synonymes</li>
                                <li>Utilisez moins de mots-clés</li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Users Results -->
                    <?php if (($filter === 'all' || $filter === 'users') && !empty($results['users'])): ?>
                        <div class="results-section">
                            <h3 class="section-title">
                                <i class="fas fa-users"></i> Membres
                            </h3>
                            <div class="results-grid">
                                <?php foreach ($results['users'] as $user): ?>
                                    <div class="result-card user-result">
                                        <div class="result-avatar">
                                            <?php if (!empty($user['photo_path'])): ?>
                                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['photo_path']) ?>" 
                                                     alt="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>">
                                            <?php else: ?>
                                                <div class="avatar-placeholder">
                                                    <?= strtoupper(substr($user['prenom'] ?? 'U', 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="result-content">
                                            <h4>
                                                <a href="<?= BASE_URL ?>/user/profile/<?= $user['id'] ?>">
                                                    <?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? 'Utilisateur')) ?>
                                                </a>
                                            </h4>
                                            <?php if (!empty($user['university']) || !empty($user['city'])): ?>
                                                <p class="result-meta">
                                                    <?php if (!empty($user['university'])): ?>
                                                        <i class="fas fa-university"></i> <?= htmlspecialchars($user['university']) ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($user['city'])): ?>
                                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($user['city']) ?>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if (!empty($user['bio'])): ?>
                                                <p class="result-excerpt"><?= htmlspecialchars(substr($user['bio'], 0, 100)) ?>...</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Forum Results -->
                    <?php if (($filter === 'all' || $filter === 'forum' || $filter === 'posts') && !empty($results['posts'])): ?>
                        <div class="results-section">
                            <h3 class="section-title">
                                <i class="fas fa-comments"></i> Discussions
                            </h3>
                            <div class="results-list">
                                <?php foreach ($results['posts'] as $post): ?>
                                    <div class="result-item">
                                        <div class="result-icon">
                                            <i class="fas fa-comment"></i>
                                        </div>
                                        <div class="result-content">
                                            <h4>
                                                <a href="<?= BASE_URL ?>/forum/show/<?= $post['id'] ?>">
                                                    <?= htmlspecialchars($post['title']) ?>
                                                </a>
                                            </h4>
                                            <p><?= htmlspecialchars(substr($post['body'] ?? '', 0, 150)) ?>...</p>
                                            <div class="result-meta">
                                                <span><i class="fas fa-user"></i> <?= htmlspecialchars(($post['prenom'] ?? '') . ' ' . ($post['nom'] ?? '')) ?></span>
                                                <span><i class="fas fa-clock"></i> <?= timeAgo($post['created_at'] ?? date('Y-m-d H:i:s')) ?></span>
                                                <span><i class="fas fa-comments"></i> <?= $post['comments_count'] ?? 0 ?> réponses</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tutorials Results -->
                    <?php if (($filter === 'all' || $filter === 'tutorials') && !empty($results['tutorials'])): ?>
                        <div class="results-section">
                            <h3 class="section-title">
                                <i class="fas fa-book"></i> Tutoriels
                            </h3>
                            <div class="results-list">
                                <?php foreach ($results['tutorials'] as $tutorial): ?>
                                    <div class="result-item">
                                        <div class="result-icon">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <div class="result-content">
                                            <h4>
                                                <a href="<?= BASE_URL ?>/tutorial/show/<?= $tutorial['id'] ?>">
                                                    <?= htmlspecialchars($tutorial['title']) ?>
                                                </a>
                                            </h4>
                                            <p><?= htmlspecialchars(substr($tutorial['description'] ?? '', 0, 150)) ?>...</p>
                                            <div class="result-meta">
                                                <span><i class="fas fa-user"></i> <?= htmlspecialchars(($tutorial['prenom'] ?? '') . ' ' . ($tutorial['nom'] ?? '')) ?></span>
                                                <span><i class="fas fa-heart"></i> <?= $tutorial['likes_count'] ?? 0 ?> likes</span>
                                                <span><i class="fas fa-eye"></i> <?= $tutorial['views'] ?? 0 ?> vues</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Projects Results -->
                    <?php if (($filter === 'all' || $filter === 'projects') && !empty($results['projects'])): ?>
                        <div class="results-section">
                            <h3 class="section-title">
                                <i class="fas fa-project-diagram"></i> Projets
                            </h3>
                            <div class="results-list">
                                <?php foreach ($results['projects'] as $project): ?>
                                    <div class="result-item">
                                        <div class="result-icon">
                                            <i class="fas fa-folder-open"></i>
                                        </div>
                                        <div class="result-content">
                                            <h4>
                                                <a href="<?= BASE_URL ?>/project/show/<?= $project['id'] ?>">
                                                    <?= htmlspecialchars($project['title']) ?>
                                                </a>
                                            </h4>
                                            <p><?= htmlspecialchars(substr($project['description'] ?? '', 0, 150)) ?>...</p>
                                            <div class="result-meta">
                                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($project['owner_name'] ?? 'Utilisateur') ?></span>
                                                <span><i class="fas fa-users"></i> <?= $project['members_count'] ?? 0 ?> membres</span>
                                                <span class="status-badge status-<?= $project['status'] ?? 'active' ?>">
                                                    <?= ucfirst($project['status'] ?? 'actif') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Jobs Results -->
                    <?php if (($filter === 'all' || $filter === 'jobs') && !empty($results['jobs'])): ?>
                        <div class="results-section">
                            <h3 class="section-title">
                                <i class="fas fa-briefcase"></i> Opportunités
                            </h3>
                            <div class="results-list">
                                <?php foreach ($results['jobs'] as $job): ?>
                                    <div class="result-item">
                                        <div class="result-icon">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div class="result-content">
                                            <h4>
                                                <a href="<?= BASE_URL ?>/job/view/<?= $job['id'] ?>">
                                                    <?= htmlspecialchars($job['title']) ?>
                                                </a>
                                            </h4>
                                            <p><?= htmlspecialchars(substr($job['description'] ?? '', 0, 150)) ?>...</p>
                                            <div class="result-meta">
                                                <span><i class="fas fa-building"></i> <?= htmlspecialchars($job['company_name'] ?? 'Entreprise') ?></span>
                                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['city'] ?? 'Non spécifié') ?></span>
                                                <span class="job-type"><?= ucfirst($job['type'] ?? 'emploi') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="search-empty-state">
                <i class="fas fa-search"></i>
                <h2>Que recherchez-vous ?</h2>
                <p>Trouvez des membres, discussions, tutoriels, projets et opportunités sur AlgoCodeBF</p>
                <div class="popular-searches">
                    <h4>Recherches populaires :</h4>
                    <div class="popular-tags">
                        <a href="?q=PHP" class="popular-tag">PHP</a>
                        <a href="?q=JavaScript" class="popular-tag">JavaScript</a>
                        <a href="?q=Python" class="popular-tag">Python</a>
                        <a href="?q=Laravel" class="popular-tag">Laravel</a>
                        <a href="?q=React" class="popular-tag">React</a>
                        <a href="?q=Stage" class="popular-tag">Stage</a>
                        <a href="?q=Développeur" class="popular-tag">Développeur</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* ===================================
   MOBILE FIRST - BASE STYLES
   =================================== */

.search-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 20px 0 30px; /* Mobile first : padding compact */
    text-align: center;
    position: relative;
    overflow: hidden;
}

.search-hero::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.search-hero h1 {
    font-size: 1.75rem; /* Mobile first : taille lisible */
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
    font-weight: 700;
}

.search-form-container {
    max-width: 100%; /* Mobile first : pleine largeur */
    margin: 0 auto;
    padding: 0 16px; /* Mobile first : padding latéral */
    position: relative;
    z-index: 1;
}

.search-form-main {
    background: white;
    border-radius: 25px; /* Mobile first : border radius adapté */
    padding: 6px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.search-input-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-input-wrapper i {
    padding-left: 12px;
    font-size: 1.2rem;
    color: var(--primary-color);
    flex-shrink: 0;
}

.search-input-wrapper input {
    flex: 1;
    padding: 14px 8px; /* Mobile first : padding généreux (48px min) */
    min-height: 48px; /* Mobile first : zone tactile optimale */
    border: none;
    font-size: 1rem; /* Mobile first : 16px pour éviter zoom iOS */
    outline: none;
}

.btn-search-submit {
    padding: 14px 20px; /* Mobile first : padding généreux */
    min-height: 48px; /* Mobile first : zone tactile optimale */
    min-width: 100px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white !important;
    border: none;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    white-space: nowrap;
    flex-shrink: 0;
}

.btn-search-submit:active {
    transform: scale(0.96); /* Mobile first : feedback tactile */
}

/* ===================================
   SECTION RÉSULTATS - MOBILE FIRST
   =================================== */

.search-results-section {
    padding: 20px 0 100px; /* Mobile first : espace pour nav mobile */
    background: #f8f9fa;
}

.search-results-section .container {
    padding: 0 16px; /* Mobile first : padding latéral */
}

.search-info {
    text-align: center;
    margin-bottom: 24px;
}

.search-info h2 {
    font-size: 1.5rem; /* Mobile first : taille lisible */
    margin-bottom: 8px;
    line-height: 1.3;
}

.search-info strong {
    color: var(--primary-color);
}

.search-info p {
    font-size: 0.95rem;
    color: #6c757d;
}

/* ===================================
   FILTRES - MOBILE FIRST
   =================================== */

.filter-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* Mobile first : smooth scroll iOS */
    padding-bottom: 12px;
    scrollbar-width: thin;
}

.filter-tabs::-webkit-scrollbar {
    height: 4px;
}

.filter-tabs::-webkit-scrollbar-thumb {
    background: rgba(200, 16, 46, 0.3);
    border-radius: 4px;
}

.filter-tab {
    padding: 12px 18px; /* Mobile first : padding généreux */
    min-height: 48px; /* Mobile first : zone tactile optimale */
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 24px;
    text-decoration: none;
    color: var(--dark-color);
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.filter-tab:active {
    transform: scale(0.97); /* Mobile first : feedback tactile */
}

.filter-tab.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(200, 16, 46, 0.3);
}

/* ===================================
   CONTENEUR RÉSULTATS - MOBILE FIRST
   =================================== */

.results-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.results-section {
    background: white;
    padding: 16px; /* Mobile first : padding compact */
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

.section-title {
    font-size: 1.25rem; /* Mobile first : taille lisible */
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f0f0;
    font-weight: 700;
}

.section-title i {
    font-size: 1.1rem;
}

/* ===================================
   GRILLE RÉSULTATS (MEMBRES) - MOBILE FIRST
   =================================== */

.results-grid {
    display: grid;
    grid-template-columns: 1fr; /* Mobile first : 1 colonne */
    gap: 16px;
}

.result-card {
    padding: 16px; /* Mobile first : padding compact */
    min-height: 120px; /* Mobile first : hauteur confortable */
    border: 2px solid #e9ecef;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: all 0.2s ease;
    -webkit-tap-highlight-color: rgba(200, 16, 46, 0.1); /* Mobile first : feedback visuel */
}

.result-card:active {
    transform: scale(0.98); /* Mobile first : feedback tactile */
    border-color: var(--primary-color);
}

.result-avatar {
    flex-shrink: 0;
}

.result-avatar img,
.result-avatar .avatar-placeholder {
    width: 64px; /* Mobile first : avatar adapté */
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 12px;
}

.avatar-placeholder {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
}

/* ===================================
   LISTES RÉSULTATS - MOBILE FIRST
   =================================== */

.results-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.result-item {
    padding: 16px; /* Mobile first : padding compact */
    min-height: 100px; /* Mobile first : hauteur confortable */
    border: 2px solid #e9ecef;
    border-radius: 12px;
    display: flex;
    gap: 14px;
    transition: all 0.2s ease;
    -webkit-tap-highlight-color: rgba(200, 16, 46, 0.1); /* Mobile first : feedback visuel */
}

.result-item:active {
    transform: scale(0.99); /* Mobile first : feedback tactile */
    border-color: var(--primary-color);
}

.result-icon {
    width: 48px; /* Mobile first : taille adaptée */
    height: 48px;
    min-width: 48px; /* Mobile first : éviter rétrécissement */
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.result-content {
    flex: 1;
    min-width: 0; /* Mobile first : éviter overflow */
}

.result-content h4 {
    margin: 0 0 8px;
    font-size: 1.05rem; /* Mobile first : taille lisible */
    line-height: 1.4;
}

.result-content h4 a {
    color: var(--dark-color);
    text-decoration: none;
    transition: color 0.2s ease;
}

.result-content h4 a:active {
    color: var(--primary-color); /* Mobile first : feedback tactile */
}

.result-content p {
    color: #6c757d;
    margin-bottom: 10px;
    line-height: 1.5;
    font-size: 0.9rem;
    display: -webkit-box;
    -webkit-line-clamp: 3; /* Mobile first : limiter à 3 lignes */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.result-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: #6c757d;
}

.result-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.result-meta i {
    font-size: 0.8rem;
}

/* ===================================
   ÉTATS VIDES - MOBILE FIRST
   =================================== */

.no-results {
    text-align: center;
    padding: 40px 20px; /* Mobile first : padding adapté */
    background: white;
    border-radius: 12px;
}

.no-results i {
    font-size: 3.5rem; /* Mobile first : taille adaptée */
    color: #e9ecef;
    margin-bottom: 16px;
}

.no-results h3 {
    font-size: 1.3rem;
    margin-bottom: 8px;
}

.no-results p {
    font-size: 0.95rem;
    color: #6c757d;
}

.search-suggestions {
    margin-top: 24px;
    text-align: left;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.search-suggestions h4 {
    font-size: 1.1rem;
    margin-bottom: 12px;
}

.search-suggestions ul {
    list-style: none;
    padding: 0;
}

.search-suggestions li {
    padding: 10px 0;
    padding-left: 24px;
    position: relative;
    font-size: 0.9rem;
    line-height: 1.5;
}

.search-suggestions li::before {
    content: '→';
    position: absolute;
    left: 0;
    color: var(--primary-color);
    font-weight: 700;
}

.search-empty-state {
    text-align: center;
    padding: 40px 20px; /* Mobile first : padding adapté */
    background: white;
    border-radius: 12px;
}

.search-empty-state i {
    font-size: 3.5rem; /* Mobile first : taille adaptée */
    color: var(--primary-color);
    margin-bottom: 16px;
    opacity: 0.5;
}

.search-empty-state h2 {
    font-size: 1.4rem;
    margin-bottom: 8px;
}

.search-empty-state p {
    font-size: 0.95rem;
    color: #6c757d;
}

.popular-searches {
    margin-top: 24px;
}

.popular-searches h4 {
    font-size: 1.1rem;
    margin-bottom: 12px;
}

.popular-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    margin-top: 16px;
}

.popular-tag {
    padding: 10px 18px; /* Mobile first : padding généreux */
    min-height: 40px; /* Mobile first : zone tactile confortable */
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 20px;
    text-decoration: none;
    color: var(--dark-color);
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.popular-tag:active {
    transform: scale(0.96); /* Mobile first : feedback tactile */
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

/* ===================================
   BADGES - MOBILE FIRST
   =================================== */

.status-badge {
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-recruiting { background: #e8f5e9; color: #2e7d32; }
.status-in_progress { background: #e3f2fd; color: #1976d2; }
.status-completed { background: #f3e5f5; color: #7b1fa2; }
.status-active { background: #e8f5e9; color: #2e7d32; }

.job-type {
    padding: 5px 12px;
    background: var(--primary-color);
    color: white;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* ===================================
   PROGRESSIVE ENHANCEMENT - DESKTOP (768px+)
   =================================== */

@media (min-width: 768px) {
    /* Hero Section */
    .search-hero {
        padding: 50px 0 40px;
    }
    
    .search-hero h1 {
        font-size: 2.5rem;
        margin-bottom: 25px;
    }
    
    .search-form-container {
        max-width: 700px;
        padding: 0 20px;
    }
    
    .search-form-main {
        padding: 8px;
    }
    
    .btn-search-submit:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    /* Résultats Section */
    .search-results-section {
        padding: 40px 0 80px;
    }
    
    .search-results-section .container {
        padding: 0 20px;
    }
    
    .search-info h2 {
        font-size: 2rem;
    }
    
    .results-container {
        gap: 32px;
    }
    
    .results-section {
        padding: 24px;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
    
    /* Grilles et Listes */
    .results-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .result-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transform: translateY(-3px);
    }
    
    .result-item {
        padding: 20px;
    }
    
    .result-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transform: translateX(5px);
    }
    
    .result-icon {
        width: 50px;
        height: 50px;
        min-width: 50px;
        font-size: 1.5rem;
    }
    
    .result-content h4 {
        font-size: 1.2rem;
    }
    
    .result-content h4 a:hover {
        color: var(--primary-color);
    }
    
    .result-content p {
        font-size: 0.95rem;
    }
    
    .filter-tab:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    
    .popular-tag:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }
    
    /* États vides */
    .no-results {
        padding: 60px 20px;
    }
    
    .no-results i {
        font-size: 5rem;
    }
    
    .search-empty-state {
        padding: 60px 20px;
    }
    
    .search-empty-state i {
        font-size: 5rem;
    }
}

/* ===================================
   DESKTOP LARGE (992px+)
   =================================== */

@media (min-width: 992px) {
    .search-hero {
        padding: 80px 0 60px;
    }
    
    .search-hero h1 {
        font-size: 3rem;
        margin-bottom: 30px;
    }
    
    .search-form-container {
        max-width: 800px;
    }
    
    .search-form-main {
        padding: 10px;
    }
    
    .search-results-section {
        padding: 60px 0 80px;
    }
    
    .results-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    
    .results-section {
        padding: 30px;
    }
}
</style>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

