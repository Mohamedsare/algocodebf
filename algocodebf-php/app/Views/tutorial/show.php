<?php
// Initialiser les variables pour éviter les warnings
$chapters = $chapters ?? [];
$videos = $videos ?? [];
$tags = $tags ?? [];
$has_liked = $has_liked ?? false;
$csrf_token = $csrf_token ?? '';

$siteName = $GLOBALS['site_settings']['site_name'] ?? 'AlgoCodeBF';
$siteDescription = $GLOBALS['site_settings']['site_description'] ?? 'Hub numérique des informaticiens du Burkina Faso';
$pageTitle = $tutorial['title'] . ' - Tutoriels - ' . $siteName;
$pageDescription = $siteDescription;
$pageKeywords = $GLOBALS['site_settings']['site_keywords'] ?? 'développement, programmation, tutoriels, projets';
$pageAuthor = $GLOBALS['site_settings']['site_author'] ?? '';
$pageImage = $GLOBALS['site_settings']['site_image'] ?? 'https://via.placeholder.com/400x300/f8f9fa/666666?text=Image+de+Tutoriel';
$pageUrl = BASE_URL . '/tutorial/show/' . ($tutorial['id'] ?? '');
$pageType = 'article';
$pagePublishedAt = $GLOBALS['site_settings']['site_published_at'] ?? '';
$pageUpdatedAt = $GLOBALS['site_settings']['site_updated_at'] ?? '';
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="tutorial-show-section">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-nav">
            <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= BASE_URL ?>/tutorial/index">Tutoriels</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= htmlspecialchars($tutorial['title']) ?></span>
        </div>

        <div class="tutorial-layout">
            <!-- Main Content -->
            <div class="tutorial-main">
                <!-- Header -->
                <article class="tutorial-header">
                    <div class="tutorial-meta-top">
                        <span class="tutorial-type type-<?= strtolower($tutorial['type'] ?? 'text') ?>">
                            <?php
                            $typeIcons = [
                                'video' => '🎥',
                                'text' => '📝',
                                'pdf' => '📄',
                                'code' => '💻',
                                'mixed' => '🔀'
                            ];
                            echo $typeIcons[$tutorial['type']] ?? '📝';
                            ?>
                            <?= htmlspecialchars($tutorial['type'] ?? 'Texte') ?>
                        </span>
                        <span class="tutorial-category">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($tutorial['category'] ?? 'Général') ?>
                        </span>
                    </div>

                    <h1 class="tutorial-title"><?= htmlspecialchars($tutorial['title']) ?></h1>

                    <p class="tutorial-description"><?= htmlspecialchars($tutorial['description']) ?></p>

                    <!-- Author Info -->
                    <div class="author-section">
                        <div class="author-info">
                            <?php
                            $authorPhoto = !empty($tutorial['photo_path']) ? BASE_URL . '/' . $tutorial['photo_path'] : BASE_URL . '/public/images/default-avatar.png';
                            $authorInitial = strtoupper(substr($tutorial['prenom'] ?? 'U', 0, 1));
                            ?>
                            <div class="author-avatar">
                                <?php if (!empty($tutorial['photo_path']) && file_exists($tutorial['photo_path'])): ?>
                                    <img src="<?= $authorPhoto ?>"
                                        alt="<?= htmlspecialchars($tutorial['prenom'] ?? 'Utilisateur') ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder"><?= $authorInitial ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="author-details">
                                <p class="author-name">
                                    Par <strong><?= htmlspecialchars($tutorial['prenom'] ?? 'Utilisateur') ?>
                                        <?= htmlspecialchars($tutorial['nom'] ?? '') ?></strong>
                                </p>
                                <p class="tutorial-date">
                                    <i class="far fa-clock"></i> Publié le
                                    <?= date('d/m/Y à H:i', strtotime($tutorial['created_at'])) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="tutorial-stats">
                            <div class="stat-item">
                                <i class="fas fa-eye"></i>
                                <span><?= number_format($tutorial['views'] ?? 0) ?></span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-heart"></i>
                                <span><?= number_format($tutorial['likes_count'] ?? 0) ?></span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-comment"></i>
                                <span><?= number_format($tutorial['comments_count'] ?? 0) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="tutorial-actions">
                        <button class="btn-action btn-like <?= ($has_liked ?? false) ? 'liked' : '' ?>" onclick="toggleLike()">
                            <i class="<?= ($has_liked ?? false) ? 'fas' : 'far' ?> fa-heart"></i>
                            <span><?= ($has_liked ?? false) ? 'Aimé' : 'Aimer' ?></span>
                        </button>
                        <button class="btn-action"
                            onclick="document.getElementById('comments').scrollIntoView({behavior: 'smooth'})">
                            <i class="far fa-comment"></i>
                            <span>Commenter</span>
                        </button>
                        <button class="btn-action" onclick="shareContent()">
                            <i class="fas fa-share-alt"></i>
                            <span>Partager</span>
                        </button>
                    </div>
                </article>

                <!-- Sommaire/Chapitres (si disponible) -->
                <?php if (!empty($chapters)): ?>
                    <div class="tutorial-chapters-section" id="sommaire-section">
                        <h3 class="chapters-title">
                            <i class="fas fa-list-ol"></i> Sommaire de la formation
                        </h3>
                        <div class="chapters-list">
                            <?php foreach ($chapters as $index => $chapter): ?>
                                <?php
                                // Déterminer l'ID de la vidéo à utiliser
                                // Si le chapitre a un video_id, l'utiliser
                                // Sinon, utiliser l'index pour faire correspondre avec l'ordre des vidéos
                                $targetVideoId = null;
                                if (!empty($chapter['video_id'])) {
                                    $targetVideoId = $chapter['video_id'];
                                } elseif (!empty($videos) && isset($videos[$index])) {
                                    // Faire correspondre par index : chapitre 1 -> vidéo 1, etc.
                                    $targetVideoId = $videos[$index]['id'];
                                } elseif (!empty($videos) && isset($videos[$chapter['chapter_number'] - 1])) {
                                    // Faire correspondre par numéro de chapitre : chapitre 1 -> vidéo index 0
                                    $targetVideoId = $videos[$chapter['chapter_number'] - 1]['id'];
                                }
                                ?>
                                <?php if ($targetVideoId): ?>
                                    <a href="#video-<?= $targetVideoId ?>" class="chapter-item chapter-link"
                                        data-chapter-id="<?= $chapter['id'] ?>" data-video-id="<?= $targetVideoId ?>"
                                        onclick="scrollToVideoAndPlay(<?= $targetVideoId ?>); return false;">
                                        <div class="chapter-number"><?= $chapter['chapter_number'] ?></div>
                                        <div class="chapter-content">
                                            <h4 class="chapter-title">
                                                <?= html_entity_decode($chapter['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h4>
                                            <?php if (!empty($chapter['description'])): ?>
                                                <p class="chapter-description">
                                                    <?= html_entity_decode($chapter['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="btn-play-chapter">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <!-- Chapitre sans vidéo associée -->
                                    <div class="chapter-item" data-chapter-id="<?= $chapter['id'] ?>">
                                        <div class="chapter-number"><?= $chapter['chapter_number'] ?></div>
                                        <div class="chapter-content">
                                            <h4 class="chapter-title">
                                                <?= html_entity_decode($chapter['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h4>
                                            <?php if (!empty($chapter['description'])): ?>
                                                <p class="chapter-description">
                                                    <?= html_entity_decode($chapter['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Vidéos multiples (si disponibles) -->
                <?php if (!empty($videos)): ?>
                    <div class="tutorial-videos-section">
                        <h3 class="videos-section-title">
                            <i class="fas fa-video"></i> Vidéos de la formation (<?= count($videos) ?>)
                        </h3>
                        <div class="videos-list-container">
                            <?php foreach ($videos as $index => $video): ?>
                                <div class="video-item-card" data-video-id="<?= $video['id'] ?>" id="video-<?= $video['id'] ?>">
                                    <div class="video-header">
                                        <div class="video-number"><?= $index + 1 ?></div>
                                        <div class="video-info">
                                            <h4 class="video-title">
                                                <?= html_entity_decode($video['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h4>
                                            <div class="video-meta-info">
                                                <?php if (!empty($video['description'])): ?>
                                                    <p class="video-description">
                                                        <?= html_entity_decode($video['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div class="video-stats">
                                                    <span><i class="fas fa-eye"></i> <?= number_format($video['views'] ?? 0) ?>
                                                        vues</span>
                                                    <?php if ($video['file_size']): ?>
                                                        <span><i class="fas fa-file"></i>
                                                            <?= round($video['file_size'] / 1024 / 1024, 2) ?> MB</span>
                                                    <?php endif; ?>
                                                    <?php if ($video['duration']): ?>
                                                        <span><i class="fas fa-clock"></i>
                                                            <?= gmdate('H:i:s', $video['duration']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="video-player-container">
                                        <video controls class="tutorial-video-player" data-video-id="<?= $video['id'] ?>"
                                            preload="metadata">
                                            <source src="<?= BASE_URL ?>/<?= htmlspecialchars($video['file_path']) ?>"
                                                type="video/mp4">
                                            Votre navigateur ne supporte pas la lecture vidéo.
                                        </video>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif (!empty($tutorial['file_path'])): ?>
                    <!-- Vidéo ou Fichier unique (ancien format) -->
                    <div class="tutorial-media">
                        <?php
                        $filePath = $tutorial['file_path'];
                        $fullPath = $filePath;
                        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                        $videoExtensions = ['mp4', 'webm', 'avi', 'mov', 'wmv', 'mpeg'];
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        ?>

                        <?php if (in_array($fileExtension, $videoExtensions)): ?>
                            <!-- Vidéo -->
                            <div class="video-container">
                                <video controls class="tutorial-video">
                                    <source src="<?= BASE_URL ?>/<?= htmlspecialchars($filePath) ?>"
                                        type="video/<?= $fileExtension ?>">
                                    Votre navigateur ne supporte pas la lecture vidéo.
                                </video>
                            </div>
                        <?php elseif (in_array($fileExtension, $imageExtensions)): ?>
                            <!-- Image -->
                            <div class="image-container">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($filePath) ?>"
                                    alt="<?= htmlspecialchars($tutorial['title']) ?>" class="tutorial-image">
                            </div>
                        <?php else: ?>
                            <!-- Autre fichier (PDF, etc.) -->
                            <div class="file-download-box">
                                <div class="file-icon-large">
                                    <?php
                                    $icon = 'fa-file';
                                    $iconColor = '#6c757d';
                                    if ($fileExtension === 'pdf') {
                                        $icon = 'fa-file-pdf';
                                        $iconColor = '#dc3545';
                                    } elseif (in_array($fileExtension, ['doc', 'docx'])) {
                                        $icon = 'fa-file-word';
                                        $iconColor = '#2b579a';
                                    } elseif (in_array($fileExtension, ['zip', 'rar'])) {
                                        $icon = 'fa-file-archive';
                                        $iconColor = '#f39c12';
                                    } else {
                                        $icon = 'fa-file-code';
                                        $iconColor = 'var(--primary-color)';
                                    }
                                    ?>
                                    <i class="fas <?= $icon ?>" style="color: <?= $iconColor ?>;"></i>
                                </div>
                                <div class="file-info-large">
                                    <h3>Fichier du tutoriel</h3>
                                    <p><?= basename($filePath) ?></p>
                                    <?php if (file_exists($fullPath)): ?>
                                        <p class="file-size"><?= round(filesize($fullPath) / 1024 / 1024, 2) ?> MB</p>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= BASE_URL ?>/<?= htmlspecialchars($filePath) ?>" class="btn-download"
                                    data-tutorial-id="<?= $tutorial['id'] ?>"
                                    data-file-path="<?= htmlspecialchars($filePath) ?>"
                                    onclick="trackDownloadDetail(event, this)" download target="_blank">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Lien Externe -->
                <?php if (!empty($tutorial['external_link'])): ?>
                    <div class="external-link-box">
                        <div class="external-icon">
                            <i class="fas fa-external-link-alt"></i>
                        </div>
                        <div class="external-info">
                            <h4>Ressource externe</h4>
                            <p><?= htmlspecialchars($tutorial['external_link']) ?></p>
                        </div>
                        <a href="<?= htmlspecialchars($tutorial['external_link']) ?>" class="btn-external" target="_blank"
                            rel="noopener noreferrer">
                            <i class="fas fa-arrow-right"></i> Ouvrir
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Contenu -->
                <div class="tutorial-content">
                    <div class="content-wrapper">
                        <?php
                        // Le contenu vient de TinyMCE qui génère du HTML propre et sécurisé
                        // Décoder les entités HTML (é, à, etc.) pour un affichage correct
                        echo html_entity_decode($tutorial['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        ?>
                    </div>
                </div>

                <!-- Tags -->
                <?php if (!empty($tags)): ?>
                    <div class="tutorial-tags">
                        <h3><i class="fas fa-tags"></i> Tags</h3>
                        <div class="tags-list">
                            <?php foreach ($tags as $tag): ?>
                                <a href="<?= BASE_URL ?>/tutorial/index?tag=<?= urlencode($tag['name']) ?>" class="tag-item">
                                    #<?= htmlspecialchars($tag['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Commentaires -->
                <div id="comments" class="comments-section">
                    <h3><i class="fas fa-comments"></i> Commentaires (<span
                            id="commentsCount"><?= number_format($tutorial['comments_count'] ?? 0) ?></span>)</h3>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form class="comment-form" id="commentForm">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="type" value="tutorial">
                            <input type="hidden" name="resource_id" value="<?= $tutorial['id'] ?>">
                            <textarea name="body" id="commentBody" placeholder="Partagez votre avis, posez une question..."
                                rows="4" required></textarea>
                            <button type="submit" class="btn-submit-comment">
                                <i class="fas fa-paper-plane"></i> Publier
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="login-prompt">
                            <i class="fas fa-lock"></i>
                            <p>Vous devez être connecté pour commenter</p>
                            <a href="<?= BASE_URL ?>/auth/login" class="btn-login-prompt">Se connecter</a>
                        </div>
                    <?php endif; ?>

                    <div id="commentsList" class="comments-list">
                        <!-- Les commentaires seront chargés ici via AJAX -->
                        <p class="loading-comments">
                            <i class="fas fa-spinner fa-spin"></i> Chargement des commentaires...
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="tutorial-sidebar">
                <!-- Author Card -->
                <div class="sidebar-card author-card">
                    <h4>À propos de l'auteur</h4>
                    <div class="author-full-info">
                        <?php if (!empty($tutorial['photo_path']) && file_exists($tutorial['photo_path'])): ?>
                            <img src="<?= $authorPhoto ?>"
                                alt="<?= htmlspecialchars($tutorial['prenom'] ?? 'Utilisateur') ?>"
                                class="author-photo-large">
                        <?php else: ?>
                            <div class="avatar-placeholder-large"><?= $authorInitial ?></div>
                        <?php endif; ?>
                        <h5><?= htmlspecialchars($tutorial['prenom'] ?? 'Utilisateur') ?>
                            <?= htmlspecialchars($tutorial['nom'] ?? '') ?></h5>
                        <?php if (!empty($tutorial['bio'])): ?>
                            <p class="author-bio"><?= htmlspecialchars($tutorial['bio']) ?></p>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/user/profile/<?= $tutorial['user_id'] ?>" class="btn-view-profile">
                            Voir le profil
                        </a>
                    </div>
                </div>

                <!-- Share Card -->
                <div class="sidebar-card share-card">
                    <h4><i class="fas fa-share-alt"></i> Partager</h4>
                    <div class="share-buttons">
                        <button class="share-btn facebook" onclick="shareOn('facebook')">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button class="share-btn twitter" onclick="shareOn('twitter')">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button class="share-btn linkedin" onclick="shareOn('linkedin')">
                            <i class="fab fa-linkedin-in"></i>
                        </button>
                        <button class="share-btn whatsapp" onclick="shareOn('whatsapp')">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                    </div>
                    <button class="btn-copy-link" onclick="copyLink()">
                        <i class="fas fa-link"></i> Copier le lien
                    </button>
                </div>

                <!-- Sommaire dans la sidebar (si disponible) -->
                <?php if (!empty($chapters)): ?>
                    <div class="sidebar-card chapters-sidebar">
                        <h4><i class="fas fa-list-ol"></i> Sommaire</h4>
                        <div class="sidebar-chapters-list">
                            <?php foreach ($chapters as $index => $chapter): ?>
                                <?php
                                // Déterminer l'ID de la vidéo à utiliser (même logique que ci-dessus)
                                $targetVideoId = null;
                                if (!empty($chapter['video_id'])) {
                                    $targetVideoId = $chapter['video_id'];
                                } elseif (!empty($videos) && isset($videos[$index])) {
                                    $targetVideoId = $videos[$index]['id'];
                                } elseif (!empty($videos) && isset($videos[$chapter['chapter_number'] - 1])) {
                                    $targetVideoId = $videos[$chapter['chapter_number'] - 1]['id'];
                                }
                                ?>
                                <?php if ($targetVideoId): ?>
                                    <a href="#video-<?= $targetVideoId ?>" class="sidebar-chapter-item"
                                        data-video-id="<?= $targetVideoId ?>"
                                        onclick="scrollToVideoAndPlay(<?= $targetVideoId ?>); return false;">
                                        <span class="sidebar-chapter-number"><?= $chapter['chapter_number'] ?></span>
                                        <span
                                            class="sidebar-chapter-title"><?= html_entity_decode($chapter['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span>
                                    </a>
                                <?php else: ?>
                                    <!-- Chapitre sans vidéo associée -->
                                    <div class="sidebar-chapter-item sidebar-chapter-no-video">
                                        <span class="sidebar-chapter-number"><?= $chapter['chapter_number'] ?></span>
                                        <span
                                            class="sidebar-chapter-title"><?= html_entity_decode($chapter['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Fichiers du Tutoriel -->
                <?php if (!empty($tutorial['file_path'])): ?>
                    <?php
                    $filePath = $tutorial['file_path'];
                    $fullPath = $filePath;
                    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $fileName = basename($filePath);
                    $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;
                    $fileSizeMB = $fileSize > 0 ? round($fileSize / 1024 / 1024, 2) : 0;

                    // Déterminer l'icône selon le type de fichier
                    $fileIcon = 'fa-file';
                    $fileIconColor = '#6c757d';
                    if ($fileExtension === 'pdf') {
                        $fileIcon = 'fa-file-pdf';
                        $fileIconColor = '#dc3545';
                    } elseif (in_array($fileExtension, ['doc', 'docx'])) {
                        $fileIcon = 'fa-file-word';
                        $fileIconColor = '#2b579a';
                    } elseif (in_array($fileExtension, ['xls', 'xlsx'])) {
                        $fileIcon = 'fa-file-excel';
                        $fileIconColor = '#28a745';
                    } elseif (in_array($fileExtension, ['ppt', 'pptx'])) {
                        $fileIcon = 'fa-file-powerpoint';
                        $fileIconColor = '#f39c12';
                    } elseif (in_array($fileExtension, ['zip', 'rar', '7z'])) {
                        $fileIcon = 'fa-file-archive';
                        $fileIconColor = '#f39c12';
                    } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $fileIcon = 'fa-file-image';
                        $fileIconColor = 'var(--secondary-color)';
                    } elseif (in_array($fileExtension, ['mp4', 'avi', 'mov', 'webm'])) {
                        $fileIcon = 'fa-file-video';
                        $fileIconColor = 'var(--primary-color)';
                    } else {
                        $fileIcon = 'fa-file-code';
                        $fileIconColor = 'var(--primary-color)';
                    }
                    ?>
                    <div class="sidebar-card files-card">
                        <h4><i class="fas fa-download"></i> Fichiers du Tutoriel</h4>
                        <div class="files-list">
                            <a href="<?= BASE_URL ?>/<?= htmlspecialchars($filePath) ?>" class="file-download-item"
                                data-tutorial-id="<?= $tutorial['id'] ?>"
                                data-file-path="<?= htmlspecialchars($filePath) ?>"
                                onclick="trackDownloadDetail(event, this)" download target="_blank">
                                <div class="file-icon-sidebar" style="color: <?= $fileIconColor ?>;">
                                    <i class="fas <?= $fileIcon ?>"></i>
                                </div>
                                <div class="file-info-sidebar">
                                    <h5 class="file-name-sidebar">
                                        <?= html_entity_decode($fileName, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h5>
                                    <div class="file-meta-sidebar">
                                        <span class="file-type-sidebar"><?= strtoupper($fileExtension) ?></span>
                                        <?php if ($fileSizeMB > 0): ?>
                                            <span class="file-size-sidebar"><i class="fas fa-weight"></i> <?= $fileSizeMB ?>
                                                MB</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="file-download-icon">
                                    <i class="fas fa-download"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Related Tutorials -->
                <div class="sidebar-card related-card">
                    <h4><i class="fas fa-lightbulb"></i> Tutoriels similaires</h4>
                    <div class="related-list">
                        <p class="text-muted">Chargement...</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
    /* Prévention des débordements au niveau global */
    html,
    body {
        max-width: 100%;
        overflow-x: hidden;
    }

    .tutorial-show-section {
        padding: 30px 0 60px;
        background: #f8f9fa;
        min-height: calc(100vh - 140px);
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Breadcrumb */
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 30px;
        font-size: 0.9rem;
        color: #6c757d;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        flex-wrap: wrap;
    }

    .breadcrumb-nav a {
        color: var(--primary-color);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .breadcrumb-nav a:hover {
        color: var(--secondary-color);
    }

    .breadcrumb-nav i.fa-chevron-right {
        font-size: 0.7rem;
    }

    /* Layout */
    .tutorial-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 30px;
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Main Content */
    .tutorial-main {
        display: flex;
        flex-direction: column;
        gap: 25px;
        max-width: 100%;
        overflow-x: hidden;
        min-width: 0;
        /* Important pour flex items */
    }

    /* Header */
    .tutorial-header {
        background: white;
        padding: 35px;
        border-radius: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        max-width: 100%;
        overflow-x: hidden;
    }

    .tutorial-meta-top {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .tutorial-type {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .tutorial-category {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        background: #f8f9fa;
        color: var(--dark-color);
        border: 2px solid #e9ecef;
    }

    .tutorial-title {
        font-size: 2.5rem;
        color: var(--dark-color);
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .tutorial-description {
        font-size: 1.2rem;
        color: #6c757d;
        line-height: 1.8;
        margin-bottom: 25px;
    }

    /* Author Section */
    .author-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 25px;
        border-top: 2px solid #f0f0f0;
        margin-bottom: 20px;
    }

    .author-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .author-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid var(--primary-color);
    }

    .author-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .author-name {
        margin: 0 0 5px;
        color: var(--dark-color);
    }

    .tutorial-date {
        margin: 0;
        font-size: 0.9rem;
        color: #6c757d;
    }

    /* Stats */
    .tutorial-stats {
        display: flex;
        gap: 20px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6c757d;
        font-weight: 600;
    }

    .stat-item i {
        color: var(--primary-color);
    }

    /* Actions */
    .tutorial-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .btn-action {
        flex: 1;
        min-width: 120px;
        padding: 12px 20px;
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        color: var(--dark-color);
    }

    .btn-action:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateY(-2px);
    }

    .btn-like.liked {
        background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
        color: white;
        border-color: #ff6b6b;
    }

    /* Media */
    .tutorial-media {
        background: white;
        padding: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .video-container {
        position: relative;
        width: 100%;
        max-width: 100%;
        background: #000;
        overflow: hidden;
    }

    .tutorial-video {
        width: 100%;
        max-width: 100%;
        max-height: 600px;
        display: block;
    }

    .image-container {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }

    .tutorial-image {
        width: 100%;
        max-width: 100%;
        height: auto;
        display: block;
    }

    .file-download-box {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 30px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .file-icon-large {
        font-size: 4rem;
    }

    .file-info-large {
        flex: 1;
    }

    .file-info-large h3 {
        margin: 0 0 8px;
        color: var(--dark-color);
    }

    .file-info-large p {
        margin: 0;
        color: #6c757d;
        word-break: break-all;
        overflow-wrap: break-word;
        max-width: 100%;
    }

    .file-size {
        font-size: 0.9rem;
        margin-top: 5px !important;
    }

    .btn-download {
        padding: 12px 25px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
    }

    /* External Link */
    .external-link-box {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 25px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        border-left: 5px solid var(--secondary-color);
    }

    .external-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .external-info {
        flex: 1;
    }

    .external-info h4 {
        margin: 0 0 8px;
        color: var(--dark-color);
    }

    .external-info p {
        margin: 0;
        color: #6c757d;
        word-break: break-all;
        overflow-wrap: break-word;
        max-width: 100%;
    }

    .btn-external {
        padding: 12px 25px;
        background: var(--secondary-color);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .btn-external:hover {
        transform: translateX(5px);
    }

    /* Content */
    .tutorial-content {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        max-width: 100%;
        overflow-x: hidden;
    }

    .content-wrapper {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #2c3e50;
    }

    .content-wrapper h1,
    .content-wrapper h2,
    .content-wrapper h3 {
        color: var(--dark-color);
        margin: 30px 0 15px;
    }

    .content-wrapper h1 {
        font-size: 2rem;
    }

    .content-wrapper h2 {
        font-size: 1.6rem;
    }

    .content-wrapper h3 {
        font-size: 1.3rem;
    }

    .content-wrapper code {
        background: #f0f0f0;
        padding: 3px 8px;
        border-radius: 4px;
        font-family: 'Courier New', Consolas, Monaco, monospace;
        color: #e74c3c;
        font-size: 0.95em;
    }

    .content-wrapper pre {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        overflow-x: auto;
        margin: 20px 0;
        border-left: 4px solid var(--primary-color);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .content-wrapper pre code {
        background: transparent;
        padding: 0;
        color: inherit;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* Styles pour les blocs de code TinyMCE */
    .content-wrapper .language-markup,
    .content-wrapper .language-javascript,
    .content-wrapper .language-css,
    .content-wrapper .language-php,
    .content-wrapper .language-python,
    .content-wrapper .language-java {
        display: block;
        background: #2d2d2d;
        color: #f8f8f2;
    }

    /* Tableaux */
    .content-wrapper table {
        width: 100%;
        max-width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: block;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .content-wrapper table th,
    .content-wrapper table td {
        padding: 12px 15px;
        text-align: left;
        border: 1px solid #e0e0e0;
        white-space: nowrap;
    }

    .content-wrapper table th {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-weight: 600;
    }

    .content-wrapper table tr:nth-child(even) {
        background: #f8f9fa;
    }

    .content-wrapper table tr:hover {
        background: #f0f0f0;
    }

    /* Citations */
    .content-wrapper blockquote {
        border-left: 4px solid var(--primary-color);
        padding: 15px 20px;
        margin: 20px 0;
        background: #f8f9fa;
        font-style: italic;
        color: #6c757d;
    }

    /* Images */
    .content-wrapper img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 20px 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Listes */
    .content-wrapper ul,
    .content-wrapper ol {
        margin: 15px 0;
        padding-left: 30px;
    }

    .content-wrapper li {
        margin: 8px 0;
    }

    /* Liens */
    .content-wrapper a {
        color: var(--primary-color);
        text-decoration: none;
        border-bottom: 1px solid transparent;
        transition: all 0.3s ease;
    }

    .content-wrapper a:hover {
        border-bottom-color: var(--primary-color);
    }

    .content-wrapper pre code {
        background: none;
        color: inherit;
        padding: 0;
    }

    .content-wrapper ul {
        margin: 15px 0;
        padding-left: 0;
        list-style: none;
    }

    .content-wrapper li {
        position: relative;
        padding-left: 30px;
        margin-bottom: 10px;
    }

    .content-wrapper li:before {
        content: '▸';
        position: absolute;
        left: 10px;
        color: var(--primary-color);
        font-weight: bold;
    }

    .content-wrapper a {
        color: var(--primary-color);
        text-decoration: underline;
    }

    /* Tags */
    .tutorial-tags {
        background: white;
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        max-width: 100%;
        overflow-x: hidden;
    }

    .tutorial-tags h3 {
        margin: 0 0 15px;
        color: var(--dark-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tags-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tag-item {
        padding: 8px 15px;
        background: #f8f9fa;
        color: var(--primary-color);
        text-decoration: none;
        border-radius: 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .tag-item:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    /* Comments */
    .comments-section {
        background: white;
        padding: 35px;
        border-radius: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        max-width: 100%;
        overflow-x: hidden;
    }

    .comments-section h3 {
        margin: 0 0 25px;
        color: var(--dark-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .comment-form textarea {
        width: 100%;
        padding: 15px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        font-size: 1rem;
        font-family: inherit;
        resize: vertical;
        transition: border-color 0.3s ease;
    }

    .comment-form textarea:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .btn-submit-comment {
        margin-top: 15px;
        padding: 12px 30px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .btn-submit-comment:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
    }

    .login-prompt {
        text-align: center;
        padding: 40px;
        background: #f8f9fa;
        border-radius: 12px;
    }

    .login-prompt i {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 15px;
    }

    .login-prompt p {
        margin: 0 0 20px;
        color: #6c757d;
    }

    .btn-login-prompt {
        display: inline-block;
        padding: 12px 30px;
        background: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-login-prompt:hover {
        background: var(--secondary-color);
    }

    /* Comment items */
    .comment-item {
        display: flex;
        gap: 15px;
        padding: 20px 0;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.5s ease;
        animation: fadeInUp 0.5s ease;
    }

    .comment-item:last-child {
        border-bottom: none;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .comment-avatar {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
    }

    .comment-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .comment-avatar-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: bold;
    }

    .comment-content {
        flex: 1;
    }

    .comment-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .comment-author {
        color: var(--dark-color);
        font-size: 1rem;
    }

    .badge-admin {
        padding: 3px 10px;
        background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
        color: white;
        font-size: 0.75rem;
        border-radius: 12px;
        font-weight: 600;
    }

    .comment-date {
        color: #6c757d;
        font-size: 0.85rem;
    }

    .comment-body {
        color: #2c3e50;
        line-height: 1.6;
        margin-bottom: 10px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
        max-width: 100%;
    }

    .comment-actions {
        display: flex;
        gap: 10px;
    }

    .btn-comment-action {
        padding: 6px 12px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        color: #6c757d;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-comment-action:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .btn-comment-action.btn-delete:hover {
        background: var(--danger-color);
        border-color: var(--danger-color);
    }

    .no-comments,
    .error-comments {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .error-comments {
        color: var(--danger-color);
    }

    /* Animations */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    /* Sidebar */
    .tutorial-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
        max-width: 100%;
        overflow-x: hidden;
    }

    .sidebar-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        max-width: 100%;
        overflow-x: hidden;
    }

    .sidebar-card h4 {
        margin: 0 0 20px;
        color: var(--dark-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Author Card */
    .author-full-info {
        text-align: center;
    }

    .author-photo-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
        border: 3px solid var(--primary-color);
    }

    .avatar-placeholder-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0 auto 15px;
    }

    .author-full-info h5 {
        margin: 0 0 10px;
        color: var(--dark-color);
    }

    .author-bio {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 15px;
        line-height: 1.6;
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }

    .btn-view-profile {
        display: inline-block;
        padding: 10px 20px;
        background: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-view-profile:hover {
        background: var(--secondary-color);
    }

    /* Share */
    .share-buttons {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 15px;
    }

    .share-btn {
        width: 100%;
        height: 45px;
        border: none;
        border-radius: 10px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1.2rem;
    }

    .share-btn.facebook {
        background: #1877f2;
    }

    .share-btn.twitter {
        background: #1da1f2;
    }

    .share-btn.linkedin {
        background: #0077b5;
    }

    .share-btn.whatsapp {
        background: #25d366;
    }

    .share-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-copy-link {
        width: 100%;
        padding: 12px;
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-copy-link:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    /* Related Tutorials */
    .related-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .related-item {
        display: flex;
        gap: 12px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .related-item:hover {
        background: white;
        border-color: var(--primary-color);
        transform: translateX(5px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .related-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .related-info {
        flex: 1;
    }

    .related-title {
        margin: 0 0 8px;
        font-size: 0.95rem;
        color: var(--dark-color);
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }

    .related-meta {
        display: flex;
        gap: 12px;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .related-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .related-meta i {
        color: var(--primary-color);
    }

    .text-muted {
        color: #6c757d;
        text-align: center;
        padding: 20px;
        font-size: 0.9rem;
    }

    /* Fichiers du Tutoriel */
    .files-card {
        margin-bottom: 20px;
    }

    .files-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .file-download-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        text-decoration: none;
        color: var(--dark-color);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        border-left: 4px solid var(--primary-color);
    }

    .file-download-item:hover {
        background: white;
        border-color: var(--primary-color);
        transform: translateX(5px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        color: var(--dark-color);
    }

    .file-icon-sidebar {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 10px;
        padding: 8px;
    }

    .file-info-sidebar {
        flex: 1;
        min-width: 0;
    }

    .file-name-sidebar {
        margin: 0 0 6px;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--dark-color);
        line-height: 1.3;
        word-wrap: break-word;
        overflow-wrap: break-word;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .file-meta-sidebar {
        display: flex;
        gap: 12px;
        font-size: 0.85rem;
        color: #6c757d;
        flex-wrap: wrap;
    }

    .file-type-sidebar {
        padding: 3px 8px;
        background: #e9ecef;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .file-size-sidebar {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .file-size-sidebar i {
        font-size: 0.8rem;
        color: var(--primary-color);
    }

    .file-download-icon {
        width: 35px;
        height: 35px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .file-download-item:hover .file-download-icon {
        background: var(--secondary-color);
        transform: scale(1.1) rotate(-5deg);
    }

    /* Responsive pour fichiers */
    @media (max-width: 768px) {
        .file-download-item {
            padding: 12px;
        }

        .file-icon-sidebar {
            width: 40px;
            height: 40px;
            font-size: 1.3rem;
        }

        .file-name-sidebar {
            font-size: 0.9rem;
        }

        .file-meta-sidebar {
            font-size: 0.8rem;
            gap: 8px;
        }

        .file-download-icon {
            width: 32px;
            height: 32px;
            font-size: 0.85rem;
        }
    }

    /* Prévention des débordements globaux */
    * {
        box-sizing: border-box;
    }

    .tutorial-show-section,
    .container,
    .tutorial-layout,
    .tutorial-main,
    .tutorial-sidebar {
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Empêcher les débordements dans le contenu */
    .content-wrapper * {
        max-width: 100%;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .content-wrapper img,
    .content-wrapper video,
    .content-wrapper iframe,
    .content-wrapper table {
        max-width: 100% !important;
        height: auto !important;
    }

    .content-wrapper pre {
        overflow-x: auto;
        max-width: 100%;
    }

    /* ======================================== 
   RESPONSIVE - MOBILE FIRST
   ======================================== */

    /* Mobile (< 768px) - ULTRA OPTIMISATIONS MOBILE FIRST */
    @media (max-width: 768px) {

        /* Container optimisé pour mobile */
        .container {
            padding: 0 15px;
            max-width: 100%;
            overflow-x: hidden;
        }

        .tutorial-show-section {
            padding: 15px 0 100px;
            background: #f8f9fa;
        }

        /* Breadcrumb ultra compact et touch-friendly */
        .breadcrumb-nav {
            margin-bottom: 15px;
            font-size: 0.8rem;
            flex-wrap: wrap;
            gap: 5px;
            padding: 10px 12px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .breadcrumb-nav a,
        .breadcrumb-nav span {
            padding: 5px 8px;
            display: inline-block;
        }

        .breadcrumb-nav i.fa-chevron-right {
            font-size: 0.6rem;
            margin: 0 3px;
            opacity: 0.5;
        }

        /* Layout optimisé - Sidebar en premier pour infos rapides */
        .tutorial-layout {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .tutorial-sidebar {
            order: -1;
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        /* Header ultra compact et lisible */
        .tutorial-header {
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
            max-width: 100%;
            overflow-x: hidden;
        }

        .tutorial-meta-top {
            gap: 8px;
            margin-bottom: 15px;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .tutorial-type,
        .tutorial-category {
            padding: 6px 12px;
            font-size: 0.85rem;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* Titre optimisé */
        .tutorial-title {
            font-size: 1.6rem;
            margin-bottom: 15px;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        /* Description optimisée */
        .tutorial-description {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 20px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Section auteur en colonne */
        .author-section {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            padding-top: 20px;
            margin-bottom: 15px;
        }

        .author-info {
            width: 100%;
            gap: 12px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border: 2px solid var(--primary-color);
        }

        .avatar-placeholder {
            font-size: 1.3rem;
        }

        .author-name {
            font-size: 0.95rem;
        }

        .tutorial-date {
            font-size: 0.85rem;
        }

        /* Stats en ligne sur mobile */
        .tutorial-stats {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .stat-item {
            justify-content: center;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.85rem;
            gap: 6px;
        }

        .stat-item i {
            font-size: 1rem;
        }

        /* Actions ultra-optimisées - Touch-friendly (min 44px hauteur) */
        .tutorial-actions {
            gap: 8px;
            margin-top: 15px;
        }

        .btn-action {
            flex: 1;
            min-width: auto;
            min-height: 44px;
            /* Standard iOS/Android touch target */
            padding: 12px 16px;
            font-size: 0.9rem;
            gap: 8px;
            border-radius: 10px;
            border-width: 1.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            touch-action: manipulation;
            /* Optimisation tactile */
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0.05);
        }

        .btn-action:active {
            transform: scale(0.97);
            transition: transform 0.1s ease;
        }

        .btn-action i {
            font-size: 1.1rem;
        }

        .btn-action span {
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Like button plus visible */
        .btn-like.liked {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f) !important;
            border-color: #ff6b6b !important;
        }

        /* Média plus compact */
        .tutorial-media {
            border-radius: 15px;
        }

        .tutorial-video {
            max-height: 280px;
        }

        .file-download-box {
            flex-direction: column;
            padding: 20px;
            gap: 15px;
            text-align: center;
        }

        .file-icon-large {
            font-size: 3rem;
        }

        .file-info-large h3 {
            font-size: 1.1rem;
        }

        .btn-download {
            width: 100%;
            justify-content: center;
            padding: 12px 20px;
        }

        /* External link box */
        .external-link-box {
            flex-direction: column;
            padding: 20px;
            gap: 15px;
            text-align: center;
        }

        .external-icon {
            margin: 0 auto;
        }

        .btn-external {
            width: 100%;
            justify-content: center;
        }

        /* Contenu ultra-optimisé pour lecture mobile */
        .tutorial-content {
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
            max-width: 100%;
            overflow-x: hidden;
        }

        .content-wrapper {
            font-size: 1rem;
            line-height: 1.75;
            color: #2c3e50;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Forcer les éléments enfants à respecter la largeur */
        .content-wrapper>* {
            max-width: 100% !important;
        }

        /* URLs et liens longs */
        .content-wrapper a {
            word-break: break-all;
            overflow-wrap: break-word;
        }

        /* Typographie hiérarchique optimisée */
        .content-wrapper h1 {
            font-size: 1.5rem;
            margin: 24px 0 12px;
            line-height: 1.3;
            font-weight: 700;
        }

        .content-wrapper h2 {
            font-size: 1.35rem;
            margin: 22px 0 10px;
            line-height: 1.35;
            font-weight: 700;
        }

        .content-wrapper h3 {
            font-size: 1.2rem;
            margin: 20px 0 10px;
            line-height: 1.4;
            font-weight: 600;
        }

        .content-wrapper p {
            margin: 12px 0;
        }

        /* Code blocks ultra-lisibles */
        .content-wrapper pre {
            padding: 12px;
            border-radius: 8px;
            margin: 15px -16px;
            /* Déborde légèrement pour plus d'espace */
            font-size: 0.8rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            /* Smooth scroll iOS */
            background: #2d2d2d !important;
            border-left: 3px solid var(--primary-color);
            color: #f8f8f2 !important;
        }

        .content-wrapper pre code {
            font-size: 0.8rem;
            line-height: 1.5;
            color: #f8f8f2 !important;
            background: transparent !important;
        }

        .content-wrapper code {
            font-size: 0.88em;
            padding: 2px 6px;
            word-break: break-all;
            background: #f0f0f0 !important;
            color: #e74c3c !important;
        }

        /* Blocs de code avec syntaxe highlighting */
        .content-wrapper .language-markup,
        .content-wrapper .language-javascript,
        .content-wrapper .language-css,
        .content-wrapper .language-php,
        .content-wrapper .language-python,
        .content-wrapper .language-java {
            color: #f8f8f2 !important;
        }

        /* Tables responsive avec scroll horizontal */
        .content-wrapper table {
            font-size: 0.85rem;
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 15px -16px;
            width: calc(100% + 32px);
        }

        .content-wrapper table th,
        .content-wrapper table td {
            padding: 8px 10px;
            min-width: 80px;
        }

        /* Citations plus visibles */
        .content-wrapper blockquote {
            padding: 12px 15px;
            margin: 15px 0;
            font-size: 0.95rem;
            border-left-width: 3px;
            background: rgba(52, 152, 219, 0.05);
        }

        /* Images responsive et optimisées */
        .content-wrapper img {
            margin: 15px 0;
            border-radius: 8px;
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* Listes mieux espacées */
        .content-wrapper ul,
        .content-wrapper ol {
            margin: 12px 0;
            padding-left: 25px;
        }

        .content-wrapper li {
            margin: 8px 0;
            line-height: 1.6;
        }

        /* Tags */
        .tutorial-tags {
            padding: 20px;
            border-radius: 15px;
        }

        .tutorial-tags h3 {
            font-size: 1.1rem;
            margin-bottom: 12px;
        }

        .tags-list {
            gap: 8px;
        }

        .tag-item {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 15px;
        }

        /* Section commentaires ultra-optimisée */
        .comments-section {
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
        }

        .comments-section h3 {
            font-size: 1.1rem;
            margin-bottom: 16px;
            font-weight: 700;
        }

        /* Form commentaire touch-friendly */
        .comment-form textarea {
            padding: 14px;
            border-radius: 10px;
            font-size: 0.95rem;
            min-height: 100px;
            resize: vertical;
            border-width: 1.5px;
            -webkit-appearance: none;
        }

        .comment-form textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn-submit-comment {
            width: 100%;
            padding: 14px 25px;
            justify-content: center;
            border-radius: 10px;
            min-height: 48px;
            /* Touch-friendly */
            font-weight: 600;
            font-size: 1rem;
            touch-action: manipulation;
        }

        .btn-submit-comment:active {
            transform: scale(0.98);
        }

        /* Items commentaires optimisés */
        .comment-item {
            padding: 14px 0;
            gap: 12px;
        }

        .comment-avatar {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
        }

        .comment-avatar-placeholder {
            font-size: 1.2rem;
        }

        .comment-author {
            font-size: 0.95rem;
            font-weight: 600;
        }

        .comment-date {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        .comment-body {
            font-size: 0.95rem;
            line-height: 1.6;
            margin-top: 6px;
            word-wrap: break-word;
        }

        .badge-admin {
            padding: 3px 8px;
            font-size: 0.7rem;
            border-radius: 10px;
        }

        /* Boutons d'action touch-friendly */
        .btn-comment-action {
            padding: 8px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
            min-height: 36px;
            touch-action: manipulation;
        }

        .btn-comment-action:active {
            transform: scale(0.95);
        }

        .login-prompt {
            padding: 30px 20px;
            border-radius: 10px;
        }

        .login-prompt i {
            font-size: 2.5rem;
            margin-bottom: 12px;
        }

        .login-prompt p {
            font-size: 0.95rem;
        }

        .btn-login-prompt {
            padding: 10px 25px;
            font-size: 0.95rem;
        }

        /* Sidebar ultra-optimisée pour mobile */
        .sidebar-card {
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
            background: white;
        }

        .sidebar-card h4 {
            font-size: 1rem;
            margin-bottom: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-card h4 i {
            font-size: 0.9rem;
        }

        /* Author card compacte */
        .author-photo-large,
        .avatar-placeholder-large {
            width: 70px;
            height: 70px;
            margin-bottom: 10px;
            border: 2px solid var(--primary-color);
        }

        .avatar-placeholder-large {
            font-size: 1.8rem;
        }

        .author-full-info h5 {
            font-size: 1rem;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .author-bio {
            font-size: 0.85rem;
            margin-bottom: 10px;
            line-height: 1.5;
            color: #6c757d;
        }

        .btn-view-profile {
            padding: 10px 20px;
            font-size: 0.9rem;
            border-radius: 8px;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            touch-action: manipulation;
        }

        .btn-view-profile:active {
            transform: scale(0.97);
        }

        /* Share buttons - Touch-friendly et grandes zones */
        .share-buttons {
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 12px;
        }

        .share-btn {
            height: 48px;
            /* Minimum 44px pour touch target */
            font-size: 1.2rem;
            border-radius: 10px;
            border: none;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            transition: transform 0.2s ease;
        }

        .share-btn:active {
            transform: scale(0.92);
        }

        .btn-copy-link {
            padding: 12px;
            font-size: 0.9rem;
            gap: 8px;
            min-height: 44px;
            border-radius: 10px;
            touch-action: manipulation;
        }

        .btn-copy-link:active {
            transform: scale(0.97);
        }

        /* Related tutorials - Cards optimisées */
        .related-item {
            padding: 12px;
            border-radius: 10px;
            gap: 12px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            touch-action: manipulation;
        }

        .related-item:active {
            transform: scale(0.98);
            background: #f8f9fa;
        }

        .related-icon {
            width: 44px;
            height: 44px;
            font-size: 1.4rem;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .related-title {
            font-size: 0.9rem;
            margin-bottom: 6px;
            font-weight: 600;
            line-height: 1.4;
        }

        .related-meta {
            font-size: 0.8rem;
            gap: 10px;
        }

        .related-meta span {
            white-space: nowrap;
        }

        .text-muted {
            padding: 14px;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        /* Scroll smooth pour iOS */
        .comments-list,
        .related-list {
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Très petits écrans (< 480px) - Optimisations extrêmes */
    @media (max-width: 480px) {
        .container {
            padding: 0 12px;
        }

        .tutorial-show-section {
            padding: 12px 0 100px;
        }

        /* Breadcrumb minimal */
        .breadcrumb-nav {
            font-size: 0.75rem;
            padding: 8px 10px;
        }

        /* Titre extra compact */
        .tutorial-title {
            font-size: 1.35rem !important;
            line-height: 1.35;
        }

        .tutorial-description {
            font-size: 0.95rem !important;
        }

        /* Actions en colonne verticale pour meilleure lisibilité */
        .tutorial-actions {
            flex-direction: column;
            gap: 10px;
        }

        .btn-action {
            width: 100%;
            min-height: 48px;
        }

        /* Stats verticales pour économiser l'espace */
        .tutorial-stats {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .stat-item {
            padding: 10px;
            justify-content: flex-start;
        }

        /* Boutons partage en 2 colonnes */
        .share-buttons {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .share-btn {
            height: 50px;
        }

        /* Sidebar cards full-width */
        .sidebar-card {
            padding: 14px;
        }

        /* Vidéo plus petite */
        .tutorial-video {
            max-height: 220px;
        }

        /* Download box simplifié */
        .file-download-box {
            padding: 16px;
        }

        .file-icon-large {
            font-size: 2.5rem;
        }

        /* Contenu avec marges réduites */
        .tutorial-content {
            padding: 14px;
        }

        .content-wrapper {
            font-size: 0.95rem;
        }

        .content-wrapper h1 {
            font-size: 1.4rem !important;
        }

        .content-wrapper h2 {
            font-size: 1.25rem !important;
        }

        .content-wrapper h3 {
            font-size: 1.15rem !important;
        }

        /* Commentaires compacts */
        .comments-section {
            padding: 14px;
        }

        .comment-item {
            padding: 12px 0;
        }

        .comment-avatar {
            width: 40px;
            height: 40px;
        }
    }

    /* Optimisations de performance - Toutes tailles */
    @media (max-width: 768px) {

        /* Désactiver les animations coûteuses sur mobile */
        * {
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0.05);
        }

        /* Optimisation du scrolling */
        body {
            -webkit-overflow-scrolling: touch;
            overflow-scrolling: touch;
        }

        /* Optimisation des images */
        img {
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }

        /* Optimisation des fonts */
        body,
        input,
        textarea,
        button {
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Désactiver les animations de hover sur mobile */
        .btn-action:hover,
        .related-item:hover,
        .share-btn:hover {
            transform: none;
        }

        /* Touch feedback optimisé */
        button,
        a,
        input[type="submit"] {
            -webkit-tap-highlight-color: rgba(52, 152, 219, 0.1);
            tap-highlight-color: rgba(52, 152, 219, 0.1);
        }
    }

    /* Tablettes (768px - 992px) */
    @media (min-width: 769px) and (max-width: 992px) {
        .tutorial-layout {
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .tutorial-sidebar {
            order: -1;
        }

        .sidebar-card {
            display: inline-block;
            width: calc(50% - 12.5px);
            vertical-align: top;
            margin-right: 12.5px;
        }

        .sidebar-card:nth-child(even) {
            margin-right: 0;
            margin-left: 12.5px;
        }
    }

    /* ========================================
   STYLES POUR SOMMAIRE ET VIDÉOS MULTIPLES
   ======================================== */

    /* Section Sommaire */
    .tutorial-chapters-section {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .chapters-title {
        margin: 0 0 25px;
        color: var(--dark-color);
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chapters-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .chapter-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 12px;
        border-left: 4px solid var(--primary-color);
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .chapter-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        color: inherit;
    }

    .chapter-link {
        cursor: pointer;
    }

    .chapter-number {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .chapter-content {
        flex: 1;
    }

    .chapter-title {
        margin: 0 0 8px;
        color: var(--dark-color);
        font-size: 1.1rem;
        font-weight: 600;
    }

    .chapter-description {
        margin: 0;
        color: #6c757d;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .btn-play-chapter {
        padding: 12px 20px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .chapter-item:hover .btn-play-chapter {
        background: var(--secondary-color);
        transform: scale(1.05);
    }

    /* Section Vidéos multiples */
    .tutorial-videos-section {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .videos-section-title {
        margin: 0 0 25px;
        color: var(--dark-color);
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .videos-list-container {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .video-item-card {
        border: 2px solid #e9ecef;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .video-item-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .video-header {
        display: flex;
        gap: 15px;
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
    }

    .video-number {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .video-info {
        flex: 1;
    }

    .video-title {
        margin: 0 0 10px;
        color: var(--dark-color);
        font-size: 1.2rem;
        font-weight: 600;
    }

    .video-description {
        margin: 0 0 10px;
        color: #6c757d;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .video-stats {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .video-stats span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .video-stats i {
        color: var(--primary-color);
    }

    .video-player-container {
        position: relative;
        width: 100%;
        background: #000;
    }

    .tutorial-video-player {
        width: 100%;
        max-height: 600px;
        display: block;
    }

    /* Sidebar Sommaire */
    .chapters-sidebar {
        max-height: 500px;
        overflow-y: auto;
    }

    .sidebar-chapters-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .sidebar-chapter-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 10px;
        text-decoration: none;
        color: var(--dark-color);
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .sidebar-chapter-item:hover {
        background: #e9ecef;
        border-left-color: var(--primary-color);
        transform: translateX(5px);
    }

    .sidebar-chapter-no-video {
        cursor: default;
        opacity: 0.7;
    }

    .sidebar-chapter-no-video:hover {
        transform: none;
        border-left-color: transparent;
    }

    .sidebar-chapter-number {
        width: 30px;
        height: 30px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .sidebar-chapter-title {
        flex: 1;
        font-size: 0.9rem;
        font-weight: 500;
        line-height: 1.4;
    }

    /* Responsive pour nouvelles sections */
    @media (max-width: 768px) {

        .tutorial-chapters-section,
        .tutorial-videos-section {
            padding: 20px;
            border-radius: 15px;
        }

        .chapters-title,
        .videos-section-title {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .chapter-item {
            flex-direction: column;
            align-items: flex-start;
            padding: 15px;
        }

        .chapter-number {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .btn-play-chapter {
            width: 100%;
            justify-content: center;
        }

        .video-header {
            flex-direction: column;
            gap: 12px;
        }

        .video-number {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }

        .video-title {
            font-size: 1.1rem;
        }

        .video-stats {
            gap: 15px;
            font-size: 0.85rem;
        }

        .tutorial-video-player {
            max-height: 300px;
        }

        .sidebar-chapter-item {
            padding: 10px;
        }

        .sidebar-chapter-number {
            width: 28px;
            height: 28px;
            font-size: 0.85rem;
        }

        .sidebar-chapter-title {
            font-size: 0.85rem;
        }
    }

    /* ========================================
   NOTIFICATION VIDÉO SUIVANTE
   ======================================== */

    .next-video-notification {
        position: fixed;
        bottom: 30px;
        right: 30px;
        max-width: 450px;
        width: calc(100% - 60px);
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        opacity: 0;
        transform: translateY(20px) scale(0.95);
        transition: all 0.3s ease;
        border: 2px solid var(--primary-color);
    }

    .next-video-notification.show {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    .notification-content {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .notification-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .notification-icon.success {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .notification-text {
        flex: 1;
    }

    .notification-text h4 {
        margin: 0 0 5px;
        color: var(--dark-color);
        font-size: 1.1rem;
    }

    .notification-text p {
        margin: 0;
        color: #6c757d;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .notification-text strong {
        color: var(--primary-color);
    }

    .notification-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-next-video {
        padding: 10px 20px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .btn-next-video:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }

    .btn-close-notification {
        width: 35px;
        height: 35px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 50%;
        color: #6c757d;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .btn-close-notification:hover {
        background: #e9ecef;
        color: var(--dark-color);
        transform: rotate(90deg);
    }

    .completion-notification {
        border-color: #28a745;
    }

    .completion-notification .notification-text h4 {
        color: #28a745;
    }

    /* Responsive pour notification */
    @media (max-width: 768px) {
        .next-video-notification {
            bottom: 20px;
            right: 20px;
            left: 20px;
            width: auto;
            max-width: none;
        }

        .notification-content {
            padding: 15px;
            flex-direction: column;
            text-align: center;
        }

        .notification-icon {
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
        }

        .notification-actions {
            width: 100%;
            flex-direction: row;
            justify-content: center;
        }

        .btn-next-video {
            flex: 1;
            justify-content: center;
        }

        .btn-close-notification {
            width: 40px;
            height: 40px;
        }
    }
</style>

<script>
    // ========================================
    // GESTION DES LIKES
    // ========================================

    function toggleLike() {
        <?php if (!isset($_SESSION['user_id'])): ?>
            alert('Vous devez être connecté pour aimer ce tutoriel');
            window.location.href = '<?= BASE_URL ?>/auth/login';
            return;
        <?php endif; ?>

        const btn = document.querySelector('.btn-like');
        const icon = btn.querySelector('i');
        const span = btn.querySelector('span');
        const likesCountSpan = document.querySelector('.stat-item i.fa-heart').nextElementSibling;

        // Désactiver le bouton temporairement
        btn.disabled = true;

        const formData = new FormData();
        formData.append('type', 'tutorial');
        formData.append('resource_id', <?= $tutorial['id'] ?>);

        fetch('<?= BASE_URL ?>/like/toggle', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.liked) {
                        // Ajouter la classe liked
                        btn.classList.add('liked');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        span.textContent = 'Aimé';
                    } else {
                        // Retirer la classe liked
                        btn.classList.remove('liked');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        span.textContent = 'Aimer';
                    }

                    // Mettre à jour le compteur
                    likesCountSpan.textContent = data.likes_count;

                    // Animation
                    btn.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        btn.style.transform = 'scale(1)';
                    }, 200);
                } else {
                    showMessage(data.message || 'Erreur lors de l\'opération', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage('Erreur lors de l\'opération', 'error');
            })
            .finally(() => {
                btn.disabled = false;
            });
    }

    // Share functions
    function shareOn(platform) {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent('<?= addslashes($tutorial["title"]) ?>');

        let shareUrl = '';
        switch (platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                break;
            case 'linkedin':
                shareUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`;
                break;
            case 'whatsapp':
                shareUrl = `https://wa.me/?text=${title}%20${url}`;
                break;
        }

        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    }

    function copyLink() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            const btn = event.target.closest('.btn-copy-link');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copié !';
            btn.style.background = 'var(--secondary-color)';
            btn.style.color = 'white';
            btn.style.borderColor = 'var(--secondary-color)';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
                btn.style.color = '';
                btn.style.borderColor = '';
            }, 2000);
        });
    }

    function shareContent() {
        if (navigator.share) {
            navigator.share({
                title: '<?= addslashes($tutorial["title"]) ?>',
                text: '<?= addslashes($tutorial["description"]) ?>',
                url: window.location.href
            });
        } else {
            copyLink();
        }
    }

    // ========================================
    // GESTION DES COMMENTAIRES
    // ========================================

    // Charger les commentaires
    function loadComments() {
        const commentsList = document.getElementById('commentsList');
        const tutorialId = <?= $tutorial['id'] ?>;

        fetch(`<?= BASE_URL ?>/comment/getComments/tutorial/${tutorialId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.comments.length > 0) {
                    renderComments(data.comments);
                } else {
                    commentsList.innerHTML =
                        '<p class="no-comments"><i class="fas fa-info-circle"></i> Aucun commentaire pour le moment. Soyez le premier à commenter !</p>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                commentsList.innerHTML =
                    '<p class="error-comments"><i class="fas fa-exclamation-triangle"></i> Erreur lors du chargement des commentaires.</p>';
            });
    }

    // Afficher les commentaires
    function renderComments(comments) {
        const commentsList = document.getElementById('commentsList');
        let html = '';

        comments.forEach(comment => {
            const userInitial = comment.user.name.charAt(0).toUpperCase();
            const userPhoto = comment.user.photo ?
                `<img src="${comment.user.photo}" alt="${comment.user.name}">` :
                `<div class="comment-avatar-placeholder">${userInitial}</div>`;

            const editButtons = comment.can_edit ? `
            <button class="btn-comment-action" onclick="editComment(${comment.id}, '${escapeHtml(comment.body)}')" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn-comment-action btn-delete" onclick="deleteComment(${comment.id})" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        ` : '';

            html += `
            <div class="comment-item" id="comment-${comment.id}">
                <div class="comment-avatar">
                    ${userPhoto}
                </div>
                <div class="comment-content">
                    <div class="comment-header">
                        <strong class="comment-author">${comment.user.name}</strong>
                        ${comment.user.role === 'admin' ? '<span class="badge-admin">Admin</span>' : ''}
                        <span class="comment-date">${comment.time_ago}</span>
                    </div>
                    <div class="comment-body" id="comment-body-${comment.id}">
                        ${escapeHtml(comment.body).replace(/\n/g, '<br>')}
                    </div>
                    <div class="comment-actions">
                        ${editButtons}
                    </div>
                </div>
            </div>
        `;
        });

        commentsList.innerHTML = html;
    }

    // Soumettre un nouveau commentaire
    document.getElementById('commentForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        const textarea = document.getElementById('commentBody');

        // Désactiver le bouton
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
        textarea.disabled = true;

        fetch('<?= BASE_URL ?>/comment/create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Réinitialiser le formulaire
                    textarea.value = '';

                    // Recharger les commentaires
                    loadComments();

                    // Mettre à jour le compteur avec animation
                    const countSpan = document.getElementById('commentsCount');
                    const currentCount = parseInt(countSpan.textContent.replace(/\s/g, ''));
                    const newCount = currentCount + 1;

                    // Animation du compteur
                    countSpan.style.transition = 'transform 0.3s ease';
                    countSpan.style.transform = 'scale(1.3)';
                    countSpan.style.color = 'var(--secondary-color)';

                    setTimeout(() => {
                        countSpan.textContent = newCount.toLocaleString();
                        countSpan.style.transform = 'scale(1)';
                        setTimeout(() => {
                            countSpan.style.color = '';
                        }, 300);
                    }, 150);

                    // Scroll vers le nouveau commentaire avec animation
                    setTimeout(() => {
                        const commentsList = document.getElementById('commentsList');
                        const firstComment = commentsList.querySelector('.comment-item');
                        if (firstComment) {
                            firstComment.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest'
                            });

                            // Flash vert sur le nouveau commentaire
                            firstComment.style.background =
                                'linear-gradient(90deg, #d4edda 0%, white 100%)';
                            firstComment.style.transition = 'background 1s ease';
                            setTimeout(() => {
                                firstComment.style.background = '';
                            }, 1000);
                        }
                    }, 300);

                    // Message de succès
                    showMessage('✅ Commentaire ajouté avec succès !', 'success');
                } else {
                    showMessage(data.message || 'Erreur lors de l\'ajout du commentaire', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage('❌ Erreur lors de l\'ajout du commentaire', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                textarea.disabled = false;
            });
    });

    // Modifier un commentaire
    function editComment(commentId, currentBody) {
        const newBody = prompt('Modifier le commentaire:', currentBody);

        if (newBody === null || newBody.trim() === '') {
            return;
        }

        if (newBody.trim() === currentBody) {
            return;
        }

        const commentElement = document.getElementById(`comment-${commentId}`);
        const commentBodyElement = document.getElementById(`comment-body-${commentId}`);

        // Animation de chargement
        commentElement.style.opacity = '0.5';
        commentBodyElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour...';

        const formData = new FormData();
        formData.append('body', newBody.trim());

        fetch(`<?= BASE_URL ?>/comment/update/${commentId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour l'affichage avec animation
                    commentBodyElement.innerHTML = escapeHtml(newBody).replace(/\n/g, '<br>');
                    commentElement.style.opacity = '1';

                    // Animation flash pour montrer le changement
                    commentElement.style.background = 'linear-gradient(90deg, #d4edda 0%, white 100%)';
                    setTimeout(() => {
                        commentElement.style.background = '';
                        commentElement.style.transition = 'background 1s ease';
                    }, 100);
                    setTimeout(() => {
                        commentElement.style.background = '';
                    }, 1100);

                    showMessage('✅ Commentaire modifié avec succès !', 'success');
                } else {
                    // Restaurer l'ancien contenu en cas d'erreur
                    commentBodyElement.innerHTML = escapeHtml(currentBody).replace(/\n/g, '<br>');
                    commentElement.style.opacity = '1';
                    showMessage(data.message || 'Erreur lors de la modification', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                commentBodyElement.innerHTML = escapeHtml(currentBody).replace(/\n/g, '<br>');
                commentElement.style.opacity = '1';
                showMessage('Erreur lors de la modification du commentaire', 'error');
            });
    }

    // Supprimer un commentaire
    function deleteComment(commentId) {
        if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce commentaire ?\n\nCette action est irréversible.')) {
            return;
        }

        const commentElement = document.getElementById(`comment-${commentId}`);

        // Animation de suppression (fade out + slide)
        commentElement.style.transition = 'all 0.5s ease';
        commentElement.style.opacity = '0.3';
        commentElement.style.transform = 'translateX(-20px)';
        commentElement.style.background = '#ffe6e6';

        fetch(`<?= BASE_URL ?>/comment/delete/${commentId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animation de suppression complète
                    commentElement.style.height = commentElement.offsetHeight + 'px';
                    setTimeout(() => {
                        commentElement.style.height = '0px';
                        commentElement.style.padding = '0';
                        commentElement.style.margin = '0';
                        commentElement.style.overflow = 'hidden';
                    }, 100);

                    // Supprimer du DOM après l'animation
                    setTimeout(() => {
                        commentElement.remove();

                        // Mettre à jour le compteur avec animation
                        const countSpan = document.getElementById('commentsCount');
                        const currentCount = parseInt(countSpan.textContent.replace(/\s/g, ''));
                        const newCount = Math.max(0, currentCount - 1);

                        // Animation du compteur
                        countSpan.style.transition = 'transform 0.3s ease';
                        countSpan.style.transform = 'scale(1.3)';
                        countSpan.style.color = 'var(--danger-color)';

                        setTimeout(() => {
                            countSpan.textContent = newCount.toLocaleString();
                            countSpan.style.transform = 'scale(1)';
                            countSpan.style.color = '';
                        }, 150);

                        // Vérifier s'il reste des commentaires
                        const commentsList = document.getElementById('commentsList');
                        if (commentsList.children.length === 0 || !commentsList.querySelector(
                                '.comment-item')) {
                            commentsList.innerHTML =
                                '<p class="no-comments"><i class="fas fa-info-circle"></i> Aucun commentaire pour le moment. Soyez le premier à commenter !</p>';
                        }

                        showMessage('🗑️ Commentaire supprimé avec succès !', 'success');
                    }, 600);
                } else {
                    // Annuler l'animation en cas d'erreur
                    commentElement.style.opacity = '1';
                    commentElement.style.transform = 'translateX(0)';
                    commentElement.style.background = '';
                    showMessage(data.message || 'Erreur lors de la suppression', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                // Annuler l'animation
                commentElement.style.opacity = '1';
                commentElement.style.transform = 'translateX(0)';
                commentElement.style.background = '';
                showMessage('Erreur lors de la suppression du commentaire', 'error');
            });
    }

    // Échapper le HTML pour la sécurité
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Afficher un message
    function showMessage(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML =
            `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
        alertDiv.style.cssText =
            'position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 15px 20px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); animation: slideInRight 0.3s ease;';

        if (type === 'success') {
            alertDiv.style.background = '#d4edda';
            alertDiv.style.color = '#155724';
            alertDiv.style.border = '2px solid #c3e6cb';
        } else {
            alertDiv.style.background = '#f8d7da';
            alertDiv.style.color = '#721c24';
            alertDiv.style.border = '2px solid #f5c6cb';
        }

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => alertDiv.remove(), 300);
        }, 3000);
    }

    // Charger les commentaires au chargement de la page
    loadComments();

    // ========================================
    // GESTION DES TUTORIELS SIMILAIRES
    // ========================================

    function loadSimilarTutorials() {
        const relatedList = document.querySelector('.related-list');
        const tutorialId = <?= $tutorial['id'] ?>;

        fetch(`<?= BASE_URL ?>/tutorial/getSimilar/${tutorialId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.tutorials.length > 0) {
                    renderSimilarTutorials(data.tutorials);
                } else {
                    relatedList.innerHTML = '<p class="text-muted">Aucun tutoriel similaire trouvé</p>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                relatedList.innerHTML = '<p class="text-muted">Erreur de chargement</p>';
            });
    }

    function renderSimilarTutorials(tutorials) {
        const relatedList = document.querySelector('.related-list');
        let html = '';

        tutorials.forEach(tuto => {
            const typeIcons = {
                'video': '🎥',
                'text': '📝',
                'pdf': '📄',
                'code': '💻',
                'mixed': '🔀'
            };
            const icon = typeIcons[tuto.type] || '📝';

            html += `
            <a href="<?= BASE_URL ?>/tutorial/show/${tuto.id}" class="related-item">
                <div class="related-icon">${icon}</div>
                <div class="related-info">
                    <h5 class="related-title">${escapeHtml(tuto.title)}</h5>
                    <div class="related-meta">
                        <span><i class="fas fa-eye"></i> ${tuto.views || 0}</span>
                        <span><i class="fas fa-heart"></i> ${tuto.likes_count || 0}</span>
                    </div>
                </div>
            </a>
        `;
        });

        relatedList.innerHTML = html;
    }

    // Charger les tutoriels similaires au chargement de la page
    loadSimilarTutorials();

    // ========================================
    // GESTION DES VIDÉOS MULTIPLES ET CHAPITRES
    // ========================================

    // Fonction principale pour scroller vers une vidéo et la jouer
    function scrollToVideoAndPlay(videoId) {
        if (!videoId) {
            return;
        }

        // Trouver la carte vidéo
        const videoCard = document.getElementById(`video-${videoId}`);
        if (!videoCard) {
            return;
        }

        // Scroller vers la vidéo avec un offset pour le header
        const offset = 100; // Offset pour le header fixe
        const elementPosition = videoCard.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;

        // Utiliser scrollIntoView pour une meilleure compatibilité
        videoCard.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        // Alternative avec window.scrollTo si scrollIntoView ne fonctionne pas
        setTimeout(() => {
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }, 100);

        // Mettre en surbrillance la carte vidéo
        videoCard.style.border = '3px solid var(--primary-color)';
        videoCard.style.boxShadow = '0 0 20px rgba(52, 152, 219, 0.5)';
        videoCard.style.transition = 'all 0.3s ease';

        // Retirer la surbrillance après 3 secondes
        setTimeout(() => {
            videoCard.style.border = '';
            videoCard.style.boxShadow = '';
        }, 3000);

        // Attendre que le scroll soit terminé avant de jouer
        setTimeout(() => {
            playVideo(videoId);
        }, 1000);
    }

    // Fonction pour jouer une vidéo spécifique
    function playVideo(videoId) {
        if (!videoId) return;

        // Trouver l'élément vidéo
        const videoElement = document.querySelector(`.tutorial-video-player[data-video-id="${videoId}"]`);
        if (!videoElement) {
            return;
        }

        // Mettre en pause toutes les autres vidéos
        document.querySelectorAll('.tutorial-video-player').forEach(video => {
            if (video !== videoElement) {
                video.pause();
            }
        });

        // Jouer la vidéo
        videoElement.play().catch(error => {
            // Si la lecture automatique échoue (politique du navigateur), l'utilisateur peut cliquer manuellement
            // Pas besoin d'afficher de message, le lecteur vidéo natif gère cela
        });

        // Incrémenter les vues de la vidéo
        trackVideoView(videoId);
    }

    // Fonction pour scroller vers une vidéo (sans jouer automatiquement)
    function scrollToVideo(videoId) {
        if (!videoId) return;

        const videoCard = document.getElementById(`video-${videoId}`);
        if (videoCard) {
            const offset = 100;
            const elementPosition = videoCard.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - offset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });

            // Mettre en surbrillance
            videoCard.style.border = '3px solid var(--primary-color)';
            videoCard.style.boxShadow = '0 0 20px rgba(52, 152, 219, 0.5)';
            setTimeout(() => {
                videoCard.style.border = '';
                videoCard.style.boxShadow = '';
            }, 2000);
        }
    }

    // Tracker les vues des vidéos
    function trackVideoView(videoId) {
        fetch(`<?= BASE_URL ?>/tutorial/trackVideoView/${videoId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        }).catch(() => {
            // Erreur silencieuse - le tracking n'est pas critique
        });
    }

    // Tracker les vues quand une vidéo commence à jouer
    document.querySelectorAll('.tutorial-video-player').forEach(video => {
        video.addEventListener('play', function() {
            const videoId = this.dataset.videoId;
            if (videoId) {
                trackVideoView(videoId);
            }
        });

        // Détecter quand une vidéo se termine pour proposer la suivante
        video.addEventListener('ended', function() {
            const videoId = parseInt(this.dataset.videoId);
            if (videoId) {
                proposeNextVideo(videoId);
            }
        });
    });

    // Proposer la vidéo suivante quand une vidéo se termine
    function proposeNextVideo(currentVideoId) {
        // Trouver toutes les vidéos dans l'ordre
        const allVideos = Array.from(document.querySelectorAll('.video-item-card'));
        const currentIndex = allVideos.findIndex(card => {
            const videoId = parseInt(card.dataset.videoId);
            return videoId === currentVideoId;
        });

        // Vérifier s'il y a une vidéo suivante
        if (currentIndex >= 0 && currentIndex < allVideos.length - 1) {
            const nextVideoCard = allVideos[currentIndex + 1];
            const nextVideoId = parseInt(nextVideoCard.dataset.videoId);
            const nextVideoTitle = nextVideoCard.querySelector('.video-title')?.textContent || 'Vidéo suivante';

            // Afficher une notification élégante
            showNextVideoProposal(nextVideoId, nextVideoTitle, currentIndex + 2);
        } else {
            // C'est la dernière vidéo
            showCompletionMessage();
        }
    }

    // Afficher une proposition pour la vidéo suivante
    function showNextVideoProposal(videoId, videoTitle, videoNumber) {
        // Supprimer toute notification existante
        const existingNotification = document.getElementById('next-video-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Créer la notification
        const notification = document.createElement('div');
        notification.id = 'next-video-notification';
        notification.className = 'next-video-notification';
        notification.innerHTML = `
        <div class="notification-content">
            <div class="notification-icon">
                <i class="fas fa-arrow-right"></i>
            </div>
            <div class="notification-text">
                <h4>Vidéo terminée !</h4>
                <p>Passer à la vidéo suivante : <strong>${escapeHtml(videoTitle)}</strong></p>
            </div>
            <div class="notification-actions">
                <button class="btn-next-video" onclick="playNextVideo(${videoId})">
                    <i class="fas fa-play"></i> Lire la suite
                </button>
                <button class="btn-close-notification" onclick="closeNextVideoNotification()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;

        // Ajouter au body
        document.body.appendChild(notification);

        // Animation d'apparition
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Auto-fermeture après 10 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                closeNextVideoNotification();
            }
        }, 10000);
    }

    // Lire la vidéo suivante
    function playNextVideo(videoId) {
        closeNextVideoNotification();
        scrollToVideoAndPlay(videoId);
    }

    // Fermer la notification
    function closeNextVideoNotification() {
        const notification = document.getElementById('next-video-notification');
        if (notification) {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }

    // Afficher un message de complétion
    function showCompletionMessage() {
        const existingNotification = document.getElementById('next-video-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement('div');
        notification.id = 'next-video-notification';
        notification.className = 'next-video-notification completion-notification';
        notification.innerHTML = `
        <div class="notification-content">
            <div class="notification-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="notification-text">
                <h4>Félicitations ! 🎉</h4>
                <p>Vous avez terminé toutes les vidéos de cette formation !</p>
            </div>
            <div class="notification-actions">
                <button class="btn-close-notification" onclick="closeNextVideoNotification()">
                    <i class="fas fa-times"></i> Fermer
                </button>
            </div>
        </div>
    `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Auto-fermeture après 8 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                closeNextVideoNotification();
            }
        }, 8000);
    }

    // Gérer les ancres dans l'URL (fallback si JavaScript ne fonctionne pas immédiatement)
    window.addEventListener('load', function() {
        // Vérifier si l'URL contient une ancre vers une vidéo
        const hash = window.location.hash;
        if (hash && hash.startsWith('#video-')) {
            const videoId = hash.replace('#video-', '');
            if (videoId) {
                // Attendre un peu que la page soit complètement chargée
                setTimeout(() => {
                    scrollToVideoAndPlay(parseInt(videoId));
                }, 500);
            }
        }

        // Gérer les clics sur les liens d'ancres (fallback)
        document.querySelectorAll('a[href^="#video-"]').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && href.startsWith('#video-')) {
                    const videoId = href.replace('#video-', '');
                    if (videoId) {
                        e.preventDefault();
                        scrollToVideoAndPlay(parseInt(videoId));
                        // Mettre à jour l'URL sans recharger la page
                        history.pushState(null, null, href);
                    }
                }
            });
        });
    });

    // ========================================
    // GESTION DES TÉLÉCHARGEMENTS
    // ========================================

    function trackDownloadDetail(event, element) {
        // NE PAS empêcher le téléchargement
        // Juste enregistrer le tracking en arrière-plan

        const tutorialId = element.dataset.tutorialId;
        const filePath = element.dataset.filePath;

        // Animation visuelle du bouton
        element.style.transform = 'scale(1.05)';
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 200);

        // Enregistrer le téléchargement en arrière-plan (sans bloquer)
        const formData = new FormData();
        formData.append('type', 'tutorial');
        formData.append('resource_id', tutorialId);
        formData.append('file_path', filePath);

        // Utiliser navigator.sendBeacon pour un tracking non-bloquant
        // Ou fetch en mode no-cors pour ne pas bloquer le téléchargement
        fetch('<?= BASE_URL ?>/download/track', {
                method: 'POST',
                body: formData,
                keepalive: true // Important: permet la requête même si l'utilisateur change de page
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('✅ Téléchargement enregistré:', data.downloads_count);

                    // Badge de confirmation
                    const badge = document.createElement('span');
                    badge.style.cssText =
                        'position: absolute; top: 5px; right: 5px; background: #28a745; color: white; border-radius: 50%; width: 24px; height: 24px; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(40,167,69,0.5); z-index: 10;';
                    badge.innerHTML = '<i class="fas fa-check"></i>';
                    element.style.position = 'relative';
                    element.appendChild(badge);

                    setTimeout(() => {
                        badge.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        badge.style.opacity = '0';
                        badge.style.transform = 'scale(0)';
                        setTimeout(() => badge.remove(), 300);
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Tracking error (non-bloquant):', error);
                // Ne JAMAIS bloquer le téléchargement
            });

        // Retourner true pour permettre le téléchargement
        return true;
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>