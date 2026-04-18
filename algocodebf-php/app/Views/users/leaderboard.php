<?php
$pageTitle = 'Classement - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

$period = $_GET['period'] ?? 'month';
?>

<!-- Hero Section -->
<section class="leaderboard-hero">
    <div class="container">
        <div class="hero-content">
            <h1><i class="fas fa-trophy"></i> Classement de la Communauté</h1>
            <p>Les membres les plus actifs et contributeurs de AlgoCodeBF</p>
        </div>
    </div>
</section>

<!-- Period Selector -->
<section class="leaderboard-content">
    <div class="container">
        <div class="period-selector">
            <a href="?period=week" class="period-btn <?= $period === 'week' ? 'active' : '' ?>">
                <i class="fas fa-calendar-week"></i> Cette Semaine
            </a>
            <a href="?period=month" class="period-btn <?= $period === 'month' ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i> Ce Mois
            </a>
            <a href="?period=all" class="period-btn <?= $period === 'all' ? 'active' : '' ?>">
                <i class="fas fa-calendar"></i> Tout Temps
            </a>
        </div>

        <!-- Top 3 Podium -->
        <?php if (!empty($leaderboard) && count($leaderboard) >= 3): ?>
        <div class="podium-section">
            <!-- 2nd Place -->
            <div class="podium-card rank-2">
                <div class="podium-rank">
                    <i class="fas fa-medal"></i>
                    <span>2</span>
                </div>
                <div class="podium-avatar">
                    <?php if (!empty($leaderboard[1]['photo_path'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($leaderboard[1]['photo_path']) ?>" 
                             alt="<?= htmlspecialchars($leaderboard[1]['prenom'] . ' ' . $leaderboard[1]['nom']) ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder-podium">
                            <?= strtoupper(substr($leaderboard[1]['prenom'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($leaderboard[1]['prenom'] . ' ' . $leaderboard[1]['nom']) ?></h3>
                <p class="podium-university"><?= !empty($leaderboard[1]['university']) ? htmlspecialchars($leaderboard[1]['university']) : 'Non spécifiée' ?></p>
                <div class="podium-score">
                    <i class="fas fa-star"></i>
                    <?= number_format($leaderboard[1]['score'] ?? 0) ?> pts
                </div>
                <a href="<?= BASE_URL ?>/user/profile/<?= $leaderboard[1]['id'] ?>" class="btn-view-podium">
                    Voir le profil
                </a>
            </div>

            <!-- 1st Place -->
            <div class="podium-card rank-1">
                <div class="crown-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="podium-rank winner">
                    <i class="fas fa-trophy"></i>
                    <span>1</span>
                </div>
                <div class="podium-avatar winner-avatar">
                    <?php if (!empty($leaderboard[0]['photo_path'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($leaderboard[0]['photo_path']) ?>" 
                             alt="<?= htmlspecialchars($leaderboard[0]['prenom'] . ' ' . $leaderboard[0]['nom']) ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder-podium">
                            <?= strtoupper(substr($leaderboard[0]['prenom'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($leaderboard[0]['prenom'] . ' ' . $leaderboard[0]['nom']) ?></h3>
                <p class="podium-university"><?= !empty($leaderboard[0]['university']) ? htmlspecialchars($leaderboard[0]['university']) : 'Non spécifiée' ?></p>
                <div class="podium-score winner-score">
                    <i class="fas fa-star"></i>
                    <?= number_format($leaderboard[0]['score'] ?? 0) ?> pts
                </div>
                <a href="<?= BASE_URL ?>/user/profile/<?= $leaderboard[0]['id'] ?>" class="btn-view-podium">
                    Voir le profil
                </a>
            </div>

            <!-- 3rd Place -->
            <div class="podium-card rank-3">
                <div class="podium-rank">
                    <i class="fas fa-medal"></i>
                    <span>3</span>
                </div>
                <div class="podium-avatar">
                    <?php if (!empty($leaderboard[2]['photo_path'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($leaderboard[2]['photo_path']) ?>" 
                             alt="<?= htmlspecialchars($leaderboard[2]['prenom'] . ' ' . $leaderboard[2]['nom']) ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder-podium">
                            <?= strtoupper(substr($leaderboard[2]['prenom'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($leaderboard[2]['prenom'] . ' ' . $leaderboard[2]['nom']) ?></h3>
                <p class="podium-university"><?= !empty($leaderboard[2]['university']) ? htmlspecialchars($leaderboard[2]['university']) : 'Non spécifiée' ?></p>
                <div class="podium-score">
                    <i class="fas fa-star"></i>
                    <?= number_format($leaderboard[2]['score'] ?? 0) ?> pts
                </div>
                <a href="<?= BASE_URL ?>/user/profile/<?= $leaderboard[2]['id'] ?>" class="btn-view-podium">
                    Voir le profil
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Full Leaderboard Table -->
        <div class="leaderboard-table-card">
            <div class="table-header">
                <h2><i class="fas fa-list-ol"></i> Classement Complet</h2>
                <div class="table-actions">
                    <button class="btn-export">
                        <i class="fas fa-download"></i> Exporter
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th class="rank-col">Rang</th>
                            <th>Membre</th>
                            <th class="center-col">Posts</th>
                            <th class="center-col">Tutoriels</th>
                            <th class="center-col">Commentaires</th>
                            <th class="center-col">J'aime</th>
                            <th class="center-col">Score Total</th>
                            <th class="action-col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($leaderboard)): ?>
                            <?php foreach ($leaderboard as $index => $user): ?>
                                <tr class="leaderboard-row <?= isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id'] ? 'current-user' : '' ?>">
                                    <td class="rank-cell">
                                        <div class="rank-badge rank-<?= $index + 1 ?>">
                                            <?php if ($index < 3): ?>
                                                <i class="fas fa-<?= $index === 0 ? 'trophy' : 'medal' ?>"></i>
                                            <?php endif; ?>
                                            <span><?= $index + 1 ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-cell-leaderboard">
                                            <div class="user-avatar-small">
                                                <?php if (!empty($user['photo_path'])): ?>
                                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['photo_path']) ?>" 
                                                         alt="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder-small">
                                                        <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="user-info-small">
                                                <a href="<?= BASE_URL ?>/user/profile/<?= $user['id'] ?>" class="user-name-link">
                                                    <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                                                </a>
                                                <?php if (!empty($user['city'])): ?>
                                                <span class="user-location">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?= htmlspecialchars($user['city']) ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center-col">
                                        <span class="stat-value"><?= $user['posts_count'] ?? 0 ?></span>
                                    </td>
                                    <td class="center-col">
                                        <span class="stat-value"><?= $user['tutorials_count'] ?? 0 ?></span>
                                    </td>
                                    <td class="center-col">
                                        <span class="stat-value"><?= $user['comments_count'] ?? 0 ?></span>
                                    </td>
                                    <td class="center-col">
                                        <span class="stat-value"><?= $user['likes_received'] ?? 0 ?></span>
                                    </td>
                                    <td class="center-col">
                                        <strong class="total-score"><?= number_format($user['score'] ?? 0) ?></strong>
                                    </td>
                                    <td class="action-col">
                                        <a href="<?= BASE_URL ?>/user/profile/<?= $user['id'] ?>" class="btn-view-small">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="no-data">
                                        <i class="fas fa-trophy"></i>
                                        <p>Aucune donnée de classement disponible</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- How Points Work -->
        <div class="points-info-card">
            <h3><i class="fas fa-info-circle"></i> Comment fonctionnent les points ?</h3>
            <div class="points-grid">
                <div class="point-item">
                    <div class="point-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div class="point-info">
                        <strong>5 points</strong>
                        <span>Par post publié</span>
                    </div>
                </div>
                <div class="point-item">
                    <div class="point-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="point-info">
                        <strong>10 points</strong>
                        <span>Par tutoriel publié</span>
                    </div>
                </div>
                <div class="point-item">
                    <div class="point-icon">
                        <i class="fas fa-reply"></i>
                    </div>
                    <div class="point-info">
                        <strong>2 points</strong>
                        <span>Par commentaire</span>
                    </div>
                </div>
                <div class="point-item">
                    <div class="point-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="point-info">
                        <strong>1 point</strong>
                        <span>Par j'aime reçu</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.leaderboard-hero {
    background: linear-gradient(135deg, #C8102E 0%, #006A4E 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.leaderboard-hero::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.4;
}

.hero-content {
    position: relative;
    z-index: 1;
}

.hero-content h1 {
    font-size: 3rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.leaderboard-content {
    padding: 60px 0 80px;
    background: #f8f9fa;
}

/* Period Selector */
.period-selector {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 50px;
    flex-wrap: wrap;
}

.period-btn {
    padding: 15px 30px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 30px;
    text-decoration: none;
    color: var(--dark-color);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.period-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    transform: translateY(-3px);
}

.period-btn.active {
    background: linear-gradient(135deg, #C8102E 0%, #006A4E 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 8px 25px rgba(200, 16, 46, 0.3);
}

/* Podium */
.podium-section {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-bottom: 60px;
    align-items: flex-end;
}

.podium-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
}

.podium-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
}

.rank-1 {
    order: 2;
    padding-top: 50px;
    background: linear-gradient(135deg, rgba(255, 209, 0, 0.1), rgba(255, 237, 78, 0.1));
    border: 3px solid #FFD100;
}

.rank-2 {
    order: 1;
    background: linear-gradient(135deg, rgba(200, 16, 46, 0.08), rgba(0, 106, 78, 0.08));
    border: 3px solid #E74C3C;
}

.rank-3 {
    order: 3;
    background: linear-gradient(135deg, rgba(0, 106, 78, 0.1), rgba(200, 16, 46, 0.1));
    border: 3px solid #006A4E;
}

.crown-icon {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 3rem;
    color: #FFD100;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50% { transform: translateX(-50%) translateY(-10px); }
}

.podium-rank {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #C8102E 0%, #006A4E 100%);
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
}

.podium-rank.winner {
    width: 80px;
    height: 80px;
    font-size: 2rem;
    background: linear-gradient(135deg, #FFD100, #FFE55C);
    color: #B8860B;
    box-shadow: 0 8px 30px rgba(255, 209, 0, 0.5);
}

.podium-avatar {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
}

.podium-avatar.winner-avatar {
    width: 130px;
    height: 130px;
}

.podium-avatar img,
.avatar-placeholder-podium {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    object-fit: cover;
}

.avatar-placeholder-podium {
    background: linear-gradient(135deg, #C8102E 0%, #006A4E 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
}

.podium-card h3 {
    font-size: 1.3rem;
    margin-bottom: 5px;
    color: var(--dark-color);
}

.podium-university {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.podium-score {
    padding: 12px 20px;
    background: linear-gradient(135deg, rgba(200, 16, 46, 0.1), rgba(0, 106, 78, 0.1));
    border-radius: 25px;
    font-size: 1.2rem;
    font-weight: 700;
    color: #C8102E;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
}

.winner-score {
    background: linear-gradient(135deg, rgba(255, 209, 0, 0.2), rgba(255, 237, 78, 0.2));
    color: #B8860B;
    font-size: 1.5rem;
}

.btn-view-podium {
    padding: 10px 25px;
    background: linear-gradient(135deg, #C8102E 0%, #006A4E 100%);
    color: white;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-view-podium:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(200, 16, 46, 0.3);
}

/* Table */
.leaderboard-table-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    margin-bottom: 40px;
}

.table-header {
    padding: 25px 30px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h2 {
    margin: 0;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn-export {
    padding: 10px 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-export:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.table-responsive {
    overflow-x: auto;
}

.leaderboard-table {
    width: 100%;
    border-collapse: collapse;
}

.leaderboard-table thead {
    background: #f8f9fa;
}

.leaderboard-table th {
    padding: 15px 20px;
    text-align: left;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.leaderboard-table td {
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.center-col {
    text-align: center;
}

.leaderboard-row {
    transition: all 0.3s ease;
}

.leaderboard-row:hover {
    background: rgba(52, 152, 219, 0.03);
}

.leaderboard-row.current-user {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.08), rgba(46, 204, 113, 0.08));
    border-left: 4px solid var(--primary-color);
}

.rank-cell {
    width: 80px;
}

.rank-badge {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    background: linear-gradient(135deg, #C8102E 0%, #006A4E 100%);
}

.rank-badge.rank-1 {
    background: linear-gradient(135deg, #FFD100, #FFE55C);
    color: #B8860B;
    font-size: 1.2rem;
    box-shadow: 0 4px 15px rgba(255, 209, 0, 0.4);
}

.rank-badge.rank-2 {
    background: linear-gradient(135deg, #E74C3C, #C8102E);
    color: #fff;
}

.rank-badge.rank-3 {
    background: linear-gradient(135deg, #006A4E, #28A745);
    color: #fff;
}

.user-cell-leaderboard {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar-small img,
.avatar-placeholder-small {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder-small {
    background: linear-gradient(135deg, #C8102E 0%, #006A4E 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.user-info-small {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.user-name-link {
    font-weight: 600;
    color: var(--dark-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.user-name-link:hover {
    color: var(--primary-color);
}

.user-location {
    font-size: 0.85rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 5px;
}

.stat-value {
    font-weight: 600;
    color: var(--dark-color);
}

.total-score {
    font-size: 1.2rem;
    color: #C8102E;
}

.btn-view-small {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    background: rgba(200, 16, 46, 0.1);
    color: #C8102E;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-view-small:hover {
    background: #C8102E;
    color: white;
    transform: scale(1.1);
}

/* Points Info */
.points-info-card {
    background: white;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
}

.points-info-card h3 {
    margin: 0 0 25px;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.points-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.point-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(200, 16, 46, 0.05), rgba(0, 106, 78, 0.05));
    border-radius: 12px;
    transition: all 0.3s ease;
}

.point-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.point-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, #C8102E 0%, #006A4E 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.point-info {
    display: flex;
    flex-direction: column;
}

.point-info strong {
    font-size: 1.2rem;
    color: var(--dark-color);
    margin-bottom: 3px;
}

.point-info span {
    font-size: 0.9rem;
    color: #6c757d;
}

.no-data {
    padding: 60px 20px;
    text-align: center;
}

.no-data i {
    font-size: 4rem;
    color: #e9ecef;
    margin-bottom: 15px;
}

/* Responsive */
@media (max-width: 992px) {
    .podium-section {
        grid-template-columns: 1fr;
    }
    
    .rank-1,
    .rank-2,
    .rank-3 {
        order: initial;
    }
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .table-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .points-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Export functionality
document.querySelector('.btn-export')?.addEventListener('click', function() {
    alert('Fonctionnalité d\'export en cours de développement');
    // Future: export to CSV or PDF
});

// Highlight current user
window.addEventListener('load', () => {
    const currentUserRow = document.querySelector('.current-user');
    if (currentUserRow) {
        currentUserRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        currentUserRow.style.animation = 'highlight 2s ease';
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>


