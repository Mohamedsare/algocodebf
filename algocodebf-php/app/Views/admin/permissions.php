<?php
$pageTitle = 'Gestion des Permissions - Admin - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Récupérer les statistiques pour la sidebar
$db = Database::getInstance();
$stats = [
    'total_users' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0,
    'total_posts' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE status = 'active'")['count'] ?? 0,
    'total_tutorials' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE status = 'active'")['count'] ?? 0,
    'total_projects' => $db->queryOne("SELECT COUNT(*) as count FROM projects")['count'] ?? 0,
    'total_jobs' => $db->queryOne("SELECT COUNT(*) as count FROM jobs WHERE status = 'active'")['count'] ?? 0,
    'total_subscribers' => $db->queryOne("SELECT COUNT(*) as count FROM newsletter_subscribers")['count'] ?? 0,
    'pending_reports' => $db->queryOne("SELECT COUNT(*) as count FROM reports WHERE status = 'pending'")['count'] ?? 0,
];
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
            <a href="<?= BASE_URL ?>/admin/dashboard" class="nav-item-ultra">
                <i class="fas fa-chart-pie"></i>
                <span>Vue d'ensemble</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#users" class="nav-item-ultra">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
                <span class="count-badge"><?= $stats['total_users'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#forum" class="nav-item-ultra">
                <i class="fas fa-comments"></i>
                <span>Forum</span>
                <span class="count-badge"><?= $stats['total_posts'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#tutorials" class="nav-item-ultra">
                <i class="fas fa-book-open"></i>
                <span>Tutoriels</span>
                <span class="count-badge"><?= $stats['total_tutorials'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#projects" class="nav-item-ultra">
                <i class="fas fa-project-diagram"></i>
                <span>Projets</span>
                <span class="count-badge"><?= $stats['total_projects'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#opportunities" class="nav-item-ultra">
                <i class="fas fa-briefcase"></i>
                <span>Opportunités</span>
                <span class="count-badge"><?= $stats['total_jobs'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#blog" class="nav-item-ultra">
                <i class="fas fa-blog"></i>
                <span>Blog</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#comments" class="nav-item-ultra">
                <i class="fas fa-comment-dots"></i>
                <span>Commentaires</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#reports" class="nav-item-ultra">
                <i class="fas fa-flag"></i>
                <span>Signalements</span>
                <?php if (($stats['pending_reports'] ?? 0) > 0): ?>
                <span class="alert-badge"><?= $stats['pending_reports'] ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#newsletter" class="nav-item-ultra">
                <i class="fas fa-envelope-open-text"></i>
                <span>Newsletter</span>
                <span class="count-badge"><?= $stats['total_subscribers'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#statistics" class="nav-item-ultra">
                <i class="fas fa-chart-bar"></i>
                <span>Statistiques</span>
            </a>
            <a href="<?= BASE_URL ?>/ddosmonitoring" class="nav-item-ultra">
                <i class="fas fa-shield-halved"></i>
                <span>Protection DDoS</span>
                <?php 
                try {
                    $ddosFile = __DIR__ . '/../../Helpers/DDoSProtection.php';
                    if (file_exists($ddosFile)) {
                        require_once $ddosFile;
                        $ddosProtection = new DDoSProtection();
                        $ddosStats = $ddosProtection->getStats();
                        $blockedIps = $ddosStats['blocked_ips'] ?? 0;
                        if ($blockedIps > 0): ?>
                            <span class="alert-badge"><?= $blockedIps ?></span>
                        <?php endif;
                    }
                } catch (Exception $e) {
                    // En cas d'erreur, ne pas afficher de badge
                }
                ?>
            </a>
            <a href="<?= BASE_URL ?>/admin/permissions" class="nav-item-ultra active">
                <i class="fas fa-shield-alt"></i>
                <span>Permissions</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard#settings" class="nav-item-ultra">
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
                <h1><i class="fas fa-shield-alt"></i> Gestion des Permissions</h1>
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
            <div class="admin-section-content active">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Stats rapides -->
        <div class="permissions-stats">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3><?= count($users) ?></h3>
                <p>Utilisateurs</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <h3><?= count(array_filter($users, fn($u) => $u['can_create_tutorial'])) ?></h3>
                <p>Autorisés Tutoriels</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-project-diagram"></i>
                <h3><?= count(array_filter($users, fn($u) => $u['can_create_project'])) ?></h3>
                <p>Autorisés Projets</p>
            </div>
        </div>

        <!-- Filtres de recherche -->
        <div class="search-filters">
            <input type="text" id="searchUser" class="search-input" placeholder="Rechercher un utilisateur...">
            <select id="filterStatus" class="filter-select">
                <option value="">Tous les statuts</option>
                <option value="active">Actifs</option>
                <option value="pending">En attente</option>
                <option value="suspended">Suspendus</option>
            </select>
            <select id="filterPermissions" class="filter-select">
                <option value="">Toutes les permissions</option>
                <option value="both">Toutes accordées</option>
                <option value="tutorial">Tutoriels uniquement</option>
                <option value="project">Projets uniquement</option>
                <option value="none">Aucune</option>
            </select>
        </div>

        <!-- Table des utilisateurs -->
        <div class="permissions-table-wrapper">
            <table class="permissions-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th><i class="fas fa-book"></i> Tutoriels</th>
                        <th><i class="fas fa-project-diagram"></i> Projets</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Aucun utilisateur trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr data-user-id="<?= $user['id'] ?>" 
                                data-status="<?= htmlspecialchars($user['status']) ?>"
                                data-tutorial="<?= $user['can_create_tutorial'] ? '1' : '0' ?>"
                                data-project="<?= $user['can_create_project'] ? '1' : '0' ?>">
                                <td>
                                    <div class="user-info">
                                        <i class="fas fa-user-circle user-icon"></i>
                                        <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= htmlspecialchars($user['status']) ?>">
                                        <?= $user['status'] === 'active' ? 'Actif' : 
                                           ($user['status'] === 'pending' ? 'En attente' : 
                                           ($user['status'] === 'suspended' ? 'Suspendu' : 'Banni')) ?>
                                    </span>
                                </td>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                               class="permission-toggle" 
                                               data-user-id="<?= $user['id'] ?>" 
                                               data-permission="tutorial"
                                               <?= $user['can_create_tutorial'] ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                               class="permission-toggle" 
                                               data-user-id="<?= $user['id'] ?>" 
                                               data-permission="project"
                                               <?= $user['can_create_project'] ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-small btn-success grant-all" 
                                                data-user-id="<?= $user['id'] ?>"
                                                title="Tout accorder">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                        <button class="btn-small btn-danger revoke-all" 
                                                data-user-id="<?= $user['id'] ?>"
                                                title="Tout retirer">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Légende -->
        <div class="permissions-legend">
            <h3><i class="fas fa-info-circle"></i> Légende</h3>
            <ul>
                <li><strong>Tutoriels :</strong> Permet à l'utilisateur de créer et publier des tutoriels</li>
                <li><strong>Projets :</strong> Permet à l'utilisateur de créer et gérer des projets collaboratifs</li>
                <li><strong>Actions :</strong> Boutons pour accorder ou retirer toutes les permissions en un clic</li>
            </ul>
                <p class="note"><i class="fas fa-shield-alt"></i> Les administrateurs ont automatiquement toutes les permissions.</p>
            </div>
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
    color: white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
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
    margin: 0;
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
    display: block;
    animation: fadeInSection 0.5s ease;
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
}

/* ================================
   STYLES SPÉCIFIQUES PERMISSIONS
   ================================ */

.permissions-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border-left: 4px solid var(--primary-color);
}

.stat-card i {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.stat-card h3 {
    font-size: 2rem;
    margin: 10px 0;
    color: var(--secondary-color);
}

.stat-card p {
    color: #666;
    font-size: 0.9rem;
}

.search-filters {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.search-input, .filter-select {
    flex: 1;
    min-width: 200px;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-input:focus, .filter-select:focus {
    outline: none;
    border-color: var(--primary-color);
}

.permissions-table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow-x: auto;
    margin-bottom: 30px;
}

.permissions-table {
    width: 100%;
    border-collapse: collapse;
}

.permissions-table thead {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.permissions-table th,
.permissions-table td {
    padding: 15px;
    text-align: left;
}

.permissions-table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.permissions-table tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s ease;
}

.permissions-table tbody tr:hover {
    background: rgba(200, 16, 46, 0.02);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-icon {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.user-name {
    font-weight: 500;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-suspended {
    background: #f8d7da;
    color: #721c24;
}

.status-banned {
    background: #dc3545;
    color: white;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 26px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--secondary-color);
}

input:checked + .slider:before {
    transform: translateX(24px);
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-small {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
    transform: translateY(-2px);
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    transform: translateY(-2px);
}

.permissions-legend {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.permissions-legend h3 {
    color: var(--primary-color);
    margin-bottom: 15px;
}

.permissions-legend ul {
    list-style: none;
    padding: 0;
}

.permissions-legend li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.permissions-legend li:last-child {
    border-bottom: none;
}

.note {
    margin-top: 15px;
    padding: 12px;
    background: rgba(255, 209, 0, 0.1);
    border-left: 4px solid var(--accent-color);
    border-radius: 4px;
    font-style: italic;
}

/* Responsive */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .btn-back-dashboard {
        width: 100%;
        justify-content: center;
    }
    
    .search-filters {
        flex-direction: column;
    }
    
    .search-input, .filter-select {
        width: 100%;
    }
    
    .permissions-table {
        font-size: 0.85rem;
    }
    
    .permissions-table th,
    .permissions-table td {
        padding: 10px 8px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?= $csrf_token ?>';

    // Gestion des toggles de permissions individuelles
    document.querySelectorAll('.permission-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const permissionType = this.dataset.permission;
            const action = this.checked ? 'grant' : 'revoke';

            fetch('<?= BASE_URL ?>/admin/togglePermission', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}&permission_type=${permissionType}&action=${action}&csrf_token=${csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    updateRowData(userId, permissionType, action === 'grant');
                } else {
                    showNotification(data.message, 'error');
                    // Revert the toggle
                    this.checked = !this.checked;
                }
            })
            .catch(error => {
                showNotification('Erreur de connexion', 'error');
                this.checked = !this.checked;
            });
        });
    });

    // Accorder toutes les permissions
    document.querySelectorAll('.grant-all').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            
            if (confirm('Accorder toutes les permissions à cet utilisateur ?')) {
                fetch('<?= BASE_URL ?>/admin/grantAllPermissions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}&csrf_token=${csrfToken}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Mettre à jour les toggles
                        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                        row.querySelectorAll('.permission-toggle').forEach(toggle => {
                            toggle.checked = true;
                        });
                        updateRowData(userId, 'both', true);
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
            }
        });
    });

    // Retirer toutes les permissions
    document.querySelectorAll('.revoke-all').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            
            if (confirm('Retirer toutes les permissions à cet utilisateur ?')) {
                fetch('<?= BASE_URL ?>/admin/revokeAllPermissions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}&csrf_token=${csrfToken}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Mettre à jour les toggles
                        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                        row.querySelectorAll('.permission-toggle').forEach(toggle => {
                            toggle.checked = false;
                        });
                        updateRowData(userId, 'both', false);
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
            }
        });
    });

    // Recherche d'utilisateurs
    document.getElementById('searchUser').addEventListener('input', filterTable);
    document.getElementById('filterStatus').addEventListener('change', filterTable);
    document.getElementById('filterPermissions').addEventListener('change', filterTable);

    function filterTable() {
        const searchTerm = document.getElementById('searchUser').value.toLowerCase();
        const statusFilter = document.getElementById('filterStatus').value;
        const permissionFilter = document.getElementById('filterPermissions').value;
        
        document.querySelectorAll('#usersTableBody tr').forEach(row => {
            const userName = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
            const userEmail = row.cells[1]?.textContent.toLowerCase() || '';
            const status = row.dataset.status;
            const hasTutorial = row.dataset.tutorial === '1';
            const hasProject = row.dataset.project === '1';
            
            let show = true;
            
            // Filtre de recherche
            if (searchTerm && !userName.includes(searchTerm) && !userEmail.includes(searchTerm)) {
                show = false;
            }
            
            // Filtre de statut
            if (statusFilter && status !== statusFilter) {
                show = false;
            }
            
            // Filtre de permissions
            if (permissionFilter) {
                if (permissionFilter === 'both' && (!hasTutorial || !hasProject)) {
                    show = false;
                } else if (permissionFilter === 'tutorial' && (!hasTutorial || hasProject)) {
                    show = false;
                } else if (permissionFilter === 'project' && (!hasProject || hasTutorial)) {
                    show = false;
                } else if (permissionFilter === 'none' && (hasTutorial || hasProject)) {
                    show = false;
                }
            }
            
            row.style.display = show ? '' : 'none';
        });
    }

    function updateRowData(userId, permissionType, value) {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (row) {
            if (permissionType === 'tutorial' || permissionType === 'both') {
                row.dataset.tutorial = value ? '1' : '0';
            }
            if (permissionType === 'project' || permissionType === 'both') {
                row.dataset.project = value ? '1' : '0';
            }
        }
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; animation: slideIn 0.3s ease;';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
            ${message}
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
});
</script>

<style>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

