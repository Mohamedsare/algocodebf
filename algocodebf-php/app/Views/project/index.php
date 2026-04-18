<?php
$pageTitle = 'Projets Collaboratifs - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Hero Section -->
<section class="projects-hero">
    <div class="container">
        <div class="hero-content">
            <h1><i class="fas fa-project-diagram"></i> Projets Collaboratifs</h1>
            <p>Rejoignez des projets innovants ou lancez le vôtre et collaborez avec la communauté</p>
            <?php 
            if (isset($_SESSION['user_id'])):
                $userModel = new User();
                if ($userModel->canCreateProject($_SESSION['user_id'])):
            ?>
                <a href="<?= BASE_URL ?>/project/create" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Créer un Projet
                </a>
            <?php 
                endif;
            endif; 
            ?>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="projects-stats">
    <div class="container">
        <div class="stats-grid-4">
            <div class="stat-item">
                <i class="fas fa-folder-open"></i>
                <h3><?= $stats['total_projects'] ?? 0 ?></h3>
                <p>Projets Actifs</p>
            </div>
            <div class="stat-item">
                <i class="fas fa-users"></i>
                <h3><?= $stats['total_members'] ?? 0 ?></h3>
                <p>Collaborateurs</p>
            </div>
            <div class="stat-item">
                <i class="fas fa-check-circle"></i>
                <h3><?= $stats['completed'] ?? 0 ?></h3>
                <p>Projets Terminés</p>
            </div>
            <div class="stat-item">
                <i class="fas fa-code-branch"></i>
                <h3><?= $stats['technologies'] ?? 0 ?></h3>
                <p>Technologies Utilisées</p>
            </div>
        </div>
    </div>
</section>

