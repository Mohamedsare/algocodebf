<?php
// Initialiser les variables pour éviter les warnings
$tutorials = $tutorials ?? [];
$categories = $categories ?? [];
$current_search = $current_search ?? '';
$current_category = $current_category ?? '';
$current_type = $current_type ?? '';
$current_level = $current_level ?? '';
$current_sort = $current_sort ?? 'recent';
$total_tutorials = $total_tutorials ?? 0;
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;

$pageTitle = 'Tutoriels - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Page des Tutoriels Style YouTube -->
<div class="tutorials-page">
    <div class="tutorials-container">

        <!-- Barre de recherche (style YouTube) -->
        <div class="tutorials-search-bar">
            <form action="<?= BASE_URL ?>/tutorial/index" method="GET" class="search-form-tutorials">
                <div class="search-input-wrapper">
                    <input type="text" name="search" id="tutorialSearch" placeholder="Rechercher des tutoriels..."
                        value="<?= htmlspecialchars($current_search ?? '') ?>" autocomplete="off">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <!-- Filtres cachés dans la barre de recherche -->
                <input type="hidden" name="category" value="<?= htmlspecialchars($current_category ?? '') ?>">
                <input type="hidden" name="type" value="<?= htmlspecialchars($current_type ?? '') ?>">
                <input type="hidden" name="level" value="<?= htmlspecialchars($current_level ?? '') ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($current_sort ?? 'recent') ?>">
            </form>
        </div>

        <!-- Filtres horizontaux (chips style YouTube) -->
        <div class="tutorials-filters">
            <div class="filters-scroll">
                <!-- Bouton "Tous" -->
                <a href="<?= BASE_URL ?>/tutorial/index"
                    class="filter-chip <?= empty($current_category) && empty($current_type) && empty($current_level) ? 'active' : '' ?>">
                    <i class="fas fa-th"></i> Tous
                </a>

                <!-- Filtres par catégorie -->
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= BASE_URL ?>/tutorial/index?category=<?= urlencode($cat) ?><?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                        class="filter-chip <?= ($current_category ?? '') === $cat ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat) ?>
                    </a>
                <?php endforeach; ?>

                <!-- Filtres par type -->
                <a href="<?= BASE_URL ?>/tutorial/index?type=video<?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="filter-chip <?= ($current_type ?? '') === 'video' ? 'active' : '' ?>">
                    <i class="fas fa-video"></i> Vidéo
                </a>
                <a href="<?= BASE_URL ?>/tutorial/index?type=pdf<?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="filter-chip <?= ($current_type ?? '') === 'pdf' ? 'active' : '' ?>">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="<?= BASE_URL ?>/tutorial/index?type=code<?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="filter-chip <?= ($current_type ?? '') === 'code' ? 'active' : '' ?>">
                    <i class="fas fa-code"></i> Code
                </a>

                <!-- Filtres par niveau -->
                <a href="<?= BASE_URL ?>/tutorial/index?level=Débutant<?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="filter-chip <?= ($current_level ?? '') === 'Débutant' ? 'active' : '' ?>">
                    ⭐ Débutant
                </a>
                <a href="<?= BASE_URL ?>/tutorial/index?level=Intermédiaire<?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="filter-chip <?= ($current_level ?? '') === 'Intermédiaire' ? 'active' : '' ?>">
                    ⭐⭐ Intermédiaire
                </a>
                <a href="<?= BASE_URL ?>/tutorial/index?level=Avancé<?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="filter-chip <?= ($current_level ?? '') === 'Avancé' ? 'active' : '' ?>">
                    ⭐⭐⭐ Avancé
                </a>
            </div>
        </div>

        <!-- Barre de tri -->
        <div class="tutorials-sort-bar">
            <div class="sort-options">
                <span class="sort-label">Trier par :</span>
                <a href="<?= BASE_URL ?>/tutorial/index?sort=recent<?= $current_category ? '&category=' . urlencode($current_category) : '' ?><?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="sort-option <?= ($current_sort ?? 'recent') === 'recent' ? 'active' : '' ?>">
                    Plus récents
                </a>
                <a href="<?= BASE_URL ?>/tutorial/index?sort=popular<?= $current_category ? '&category=' . urlencode($current_category) : '' ?><?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="sort-option <?= ($current_sort ?? '') === 'popular' ? 'active' : '' ?>">
                    Plus populaires
                </a>
                <a href="<?= BASE_URL ?>/tutorial/index?sort=views<?= $current_category ? '&category=' . urlencode($current_category) : '' ?><?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="sort-option <?= ($current_sort ?? '') === 'views' ? 'active' : '' ?>">
                    Plus vus
                </a>
                <a href="<?= BASE_URL ?>/tutorial/index?sort=likes<?= $current_category ? '&category=' . urlencode($current_category) : '' ?><?= $current_search ? '&search=' . urlencode($current_search) : '' ?>"
                    class="sort-option <?= ($current_sort ?? '') === 'likes' ? 'active' : '' ?>">
                    Plus aimés
                </a>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $userModel = new User();
                if ($userModel->canCreateTutorial($_SESSION['user_id'])):
                ?>
                    <a href="<?= BASE_URL ?>/tutorial/create" class="btn-create-tutorial">
                        <i class="fas fa-plus"></i> Créer un tutoriel
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Résultats de recherche -->
        <?php if (!empty($current_search)): ?>
            <div class="search-results-info">
                <p>
                    <strong><?= $total_tutorials ?? 0 ?></strong> tutoriel(s) trouvé(s) pour
                    "<strong><?= htmlspecialchars($current_search) ?></strong>"
                </p>
            </div>
        <?php endif; ?>

        <!-- Grille de tutoriels (style YouTube) -->
        <div class="tutorials-grid">
            <?php if (empty($tutorials)): ?>
                <div class="empty-tutorials">
                    <div class="empty-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Aucun tutoriel disponible</h3>
                    <p>
                        <?php if (!empty($current_search)): ?>
                            Aucun tutoriel ne correspond à votre recherche.
                        <?php else: ?>
                            Soyez le premier à partager un tutoriel !
                        <?php endif; ?>
                    </p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $userModel = new User();
                        if ($userModel->canCreateTutorial($_SESSION['user_id'])):
                        ?>
                            <a href="<?= BASE_URL ?>/tutorial/create" class="btn-create-empty">
                                <i class="fas fa-plus"></i> Créer un tutoriel
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($tutorials as $tutorial): ?>
                    <div class="tutorial-card-youtube">
                        <!-- Thumbnail -->
                        <a href="<?= BASE_URL ?>/tutorial/show/<?= $tutorial['id'] ?>" class="tutorial-thumbnail-link">
                            <div class="tutorial-thumbnail-container">
                                <?php if (!empty($tutorial['file_path']) && file_exists($tutorial['file_path'])): ?>
                                    <?php
                                    $fileExtension = strtolower(pathinfo($tutorial['file_path'], PATHINFO_EXTENSION));
                                    $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    ?>
                                    <?php if ($isImage): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($tutorial['file_path']) ?>"
                                            alt="<?= htmlspecialchars($tutorial['title']) ?>" class="tutorial-thumbnail-img">
                                    <?php elseif ($tutorial['type'] === 'video'): ?>
                                        <div class="tutorial-thumbnail-video">
                                            <i class="fas fa-play-circle"></i>
                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($tutorial['file_path']) ?>"
                                                alt="<?= htmlspecialchars($tutorial['title']) ?>" class="tutorial-thumbnail-img"
                                                onerror="this.style.display='none'; this.parentElement.classList.add('no-image');">
                                        </div>
                                    <?php else: ?>
                                        <div class="tutorial-thumbnail-placeholder">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="tutorial-thumbnail-placeholder">
                                        <?php
                                        $typeIcons = [
                                            'video' => 'fa-play-circle',
                                            'pdf' => 'fa-file-pdf',
                                            'code' => 'fa-code',
                                            'article' => 'fa-file-alt'
                                        ];
                                        $icon = $typeIcons[$tutorial['type']] ?? 'fa-book';
                                        ?>
                                        <i class="fas <?= $icon ?>"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Durée/Badge type (pour les vidéos) -->
                                <?php if ($tutorial['type'] === 'video'): ?>
                                    <div class="tutorial-type-badge">
                                        <i class="fas fa-video"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>

                        <!-- Informations du tutoriel -->
                        <div class="tutorial-info">
                            <!-- Avatar et métadonnées -->
                            <div class="tutorial-meta">
                                <a href="<?= BASE_URL ?>/user/profile/<?= $tutorial['user_id'] ?>"
                                    class="tutorial-author-avatar">
                                    <?php
                                    $avatarInitial = strtoupper(substr($tutorial['prenom'] ?? 'U', 0, 1));
                                    $hasAvatar = !empty($tutorial['photo_path']);
                                    ?>
                                    <?php if ($hasAvatar): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($tutorial['photo_path']) ?>"
                                            alt="<?= htmlspecialchars($tutorial['prenom'] . ' ' . $tutorial['nom']) ?>"
                                            class="tutorial-avatar-img"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="avatar-placeholder" style="display: none;">
                                            <?= $avatarInitial ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <?= $avatarInitial ?>
                                        </div>
                                    <?php endif; ?>
                                </a>

                                <div class="tutorial-details">
                                    <!-- Titre -->
                                    <h3 class="tutorial-title">
                                        <a href="<?= BASE_URL ?>/tutorial/show/<?= $tutorial['id'] ?>">
                                            <?= htmlspecialchars($tutorial['title']) ?>
                                        </a>
                                    </h3>

                                    <!-- Auteur -->
                                    <a href="<?= BASE_URL ?>/user/profile/<?= $tutorial['user_id'] ?>"
                                        class="tutorial-author-name">
                                        <?= htmlspecialchars($tutorial['prenom'] . ' ' . $tutorial['nom']) ?>
                                    </a>

                                    <!-- Statistiques -->
                                    <div class="tutorial-stats">
                                        <span class="stat-item">
                                            <i class="fas fa-eye"></i> <?= number_format($tutorial['views'] ?? 0) ?> vues
                                        </span>
                                        <span class="stat-separator">•</span>
                                        <span class="stat-item">
                                            <?= timeAgo($tutorial['created_at']) ?>
                                        </span>
                                        <?php if (($tutorial['likes_count'] ?? 0) > 0): ?>
                                            <span class="stat-separator">•</span>
                                            <span class="stat-item">
                                                <i class="fas fa-heart"></i> <?= number_format($tutorial['likes_count']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Niveau (petit badge) -->
                                    <?php if (!empty($tutorial['level'])): ?>
                                        <div class="tutorial-level-badge">
                                            <?= htmlspecialchars($tutorial['level']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (($total_pages ?? 1) > 1): ?>
            <div class="tutorials-pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $current_category ? '&category=' . urlencode($current_category) : '' ?><?= $current_search ? '&search=' . urlencode($current_search) : '' ?><?= $current_sort ? '&sort=' . urlencode($current_sort) : '' ?>"
                        class="pagination-btn">
                        <i class="fas fa-chevron-left"></i> Précédent
                    </a>
                <?php endif; ?>

                <div class="pagination-info">
                    Page <?= $page ?> sur <?= $total_pages ?>
                </div>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $current_category ? '&category=' . urlencode($current_category) : '' ?><?= $current_search ? '&search=' . urlencode($current_search) : '' ?><?= $current_sort ? '&sort=' . urlencode($current_sort) : '' ?>"
                        class="pagination-btn">
                        Suivant <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* ===================================
   PAGE TUTORIELS STYLE YOUTUBE
   =================================== */

    .tutorials-page {
        min-height: calc(100vh - 200px);
        background: #f9f9f9;
        padding: 20px 0 40px;
    }

    .tutorials-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* ===================================
   BARRE DE RECHERCHE
   =================================== */

    .tutorials-search-bar {
        margin-bottom: 20px;
    }

    .search-form-tutorials {
        max-width: 600px;
        margin: 0 auto;
    }

    .search-input-wrapper {
        display: flex;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 40px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
    }

    .search-input-wrapper:focus-within {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        border-color: var(--primary-color);
    }

    .search-input-wrapper input {
        flex: 1;
        padding: 12px 20px;
        border: none;
        outline: none;
        font-size: 16px;
        background: transparent;
    }

    .search-input-wrapper input::placeholder {
        color: #999;
    }

    .search-btn {
        padding: 12px 24px;
        background: #f8f8f8;
        border: none;
        border-left: 1px solid #e0e0e0;
        cursor: pointer;
        color: #606060;
        transition: background 0.2s ease;
    }

    .search-btn:hover {
        background: #f0f0f0;
    }

    .search-btn i {
        font-size: 18px;
    }

    /* ===================================
   FILTRES HORIZONTAUX (CHIPS)
   =================================== */

    .tutorials-filters {
        margin-bottom: 20px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .tutorials-filters::-webkit-scrollbar {
        height: 4px;
    }

    .tutorials-filters::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 2px;
    }

    .filters-scroll {
        display: flex;
        gap: 12px;
        padding: 8px 0;
        min-width: max-content;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        color: #030303;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        white-space: nowrap;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .filter-chip:hover {
        background: #f0f0f0;
        border-color: #d0d0d0;
    }

    .filter-chip.active {
        background: #030303;
        color: white;
        border-color: #030303;
    }

    .filter-chip i {
        font-size: 12px;
    }

    /* ===================================
   BARRE DE TRI
   =================================== */

    .tutorials-sort-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 12px 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .sort-options {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .sort-label {
        font-size: 14px;
        color: #606060;
        font-weight: 500;
    }

    .sort-option {
        color: #606060;
        text-decoration: none;
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .sort-option:hover {
        background: #f0f0f0;
        color: #030303;
    }

    .sort-option.active {
        color: var(--primary-color);
        font-weight: 600;
        background: rgba(200, 16, 46, 0.1);
    }

    .btn-create-tutorial {
        padding: 10px 20px;
        background: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-create-tutorial:hover {
        background: #b8123a;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(200, 16, 46, 0.3);
    }

    /* ===================================
   RÉSULTATS DE RECHERCHE
   =================================== */

    .search-results-info {
        margin-bottom: 20px;
        padding: 12px 16px;
        background: white;
        border-radius: 8px;
        border-left: 4px solid var(--primary-color);
    }

    .search-results-info p {
        margin: 0;
        color: #606060;
        font-size: 14px;
    }

    .search-results-info strong {
        color: #030303;
    }

    /* ===================================
   GRILLE DE TUTORIELS (STYLE YOUTUBE)
   =================================== */

    .tutorials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .tutorial-card-youtube {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }

    .tutorial-card-youtube:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    /* Thumbnail */
    .tutorial-thumbnail-link {
        display: block;
        position: relative;
        width: 100%;
        padding-top: 56.25%;
        /* Ratio 16:9 */
        background: #000;
        overflow: hidden;
    }

    .tutorial-thumbnail-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .tutorial-thumbnail-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .tutorial-card-youtube:hover .tutorial-thumbnail-img {
        transform: scale(1.05);
    }

    .tutorial-thumbnail-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-size: 48px;
    }

    .tutorial-thumbnail-video {
        width: 100%;
        height: 100%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tutorial-thumbnail-video i {
        position: absolute;
        font-size: 64px;
        color: white;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        z-index: 2;
    }

    .tutorial-thumbnail-video.no-image {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .tutorial-type-badge {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Informations du tutoriel */
    .tutorial-info {
        padding: 12px;
    }

    .tutorial-meta {
        display: flex;
        gap: 12px;
    }

    .tutorial-author-avatar {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
    }

    .tutorial-author-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .tutorial-avatar-img {
        display: block;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
    }

    .tutorial-details {
        flex: 1;
        min-width: 0;
    }

    .tutorial-title {
        margin: 0 0 6px 0;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .tutorial-title a {
        color: #030303;
        text-decoration: none;
    }

    .tutorial-title a:hover {
        color: var(--primary-color);
    }

    .tutorial-author-name {
        display: block;
        color: #606060;
        font-size: 13px;
        text-decoration: none;
        margin-bottom: 4px;
    }

    .tutorial-author-name:hover {
        color: #030303;
    }

    .tutorial-stats {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #606060;
        font-size: 13px;
        margin-bottom: 4px;
    }

    .stat-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .stat-separator {
        color: #999;
    }

    .tutorial-level-badge {
        display: inline-block;
        padding: 2px 8px;
        background: #f0f0f0;
        color: #606060;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
        margin-top: 4px;
    }

    /* ===================================
   ÉTAT VIDE
   =================================== */

    .empty-tutorials {
        grid-column: 1 / -1;
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 12px;
    }

    .empty-icon {
        font-size: 64px;
        color: #ccc;
        margin-bottom: 20px;
    }

    .empty-tutorials h3 {
        margin: 0 0 12px 0;
        color: #030303;
        font-size: 20px;
    }

    .empty-tutorials p {
        margin: 0 0 24px 0;
        color: #606060;
        font-size: 14px;
    }

    .btn-create-empty {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 24px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-create-empty:hover {
        background: #b8123a;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(200, 16, 46, 0.3);
    }

    /* ===================================
   PAGINATION
   =================================== */

    .tutorials-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        margin-top: 40px;
    }

    .pagination-btn {
        padding: 10px 20px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        color: #030303;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .pagination-btn:hover {
        background: #f0f0f0;
        border-color: #d0d0d0;
    }

    .pagination-info {
        color: #606060;
        font-size: 14px;
    }

    /* ===================================
   RESPONSIVE
   =================================== */

    @media (max-width: 768px) {
        .tutorials-container {
            padding: 0 12px;
        }

        .tutorials-search-bar {
            margin-bottom: 16px;
        }

        .search-input-wrapper input {
            font-size: 14px;
            padding: 10px 16px;
        }

        .search-btn {
            padding: 10px 20px;
        }

        .filters-scroll {
            gap: 8px;
            padding: 6px 0;
        }

        .filter-chip {
            padding: 6px 12px;
            font-size: 13px;
        }

        .tutorials-sort-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .sort-options {
            flex-wrap: wrap;
            gap: 12px;
        }

        .tutorials-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }

        .tutorial-info {
            padding: 10px;
        }

        .tutorial-author-avatar {
            width: 36px;
            height: 36px;
        }

        .tutorial-title {
            font-size: 13px;
        }

        .tutorial-stats {
            font-size: 12px;
        }

        .tutorials-pagination {
            flex-direction: column;
            gap: 12px;
        }

        .pagination-btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .tutorials-grid {
            grid-template-columns: 1fr;
        }

        .tutorial-card-youtube {
            max-width: 100%;
        }
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>