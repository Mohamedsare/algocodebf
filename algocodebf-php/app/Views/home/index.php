<?php require_once VIEWS . '/layouts/header.php'; ?>

<!-- Section Hero Carousel -->
<section class="hero-carousel">
    <div class="carousel-container">
        <!-- Slide 1 -->
        <div class="carousel-slide active">
            <div class="slide-content">
                <div class="slide-left">
                    <div class="slide-text">
                        <span class="slide-badge">🚀 Communauté Tech</span>
                        <h1 class="slide-title">Bienvenue sur AlgoCodeBF</h1>
                        <p class="slide-description">Le hub numérique qui rassemble tous les informaticiens,
                            développeurs et passionnés de technologie du Burkina Faso</p>
                        <div class="slide-buttons">
                            <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/auth/register" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Rejoindre gratuitement
                            </a>
                            <a href="<?= BASE_URL ?>/auth/login" class="btn btn-secondary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Se connecter
                            </a>
                            <?php else: ?>
                            <a href="<?= BASE_URL ?>/forum/index" class="btn btn-primary btn-lg">
                                <i class="fas fa-comments"></i> Accéder au Forum
                            </a>
                            <a href="<?= BASE_URL ?>/tutorial/index" class="btn btn-secondary btn-lg">
                                <i class="fas fa-book"></i> Voir les Tutoriels
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="slide-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['total_users'] ?>+</span>
                                <span class="stat-label">Membres</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['total_posts'] ?>+</span>
                                <span class="stat-label">Discussions</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['total_tutorials'] ?>+</span>
                                <span class="stat-label">Tutoriels</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slide-right">
                    <div class="slide-image">
                        <img src="<?= BASE_URL ?>/public/images/im1.png" alt="Communauté Tech"
                            onerror="this.src='https://img.freepik.com/free-vector/teamwork-concept-landing-page_52683-20165.jpg'">
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="carousel-slide">
            <div class="slide-content">
                <div class="slide-left">
                    <div class="slide-text">
                        <span class="slide-badge">💼 Opportunités</span>
                        <h1 class="slide-title">Boostez votre carrière</h1>
                        <p class="slide-description">Découvrez des opportunités d'emploi, des stages et des projets
                            freelance dans le domaine tech au Burkina Faso</p>
                        <div class="slide-buttons">
                            <a href="<?= BASE_URL ?>/job/index" class="btn btn-primary btn-lg">
                                <i class="fas fa-briefcase"></i> Voir les offres
                            </a>
                            <a href="<?= BASE_URL ?>/project/index" class="btn btn-secondary btn-lg">
                                <i class="fas fa-project-diagram"></i> Explorer les projets
                            </a>
                        </div>
                    </div>
                </div>
                <div class="slide-right">
                    <div class="slide-image">
                        <img src="<?= BASE_URL ?>/public/images/im2.jpg" alt="Carrière Tech"
                            onerror="this.src='https://img.freepik.com/free-vector/career-progress-concept-illustration_114360-5277.jpg'">
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="carousel-slide">
            <div class="slide-content">
                <div class="slide-left">
                    <div class="slide-text">
                        <span class="slide-badge">📚 Apprentissage</span>
                        <h1 class="slide-title">Apprenez et progressez</h1>
                        <p class="slide-description">Accédez à des tutoriels, des cours et des ressources partagées par
                            la communauté pour développer vos compétences</p>
                        <div class="slide-buttons">
                            <a href="<?= BASE_URL ?>/tutorial/index" class="btn btn-primary btn-lg">
                                <i class="fas fa-graduation-cap"></i> Commencer à apprendre
                            </a>
                            <a href="<?= BASE_URL ?>/blog/index" class="btn btn-secondary btn-lg">
                                <i class="fas fa-newspaper"></i> Lire le blog
                            </a>
                        </div>
                    </div>
                </div>
                <div class="slide-right">
                    <div class="slide-image">
                        <img src="<?= BASE_URL ?>/public/images/im.png" alt="Apprentissage"
                            onerror="this.src='https://img.freepik.com/free-vector/online-tutorials-concept_52683-37481.jpg'">
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation du carousel -->
        <button class="carousel-nav carousel-prev" onclick="moveSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="carousel-nav carousel-next" onclick="moveSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Indicateurs -->
        <div class="carousel-indicators">
            <span class="indicator active" onclick="goToSlide(0)"></span>
            <span class="indicator" onclick="goToSlide(1)"></span>
            <span class="indicator" onclick="goToSlide(2)"></span>
        </div>
    </div>
</section>

<!-- Statistiques -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Membres</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-comments"></i></div>
                <div class="stat-number"><?= $stats['total_posts'] ?></div>
                <div class="stat-label">Discussions</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div class="stat-number"><?= $stats['total_tutorials'] ?></div>
                <div class="stat-label">Tutoriels</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-project-diagram"></i></div>
                <div class="stat-number"><?= $stats['total_projects'] ?></div>
                <div class="stat-label">Projets</div>
            </div>
        </div>
    </div>
</section>

