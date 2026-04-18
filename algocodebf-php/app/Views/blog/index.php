<?php
$pageTitle = 'Blog Tech - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Préparer les données
$posts = $posts ?? [];
$popular = $popular ?? [];
$featured_post = $featured_post ?? null;
$current_category = $current_category ?? null;
?>

<!-- Hero Section Ultra-Moderne -->
<section class="blog-hero-ultra">
    <div class="hero-particles"></div>
    <div class="hero-gradient-overlay"></div>
    <div class="container">
        <div class="hero-content-ultra">
            <div class="hero-badge-animated">
                <i class="fas fa-rocket"></i> Blog Tech BF
            </div>
            <h1 class="hero-title-ultra">
                Inspirations & Actualités<br>
                <span class="gradient-text-animated">de la Tech Burkinabè</span>
            </h1>
            <p class="hero-subtitle-ultra">
                Découvrez les dernières tendances, tutoriels et success stories de notre communauté
            </p>
            
            <!-- Search Bar Ultra-Moderne -->
            <div class="search-bar-ultra">
                <div class="search-wrapper-ultra">
                    <i class="fas fa-search search-icon-ultra"></i>
                    <input type="text" 
                           id="blogSearch" 
                           class="search-input-ultra"
                           placeholder="Rechercher un article, un sujet, un tag..."
                           onkeyup="searchBlogsUltra()">
                    <button class="btn-clear-search" id="btnClearSearch" onclick="clearSearchUltra()" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Post avec effet Parallax -->
