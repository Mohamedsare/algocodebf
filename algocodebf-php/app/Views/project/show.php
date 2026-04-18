<?php
$pageTitle = ($project['title'] ?? 'Projet') . ' - Projets - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="project-show-section">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-nav">
            <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= BASE_URL ?>/project/index">Projets</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= htmlspecialchars($project['title'] ?? 'Projet') ?></span>
        </div>

        <div class="project-layout">
            <!-- Main Content -->
            <div class="project-main">
                <!-- Header -->
                <article class="project-header">
                    <div class="project-status-top">
                        <span class="project-status status-<?= strtolower($project['status'] ?? 'active') ?>">
                            <?php
                            $statusLabels = [
                                'planning' => '📋 Planification',
                                'in-progress' => '🚀 En cours',
                                'completed' => '✅ Terminé',
                                'on-hold' => '⏸️ En pause'
                            ];
                            echo $statusLabels[$project['status']] ?? '📋 ' . $project['status'];
                            ?>
                        </span>
                        <?php if ($project['looking_for_members']): ?>
                            <span class="badge-recruiting">
                                <i class="fas fa-user-plus"></i> Recherche de membres
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="project-title"><?= htmlspecialchars($project['title'] ?? 'Projet sans titre') ?></h1>
                    
                    <p class="project-description"><?= htmlspecialchars($project['description'] ?? 'Aucune description') ?></p>

                    <!-- Owner Info -->
                    <div class="owner-section">
                        <div class="owner-info">
                            <?php
                            $ownerPhoto = !empty($project['owner_photo']) ? BASE_URL . '/' . $project['owner_photo'] : null;
                            $ownerInitial = strtoupper(substr($project['owner_prenom'] ?? 'U', 0, 1));
                            ?>
                            <div class="owner-avatar">
                                <?php if ($ownerPhoto && file_exists($project['owner_photo'])): ?>
                                    <img src="<?= $ownerPhoto ?>" alt="<?= htmlspecialchars($project['owner_prenom']) ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder"><?= $ownerInitial ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="owner-details">
                                <p class="owner-label">Créé par</p>
                                <p class="owner-name">
                                    <strong><?= htmlspecialchars($project['owner_prenom'] ?? 'Utilisateur') ?> <?= htmlspecialchars($project['owner_nom'] ?? '') ?></strong>
                                </p>
                                <p class="project-date">
                                    <i class="far fa-clock"></i> <?= timeAgo($project['created_at']) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Members Count -->
                        <div class="project-stats-quick">
                            <div class="stat-item-quick">
                                <i class="fas fa-users"></i>
                                <span><?= count($members) ?> membre<?= count($members) > 1 ? 's' : '' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="project-actions">
                        <?php if ($this->isLoggedIn()): ?>
                            <?php if ($is_owner): ?>
                                <a href="<?= BASE_URL ?>/project/edit/<?= $project['id'] ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                            <?php elseif (!$is_member && $project['looking_for_members']): ?>
                                <button class="btn-action btn-join" onclick="joinProject()">
                                    <i class="fas fa-user-plus"></i> Rejoindre le projet
                                </button>
                            <?php elseif ($is_member): ?>
                                <button class="btn-action btn-leave" onclick="leaveProject()">
                                    <i class="fas fa-sign-out-alt"></i> Quitter le projet
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <button class="btn-action" onclick="shareProject()">
                            <i class="fas fa-share-alt"></i> Partager
                        </button>
                    </div>
                </article>

                <!-- Technologies (extrait de la description si possible) -->
                <?php
                // Chercher les technologies mentionnées dans la description
                $techKeywords = ['PHP', 'JavaScript', 'React', 'Vue', 'Angular', 'Node', 'Python', 'Java', 'Flutter', 'Dart', 'MySQL', 'MongoDB', 'Firebase', 'Laravel', 'Django', 'Spring'];
                $foundTechs = [];
                $desc = $project['description'] ?? '';
                foreach ($techKeywords as $tech) {
                    if (stripos($desc, $tech) !== false) {
                        $foundTechs[] = $tech;
                    }
                }
                ?>
                <?php if (!empty($foundTechs)): ?>
                    <div class="project-technologies">
                        <h3><i class="fas fa-tools"></i> Technologies détectées</h3>
                        <div class="tech-tags">
                            <?php foreach ($foundTechs as $tech): ?>
                                <span class="tech-tag"><?= htmlspecialchars($tech) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Links -->
                <?php if (!empty($project['github_link']) || !empty($project['demo_link'])): ?>
                    <div class="project-links">
                        <h3><i class="fas fa-link"></i> Liens du projet</h3>
                        <div class="links-grid">
                            <?php if (!empty($project['github_link'])): ?>
                                <a href="<?= htmlspecialchars($project['github_link']) ?>" 
                                   class="link-card github" 
                                   target="_blank" 
                                   rel="noopener noreferrer">
                                    <i class="fab fa-github"></i>
                                    <div>
                                        <strong>GitHub Repository</strong>
                                        <small>Code source</small>
                                    </div>
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['demo_link'])): ?>
                                <a href="<?= htmlspecialchars($project['demo_link']) ?>" 
                                   class="link-card demo" 
                                   target="_blank" 
                                   rel="noopener noreferrer">
                                    <i class="fas fa-rocket"></i>
                                    <div>
                                        <strong>Démo en ligne</strong>
                                        <small>Voir le projet</small>
                                    </div>
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Members Section -->
                <div class="members-section">
                    <h3><i class="fas fa-users"></i> Membres du projet (<?= count($members) ?>)</h3>
                    <?php if (!empty($members)): ?>
                        <div class="members-grid">
                            <?php foreach ($members as $member): ?>
                                <div class="member-card">
                                    <div class="member-avatar-container">
                                        <?php if (!empty($member['photo_path'])): ?>
                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($member['photo_path']) ?>" 
                                                 alt="<?= htmlspecialchars($member['prenom']) ?>"
                                                 class="member-avatar">
                                        <?php else: ?>
                                            <div class="member-avatar-placeholder">
                                                <?= strtoupper(substr($member['prenom'] ?? 'U', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($member['user_id'] == $project['owner_id']): ?>
                                            <span class="owner-badge" title="Créateur du projet">👑</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="member-info">
                                        <h5>
                                            <a href="<?= BASE_URL ?>/user/profile/<?= $member['user_id'] ?>">
                                                <?= htmlspecialchars($member['prenom'] . ' ' . $member['nom']) ?>
                                            </a>
                                        </h5>
                                        <p class="member-role"><?= htmlspecialchars($member['role']) ?></p>
                                        <p class="member-joined">
                                            <i class="fas fa-calendar"></i> Rejoint <?= timeAgo($member['joined_at']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <p>Aucun membre pour le moment</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Comments Section -->
                <div id="comments" class="comments-section">
                    <h3><i class="fas fa-comments"></i> Commentaires (<span id="commentsCount">0</span>)</h3>
                    
                    <?php if ($this->isLoggedIn()): ?>
                        <form class="comment-form" id="commentForm">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="type" value="project">
                            <input type="hidden" name="resource_id" value="<?= $project['id'] ?>">
                            <textarea name="body" 
                                      id="commentBody"
                                      placeholder="Partagez votre avis, posez une question..." 
                                      rows="4"
                                      required></textarea>
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
                        <p class="loading-comments">
                            <i class="fas fa-spinner fa-spin"></i> Chargement des commentaires...
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="project-sidebar">
                <!-- Project Info Card -->
                <div class="sidebar-card info-card">
                    <h4><i class="fas fa-info-circle"></i> Informations</h4>
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-flag"></i> Statut</span>
                            <span class="info-value"><?= ucfirst($project['status'] ?? 'planning') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-calendar"></i> Créé</span>
                            <span class="info-value"><?= date('d/m/Y', strtotime($project['created_at'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-users"></i> Membres</span>
                            <span class="info-value"><?= $project['members_count'] ?? 0 ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-eye"></i> Visibilité</span>
                            <span class="info-value"><?= ucfirst($project['visibility'] ?? 'public') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Members Wanted (if recruiting) -->
                <?php if ($project['looking_for_members']): ?>
                    <div class="sidebar-card recruiting-card">
                        <h4><i class="fas fa-bullhorn"></i> On recrute !</h4>
                        <p>Ce projet recherche activement de nouveaux membres pour contribuer.</p>
                        <?php if (!$is_member && $this->isLoggedIn()): ?>
                            <button class="btn-join-full" onclick="joinProject()">
                                <i class="fas fa-user-plus"></i> Rejoindre le projet
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<style>
.project-show-section {
    padding: 30px 0 60px;
    background: #f8f9fa;
    min-height: calc(100vh - 140px);
}

/* Breadcrumb */
.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 30px;
    font-size: 0.9rem;
    color: #6c757d;
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
.project-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 30px;
}

/* Main Content */
.project-main {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

/* Header */
.project-header {
    background: white;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.project-status-top {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.project-status {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    color: white;
}

.status-planning {
    background: linear-gradient(135deg, #6c757d, #5a6268);
}

.status-in-progress {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.status-completed {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.status-on-hold {
    background: linear-gradient(135deg, #ffc107, #ff9800);
}

.badge-recruiting {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    color: white;
}

.project-title {
    font-size: 2.5rem;
    color: var(--dark-color);
    margin-bottom: 20px;
    line-height: 1.3;
}

.project-description {
    font-size: 1.2rem;
    color: #6c757d;
    line-height: 1.8;
    margin-bottom: 25px;
}

/* Owner Section */
.owner-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 25px;
    border-top: 2px solid #f0f0f0;
    margin-bottom: 20px;
}

.owner-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.owner-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--primary-color);
}

.owner-avatar img {
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

.owner-label {
    margin: 0 0 5px;
    font-size: 0.85rem;
    color: #6c757d;
}

.owner-name {
    margin: 0 0 5px;
    color: var(--dark-color);
}

.project-date {
    margin: 0;
    font-size: 0.9rem;
    color: #6c757d;
}

.project-stats-quick {
    display: flex;
    gap: 20px;
}

.stat-item-quick {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
    font-weight: 600;
}

.stat-item-quick i {
    color: var(--primary-color);
}

/* Actions */
.project-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 12px 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    color: var(--dark-color);
    text-decoration: none;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn-edit {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.btn-edit:hover {
    background: var(--primary-color);
    color: white;
}

.btn-join {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

.btn-join:hover {
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
}

.btn-leave {
    border-color: var(--danger-color);
    color: var(--danger-color);
}

.btn-leave:hover {
    background: var(--danger-color);
    color: white;
}

/* Technologies */
.project-technologies {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.project-technologies h3 {
    margin: 0 0 20px;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.tech-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.tech-tag {
    padding: 8px 16px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    color: var(--dark-color);
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.tech-tag:hover {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
}

/* Links */
.project-links {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.project-links h3 {
    margin: 0 0 20px;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.links-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.link-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    background: #f8f9fa;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.link-card.github {
    border-left-color: #24292e;
}

.link-card.demo {
    border-left-color: var(--secondary-color);
}

.link-card:hover {
    background: white;
    transform: translateX(5px);
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
}

.link-card i:first-child {
    font-size: 2rem;
}

.link-card.github i:first-child {
    color: #24292e;
}

.link-card.demo i:first-child {
    color: var(--secondary-color);
}

.link-card div {
    flex: 1;
}

.link-card strong {
    display: block;
    color: var(--dark-color);
    margin-bottom: 3px;
}

.link-card small {
    color: #6c757d;
}

.link-card i.fa-external-link-alt {
    color: #6c757d;
}

/* Members Section */
.members-section {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.members-section h3 {
    margin: 0 0 20px;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.members-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.member-card {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.member-card:hover {
    background: white;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.member-avatar-container {
    position: relative;
}

.member-avatar,
.member-avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
}

.member-avatar {
    object-fit: cover;
}

.member-avatar-placeholder {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
}

.owner-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    font-size: 1.2rem;
    background: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.member-info {
    flex: 1;
}

.member-info h5 {
    margin: 0 0 5px;
}

.member-info a {
    color: var(--dark-color);
    text-decoration: none;
    font-weight: 600;
}

.member-info a:hover {
    color: var(--primary-color);
}

.member-role {
    margin: 0 0 5px;
    color: #6c757d;
    font-size: 0.9rem;
}

.member-joined {
    margin: 0;
    font-size: 0.85rem;
    color: #6c757d;
}

/* Comments (reusing tutorial styles) */
.comments-section {
    background: white;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
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
.error-comments,
.loading-comments {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.error-comments {
    color: var(--danger-color);
}

/* Sidebar */
.project-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.sidebar-card h4 {
    margin: 0 0 20px;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    color: #6c757d;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-value {
    font-weight: 600;
    color: var(--dark-color);
}

.recruiting-card {
    background: linear-gradient(135deg, #fff5f5, #ffe6e6);
    border: 2px solid #ff6b6b;
}

.recruiting-card p {
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 15px;
}

.btn-join-full {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-join-full:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
}

/* Responsive */
@media (max-width: 992px) {
    .project-layout {
        grid-template-columns: 1fr;
    }
    
    .members-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .project-title {
        font-size: 1.8rem;
    }
    
    .owner-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
}
</style>

<script>
// ========================================
// GESTION DES MEMBRES
// ========================================

function joinProject() {
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
        z-index: 9999;
        animation: fadeIn 0.3s ease;
    `;
    
    modal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 20px; max-width: 500px; width: 90%; animation: slideUp 0.3s ease;">
            <h3 style="margin: 0 0 20px; color: var(--dark-color); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-user-plus" style="color: var(--primary-color);"></i>
                Rejoindre le projet
            </h3>
            <p style="color: #6c757d; margin-bottom: 20px; line-height: 1.6;">
                Votre demande sera envoyée au créateur du projet qui pourra l'accepter ou la refuser.
            </p>
            <form id="joinForm" style="display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; color: var(--dark-color); font-weight: 600;">
                        Rôle souhaité
                    </label>
                    <input type="text" name="role" id="roleInput" 
                           placeholder="Ex: Développeur Frontend, Designer, etc."
                           value="Contributeur"
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 1rem;"
                           required>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; color: var(--dark-color); font-weight: 600;">
                        Message de motivation (optionnel)
                    </label>
                    <textarea name="motivation" id="motivationInput" rows="4"
                              placeholder="Expliquez pourquoi vous voulez rejoindre ce projet..."
                              style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 1rem; resize: vertical;"></textarea>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" style="flex: 1; padding: 12px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-paper-plane"></i> Envoyer la demande
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
    document.getElementById('roleInput').focus();
    
    document.getElementById('joinForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const role = document.getElementById('roleInput').value.trim();
        const motivation = document.getElementById('motivationInput').value.trim();
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
        
        const formData = new FormData();
        formData.append('role', role);
        formData.append('motivation', motivation);
        formData.append('csrf_token', '<?= $csrf_token ?>');
        
        fetch('<?= BASE_URL ?>/project/requestJoin/<?= $project['id'] ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.remove();
                showMessage('✅ ' + data.message, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showMessage('❌ ' + (data.message || 'Erreur'), 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showMessage('❌ Erreur lors de l\'envoi', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
}

function leaveProject() {
    if (confirm('⚠️ Êtes-vous sûr de vouloir quitter ce projet ?')) {
        alert('Fonctionnalité à implémenter : Quitter le projet');
        // TODO: Implémenter la logique AJAX pour quitter le projet
    }
}

function shareProject() {
    const url = window.location.href;
    const title = '<?= addslashes($project["title"] ?? "Projet") ?>';
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            alert('✅ Lien copié dans le presse-papier !');
        });
    }
}

// ========================================
// GESTION DES COMMENTAIRES
// ========================================

function loadComments() {
    const commentsList = document.getElementById('commentsList');
    const projectId = <?= $project['id'] ?>;
    
    fetch(`<?= BASE_URL ?>/comment/getComments/project/${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comments.length > 0) {
                renderComments(data.comments);
                document.getElementById('commentsCount').textContent = data.total;
            } else {
                commentsList.innerHTML = '<p class="no-comments"><i class="fas fa-info-circle"></i> Aucun commentaire pour le moment.</p>';
                document.getElementById('commentsCount').textContent = '0';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            commentsList.innerHTML = '<p class="error-comments"><i class="fas fa-exclamation-triangle"></i> Erreur de chargement.</p>';
        });
}

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

document.getElementById('commentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    const textarea = document.getElementById('commentBody');
    
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
            textarea.value = '';
            loadComments();
            showMessage('✅ Commentaire ajouté !', 'success');
        } else {
            showMessage(data.message || 'Erreur', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showMessage('❌ Erreur lors de l\'ajout', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        textarea.disabled = false;
    });
});

function editComment(commentId, currentBody) {
    const newBody = prompt('Modifier le commentaire:', currentBody);
    if (!newBody || newBody.trim() === '' || newBody.trim() === currentBody) return;
    
    const formData = new FormData();
    formData.append('body', newBody.trim());
    
    fetch(`<?= BASE_URL ?>/comment/update/${commentId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`comment-body-${commentId}`).innerHTML = escapeHtml(newBody).replace(/\n/g, '<br>');
            showMessage('✅ Modifié !', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    });
}

function deleteComment(commentId) {
    if (!confirm('Supprimer ce commentaire ?')) return;
    
    fetch(`<?= BASE_URL ?>/comment/delete/${commentId}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`comment-${commentId}`).remove();
            const count = parseInt(document.getElementById('commentsCount').textContent);
            document.getElementById('commentsCount').textContent = Math.max(0, count - 1);
            showMessage('🗑️ Supprimé !', 'success');
        }
    });
}

function escapeHtml(text) {
    const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showMessage(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.style.cssText = `position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 15px 20px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.3);`;
    alertDiv.style.background = type === 'success' ? '#d4edda' : '#f8d7da';
    alertDiv.style.color = type === 'success' ? '#155724' : '#721c24';
    alertDiv.style.border = type === 'success' ? '2px solid #c3e6cb' : '2px solid #f5c6cb';
    alertDiv.textContent = message;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}

// Charger les commentaires
loadComments();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