<!-- Projects Content -->
<section class="projects-content">
    <div class="container">
        <!-- Filters -->
        <div class="projects-filters">
            <button class="filter-btn active" data-status="all">
                <i class="fas fa-globe"></i> Tous
            </button>
            <button class="filter-btn" data-status="recruiting">
                <i class="fas fa-user-plus"></i> Recrutent
            </button>
            <button class="filter-btn" data-status="in_progress">
                <i class="fas fa-spinner"></i> En cours
            </button>
            <button class="filter-btn" data-status="completed">
                <i class="fas fa-check"></i> Terminés
            </button>
        </div>

        <!-- Projects Grid -->
        <div class="projects-grid">
            <?php if (empty($projects)): ?>
                <div class="empty-projects">
                    <i class="fas fa-project-diagram"></i>
                    <h3>Aucun projet disponible</h3>
                    <p>Lancez le premier projet collaboratif !</p>
                    <?php 
                    if (isset($_SESSION['user_id'])):
                        $userModel = new User();
                        if ($userModel->canCreateProject($_SESSION['user_id'])):
                    ?>
                        <a href="<?= BASE_URL ?>/project/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Créer un Projet
                        </a>
                    <?php 
                        endif;
                    endif; 
                    ?>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="project-card" data-status="<?= htmlspecialchars($project['status']) ?>">
                        <!-- Ribbon si recherche de membres -->
                        <?php if ($project['looking_for_members']): ?>
                            <div class="recruiting-ribbon">
                                <i class="fas fa-bullhorn"></i> On recrute !
                            </div>
                        <?php endif; ?>

                        <div class="project-card-header">
                            <div class="project-badges">
                                <span class="project-status status-<?= strtolower($project['status'] ?? 'planning') ?>">
                                    <?php
                                    $statusIcons = [
                                        'planning' => '📋',
                                        'in_progress' => '🚀',
                                        'in-progress' => '🚀',
                                        'completed' => '✅',
                                        'on-hold' => '⏸️'
                                    ];
                                    $statusLabels = [
                                        'planning' => 'Planification',
                                        'in_progress' => 'En cours',
                                        'in-progress' => 'En cours',
                                        'completed' => 'Terminé',
                                        'on-hold' => 'En pause'
                                    ];
                                    echo ($statusIcons[$project['status']] ?? '📋') . ' ';
                                    echo $statusLabels[$project['status']] ?? $project['status'];
                                    ?>
                                </span>
                                <span class="project-visibility">
                                    <i class="fas fa-<?= $project['visibility'] === 'public' ? 'globe' : 'lock' ?>"></i>
                                </span>
                            </div>
                        </div>

                        <div class="project-card-body">
                            <h3 class="project-card-title">
                                <a href="<?= BASE_URL ?>/project/show/<?= $project['id'] ?>">
                                    <?= htmlspecialchars($project['title']) ?>
                                </a>
                            </h3>
                            
                            <p class="project-card-description">
                                <?= htmlspecialchars(substr($project['description'] ?? '', 0, 120)) ?>...
                            </p>

                            <!-- Technologies détectées automatiquement -->
                            <div class="project-tech-stack">
                                <?php
                                $techKeywords = ['PHP', 'JavaScript', 'React', 'Vue', 'Angular', 'Node', 'Python', 'Java', 'Flutter', 'Dart', 'MySQL', 'PostgreSQL', 'MongoDB', 'Firebase', 'Laravel', 'Django', 'Spring', 'Swift', 'Kotlin'];
                                $foundTechs = [];
                                $desc = $project['description'] ?? '';
                                foreach ($techKeywords as $tech) {
                                    if (stripos($desc, $tech) !== false) {
                                        $foundTechs[] = $tech;
                                    }
                                }
                                
                                if (!empty($foundTechs)):
                                    $displayTechs = array_slice($foundTechs, 0, 4);
                                    foreach ($displayTechs as $tech):
                                ?>
                                    <span class="tech-tag-modern"><?= htmlspecialchars($tech) ?></span>
                                <?php 
                                    endforeach;
                                    if (count($foundTechs) > 4):
                                ?>
                                    <span class="tech-tag-modern more">+<?= count($foundTechs) - 4 ?></span>
                                <?php 
                                    endif;
                                endif;
                                ?>
                            </div>
                        </div>

                        <!-- Owner Info -->
                        <div class="project-card-owner">
                            <div class="owner-info-compact">
                                <?php
                                $ownerInitial = strtoupper(substr($project['owner_prenom'] ?? 'U', 0, 1));
                                ?>
                                <div class="owner-avatar-small">
                                    <?php if (!empty($project['owner_photo'])): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($project['owner_photo']) ?>" 
                                             alt="<?= htmlspecialchars($project['owner_prenom']) ?>">
                                    <?php else: ?>
                                        <div class="owner-avatar-placeholder-small"><?= $ownerInitial ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="owner-text">
                                    <span class="owner-label">Créé par</span>
                                    <strong><?= htmlspecialchars($project['owner_prenom'] ?? 'Utilisateur') ?> <?= htmlspecialchars($project['owner_nom'] ?? '') ?></strong>
                                </div>
                            </div>
                            <div class="project-date-compact">
                                <i class="far fa-clock"></i> <?= timeAgo($project['created_at']) ?>
                            </div>
                        </div>

                        <div class="project-card-footer">
                            <div class="project-stats-compact">
                                <span title="Membres" class="stat-badge">
                                    <i class="fas fa-users"></i> <?= $project['members_count'] ?? 0 ?>
                                </span>
                                <?php if (!empty($project['github_link'])): ?>
                                    <span title="Code sur GitHub" class="stat-badge github-badge">
                                        <i class="fab fa-github"></i>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($project['demo_link'])): ?>
                                    <span title="Démo disponible" class="stat-badge demo-badge">
                                        <i class="fas fa-rocket"></i>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <a href="<?= BASE_URL ?>/project/show/<?= $project['id'] ?>" class="btn-view-project">
                                <span>Découvrir</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.projects-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.projects-hero::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
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

.projects-stats {
    padding: 60px 0;
    background: white;
}

.stats-grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.stat-item {
    text-align: center;
    padding: 30px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    border-radius: 15px;
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.stat-item i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.stat-item h3 {
    font-size: 2.5rem;
    margin: 0;
    color: var(--dark-color);
}

.stat-item p {
    margin: 10px 0 0;
    color: #6c757d;
}

.projects-content {
    padding: 60px 0 80px;
    background: #f8f9fa;
}

.projects-filters {
    display: flex;
    gap: 15px;
    margin-bottom: 40px;
    flex-wrap: wrap;
    justify-content: center;
}

.filter-btn {
    padding: 12px 25px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 25px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.filter-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.filter-btn.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 30px;
}

.project-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: visible;
    border: 2px solid transparent;
}

.project-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    border-color: var(--primary-color);
}

/* Recruiting Ribbon */
.recruiting-ribbon {
    position: absolute;
    top: 20px;
    right: -35px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    color: white;
    padding: 8px 40px;
    font-size: 0.85rem;
    font-weight: 700;
    transform: rotate(45deg);
    box-shadow: 0 3px 10px rgba(255, 107, 107, 0.4);
    z-index: 10;
    text-align: center;
}