<?php if ($featured_post): ?>
<section class="featured-ultra-section">
    <div class="container">
        <div class="featured-card-ultra" onclick="window.location='<?= BASE_URL ?>/blog/show/<?= $featured_post['slug'] ?? $featured_post['id'] ?>'">
            <div class="featured-image-ultra" 
                 style="background-image: url('<?= BASE_URL ?>/<?= htmlspecialchars($featured_post['featured_image'] ?? 'uploads/blog/default.jpg') ?>');">
                <div class="featured-overlay-ultra"></div>
                <div class="featured-pulse-badge">
                    <i class="fas fa-fire"></i> À LA UNE
                </div>
            </div>
            <div class="featured-content-ultra">
                <div class="featured-meta-row">
                    <span class="category-chip cat-<?= strtolower($featured_post['category'] ?? 'actualites') ?>">
                        <?= htmlspecialchars($featured_post['category'] ?? 'Actualités', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="reading-chip">
                        <i class="far fa-clock"></i> 
                        <?= ceil(str_word_count($featured_post['content'] ?? '') / 200) ?> min
                    </span>
                </div>
                <h2 class="featured-title-ultra"><?= cleanAndSecure($featured_post['title']) ?></h2>
                <p class="featured-excerpt-ultra">
                    <?= cleanAndSecure(substr($featured_post['excerpt'] ?? '', 0, 160)) ?>...
                </p>
                <div class="featured-footer-ultra">
                    <div class="author-compact">
                        <?php 
                        $authorPhoto = $featured_post['author_photo'] ?? '';
                        $authorName = $featured_post['author_name'] ?? 'Auteur';
                        $authorInitial = strtoupper(substr($authorName, 0, 1));
                        ?>
                        <div class="author-avatar-compact">
                            <?php if (!empty($authorPhoto)): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($authorPhoto) ?>" alt="<?= htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <span class="avatar-initial"><?= $authorInitial ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="author-text">
                            <strong><?= htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8') ?></strong>
                            <span><?= timeAgo($featured_post['created_at']) ?></span>
                        </div>
                    </div>
                    <button class="btn-featured-read">
                        Découvrir <i class="fas fa-arrow-right arrow-animated"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Filtres Flottants Modernes -->
<section class="filters-ultra-section">
    <div class="container">
        <div class="filters-container-ultra">
            <!-- Catégories Pills -->
            <div class="categories-pills">
                <button class="pill-btn <?= !$current_category ? 'active' : '' ?>" 
                        onclick="filterCategory('')"
                        data-category="">
                    <i class="fas fa-th-large"></i> Tout
                </button>
                <button class="pill-btn <?= $current_category === 'Actualités' ? 'active' : '' ?>" 
                        onclick="filterCategory('Actualités')"
                        data-category="actualites">
                    <i class="fas fa-newspaper"></i> Actualités
                </button>
                <button class="pill-btn <?= $current_category === 'Tutoriels' ? 'active' : '' ?>" 
                        onclick="filterCategory('Tutoriels')"
                        data-category="tutoriels">
                    <i class="fas fa-graduation-cap"></i> Tutoriels
                </button>
                <button class="pill-btn <?= $current_category === 'Carrière' ? 'active' : '' ?>" 
                        onclick="filterCategory('Carrière')"
                        data-category="carriere">
                    <i class="fas fa-briefcase"></i> Carrière
                </button>
                <button class="pill-btn <?= $current_category === 'Startups' ? 'active' : '' ?>" 
                        onclick="filterCategory('Startups')"
                        data-category="startups">
                    <i class="fas fa-rocket"></i> Startups
                </button>
                <button class="pill-btn <?= $current_category === 'Événements' ? 'active' : '' ?>" 
                        onclick="filterCategory('Événements')"
                        data-category="evenements">
                    <i class="fas fa-calendar-star"></i> Événements
                </button>
                <?php foreach ($categories as $cat): ?>
                    <?php if (!in_array($cat['name'], ['Actualités', 'Tutoriels', 'Carrière', 'Startups', 'Événements'])): ?>
                        <button class="pill-btn <?= $current_category === $cat['name'] ? 'active' : '' ?>" 
                                onclick="filterCategory('<?= htmlspecialchars($cat['name']) ?>')"
                                data-category="<?= htmlspecialchars($cat['slug']) ?>">
                            <i class="<?= htmlspecialchars($cat['icon']) ?>" style="color: <?= htmlspecialchars($cat['color']) ?>"></i> 
                            <?= htmlspecialchars($cat['name']) ?>
                            <?php if (($cat['posts_count'] ?? 0) > 0): ?>
                                <span class="count-badge"><?= $cat['posts_count'] ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Sort Dropdown -->
            <div class="sort-dropdown-ultra">
                <select id="sortSelect" onchange="sortPostsUltra(this.value)">
                    <option value="recent"><i class="fas fa-clock"></i> Plus récents</option>
                    <option value="popular">⭐ Plus populaires</option>
                    <option value="views">👁️ Plus lus</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Grid Articles Ultra-Moderne -->
<section class="blog-grid-ultra">
    <div class="container">
        <div class="grid-layout-ultra">
            <!-- Main Grid -->
            <div class="main-grid-ultra">
                <?php if (empty($posts)): ?>
                    <div class="no-posts-ultra">
                        <div class="no-posts-animation">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Aucun article trouvé</h3>
                        <p>Aucun contenu ne correspond à vos critères de recherche</p>
                        <button class="btn-reset-filters" onclick="resetAllFilters()">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Loading indicator -->
                    <div class="loading-indicator" id="loadingIndicator" style="display: none;">
                        <div class="spinner"></div>
                        <p>Recherche en cours...</p>
                    </div>

                    <!-- Articles grid -->
                    <div class="articles-grid-masonry" id="articlesGrid">
                        <?php foreach ($posts as $index => $post): ?>
                            <article class="article-card-ultra" 
                                     data-category="<?= strtolower($post['category'] ?? '') ?>"
                                     data-title="<?= strtolower($post['title']) ?>"
                                     data-views="<?= $post['views'] ?? 0 ?>"
                                     data-likes="<?= $post['likes_count'] ?? 0 ?>"
                                     style="animation-delay: <?= $index * 0.1 ?>s">
                                <a href="<?= BASE_URL ?>/blog/show/<?= $post['slug'] ?? $post['id'] ?>" class="card-link-ultra">
                                    <div class="card-image-ultra">
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($post['featured_image'] ?? 'uploads/blog/default.jpg') ?>" 
                                             alt="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>"
                                             loading="lazy">
                                        <div class="image-overlay-ultra"></div>
                                        <span class="category-floating cat-<?= strtolower($post['category'] ?? 'actualites') ?>">
                                            <?= htmlspecialchars($post['category'] ?? 'Actualités', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <div class="quick-stats-overlay">
                                            <span><i class="far fa-eye"></i> <?= formatNumber($post['views'] ?? 0) ?></span>
                                            <span><i class="far fa-heart"></i> <?= $post['likes_count'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-content-ultra">
                                        <div class="card-meta-top">
                                            <span class="reading-time-badge">
                                                <i class="far fa-clock"></i> 
                                                <?= ceil(str_word_count($post['content'] ?? '') / 200) ?> min
                                            </span>
                                            <span class="date-badge">
                                                <?= timeAgo($post['created_at']) ?>
                                            </span>
                                        </div>
                                        
                                        <h3 class="card-title-ultra"><?= cleanAndSecure($post['title']) ?></h3>
                                        
                                        <p class="card-excerpt-ultra">
                                            <?= cleanAndSecure(substr($post['excerpt'] ?? '', 0, 110)) ?>...
                                        </p>
                                        
                                        <div class="card-footer-ultra">
                                            <div class="author-mini-ultra">
                                                <?php 
                                                $authorPhoto = $post['author_photo'] ?? '';
                                                $authorName = $post['author_name'] ?? 'Auteur';
                                                $authorInitial = strtoupper(substr($authorName, 0, 1));
                                                ?>
                                                <div class="author-avatar-mini-ultra">
                                                    <?php if (!empty($authorPhoto)): ?>
                                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($authorPhoto) ?>" alt="<?= htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8') ?>">
                                                    <?php else: ?>
                                                        <span class="avatar-placeholder-mini-ultra"><?= $authorInitial ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="author-name-ultra"><?= htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                            <div class="read-more-icon">
                                                <i class="fas fa-arrow-right"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container" id="paginationContainer" style="display: none;">
                        <!-- La pagination sera générée dynamiquement -->
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Ultra-Moderne -->
            <aside class="sidebar-ultra">
                <!-- Stats Widget -->
                <div class="widget-ultra widget-stats">
                    <div class="stats-grid-ultra">
                        <div class="stat-box-ultra">
                            <div class="stat-icon-ultra" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= count($posts) ?></div>
                                <div class="stat-label">Articles</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popular Posts -->
                <?php if (!empty($popular)): ?>
                <div class="widget-ultra">
                    <h3 class="widget-title-ultra">
                        <i class="fas fa-fire-alt"></i> Populaires
                    </h3>
                    <div class="popular-list-ultra">
                        <?php foreach (array_slice($popular, 0, 5) as $index => $pop): ?>
                            <a href="<?= BASE_URL ?>/blog/show/<?= $pop['slug'] ?? $pop['id'] ?>" 
                               class="popular-item-ultra">
                                <div class="popular-rank-ultra rank-<?= $index + 1 ?>">
                                    <?= $index + 1 ?>
                                </div>
                                <div class="popular-content-ultra">
                                    <h4><?= cleanAndSecure($pop['title']) ?></h4>
                                    <div class="popular-meta-ultra">
                                        <span><i class="far fa-eye"></i> <?= formatNumber($pop['views'] ?? 0) ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Newsletter Ultra-Design -->
                <div class="widget-ultra widget-newsletter-ultra">
                    <div class="newsletter-glow"></div>
                    <div class="newsletter-icon-ultra">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h3>Restez connecté</h3>
                    <p>Recevez nos meilleurs articles chaque semaine</p>
                    <form class="newsletter-form-ultra" onsubmit="console.log('📧 Formulaire soumis !'); console.log('Type:', typeof window.subscribeNewsletterUltra); if (typeof window.subscribeNewsletterUltra === 'function') { window.subscribeNewsletterUltra(event); } else { console.error('❌ subscribeNewsletterUltra non défini !'); alert('Erreur: La fonction newsletter n\'est pas chargée. Vérifiez la console (F12)'); } return false;">
                        <input type="email" 
                               placeholder="votre@email.com" 
                               required>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    <div class="newsletter-count">
                        <i class="fas fa-users"></i> Rejoignez 500+ abonnés
                    </div>
                </div>

                <!-- CTA Widget si connecté -->
                <?php if ($this->isLoggedIn() && $_SESSION['user_role'] === 'admin'): ?>
                <div class="widget-ultra widget-cta-ultra">
                    <div class="cta-icon-ultra">
                        <i class="fas fa-pen-nib"></i>
                    </div>
                    <h3>Créer un article</h3>
                    <p>Partagez votre expertise avec la communauté</p>
                    <a href="<?= BASE_URL ?>/blog/create" class="btn-cta-ultra">
                        <i class="fas fa-plus-circle"></i> Nouvel article
                    </a>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<!-- Scroll to Top Button -->
<button class="scroll-top-btn" id="scrollTopBtn" onclick="scrollToTop()">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
/* ================================
   HERO SECTION ULTRA-MODERNE
   ================================ */
.blog-hero-ultra {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    overflow: hidden;
    padding: 60px 0;
}

.hero-particles {
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(255, 255, 255, 0.06) 0%, transparent 50%);
    animation: particlesFloat 20s ease-in-out infinite;
}

@keyframes particlesFloat {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.hero-gradient-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.1) 100%);
}

