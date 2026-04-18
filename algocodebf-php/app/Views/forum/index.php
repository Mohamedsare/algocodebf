<?php
$pageTitle = 'Forum - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Hero Section Forum -->
<section class="hero-section forum-hero">
    <div class="container">
        <div class="hero-content">
            <h1><i class="fas fa-comments"></i> Forum de Discussion</h1>
            <p>Échangez, apprenez et partagez avec la communauté tech du Burkina Faso</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/forum/create" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Nouvelle Discussion
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Forum Categories & Stats -->
<section class="forum-stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-comment-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['total_posts'] ?? 0 ?></h3>
                    <p>Discussions</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['active_members'] ?? 0 ?></h3>
                    <p>Membres Actifs</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['trending_topics'] ?? 0 ?></h3>
                    <p>Topics Tendances</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['today_posts'] ?? 0 ?></h3>
                    <p>Aujourd'hui</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Forum Categories -->
<section class="forum-categories-section">
    <div class="container">
        <h2 class="section-title">
            <i class="fas fa-th-large"></i> Catégories
        </h2>
        
        <div class="category-filter">
            <button class="filter-btn active" data-category="all">
                <i class="fas fa-globe"></i> Toutes
            </button>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" data-category="<?= htmlspecialchars($cat['slug']) ?>">
                        <i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i> <?= htmlspecialchars($cat['name']) ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Forum Posts -->
