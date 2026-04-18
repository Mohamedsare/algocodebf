<?php
$pageTitle = htmlspecialchars($post['title'] ?? 'Article') . ' - Blog AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Préparer les données
$post = $post ?? [];
$popular = $popular ?? [];
$related_posts = $related_posts ?? [];
?>

<!-- Article Header Mobile-First -->
<article class="blog-article-page-mobile">
    <!-- Hero Image Mobile-First -->
    <div class="article-hero-mobile">
        <?php 
        // Version ultra-simple pour garantir l'affichage
        $featuredImage = $post['featured_image'] ?? '';
        
        // Toujours avoir une image
        if (empty($featuredImage)) {
            $imageSrc = 'https://via.placeholder.com/400x300/f8f9fa/666666?text=Image+de+Blog';
        } else {
            // Si l'image commence par http, c'est une URL complète
            if (strpos($featuredImage, 'http') === 0) {
                $imageSrc = $featuredImage;
            } else {
                // Sinon, construire le chemin complet
                $imageSrc = BASE_URL . '/' . ltrim($featuredImage, '/');
            }
        }
        ?>
        <img src="<?= $imageSrc ?? 'https://via.placeholder.com/400x300/f8f9fa/666666?text=Image+de+Blog' ?>" 
             alt="<?= htmlspecialchars($post['title'] ?? 'Article', ENT_QUOTES, 'UTF-8') ?>" 
             class="hero-image-mobile"
             onerror="this.src='https://via.placeholder.com/400x300/f8f9fa/666666?text=Image+de+Blog'"
             loading="lazy">
        
        <div class="hero-overlay-mobile"></div>
        
        <!-- Hero Content Mobile -->
        <div class="hero-content-mobile">
            <span class="category-badge-mobile cat-<?= strtolower($post['category'] ?? 'actualites') ?>">
                    <?= htmlspecialchars($post['category'] ?? 'Actualités') ?>
                </span>
            
            <h1 class="article-title-mobile"><?= htmlspecialchars($post['title'] ?? 'Titre de l\'article', ENT_QUOTES, 'UTF-8') ?></h1>
            
            <!-- Meta Mobile -->
            <div class="article-meta-mobile">
                <div class="meta-author-mobile">
                    <?php 
                    $authorPhoto = $post['author_photo'] ?? '';
                    $authorName = $post['author_name'] ?? 'Auteur';
                    $authorInitial = strtoupper(substr($authorName, 0, 1));
                    ?>
                    <div class="author-avatar-mobile">
                        <?php if (!empty($authorPhoto) && file_exists(ROOT . '/' . $authorPhoto)): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($authorPhoto) ?>" alt="<?= htmlspecialchars($authorName) ?>">
                            <?php else: ?>
                            <div class="avatar-placeholder-mobile"><?= $authorInitial ?></div>
                            <?php endif; ?>
                        </div>
                    <div class="author-info-mobile">
                            <strong><?= htmlspecialchars($authorName) ?></strong>
                        <span><?= timeAgo($post['created_at'] ?? date('Y-m-d H:i:s')) ?></span>
                        </div>
                    </div>
                
                <div class="meta-stats-mobile">
                        <span><i class="far fa-clock"></i> <?= ceil(str_word_count($post['content'] ?? '') / 200) ?> min</span>
                    <span><i class="far fa-eye"></i> <?= formatNumber($post['views'] ?? 0) ?></span>
                        <span><i class="far fa-heart"></i> <span id="likesCount"><?= $post['likes_count'] ?? 0 ?></span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Mobile-First -->
    <div class="container-mobile">
        <div class="article-layout-mobile">
            <!-- Article Content -->
            <main class="article-main-mobile">
                <!-- Action Bar Mobile -->
                <div class="actions-bar-mobile">
                    <div class="actions-left-mobile">
                        <button class="btn-action-mobile <?= ($post['user_liked'] ?? false) ? 'active' : '' ?>" 
                                id="likeBtn"
                                onclick="toggleLike()">
                            <i class="<?= ($post['user_liked'] ?? false) ? 'fas' : 'far' ?> fa-heart"></i>
                            <span id="likeText"><?= ($post['user_liked'] ?? false) ? 'Aimé' : 'Aimer' ?></span>
                        </button>
                        <button class="btn-action-mobile" onclick="shareArticle()">
                            <i class="fas fa-share-alt"></i>
                        </button>
                        <button class="btn-action-mobile" onclick="bookmarkArticle()">
                            <i class="far fa-bookmark"></i>
                        </button>
                    </div>
                    <div class="actions-right-mobile">
                        <?php if ($this->isLoggedIn() && ($_SESSION['user_role'] === 'admin' || $post['author_id'] == $_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/blog/edit/<?= $post['slug'] ?? '' ?>" class="btn-action-mobile btn-edit-mobile">
                                <i class="fas fa-edit"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Article Content Mobile -->
                <div class="article-content-mobile">
                    <?= markdownToHtml($post['content'] ?? '') ?>
                </div>

                <!-- Tags Mobile -->
                <?php if (!empty($post['tags']) && $post['tags'] !== ''): ?>
                <div class="article-tags-mobile">
                    <h4><i class="fas fa-tags"></i> Tags</h4>
                    <div class="tags-list-mobile">
                        <?php foreach (explode(',', $post['tags']) as $tag): ?>
                            <?php if (trim($tag) !== ''): ?>
                            <a href="<?= BASE_URL ?>/blog/index?tag=<?= urlencode(trim($tag)) ?>" class="tag-badge-mobile">
                                <?= htmlspecialchars(trim($tag)) ?>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Author Box Mobile -->
                <div class="author-box-mobile">
                    <div class="author-box-header-mobile">
                        <div class="author-box-avatar-mobile">
                            <?php if (!empty($authorPhoto) && file_exists(ROOT . '/' . $authorPhoto)): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($authorPhoto) ?>" alt="<?= htmlspecialchars($authorName) ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder-large-mobile"><?= $authorInitial ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="author-box-info-mobile">
                            <h3><?= htmlspecialchars($authorName) ?></h3>
                            <p>Auteur de l'article</p>
                        </div>
                    </div>
                    <div class="author-box-actions-mobile">
                        <button class="btn-follow-mobile">
                            <i class="fas fa-user-plus"></i> Suivre
                        </button>
                    </div>
                </div>
            </main>

            <!-- Sidebar Mobile (collapsible) -->
            <aside class="article-sidebar-mobile">
                <!-- Popular Articles Mobile -->
                <div class="sidebar-section-mobile">
                    <h3><i class="fas fa-fire"></i> Articles Populaires</h3>
                    <div class="popular-articles-mobile">
                        <?php if (!empty($popular) && count($popular) > 0): ?>
                            <?php foreach ($popular as $popularPost): ?>
                                <div class="popular-article-mobile">
                                    <div class="popular-article-image-mobile">
                                        <?php 
                                        $popImage = $popularPost['featured_image'] ?? '';
                                        if (empty($popImage)) {
                                            $popImageSrc = 'https://via.placeholder.com/60x60/f8f9fa/666666?text=Blog';
                                        } else {
                                            if (strpos($popImage, 'http') === 0) {
                                                $popImageSrc = $popImage;
                                            } else {
                                                $popImageSrc = BASE_URL . '/' . $popImage;
                                            }
                                        }
                                        ?>
                                        <img src="<?= $popImageSrc ?>" 
                                             alt="<?= htmlspecialchars($popularPost['title']) ?>"
                                             onerror="this.src='https://via.placeholder.com/60x60/f8f9fa/666666?text=Blog'"
                                             loading="lazy">
                                    </div>
                                    <div class="popular-article-content-mobile">
                                        <h4>
                                            <a href="<?= BASE_URL ?>/blog/show/<?= $popularPost['slug'] ?? $popularPost['id'] ?>">
                                                <?= htmlspecialchars($popularPost['title']) ?>
                                            </a>
                                        </h4>
                                        <div class="popular-article-meta-mobile">
                                            <span><i class="far fa-eye"></i> <?= formatNumber($popularPost['views'] ?? 0) ?></span>
                                            <span><i class="far fa-heart"></i> <?= $popularPost['likes_count'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-articles-mobile">
                                <p><i class="fas fa-info-circle"></i> Aucun article populaire pour le moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Related Articles Mobile -->
                <?php if (!empty($related_posts)): ?>
                <div class="sidebar-section-mobile">
                    <h3><i class="fas fa-link"></i> Articles Similaires</h3>
                    <div class="related-articles-mobile">
                        <?php foreach ($related_posts as $relatedPost): ?>
                            <div class="related-article-mobile">
                                <div class="related-article-image-mobile">
                                    <?php 
                                    $relImage = $relatedPost['featured_image'] ?? '';
                                    if (empty($relImage)) {
                                        $relImageSrc = 'https://via.placeholder.com/60x60/f8f9fa/666666?text=Blog';
                                    } else {
                                        if (strpos($relImage, 'http') === 0) {
                                            $relImageSrc = $relImage;
                                        } else {
                                            $relImageSrc = BASE_URL . '/' . $relImage;
                                        }
                                    }
                                    ?>
                                    <img src="<?= $relImageSrc ?>" 
                                         alt="<?= htmlspecialchars($relatedPost['title']) ?>"
                                         onerror="this.src='https://via.placeholder.com/60x60/f8f9fa/666666?text=Blog'"
                                         loading="lazy">
                                </div>
                                <div class="related-article-content-mobile">
                                    <h4>
                                        <a href="<?= BASE_URL ?>/blog/show/<?= $relatedPost['slug'] ?>">
                                            <?= htmlspecialchars($relatedPost['title']) ?>
                                        </a>
                                    </h4>
                                    <span class="related-article-category-mobile">
                                        <?= htmlspecialchars($relatedPost['category']) ?>
                                    </span>
                    </div>
                </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</article>

<!-- CSS Mobile-First -->
<style>
/* Reset et Base Mobile */
* {
    box-sizing: border-box;
}

.blog-article-page-mobile {
    min-height: 100vh;
    background: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.container-mobile {
    max-width: 100%;
    padding: 0 10px;
    margin: 0 auto;
}

/* Hero Section Mobile - DESIGN MODERNE ET JOLI */
.article-hero-mobile {
    position: relative;
    width: 100%;
    height: 280px;
    overflow: hidden;
    margin-bottom: 20px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.hero-image-mobile {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center center;
    display: block;
    background: linear-gradient(135deg, #38a169 0%, #e53e3e 100%);
    border: none;
    outline: none;
    transition: transform 0.3s ease;
}

.article-hero-mobile:hover .hero-image-mobile {
    transform: scale(1.05);
}

.hero-overlay-mobile {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.7) 100%);
    backdrop-filter: blur(1px);
}

.hero-content-mobile {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 25px 20px;
    color: white;
    z-index: 2;
    background: linear-gradient(transparent, rgba(0,0,0,0.3));
}

.category-badge-mobile {
    display: inline-block;
    padding: 8px 16px;
    background: linear-gradient(135deg, #38a169, #e53e3e);
    color: white;
    border-radius: 25px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 15px;
    box-shadow: 0 4px 15px rgba(56, 161, 105, 0.3);
    letter-spacing: 0.5px;
}

.article-title-mobile {
    font-size: 28px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0 0 20px 0;
    color: white;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
    background: linear-gradient(135deg, #ffffff, #f1f2f6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.article-meta-mobile {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.meta-author-mobile {
    display: flex;
    align-items: center;
    gap: 10px;
}

.author-avatar-mobile {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.author-avatar-mobile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-mobile {
    width: 100%;
    height: 100%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}

.author-info-mobile {
    flex: 1;
}

.author-info-mobile strong {
    display: block;
    color: white;
    font-size: 14px;
    font-weight: 600;
}

.author-info-mobile span {
    color: rgba(255,255,255,0.8);
    font-size: 12px;
}

.meta-stats-mobile {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.meta-stats-mobile span {
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Layout Mobile - DESIGN MODERNE ET JOLI */
.article-layout-mobile {
    display: block;
    width: 100%;
}

.article-main-mobile {
    width: 100%;
    background: white;
    border-radius: 20px;
    padding: 25px 20px;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    position: relative;
    overflow: hidden;
}

.article-main-mobile::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #38a169 0%, #e53e3e 100%);
}

/* Actions Bar Mobile - VRAIMENT MOBILE */
.actions-bar-mobile {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.actions-left-mobile {
    display: flex;
    gap: 8px;
    flex: 1;
}

.actions-right-mobile {
    display: flex;
    gap: 8px;
}

.btn-action-mobile {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border: none;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 25px;
    color: #495057;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 40px;
    white-space: nowrap;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-action-mobile:hover {
    background: linear-gradient(135deg, #38a169, #e53e3e);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(56, 161, 105, 0.3);
}

.btn-action-mobile.active {
    background: linear-gradient(135deg, #38a169, #e53e3e);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(56, 161, 105, 0.3);
}

.btn-edit-mobile {
    background: linear-gradient(135deg, #38a169, #e53e3e);
    color: white;
    box-shadow: 0 2px 10px rgba(56, 161, 105, 0.3);
}

/* Article Content Mobile - VRAIMENT MOBILE */
.article-content-mobile {
    line-height: 1.6;
    color: #333;
    font-size: 14px;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.article-content-mobile h1,
.article-content-mobile h2,
.article-content-mobile h3,
.article-content-mobile h4,
.article-content-mobile h5,
.article-content-mobile h6 {
    color: #333;
    margin: 20px 0 10px 0;
    font-weight: 700;
    line-height: 1.3;
    word-wrap: break-word;
}

.article-content-mobile h1 { font-size: 22px; }
.article-content-mobile h2 { font-size: 20px; }
.article-content-mobile h3 { font-size: 18px; }
.article-content-mobile h4 { font-size: 16px; }

.article-content-mobile p {
    margin: 12px 0;
    font-size: 14px;
    line-height: 1.6;
    word-wrap: break-word;
}

.article-content-mobile img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 15px 0;
    display: block;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.article-content-mobile blockquote {
    border-left: 4px solid var(--primary-color);
    padding: 15px 20px;
    margin: 20px 0;
    background: #f8f9fa;
    border-radius: 0 10px 10px 0;
}

.article-content-mobile ul,
.article-content-mobile ol {
    padding-left: 20px;
    margin: 15px 0;
}

.article-content-mobile li {
    margin: 8px 0;
}

.article-content-mobile code {
    background: #f1f3f4;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
}

.article-content-mobile pre {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    overflow-x: auto;
    margin: 20px 0;
    border: 1px solid #e9ecef;
}

.article-content-mobile pre code {
    background: none;
    padding: 0;
}

/* Tags Mobile */
.article-tags-mobile {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.article-tags-mobile h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tags-list-mobile {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tag-badge-mobile {
    padding: 6px 12px;
    background: #f1f3f4;
    color: #666;
    border-radius: 20px;
    font-size: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.tag-badge-mobile:hover {
    background: linear-gradient(135deg, #38a169, #e53e3e);
    color: white;
}

/* Author Box Mobile */
.author-box-mobile {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 15px;
    border: 1px solid #e9ecef;
}

.author-box-header-mobile {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.author-box-avatar-mobile {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.author-box-avatar-mobile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-large-mobile {
    width: 100%;
    height: 100%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
}

.author-box-info-mobile h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.author-box-info-mobile p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.author-box-actions-mobile {
    display: flex;
    gap: 10px;
}

.btn-follow-mobile {
    padding: 8px 16px;
    background: linear-gradient(135deg, #38a169, #e53e3e);
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(56, 161, 105, 0.3);
}

.btn-follow-mobile:hover {
    background: linear-gradient(135deg, #e53e3e, #38a169);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(56, 161, 105, 0.4);
}

/* Sidebar Mobile - DESIGN MODERNE */
.article-sidebar-mobile {
    background: white;
    border-radius: 20px;
    padding: 25px 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    margin-top: 20px;
    position: relative;
    overflow: hidden;
}

.article-sidebar-mobile::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #38a169, #e53e3e);
}

.sidebar-section-mobile {
    margin-bottom: 30px;
}

.sidebar-section-mobile:last-child {
    margin-bottom: 0;
}

.sidebar-section-mobile h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Popular Articles Mobile */
.popular-articles-mobile {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.popular-article-mobile {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 15px;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    margin-bottom: 10px;
}

.popular-article-mobile:hover {
    background: linear-gradient(135deg, #e9ecef, #f8f9fa);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.popular-article-image-mobile {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f8f9fa;
}

.popular-article-image-mobile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border: none;
}

.popular-article-content-mobile {
    flex: 1;
}

.popular-article-content-mobile h4 {
    margin: 0 0 8px 0;
    font-size: 14px;
    line-height: 1.4;
}

.popular-article-content-mobile h4 a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.popular-article-content-mobile h4 a:hover {
    background: linear-gradient(135deg, #38a169, #e53e3e);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.popular-article-meta-mobile {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: #666;
}

.popular-article-meta-mobile span {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Related Articles Mobile */
.related-articles-mobile {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.related-article-mobile {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 15px;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    margin-bottom: 10px;
}

.related-article-mobile:hover {
    background: linear-gradient(135deg, #e9ecef, #f8f9fa);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.related-article-image-mobile {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f8f9fa;
}

.related-article-image-mobile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border: none;
}

.related-article-content-mobile {
    flex: 1;
}

.related-article-content-mobile h4 {
    margin: 0 0 8px 0;
    font-size: 14px;
    line-height: 1.4;
}

.related-article-content-mobile h4 a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.related-article-content-mobile h4 a:hover {
    background: linear-gradient(135deg, #38a169, #e53e3e);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.related-article-category-mobile {
    font-size: 12px;
    color: #d69e2e;
    background: rgba(214, 158, 46, 0.1);
    padding: 4px 8px;
    border-radius: 12px;
    display: inline-block;
}

.no-articles-mobile {
    text-align: center;
    padding: 20px;
    color: #666;
    font-style: italic;
}

.no-articles-mobile i {
    color: #999;
    margin-right: 8px;
}

/* Tablet Styles - DESIGN MODERNE */
@media (min-width: 768px) {
    .container-mobile {
        max-width: 750px;
        padding: 0 20px;
    }
    
    .article-hero-mobile {
        height: 350px;
        border-radius: 20px;
        margin-bottom: 30px;
    }
    
    .article-title-mobile {
        font-size: 32px;
    }
    
    .article-meta-mobile {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
    
    .article-layout-mobile {
        display: flex;
        flex-direction: row;
        gap: 30px;
    }
    
    .article-main-mobile {
        flex: 2;
        border-radius: 20px;
        padding: 30px 25px;
    }
    
    .article-sidebar-mobile {
        flex: 1;
        max-width: 350px;
        border-radius: 20px;
        margin-top: 0;
        padding: 30px 25px;
    }
    
    .article-content-mobile {
        font-size: 16px;
    }
    
    .article-content-mobile h1 { font-size: 28px; }
    .article-content-mobile h2 { font-size: 26px; }
    .article-content-mobile h3 { font-size: 22px; }
    .article-content-mobile h4 { font-size: 20px; }
}

@media (min-width: 1024px) {
    .container-mobile {
        max-width: 1200px;
        padding: 0 40px;
    }
    
    .article-hero-mobile {
        height: 450px;
        border-radius: 25px;
    }
    
    .article-title-mobile {
        font-size: 36px;
    }
    
    .article-main-mobile {
        padding: 40px 35px;
        border-radius: 25px;
    }
    
    .article-sidebar-mobile {
        padding: 40px 35px;
        border-radius: 25px;
        max-width: 400px;
    }
    
    .article-content-mobile {
        font-size: 18px;
    }
    
    .article-content-mobile h1 { font-size: 32px; }
    .article-content-mobile h2 { font-size: 28px; }
    .article-content-mobile h3 { font-size: 24px; }
    .article-content-mobile h4 { font-size: 22px; }
}
</style>

<!-- JavaScript -->
<script>
function toggleLike() {
    const likeBtn = document.getElementById('likeBtn');
    const likeText = document.getElementById('likeText');
    const likesCount = document.getElementById('likesCount');
    
    // Animation immédiate
    likeBtn.style.transform = 'scale(0.95)';
    setTimeout(() => {
        likeBtn.style.transform = 'scale(1)';
    }, 150);
    
    // Toggle state
    const isActive = likeBtn.classList.contains('active');
    
    if (isActive) {
        likeBtn.classList.remove('active');
        likeBtn.querySelector('i').className = 'far fa-heart';
        likeText.textContent = 'Aimer';
        likesCount.textContent = parseInt(likesCount.textContent) - 1;
    } else {
        likeBtn.classList.add('active');
        likeBtn.querySelector('i').className = 'fas fa-heart';
        likeText.textContent = 'Aimé';
        likesCount.textContent = parseInt(likesCount.textContent) + 1;
    }
    
    // AJAX call pour sauvegarder
    fetch('<?= BASE_URL ?>/blog/like/<?= $post['id'] ?? 0 ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .catch(error => {
        console.error('Erreur lors du like:', error);
        // Revert changes on error
        if (isActive) {
            likeBtn.classList.add('active');
            likeBtn.querySelector('i').className = 'fas fa-heart';
            likeText.textContent = 'Aimé';
            likesCount.textContent = parseInt(likesCount.textContent) + 1;
            } else {
            likeBtn.classList.remove('active');
            likeBtn.querySelector('i').className = 'far fa-heart';
            likeText.textContent = 'Aimer';
            likesCount.textContent = parseInt(likesCount.textContent) - 1;
        }
    });
}

function shareArticle() {
    if (navigator.share) {
        navigator.share({
            title: '<?= addslashes($post['title'] ?? '') ?>',
            text: '<?= addslashes(substr($post['excerpt'] ?? '', 0, 100)) ?>',
            url: window.location.href
        });
    } else {
        // Fallback: copier l'URL
    navigator.clipboard.writeText(window.location.href).then(() => {
            alert('✅ Lien copié dans le presse-papiers !');
    });
    }
}

function bookmarkArticle() {
    alert('📚 Fonctionnalité de sauvegarde bientôt disponible !');
}

// Smooth scroll pour les ancres
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Lazy loading pour les images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>