.project-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.project-status {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-recruiting {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-in_progress {
    background: #e3f2fd;
    color: #1976d2;
}

.status-completed {
    background: #f3e5f5;
    color: #7b1fa2;
}

.menu-btn {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    color: #6c757d;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.menu-btn:hover {
    background: #f8f9fa;
}

.project-body {
    flex: 1;
}

.project-title {
    font-size: 1.3rem;
    margin-bottom: 15px;
}

.project-title a {
    color: var(--dark-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.project-title a:hover {
    color: var(--primary-color);
}

.project-description {
    color: #6c757d;
    line-height: 1.7;
    margin-bottom: 20px;
}

.project-tech-stack {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.tech-tag {
    padding: 5px 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--dark-color);
}

.tech-tag.more {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

.project-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.project-team {
    display: flex;
    align-items: center;
    gap: 10px;
}

.team-avatars {
    display: flex;
}

.team-avatars img {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: 2px solid white;
    margin-left: -10px;
    transition: all 0.3s ease;
}

.team-avatars img:first-child {
    margin-left: 0;
}

.team-avatars img:hover {
    transform: scale(1.2);
    z-index: 10;
}

.more-members {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    margin-left: -10px;
    border: 2px solid white;
}

.team-count {
    font-size: 0.9rem;
    color: #6c757d;
}

.btn-join {
    padding: 10px 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-join:hover {
    transform: translateX(3px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.empty-projects {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
}

.empty-projects i {
    font-size: 5rem;
    color: #e9ecef;
    margin-bottom: 20px;
}

/* Nouveau CSS pour les cartes améliorées */
.project-card-header {
    padding: 20px 25px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.project-badges {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-planning {
    background: linear-gradient(135deg, #6c757d, #5a6268);
}

.status-in-progress,
.status-in_progress {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.status-completed {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.status-on-hold,
.status-on_hold {
    background: linear-gradient(135deg, #ffc107, #ff9800);
}

.project-visibility {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 0.9rem;
}

.project-card-body {
    padding: 25px;
    flex: 1;
}

.project-card-title {
    margin: 0 0 15px;
    font-size: 1.4rem;
    line-height: 1.4;
}

.project-card-title a {
    color: var(--dark-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.project-card-title a:hover {
    color: var(--primary-color);
}

.project-card-description {
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.tech-tag-modern {
    padding: 5px 12px;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    color: var(--primary-color);
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid #90caf9;
    transition: all 0.3s ease;
}

.tech-tag-modern:hover {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
    transform: translateY(-2px) scale(1.05);
}

.tech-tag-modern.more {
    background: linear-gradient(135deg, #fce4ec, #f8bbd0);
    color: #e91e63;
    border-color: #f48fb1;
}

.project-card-owner {
    padding: 15px 25px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
}

.owner-info-compact {
    display: flex;
    align-items: center;
    gap: 12px;
}

.owner-avatar-small {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--primary-color);
    flex-shrink: 0;
}

.owner-avatar-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.owner-avatar-placeholder-small {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    font-weight: bold;
}

.owner-text {
    display: flex;
    flex-direction: column;
}

.owner-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 2px;
}

.owner-text strong {
    color: var(--dark-color);
    font-size: 0.9rem;
}

.project-date-compact {
    font-size: 0.85rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 5px;
}

.project-card-footer {
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.project-stats-compact {
    display: flex;
    gap: 10px;
}

.stat-badge {
    padding: 6px 12px;
    background: #f8f9fa;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.stat-badge:hover {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    transform: scale(1.1);
}

.stat-badge i {
    color: var(--primary-color);
}

.stat-badge:hover i {
    color: white;
}

.github-badge {
    background: #24292e !important;
    color: white !important;
}

.github-badge i {
    color: white !important;
}

.demo-badge {
    background: linear-gradient(135deg, var(--secondary-color), #20c997) !important;
    color: white !important;
}

.demo-badge i {
    color: white !important;
}

.btn-view-project {
    padding: 10px 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
}

.btn-view-project:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
}

.btn-view-project i {
    transition: transform 0.3s ease;
}

.btn-view-project:hover i {
    transform: translateX(5px);
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .stats-grid-4 {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .stat-item {
        padding: 20px 16px;
    }
    
    .stat-item i {
        font-size: 2rem;
        margin-bottom: 10px;
    }
    
    .stat-item h3 {
        font-size: 1.8rem;
    }
    
    .stat-item p {
        font-size: 0.85rem;
    }
    
    .projects-grid {
        grid-template-columns: 1fr;
    }
    
    .recruiting-ribbon {
        font-size: 0.7rem;
        padding: 6px 35px;
    }
}
</style>

<script>
// Filter projects
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const status = this.dataset.status;
        document.querySelectorAll('.project-card').forEach(card => {
            if (status === 'all' || card.dataset.status === status) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

