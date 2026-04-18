<?php
$pageTitle = 'Admin Dashboard - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Charger Chart.js pour les graphiques
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';

// Security check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/home/index');
    exit;
}

// Préparer les données
$stats = $stats ?? [];
$recent_users = $recent_users ?? [];
?>

<div class="admin-dashboard-ultra">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar-ultra">
        <div class="sidebar-header">
            <div class="admin-logo">
                <i class="fas fa-shield-halved"></i>
                <span>Admin Panel</span>
            </div>
        </div>

        <nav class="admin-nav-ultra">
            <a href="#overview" class="nav-item-ultra active" data-section="overview">
                <i class="fas fa-chart-pie"></i>
                <span>Vue d'ensemble</span>
            </a>
            <a href="#users" class="nav-item-ultra" data-section="users">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
                <span class="count-badge"><?= $stats['total_users'] ?? 0 ?></span>
            </a>
            <a href="#forum" class="nav-item-ultra" data-section="forum">
                <i class="fas fa-comments"></i>
                <span>Forum</span>
                <span class="count-badge"><?= $stats['total_posts'] ?? 0 ?></span>
            </a>
            <a href="#tutorials" class="nav-item-ultra" data-section="tutorials">
                <i class="fas fa-book-open"></i>
                <span>Tutoriels</span>
                <span class="count-badge"><?= $stats['total_tutorials'] ?? 0 ?></span>
            </a>
            <a href="#projects" class="nav-item-ultra" data-section="projects">
                <i class="fas fa-project-diagram"></i>
                <span>Projets</span>
                <span class="count-badge"><?= $stats['total_projects'] ?? 0 ?></span>
            </a>
            <a href="#opportunities" class="nav-item-ultra" data-section="opportunities">
                <i class="fas fa-briefcase"></i>
                <span>Opportunités</span>
                <span class="count-badge"><?= $stats['total_jobs'] ?? 0 ?></span>
            </a>
            <a href="#blog" class="nav-item-ultra" data-section="blog">
                <i class="fas fa-blog"></i>
                <span>Blog</span>
            </a>
            <a href="#comments" class="nav-item-ultra" data-section="comments">
                <i class="fas fa-comment-dots"></i>
                <span>Commentaires</span>
            </a>
            <a href="#reports" class="nav-item-ultra" data-section="reports">
                <i class="fas fa-flag"></i>
                <span>Signalements</span>
                <?php if (($stats['pending_reports'] ?? 0) > 0): ?>
                <span class="alert-badge"><?= $stats['pending_reports'] ?></span>
                <?php endif; ?>
            </a>
            <a href="#newsletter" class="nav-item-ultra" data-section="newsletter">
                <i class="fas fa-envelope-open-text"></i>
                <span>Newsletter</span>
                <span class="count-badge"><?= $stats['total_subscribers'] ?? 0 ?></span>
            </a>
            <a href="#statistics" class="nav-item-ultra" data-section="statistics">
                <i class="fas fa-chart-bar"></i>
                <span>Statistiques</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/permissions" class="nav-item-ultra">
                <i class="fas fa-shield-alt"></i>
                <span>Permissions</span>
            </a>
            <a href="#settings" class="nav-item-ultra" data-section="settings">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>/home/index" class="btn-back-site">
                <i class="fas fa-home"></i> Retour au site
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-content-ultra">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <div class="topbar-left">
                <h1 id="sectionTitle"><i class="fas fa-chart-pie"></i> Vue d'ensemble</h1>
            </div>
            <div class="topbar-right">
                <div class="admin-profile">
                    <?php
                    $adminPhoto = $_SESSION['user_photo'] ?? '';
                    $adminName = ($_SESSION['user_prenom'] ?? 'Admin') . ' ' . ($_SESSION['user_nom'] ?? '');
                    $adminInitial = strtoupper(substr($_SESSION['user_prenom'] ?? 'A', 0, 1));
                    ?>
                    <div class="profile-avatar">
                        <?php if (!empty($adminPhoto)): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($adminPhoto) ?>"
                            alt="<?= htmlspecialchars($adminName) ?>">
                        <?php else: ?>
                        <div class="avatar-placeholder-admin"><?= $adminInitial ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <strong><?= htmlspecialchars($adminName) ?></strong>
                        <span>Administrateur</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Sections -->
        <div class="admin-sections">

            <!-- SECTION: VUE D'ENSEMBLE -->
            <section id="section-overview" class="admin-section-content active">
                <!-- Stats Cards -->
                <div class="stats-grid-admin">
                    <div class="stat-card-admin card-users">
                        <div class="stat-icon-admin">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-data">
                            <h3><?= formatNumber($stats['total_users'] ?? 0) ?></h3>
                            <p>Utilisateurs</p>
                            <span class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i> +12% ce mois
                            </span>
                        </div>
                    </div>

                    <div class="stat-card-admin card-posts">
                        <div class="stat-icon-admin">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-data">
                            <h3><?= formatNumber($stats['total_posts'] ?? 0) ?></h3>
                            <p>Discussions</p>
                            <span class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i> +8% ce mois
                            </span>
                        </div>
                    </div>

                    <div class="stat-card-admin card-tutorials">
                        <div class="stat-icon-admin">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-data">
                            <h3><?= formatNumber($stats['total_tutorials'] ?? 0) ?></h3>
                            <p>Tutoriels</p>
                            <span class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i> +15% ce mois
                            </span>
                        </div>
                    </div>

                    <div class="stat-card-admin card-reports">
                        <div class="stat-icon-admin">
                            <i class="fas fa-flag"></i>
                        </div>
                        <div class="stat-data">
                            <h3><?= formatNumber($stats['pending_reports'] ?? 0) ?></h3>
                            <p>Signalements</p>
                            <span class="stat-trend warning">
                                <i class="fas fa-exclamation-circle"></i> À traiter
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="charts-row">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-line"></i> Activité des Utilisateurs</h3>
                        <canvas id="activityChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-pie"></i> Répartition du Contenu</h3>
                        <canvas id="contentChart"></canvas>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="recent-section">
                    <div class="section-header">
                        <h2><i class="fas fa-history"></i> Nouveaux Utilisateurs</h2>
                        <a href="#users" data-section="users" class="btn-view-all">
                            Voir tout <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Université</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_users)): ?>
                                <?php foreach (array_slice($recent_users, 0, 5) as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <?php
                                                    $userPhoto = $user['photo_path'] ?? '';
                                                    $userName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
                                                    $userInitial = strtoupper(substr($user['prenom'] ?? 'U', 0, 1));
                                                    ?>
                                            <?php if (!empty($userPhoto)): ?>
                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($userPhoto) ?>"
                                                alt="<?= htmlspecialchars($userName) ?>">
                                            <?php else: ?>
                                            <div class="avatar-placeholder-dash"><?= $userInitial ?></div>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($userName ?: 'Utilisateur') ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($user['university'] ?? '-') ?></td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <span class="status-badge <?= $user['status'] ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" onclick="viewUser(<?= $user['id'] ?>)"
                                                title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-action btn-edit" onclick="editUser(<?= $user['id'] ?>)"
                                                title="Éditer">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun utilisateur récent</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- SECTION: UTILISATEURS -->
            <section id="section-users" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-users"></i> Gestion des Utilisateurs</h2>
                    <div class="header-actions">
                        <div class="search-box-admin">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchUsers" placeholder="Rechercher un utilisateur..."
                                onkeyup="searchInTable('usersTable', this.value)">
                        </div>
                        <select class="filter-select-admin" onchange="filterUsers(this.value)">
                            <option value="">Tous les statuts</option>
                            <option value="active">Actifs</option>
                            <option value="pending">En attente</option>
                            <option value="suspended">Suspendus</option>
                            <option value="banned">Bannis</option>
                        </select>
                    </div>
                </div>

                <div class="content-loading" id="usersLoading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des utilisateurs...
                </div>

                <div class="table-responsive" id="usersContent" style="display: none;">
                    <table class="admin-table" id="usersTable">
                        <!-- Sera chargé dynamiquement -->
                    </table>
                </div>
            </section>

            <!-- SECTION: FORUM -->
            <section id="section-forum" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-comments"></i> Gestion du Forum</h2>
                </div>

                <!-- Onglets -->
                <div class="forum-tabs">
                    <button class="forum-tab-btn active" data-tab="categories">
                        <i class="fas fa-tags"></i> Catégories
                    </button>
                    <button class="forum-tab-btn" data-tab="posts">
                        <i class="fas fa-comments"></i> Discussions
                    </button>
                </div>

                <!-- TAB: CATÉGORIES -->
                <div id="forum-tab-categories" class="forum-tab-content active">
                    <div class="section-subheader">
                        <h3><i class="fas fa-tags"></i> Gestion des Catégories</h3>
                        <button class="btn-primary-admin" onclick="openCategoryModal()">
                            <i class="fas fa-plus"></i> Nouvelle Catégorie
                        </button>
                    </div>

                    <div class="content-loading" id="categoriesLoading">
                        <i class="fas fa-spinner fa-spin"></i> Chargement des catégories...
                    </div>

                    <div class="categories-grid" id="categoriesContent" style="display: none;">
                        <!-- Sera chargé dynamiquement -->
                    </div>
                </div>

                <!-- TAB: DISCUSSIONS -->
                <div id="forum-tab-posts" class="forum-tab-content">
                    <div class="section-subheader">
                        <h3><i class="fas fa-comments"></i> Discussions du Forum</h3>
                        <div class="header-actions">
                            <div class="search-box-admin">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchPosts" placeholder="Rechercher..."
                                    onkeyup="searchInTable('postsTable', this.value)">
                            </div>
                            <select class="filter-select-admin" id="postsStatusFilter"
                                onchange="loadForumPosts(this.value)">
                                <option value="">📊 Tous les statuts</option>
                                <option value="active">✅ Actives</option>
                                <option value="hidden">👁️ Masquées</option>
                                <option value="deleted">🗑️ Supprimées</option>
                            </select>
                        </div>
                    </div>

                    <div class="content-loading" id="postsLoading">
                        <i class="fas fa-spinner fa-spin"></i> Chargement des discussions...
                    </div>

                    <div class="table-responsive" id="postsContent" style="display: none;">
                        <table class="admin-table" id="postsTable">
                            <!-- Sera chargé dynamiquement -->
                        </table>
                    </div>
                </div>
            </section>

            <!-- SECTION: TUTORIELS -->
            <section id="section-tutorials" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-book-open"></i> Gestion des Tutoriels</h2>
                    <div class="header-actions">
                        <div class="search-box-admin">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchTutorials" placeholder="Rechercher un tutoriel..."
                                onkeyup="searchInTable('tutorialsTable', this.value)">
                        </div>
                        <select class="filter-select-admin" id="tutorialsTypeFilter" onchange="loadTutorialsData()">
                            <option value="">📚 Tous les types</option>
                            <option value="video">🎥 Vidéos</option>
                            <option value="pdf">📄 PDF</option>
                            <option value="code">💻 Code</option>
                            <option value="article">📝 Articles</option>
                        </select>
                        <select class="filter-select-admin" id="tutorialsStatusFilter" onchange="loadTutorialsData()">
                            <option value="">📊 Tous les statuts</option>
                            <option value="pending">⏳ En attente</option>
                            <option value="active">✅ Actifs</option>
                            <option value="hidden">👁️ Masqués</option>
                            <option value="deleted">🗑️ Supprimés</option>
                        </select>
                    </div>
                </div>

                <div class="content-loading" id="tutorialsLoading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des tutoriels...
                </div>

                <div class="table-responsive" id="tutorialsContent" style="display: none;">
                    <table class="admin-table" id="tutorialsTable">
                        <!-- Sera chargé dynamiquement -->
                    </table>
                </div>
            </section>

            <!-- SECTION: PROJETS -->
            <section id="section-projects" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-project-diagram"></i> Gestion des Projets</h2>
                    <div class="header-actions">
                        <div class="search-box-admin">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchProjects" placeholder="Rechercher..."
                                onkeyup="searchInTable('projectsTable', this.value)">
                        </div>
                        <select class="filter-select-admin" id="projectsStatusFilter" onchange="loadProjectsData()">
                            <option value="">📊 Tous les statuts</option>
                            <option value="planning">📋 Planification</option>
                            <option value="in_progress">⚡ En cours</option>
                            <option value="completed">✅ Terminés</option>
                            <option value="archived">📦 Archivés</option>
                        </select>
                        <select class="filter-select-admin" id="projectsVisibilityFilter" onchange="loadProjectsData()">
                            <option value="">👁️ Toutes visibilités</option>
                            <option value="public">🌐 Publics</option>
                            <option value="private">🔒 Privés</option>
                        </select>
                    </div>
                </div>

                <div class="content-loading" id="projectsLoading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des projets...
                </div>

                <div class="table-responsive" id="projectsContent" style="display: none;">
                    <table class="admin-table" id="projectsTable">
                        <!-- Sera chargé dynamiquement -->
                    </table>
                </div>
            </section>

            <!-- SECTION: OPPORTUNITÉS -->
            <section id="section-opportunities" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-briefcase"></i> Gestion des Opportunités</h2>
                    <div class="header-actions">
                        <button class="btn-primary-admin" onclick="scrapeJobsNow()" id="scrapeJobsBtn">
                            <i class="fas fa-robot"></i> Scraper les Offres
                        </button>
                        <div class="search-box-admin">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchJobs" placeholder="Rechercher..."
                                onkeyup="searchInTable('jobsTable', this.value)">
                        </div>
                        <select class="filter-select-admin" id="jobsTypeFilter" onchange="loadJobsData()">
                            <option value="">💼 Tous les types</option>
                            <option value="stage">🎓 Stages</option>
                            <option value="emploi">👔 Emplois</option>
                            <option value="hackathon">💻 Hackathons</option>
                            <option value="formation">📚 Formations</option>
                            <option value="freelance">🚀 Freelance</option>
                        </select>
                        <select class="filter-select-admin" id="jobsStatusFilter" onchange="loadJobsData()">
                            <option value="">📊 Tous les statuts</option>
                            <option value="pending">⏳ En attente</option>
                            <option value="active">✅ Actives</option>
                            <option value="closed">🔒 Fermées</option>
                            <option value="expired">⏰ Expirées</option>
                        </select>
                    </div>
                </div>

                <div id="scrapeResultsAlert" style="display: none; margin-bottom: 20px;"></div>

                <div class="content-loading" id="jobsLoading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des opportunités...
                </div>

                <div class="table-responsive" id="jobsContent" style="display: none;">
                    <table class="admin-table" id="jobsTable">
                        <!-- Sera chargé dynamiquement -->
                    </table>
                </div>
            </section>

            <!-- SECTION: BLOG -->
            <section id="section-blog" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-blog"></i> Gestion du Blog</h2>
                </div>

                <!-- Tabs pour Articles et Catégories -->
                <div class="tabs-container">
                    <div class="tabs-header">
                        <button class="tab-btn active" onclick="switchBlogTab('articles')">
                            <i class="fas fa-newspaper"></i> Articles
                        </button>
                        <button class="tab-btn" onclick="switchBlogTab('categories')">
                            <i class="fas fa-tags"></i> Catégories
                        </button>
                    </div>

                    <!-- Tab: Articles -->
                    <div id="blog-tab-articles" class="tab-content active">
                        <div class="section-header" style="margin-top: 20px;">
                            <div class="header-actions">
                                <a href="<?= BASE_URL ?>/blog/create" class="btn-primary-admin" target="_blank">
                                    <i class="fas fa-plus"></i> Nouvel Article
                                </a>
                                <div class="search-box-admin">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="searchBlog" placeholder="Rechercher..."
                                        onkeyup="searchInTable('blogTable', this.value)">
                                </div>
                                <select class="filter-select-admin" id="blogStatusFilter" onchange="loadBlogPosts()">
                                    <option value="">📊 Tous les statuts</option>
                                    <option value="published">✅ Publiés</option>
                                    <option value="draft">📝 Brouillons</option>
                                    <option value="archived">📦 Archivés</option>
                                </select>
                            </div>
                        </div>

                        <div class="content-loading" id="blogLoading">
                            <i class="fas fa-spinner fa-spin"></i> Chargement des articles...
                        </div>

                        <div class="table-responsive" id="blogContent" style="display: none;">
                            <table class="admin-table" id="blogTable">
                                <!-- Sera chargé dynamiquement -->
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Catégories -->
                    <div id="blog-tab-categories" class="tab-content">
                        <div class="section-header" style="margin-top: 20px;">
                            <div class="header-actions">
                                <button class="btn-primary-admin" onclick="openBlogCategoryModal()">
                                    <i class="fas fa-plus"></i> Nouvelle Catégorie
                                </button>
                            </div>
                        </div>

                        <div id="blogCategoriesGrid">
                            <div class="content-loading">
                                <i class="fas fa-spinner fa-spin"></i> Chargement des catégories...
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: COMMENTAIRES -->
            <section id="section-comments" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-comment-dots"></i> Modération des Commentaires</h2>
                    <div class="header-actions">
                        <div class="search-box-admin">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchComments" placeholder="Rechercher..."
                                onkeyup="searchInTable('commentsTable', this.value)">
                        </div>
                        <select class="filter-select-admin" id="commentsTypeFilter" onchange="loadCommentsData()">
                            <option value="">📍 Tous les types</option>
                            <option value="post">💬 Forum</option>
                            <option value="tutorial">📚 Tutoriels</option>
                            <option value="blog">📝 Blog</option>
                        </select>
                        <select class="filter-select-admin" id="commentsStatusFilter" onchange="loadCommentsData()">
                            <option value="">📊 Tous les statuts</option>
                            <option value="active">✅ Actifs</option>
                            <option value="hidden">👁️ Masqués</option>
                            <option value="deleted">🗑️ Supprimés</option>
                        </select>
                    </div>
                </div>

                <div class="content-loading" id="commentsLoading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des commentaires...
                </div>

                <div class="table-responsive" id="commentsContent" style="display: none;">
                    <table class="admin-table" id="commentsTable">
                        <!-- Sera chargé dynamiquement -->
                    </table>
                </div>
            </section>

            <!-- SECTION: NEWSLETTER -->
            <section id="section-newsletter" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-envelope-open-text"></i> Gestion de la Newsletter</h2>
                    <div class="header-actions">
                        <div class="search-box-admin">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchNewsletter" placeholder="Rechercher..."
                                onkeyup="searchInTable('newsletterTable', this.value)">
                        </div>
                        <select class="filter-select-admin" id="newsletterStatusFilter" onchange="loadNewsletterData()">
                            <option value="">📊 Tous les statuts</option>
                            <option value="active">✅ Actifs</option>
                            <option value="unsubscribed">🚫 Désabonnés</option>
                            <option value="bounced">⚠️ Rebondis</option>
                        </select>
                        <button class="btn-primary-admin" onclick="exportNewsletterSubscribers()">
                            <i class="fas fa-download"></i> Exporter CSV
                        </button>
                    </div>
                </div>

                <!-- Stats Newsletter -->
                <div class="stats-grid-admin" style="margin-bottom: 30px;" id="newsletterStats">
                    <div class="stat-card-admin" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <div class="stat-icon-admin">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-data">
                            <h3 id="statsActiveSubscribers">0</h3>
                            <p>Abonnés actifs</p>
                        </div>
                    </div>

                    <div class="stat-card-admin" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                        <div class="stat-icon-admin">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div class="stat-data">
                            <h3 id="statsUnsubscribed">0</h3>
                            <p>Désabonnés</p>
                        </div>
                    </div>

                    <div class="stat-card-admin" style="background: linear-gradient(135deg, #ffc107, #ff9800);">
                        <div class="stat-icon-admin">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-data">
                            <h3 id="statsBounced">0</h3>
                            <p>Rebondis</p>
                        </div>
                    </div>
                </div>

                <div class="content-loading" id="newsletterLoading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des abonnés...
                </div>

                <div class="table-responsive" id="newsletterContent" style="display: none;">
                    <table class="admin-table" id="newsletterTable">
                        <!-- Sera chargé dynamiquement -->
                    </table>
                </div>
            </section>

            <!-- SECTION: STATISTIQUES AVANCÉES -->
            <section id="section-statistics" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-chart-bar"></i> Statistiques Avancées</h2>
                </div>

                <!-- Stats en temps réel -->
                <div class="stats-grid-admin" style="margin-bottom: 30px;">
                    <div class="stat-card-admin" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <div class="stat-icon-admin" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-data">
                            <h3 id="onlineUsersCount">0</h3>
                            <p style="color: white;">En ligne maintenant</p>
                        </div>
                    </div>
                    <div class="stat-card-admin" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <div class="stat-icon-admin" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-data">
                            <h3 id="todayVisitsCount">0</h3>
                            <p style="color: white;">Visites aujourd'hui</p>
                        </div>
                    </div>
                    <div class="stat-card-admin" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <div class="stat-icon-admin" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="stat-data">
                            <h3 id="countriesCount">0</h3>
                            <p style="color: white;">Pays</p>
                        </div>
                    </div>
                    <div class="stat-card-admin" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                        <div class="stat-icon-admin" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="stat-data">
                            <h3 id="topUserName">-</h3>
                            <p style="color: white;">Utilisateur le plus actif</p>
                        </div>
                    </div>
                </div>

                <!-- Graphiques -->
                <div class="charts-row" style="margin-bottom: 30px;">
                    <div class="chart-card">
                        <h3><i class="fas fa-map-marked-alt"></i> Visiteurs par Pays</h3>
                        <canvas id="countriesChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <h3><i class="fas fa-devices"></i> Types d'Appareils</h3>
                        <canvas id="devicesChart"></canvas>
                    </div>
                </div>

                <!-- Utilisateurs en ligne -->
                <div class="recent-section">
                    <div class="section-header">
                        <h2><i class="fas fa-users-cog"></i> Utilisateurs en Ligne</h2>
                        <button class="btn-primary-admin" onclick="loadStatistics()">
                            <i class="fas fa-sync"></i> Actualiser
                        </button>
                    </div>
                    <div id="onlineUsersTable">
                        <div class="content-loading">
                            <i class="fas fa-spinner fa-spin"></i> Chargement...
                        </div>
                    </div>
                </div>

                <!-- Top utilisateurs actifs -->
                <div class="recent-section">
                    <div class="section-header">
                        <h2><i class="fas fa-trophy"></i> Utilisateurs les Plus Actifs (7 derniers jours)</h2>
                    </div>
                    <div id="topUsersTable">
                        <div class="content-loading">
                            <i class="fas fa-spinner fa-spin"></i> Chargement...
                        </div>
                    </div>
                </div>

                <!-- Historique des visites -->
                <div class="recent-section">
                    <div class="section-header">
                        <h2><i class="fas fa-history"></i> Dernières Visites (200 plus récentes)</h2>
                    </div>
                    <div class="table-responsive" id="visitorLogsTable">
                        <div class="content-loading">
                            <i class="fas fa-spinner fa-spin"></i> Chargement...
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: SIGNALEMENTS -->
            <section id="section-reports" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-flag"></i> Gestion des Signalements</h2>
                    <div class="header-actions">
                        <div class="search-box-admin">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchReports" placeholder="Rechercher..."
                                onkeyup="searchInTable('reportsTable', this.value)">
                        </div>
                        <select class="filter-select-admin" id="reportsTypeFilter" onchange="loadReportsData()">
                            <option value="">📍 Tous les types</option>
                            <option value="post">💬 Forum</option>
                            <option value="tutorial">📚 Tutoriel</option>
                            <option value="comment">💭 Commentaire</option>
                            <option value="user">👤 Utilisateur</option>
                        </select>
                        <select class="filter-select-admin" id="reportsStatusFilter" onchange="loadReportsData()">
                            <option value="pending">⏳ En attente</option>
                            <option value="reviewed">👀 Examinés</option>
                            <option value="resolved">✅ Résolus</option>
                            <option value="dismissed">❌ Rejetés</option>
                        </select>
                    </div>
                </div>

                <div class="content-loading" id="reportsLoading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des signalements...
                </div>

                <div class="table-responsive" id="reportsContent" style="display: none;">
                    <table class="admin-table" id="reportsTable">
                        <!-- Sera chargé dynamiquement -->
                    </table>
                </div>
            </section>

            <!-- SECTION: PARAMÈTRES -->
            <section id="section-settings" class="admin-section-content">
                <div class="section-header">
                    <h2><i class="fas fa-cog"></i> Paramètres du Système</h2>
                </div>

                <!-- Onglets des paramètres -->
                <div class="forum-tabs">
                    <button class="forum-tab-btn active" data-tab="general">
                        <i class="fas fa-globe"></i> Général
                    </button>
                    <button class="forum-tab-btn" data-tab="security">
                        <i class="fas fa-shield-alt"></i> Sécurité
                    </button>
                    <button class="forum-tab-btn" data-tab="moderation">
                        <i class="fas fa-gavel"></i> Modération
                    </button>
                    <button class="forum-tab-btn" data-tab="backup">
                        <i class="fas fa-download"></i> Sauvegarde
                    </button>
                    <button class="forum-tab-btn" data-tab="system">
                        <i class="fas fa-server"></i> Système
                    </button>
                </div>

                <div class="content-loading" id="settingsLoading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des paramètres...
                </div>

                <!-- Container for settings tabs -->
                <div id="settingsContainer" style="display: none;">
                    <!-- TAB: GÉNÉRAL -->
                    <div id="settings-tab-general" class="forum-tab-content active">
                        <form id="settingsGeneralForm" class="settings-form">
                            <!-- Sera chargé dynamiquement -->
                        </form>
                    </div>

                    <!-- TAB: SÉCURITÉ -->
                    <div id="settings-tab-security" class="forum-tab-content">
                        <form id="settingsSecurityForm" class="settings-form">
                            <!-- Sera chargé dynamiquement -->
                        </form>
                    </div>

                    <!-- TAB: MODÉRATION -->
                    <div id="settings-tab-moderation" class="forum-tab-content">
                        <form id="settingsModerationForm" class="settings-form">
                            <!-- Sera chargé dynamiquement -->
                        </form>
                    </div>

                    <!-- TAB: SAUVEGARDE -->
                    <div id="settings-tab-backup" class="forum-tab-content">
                        <div class="settings-form">
                            <div class="settings-group">
                                <h3><i class="fas fa-database"></i> Sauvegarde de la Base de Données</h3>

                                <div class="backup-section">
                                    <div class="backup-info">
                                        <i class="fas fa-info-circle"></i>
                                        <div>
                                            <p style="margin: 0 0 10px 0; color: #495057;">
                                                Téléchargez une copie complète de votre base de données au format SQL.
                                            </p>
                                            <p style="margin: 0; color: #6c757d; font-size: 0.9rem;">
                                                Le fichier inclut la structure des tables et toutes les données.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="backup-actions">
                                        <button type="button" class="btn-backup-full"
                                            onclick="downloadDatabaseBackup()">
                                            <i class="fas fa-download"></i> Télécharger la Sauvegarde Complète
                                        </button>

                                        <button type="button" class="btn-backup-structure"
                                            onclick="downloadDatabaseBackup('structure')">
                                            <i class="fas fa-code"></i> Structure Seule (sans données)
                                        </button>
                                    </div>

                                    <div class="backup-stats" id="backupStats">
                                        <div class="backup-stat-item">
                                            <i class="fas fa-clock"></i>
                                            <div>
                                                <strong>Dernière sauvegarde</strong>
                                                <span id="lastBackupTime">Jamais</span>
                                            </div>
                                        </div>
                                        <div class="backup-stat-item">
                                            <i class="fas fa-database"></i>
                                            <div>
                                                <strong>Taille estimée</strong>
                                                <span id="dbSize">Calcul...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h3><i class="fas fa-exclamation-triangle"></i> Recommandations</h3>
                                <ul style="color: #6c757d; line-height: 1.8;">
                                    <li>✅ Effectuez des sauvegardes régulières (hebdomadaire recommandé)</li>
                                    <li>✅ Conservez plusieurs versions de sauvegarde</li>
                                    <li>✅ Stockez les sauvegardes dans un endroit sécurisé</li>
                                    <li>✅ Testez vos sauvegardes régulièrement</li>
                                    <li>⚠️ Ne partagez jamais vos fichiers de sauvegarde</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: SYSTÈME -->
                    <div id="settings-tab-system" class="forum-tab-content">
                        <div class="system-stats-grid" id="systemStatsContent">
                            <!-- Sera chargé dynamiquement -->
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>
</div>

<style>
/* ================================
   LAYOUT PRINCIPAL
   ================================ */
.admin-dashboard-ultra {
    display: grid;
    grid-template-columns: 280px 1fr;
    min-height: calc(100vh - 70px);
    background: #f5f7fa;
}

/* ================================
   SIDEBAR
   ================================ */
.admin-sidebar-ultra {
    background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
    color: white;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: calc(100vh - 70px);
    overflow-y: auto;
}

.sidebar-header {
    padding: 30px 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.admin-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.4rem;
    font-weight: 800;
}

.admin-logo i {
    font-size: 1.8rem;
    color: var(--primary-color);
}

.admin-nav-ultra {
    flex: 1;
    padding: 20px 0;
}

.nav-item-ultra {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 25px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    margin: 5px 15px;
    border-radius: 12px;
}

.nav-item-ultra::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 0;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 0 4px 4px 0;
    transition: height 0.3s ease;
}

