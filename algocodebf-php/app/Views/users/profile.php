<?php 
$pageTitle = htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . ' - Profil AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<section class="profile-section">
    <div class="container">
        <!-- Cover & Profile Header -->
        <div class="profile-header-wrapper">
            <div class="profile-cover">
                <div class="cover-pattern"></div>
                <?php if ($is_own_profile): ?>
                    <button class="btn-edit-cover" title="Changer la couverture">
                        <i class="fas fa-camera"></i>
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="profile-header-content">
                <div class="profile-avatar-wrapper">
                    <div class="profile-avatar">
                        <?php if (!empty($user['photo_path'])): ?>
                            <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($user['photo_path']) ?>" 
                                 alt="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder-profile">
                                <?= strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($user['is_online'] ?? false): ?>
                            <span class="online-indicator-profile"></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($is_own_profile): ?>
                        <button class="btn-edit-avatar" title="Changer la photo">
                            <i class="fas fa-camera"></i>
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="profile-info-main">
                    <h1 class="profile-name">
                        <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                        <?php if (!empty($badges)): ?>
                            <span class="verified-badge" title="Membre vérifié">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        <?php endif; ?>
                    </h1>
                    <p class="profile-role"><?= htmlspecialchars($user['faculty'] ?? 'Membre') ?></p>
                    
                    <div class="profile-meta-info">
                        <?php if (!empty($user['university'])): ?>
                        <span class="meta-item">
                            <i class="fas fa-university"></i>
                            <?= htmlspecialchars($user['university']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($user['city'])): ?>
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($user['city']) ?>
                        </span>
                        <?php endif; ?>
                        <span class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            Membre depuis <?= date('M Y', strtotime($user['created_at'])) ?>
                        </span>
                    </div>
                </div>
                
                <div class="profile-actions-wrapper">
                    <?php if ($is_own_profile): ?>
                        <a href="<?= BASE_URL ?>/user/edit" class="btn-action btn-primary-action">
                            <i class="fas fa-edit"></i>
                            <span>Modifier</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/message/compose/<?= $user['id'] ?>" class="btn-action btn-primary-action">
                            <i class="fas fa-envelope"></i>
                            <span>Message</span>
                        </a>
                        <button class="btn-action btn-secondary-action" id="followBtn" onclick="toggleFollow(<?= $user['id'] ?>)">
                            <i class="fas <?= $is_following ? 'fa-user-minus' : 'fa-user-plus' ?>"></i>
                            <span id="followText"><?= $is_following ? 'Ne plus suivre' : 'Suivre' ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid-profile">
            <div class="stat-card-profile stat-posts">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $user['posts_count'] ?? 0 ?></div>
                    <div class="stat-label">Discussions</div>
                </div>
            </div>
            
            <div class="stat-card-profile stat-tutorials">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $user['tutorials_count'] ?? 0 ?></div>
                    <div class="stat-label">Tutoriels</div>
                </div>
            </div>
            
            <div class="stat-card-profile stat-likes">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $user['likes_received'] ?? 0 ?></div>
                    <div class="stat-label">J'aime Reçus</div>
                </div>
            </div>
            
            <div class="stat-card-profile stat-reputation">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $user['reputation'] ?? 0 ?></div>
                    <div class="stat-label">Réputation</div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="profile-content-grid">
            <!-- Sidebar -->
            <aside class="profile-sidebar-modern">
                <!-- About -->
                <div class="sidebar-card">
                    <div class="card-header-modern">
                        <i class="fas fa-user-circle"></i>
                        <h3>À propos</h3>
                    </div>
                    <div class="card-body-modern">
                        <?php if (!empty($user['bio'])): ?>
                            <p class="bio-text"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted">Aucune bio disponible</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Skills -->
                <?php if (!empty($skills)): ?>
                <div class="sidebar-card">
                    <div class="card-header-modern">
                        <i class="fas fa-code"></i>
                        <h3>Compétences</h3>
                    </div>
                    <div class="card-body-modern">
                        <div class="skills-grid-modern">
                            <?php foreach ($skills as $skill): ?>
                                <div class="skill-badge-modern">
                                    <span class="skill-name-modern"><?= htmlspecialchars($skill['name']) ?></span>
                                    <span class="skill-level-modern level-<?= strtolower($skill['level']) ?>">
                                        <?= htmlspecialchars($skill['level']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Badges -->
                <?php if (!empty($badges)): ?>
                <div class="sidebar-card">
                    <div class="card-header-modern">
                        <i class="fas fa-certificate"></i>
                        <h3>Badges</h3>
                    </div>
                    <div class="card-body-modern">
                        <div class="badges-grid-modern">
                            <?php foreach ($badges as $badge): ?>
                                <div class="badge-card-modern" title="<?= htmlspecialchars($badge['description']) ?>">
                                    <div class="badge-icon-large"><?= $badge['icon'] ?? '🏆' ?></div>
                                    <div class="badge-name-modern"><?= htmlspecialchars($badge['name']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- CV Download -->
                <?php if (!empty($user['cv_path'])): ?>
                <div class="sidebar-card cv-card">
                    <div class="card-header-modern">
                        <i class="fas fa-file-pdf"></i>
                        <h3>Curriculum Vitae</h3>
                    </div>
                    <div class="card-body-modern">
                        <a href="<?= BASE_URL ?>/public/<?= htmlspecialchars($user['cv_path']) ?>" 
                           target="_blank" 
                           class="btn-download-cv">
                            <i class="fas fa-download"></i>
                            <span>Télécharger le CV</span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Contact Info -->
                <div class="sidebar-card">
                    <div class="card-header-modern">
                        <i class="fas fa-address-card"></i>
                        <h3>Contact</h3>
                    </div>
                    <div class="card-body-modern">
                        <div class="contact-info-list">
                            <?php if (!empty($user['email'])): ?>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($user['phone'])): ?>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span><?= htmlspecialchars($user['phone']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="profile-main-modern">
                <!-- Activity Tabs -->
                <div class="activity-tabs">
                    <button class="tab-btn active" data-tab="posts">
                        <i class="fas fa-comments"></i> Discussions
                    </button>
                    <button class="tab-btn" data-tab="tutorials">
                        <i class="fas fa-book"></i> Tutoriels
                    </button>
                    <button class="tab-btn" data-tab="projects">
                        <i class="fas fa-project-diagram"></i> Projets
                    </button>
                </div>

                <!-- Posts Tab -->
                <div class="tab-content active" id="posts-tab">
                    <?php if (!empty($posts)): ?>
                        <div class="activity-list">
                            <?php foreach ($posts as $post): ?>
                                <div class="activity-card">
                                    <div class="activity-icon">
                                        <i class="fas fa-comment-dots"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h4 class="activity-title">
                                            <a href="<?= BASE_URL ?>/forum/show/<?= $post['id'] ?>">
                                                <?= htmlspecialchars($post['title']) ?>
                                            </a>
                                        </h4>
                                        <p class="activity-excerpt">
                                            <?= htmlspecialchars(substr($post['body'], 0, 120)) ?>...
                                        </p>
                                        <div class="activity-meta">
                                            <span class="category-badge"><?= htmlspecialchars($post['category']) ?></span>
                                            <span><i class="fas fa-clock"></i> <?= timeAgo($post['created_at']) ?></span>
                                            <span><i class="fas fa-comments"></i> <?= $post['comments_count'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($is_owner): ?>
                                        <div class="activity-actions">
                                            <a href="<?= BASE_URL ?>/forum/edit/<?= $post['id'] ?>" 
                                               class="btn-action-edit" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deletePost(<?= $post['id'] ?>, '<?= htmlspecialchars(addslashes($post['title'])) ?>')" 
                                                    class="btn-action-delete" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-activity">
                            <i class="fas fa-comments"></i>
                            <h3>Aucune discussion</h3>
                            <p>Cet utilisateur n'a pas encore publié de discussion</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tutorials Tab -->
                <div class="tab-content" id="tutorials-tab">
                    <?php if (!empty($tutorials)): ?>
                        <div class="activity-list">
                            <?php foreach ($tutorials as $tutorial): ?>
                                <div class="activity-card">
                                    <div class="activity-icon tutorial-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h4 class="activity-title">
                                            <a href="<?= BASE_URL ?>/tutorial/show/<?= $tutorial['id'] ?>">
                                                <?= htmlspecialchars($tutorial['title']) ?>
                                            </a>
                                        </h4>
                                        <p class="activity-excerpt">
                                            <?= htmlspecialchars(substr($tutorial['description'] ?? '', 0, 120)) ?>...
                                        </p>
                                        <div class="activity-meta">
                                            <span><i class="fas fa-eye"></i> <?= $tutorial['views'] ?? 0 ?> vues</span>
                                            <span><i class="fas fa-heart"></i> <?= $tutorial['likes_count'] ?? 0 ?> likes</span>
                                            <span><i class="fas fa-download"></i> <?= $tutorial['downloads'] ?? 0 ?> téléch.</span>
                                            <span><i class="fas fa-clock"></i> <?= timeAgo($tutorial['created_at']) ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($is_owner): ?>
                                        <div class="activity-actions">
                                            <a href="<?= BASE_URL ?>/tutorial/edit/<?= $tutorial['id'] ?>" 
                                               class="btn-action-edit" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteTutorial(<?= $tutorial['id'] ?>, '<?= htmlspecialchars(addslashes($tutorial['title'])) ?>')" 
                                                    class="btn-action-delete" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-activity">
                            <i class="fas fa-book"></i>
                            <h3>Aucun tutoriel</h3>
                            <p>Cet utilisateur n'a pas encore publié de tutoriel</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Projects Tab -->
                <div class="tab-content" id="projects-tab">
                    <?php if (!empty($projects)): ?>
                        <div class="activity-list">
                            <?php foreach ($projects as $project): ?>
                                <div class="activity-card">
                                    <div class="activity-icon project-icon">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h4 class="activity-title">
                                            <a href="<?= BASE_URL ?>/project/show/<?= $project['id'] ?>">
                                                <?= htmlspecialchars($project['title']) ?>
                                            </a>
                                        </h4>
                                        <p class="activity-excerpt">
                                            <?= htmlspecialchars(substr($project['description'], 0, 120)) ?>...
                                        </p>
                                        <div class="activity-meta">
                                            <span class="status-badge-profile status-<?= $project['status'] ?>">
                                                <?= ucfirst($project['status']) ?>
                                            </span>
                                            <span><i class="fas fa-users"></i> <?= $project['members_count'] ?? 0 ?> membres</span>
                                            <span><i class="fas fa-clock"></i> <?= timeAgo($project['created_at']) ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($is_owner && $project['owner_id'] == $_SESSION['user_id']): ?>
                                        <div class="activity-actions">
                                            <a href="<?= BASE_URL ?>/project/edit/<?= $project['id'] ?>" 
                                               class="btn-action-edit" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteProject(<?= $project['id'] ?>, '<?= htmlspecialchars(addslashes($project['title'])) ?>')" 
                                                    class="btn-action-delete" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-activity">
                            <i class="fas fa-project-diagram"></i>
                            <h3>Aucun projet</h3>
                            <p>Cet utilisateur n'a pas encore de projet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</section>

<style>
.profile-section {
    padding-bottom: 80px;
    background: #f8f9fa;
}

/* Profile Header */
.profile-header-wrapper {
    background: white;
    border-radius: 0 0 20px 20px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    overflow: hidden;
}

.profile-cover {
    height: 300px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    position: relative;
    overflow: hidden;
}

.cover-pattern {
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.5;
}

.btn-edit-cover {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    transition: all 0.3s ease;
    z-index: 10;
}

.btn-edit-cover:hover {
    background: white;
    transform: scale(1.1);
}

.profile-header-content {
    padding: 0 40px 40px;
    display: flex;
    gap: 30px;
    align-items: flex-end;
}

.profile-avatar-wrapper {
    position: relative;
    margin-top: -80px;
}

.profile-avatar {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    border: 6px solid white;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    background: white;
    position: relative;
}

.profile-avatar img,
.avatar-placeholder-profile {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-profile {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.5rem;
    font-weight: 700;
}

.online-indicator-profile {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 25px;
    height: 25px;
    background: var(--secondary-color);
    border: 4px solid white;
    border-radius: 50%;
    animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.btn-edit-avatar {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: var(--primary-color);
    border: 3px solid white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s ease;
}

.btn-edit-avatar:hover {
    transform: scale(1.15);
    background: var(--secondary-color);
}

.profile-info-main {
    flex: 1;
    padding-bottom: 10px;
}

.profile-name {
    font-size: 2.2rem;
    margin: 0 0 5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.verified-badge {
    color: var(--primary-color);
    font-size: 1.5rem;
}

.profile-role {
    font-size: 1.1rem;
    color: #6c757d;
    margin: 0 0 15px;
    font-weight: 500;
}

.profile-meta-info {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
    font-size: 0.95rem;
    color: #6c757d;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.meta-item i {
    color: var(--primary-color);
}

.profile-actions-wrapper {
    display: flex;
    gap: 15px;
    padding-bottom: 10px;
}

.btn-action {
    padding: 12px 25px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary-action {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.btn-primary-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
}

.btn-secondary-action {
    background: white;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.btn-secondary-action:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-3px);
}

/* Stats Grid */
.stats-grid-profile {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card-profile {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card-profile::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.stat-card-profile:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon-wrapper {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(46, 204, 113, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--primary-color);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-color);
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Content Grid */
.profile-content-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 30px;
}

/* Sidebar */
.profile-sidebar-modern {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.sidebar-card:hover {
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
}

.card-header-modern {
    padding: 20px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-header-modern i {
    font-size: 1.3rem;
    color: var(--primary-color);
}

.card-header-modern h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.card-body-modern {
    padding: 20px;
}

.bio-text {
    line-height: 1.7;
    color: var(--dark-color);
}

.skills-grid-modern {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.skill-badge-modern {
    padding: 8px 15px;
    background: #f8f9fa;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.skill-badge-modern:hover {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    transform: translateY(-2px);
}

.skill-name-modern {
    font-weight: 600;
}

.skill-level-modern {
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
}

.level-debutant { background: #e8f5e9; color: #2e7d32; }
.level-intermediaire { background: #fff3e0; color: #e65100; }
.level-avance { background: #ffebee; color: #c62828; }

.badges-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 15px;
}

.badge-card-modern {
    padding: 15px;
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.badge-card-modern:hover {
    transform: translateY(-5px) rotate(3deg);
    box-shadow: 0 10px 25px rgba(255, 215, 0, 0.4);
}

.badge-icon-large {
    font-size: 2.5rem;
    margin-bottom: 8px;
}

.badge-name-modern {
    font-size: 0.8rem;
    font-weight: 600;
    color: #856404;
}

.btn-download-cv {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-download-cv:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(231, 76, 60, 0.4);
}

.contact-info-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
    font-size: 0.9rem;
}

.contact-item i {
    color: var(--primary-color);
    width: 20px;
}

/* Main Content */
.profile-main-modern {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.activity-tabs {
    background: white;
    padding: 8px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    display: flex;
    gap: 8px;
}

.tab-btn {
    flex: 1;
    padding: 12px 20px;
    background: none;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    color: #6c757d;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.tab-btn:hover {
    background: rgba(52, 152, 219, 0.1);
    color: var(--primary-color);
}

.tab-btn.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.activity-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    display: flex;
    gap: 20px;
    transition: all 0.3s ease;
    position: relative;
}

.activity-card:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
}

/* Activity Actions */
.activity-actions {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    margin-left: auto;
}

.btn-action-edit,
.btn-action-delete {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2px solid #e9ecef;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 0.95rem;
}

.btn-action-edit {
    color: var(--primary-color);
}

.btn-action-edit:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: scale(1.1);
}

.btn-action-delete {
    color: var(--danger-color);
}

.btn-action-delete:hover {
    background: var(--danger-color);
    color: white;
    border-color: var(--danger-color);
    transform: scale(1.1);
}

.activity-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.tutorial-icon {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.project-icon {
    background: linear-gradient(135deg, #f093fb, #f5576c);
}

.activity-content {
    flex: 1;
}

.activity-title {
    margin: 0 0 10px;
    font-size: 1.2rem;
}

.activity-title a {
    color: var(--dark-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.activity-title a:hover {
    color: var(--primary-color);
}

.activity-excerpt {
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 15px;
}

.activity-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: #6c757d;
}

.activity-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.category-badge {
    padding: 4px 12px;
    background: var(--primary-color);
    color: white;
    border-radius: 12px;
    font-weight: 600;
}

.status-badge-profile {
    padding: 4px 12px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.8rem;
}

.status-recruiting { background: #e8f5e9; color: #2e7d32; }
.status-in_progress { background: #e3f2fd; color: #1976d2; }
.status-completed { background: #f3e5f5; color: #7b1fa2; }

.empty-state-activity {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
}

.empty-state-activity i {
    font-size: 5rem;
    color: #e9ecef;
    margin-bottom: 20px;
}

.empty-state-activity h3 {
    margin-bottom: 10px;
    color: var(--dark-color);
}

.empty-state-activity p {
    color: #6c757d;
}

/* Responsive */
@media (max-width: 1200px) {
    .profile-content-grid {
        grid-template-columns: 300px 1fr;
    }
}

@media (max-width: 992px) {
    .profile-content-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-sidebar-modern {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .profile-header-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 0 20px 30px;
    }
    
    .profile-avatar-wrapper {
        margin-top: -60px;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
    }
    
    .profile-name {
        font-size: 1.8rem;
        justify-content: center;
    }
    
    .profile-meta-info {
        justify-content: center;
    }
    
    .profile-actions-wrapper {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-action {
        justify-content: center;
    }
    
    .stats-grid-profile {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .profile-sidebar-modern {
        grid-template-columns: 1fr;
    }
    
    .activity-tabs {
        flex-direction: column;
    }
    
    .activity-card {
        flex-direction: column;
    }
}
</style>

<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active from all
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // Add active to clicked
        this.classList.add('active');
        const tabId = this.dataset.tab + '-tab';
        document.getElementById(tabId).classList.add('active');
    });
});

// Delete post function
function deletePost(postId, postTitle) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la discussion "${postTitle}" ?\n\nCette action est irréversible et supprimera également tous les commentaires associés.`)) {
        // Create a form to submit the delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/forum/delete/' + postId;
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrfInput);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}

// Supprimer un tutoriel
function deleteTutorial(tutorialId, tutorialTitle) {
    if (confirm(`⚠️ Êtes-vous sûr de vouloir supprimer le tutoriel "${tutorialTitle}" ?\n\nCette action est irréversible et supprimera :\n• Le tutoriel complet\n• Tous les commentaires associés\n• Tous les likes\n• Le fichier uploadé`)) {
        // Animation de suppression
        const tutorialCard = event.target.closest('.activity-card');
        if (tutorialCard) {
            tutorialCard.style.transition = 'all 0.5s ease';
            tutorialCard.style.opacity = '0.3';
            tutorialCard.style.transform = 'translateX(-20px)';
            tutorialCard.style.background = '#ffe6e6';
        }
        
        // Créer le formulaire de suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/tutorial/delete/' + tutorialId;
        
        // Ajouter le token CSRF
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrfInput);
        
        // Soumettre
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteProject(projectId, projectTitle) {
    if (confirm(`⚠️ Êtes-vous sûr de vouloir supprimer le projet "${projectTitle}" ?\n\nCette action est irréversible et supprimera :\n• Le projet complet\n• Tous les membres seront retirés\n• Toutes les informations associées`)) {
        // Animation de suppression
        const projectCard = event.target.closest('.activity-card');
        if (projectCard) {
            projectCard.style.transition = 'all 0.5s ease';
            projectCard.style.opacity = '0.3';
            projectCard.style.transform = 'translateX(-20px)';
            projectCard.style.background = '#ffe6e6';
        }
        
        // Créer le formulaire de suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/project/delete/' + projectId;
        
        // Ajouter le token CSRF
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrfInput);
        
        // Soumettre
        document.body.appendChild(form);
        form.submit();
    }
}

// Toggle follow/unfollow
function toggleFollow(userId) {
    const followBtn = document.getElementById('followBtn');
    const followText = document.getElementById('followText');
    const followIcon = followBtn.querySelector('i');
    
    // Désactiver le bouton pendant la requête
    followBtn.disabled = true;
    followBtn.style.opacity = '0.6';
    
    // Animation de chargement
    const originalText = followText.textContent;
    followText.textContent = 'Chargement...';
    
    fetch(`<?= BASE_URL ?>/user/follow/${userId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'follow') {
                // Mettre à jour l'interface pour "suivi"
                followIcon.className = 'fas fa-user-minus';
                followText.textContent = 'Ne plus suivre';
                followBtn.classList.remove('btn-secondary-action');
                followBtn.classList.add('btn-primary-action');
                
                // Afficher un message de succès
                showNotification('✅ Vous suivez maintenant cet utilisateur', 'success');
            } else {
                // Mettre à jour l'interface pour "non suivi"
                followIcon.className = 'fas fa-user-plus';
                followText.textContent = 'Suivre';
                followBtn.classList.remove('btn-primary-action');
                followBtn.classList.add('btn-secondary-action');
                
                // Afficher un message de succès
                showNotification('✅ Vous ne suivez plus cet utilisateur', 'success');
            }
        } else {
            // Afficher un message d'erreur
            showNotification('❌ ' + (data.message || 'Erreur lors de l\'opération'), 'error');
            followText.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('❌ Erreur de connexion', 'error');
        followText.textContent = originalText;
    })
    .finally(() => {
        // Réactiver le bouton
        followBtn.disabled = false;
        followBtn.style.opacity = '1';
    });
}

// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        max-width: 300px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    // Couleurs selon le type
    if (type === 'success') {
        notification.style.background = 'linear-gradient(135deg, #27ae60, #2ecc71)';
    } else if (type === 'error') {
        notification.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
    } else {
        notification.style.background = 'linear-gradient(135deg, #3498db, #2980b9)';
    }
    
    notification.textContent = message;
    
    // Ajouter au DOM
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Supprimer après 4 secondes
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}
</script>

<?php 
require_once __DIR__ . '/../layouts/footer.php'; 
?>