.hero-content-ultra {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 900px;
    margin: 0 auto;
    padding: 0 20px;
}

.hero-badge-animated {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 25px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 50px;
    color: white;
    font-weight: 700;
    font-size: 0.95rem;
    margin-bottom: 30px;
    animation: badgePulse 3s ease-in-out infinite;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

@keyframes badgePulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.hero-title-ultra {
    font-size: 4rem;
    font-weight: 900;
    color: white;
    margin-bottom: 25px;
    line-height: 1.15;
    letter-spacing: -1px;
    text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: titleSlideUp 1s ease;
}

@keyframes titleSlideUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.gradient-text-animated {
    background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.85) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block;
    animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { filter: brightness(1); }
    50% { filter: brightness(1.2); }
}

.hero-subtitle-ultra {
    font-size: 1.3rem;
    color: rgba(255, 255, 255, 0.95);
    margin-bottom: 45px;
    line-height: 1.6;
    font-weight: 400;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    animation: subtitleFade 1.2s ease;
}

@keyframes subtitleFade {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Search Bar Ultra */
.search-bar-ultra {
    max-width: 650px;
    margin: 0 auto;
    animation: searchAppear 1.4s ease;
}

@keyframes searchAppear {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.search-wrapper-ultra {
    position: relative;
    display: flex;
    align-items: center;
    background: white;
    border-radius: 60px;
    padding: 8px 25px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.search-wrapper-ultra:hover {
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
    transform: translateY(-3px);
}

.search-wrapper-ultra:focus-within {
    box-shadow: 0 25px 70px rgba(102, 126, 234, 0.5);
}

.search-icon-ultra {
    color: var(--primary-color);
    font-size: 1.4rem;
    margin-right: 15px;
    animation: searchIconPulse 2s ease infinite;
}

@keyframes searchIconPulse {
    0%, 100% { opacity: 0.7; }
    50% { opacity: 1; }
}

.search-input-ultra {
    flex: 1;
    border: none;
    background: transparent;
    padding: 16px 10px;
    font-size: 1.1rem;
    color: var(--dark-color);
    font-weight: 500;
}

.search-input-ultra:focus {
    outline: none;
}

.search-input-ultra::placeholder {
    color: #999;
}

.btn-clear-search {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f0f0f0;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-clear-search:hover {
    background: var(--danger-color);
    color: white;
    transform: rotate(90deg);
}

/* ================================
   FEATURED POST ULTRA
   ================================ */
.featured-ultra-section {
    padding: 50px 0;
    background: #f8f9fa;
}

.featured-card-ultra {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    background: white;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.featured-card-ultra::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 25px;
    padding: 3px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color), #f093fb);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.5s ease;
}

.featured-card-ultra:hover::before {
    opacity: 1;
}

.featured-card-ultra:hover {
    transform: translateY(-10px);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2);
}

.featured-image-ultra {
    position: relative;
    background-size: cover;
    background-position: center;
    min-height: 450px;
    transition: transform 0.8s ease;
}

.featured-card-ultra:hover .featured-image-ultra {
    transform: scale(1.05);
}

.featured-overlay-ultra {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.2), rgba(0,0,0,0.4));
}