<!-- Discussions récentes -->
<section class="content-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-comments"></i> Discussions récentes</h2>
            <a href="<?= BASE_URL ?>/forum/index" class="btn btn-outline">Voir tout</a>
        </div>

        <div class="posts-grid">
            <?php if (!empty($recent_posts)): ?>
            <?php foreach ($recent_posts as $post): ?>
            <div class="post-card">
                <div class="post-header">
                    <div class="post-author">
                        <?php if (!empty($post['photo_path']) && file_exists($post['photo_path'])): ?>
                        <img src="<?= BASE_URL ?>/<?= $post['photo_path'] ?>"
                            alt="<?= cleanAndSecure($post['prenom'] . ' ' . $post['nom']) ?>" class="avatar-xs">
                        <?php else: ?>
                        <div class="avatar-xs avatar-placeholder-xs">
                            <?= strtoupper(substr($post['prenom'] ?? 'U', 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                        <div>
                            <strong><?= cleanAndSecure($post['prenom'] . ' ' . $post['nom']) ?></strong>
                            <span class="post-date"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></span>
                        </div>
                    </div>
                    <span class="badge badge-primary"><?= htmlspecialchars($post['category']) ?></span>
                </div>
                <h3><a href="<?= BASE_URL ?>/forum/show/<?= $post['id'] ?>"><?= cleanAndSecure($post['title']) ?></a>
                </h3>
                <p><?= cleanAndSecure(mb_substr(strip_tags($post['body']), 0, 150)) ?>...</p>
                <div class="post-footer">
                    <div class="post-stats">
                        <span><i class="fas fa-eye"></i> <?= $post['views'] ?></span>
                        <span><i class="fas fa-comments"></i> <?= $post['comments_count'] ?></span>
                        <span><i class="fas fa-heart"></i> <?= $post['likes_count'] ?></span>
                    </div>
                    <div class="post-actions">
                        <a href="<?= BASE_URL ?>/forum/show/<?= $post['id'] ?>" class="btn-reply" title="Répondre">
                            <i class="fas fa-reply"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>Aucune discussion pour le moment. Soyez le premier à en créer une!</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Tutoriels populaires -->
<section class="content-section bg-light">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-book"></i> Tutoriels populaires</h2>
            <a href="<?= BASE_URL ?>/tutorial/index" class="btn btn-outline">Voir tout</a>
        </div>

        <div class="tutorials-grid">
            <?php if (!empty($popular_tutorials)): ?>
            <?php foreach ($popular_tutorials as $tutorial): ?>
            <div class="tutorial-card">
                <div class="tutorial-type">
                    <?php
                            $icon = match($tutorial['type']) {
                                'video' => 'fa-video',
                                'pdf' => 'fa-file-pdf',
                                'code' => 'fa-code',
                                'article' => 'fa-newspaper',
                                default => 'fa-book'
                            };
                            ?>
                    <i class="fas <?= $icon ?>"></i>
                </div>
                <h3><a
                        href="<?= BASE_URL ?>/tutorial/show/<?= $tutorial['id'] ?>"><?= cleanAndSecure($tutorial['title']) ?></a>
                </h3>
                <p><?= cleanAndSecure(mb_substr(strip_tags($tutorial['description']), 0, 100)) ?>...</p>
                <div class="tutorial-footer">
                    <div class="tutorial-author">
                        <?php if (!empty($tutorial['photo_path']) && file_exists($tutorial['photo_path'])): ?>
                        <img src="<?= BASE_URL ?>/<?= $tutorial['photo_path'] ?>"
                            alt="<?= cleanAndSecure($tutorial['prenom'] . ' ' . $tutorial['nom']) ?>" class="avatar-xs">
                        <?php else: ?>
                        <div class="avatar-xs avatar-placeholder-xs">
                            <?= strtoupper(substr($tutorial['prenom'] ?? 'U', 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                        <span><?= cleanAndSecure($tutorial['prenom'] . ' ' . $tutorial['nom']) ?></span>
                    </div>
                    <div class="tutorial-stats">
                        <span><i class="fas fa-eye"></i> <?= $tutorial['views'] ?></span>
                        <span><i class="fas fa-heart"></i> <?= $tutorial['likes_count'] ?></span>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/tutorial/show/<?= $tutorial['id'] ?>" class="btn-view-tutorial">
                    <i class="fas fa-arrow-right"></i> Voir le tutoriel
                </a>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>Aucun tutoriel pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Articles de blog récents -->
<?php if (!empty($recent_blogs)): ?>
<section class="content-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-newspaper"></i> Actualités & Blog</h2>
            <a href="<?= BASE_URL ?>/blog/index" class="btn btn-outline">Voir tout</a>
        </div>

        <div class="blog-grid">
            <?php foreach ($recent_blogs as $blog): ?>
            <div class="blog-card">
                <?php if (!empty($blog['featured_image'])): ?>
                <div class="blog-image">
                    <img src="<?= BASE_URL ?>/<?= $blog['featured_image'] ?>"
                        alt="<?= cleanAndSecure($blog['title']) ?>">
                </div>
                <?php endif; ?>
                <div class="blog-content">
                    <span class="badge badge-secondary"><?= htmlspecialchars($blog['category']) ?></span>
                    <h3><a
                            href="<?= BASE_URL ?>/blog/show/<?= $blog['slug'] ?>"><?= cleanAndSecure($blog['title']) ?></a>
                    </h3>
                    <p><?= cleanAndSecure($blog['excerpt']) ?></p>
                    <div class="blog-footer">
                        <span class="blog-author"><?= cleanAndSecure($blog['prenom'] . ' ' . $blog['nom']) ?></span>
                        <span class="blog-date"><?= date('d M Y', strtotime($blog['published_at'])) ?></span>
                    </div>
                    <div class="blog-actions">
                        <a href="<?= BASE_URL ?>/blog/show/<?= $blog['slug'] ?>" class="btn-read-more">
                            <i class="fas fa-arrow-right"></i> Lire la suite
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Appel à l'action -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Prêt à rejoindre la communauté tech du Burkina Faso ?</h2>
            <p>Connectez-vous avec des développeurs, designers, et professionnels de l'IT de tout le pays</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/auth/register" class="btn btn-primary btn-lg">REJOIGNEZ NOUS MAINTENANT</a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/project/create" class="btn btn-primary btn-lg">REJOIGNEZ NOUS MAINTENANT</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once VIEWS . '/layouts/footer.php'; ?>