.nav-item-ultra:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.nav-item-ultra.active {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.2), rgba(52, 152, 219, 0.1));
    color: white;
}

.nav-item-ultra.active::before {
    height: 70%;
}

.nav-item-ultra i {
    font-size: 1.2rem;
    width: 24px;
}

.nav-item-ultra span {
    flex: 1;
    font-weight: 600;
}

.count-badge {
    padding: 4px 10px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
}

.alert-badge {
    padding: 4px 10px;
    background: #ff6b6b;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    animation: pulse 2s infinite;
}

.sidebar-footer {
    padding: 20px 25px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-back-site {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-back-site:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* ================================
   MAIN CONTENT
   ================================ */
.admin-content-ultra {
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.admin-topbar {
    background: white;
    padding: 25px 35px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 99;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.topbar-left h1 {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 12px;
}

.topbar-left h1 i {
    color: var(--primary-color);
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.profile-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--primary-color);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-admin {
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

.profile-info strong {
    display: block;
    color: var(--dark-color);
    font-weight: 700;
}

.profile-info span {
    font-size: 0.85rem;
    color: #6c757d;
}

/* ================================
   SECTIONS CONTENT
   ================================ */
.admin-sections {
    padding: 35px;
    flex: 1;
}

.admin-section-content {
    display: none;
    animation: fadeInSection 0.5s ease;
}

.admin-section-content.active {
    display: block;
}

@keyframes fadeInSection {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ================================
   STATS CARDS
   ================================ */
.stats-grid-admin {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
    margin-bottom: 35px;
}

.stat-card-admin {
    background: white;
    padding: 28px;
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card-admin::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.1;
}

.card-users::before {
    background: var(--primary-color);
}

.card-posts::before {
    background: #28a745;
}

.card-tutorials::before {
    background: #ffc107;
}

.card-reports::before {
    background: #dc3545;
}

.stat-card-admin:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-icon-admin {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.card-users .stat-icon-admin {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.card-posts .stat-icon-admin {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.card-tutorials .stat-icon-admin {
    background: linear-gradient(135deg, #ffc107, #ff9800);
}

.card-reports .stat-icon-admin {
    background: linear-gradient(135deg, #dc3545, #c82333);
}

.stat-data h3 {
    font-size: 2.2rem;
    font-weight: 900;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.stat-data p {
    color: #6c757d;
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.stat-trend {
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.stat-trend.positive {
    color: #28a745;
}

.stat-trend.warning {
    color: #ffc107;
}

/* ================================
   CHARTS
   ================================ */
.charts-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 35px;
}

.chart-card {
    background: white;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.chart-card h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-card h3 i {
    color: var(--primary-color);
}

/* ================================
   SECTION HEADER
   ================================ */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 20px;
}

.section-header h2 {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-header h2 i {
    color: var(--primary-color);
}

.header-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.search-box-admin {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.search-box-admin:focus-within {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.search-box-admin i {
    color: var(--primary-color);
}

.search-box-admin input {
    border: none;
    background: transparent;
    font-size: 0.95rem;
    color: var(--dark-color);
    width: 250px;
}

.search-box-admin input:focus {
    outline: none;
}

.filter-select-admin {
    padding: 10px 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 50px;
    color: var(--dark-color);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select-admin:focus {
    outline: none;
    border-color: var(--primary-color);
}

.btn-filter-quick {
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.btn-filter-quick:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
}

.btn-filter-quick:active {
    transform: translateY(0);
}

/* Forum Tabs */
.forum-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid #e9ecef;
}

.forum-tab-btn {
    padding: 12px 24px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.forum-tab-btn:hover {
    color: var(--primary-color);
}

.forum-tab-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.forum-tab-content {
    display: none;
}

.forum-tab-content.active {
    display: block;
}

.section-subheader {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-subheader h3 {
    margin: 0;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.category-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    position: relative;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.category-card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.category-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.category-info h4 {
    margin: 0 0 5px 0;
    color: var(--dark-color);
}

.category-info p {
    margin: 0;
    font-size: 0.85rem;
    color: #6c757d;
}

.category-description {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 15px;
    line-height: 1.5;
}

.category-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.category-post-count {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
    font-size: 0.9rem;
}

.category-actions {
    display: flex;
    gap: 8px;
}

.category-status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.category-status-badge.active {
    background: #d4edda;
    color: #155724;
}

.category-status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.post-cell {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.post-cell i {
    margin-top: 3px;
}

.post-cell strong {
    display: block;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.category-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #e9ecef;
    color: #495057;
}

/* Settings Forms */
.settings-form {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.settings-group {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
}

.settings-group:last-child {
    border-bottom: none;
}

.settings-group h3 {
    margin: 0 0 20px 0;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 15px;
}

.setting-info {
    flex: 1;
}

.setting-info label {
    display: block;
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
}

.setting-info small {
    color: #6c757d;
    font-size: 0.85rem;
}

.setting-control input[type="text"],
.setting-control input[type="number"],
.setting-control input[type="email"] {
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    width: 250px;
    font-size: 0.95rem;
}

.setting-control input:focus {
    outline: none;
    border-color: #667eea;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 30px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 30px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

.toggle-switch input:checked+.toggle-slider {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.toggle-switch input:checked+.toggle-slider:before {
    transform: translateX(30px);
}

.system-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.system-stat-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    text-align: center;
}

.system-stat-card i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.system-stat-card h4 {
    margin: 0 0 10px 0;
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
}

.system-stat-card .stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--dark-color);
}

/* Backup Section */
.backup-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 25px;
}

.backup-info {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
    padding: 20px;
    background: white;
    border-radius: 10px;
    border-left: 4px solid #007bff;
}

.backup-info i {
    font-size: 2rem;
    color: #007bff;
}

.backup-actions {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.btn-backup-full {
    flex: 1;
    padding: 18px 24px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-backup-full:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.btn-backup-structure {
    flex: 1;
    padding: 18px 24px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-backup-structure:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.backup-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.backup-stat-item {
    background: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.backup-stat-item i {
    font-size: 2rem;
    color: #667eea;
}

.backup-stat-item strong {
    display: block;
    color: #6c757d;
    font-size: 0.85rem;
    margin-bottom: 5px;
}

.backup-stat-item span {
    display: block;
    color: var(--dark-color);
    font-weight: 600;
    font-size: 1.1rem;
}

.btn-primary-admin {
    padding: 10px 22px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-primary-admin:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
}

.btn-view-all {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-view-all:hover {
    gap: 12px;
}

/* ================================
   TABLES
   ================================ */
.recent-section {
    background: white;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.table-responsive {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table thead {
    background: #f8f9fa;
}

.admin-table th {
    padding: 15px;
    text-align: left;
    font-weight: 700;
    color: var(--dark-color);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.admin-table td {
    padding: 18px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.admin-table tbody tr {
    transition: all 0.3s ease;
}

.admin-table tbody tr:hover {
    background: #f8f9fa;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-cell img,
.avatar-placeholder-dash {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
}

.avatar-placeholder-dash {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
}

.user-cell span {
    font-weight: 600;
    color: var(--dark-color);
}

.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: capitalize;
}

.status-badge.active {
    background: #d4edda;
    color: #28a745;
}

.status-badge.pending {
    background: #fff3cd;
    color: #ffc107;
}

.status-badge.suspended {
    background: #f8d7da;
    color: #dc3545;
}

.status-badge.banned {
    background: #2c3e50;
    color: white;
}

.role-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.role-admin {
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    color: white;
}

.role-user {
    background: #e7f3ff;
    color: var(--primary-color);
}

.role-company {
    background: #fff8e1;
    color: #ffc107;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-action {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.btn-view {
    background: #e7f3ff;
    color: var(--primary-color);
}

.btn-view:hover {
    background: var(--primary-color);
    color: white;
}

.btn-edit {
    background: #fff8e1;
    color: #ffc107;
}

.btn-edit:hover {
    background: #ffc107;
    color: white;
}

.btn-delete {
    background: #ffebee;
    color: #dc3545;
}

.btn-delete:hover {
    background: #dc3545;
    color: white;
}

.btn-warning {
    background: #fff8e1;
    color: #ffc107;
}

.btn-warning:hover {
    background: #ffc107;
    color: white;
}

.btn-success {
    background: #d4edda;
    color: #28a745;
}

.btn-success:hover {
    background: #28a745;
    color: white;
}

.btn-danger {
    background: #ffebee;
    color: #dc3545;
}

.btn-danger:hover {
    background: #dc3545;
    color: white;
}

/* Modal Utilisateur */
.user-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.3s ease;
}

.user-modal-container {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    animation: slideUp 0.3s ease;
}

.user-modal-header {
    padding: 25px 30px;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.user-modal-header h3 {
    margin: 0;
    color: var(--dark-color);
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-close-modal {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    color: #6c757d;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.btn-close-modal:hover {
    background: var(--danger-color);
    border-color: var(--danger-color);
    color: white;
    transform: rotate(90deg);
}

.user-modal-body {
    padding: 30px;
    overflow-y: auto;
    flex: 1;
}

.user-profile-section {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    padding-bottom: 25px;
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 25px;
}

.user-profile-info h2 {
    margin: 0 0 8px;
    color: var(--dark-color);
}

.user-profile-info p {
    color: #6c757d;
    margin-bottom: 12px;
}

.user-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.user-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.user-documents-section {
    margin: 30px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.user-documents-section h4 {
    margin: 0 0 15px 0;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 10px;
}

.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.document-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    text-decoration: none;
    color: #495057;
    transition: all 0.3s ease;
}

.document-item:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.document-item i:first-child {
    font-size: 24px;
    color: #667eea;
}

.document-item span {
    flex: 1;
    font-weight: 600;
}

.document-item i:last-child {
    font-size: 14px;
    color: #6c757d;
}

.detail-item {
    display: flex;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
}

.detail-item i {
    color: var(--primary-color);
    font-size: 1.3rem;
    width: 25px;
    flex-shrink: 0;
}

.detail-item strong {
    display: block;
    color: var(--dark-color);
    font-size: 0.85rem;
    margin-bottom: 4px;
}

.detail-item span {
    color: #6c757d;
    font-size: 0.95rem;
}

.user-bio-section {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.user-bio-section h4 {
    margin: 0 0 12px;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-bio-section p {
    color: #2c3e50;
    line-height: 1.7;
    margin: 0;
}

.user-modal-footer {
    padding: 20px 30px;
    border-top: 2px solid #e9ecef;
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

/* Form Styles for Modals */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #495057;
    font-weight: 600;
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

.btn-modal-profile {
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-modal-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
}

.btn-modal-close {
    padding: 12px 24px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    color: #6c757d;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
}

.btn-modal-close:hover {
    background: #e9ecef;
}

/* Animations */
@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateX(-20px);
    }
}

@keyframes flashGreen {

    0%,
    100% {
        background: transparent;
    }

    50% {
        background: rgba(40, 167, 69, 0.1);
    }
}

/* ================================
   LOADING STATE
   ================================ */
.content-loading {
    text-align: center;
    padding: 80px 20px;
    color: #6c757d;
    font-size: 1.1rem;
}

.content-loading i {
    font-size: 3rem;
    margin-bottom: 15px;
    display: block;
}

/* ================================
   SETTINGS GRID
   ================================ */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.settings-card {
    background: white;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: all 0.3s ease;
}

.settings-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.settings-card h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.settings-card h3 i {
    color: var(--primary-color);
}

.settings-card p {
    color: #6c757d;
    margin-bottom: 20px;
    line-height: 1.6;
}

.btn-settings {
    padding: 12px 25px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-settings:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

/* ================================
   RESPONSIVE
   ================================ */
@media (max-width: 1200px) {
    .admin-dashboard-ultra {
        grid-template-columns: 260px 1fr;
    }
}

@media (max-width: 992px) {
    .admin-dashboard-ultra {
        grid-template-columns: 1fr;
    }

    .admin-sidebar-ultra {
        position: static;
        height: auto;
    }

    .admin-nav-ultra {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        padding: 20px 15px;
    }

    .nav-item-ultra {
        margin: 0;
        justify-content: center;
        text-align: center;
        flex-direction: column;
        padding: 15px 10px;
    }

    .charts-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .admin-sections {
        padding: 20px;
    }

    .admin-topbar {
        padding: 20px;
    }

    .topbar-left h1 {
        font-size: 1.4rem;
    }

    .stats-grid-admin {
        grid-template-columns: 1fr;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
        flex-direction: column;
    }

    .search-box-admin {
        width: 100%;
    }

    .search-box-admin input {
        width: 100%;
    }

    .filter-select-admin {
        width: 100%;
    }
}

/* Statistiques Avancées */
.online-users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.online-user-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.online-user-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
}

.online-user-avatar {
    position: relative;
    width: 60px;
    height: 60px;
}

.online-user-avatar img,
.online-user-avatar .avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 15px;
    height: 15px;
    background: #28a745;
    border: 3px solid white;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

.online-user-info h4 {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
    color: var(--dark-color);
}

.online-user-info p {
    margin: 3px 0;
    font-size: 0.9rem;
    color: #6c757d;
}

.online-user-info .page-url {
    font-size: 0.8rem;
    color: #999;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 250px;
}

.top-users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.top-user-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.top-user-card.top-rank {
    border: 2px solid #ffd700;
    background: linear-gradient(135deg, #fff9e6, #ffffff);
}

.top-user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.rank-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 1.5rem;
    font-weight: bold;
}

.top-user-avatar {
    width: 80px;
    height: 80px;
    margin: 0 auto 15px;
}

.top-user-avatar img,
.top-user-avatar .avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}

.top-user-card h4 {
    margin: 10px 0 5px 0;
    font-size: 1.2rem;
    color: var(--dark-color);
}

.user-email {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 15px;
}

.activity-stats {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin: 15px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.activity-stats span {
    font-size: 0.9rem;
    color: #495057;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.activity-stats span i {
    margin-right: 8px;
    color: var(--primary-color);
}

.btn-view-profile-stats {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-view-profile-stats:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

/* Tabs System */
.tabs-container {
    margin-top: 20px;
}

.tabs-header {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 20px;
}

.tab-btn {
    padding: 12px 24px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-btn:hover {
    color: var(--primary-color);
    background: rgba(102, 126, 234, 0.05);
}

.tab-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
    background: rgba(102, 126, 234, 0.1);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

/* Blog Categories Grid */
#blogCategoriesGrid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.category-card-blog {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.category-card-blog::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: var(--color);
}

.category-card-blog:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.category-header-blog {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.category-icon-blog {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    background: var(--color);
}

.category-info-blog h4 {
    margin: 0 0 5px 0;
    font-size: 1.2rem;
    color: var(--dark-color);
}

.category-info-blog p {
    margin: 0;
    font-size: 0.9rem;
    color: #6c757d;
}

.category-description-blog {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 15px;
    line-height: 1.5;
}

.category-stats-blog {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 10px;
}

.category-stat-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9rem;
    color: #495057;
}

.category-actions-blog {
    display: flex;
    gap: 8px;
}

.category-actions-blog button {
    flex: 1;
    padding: 8px;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-edit-category {
    background: #e3f2fd;
    color: #1976d2;
}

.btn-edit-category:hover {
    background: #1976d2;
    color: white;
}

.btn-toggle-category {
    background: #fff3cd;
    color: #856404;
}

.btn-toggle-category:hover {
    background: #856404;
    color: white;
}

.btn-delete-category {
    background: #f8d7da;
    color: #721c24;
}

.btn-delete-category:hover {
    background: #721c24;
    color: white;
}

.category-status-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.category-status-badge.active {
    background: #d4edda;
    color: #28a745;
}

.category-status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}
</style>

<script>
// Définir les fonctions IMMÉDIATEMENT dans le scope global
window.switchSection = function(event, sectionName) {
    console.log('📍 Changement de section vers:', sectionName);

    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }


    // Update nav active state
    document.querySelectorAll('.nav-item-ultra').forEach(item => {
        item.classList.remove('active');
    });

    if (event && event.target) {
        const navItem = event.target.closest('.nav-item-ultra');
        if (navItem) {
            navItem.classList.add('active');
        }
    }

    // Hide all sections
    document.querySelectorAll('.admin-section-content').forEach(section => {
        section.classList.remove('active');
    });

    // Show selected section
    const section = document.getElementById(`section-${sectionName}`);
    if (section) {
        section.classList.add('active');
        window.currentSection = sectionName;

        // Update title
        window.updateSectionTitle(sectionName);

        // Load data if not loaded
        window.loadSectionData(sectionName);

        // Update URL hash without triggering scroll
        if (window.history && window.history.pushState) {
            history.pushState(null, null, `#${sectionName}`);
        }
    }
};

const csrfToken = '<?= Security::generateCSRFToken() ?>';
window.currentSection = 'overview';

// ========================================
// TITRES DES SECTIONS
// ========================================
window.updateSectionTitle = function(section) {
    const titles = {
        'overview': '<i class="fas fa-chart-pie"></i> Vue d\'ensemble',
        'users': '<i class="fas fa-users"></i> Gestion des Utilisateurs',
        'forum': '<i class="fas fa-comments"></i> Gestion du Forum',
        'tutorials': '<i class="fas fa-book-open"></i> Gestion des Tutoriels',
        'projects': '<i class="fas fa-project-diagram"></i> Gestion des Projets',
        'opportunities': '<i class="fas fa-briefcase"></i> Gestion des Opportunités',
        'blog': '<i class="fas fa-blog"></i> Gestion du Blog',
        'comments': '<i class="fas fa-comment-dots"></i> Modération des Commentaires',
        'reports': '<i class="fas fa-flag"></i> Gestion des Signalements',
        'newsletter': '<i class="fas fa-envelope-open-text"></i> Gestion de la Newsletter',
        'statistics': '<i class="fas fa-chart-bar"></i> Statistiques Avancées',
        'settings': '<i class="fas fa-cog"></i> Paramètres du Système'
    };

    const titleElement = document.getElementById('sectionTitle');
    if (titleElement) {
        titleElement.innerHTML = titles[section] || '';
    }
};

// ========================================
// CHARGEMENT DES DONNÉES
// ========================================
const loadedSections = {
    'overview': true // Déjà chargé
};

window.loadSectionData = function(section) {
    if (loadedSections[section]) return;

    switch (section) {
        case 'users':
            window.loadUsers();
            break;
        case 'forum':
            window.loadForum();
            break;
        case 'tutorials':
            window.loadTutorials();
            break;
        case 'projects':
            window.loadProjects();
            break;
        case 'opportunities':
            window.loadOpportunities();
            break;
        case 'blog':
            window.loadBlog();
            break;
        case 'comments':
            window.loadComments();
            break;
        case 'reports':
            window.loadReports();
            break;
        case 'newsletter':
            window.loadNewsletter();
            break;
        case 'statistics':
            window.loadStatistics();
            break;
        case 'settings':
            window.loadSettings();
            break;
    }

    loadedSections[section] = true;
};

// Chargement des utilisateurs
window.loadUsers = function(status = null) {
    const loadingElem = document.getElementById('usersLoading');
    const contentElem = document.getElementById('usersContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    fetch('<?= BASE_URL ?>/admin/getUsersData' + (status ? '?status=' + status : ''))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderUsersTable(data.users);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'block';
                console.log('✅ ' + data.users.length + ' utilisateur(s) chargé(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement utilisateurs:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des utilisateurs</p></div>';
            }
            window.showMessage('Erreur de chargement des utilisateurs', 'error');
        });
};

window.renderUsersTable = function(users) {
    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Université</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Inscrit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    users.forEach(user => {
        const userName = `${user.prenom || ''} ${user.nom || ''}`.trim() || 'Utilisateur';
        const userInitial = user.prenom ? user.prenom.charAt(0).toUpperCase() : 'U';
        const avatar = user.photo_path ?
            `<img src="<?= BASE_URL ?>/${user.photo_path}" alt="${userName}">` :
            `<div class="avatar-placeholder-dash">${userInitial}</div>`;

        const isAdmin = user.role === 'admin';

        html += `
            <tr data-user-id="${user.id}">
                <td>
                    <div class="user-cell">
                        ${avatar}
                        <span>${userName}</span>
                    </div>
                </td>
                <td>${user.email || '-'}</td>
                <td>${user.university || '-'}</td>
                <td><span class="role-badge role-${user.role}">${user.role}</span></td>
                <td><span class="status-badge ${user.status}">${user.status}</span></td>
                <td>${new Date(user.created_at).toLocaleDateString('fr-FR')}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-action btn-view" onclick='viewUserModal(${JSON.stringify(user).replace(/'/g, "&apos;")})' title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${!isAdmin ? `
                            ${user.status === 'active' ? `
                                <button class="btn-action btn-warning" onclick="suspendUser(${user.id}, '${userName}')" title="Suspendre">
                                    <i class="fas fa-pause"></i>
                                </button>
                            ` : ''}
                                   ${user.status === 'suspended' ? `
                                       <button class="btn-action btn-success" onclick="activateUser(${user.id}, '${userName}')" title="Restaurer l'utilisateur">
                                           <i class="fas fa-undo"></i>
                                       </button>
                                   ` : ''}
                            ${user.status !== 'banned' ? `
                                <button class="btn-action btn-danger" onclick="banUser(${user.id}, '${userName}')" title="Bannir">
                                    <i class="fas fa-ban"></i>
                                </button>
                            ` : ''}
                            <button class="btn-action btn-delete" onclick="deleteUser(${user.id}, '${userName}')" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : `
                            <button class="btn-action" disabled title="Admin protégé" style="opacity: 0.5; cursor: not-allowed;">
                                <i class="fas fa-shield-alt"></i>
                            </button>
                        `}
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';
    const usersTable = document.getElementById('usersTable');
    if (usersTable) {
        usersTable.innerHTML = html;
    }
};

// ========================================
// GESTION DU FORUM
// ========================================

window.loadForum = function() {
    // Charger les catégories par défaut
    window.loadForumCategories();

    // Gérer les onglets du forum
    const tabButtons = document.querySelectorAll('.forum-tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.getAttribute('data-tab');

            // Mettre à jour les boutons
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Mettre à jour les contenus
            document.querySelectorAll('.forum-tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById('forum-tab-' + tab).classList.add('active');

            // Charger les données si nécessaire
            if (tab === 'posts') {
                window.loadForumPosts();
            }
        });
    });
};

// Charger les catégories du forum
window.loadForumCategories = function() {
    const loadingElem = document.getElementById('categoriesLoading');
    const contentElem = document.getElementById('categoriesContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    fetch('<?= BASE_URL ?>/admin/getForumCategories')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderForumCategories(data.categories);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'grid';
                console.log('✅ ' + data.categories.length + ' catégorie(s) chargée(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement catégories:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des catégories</p></div>';
            }
            window.showMessage('Erreur de chargement des catégories', 'error');
        });
};

// Rendre les catégories
window.renderForumCategories = function(categories) {
    const container = document.getElementById('categoriesContent');
    if (!container) return;

    let html = '';

    categories.forEach(cat => {
        const statusClass = cat.is_active == 1 ? 'active' : 'inactive';
        const statusText = cat.is_active == 1 ? 'Active' : 'Inactive';

        html += `
            <div class="category-card" data-id="${cat.id}">
                <span class="category-status-badge ${statusClass}">${statusText}</span>
                
                <div class="category-card-header">
                    <div class="category-icon" style="background: ${cat.color};">
                        <i class="fas ${cat.icon}"></i>
                    </div>
                    <div class="category-info">
                        <h4>${cat.name}</h4>
                        <p>${cat.slug}</p>
                    </div>
                </div>
                
                <p class="category-description">
                    ${cat.description || 'Aucune description'}
                </p>
                
                <div class="category-stats">
                    <div class="category-post-count">
                        <i class="fas fa-comments"></i>
                        <span>${cat.post_count} discussion${cat.post_count > 1 ? 's' : ''}</span>
                    </div>
                    <div class="category-actions">
                        <button class="btn-action btn-view" onclick="editForumCategory(${cat.id})" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action ${cat.is_active == 1 ? 'btn-warning' : 'btn-success'}" 
                                onclick="toggleForumCategory(${cat.id}, ${cat.is_active == 1 ? 0 : 1})" 
                                title="${cat.is_active == 1 ? 'Désactiver' : 'Activer'}">
                            <i class="fas ${cat.is_active == 1 ? 'fa-eye-slash' : 'fa-eye'}"></i>
                        </button>
                        ${cat.post_count == 0 ? `
                            <button class="btn-action btn-delete" onclick="deleteForumCategory(${cat.id}, '${cat.name}')" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });

    if (categories.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-tags"></i><p>Aucune catégorie</p></div>';
    }

    container.innerHTML = html;
};

// Ouvrir le modal de création/édition
window.openCategoryModal = function(categoryId = null) {
    const isEdit = categoryId !== null;
    const title = isEdit ? 'Modifier la Catégorie' : 'Nouvelle Catégorie';

    let category = null;
    if (isEdit) {
        // Récupérer les données de la catégorie depuis le DOM
        const card = document.querySelector(`.category-card[data-id="${categoryId}"]`);
        if (!card) return;

        const iconElement = card.querySelector('.category-icon i');
        const colorElement = card.querySelector('.category-icon');

        category = {
            id: categoryId,
            name: card.querySelector('.category-info h4').textContent,
            slug: card.querySelector('.category-info p').textContent,
            description: card.querySelector('.category-description').textContent.trim(),
            icon: iconElement ? iconElement.className.replace('fas ', '') : 'fa-folder',
            color: colorElement ? colorElement.style.background : '#667eea'
        };
    }

    const modal = document.createElement('div');
    modal.className = 'user-modal-overlay';
    modal.innerHTML = `
        <div class="user-modal-container" style="max-width: 600px;">
            <div class="user-modal-header">
                <h3><i class="fas fa-tag"></i> ${title}</h3>
                <button class="btn-close-modal" onclick="this.closest('.user-modal-overlay').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="user-modal-body">
                <form id="categoryForm">
                    <div class="form-group">
                        <label>Nom de la catégorie <span style="color: red;">*</span></label>
                        <input type="text" id="catName" class="form-control" 
                               value="${category ? category.name : ''}" 
                               placeholder="Ex: Programmation" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="catDescription" class="form-control" rows="3" 
                                  placeholder="Description de la catégorie...">${category ? category.description : ''}</textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Icône FontAwesome</label>
                            <input type="text" id="catIcon" class="form-control" 
                                   value="${category ? category.icon : 'fa-folder'}" 
                                   placeholder="fa-code">
                            <small style="color: #6c757d;">Ex: fa-code, fa-network-wired</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Couleur</label>
                            <input type="color" id="catColor" class="form-control" 
                                   value="${category ? category.color : '#667eea'}" 
                                   style="height: 45px;">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px; text-align: right;">
                        <button type="button" class="btn-modal-close" onclick="this.closest('.user-modal-overlay').remove()" 
                                style="margin-right: 10px;">
                            Annuler
                        </button>
                        <button type="submit" class="btn-modal-profile">
                            <i class="fas fa-save"></i> ${isEdit ? 'Enregistrer' : 'Créer'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Gérer la soumission
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const name = document.getElementById('catName').value.trim();
        const description = document.getElementById('catDescription').value.trim();
        const icon = document.getElementById('catIcon').value.trim();
        const color = document.getElementById('catColor').value;

        if (!name) {
            window.showMessage('Le nom est requis', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        formData.append('icon', icon);
        formData.append('color', color);

        if (isEdit) {
            formData.append('id', categoryId);
            window.saveForumCategory(formData, true);
        } else {
            window.saveForumCategory(formData, false);
        }

        modal.remove();
        document.body.style.overflow = '';
    });

    // Fermer en cliquant en dehors
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    });
};

// Sauvegarder une catégorie
window.saveForumCategory = function(formData, isEdit) {
    const url = isEdit ? '<?= BASE_URL ?>/admin/updateForumCategory' : '<?= BASE_URL ?>/admin/createForumCategory';

    fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadForumCategories();
            } else {
                window.showMessage(data.message || 'Erreur', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la sauvegarde', 'error');
        });
};

// Éditer une catégorie
window.editForumCategory = function(categoryId) {
    window.openCategoryModal(categoryId);
};

// Toggle actif/inactif
window.toggleForumCategory = function(categoryId, newStatus) {
    const formData = new FormData();
    formData.append('id', categoryId);
    formData.append('is_active', newStatus);

    fetch('<?= BASE_URL ?>/admin/toggleForumCategory', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadForumCategories();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la mise à jour', 'error');
        });
};

// Supprimer une catégorie
window.deleteForumCategory = function(categoryId, categoryName) {
    if (!confirm(`🗑️ Supprimer la catégorie "${categoryName}" ?\n\nCette action est irréversible !`)) {
        return;
    }

    const formData = new FormData();
    formData.append('id', categoryId);

    fetch('<?= BASE_URL ?>/admin/deleteForumCategory', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadForumCategories();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la suppression', 'error');
        });
};

// ========================================
// GESTION DES DISCUSSIONS DU FORUM
// ========================================

// Charger les discussions du forum
window.loadForumPosts = function(status = null) {
    const loadingElem = document.getElementById('postsLoading');
    const contentElem = document.getElementById('postsContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    fetch('<?= BASE_URL ?>/admin/getForumPosts' + (status ? '?status=' + status : ''))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderForumPosts(data.posts);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'block';
                console.log('✅ ' + data.posts.length + ' discussion(s) chargée(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement discussions:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des discussions</p></div>';
            }
            window.showMessage('Erreur de chargement des discussions', 'error');
        });
};

// Rendre le tableau des discussions
window.renderForumPosts = function(posts) {
    const postsTable = document.getElementById('postsTable');
    if (!postsTable) return;

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Discussion</th>
                    <th>Auteur</th>
                    <th>Catégorie</th>
                    <th>Stats</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    posts.forEach(post => {
        const excerpt = post.body.substring(0, 80) + '...';
        const authorInitial = post.author_name.charAt(0).toUpperCase();
        const avatar = post.author_photo ?
            `<img src="<?= BASE_URL ?>/${post.author_photo}" alt="${post.author_name}">` :
            `<div class="avatar-placeholder-dash">${authorInitial}</div>`;

        html += `
            <tr data-post-id="${post.id}">
                <td>
                    <div class="post-cell">
                        ${post.is_pinned == 1 ? '<i class="fas fa-thumbtack" style="color: #f39c12;" title="Épinglée"></i>' : ''}
                        ${post.is_locked == 1 ? '<i class="fas fa-lock" style="color: #e74c3c;" title="Verrouillée"></i>' : ''}
                        <div>
                            <strong>${post.title}</strong>
                            <p style="margin: 5px 0 0; color: #6c757d; font-size: 0.85rem;">${excerpt}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="user-cell">
                        ${avatar}
                        <span>${post.author_name}</span>
                    </div>
                </td>
                <td>
                    <span class="category-badge">${post.category}</span>
                </td>
                <td>
                    <div style="display: flex; gap: 15px; font-size: 0.9rem;">
                        <span title="Vues"><i class="fas fa-eye"></i> ${post.views}</span>
                        <span title="Commentaires"><i class="fas fa-comments"></i> ${post.comments_count}</span>
                        <span title="Likes"><i class="fas fa-heart"></i> ${post.likes_count}</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge ${post.status}">${post.status}</span>
                </td>
                <td>
                    ${new Date(post.created_at).toLocaleDateString('fr-FR')}
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/forum/show/${post.id}" target="_blank" class="btn-action btn-view" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn-action ${post.is_pinned == 1 ? 'btn-warning' : 'btn-success'}" 
                                onclick="togglePinPost(${post.id}, ${post.is_pinned == 1 ? 0 : 1})" 
                                title="${post.is_pinned == 1 ? 'Désépingler' : 'Épingler'}">
                            <i class="fas fa-thumbtack"></i>
                        </button>
                        <button class="btn-action ${post.is_locked == 1 ? 'btn-success' : 'btn-warning'}" 
                                onclick="toggleLockPost(${post.id}, ${post.is_locked == 1 ? 0 : 1})" 
                                title="${post.is_locked == 1 ? 'Déverrouiller' : 'Verrouiller'}">
                            <i class="fas fa-lock"></i>
                        </button>
                        ${post.status === 'active' ? `
                            <button class="btn-action btn-danger" onclick="updatePostStatus(${post.id}, 'hidden')" title="Masquer">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        ` : ''}
                        ${post.status === 'hidden' ? `
                            <button class="btn-action btn-success" onclick="updatePostStatus(${post.id}, 'active')" title="Rendre visible">
                                <i class="fas fa-eye"></i>
                            </button>
                        ` : ''}
                        <button class="btn-action btn-delete" onclick="updatePostStatus(${post.id}, 'deleted')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    if (posts.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-comments"></i><p>Aucune discussion</p></div>';
    }

    postsTable.innerHTML = html;
};

// Épingler/Désépingler une discussion
window.togglePinPost = function(postId, isPinned) {
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('is_pinned', isPinned);

    fetch('<?= BASE_URL ?>/admin/togglePinPost', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadForumPosts(document.getElementById('postsStatusFilter').value);
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la mise à jour', 'error');
        });
};

// Verrouiller/Déverrouiller une discussion
window.toggleLockPost = function(postId, isLocked) {
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('is_locked', isLocked);

    fetch('<?= BASE_URL ?>/admin/toggleLockPost', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadForumPosts(document.getElementById('postsStatusFilter').value);
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la mise à jour', 'error');
        });
};

// Mettre à jour le statut d'une discussion
window.updatePostStatus = function(postId, newStatus) {
    const messages = {
        'hidden': 'Masquer cette discussion ?',
        'active': 'Rendre cette discussion visible ?',
        'deleted': '🗑️ Supprimer définitivement cette discussion ?'
    };

    if (!confirm(messages[newStatus] || 'Confirmer cette action ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('status', newStatus);

    fetch('<?= BASE_URL ?>/admin/updatePostStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage('✅ ' + data.message, 'success');

                // Si suppression, animer la ligne avant de recharger
                if (newStatus === 'deleted') {
                    const row = document.querySelector(`#postsTable tr[data-post-id="${postId}"]`);
                    if (row) {
                        row.style.animation = 'fadeOut 0.5s ease';
                        setTimeout(() => {
                            window.loadForumPosts(document.getElementById('postsStatusFilter')?.value ||
                                '');
                        }, 500);
                    } else {
                        window.loadForumPosts(document.getElementById('postsStatusFilter')?.value || '');
                    }
                } else {
                    window.loadForumPosts(document.getElementById('postsStatusFilter')?.value || '');
                }
            } else {
                window.showMessage('❌ ' + (data.message || 'Erreur'), 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('❌ Erreur lors de la mise à jour', 'error');
        });
};

// ========================================
// GESTION DES TUTORIELS
// ========================================

window.loadTutorials = function() {
    window.loadTutorialsData();
};

window.loadTutorialsData = function() {
    const loadingElem = document.getElementById('tutorialsLoading');
    const contentElem = document.getElementById('tutorialsContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    const statusFilter = document.getElementById('tutorialsStatusFilter');
    const typeFilter = document.getElementById('tutorialsTypeFilter');

    const params = new URLSearchParams();
    if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
    if (typeFilter && typeFilter.value) params.append('type', typeFilter.value);

    fetch('<?= BASE_URL ?>/admin/getTutorialsData' + (params.toString() ? '?' + params.toString() : ''))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderTutorialsTable(data.tutorials);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'block';
                console.log('✅ ' + data.tutorials.length + ' tutoriel(s) chargé(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement tutoriels:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des tutoriels</p></div>';
            }
            window.showMessage('Erreur de chargement des tutoriels', 'error');
        });
};

window.renderTutorialsTable = function(tutorials) {
    const tutorialsTable = document.getElementById('tutorialsTable');
    if (!tutorialsTable) return;

    const typeIcons = {
        'video': '🎥',
        'pdf': '📄',
        'code': '💻',
        'article': '📝'
    };

    const levelColors = {
        'Débutant': '#28a745',
        'Intermédiaire': '#ffc107',
        'Avancé': '#fd7e14',
        'Expert': '#dc3545'
    };

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tutoriel</th>
                    <th>Auteur</th>
                    <th>Type</th>
                    <th>Niveau</th>
                    <th>Stats</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    tutorials.forEach(tutorial => {
        const excerpt = tutorial.description ? tutorial.description.substring(0, 60) + '...' :
            'Aucune description';
        const authorInitial = tutorial.author_name.charAt(0).toUpperCase();
        const avatar = tutorial.author_photo ?
            `<img src="<?= BASE_URL ?>/${tutorial.author_photo}" alt="${tutorial.author_name}">` :
            `<div class="avatar-placeholder-dash">${authorInitial}</div>`;

        html += `
            <tr data-tutorial-id="${tutorial.id}">
                <td>
                    <div class="post-cell">
                        <div>
                            <strong>${tutorial.title}</strong>
                            <p style="margin: 5px 0 0; color: #6c757d; font-size: 0.85rem;">${excerpt}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="user-cell">
                        ${avatar}
                        <span>${tutorial.author_name}</span>
                    </div>
                </td>
                <td>
                    <span style="font-size: 1.5rem;" title="${tutorial.type}">${typeIcons[tutorial.type] || '📚'}</span>
                </td>
                <td>
                    <span class="level-badge" style="background: ${levelColors[tutorial.level] || '#6c757d'}33; color: ${levelColors[tutorial.level] || '#6c757d'}; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                        ${tutorial.level || 'N/A'}
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 12px; font-size: 0.9rem;">
                        <span title="Vues"><i class="fas fa-eye"></i> ${tutorial.views}</span>
                        <span title="Téléchargements"><i class="fas fa-download"></i> ${tutorial.downloads || 0}</span>
                        <span title="Likes"><i class="fas fa-heart"></i> ${tutorial.likes_count}</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge ${tutorial.status}">${tutorial.status}</span>
                </td>
                <td>
                    ${new Date(tutorial.created_at).toLocaleDateString('fr-FR')}
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/tutorial/show/${tutorial.id}" target="_blank" class="btn-action btn-view" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                        ${tutorial.status === 'pending' ? `
                            <button class="btn-action btn-success" onclick="approveTutorial(${tutorial.id})" title="Approuver">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        ${tutorial.status === 'active' ? `
                            <button class="btn-action btn-warning" onclick="updateTutorialStatus(${tutorial.id}, 'hidden')" title="Masquer">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        ` : ''}
                        ${tutorial.status === 'hidden' ? `
                            <button class="btn-action btn-success" onclick="updateTutorialStatus(${tutorial.id}, 'active')" title="Publier">
                                <i class="fas fa-eye"></i>
                            </button>
                        ` : ''}
                        <button class="btn-action btn-delete" onclick="updateTutorialStatus(${tutorial.id}, 'deleted')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    if (tutorials.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-book-open"></i><p>Aucun tutoriel</p></div>';
    }

    tutorialsTable.innerHTML = html;
};

// Approuver un tutoriel en attente
window.approveTutorial = function(tutorialId) {
    if (!confirm('✅ Approuver et publier ce tutoriel ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('tutorial_id', tutorialId);

    fetch('<?= BASE_URL ?>/admin/approveTutorial', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadTutorialsData();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de l\'approbation', 'error');
        });
};

// Mettre à jour le statut d'un tutoriel
window.updateTutorialStatus = function(tutorialId, newStatus) {
    const messages = {
        'pending': 'Mettre ce tutoriel en attente de validation ?',
        'active': 'Publier ce tutoriel ?',
        'hidden': 'Masquer ce tutoriel ?',
        'deleted': '🗑️ Supprimer définitivement ce tutoriel ?'
    };

    if (!confirm(messages[newStatus] || 'Confirmer cette action ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('tutorial_id', tutorialId);
    formData.append('status', newStatus);

    fetch('<?= BASE_URL ?>/admin/updateTutorialStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadTutorialsData();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la mise à jour', 'error');
        });
};

// ========================================
// GESTION DES PROJETS
// ========================================

window.loadProjects = function() {
    window.loadProjectsData();
};

window.loadProjectsData = function() {
    const loadingElem = document.getElementById('projectsLoading');
    const contentElem = document.getElementById('projectsContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    const statusFilter = document.getElementById('projectsStatusFilter');
    const visibilityFilter = document.getElementById('projectsVisibilityFilter');

    const params = new URLSearchParams();
    if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
    if (visibilityFilter && visibilityFilter.value) params.append('visibility', visibilityFilter.value);

    fetch('<?= BASE_URL ?>/admin/getProjectsData' + (params.toString() ? '?' + params.toString() : ''))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderProjectsTable(data.projects);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'block';
                console.log('✅ ' + data.projects.length + ' projet(s) chargé(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement projets:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des projets</p></div>';
            }
            window.showMessage('Erreur de chargement des projets', 'error');
        });
};

window.renderProjectsTable = function(projects) {
    const projectsTable = document.getElementById('projectsTable');
    if (!projectsTable) return;

    const statusLabels = {
        'planning': '📋 Planification',
        'in_progress': '⚡ En cours',
        'completed': '✅ Terminé',
        'archived': '📦 Archivé'
    };

    const statusColors = {
        'planning': '#6c757d',
        'in_progress': '#007bff',
        'completed': '#28a745',
        'archived': '#ffc107'
    };

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Projet</th>
                    <th>Créateur</th>
                    <th>Membres</th>
                    <th>Visibilité</th>
                    <th>Statut</th>
                    <th>Recrutement</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    projects.forEach(project => {
        const excerpt = project.description ? project.description.substring(0, 70) + '...' :
            'Aucune description';
        const ownerInitial = project.owner_name.charAt(0).toUpperCase();
        const avatar = project.owner_photo ?
            `<img src="<?= BASE_URL ?>/${project.owner_photo}" alt="${project.owner_name}">` :
            `<div class="avatar-placeholder-dash">${ownerInitial}</div>`;

        const hasLinks = project.github_link || project.demo_link;

        html += `
            <tr data-project-id="${project.id}">
                <td>
                    <div class="post-cell">
                        <div>
                            <strong>${project.title}</strong>
                            <p style="margin: 5px 0 0; color: #6c757d; font-size: 0.85rem;">${excerpt}</p>
                            ${hasLinks ? `
                                <div style="margin-top: 8px; display: flex; gap: 10px;">
                                    ${project.github_link ? `<a href="${project.github_link}" target="_blank" style="color: #6c757d; font-size: 0.8rem;"><i class="fab fa-github"></i> GitHub</a>` : ''}
                                    ${project.demo_link ? `<a href="${project.demo_link}" target="_blank" style="color: #6c757d; font-size: 0.8rem;"><i class="fas fa-external-link-alt"></i> Demo</a>` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="user-cell">
                        ${avatar}
                        <span>${project.owner_name}</span>
                    </div>
                </td>
                <td>
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-users"></i> ${project.members_count}
                    </span>
                </td>
                <td>
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <i class="fas ${project.visibility === 'public' ? 'fa-globe' : 'fa-lock'}" 
                           style="color: ${project.visibility === 'public' ? '#28a745' : '#ffc107'};"></i>
                        ${project.visibility === 'public' ? 'Public' : 'Privé'}
                    </span>
                </td>
                <td>
                    <span class="status-badge" style="background: ${statusColors[project.status]}33; color: ${statusColors[project.status]};">
                        ${statusLabels[project.status] || project.status}
                    </span>
                </td>
                <td>
                    ${project.looking_for_members == 1 ? 
                        '<span style="color: #28a745;"><i class="fas fa-user-plus"></i> Oui</span>' : 
                        '<span style="color: #6c757d;"><i class="fas fa-user-minus"></i> Non</span>'}
                </td>
                <td>
                    ${new Date(project.created_at).toLocaleDateString('fr-FR')}
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/project/show/${project.id}" target="_blank" class="btn-action btn-view" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn-action ${project.visibility === 'public' ? 'btn-warning' : 'btn-success'}" 
                                onclick="toggleProjectVisibility(${project.id}, '${project.visibility === 'public' ? 'private' : 'public'}')" 
                                title="${project.visibility === 'public' ? 'Rendre privé' : 'Rendre public'}">
                            <i class="fas ${project.visibility === 'public' ? 'fa-lock' : 'fa-globe'}"></i>
                        </button>
                        <button class="btn-action btn-primary" onclick="changeProjectStatus(${project.id}, '${project.status}')" title="Changer le statut">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action btn-delete" onclick="updateProjectStatus(${project.id}, 'deleted')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    if (projects.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-project-diagram"></i><p>Aucun projet</p></div>';
    }

    projectsTable.innerHTML = html;
};

// Toggle visibilité public/privé
window.toggleProjectVisibility = function(projectId, newVisibility) {
    const formData = new FormData();
    formData.append('project_id', projectId);
    formData.append('visibility', newVisibility);

    fetch('<?= BASE_URL ?>/admin/toggleProjectVisibility', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadProjectsData();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la mise à jour', 'error');
        });
};

// Changer le statut d'un projet (avec modal de sélection)
window.changeProjectStatus = function(projectId, currentStatus) {
    const statusOptions = {
        'planning': '📋 Planification',
        'in_progress': '⚡ En cours',
        'completed': '✅ Terminé',
        'archived': '📦 Archivé'
    };

    const modal = document.createElement('div');
    modal.className = 'user-modal-overlay';
    modal.innerHTML = `
        <div class="user-modal-container" style="max-width: 500px;">
            <div class="user-modal-header">
                <h3><i class="fas fa-tasks"></i> Changer le statut du projet</h3>
                <button class="btn-close-modal" onclick="this.closest('.user-modal-overlay').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="user-modal-body">
                <div class="form-group">
                    <label>Nouveau statut</label>
                    <select id="newProjectStatus" class="form-control">
                        ${Object.entries(statusOptions).map(([value, label]) => 
                            `<option value="${value}" ${value === currentStatus ? 'selected' : ''}>${label}</option>`
                        ).join('')}
                    </select>
                </div>
                <div style="text-align: right; margin-top: 20px;">
                    <button class="btn-modal-close" onclick="this.closest('.user-modal-overlay').remove()" style="margin-right: 10px;">
                        Annuler
                    </button>
                    <button class="btn-modal-profile" onclick="saveProjectStatus(${projectId})">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    });
};

window.saveProjectStatus = function(projectId) {
    const newStatus = document.getElementById('newProjectStatus').value;

    window.updateProjectStatus(projectId, newStatus);

    const modal = document.querySelector('.user-modal-overlay');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
};

// Mettre à jour le statut d'un projet
window.updateProjectStatus = function(projectId, newStatus) {
    const messages = {
        'planning': 'Mettre ce projet en planification ?',
        'in_progress': 'Marquer ce projet comme en cours ?',
        'completed': 'Marquer ce projet comme terminé ?',
        'archived': 'Archiver ce projet ?',
        'deleted': '🗑️ Supprimer définitivement ce projet ?'
    };

    if (newStatus === 'deleted') {
        if (!confirm(messages[newStatus] || 'Confirmer cette action ?')) {
            return;
        }
    }

    const formData = new FormData();
    formData.append('project_id', projectId);
    formData.append('status', newStatus);

    fetch('<?= BASE_URL ?>/admin/updateProjectStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadProjectsData();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la mise à jour', 'error');
        });
};

// ========================================
// GESTION DES OPPORTUNITÉS
// ========================================

window.loadOpportunities = function() {
    window.loadJobsData();
};

window.loadJobsData = function() {
    const loadingElem = document.getElementById('jobsLoading');
    const contentElem = document.getElementById('jobsContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    const typeFilter = document.getElementById('jobsTypeFilter');
    const statusFilter = document.getElementById('jobsStatusFilter');

    const params = new URLSearchParams();
    if (typeFilter && typeFilter.value) params.append('type', typeFilter.value);
    if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);

    fetch('<?= BASE_URL ?>/admin/getJobsData' + (params.toString() ? '?' + params.toString() : ''))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderJobsTable(data.jobs);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'block';
                console.log('✅ ' + data.jobs.length + ' opportunité(s) chargée(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement opportunités:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des opportunités</p></div>';
            }
            window.showMessage('Erreur de chargement des opportunités', 'error');
        });
};

window.renderJobsTable = function(jobs) {
    const jobsTable = document.getElementById('jobsTable');
    if (!jobsTable) return;

    const typeLabels = {
        'stage': '🎓 Stage',
        'emploi': '👔 Emploi',
        'hackathon': '💻 Hackathon',
        'formation': '📚 Formation',
        'freelance': '🚀 Freelance'
    };

    const statusLabels = {
        'pending': '⏳ En attente',
        'active': '✅ Active',
        'closed': '🔒 Fermée',
        'expired': '⏰ Expirée'
    };

    const statusColors = {
        'pending': '#ffc107',
        'active': '#28a745',
        'closed': '#6c757d',
        'expired': '#dc3545'
    };

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Entreprise</th>
                    <th>Type</th>
                    <th>Ville</th>
                    <th>Statut</th>
                    <th>Candidatures</th>
                    <th>Vues</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    jobs.forEach(job => {
        const companyInitial = job.company_name.charAt(0).toUpperCase();
        const avatar = job.company_photo ?
            `<img src="<?= BASE_URL ?>/${job.company_photo}" alt="${job.company_name}">` :
            `<div class="avatar-placeholder-dash">${companyInitial}</div>`;

        const deadline = job.deadline ? new Date(job.deadline).toLocaleDateString('fr-FR') : 'N/A';
        const isExpired = job.deadline && new Date(job.deadline) < new Date();

        html += `
            <tr data-job-id="${job.id}">
                <td>
                    <div class="post-cell">
                        <strong>${job.title}</strong>
                        ${job.salary_range ? `<p style="margin: 5px 0 0; color: #28a745; font-size: 0.85rem;"><i class="fas fa-money-bill-wave"></i> ${job.salary_range}</p>` : ''}
                    </div>
                </td>
                <td>
                    <div class="user-cell">
                        ${avatar}
                        <span>${job.company_name}</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge" style="background: #667eea33; color: #667eea;">
                        ${typeLabels[job.type] || job.type}
                    </span>
                </td>
                <td>
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-map-marker-alt"></i> ${job.city || 'N/A'}
                    </span>
                </td>
                <td>
                    <span class="status-badge" style="background: ${statusColors[job.status]}33; color: ${statusColors[job.status]};">
                        ${statusLabels[job.status] || job.status}
                    </span>
                </td>
                <td>
                    <button class="btn-action btn-info" onclick="viewJobApplications(${job.id}, '${job.title}')" title="Voir les candidatures">
                        <i class="fas fa-users"></i> ${job.applications_count}
                    </button>
                </td>
                <td>
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-eye"></i> ${job.views}
                    </span>
                </td>
                <td>
                    <span style="color: ${isExpired ? '#dc3545' : '#6c757d'};">
                        ${deadline}
                        ${isExpired ? '<br><small style="color: #dc3545;">⏰ Expirée</small>' : ''}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/job/show/${job.id}" target="_blank" class="btn-action btn-view" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                        ${job.status === 'pending' ? `
                            <button class="btn-action btn-success" onclick="updateJobStatus(${job.id}, 'active')" title="Activer">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        ${job.status === 'active' ? `
                            <button class="btn-action btn-warning" onclick="updateJobStatus(${job.id}, 'closed')" title="Fermer">
                                <i class="fas fa-lock"></i>
                            </button>
                        ` : ''}
                        ${job.status !== 'active' && job.status !== 'pending' ? `
                            <button class="btn-action btn-success" onclick="updateJobStatus(${job.id}, 'active')" title="Réactiver">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        <button class="btn-action btn-delete" onclick="deleteJob(${job.id}, '${job.title}')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    if (jobs.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-briefcase"></i><p>Aucune opportunité</p></div>';
    }

    jobsTable.innerHTML = html;
};

// Mettre à jour le statut d'une opportunité
window.updateJobStatus = function(jobId, newStatus) {
    const messages = {
        'pending': 'Mettre cette opportunité en attente ?',
        'active': 'Activer cette opportunité ?',
        'closed': 'Fermer cette opportunité ?',
        'expired': 'Marquer comme expirée ?'
    };

    if (!confirm(messages[newStatus] || 'Confirmer cette action ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('job_id', jobId);
    formData.append('status', newStatus);

    fetch('<?= BASE_URL ?>/admin/updateJobStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage('✅ ' + data.message, 'success');
                window.loadJobsData();
            } else {
                window.showMessage('❌ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('❌ Erreur lors de la mise à jour', 'error');
        });
};

// Supprimer une opportunité
window.deleteJob = function(jobId, jobTitle) {
    if (!confirm(
            `🗑️ Supprimer définitivement l'opportunité "${jobTitle}" ?\n\nToutes les candidatures associées seront également supprimées.`
        )) {
        return;
    }

    const formData = new FormData();
    formData.append('job_id', jobId);

    fetch('<?= BASE_URL ?>/admin/deleteJob', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage('✅ ' + data.message, 'success');
                window.loadJobsData();
            } else {
                window.showMessage('❌ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('❌ Erreur lors de la suppression', 'error');
        });
};

// Scraper les offres d'emploi depuis les sites partenaires
window.scrapeJobsNow = function() {
    const btn = document.getElementById('scrapeJobsBtn');
    const originalContent = btn.innerHTML;
    const alertDiv = document.getElementById('scrapeResultsAlert');

    // Confirmation
    if (!confirm(
            '🤖 Lancer le scraping des offres d\'emploi ?\n\nCela va récupérer les offres depuis:\n• Global Expertise\n• EmploiBurkina\n• Travail-Burkina\n\nCela peut prendre quelques minutes.'
        )) {
        return;
    }

    // Désactiver le bouton et afficher le chargement
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scraping en cours...';

    // Afficher un message d'info
    alertDiv.style.display = 'block';
    alertDiv.innerHTML = `
        <div style="background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8;">
            <i class="fas fa-info-circle"></i> <strong>Scraping en cours...</strong><br>
            <small>Récupération des offres depuis les sites partenaires. Veuillez patienter...</small>
        </div>
    `;

    // Ouvrir le script de scraping dans une nouvelle fenêtre
    const scrapeWindow = window.open('<?= BASE_URL ?>/public/scrape_jobs.php', '_blank', 'width=900,height=700');

    // Réactiver le bouton après 5 secondes
    setTimeout(function() {
        btn.disabled = false;
        btn.innerHTML = originalContent;

        alertDiv.innerHTML = `
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;">
                <i class="fas fa-check-circle"></i> <strong>Scraping lancé !</strong><br>
                <small>Consultez la fenêtre de scraping pour voir les résultats. Les nouvelles offres apparaîtront automatiquement.</small>
                <button onclick="window.loadJobsData(); document.getElementById('scrapeResultsAlert').style.display='none';" style="margin-top: 10px; padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    🔄 Actualiser la liste
                </button>
            </div>
        `;
    }, 5000);
};

// Voir les candidatures pour une opportunité
window.viewJobApplications = function(jobId, jobTitle) {
    fetch('<?= BASE_URL ?>/admin/getJobApplications?job_id=' + jobId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showApplicationsModal(jobId, jobTitle, data.applications);
            } else {
                window.showMessage('❌ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('❌ Erreur de chargement des candidatures', 'error');
        });
};

// Afficher le modal des candidatures
function showApplicationsModal(jobId, jobTitle, applications) {
    let html = `
        <div class="modal-overlay" onclick="this.remove()">
            <div class="modal-content-dash" onclick="event.stopPropagation()" style="max-width: 800px;">
                <div class="modal-header-dash">
                    <h3><i class="fas fa-users"></i> Candidatures pour "${jobTitle}"</h3>
                    <button class="modal-close-dash" onclick="this.closest('.modal-overlay').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body-dash">
    `;

    if (applications.length === 0) {
        html += `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune candidature pour le moment</p>
            </div>
        `;
    } else {
        html += '<div style="max-height: 500px; overflow-y: auto;">';
        applications.forEach((app, index) => {
            const statusLabels = {
                'pending': '⏳ En attente',
                'viewed': '👁️ Vue',
                'accepted': '✅ Acceptée',
                'rejected': '❌ Rejetée'
            };
            const statusColors = {
                'pending': '#ffc107',
                'viewed': '#17a2b8',
                'accepted': '#28a745',
                'rejected': '#dc3545'
            };

            const applicantInitial = app.applicant_name.charAt(0).toUpperCase();
            const avatar = app.applicant_photo ?
                `<img src="<?= BASE_URL ?>/${app.applicant_photo}" alt="${app.applicant_name}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">` :
                `<div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">${applicantInitial}</div>`;

            html += `
                <div style="padding: 15px; margin-bottom: 15px; border: 1px solid #e0e0e0; border-radius: 8px; background: #f9f9f9;">
                    <div style="display: flex; gap: 15px; align-items: start;">
                        ${avatar}
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div>
                                    <strong style="font-size: 1.1rem;">${app.applicant_name}</strong>
                                    <div style="margin-top: 5px; color: #6c757d; font-size: 0.9rem;">
                                        <i class="fas fa-envelope"></i> ${app.applicant_email || 'N/A'}
                                        ${app.applicant_phone ? `<br><i class="fas fa-phone"></i> ${app.applicant_phone}` : ''}
                                    </div>
                                </div>
                                <span class="status-badge" style="background: ${statusColors[app.status]}33; color: ${statusColors[app.status]};">
                                    ${statusLabels[app.status]}
                                </span>
                            </div>
                            ${app.cover_letter ? `
                                <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 5px; font-size: 0.9rem;">
                                    <strong>Lettre de motivation :</strong><br>
                                    ${app.cover_letter}
                                </div>
                            ` : ''}
                            <div style="margin-top: 10px; font-size: 0.85rem; color: #6c757d;">
                                Candidature envoyée le ${new Date(app.created_at).toLocaleDateString('fr-FR')} à ${new Date(app.created_at).toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
    }

    html += `
                </div>
                <div class="modal-footer-dash">
                    <button class="btn-secondary-admin" onclick="this.closest('.modal-overlay').remove()">
                        <i class="fas fa-times"></i> Fermer
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', html);
}

// ========================================
// GESTION DU BLOG
// ========================================

window.loadBlog = function() {
    window.loadBlogPosts();
};

// Fonction pour changer d'onglet dans la section Blog
window.switchBlogTab = function(tab) {
    // Changer les boutons d'onglets
    document.querySelectorAll('#section-blog .tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.closest('.tab-btn').classList.add('active');

    // Changer le contenu des onglets
    document.querySelectorAll('#section-blog .tab-content').forEach(content => {
        content.classList.remove('active');
    });

    if (tab === 'articles') {
        document.getElementById('blog-tab-articles').classList.add('active');
    } else if (tab === 'categories') {
        document.getElementById('blog-tab-categories').classList.add('active');
        loadBlogCategories();
    }
};

// Charger les catégories de blog
window.loadBlogCategories = function() {
    fetch('<?= BASE_URL ?>/admin/blogCategories')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderBlogCategories(data.categories);
            }
        })
        .catch(error => console.error('Erreur chargement catégories blog:', error));
};

// Afficher les catégories
window.renderBlogCategories = function(categories) {
    const container = document.getElementById('blogCategoriesGrid');

    if (!categories || categories.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-tags"></i><p>Aucune catégorie</p></div>';
        return;
    }

    let html = '';
    categories.forEach(cat => {
        const statusLabel = cat.status === 'active' ? 'Active' : 'Inactive';
        const statusClass = cat.status === 'active' ? 'active' : 'inactive';
        const description = cat.description || 'Aucune description';

        html += `
            <div class="category-card-blog" style="--color: ${cat.color}">
                <span class="category-status-badge ${statusClass}">${statusLabel}</span>
                
                <div class="category-header-blog">
                    <div class="category-icon-blog" style="background: ${cat.color}">
                        <i class="${cat.icon}"></i>
                    </div>
                    <div class="category-info-blog">
                        <h4>${cat.name}</h4>
                        <p>${cat.slug}</p>
                    </div>
                </div>
                
                <div class="category-description-blog">
                    ${description}
                </div>
                
                <div class="category-stats-blog">
                    <div class="category-stat-item">
                        <i class="fas fa-newspaper"></i>
                        <span>${cat.posts_count || 0} articles</span>
                    </div>
                </div>
                
                <div class="category-actions-blog">
                    <button class="btn-edit-category" onclick="editBlogCategory(${cat.id}, \`${cat.name}\`, \`${description}\`, '${cat.icon}', '${cat.color}')">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button class="btn-toggle-category" onclick="toggleBlogCategory(${cat.id})">
                        <i class="fas fa-power-off"></i> ${cat.status === 'active' ? 'Désactiver' : 'Activer'}
                    </button>
                    <button class="btn-delete-category" onclick="deleteBlogCategory(${cat.id}, \`${cat.name}\`, ${cat.posts_count || 0})">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
};

// Ouvrir le modal pour créer/modifier une catégorie
window.openBlogCategoryModal = function(id = null, name = '', description = '', icon = 'fa-folder', color = '#667eea') {
    const isEdit = id !== null;
    const modalTitle = isEdit ? 'Modifier la Catégorie' : 'Nouvelle Catégorie';
    const submitText = isEdit ? 'Enregistrer' : 'Créer';

    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.3s ease;
    `;

    modal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 20px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; animation: slideUp 0.3s ease;">
            <h3 style="margin: 0 0 20px; color: var(--dark-color); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-tag" style="color: var(--primary-color);"></i>
                ${modalTitle}
            </h3>
            
            <form id="blogCategoryForm" style="display: flex; flex-direction: column; gap: 15px;">
                ${isEdit ? `<input type="hidden" name="id" value="${id}">` : ''}
                
                <div>
                    <label style="display: block; margin-bottom: 8px; color: var(--dark-color); font-weight: 600;">
                        Nom de la catégorie *
                    </label>
                    <input type="text" name="name" value="${name}" 
                           placeholder="Ex: Actualités, Tutoriels..."
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 1rem;"
                           required>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; color: var(--dark-color); font-weight: 600;">
                        Description
                    </label>
                    <textarea name="description" rows="3"
                              placeholder="Description de la catégorie..."
                              style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 1rem; resize: vertical;">${description}</textarea>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; color: var(--dark-color); font-weight: 600;">
                        Icône FontAwesome
                    </label>
                    <input type="text" name="icon" value="${icon}"
                           placeholder="Ex: fa-newspaper, fa-book, fa-code..."
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 1rem;">
                    <small style="color: #6c757d;">Voir: <a href="https://fontawesome.com/icons" target="_blank">FontAwesome Icons</a></small>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; color: var(--dark-color); font-weight: 600;">
                        Couleur
                    </label>
                    <input type="color" name="color" value="${color}"
                           style="width: 100%; height: 50px; border: 2px solid #e9ecef; border-radius: 10px; cursor: pointer;">
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" style="flex: 1; padding: 12px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-save"></i> ${submitText}
                    </button>
                    <button type="button" onclick="this.closest('[style*=fixed]').remove()" 
                            style="padding: 12px 20px; background: #f8f9fa; border: 2px solid #dee2e6; border-radius: 10px; font-weight: 600; cursor: pointer; color: #6c757d;">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    `;

    document.body.appendChild(modal);

    document.getElementById('blogCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';

        const url = isEdit ? '<?= BASE_URL ?>/admin/updateBlogCategory' :
            '<?= BASE_URL ?>/admin/createBlogCategory';

        fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.remove();
                    showMessage('✅ ' + data.message, 'success');
                    loadBlogCategories();
                } else {
                    showMessage('❌ ' + (data.message || 'Erreur'), 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage('❌ Erreur lors de l\'enregistrement', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
    });
};

// Modifier une catégorie
window.editBlogCategory = function(id, name, description, icon, color) {
    openBlogCategoryModal(id, name, description, icon, color);
};

// Activer/Désactiver une catégorie
window.toggleBlogCategory = function(id) {
    const formData = new FormData();
    formData.append('id', id);

    fetch('<?= BASE_URL ?>/admin/toggleBlogCategory', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('✅ ' + data.message, 'success');
                loadBlogCategories();
            } else {
                showMessage('❌ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showMessage('❌ Erreur', 'error');
        });
};

// Supprimer une catégorie
window.deleteBlogCategory = function(id, name, postsCount) {
    if (postsCount > 0) {
        showMessage('❌ Impossible de supprimer : cette catégorie contient ' + postsCount + ' article(s)', 'error');
        return;
    }

    if (!confirm(`Supprimer la catégorie "${name}" ?\nCette action est irréversible.`)) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);

    fetch('<?= BASE_URL ?>/admin/deleteBlogCategory', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('✅ ' + data.message, 'success');
                loadBlogCategories();
            } else {
                showMessage('❌ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showMessage('❌ Erreur', 'error');
        });
};

window.loadBlogPosts = function() {
    const loadingElem = document.getElementById('blogLoading');
    const contentElem = document.getElementById('blogContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    const statusFilter = document.getElementById('blogStatusFilter');

    const params = new URLSearchParams();
    if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);

    fetch('<?= BASE_URL ?>/admin/blog' + (params.toString() ? '?' + params.toString() : ''))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderBlogTable(data.posts);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'block';
                console.log('✅ ' + data.posts.length + ' article(s) chargé(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement blog:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des articles</p></div>';
            }
            window.showMessage('Erreur de chargement des articles', 'error');
        });
};

window.renderBlogTable = function(posts) {
    const blogTable = document.getElementById('blogTable');
    if (!blogTable) return;

    const statusLabels = {
        'draft': '📝 Brouillon',
        'published': '✅ Publié',
        'archived': '📦 Archivé'
    };

    const statusColors = {
        'draft': '#6c757d',
        'published': '#28a745',
        'archived': '#ffc107'
    };

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Auteur</th>
                    <th>Catégorie</th>
                    <th>Stats</th>
                    <th>Statut</th>
                    <th>Publication</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    posts.forEach(post => {
        const authorInitial = post.author_name.charAt(0).toUpperCase();
        const avatar = post.author_photo ?
            `<img src="<?= BASE_URL ?>/${post.author_photo}" alt="${post.author_name}">` :
            `<div class="avatar-placeholder-dash">${authorInitial}</div>`;

        const hasFeaturedImage = post.featured_image;

        html += `
            <tr data-post-id="${post.id}">
                <td>
                    <div class="post-cell">
                        ${hasFeaturedImage ? `
                            <img src="<?= BASE_URL ?>/${post.featured_image}" 
                                 alt="${post.title}" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 12px;">
                        ` : ''}
                        <div>
                            <strong>${post.title}</strong>
                            <p style="margin: 5px 0 0; color: #6c757d; font-size: 0.85rem;">${post.excerpt || 'Aucun extrait'}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="user-cell">
                        ${avatar}
                        <span>${post.author_name}</span>
                    </div>
                </td>
                <td>
                    <span class="category-badge">${post.category || 'Non catégorisé'}</span>
                </td>
                <td>
                    <div style="display: flex; gap: 12px; font-size: 0.9rem;">
                        <span title="Vues"><i class="fas fa-eye"></i> ${post.views}</span>
                        <span title="Commentaires"><i class="fas fa-comments"></i> ${post.comments_count}</span>
                        <span title="Likes"><i class="fas fa-heart"></i> ${post.likes_count}</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge" style="background: ${statusColors[post.status]}33; color: ${statusColors[post.status]};">
                        ${statusLabels[post.status] || post.status}
                    </span>
                </td>
                <td>
                    ${post.published_at ? new Date(post.published_at).toLocaleDateString('fr-FR') : '-'}
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/blog/show/${post.slug}" target="_blank" class="btn-action btn-view" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/blog/edit/${post.slug}" target="_blank" class="btn-action btn-primary" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        ${post.status === 'draft' ? `
                            <button class="btn-action btn-success" onclick="updateBlogPostStatus(${post.id}, 'published')" title="Publier">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        ${post.status === 'published' ? `
                            <button class="btn-action btn-warning" onclick="updateBlogPostStatus(${post.id}, 'draft')" title="Mettre en brouillon">
                                <i class="fas fa-file-alt"></i>
                            </button>
                            <button class="btn-action btn-secondary" onclick="updateBlogPostStatus(${post.id}, 'archived')" title="Archiver">
                                <i class="fas fa-archive"></i>
                            </button>
                        ` : ''}
                        ${post.status === 'archived' ? `
                            <button class="btn-action btn-success" onclick="updateBlogPostStatus(${post.id}, 'published')" title="Republier">
                                <i class="fas fa-undo"></i>
                            </button>
                        ` : ''}
                        <button class="btn-action btn-delete" onclick="deleteBlogPost(${post.id}, '${post.title}')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    if (posts.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-blog"></i><p>Aucun article</p></div>';
    }

    blogTable.innerHTML = html;
};

// Mettre à jour le statut d'un article
window.updateBlogPostStatus = function(postId, newStatus) {
    const messages = {
        'draft': 'Mettre cet article en brouillon ?',
        'published': 'Publier cet article ?',
        'archived': 'Archiver cet article ?'
    };

    if (!confirm(messages[newStatus] || 'Confirmer cette action ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('status', newStatus);

    fetch('<?= BASE_URL ?>/admin/updateBlogPostStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadBlogPosts();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la mise à jour', 'error');
        });
};

// Supprimer un article de blog
window.deleteBlogPost = function(postId, postTitle) {
    if (!confirm(`🗑️ Supprimer l'article "${postTitle}" ?\n\nCette action est IRRÉVERSIBLE !`)) {
        return;
    }

    const confirmation = prompt('Tapez "SUPPRIMER" en majuscules pour confirmer:');
    if (confirmation !== 'SUPPRIMER') {
        return;
    }

    const formData = new FormData();
    formData.append('post_id', postId);

    fetch('<?= BASE_URL ?>/admin/deleteBlogPost', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadBlogPosts();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la suppression', 'error');
        });
};

// ========================================
// MODÉRATION DES COMMENTAIRES
// ========================================

window.loadComments = function() {
    window.loadCommentsData();
};

window.loadCommentsData = function() {
    const loadingElem = document.getElementById('commentsLoading');
    const contentElem = document.getElementById('commentsContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    const statusFilter = document.getElementById('commentsStatusFilter');
    const typeFilter = document.getElementById('commentsTypeFilter');

    const params = new URLSearchParams();
    if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
    if (typeFilter && typeFilter.value) params.append('type', typeFilter.value);

    fetch('<?= BASE_URL ?>/admin/getCommentsData' + (params.toString() ? '?' + params.toString() : ''))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderCommentsTable(data.comments);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'block';
                console.log('✅ ' + data.comments.length + ' commentaire(s) chargé(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement commentaires:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des commentaires</p></div>';
            }
            window.showMessage('Erreur de chargement des commentaires', 'error');
        });
};

window.renderCommentsTable = function(comments) {
    const commentsTable = document.getElementById('commentsTable');
    if (!commentsTable) return;

    const typeLabels = {
        'post': '💬 Forum',
        'tutorial': '📚 Tutoriel',
        'blog': '📝 Blog'
    };

    const typeColors = {
        'post': '#007bff',
        'tutorial': '#28a745',
        'blog': '#dc3545'
    };

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Commentaire</th>
                    <th>Auteur</th>
                    <th>Type</th>
                    <th>Contenu</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    comments.forEach(comment => {
        const userInitial = comment.user_name.charAt(0).toUpperCase();
        const avatar = comment.user_photo ?
            `<img src="<?= BASE_URL ?>/${comment.user_photo}" alt="${comment.user_name}">` :
            `<div class="avatar-placeholder-dash">${userInitial}</div>`;

        const excerpt = comment.body.substring(0, 100) + (comment.body.length > 100 ? '...' : '');

        html += `
            <tr data-comment-id="${comment.id}">
                <td>
                    <div class="comment-preview">
                        <p style="margin: 0; color: #495057; line-height: 1.6;">${excerpt}</p>
                    </div>
                </td>
                <td>
                    <div class="user-cell">
                        ${avatar}
                        <span>${comment.user_name}</span>
                    </div>
                </td>
                <td>
                    <span class="type-badge" style="background: ${typeColors[comment.commentable_type]}22; color: ${typeColors[comment.commentable_type]}; padding: 5px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                        ${typeLabels[comment.commentable_type] || comment.commentable_type}
                    </span>
                </td>
                <td>
                    <div style="max-width: 250px;">
                        <strong style="display: block; margin-bottom: 5px; font-size: 0.9rem;">${comment.resource_title}</strong>
                        <span style="color: #6c757d; font-size: 0.8rem;">ID: ${comment.commentable_id}</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge ${comment.status}">${comment.status}</span>
                </td>
                <td>
                    ${new Date(comment.created_at).toLocaleDateString('fr-FR', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'})}
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/user/profile/${comment.user_id}" target="_blank" class="btn-action btn-view" title="Profil de l'auteur">
                            <i class="fas fa-user"></i>
                        </a>
                        ${comment.status === 'active' ? `
                            <button class="btn-action btn-warning" onclick="moderateComment(${comment.id}, 'hidden')" title="Masquer">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        ` : ''}
                        ${comment.status === 'hidden' ? `
                            <button class="btn-action btn-success" onclick="moderateComment(${comment.id}, 'active')" title="Approuver">
                                <i class="fas fa-eye"></i>
                            </button>
                        ` : ''}
                        <button class="btn-action btn-delete" onclick="deleteCommentAdmin(${comment.id})" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    if (comments.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-comment-dots"></i><p>Aucun commentaire</p></div>';
    }

    commentsTable.innerHTML = html;
};

// Modérer un commentaire (masquer/approuver)
window.moderateComment = function(commentId, newStatus) {
    const messages = {
        'active': 'Approuver ce commentaire ?',
        'hidden': 'Masquer ce commentaire ?'
    };

    if (!confirm(messages[newStatus] || 'Confirmer cette action ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('comment_id', commentId);
    formData.append('status', newStatus);

    fetch('<?= BASE_URL ?>/admin/updateCommentStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadCommentsData();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la modération', 'error');
        });
};

// Supprimer un commentaire
window.deleteCommentAdmin = function(commentId) {
    if (!confirm('🗑️ Supprimer définitivement ce commentaire ?\n\nCette action est irréversible !')) {
        return;
    }

    const formData = new FormData();
    formData.append('comment_id', commentId);

    fetch('<?= BASE_URL ?>/admin/deleteComment', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadCommentsData();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la suppression', 'error');
        });
};

// ========================================
// GESTION DES SIGNALEMENTS
// ========================================

window.loadReports = function() {
    window.loadReportsData();
};

window.loadReportsData = function() {
    const loadingElem = document.getElementById('reportsLoading');
    const contentElem = document.getElementById('reportsContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    const statusFilter = document.getElementById('reportsStatusFilter');
    const typeFilter = document.getElementById('reportsTypeFilter');

    const params = new URLSearchParams();
    if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
    if (typeFilter && typeFilter.value) params.append('type', typeFilter.value);

    fetch('<?= BASE_URL ?>/admin/getReportsData' + (params.toString() ? '?' + params.toString() : ''))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderReportsTable(data.reports);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'block';
                console.log('✅ ' + data.reports.length + ' signalement(s) chargé(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement signalements:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des signalements</p></div>';
            }
            window.showMessage('Erreur de chargement des signalements', 'error');
        });
};

window.renderReportsTable = function(reports) {
    const reportsTable = document.getElementById('reportsTable');
    if (!reportsTable) return;

    const typeLabels = {
        'post': '💬 Post Forum',
        'tutorial': '📚 Tutoriel',
        'comment': '💭 Commentaire',
        'user': '👤 Utilisateur',
        'message': '✉️ Message'
    };

    const statusLabels = {
        'pending': '⏳ En attente',
        'reviewed': '👀 Examiné',
        'resolved': '✅ Résolu',
        'dismissed': '❌ Rejeté'
    };

    const statusColors = {
        'pending': '#ffc107',
        'reviewed': '#007bff',
        'resolved': '#28a745',
        'dismissed': '#6c757d'
    };

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Signalé par</th>
                    <th>Type</th>
                    <th>Contenu signalé</th>
                    <th>Raison</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    reports.forEach(report => {
        const reporterInitial = report.reporter_name.charAt(0).toUpperCase();
        const avatar = report.reporter_photo ?
            `<img src="<?= BASE_URL ?>/${report.reporter_photo}" alt="${report.reporter_name}">` :
            `<div class="avatar-placeholder-dash">${reporterInitial}</div>`;

        const reasonExcerpt = report.reason.substring(0, 60) + (report.reason.length > 60 ? '...' : '');

        html += `
            <tr data-report-id="${report.id}">
                <td>
                    <div class="user-cell">
                        ${avatar}
                        <span>${report.reporter_name}</span>
                    </div>
                </td>
                <td>
                    <span class="type-badge" style="background: #e9ecef; color: #495057; padding: 5px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                        ${typeLabels[report.reportable_type] || report.reportable_type}
                    </span>
                </td>
                <td>
                    <div>
                        <strong style="display: block; margin-bottom: 5px;">${report.resource_title}</strong>
                        <small style="color: #6c757d;">Par: ${report.resource_author}</small>
                    </div>
                </td>
                <td>
                    <div style="max-width: 250px;">
                        <p style="margin: 0; color: #495057; font-size: 0.9rem;" title="${report.reason}">${reasonExcerpt}</p>
                        ${report.admin_note ? `<small style="display: block; margin-top: 5px; color: #28a745; font-weight: 600;">📝 ${report.admin_note}</small>` : ''}
                    </div>
                </td>
                <td>
                    <span class="status-badge" style="background: ${statusColors[report.status]}33; color: ${statusColors[report.status]};">
                        ${statusLabels[report.status] || report.status}
                    </span>
                </td>
                <td>
                    ${new Date(report.created_at).toLocaleDateString('fr-FR', {day: '2-digit', month: 'short'})}
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-action btn-view" onclick="viewReportDetails(${report.id}, ${JSON.stringify(report).replace(/"/g, '&quot;')})" title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${report.status === 'pending' ? `
                            <button class="btn-action btn-primary" onclick="reviewReport(${report.id})" title="Examiner">
                                <i class="fas fa-search"></i>
                            </button>
                        ` : ''}
                        ${report.status !== 'resolved' ? `
                            <button class="btn-action btn-success" onclick="resolveReport(${report.id})" title="Résoudre">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        ${report.status !== 'dismissed' ? `
                            <button class="btn-action btn-warning" onclick="dismissReport(${report.id})" title="Rejeter">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        <button class="btn-action btn-delete" onclick="deleteReportAdmin(${report.id})" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    if (reports.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-flag"></i><p>Aucun signalement</p></div>';
    }

    reportsTable.innerHTML = html;
};

// Voir les détails d'un signalement
window.viewReportDetails = function(reportId, report) {
    const typeLabels = {
        'post': '💬 Post Forum',
        'tutorial': '📚 Tutoriel',
        'comment': '💭 Commentaire',
        'user': '👤 Utilisateur',
        'message': '✉️ Message'
    };

    const modal = document.createElement('div');
    modal.className = 'user-modal-overlay';
    modal.innerHTML = `
        <div class="user-modal-container" style="max-width: 700px;">
            <div class="user-modal-header">
                <h3><i class="fas fa-flag"></i> Détails du Signalement #${reportId}</h3>
                <button class="btn-close-modal" onclick="this.closest('.user-modal-overlay').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="user-modal-body">
                <div class="user-details-grid">
                    <div class="detail-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <strong>Signalé par</strong>
                            <span>${report.reporter_name}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-tag"></i>
                        <div>
                            <strong>Type</strong>
                            <span>${typeLabels[report.reportable_type] || report.reportable_type}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar"></i>
                        <div>
                            <strong>Date</strong>
                            <span>${new Date(report.created_at).toLocaleDateString('fr-FR', {day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Statut</strong>
                            <span>${report.status}</span>
                        </div>
                    </div>
                </div>
                
                <div class="user-bio-section">
                    <h4><i class="fas fa-file-alt"></i> Contenu signalé</h4>
                    <p><strong>${report.resource_title}</strong></p>
                    <p style="color: #6c757d; margin-top: 5px;">Auteur: ${report.resource_author}</p>
                </div>
                
                <div class="user-bio-section">
                    <h4><i class="fas fa-exclamation-triangle"></i> Raison du signalement</h4>
                    <p>${report.reason}</p>
                </div>
                
                ${report.admin_note ? `
                    <div class="user-bio-section">
                        <h4><i class="fas fa-sticky-note"></i> Note admin</h4>
                        <p>${report.admin_note}</p>
                    </div>
                ` : ''}
                
                <div class="form-group">
                    <label>Ajouter/Modifier une note</label>
                    <textarea id="adminNoteInput" class="form-control" rows="3" placeholder="Note interne pour ce signalement...">${report.admin_note || ''}</textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button class="btn-modal-profile" onclick="updateReportWithNote(${reportId}, 'reviewed')" style="flex: 1;">
                        <i class="fas fa-search"></i> Marquer comme examiné
                    </button>
                    <button class="btn-modal-profile" onclick="updateReportWithNote(${reportId}, 'resolved')" style="flex: 1; background: #28a745;">
                        <i class="fas fa-check"></i> Résoudre
                    </button>
                    <button class="btn-modal-close" onclick="updateReportWithNote(${reportId}, 'dismissed')" style="flex: 1;">
                        <i class="fas fa-times"></i> Rejeter
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    });
};

// Mettre à jour le signalement avec note
window.updateReportWithNote = function(reportId, newStatus) {
    const adminNote = document.getElementById('adminNoteInput')?.value || '';

    const formData = new FormData();
    formData.append('report_id', reportId);
    formData.append('status', newStatus);
    formData.append('admin_note', adminNote);

    fetch('<?= BASE_URL ?>/admin/updateReportStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadReportsData();

                const modal = document.querySelector('.user-modal-overlay');
                if (modal) {
                    modal.remove();
                    document.body.style.overflow = '';
                }
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la mise à jour', 'error');
        });
};

// Examiner un signalement
window.reviewReport = function(reportId) {
    window.updateReportStatus(reportId, 'reviewed', '');
};

// Résoudre un signalement
window.resolveReport = function(reportId) {
    if (!confirm('✅ Marquer ce signalement comme résolu ?')) {
        return;
    }
    window.updateReportStatus(reportId, 'resolved', '');
};

// Rejeter un signalement
window.dismissReport = function(reportId) {
    if (!confirm('❌ Rejeter ce signalement ?')) {
        return;
    }
    window.updateReportStatus(reportId, 'dismissed', '');
};

// Mettre à jour le statut d'un signalement
window.updateReportStatus = function(reportId, newStatus, adminNote = '') {
    const formData = new FormData();
    formData.append('report_id', reportId);
    formData.append('status', newStatus);
    if (adminNote) formData.append('admin_note', adminNote);

    fetch('<?= BASE_URL ?>/admin/updateReportStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadReportsData();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la mise à jour', 'error');
        });
};

// Supprimer un signalement
window.deleteReportAdmin = function(reportId) {
    if (!confirm('🗑️ Supprimer définitivement ce signalement ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('report_id', reportId);

    fetch('<?= BASE_URL ?>/admin/deleteReport', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage(data.message, 'success');
                window.loadReportsData();
            } else {
                window.showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la suppression', 'error');
        });
};

// ========================================
// ACTIONS UTILISATEURS
// ========================================

// Voir les détails dans un modal
window.viewUserModal = function(user) {
    const userName = `${user.prenom || ''} ${user.nom || ''}`.trim() || 'Utilisateur';
    const userInitial = user.prenom ? user.prenom.charAt(0).toUpperCase() : 'U';
    const avatar = user.photo_path ?
        `<img src="<?= BASE_URL ?>/${user.photo_path}" alt="${userName}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">` :
        `<div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800;">${userInitial}</div>`;

    const modal = document.createElement('div');
    modal.className = 'user-modal-overlay';
    modal.innerHTML = `
        <div class="user-modal-container">
            <div class="user-modal-header">
                <h3><i class="fas fa-user"></i> Détails de l'utilisateur</h3>
                <button class="btn-close-modal" onclick="this.closest('.user-modal-overlay').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="user-modal-body">
                <div class="user-profile-section">
                    ${avatar}
                    <div class="user-profile-info">
                        <h2>${userName}</h2>
                        <p>${user.email}</p>
                        <div class="user-badges">
                            <span class="role-badge role-${user.role}">${user.role}</span>
                            <span class="status-badge ${user.status}">${user.status}</span>
                        </div>
                    </div>
                </div>
                
                <div class="user-details-grid">
                    <div class="detail-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email vérifié</strong>
                            <span>${user.email_verified ? '<span style="color: #28a745;">✅ Oui</span>' : '<span style="color: #dc3545;">❌ Non</span>'}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-university"></i>
                        <div>
                            <strong>Université</strong>
                            <span>${user.university || '-'}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-building"></i>
                        <div>
                            <strong>Faculté</strong>
                            <span>${user.faculty || '-'}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Ville</strong>
                            <span>${user.city || '-'}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <strong>Téléphone</strong>
                            <span>${user.phone || '-'}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-plus"></i>
                        <div>
                            <strong>Inscription</strong>
                            <span>${new Date(user.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-sign-in-alt"></i>
                        <div>
                            <strong>Dernière connexion</strong>
                            <span>${user.last_login ? new Date(user.last_login).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Jamais'}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-edit"></i>
                        <div>
                            <strong>Dernière modification</strong>
                            <span>${user.updated_at ? new Date(user.updated_at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }) : '-'}</span>
                        </div>
                    </div>
                </div>
                
                ${user.cv_path || user.document_path ? `
                    <div class="user-documents-section">
                        <h4><i class="fas fa-file-alt"></i> Documents</h4>
                        <div class="documents-grid">
                            ${user.cv_path ? `
                                <a href="<?= BASE_URL ?>/${user.cv_path}" target="_blank" class="document-item">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>Curriculum Vitae</span>
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            ` : ''}
                            ${user.document_path ? `
                                <a href="<?= BASE_URL ?>/${user.document_path}" target="_blank" class="document-item">
                                    <i class="fas fa-id-card"></i>
                                    <span>Carte étudiant / Attestation</span>
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            ` : ''}
                        </div>
                    </div>
                ` : ''}
                
                ${user.bio ? `
                    <div class="user-bio-section">
                        <h4><i class="fas fa-info-circle"></i> Bio</h4>
                        <p>${user.bio}</p>
                    </div>
                ` : ''}
            </div>
            <div class="user-modal-footer">
                <a href="<?= BASE_URL ?>/user/profile/${user.id}" target="_blank" class="btn-modal-profile">
                    <i class="fas fa-external-link-alt"></i> Voir le profil complet
                </a>
                <button class="btn-modal-close" onclick="this.closest('.user-modal-overlay').remove()">
                    Fermer
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Fermer en cliquant en dehors
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    });
};

// Suspendre un utilisateur (mettre en pause)
window.suspendUser = function(userId, userName) {
    if (!confirm(
            `⏸️ Suspendre l'utilisateur "${userName}" ?\n\nL'utilisateur ne pourra plus se connecter jusqu'à réactivation.`
        )) {
        return;
    }

    window.updateUserStatus(userId, 'suspended', 'Utilisateur suspendu');
};

// Activer/Restaurer un utilisateur
window.activateUser = function(userId, userName) {
    if (!confirm(
            `✅ Restaurer l'accès à "${userName}" ?\n\nL'utilisateur pourra se reconnecter et utiliser la plateforme.`
        )) {
        return;
    }

    window.updateUserStatus(userId, 'active', `✅ ${userName} a été restauré et peut maintenant se connecter`);
};

// Bannir un utilisateur (bannissement permanent)
window.banUser = function(userId, userName) {
    const reason = prompt(
        `🚫 BANNIR l'utilisateur "${userName}" ?\n\nCette action est sévère. Entrez une raison :`);

    if (!reason) {
        return;
    }

    if (!confirm(`⚠️ CONFIRMATION FINALE\n\nBannir définitivement "${userName}" ?\nRaison: ${reason}`)) {
        return;
    }

    window.updateUserStatus(userId, 'banned', `Utilisateur banni. Raison: ${reason}`);
};

// Supprimer un utilisateur
window.deleteUser = function(userId, userName) {
    if (!confirm(`🗑️ ATTENTION : Supprimer l'utilisateur "${userName}" ?\n\nCette action est IRRÉVERSIBLE !`)) {
        return;
    }

    const confirmation = prompt('Tapez "SUPPRIMER" en majuscules pour confirmer:');

    if (confirmation !== 'SUPPRIMER') {
        showMessage('❌ Suppression annulée', 'info');
        return;
    }

    const formData = new FormData();
    formData.append('user_id', userId);

    fetch('<?= BASE_URL ?>/admin/delete-user', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('✅ Utilisateur supprimé définitivement', 'success');

                // Retirer la ligne du tableau avec animation
                const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                if (row) {
                    row.style.animation = 'fadeOut 0.5s ease';
                    setTimeout(() => {
                        row.remove();
                    }, 500);
                }
            } else {
                showMessage('❌ Erreur: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showMessage('❌ Erreur lors de la suppression', 'error');
        });
};

// Fonction générique pour mettre à jour le statut
window.updateUserStatus = function(userId, newStatus, successMessage) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', newStatus);

    fetch('<?= BASE_URL ?>/admin/updateUser', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('✅ ' + successMessage, 'success');

                // Mettre à jour la ligne dans le tableau
                const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                if (row) {
                    const statusBadge = row.querySelector('.status-badge');
                    if (statusBadge) {
                        statusBadge.className = `status-badge ${newStatus}`;
                        statusBadge.textContent = newStatus;
                    }

                    // Ajouter un effet flash
                    row.style.animation = 'flashGreen 0.8s ease';
                    setTimeout(() => {
                        row.style.animation = '';
                    }, 800);
                }
            } else {
                showMessage('❌ Erreur: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showMessage('❌ Erreur lors de la mise à jour', 'error');
        });
};

// ========================================
// FILTRES & RECHERCHE
// ========================================
window.searchInTable = function(tableId, query) {
    const table = document.getElementById(tableId);
    const rows = table?.querySelectorAll('tbody tr') || [];

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query.toLowerCase()) ? '' : 'none';
    });
};

window.filterUsers = function(status) {
    console.log('🔍 Filtrage par statut:', status || 'tous');
    window.loadUsers(status);
};

// Filtre rapide pour les utilisateurs suspendus
window.quickFilterSuspended = function() {
    const filterSelect = document.getElementById('statusFilter');
    if (filterSelect) {
        filterSelect.value = 'suspended';
        window.filterUsers('suspended');
    }
};

window.filterForum = function(status) {
    const statusFilter = document.getElementById('postsStatusFilter');
    if (statusFilter) {
        statusFilter.value = status;
    }
    window.loadForumPosts(status);
};

window.filterTutorials = function(status) {
    const statusFilter = document.getElementById('tutorialsStatusFilter');
    if (statusFilter) {
        statusFilter.value = status;
    }
    window.loadTutorialsData();
};

window.filterProjects = function(status) {
    const statusFilter = document.getElementById('projectsStatusFilter');
    if (statusFilter) {
        statusFilter.value = status;
    }
    window.loadProjectsData();
};

window.filterComments = function(status) {
    const statusFilter = document.getElementById('commentsStatusFilter');
    if (statusFilter) {
        statusFilter.value = status;
    }
    window.loadCommentsData();
};

window.filterReports = function(status) {
    const statusFilter = document.getElementById('reportsStatusFilter');
    if (statusFilter) {
        statusFilter.value = status;
    }
    window.loadReportsData();
};

// ========================================
// GESTION DE LA NEWSLETTER
// ========================================

window.loadNewsletter = function() {
    window.loadNewsletterData();
};

window.loadNewsletterData = function() {
    const loadingElem = document.getElementById('newsletterLoading');
    const contentElem = document.getElementById('newsletterContent');

    if (loadingElem) loadingElem.style.display = 'block';
    if (contentElem) contentElem.style.display = 'none';

    const statusFilter = document.getElementById('newsletterStatusFilter');
    const params = new URLSearchParams();
    if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);

    fetch('<?= BASE_URL ?>/newsletter/getSubscribers' + (params.toString() ? '?' + params.toString() : ''))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderNewsletterTable(data.subscribers);
                window.updateNewsletterStats(data.stats);
                if (loadingElem) loadingElem.style.display = 'none';
                if (contentElem) contentElem.style.display = 'block';
                console.log('✅ ' + data.subscribers.length + ' abonné(s) chargé(s)');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement newsletter:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            if (contentElem) {
                contentElem.style.display = 'block';
                contentElem.innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement des abonnés</p></div>';
            }
            window.showMessage('Erreur de chargement des abonnés', 'error');
        });
};

window.updateNewsletterStats = function(stats) {
    if (document.getElementById('statsActiveSubscribers')) {
        document.getElementById('statsActiveSubscribers').textContent = stats.active || 0;
    }
    if (document.getElementById('statsUnsubscribed')) {
        document.getElementById('statsUnsubscribed').textContent = stats.unsubscribed || 0;
    }
    if (document.getElementById('statsBounced')) {
        document.getElementById('statsBounced').textContent = stats.bounced || 0;
    }
};

window.renderNewsletterTable = function(subscribers) {
    const newsletterTable = document.getElementById('newsletterTable');
    if (!newsletterTable) return;

    const statusLabels = {
        'active': '✅ Actif',
        'unsubscribed': '🚫 Désabonné',
        'bounced': '⚠️ Rebondi'
    };

    const statusColors = {
        'active': '#28a745',
        'unsubscribed': '#6c757d',
        'bounced': '#ffc107'
    };

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>IP</th>
                    <th>Inscrit le</th>
                    <th>Dernière newsletter</th>
                    <th>Total envoyé</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    subscribers.forEach(sub => {
        const subscribedDate = new Date(sub.subscribed_at).toLocaleDateString('fr-FR');
        const lastSent = sub.last_sent_at ? new Date(sub.last_sent_at).toLocaleDateString('fr-FR') :
            'Jamais';

        html += `
            <tr data-subscriber-id="${sub.id}">
                <td>
                    <strong>${sub.email}</strong>
                </td>
                <td>
                    <span class="status-badge" style="background: ${statusColors[sub.status]}33; color: ${statusColors[sub.status]};">
                        ${statusLabels[sub.status] || sub.status}
                    </span>
                </td>
                <td>
                    <span style="font-family: monospace; font-size: 0.85rem;">${sub.ip_address || 'N/A'}</span>
                </td>
                <td>
                    ${subscribedDate}
                </td>
                <td>
                    ${lastSent}
                </td>
                <td>
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-envelope"></i> ${sub.total_sent}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        ${sub.status === 'active' ? `
                            <button class="btn-action btn-info" onclick="copyEmail('${sub.email}')" title="Copier l'email">
                                <i class="fas fa-copy"></i>
                            </button>
                        ` : ''}
                        <button class="btn-action btn-delete" onclick="deleteSubscriber(${sub.id}, '${sub.email}')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    if (subscribers.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-envelope-open"></i><p>Aucun abonné</p></div>';
    }

    newsletterTable.innerHTML = html;
};

// Copier l'email dans le presse-papiers
window.copyEmail = function(email) {
    navigator.clipboard.writeText(email).then(() => {
        window.showMessage('✅ Email copié : ' + email, 'success');
    }).catch(err => {
        console.error('Erreur copie:', err);
        window.showMessage('❌ Erreur lors de la copie', 'error');
    });
};

// Supprimer un abonné
window.deleteSubscriber = function(subscriberId, email) {
    if (!confirm(`🗑️ Supprimer définitivement l'abonné "${email}" ?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('id', subscriberId);

    fetch('<?= BASE_URL ?>/newsletter/deleteSubscriber', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage('✅ ' + data.message, 'success');
                window.loadNewsletterData();
            } else {
                window.showMessage('❌ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('❌ Erreur lors de la suppression', 'error');
        });
};

// Exporter les abonnés en CSV
window.exportNewsletterSubscribers = function() {
    window.location.href = '<?= BASE_URL ?>/newsletter/exportSubscribers';
    window.showMessage('✅ Export en cours...', 'success');
};

// ========================================
// GESTION DES PARAMÈTRES
// ========================================

window.loadSettings = function() {
    window.loadSettingsData();

    // Gérer les onglets des paramètres
    const tabButtons = document.querySelectorAll('#section-settings .forum-tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.getAttribute('data-tab');

            // Mettre à jour les boutons
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Mettre à jour les contenus
            document.querySelectorAll('#settingsContainer .forum-tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById('settings-tab-' + tab).classList.add('active');

            // Charger les stats système si nécessaire
            if (tab === 'system') {
                window.loadSystemStats();
            }

            // Charger les infos de backup si nécessaire
            if (tab === 'backup') {
                window.loadBackupInfo();
            }
        });
    });
};

window.loadSettingsData = function() {
    const loadingElem = document.getElementById('settingsLoading');
    const containerElem = document.getElementById('settingsContainer');

    if (loadingElem) loadingElem.style.display = 'block';
    if (containerElem) containerElem.style.display = 'none';

    fetch('<?= BASE_URL ?>/admin/getSettings')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderSettingsForms(data.settings);
                if (loadingElem) loadingElem.style.display = 'none';
                if (containerElem) containerElem.style.display = 'block';
                console.log('✅ Paramètres chargés');
            } else {
                throw new Error(data.message || 'Erreur de chargement');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement paramètres:', error);
            if (loadingElem) loadingElem.style.display = 'none';
            window.showMessage('Erreur de chargement des paramètres', 'error');
        });
};

window.renderSettingsForms = function(settingsByCategory) {
    // Formulaire Général
    const generalForm = document.getElementById('settingsGeneralForm');
    if (generalForm && settingsByCategory.general) {
        generalForm.innerHTML = window.renderCategorySettings(settingsByCategory.general, 'general');
    }

    // Formulaire Sécurité
    const securityForm = document.getElementById('settingsSecurityForm');
    if (securityForm && settingsByCategory.security) {
        securityForm.innerHTML = window.renderCategorySettings(settingsByCategory.security, 'security');
    }

    // Formulaire Modération
    const moderationForm = document.getElementById('settingsModerationForm');
    if (moderationForm) {
        if (settingsByCategory.moderation && settingsByCategory.moderation.length > 0) {
            moderationForm.innerHTML = window.renderCategorySettings(settingsByCategory.moderation, 'moderation');
        } else {
            // Afficher des paramètres de modération par défaut si aucun n'existe
            moderationForm.innerHTML = window.renderDefaultModerationSettings();
        }
    }
};

window.renderCategorySettings = function(settings, category) {
    let html = '<div class="settings-group">';

    settings.forEach(setting => {
        html += `
            <div class="setting-item">
                <div class="setting-info">
                    <label>${setting.description || setting.setting_key}</label>
                    <small>Clé: ${setting.setting_key}</small>
                </div>
                <div class="setting-control">
        `;

        if (setting.setting_type === 'boolean') {
            const checked = setting.setting_value == '1' ? 'checked' : '';
            html += `
                <label class="toggle-switch">
                    <input type="checkbox" ${checked} 
                           onchange="saveSetting('${setting.setting_key}', this.checked ? '1' : '0')">
                    <span class="toggle-slider"></span>
                </label>
            `;
        } else if (setting.setting_type === 'number') {
            html += `
                <input type="number" 
                       value="${setting.setting_value}" 
                       onchange="saveSetting('${setting.setting_key}', this.value)">
            `;
        } else {
            html += `
                <input type="text" 
                       value="${setting.setting_value || ''}" 
                       onchange="saveSetting('${setting.setting_key}', this.value)"
                       placeholder="${setting.description}">
            `;
        }

        html += `
                </div>
            </div>
        `;
    });

    html += '</div>';
    html += `
        <div style="text-align: right; margin-top: 20px;">
            <button type="button" class="btn-primary-admin" onclick="window.showMessage('Paramètres sauvegardés automatiquement', 'success')">
                <i class="fas fa-check"></i> Paramètres sauvegardés automatiquement
            </button>
        </div>
    `;

    return html;
};

// Afficher les paramètres de modération par défaut
window.renderDefaultModerationSettings = function() {
    const defaultSettings = [{
            setting_key: 'auto_moderate_posts',
            setting_value: 'false',
            setting_type: 'boolean',
            description: 'Modération automatique des posts (nécessite validation avant publication)'
        },
        {
            setting_key: 'auto_moderate_comments',
            setting_value: 'false',
            setting_type: 'boolean',
            description: 'Modération automatique des commentaires'
        },
        {
            setting_key: 'moderation_keywords',
            setting_value: '',
            setting_type: 'text',
            description: 'Mots-clés à surveiller (séparés par des virgules)'
        },
        {
            setting_key: 'max_reports_before_hide',
            setting_value: '3',
            setting_type: 'number',
            description: 'Nombre de signalements avant masquage automatique'
        },
        {
            setting_key: 'require_email_verification',
            setting_value: 'true',
            setting_type: 'boolean',
            description: 'Vérification email requise pour publier'
        },
        {
            setting_key: 'spam_detection_enabled',
            setting_value: 'true',
            setting_type: 'boolean',
            description: 'Activer la détection de spam'
        },
        {
            setting_key: 'min_account_age_to_post',
            setting_value: '0',
            setting_type: 'number',
            description: 'Âge minimum du compte pour publier (en jours)'
        },
        {
            setting_key: 'max_posts_per_day',
            setting_value: '10',
            setting_type: 'number',
            description: 'Nombre maximum de posts par jour par utilisateur'
        }
    ];

    let html = '<div class="settings-group">';
    html +=
        '<div style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 25px;">';
    html += '<i class="fas fa-info-circle" style="color: #856404; margin-right: 8px;"></i>';
    html += '<strong style="color: #856404;">Information:</strong> ';
    html +=
        '<span style="color: #856404;">Ces paramètres seront créés automatiquement lors de la première sauvegarde.</span>';
    html += '</div>';

    defaultSettings.forEach(setting => {
        html += `
            <div class="setting-item">
                <div class="setting-info">
                    <label>${setting.description}</label>
                    <small>Clé: ${setting.setting_key}</small>
                </div>
                <div class="setting-control">
        `;

        if (setting.setting_type === 'boolean') {
            const checked = setting.setting_value === 'true' ? 'checked' : '';
            html += `
                <label class="toggle-switch">
                    <input type="checkbox" ${checked} 
                           onchange="saveModerationSetting('${setting.setting_key}', this.checked ? '1' : '0')">
                    <span class="toggle-slider"></span>
                </label>
            `;
        } else if (setting.setting_type === 'number') {
            html += `
                <input type="number" 
                       value="${setting.setting_value}" 
                       onchange="saveModerationSetting('${setting.setting_key}', this.value)"
                       min="0">
            `;
        } else {
            html += `
                <input type="text" 
                       value="${setting.setting_value || ''}" 
                       onchange="saveModerationSetting('${setting.setting_key}', this.value)"
                       placeholder="${setting.description}">
            `;
        }

        html += `
                </div>
            </div>
        `;
    });

    html += '</div>';
    html += `
        <div style="text-align: right; margin-top: 20px;">
            <button type="button" class="btn-primary-admin" onclick="window.showMessage('Paramètres sauvegardés automatiquement', 'success')">
                <i class="fas fa-check"></i> Paramètres sauvegardés automatiquement
            </button>
        </div>
    `;

    return html;
};

// Sauvegarder un paramètre de modération (crée le paramètre s'il n'existe pas)
window.saveModerationSetting = function(key, value) {
    const formData = new FormData();
    formData.append('key', key);
    formData.append('value', value);
    formData.append('category', 'moderation');

    fetch('<?= BASE_URL ?>/admin/updateSetting', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage('✅ ' + data.message, 'success');
                // Recharger les paramètres pour mettre à jour l'affichage
                setTimeout(() => {
                    window.loadSettingsData();
                }, 500);
            } else {
                window.showMessage('❌ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la sauvegarde', 'error');
        });
};

// Sauvegarder un paramètre individuel
window.saveSetting = function(key, value) {
    const formData = new FormData();
    formData.append('key', key);
    formData.append('value', value);

    fetch('<?= BASE_URL ?>/admin/updateSetting', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showMessage('✅ ' + data.message, 'success');
            } else {
                window.showMessage('❌ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.showMessage('Erreur lors de la sauvegarde', 'error');
        });
};

// Charger les statistiques système
window.loadSystemStats = function() {
    const statsContent = document.getElementById('systemStatsContent');
    if (!statsContent) return;

    statsContent.innerHTML =
        '<div class="content-loading"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';

    fetch('<?= BASE_URL ?>/admin/getSystemStats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.renderSystemStats(data.stats);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            statsContent.innerHTML =
                '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement</p></div>';
        });
};

window.renderSystemStats = function(stats) {
    const statsContent = document.getElementById('systemStatsContent');
    if (!statsContent) return;

    statsContent.innerHTML = `
        <div class="system-stat-card">
            <i class="fas fa-database"></i>
            <h4>Taille Base de Données</h4>
            <div class="stat-value">${stats.database_size}</div>
        </div>
        <div class="system-stat-card">
            <i class="fas fa-table"></i>
            <h4>Nombre de Tables</h4>
            <div class="stat-value">${stats.total_tables}</div>
        </div>
        <div class="system-stat-card">
            <i class="fas fa-hdd"></i>
            <h4>Espace Disque (Uploads)</h4>
            <div class="stat-value">${stats.disk_usage}</div>
        </div>
        <div class="system-stat-card">
            <i class="fab fa-php"></i>
            <h4>Version PHP</h4>
            <div class="stat-value">${stats.php_version}</div>
        </div>
        <div class="system-stat-card">
            <i class="fas fa-database"></i>
            <h4>Version MySQL</h4>
            <div class="stat-value" style="font-size: 1.5rem;">${stats.mysql_version.split('-')[0]}</div>
        </div>
        <div class="system-stat-card">
            <i class="fas fa-server"></i>
            <h4>Serveur</h4>
            <div class="stat-value" style="font-size: 1.3rem;">Apache</div>
        </div>
    `;
};

// Charger les informations de sauvegarde
window.loadBackupInfo = function() {
    fetch('<?= BASE_URL ?>/admin/getSystemStats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dbSizeElem = document.getElementById('dbSize');
                if (dbSizeElem) {
                    dbSizeElem.textContent = data.stats.database_size;
                }
            }
        })
        .catch(error => console.error('Erreur:', error));

    // Charger la date de dernière sauvegarde si elle existe
    fetch('<?= BASE_URL ?>/admin/getSettings')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.settings.maintenance) {
                const lastBackupSetting = data.settings.maintenance.find(s => s.setting_key ===
                    'last_backup_date');
                if (lastBackupSetting && lastBackupSetting.setting_value) {
                    const lastBackupElem = document.getElementById('lastBackupTime');
                    if (lastBackupElem) {
                        const date = new Date(lastBackupSetting.setting_value);
                        lastBackupElem.textContent = date.toLocaleString('fr-FR');
                    }
                }
            }
        })
        .catch(error => console.error('Erreur:', error));
};

// Télécharger la sauvegarde de la base de données
window.downloadDatabaseBackup = function(type = 'full') {
    const btnText = type === 'structure' ? 'Téléchargement de la structure...' :
        'Téléchargement de la sauvegarde...';

    window.showMessage(btnText, 'info');

    // Créer un lien invisible et le cliquer pour télécharger
    const url = '<?= BASE_URL ?>/admin/downloadDatabaseBackup' + (type === 'structure' ? '?type=structure' :
        '?type=full');
    const link = document.createElement('a');
    link.href = url;
    link.download = `hubtech_backup_${new Date().toISOString().slice(0,10)}.sql`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Mettre à jour la date de dernière sauvegarde après 2 secondes
    setTimeout(() => {
        const lastBackupElem = document.getElementById('lastBackupTime');
        if (lastBackupElem) {
            lastBackupElem.textContent = new Date().toLocaleString('fr-FR');
        }
        window.showMessage('✅ Sauvegarde téléchargée avec succès !', 'success');
    }, 2000);
};

// ========================================
// NOTIFICATIONS
// ========================================
window.showMessage = function(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = 'notification-toast notification-' + type;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    notification.style.cssText = `
        position: fixed;
        top: 90px;
        right: 30px;
        z-index: 10000;
        padding: 18px 25px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        border-left: 4px solid ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#667eea'};
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    `;

    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.5s ease';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
};

// Style pour les états vides
const emptyStateStyle = document.createElement('style');
emptyStateStyle.textContent = `
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #999;
}
.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}
.empty-state p {
    font-size: 18px;
    margin: 0;
}
`;
document.head.appendChild(emptyStateStyle);

// ========================================
// CHARTS (Chart.js)
// ========================================

let activityChart = null;
let contentChart = null;
let countriesChart = null;
let devicesChart = null;

window.loadCharts = function() {
    fetch('<?= BASE_URL ?>/admin/getChartData')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.createActivityChart(data.activity);
                window.createContentChart(data.content);
            }
        })
        .catch(error => console.error('Erreur chargement graphiques:', error));
};

window.createActivityChart = function(activityData) {
    const ctx = document.getElementById('activityChart');
    if (!ctx) return;

    // Détruire le graphique existant si présent
    if (activityChart) {
        activityChart.destroy();
    }

    // Formater les dates pour l'affichage
    const labels = activityData.labels.map(date => {
        const d = new Date(date);
        return d.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'short'
        });
    });

    activityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nouvelles inscriptions',
                data: activityData.data,
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(102, 126, 234)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
};

window.createContentChart = function(contentData) {
    const ctx = document.getElementById('contentChart');
    if (!ctx) return;

    // Détruire le graphique existant si présent
    if (contentChart) {
        contentChart.destroy();
    }

    contentChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Forum', 'Tutoriels', 'Projets', 'Blog'],
            datasets: [{
                data: [
                    contentData.posts || 0,
                    contentData.tutorials || 0,
                    contentData.projects || 0,
                    contentData.blog || 0
                ],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    'rgb(102, 126, 234)',
                    'rgb(40, 167, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(220, 53, 69)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                title: {
                    display: false
                }
            }
        }
    });
};

// ========================================
// STATISTIQUES AVANCÉES
// ========================================

window.loadStatistics = function() {
    fetch('<?= BASE_URL ?>/admin/getAdvancedStatistics')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour les stats en haut
                document.getElementById('onlineUsersCount').textContent = data.summary.online;
                document.getElementById('todayVisitsCount').textContent = data.summary.today_visits;
                document.getElementById('countriesCount').textContent = data.summary.countries;
                document.getElementById('topUserName').textContent = data.summary.top_user;

                // Créer les graphiques
                window.createCountriesChart(data.countries);
                window.createDevicesChart(data.devices);

                // Afficher les tableaux
                window.renderOnlineUsers(data.online_users);
                window.renderTopUsers(data.top_users);
                window.renderVisitorLogs(data.visitor_logs);
            }
        })
        .catch(error => console.error('Erreur chargement statistiques:', error));
};

window.createCountriesChart = function(countriesData) {
    const ctx = document.getElementById('countriesChart');
    if (!ctx) return;

    if (countriesChart) {
        countriesChart.destroy();
    }

    const labels = countriesData.map(c => c.country || 'Inconnu');
    const values = countriesData.map(c => parseInt(c.visitors));

    countriesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Visiteurs',
                data: values,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgb(102, 126, 234)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
};

window.createDevicesChart = function(devicesData) {
    const ctx = document.getElementById('devicesChart');
    if (!ctx) return;

    if (devicesChart) {
        devicesChart.destroy();
    }

    const deviceLabels = {
        'desktop': 'Ordinateur',
        'mobile': 'Mobile',
        'tablet': 'Tablette',
        'bot': 'Bot'
    };

    const labels = devicesData.map(d => deviceLabels[d.device_type] || d.device_type);
    const values = devicesData.map(d => parseInt(d.count));

    devicesChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(40, 167, 69, 0.8)'
                ],
                borderColor: [
                    'rgb(102, 126, 234)',
                    'rgb(220, 53, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(40, 167, 69)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
};

window.renderOnlineUsers = function(onlineUsers) {
    const container = document.getElementById('onlineUsersTable');

    if (!onlineUsers || onlineUsers.length === 0) {
        container.innerHTML =
            '<div class="empty-state"><i class="fas fa-users-slash"></i><p>Aucun utilisateur en ligne</p></div>';
        return;
    }

    let html = '<div class="online-users-grid">';

    onlineUsers.forEach(user => {
        const userName = user.user_id ? `${user.prenom} ${user.nom}` : 'Visiteur';
        const userPhoto = user.photo_path ? `<?= BASE_URL ?>/${user.photo_path}` : null;
        const userInitial = userName.charAt(0).toUpperCase();
        const lastSeen = new Date(user.last_seen).toLocaleTimeString('fr-FR');
        const pageUrl = user.page_url || '/';

        html += `
            <div class="online-user-card">
                <div class="online-user-avatar">
                    ${userPhoto ? `<img src="${userPhoto}" alt="${userName}">` : `<div class="avatar-placeholder">${userInitial}</div>`}
                    <span class="online-indicator"></span>
                </div>
                <div class="online-user-info">
                    <h4>${userName}</h4>
                    <p><i class="fas fa-network-wired"></i> ${user.ip_address}</p>
                    <p><i class="fas fa-clock"></i> ${lastSeen}</p>
                    <p class="page-url"><i class="fas fa-link"></i> ${pageUrl}</p>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
};

window.renderTopUsers = function(topUsers) {
    const container = document.getElementById('topUsersTable');

    if (!topUsers || topUsers.length === 0) {
        container.innerHTML =
            '<div class="empty-state"><i class="fas fa-trophy"></i><p>Aucune activité récente</p></div>';
        return;
    }

    let html = '<div class="top-users-grid">';

    topUsers.forEach((user, index) => {
        const userPhoto = user.photo_path ? `<?= BASE_URL ?>/${user.photo_path}` : null;
        const userInitial = user.prenom.charAt(0).toUpperCase();
        const rank = index + 1;
        const rankBadge = rank === 1 ? '🥇' : rank === 2 ? '🥈' : rank === 3 ? '🥉' : `#${rank}`;

        html += `
            <div class="top-user-card ${rank <= 3 ? 'top-rank' : ''}">
                <div class="rank-badge">${rankBadge}</div>
                <div class="top-user-avatar">
                    ${userPhoto ? `<img src="${userPhoto}" alt="${user.prenom} ${user.nom}">` : `<div class="avatar-placeholder">${userInitial}</div>`}
                </div>
                <h4>${user.prenom} ${user.nom}</h4>
                <p class="user-email">${user.email}</p>
                <div class="activity-stats">
                    <span><i class="fas fa-chart-line"></i> ${user.total_activities} actions</span>
                    <span><i class="fas fa-comments"></i> ${user.posts || 0} posts</span>
                    <span><i class="fas fa-comment"></i> ${user.comments || 0} commentaires</span>
                    <span><i class="fas fa-book"></i> ${user.tutorials || 0} tutoriels</span>
                    <span><i class="fas fa-project-diagram"></i> ${user.projects || 0} projets</span>
                </div>
                <a href="<?= BASE_URL ?>/user/profile/${user.id}" class="btn-view-profile-stats" target="_blank">
                    Voir le profil <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
};

window.renderVisitorLogs = function(logs) {
    const container = document.getElementById('visitorLogsTable');

    if (!logs || logs.length === 0) {
        container.innerHTML =
            '<div class="empty-state"><i class="fas fa-history"></i><p>Aucune visite enregistrée</p></div>';
        return;
    }

    let html = `
        <table class="table-admin">
            <thead>
                <tr>
                    <th>Date/Heure</th>
                    <th>Utilisateur</th>
                    <th>IP</th>
                    <th>Localisation</th>
                    <th>Appareil</th>
                    <th>Navigateur/OS</th>
                    <th>Page</th>
                </tr>
            </thead>
            <tbody>
    `;

    logs.forEach(log => {
        const date = new Date(log.created_at).toLocaleString('fr-FR');
        const userName = log.user_id ? `${log.prenom} ${log.nom}` : 'Visiteur';
        const location = log.country && log.city ? `${log.city}, ${log.country}` : (log.country ||
            'Inconnu');
        const deviceIcon = log.device_type === 'mobile' ? 'fa-mobile-alt' : log.device_type === 'tablet' ?
            'fa-tablet-alt' : 'fa-desktop';
        const pageUrl = log.page_url || '/';

        html += `
            <tr>
                <td>${date}</td>
                <td>${userName}</td>
                <td><code>${log.ip_address}</code></td>
                <td><i class="fas fa-map-marker-alt"></i> ${location}</td>
                <td><i class="fas ${deviceIcon}"></i> ${log.device_type || 'desktop'}</td>
                <td>${log.browser || '-'} / ${log.os || '-'}</td>
                <td><small>${pageUrl}</small></td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;

    container.innerHTML = html;
};

// ========================================
// INIT
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Admin Dashboard initialisé');

    // Charger les graphiques pour la vue d'ensemble
    if (typeof Chart !== 'undefined') {
        window.loadCharts();
    } else {
        console.warn('Chart.js non chargé');
    }

    // Attacher les événements de clic aux liens de navigation
    const navLinks = document.querySelectorAll('.nav-item-ultra[data-section]');

    navLinks.forEach((link) => {
        const section = link.getAttribute('data-section');

        link.addEventListener('click', function(e) {
            e.preventDefault();
            window.switchSection(e, section);
        });
    });

    // Gérer la navigation par hash
    const hash = window.location.hash.substring(1);
    if (hash && hash !== 'overview' && hash !== '') {
        // Trouver et activer le nav item correspondant
        document.querySelectorAll('.nav-item-ultra').forEach(item => {
            item.classList.remove('active');
        });

        window.switchSection(null, hash);

        // Activer le bon nav item
        const navItems = document.querySelectorAll('.nav-item-ultra');
        navItems.forEach(item => {
            const onclick = item.getAttribute('onclick');
            if (onclick && onclick.includes(`'${hash}'`)) {
                item.classList.add('active');
            }
        });
    }

    // Écouter les changements de hash
    window.addEventListener('hashchange', function() {
        const newHash = window.location.hash.substring(1);
        if (newHash) {
            window.switchSection(null, newHash);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>