.featured-pulse-badge {
    position: absolute;
    top: 25px;
    left: 25px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    color: white;
    border-radius: 50px;
    font-weight: 800;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 2;
    box-shadow: 0 8px 24px rgba(255, 107, 107, 0.5);
    animation: pulse 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { 
        transform: scale(1);
        box-shadow: 0 8px 24px rgba(255, 107, 107, 0.5);
    }
    50% { 
        transform: scale(1.08);
        box-shadow: 0 12px 32px rgba(255, 107, 107, 0.7);
    }
}

.featured-content-ultra {
    padding: 45px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.featured-meta-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.category-chip,
.reading-chip {
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
}

.category-chip {
    color: white;
}

.reading-chip {
    background: #f8f9fa;
    color: #6c757d;
}

.cat-actualités, .cat-actualites { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
.cat-tutoriels { background: linear-gradient(135deg, #28a745, #20c997); }
.cat-carrière, .cat-carriere { background: linear-gradient(135deg, #ffc107, #ff9800); }
.cat-startups { background: linear-gradient(135deg, #ff6b6b, #ee5a6f); }
.cat-événements, .cat-evenements { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }

.featured-title-ultra {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--dark-color);
    margin-bottom: 18px;
    line-height: 1.25;
    letter-spacing: -0.5px;
}

.featured-excerpt-ultra {
    color: #6c757d;
    font-size: 1.05rem;
    line-height: 1.7;
    margin-bottom: 30px;
}

.featured-footer-ultra {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 25px;
    border-top: 2px solid #f0f0f0;
}

.author-compact {
    display: flex;
    align-items: center;
    gap: 12px;
}

.author-avatar-compact {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--primary-color);
}

.author-avatar-compact img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initial {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
}

.author-text strong {
    display: block;
    color: var(--dark-color);
    font-size: 1rem;
    margin-bottom: 3px;
}

.author-text span {
    color: #6c757d;
    font-size: 0.85rem;
}

.btn-featured-read {
    padding: 14px 28px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 8px 24px rgba(52, 152, 219, 0.3);
}

.btn-featured-read:hover {
    transform: translateX(8px);
    box-shadow: 0 12px 32px rgba(52, 152, 219, 0.5);
}

.arrow-animated {
    transition: transform 0.3s ease;
}

.btn-featured-read:hover .arrow-animated {
    transform: translateX(5px);
}

/* ================================
   FILTRES ULTRA-MODERNES
   ================================ */
.filters-ultra-section {
    padding: 30px 0;
    background: white;
    border-bottom: 1px solid #e9ecef;
    position: relative;
    z-index: 10;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
}

.filters-container-ultra {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.categories-pills {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    flex: 1;
}

.pill-btn {
    padding: 12px 22px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 50px;
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.pill-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.pill-btn span {
    position: relative;
    z-index: 1;
}

.pill-btn i {
    position: relative;
    z-index: 1;
}

.pill-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.2);
}

.pill-btn.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
    box-shadow: 0 8px 24px rgba(52, 152, 219, 0.4);
}

.sort-dropdown-ultra select {
    padding: 12px 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 50px;
    color: var(--dark-color);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
    padding-right: 45px;
}

.sort-dropdown-ultra select:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
}

.sort-dropdown-ultra select:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* ================================
   GRID ULTRA-MODERNE
   ================================ */
.blog-grid-ultra {
    padding: 60px 0 80px;
    background: #f8f9fa;
}

.grid-layout-ultra {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 40px;
}

.articles-grid-masonry {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 30px;
}

/* ================================
   CARDS ULTRA-MODERNES
   ================================ */
.article-card-ultra {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    animation: cardAppear 0.6s ease backwards;
    position: relative;
}

@keyframes cardAppear {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.article-card-ultra:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.card-link-ultra {
    text-decoration: none;
    color: inherit;
    display: block;
}

.card-image-ultra {
    position: relative;
    height: 240px;
    overflow: hidden;
}

.card-image-ultra img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}

.article-card-ultra:hover .card-image-ultra img {
    transform: scale(1.15) rotate(2deg);
}

.image-overlay-ultra {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.4) 100%);
    opacity: 0;
    transition: opacity 0.5s ease;
}

.article-card-ultra:hover .image-overlay-ultra {
    opacity: 1;
}

.category-floating {
    position: absolute;
    top: 18px;
    left: 18px;
    padding: 8px 18px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    color: white;
    z-index: 2;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.quick-stats-overlay {
    position: absolute;
    bottom: 18px;
    right: 18px;
    display: flex;
    gap: 12px;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.4s ease;
    z-index: 2;
}

.article-card-ultra:hover .quick-stats-overlay {
    opacity: 1;
    transform: translateY(0);
}

.quick-stats-overlay span {
    padding: 6px 14px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.card-content-ultra {
    padding: 28px;
}

.card-meta-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.reading-time-badge,
.date-badge {
    padding: 6px 12px;
    background: #f8f9fa;
    color: #6c757d;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.card-title-ultra {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--dark-color);
    margin-bottom: 14px;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.3s ease;
}

.article-card-ultra:hover .card-title-ultra {
    color: var(--primary-color);
}

.card-excerpt-ultra {
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.7;
    margin-bottom: 22px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-footer-ultra {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 20px;
    border-top: 2px solid #f8f9fa;
}

.author-mini-ultra {
    display: flex;
    align-items: center;
    gap: 10px;
}

.author-avatar-mini-ultra {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--primary-color);
}

.author-avatar-mini-ultra img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-mini-ultra {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.9rem;
}

.author-name-ultra {
    font-weight: 600;
    color: var(--dark-color);
    font-size: 0.9rem;
}

.read-more-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.article-card-ultra:hover .read-more-icon {
    transform: translateX(5px) rotate(360deg);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
}

/* ================================
   SIDEBAR ULTRA
   ================================ */
.sidebar-ultra {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.widget-ultra {
    background: white;
    padding: 28px;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.widget-ultra:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
    transform: translateY(-5px);
}

.widget-title-ultra {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--dark-color);
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.widget-title-ultra i {
    color: var(--primary-color);
    font-size: 1.3rem;
}

/* Stats Widget */
.widget-stats {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.stats-grid-ultra {
    display: grid;
    gap: 15px;
}

.stat-box-ultra {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 18px;
    border-radius: 15px;
}

.stat-icon-ultra {
    width: 55px;
    height: 55px;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 5px;
    color: var(--accent-color);
}

.stat-label {
    font-size: 0.85rem;
    opacity: 0.9;
    font-weight: 600;
    color: white;
}

/* Popular Posts Ultra */
.popular-list-ultra {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.popular-item-ultra {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 15px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.popular-item-ultra::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.popular-item-ultra:hover::before {
    transform: scaleY(1);
}

.popular-item-ultra:hover {
    background: white;
    transform: translateX(8px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.popular-rank-ultra {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.2rem;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.rank-1 {
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    color: #000;
}

.rank-2 {
    background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
    color: #000;
}

.rank-3 {
    background: linear-gradient(135deg, #cd7f32, #e09856);
    color: #fff;
}

.popular-content-ultra {
    flex: 1;
    min-width: 0;
}

.popular-content-ultra h4 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 6px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.popular-meta-ultra {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Newsletter Ultra */
.widget-newsletter-ultra {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.newsletter-glow {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: glowPulse 4s ease-in-out infinite;
}

@keyframes glowPulse {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

.newsletter-icon-ultra {
    font-size: 3.5rem;
    margin-bottom: 18px;
    animation: iconFloat 3s ease-in-out infinite;
}

@keyframes iconFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.widget-newsletter-ultra h3 {
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: 10px;
}

.widget-newsletter-ultra p {
    opacity: 0.95;
    margin-bottom: 25px;
    font-size: 0.95rem;
}

.newsletter-form-ultra {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.newsletter-form-ultra input {
    flex: 1;
    padding: 14px 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    color: white;
    font-size: 0.95rem;
    font-weight: 500;
}

.newsletter-form-ultra input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.newsletter-form-ultra input:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.2);
    border-color: white;
}

.newsletter-form-ultra button {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: white;
    color: var(--primary-color);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.newsletter-form-ultra button:hover {
    transform: scale(1.15) rotate(15deg);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.newsletter-count {
    font-size: 0.85rem;
    opacity: 0.9;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* CTA Widget */
.widget-cta-ultra {
    background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cta-icon-ultra {
    font-size: 3rem;
    margin-bottom: 15px;
    animation: iconBounce 2s ease-in-out infinite;
}

@keyframes iconBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.widget-cta-ultra h3 {
    font-size: 1.3rem;
    font-weight: 800;
    margin-bottom: 10px;
}

.widget-cta-ultra p {
    opacity: 0.95;
    margin-bottom: 20px;
}

.btn-cta-ultra {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 28px;
    background: white;
    color: var(--primary-color);
    border-radius: 50px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.btn-cta-ultra:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
}

/* No Posts */
.no-posts-ultra {
    text-align: center;
    padding: 100px 20px;
}

.no-posts-animation {
    width: 120px;
    height: 120px;
    margin: 0 auto 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.5rem;
    color: #dee2e6;
    animation: noPostsPulse 2s ease-in-out infinite;
}

@keyframes noPostsPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.no-posts-ultra h3 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 12px;
}

.no-posts-ultra p {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 25px;
}

.btn-reset-filters {
    padding: 14px 30px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-reset-filters:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(52, 152, 219, 0.4);
}

/* Scroll to Top */
.scroll-top-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 55px;
    height: 55px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 1.3rem;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(52, 152, 219, 0.4);
    transition: all 0.3s ease;
    z-index: 999;
}

.scroll-top-btn:hover {
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 12px 32px rgba(52, 152, 219, 0.6);
}

.scroll-top-btn.visible {
    display: flex;
    animation: bounceIn 0.6s ease;
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

/* ================================
   RESPONSIVE ULTRA
   ================================ */
@media (max-width: 1200px) {
    .articles-grid-masonry {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}

@media (max-width: 992px) {
    .grid-layout-ultra {
        grid-template-columns: 1fr;
    }
    
    .featured-card-ultra {
        grid-template-columns: 1fr;
    }
    
    .featured-image-ultra {
        min-height: 320px;
    }
    
    .sidebar-ultra {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .hero-title-ultra {
        font-size: 2.5rem;
    }
    
    .hero-subtitle-ultra {
        font-size: 1.05rem;
    }
    
    .categories-pills {
        width: 100%;
        justify-content: flex-start;
        overflow-x: auto;
        padding-bottom: 10px;
        scrollbar-width: thin;
    }
    
    .categories-pills::-webkit-scrollbar {
        height: 4px;
    }
    
    .categories-pills::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 10px;
    }
    
    .pill-btn {
        flex-shrink: 0;
    }
    
    .sort-dropdown-ultra {
        width: 100%;
    }
    
    .sort-dropdown-ultra select {
        width: 100%;
    }
    
    .articles-grid-masonry {
        grid-template-columns: 1fr;
        gap: 25px;
    }
    
    .featured-content-ultra {
        padding: 30px;
    }
    
    .featured-title-ultra {
        font-size: 1.7rem;
    }
    
    .featured-footer-ultra {
        flex-direction: column;
        gap: 18px;
        align-items: flex-start;
    }
    
    .page-header-modern {
        grid-template-columns: 1fr;
        text-align: center;
    }
}

@media (max-width: 576px) {
    .hero-title-ultra {
        font-size: 2rem;
    }
    
    .search-wrapper-ultra {
        padding: 6px 18px;
    }
    
    .search-input-ultra {
        font-size: 0.95rem;
        padding: 12px 8px;
    }
    
    .card-content-ultra {
        padding: 22px;
    }
    
    .widget-ultra {
        padding: 22px;
    }
    
    .scroll-top-btn {
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
    }
}

/* ================================
   LOADING INDICATOR
   ================================ */
.loading-indicator {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-indicator p {
    color: #6c757d;
    font-weight: 600;
    margin: 0;
}

/* ================================
   PAGINATION ULTRA
   ================================ */
.pagination-container {
    margin-top: 40px;
    display: flex;
    justify-content: center;
}

.pagination-ultra {
    display: flex;
    gap: 10px;
    align-items: center;
    background: white;
    padding: 15px 25px;
    border-radius: 50px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

.pagination-btn {
    padding: 12px 18px;
    background: #f8f9fa;
    border: 2px solid transparent;
    border-radius: 50px;
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 45px;
    justify-content: center;
}

.pagination-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.pagination-btn.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.pagination-btn:disabled:hover {
    background: #f8f9fa;
    color: #6c757d;
    box-shadow: none;
}
</style>

<script>
// ========================================
// RECHERCHE ASYNCHRONE ULTRA
// ========================================
let currentPage = 1;
let isLoading = false;
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    // Charger les options de filtres
    loadFilterOptions();
    
    // Configurer les événements
    setupEventListeners();
});

function setupEventListeners() {
    // Recherche avec debounce
    const searchInput = document.getElementById('blogSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadBlogPosts();
            }, 500);
        });
    }

    // Bouton clear search
    const clearBtn = document.getElementById('btnClearSearch');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearSearchUltra);
    }

    // Filtres de catégorie
    document.querySelectorAll('.pill-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const category = this.dataset.category || '';
            filterCategory(category);
        });
    });

    // Tri
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            currentPage = 1;
            loadBlogPosts();
        });
    }
}

function loadFilterOptions() {
    fetch(`<?= BASE_URL ?>/blog/getFilterOptions`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Les catégories sont déjà chargées côté serveur
            console.log('Options de filtres chargées:', data.categories);
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement des options:', error);
    });
}

function loadBlogPosts() {
    if (isLoading) return;
    
    isLoading = true;
    showLoading();
    
    const search = document.getElementById('blogSearch').value.trim();
    const sort = document.getElementById('sortSelect').value || 'recent';
    const category = getCurrentCategory();
    
    const params = new URLSearchParams({
        search: search,
        category: category,
        sort: sort,
        page: currentPage,
        limit: 12
    });
    
    fetch(`<?= BASE_URL ?>/blog/search?${params}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur HTTP: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            displayBlogPosts(data.posts);
            displayPagination(data.pagination);
            updateSearchUI(search, category);
        } else {
            showError(data.message || 'Erreur lors du chargement des articles');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showError('Erreur de connexion');
    })
    .finally(() => {
        isLoading = false;
        hideLoading();
    });
}

function displayBlogPosts(posts) {
    const grid = document.getElementById('articlesGrid');
    const loadingIndicator = document.getElementById('loadingIndicator');
    
    if (!posts || posts.length === 0) {
        grid.innerHTML = `
            <div class="no-posts-ultra">
                <div class="no-posts-animation">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Aucun article trouvé</h3>
                <p>Aucun contenu ne correspond à vos critères de recherche</p>
                <button class="btn-reset-filters" onclick="resetAllFilters()">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
            </div>
        `;
        return;
    }
    
    grid.innerHTML = posts.map((post, index) => createBlogPostCard(post, index)).join('');
}

function createBlogPostCard(post, index) {
    const authorName = post.author_name || 'Auteur';
    const authorInitial = authorName.charAt(0).toUpperCase();
    const authorPhoto = post.author_photo ? `${escapeHtml('<?= BASE_URL ?>/' + post.author_photo)}` : null;
    
    // Nettoyer les apostrophes pour l'affichage
    const cleanTitle = cleanApostrophes(post.title || '');
    const cleanExcerpt = cleanApostrophes(post.excerpt || '');
    const cleanAuthorName = cleanApostrophes(authorName);
    
    return `
        <article class="article-card-ultra" style="animation-delay: ${index * 0.1}s">
            <a href="${escapeHtml('<?= BASE_URL ?>/blog/show/' + (post.slug || post.id))}" class="card-link-ultra">
                <div class="card-image-ultra">
                    <img src="${escapeHtml('<?= BASE_URL ?>/' + post.featured_image)}" 
                         alt="${escapeHtml(cleanTitle)}"
                         loading="lazy">
                    <div class="image-overlay-ultra"></div>
                    <span class="category-floating cat-${escapeHtml((post.category || 'actualites').toLowerCase())}">
                        ${escapeHtml(post.category || 'Actualités')}
                    </span>
                    <div class="quick-stats-overlay">
                        <span><i class="far fa-eye"></i> ${formatNumber(post.views || 0)}</span>
                        <span><i class="far fa-heart"></i> ${post.likes_count || 0}</span>
                    </div>
                </div>
                
                <div class="card-content-ultra">
                    <div class="card-meta-top">
                        <span class="reading-time-badge">
                            <i class="far fa-clock"></i> ${post.reading_time || 1} min
                        </span>
                        <span class="date-badge">
                            ${timeAgo(post.created_at)}
                        </span>
                    </div>
                    
                    <h3 class="card-title-ultra">${escapeHtml(cleanTitle)}</h3>
                    
                    <p class="card-excerpt-ultra">
                        ${escapeHtml(substr(cleanExcerpt, 0, 110))}...
                    </p>
                    
                    <div class="card-footer-ultra">
                        <div class="author-mini-ultra">
                            <div class="author-avatar-mini-ultra">
                                ${authorPhoto ? 
                                    `<img src="${authorPhoto}" alt="${escapeHtml(cleanAuthorName)}">` :
                                    `<span class="avatar-placeholder-mini-ultra">${authorInitial}</span>`
                                }
                            </div>
                            <span class="author-name-ultra">${escapeHtml(cleanAuthorName)}</span>
                        </div>
                        <div class="read-more-icon">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </a>
        </article>
    `;
}

function displayPagination(pagination) {
    const container = document.getElementById('paginationContainer');
    
    if (pagination.total_pages <= 1) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    
    let paginationHTML = '<div class="pagination-ultra">';
    
    // Bouton précédent
    if (pagination.has_prev) {
        paginationHTML += `<button class="pagination-btn" onclick="changePage(${pagination.current_page - 1})">
            <i class="fas fa-chevron-left"></i> Précédent
        </button>`;
    }
    
    // Numéros de pages
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `<button class="pagination-btn ${i === pagination.current_page ? 'active' : ''}" 
                                 onclick="changePage(${i})">${i}</button>`;
    }
    
    // Bouton suivant
    if (pagination.has_next) {
        paginationHTML += `<button class="pagination-btn" onclick="changePage(${pagination.current_page + 1})">
            Suivant <i class="fas fa-chevron-right"></i>
        </button>`;
    }
    
    paginationHTML += '</div>';
    container.innerHTML = paginationHTML;
}

function changePage(page) {
    currentPage = page;
    loadBlogPosts();
    // Scroll vers le haut de la grille
    document.getElementById('articlesGrid').scrollIntoView({ behavior: 'smooth' });
}

function getCurrentCategory() {
    const activePill = document.querySelector('.pill-btn.active');
    return activePill ? activePill.dataset.category || '' : '';
}

function updateSearchUI(search, category) {
    const clearBtn = document.getElementById('btnClearSearch');
    if (clearBtn) {
        clearBtn.style.display = search ? 'flex' : 'none';
    }
}

function clearSearchUltra() {
    document.getElementById('blogSearch').value = '';
    currentPage = 1;
    loadBlogPosts();
}

function filterCategory(category) {
    // Mettre à jour l'état actif des boutons
    document.querySelectorAll('.pill-btn').forEach(btn => {
        btn.classList.remove('active');
        if ((btn.dataset.category || '') === (category || '')) {
            btn.classList.add('active');
        }
    });
    
    currentPage = 1;
    loadBlogPosts();
}

function resetAllFilters() {
    // Réinitialiser tous les filtres
    document.getElementById('blogSearch').value = '';
    document.getElementById('sortSelect').value = 'recent';
    
    // Réinitialiser les boutons de catégorie
    document.querySelectorAll('.pill-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.category === '') {
            btn.classList.add('active');
        }
    });
    
    currentPage = 1;
    loadBlogPosts();
}

function showLoading() {
    document.getElementById('loadingIndicator').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loadingIndicator').style.display = 'none';
}

function showError(message) {
    const grid = document.getElementById('articlesGrid');
    grid.innerHTML = `
        <div class="no-posts-ultra">
            <div class="no-posts-animation">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Erreur</h3>
            <p>${escapeHtml(message)}</p>
            <button class="btn-reset-filters" onclick="loadBlogPosts()">
                <i class="fas fa-redo"></i> Réessayer
            </button>
        </div>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function cleanApostrophes(text) {
    if (!text) return '';
    
    // Nettoyer le double encodage HTML
    let cleaned = text
        .replace(/&amp;#039;/g, "'")
        .replace(/&#039;/g, "'")
        .replace(/&amp;quot;/g, '"')
        .replace(/&quot;/g, '"')
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>');
    
    return cleaned;
}

function substr(text, start, length) {
    return text ? text.substring(start, start + length) : '';
}

function formatNumber(num) {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k';
    }
    return num.toString();
}

function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'À l\'instant';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' min';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' h';
    if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' j';
    if (diffInSeconds < 31536000) return Math.floor(diffInSeconds / 2592000) + ' mois';
    return Math.floor(diffInSeconds / 31536000) + ' ans';
}

// ========================================
// NEWSLETTER
// ========================================
function subscribeNewsletterUltra(event) {
    event.preventDefault();
    const email = event.target.querySelector('input[type="email"]').value;
    const btn = event.target.querySelector('button');
    const originalHTML = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-check"></i>';
        showNotificationUltra('✅ Merci ! Vous recevrez bientôt nos meilleurs articles.', 'success');
        event.target.reset();
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }, 2000);
    }, 1000);
}

// ========================================
// SCROLL TO TOP
// ========================================
const scrollTopBtn = document.getElementById('scrollTopBtn');

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 400) {
        scrollTopBtn.classList.add('visible');
    } else {
        scrollTopBtn.classList.remove('visible');
    }
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// ========================================
// NOTIFICATIONS
// ========================================
function showNotificationUltra(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 30px;
        right: 30px;
        z-index: 10000;
        padding: 18px 28px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 'linear-gradient(135deg, var(--primary-color), var(--secondary-color))'};
        color: white;
        border-radius: 15px;
        font-weight: 600;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.5s ease';
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOutRight {
    to {
        opacity: 0;
        transform: translateX(100px);
    }
}

// ========================================
// PARALLAX EFFECT (optionnel, performance)
// ========================================
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const heroParticles = document.querySelector('.hero-particles');
    if (heroParticles && scrolled < 800) {
        heroParticles.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