<section class="forum-posts-section">
    <div class="container">
        <div class="forum-layout">
            <!-- Main Content -->
            <div class="forum-main">
                <div class="forum-toolbar">
                    <div class="toolbar-left">
                        <h3>Discussions Récentes</h3>
                    </div>
                    <div class="toolbar-right">
                        <select class="sort-select">
                            <option value="recent">Plus récentes</option>
                            <option value="popular">Plus populaires</option>
                            <option value="trending">Tendances</option>
                            <option value="unanswered">Sans réponse</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>Aucune discussion pour le moment</h3>
                        <p>Soyez le premier à démarrer une discussion !</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/forum/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Créer une discussion
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="forum-posts-list">
                        <?php foreach ($posts as $post): ?>
                            <div class="forum-post-card" data-category="<?= htmlspecialchars($post['category']) ?>">
                                <div class="post-avatar">
                                    <?php if (!empty($post['photo_path'])): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($post['photo_path']) ?>" 
                                             alt="<?= cleanAndSecure(($post['prenom'] ?? '') . ' ' . ($post['nom'] ?? '')) ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <?= strtoupper(substr($post['prenom'] ?? 'U', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="post-content">
                                    <div class="post-header">
                                        <h3 class="post-title">
                                            <a href="<?= BASE_URL ?>/forum/show/<?= $post['id'] ?>">
                                                <?= cleanAndSecure($post['title']) ?>
                                            </a>
                                        </h3>
                                        <span class="post-category category-<?= htmlspecialchars($post['category']) ?>">
                                            <?= htmlspecialchars($post['category']) ?>
                                        </span>
                                    </div>
                                    
                                    <p class="post-excerpt">
                                        <?= cleanAndSecure(substr(strip_tags($post['body']), 0, 150)) ?>...
                                    </p>
                                    
                                    <div class="post-meta">
                                        <span class="meta-item">
                                            <i class="fas fa-user"></i>
                                            <a href="<?= BASE_URL ?>/user/profile/<?= $post['user_id'] ?>">
                                                <?= cleanAndSecure(($post['prenom'] ?? 'Utilisateur') . ' ' . ($post['nom'] ?? '')) ?>
                                            </a>
                                        </span>
                                        <span class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            <?= timeAgo($post['created_at']) ?>
                                        </span>
                                        <span class="meta-item">
                                            <i class="fas fa-comments"></i>
                                            <?= $post['comments_count'] ?? 0 ?> réponses
                                        </span>
                                        <span class="meta-item">
                                            <i class="fas fa-eye"></i>
                                            <?= $post['views'] ?? 0 ?> vues
                                        </span>
                                        <?php if ($post['is_trending'] ?? false): ?>
                                            <span class="meta-item trending">
                                                <i class="fas fa-fire"></i> Tendance
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="post-actions">
                                    <a href="<?= BASE_URL ?>/forum/show/<?= $post['id'] ?>" class="btn-read-more">
                                        Lire la suite <i class="fas fa-arrow-right"></i>
                                    </a>
                                    <button class="action-btn like-btn" title="J'aime">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span><?= $post['likes_count'] ?? 0 ?></span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                        <div class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <a href="?page=<?= $i ?>" 
                                   class="page-link <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="page-link">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="forum-sidebar">
                <!-- Trending Topics -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-fire"></i> Topics Tendances</h3>
                    <div class="trending-list">
                        <?php if (isset($trending_topics) && !empty($trending_topics)): ?>
                            <?php foreach ($trending_topics as $topic): ?>
                                <a href="<?= BASE_URL ?>/forum/show/<?= $topic['id'] ?>" class="trending-item">
                                    <span class="trending-number">#<?= $topic['rank'] ?></span>
                                    <span class="trending-title"><?= cleanAndSecure($topic['title']) ?></span>
                                    <span class="trending-count"><?= $topic['views'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Aucun topic tendance</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Top Contributors -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-trophy"></i> Top Contributeurs</h3>
                    <div class="contributors-list">
                        <?php if (isset($top_contributors) && !empty($top_contributors)): ?>
                            <?php foreach ($top_contributors as $contributor): ?>
                                <div class="contributor-item">
                                    <div class="contributor-avatar">
                                        <?php if (!empty($contributor['photo'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($contributor['photo']) ?>" 
                                                 alt="<?= cleanAndSecure($contributor['name']) ?>">
                                        <?php else: ?>
                                            <div class="avatar-placeholder small">
                                                <?= strtoupper(substr($contributor['name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="contributor-info">
                                        <a href="<?= BASE_URL ?>/user/profile/<?= $contributor['id'] ?>">
                                            <?= cleanAndSecure($contributor['name']) ?>
                                        </a>
                                        <span class="contributor-posts"><?= $contributor['posts_count'] ?> posts</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Aucun contributeur</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Forum Rules -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-info-circle"></i> Règles du Forum</h3>
                    <ul class="rules-list">
                        <li><i class="fas fa-check"></i> Restez respectueux</li>
                        <li><i class="fas fa-check"></i> Pas de spam</li>
                        <li><i class="fas fa-check"></i> Contenu pertinent</li>
                        <li><i class="fas fa-check"></i> Pas de harcèlement</li>
                        <li><i class="fas fa-check"></i> Entraide et bienveillance</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
/* Forum Hero */
.forum-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.forum-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
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

.hero-content p {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

/* Stats Section */
.forum-stats-section {
    padding: 40px 0;
    background: #f8f9fa;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.stat-info h3 {
    font-size: 2rem;
    margin: 0;
    color: var(--primary-color);
    font-weight: 700;
}

.stat-info p {
    margin: 5px 0 0;
    color: #6c757d;
    font-size: 0.9rem;
}

/* Categories */
.forum-categories-section {
    padding: 40px 0 20px;
}

.section-title {
    font-size: 1.8rem;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.category-filter {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.filter-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    transform: translateY(-2px);
}

.filter-btn.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

/* Forum Layout */
.forum-posts-section {
    padding: 20px 0 80px;
}

.forum-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

.forum-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.toolbar-left h3 {
    margin: 0;
    font-size: 1.3rem;
}

.sort-select {
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.sort-select:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* Forum Posts */
.forum-posts-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.forum-post-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    display: flex;
    gap: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.forum-post-card:hover {
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transform: translateX(5px);
    border-left-color: var(--primary-color);
}

.post-avatar img,
.post-avatar .avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.post-content {
    flex: 1;
}

.post-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
    gap: 15px;
}

.post-title {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
}

.post-title a {
    color: var(--dark-color);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.post-title a:hover {
    color: var(--primary-color);
    transform: translateX(5px);
}

/* Effet hover sur la carte entière */
.forum-post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15);
}

.forum-post-card:hover .post-title a {
    color: var(--primary-color);
}

.post-category {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
}

.category-programmation { background: #e3f2fd; color: #1976d2; }
.category-reseau { background: #f3e5f5; color: #7b1fa2; }
.category-cybersecurite { background: #ffebee; color: #c62828; }
.category-ia { background: #e8f5e9; color: #2e7d32; }
.category-web { background: #fff3e0; color: #e65100; }
.category-mobile { background: #e0f2f1; color: #00695c; }
.category-design { background: #fce4ec; color: #c2185b; }

.post-excerpt {
    color: #6c757d;
    margin-bottom: 15px;
    line-height: 1.6;
}

.post-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: #6c757d;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.meta-item a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.meta-item a:hover {
    color: var(--primary-color);
}

.meta-item.trending {
    color: #ff5722;
    font-weight: 600;
}

.post-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
}

/* Bouton Lire la suite */
.btn-read-more {
    padding: 12px 25px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.btn-read-more:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
}

.btn-read-more i {
    transition: transform 0.3s ease;
}

.btn-read-more:hover i {
    transform: translateX(5px);
}

/* Actions buttons */
.post-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.action-btn {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    padding: 8px 15px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--dark-color);
    font-weight: 600;
    min-width: auto;
}

.action-btn:hover {
    background: white;
    border-color: var(--primary-color);
    color: var(--primary-color);
    transform: translateY(-2px);
}

.like-btn:hover {
    border-color: #e74c3c;
    color: #e74c3c;
}

/* Sidebar */
.forum-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.sidebar-card h3 {
    margin: 0 0 20px;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.trending-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.trending-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 8px;
    text-decoration: none;
    color: var(--dark-color);
    transition: all 0.3s ease;
}

.trending-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.trending-number {
    font-weight: 700;
    color: var(--primary-color);
    font-size: 1.2rem;
}

.trending-title {
    flex: 1;
    font-size: 0.9rem;
}

.trending-count {
    font-size: 0.8rem;
    color: #6c757d;
}

.contributors-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.contributor-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.contributor-avatar img,
.contributor-avatar .avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder.small {
    width: 40px;
    height: 40px;
    font-size: 1rem;
}

.contributor-info {
    flex: 1;
}

.contributor-info a {
    text-decoration: none;
    color: var(--dark-color);
    font-weight: 600;
    display: block;
    transition: color 0.3s ease;
}

.contributor-info a:hover {
    color: var(--primary-color);
}

.contributor-posts {
    font-size: 0.8rem;
    color: #6c757d;
}

.rules-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.rules-list li {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.rules-list li:last-child {
    border-bottom: none;
}

.rules-list li i {
    color: var(--secondary-color);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
}

.empty-state i {
    font-size: 5rem;
    color: #e9ecef;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin-bottom: 10px;
    color: var(--dark-color);
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 30px;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.page-link {
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 8px;
    text-decoration: none;
    color: var(--dark-color);
    font-weight: 500;
    transition: all 0.3s ease;
}

.page-link:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.page-link.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

/* Responsive */
@media (max-width: 992px) {
    .forum-layout {
        grid-template-columns: 1fr;
    }
    
    .forum-sidebar {
        order: -1;
    }
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .stat-card {
        padding: 20px 16px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .stat-info h3 {
        font-size: 1.5rem;
    }
    
    .stat-info p {
        font-size: 0.85rem;
    }
    
    .forum-post-card {
        flex-direction: column;
    }
    
    .post-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .forum-toolbar {
        flex-direction: column;
        gap: 15px;
    }
}
</style>

<script>
// Category Filter
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.dataset.category;
        const posts = document.querySelectorAll('.forum-post-card');
        
        posts.forEach(post => {
            if (category === 'all' || post.dataset.category === category) {
                post.style.display = 'flex';
                setTimeout(() => {
                    post.style.opacity = '1';
                    post.style.transform = 'translateX(0)';
                }, 10);
            } else {
                post.style.opacity = '0';
                post.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    post.style.display = 'none';
                }, 300);
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

