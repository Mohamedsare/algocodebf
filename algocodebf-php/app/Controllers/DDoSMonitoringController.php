<?php
/**
 * Contrôleur pour le monitoring DDoS
 * Gestion de l'accès au dashboard de surveillance DDoS
 */

// Inclure la classe Controller de base
require_once __DIR__ . '/../Core/Controller.php';

class DDoSMonitoringController extends Controller
{
    /**
     * Afficher le dashboard de monitoring DDoS
     */
    public function index()
    {
        // Vérifier les permissions admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Accès refusé - Permissions administrateur requises';
            $this->redirect('auth/login');
        }
        
        // Inclure directement le dashboard complet
        $this->showCompleteMonitoring();
    }
    
    /**
     * Afficher le dashboard complet de monitoring DDoS
     */
    private function showCompleteMonitoring()
    {
        try {
            // Inclure les fichiers de configuration nécessaires
            require_once __DIR__ . '/../../config/ddos_config.php';
            require_once __DIR__ . '/../../app/Helpers/DDoSProtection.php';
            $ddosProtection = new DDoSProtection();
            $db = Database::getInstance();
            
            // Récupérer les statistiques générales
            $stats = $ddosProtection->getStats();
            
            // Récupérer les IPs actuellement bloquées
            $blockedIps = $db->query("
                SELECT ip_address, blocked_until, suspicious_score, 
                       request_count_minute, request_count_hour, request_count_day,
                       TIMESTAMPDIFF(SECOND, NOW(), blocked_until) as remaining_seconds
                FROM ddos_protection 
                WHERE blocked_until > NOW() 
                ORDER BY blocked_until ASC 
                LIMIT 50
            ");
            
            // Récupérer les IPs les plus suspectes
            $suspiciousIps = $db->query("
                SELECT ip_address, suspicious_score, request_count_minute, 
                       request_count_hour, request_count_day, updated_at
                FROM ddos_protection 
                WHERE suspicious_score > 0 
                ORDER BY suspicious_score DESC, updated_at DESC 
                LIMIT 20
            ");
            
            // Récupérer l'activité récente (dernières 24h)
            $recentActivity = $db->query("
                SELECT DATE(created_at) as date, 
                       COUNT(*) as total_ips,
                       COUNT(CASE WHEN blocked_until IS NOT NULL THEN 1 END) as blocked_count,
                       AVG(suspicious_score) as avg_suspicious_score
                FROM ddos_protection 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC
                LIMIT 7
            ");
            
            // Récupérer les statistiques par heure (dernières 24h)
            $hourlyStats = $db->query("
                SELECT HOUR(updated_at) as hour,
                       COUNT(*) as requests,
                       COUNT(CASE WHEN blocked_until IS NOT NULL THEN 1 END) as blocks
                FROM ddos_protection 
                WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY HOUR(updated_at)
                ORDER BY hour ASC
            ");
            
        } catch (Exception $e) {
            $error = "Erreur lors du chargement des données: " . $e->getMessage();
            $stats = ['total_ips' => 0, 'blocked_ips' => 0, 'suspicious_ips' => 0];
            $blockedIps = [];
            $suspiciousIps = [];
            $recentActivity = [];
            $hourlyStats = [];
        }
        
        $pageTitle = 'Monitoring DDoS - AlgoCodeBF';
        require_once __DIR__ . '/../Views/layouts/header.php';
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
                    </a>
                    <a href="<?= BASE_URL ?>/admin/dashboard#forum" class="nav-item-ultra">
                        <i class="fas fa-comments"></i>
                        <span>Forum</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/dashboard#tutorials" class="nav-item-ultra">
                        <i class="fas fa-book-open"></i>
                        <span>Tutoriels</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/dashboard#projects" class="nav-item-ultra">
                        <i class="fas fa-project-diagram"></i>
                        <span>Projets</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/dashboard#opportunities" class="nav-item-ultra">
                        <i class="fas fa-briefcase"></i>
                        <span>Opportunités</span>
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
                    </a>
                    <a href="<?= BASE_URL ?>/admin/dashboard#newsletter" class="nav-item-ultra">
                        <i class="fas fa-envelope-open-text"></i>
                        <span>Newsletter</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/dashboard#statistics" class="nav-item-ultra">
                        <i class="fas fa-chart-bar"></i>
                        <span>Statistiques</span>
                    </a>
                    <a href="<?= BASE_URL ?>/ddosmonitoring" class="nav-item-ultra active">
                        <i class="fas fa-shield-halved"></i>
                        <span>Protection DDoS</span>
                        <?php 
                        try {
                            if (isset($stats['blocked_ips']) && $stats['blocked_ips'] > 0): ?>
                                <span class="alert-badge"><?= $stats['blocked_ips'] ?></span>
                            <?php endif;
                        } catch (Exception $e) {
                            // En cas d'erreur, ne pas afficher de badge
                        }
                        ?>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/permissions" class="nav-item-ultra">
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
            
            <!-- Main Content -->
            <main class="admin-content-ultra">
                <!-- Top Bar -->
                <div class="admin-topbar">
                    <div class="topbar-left">
                        <h1><i class="fas fa-shield-halved"></i> Monitoring DDoS Protection</h1>
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
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Alertes -->
                        <?php 
                        $alertThreshold = defined('DDOS_ALERT_THRESHOLD') ? DDOS_ALERT_THRESHOLD : 100;
                        if ($stats['blocked_ips'] > $alertThreshold): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>ALERTE DDoS !</strong> <?= $stats['blocked_ips'] ?> IPs actuellement bloquées (seuil: <?= $alertThreshold ?>)
                            </div>
                        <?php elseif ($stats['blocked_ips'] > 0): ?>
                            <div class="alert">
                                <i class="fas fa-info-circle"></i>
                                <strong>Attention :</strong> <?= $stats['blocked_ips'] ?> IPs actuellement bloquées
                            </div>
                        <?php endif; ?>
                        
                        <!-- Actions -->
                        <div class="actions" style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                            <button class="btn btn-warning" onclick="exportLogs()">
                                <i class="fas fa-download"></i> Exporter Logs
                            </button>
                            <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour Dashboard
                            </a>
                        </div>
                        
                        <!-- Statistiques principales -->
                        <div class="stats-grid-admin" style="margin-bottom: 35px;">
                            <div class="stat-card-admin card-users">
                                <div class="stat-icon-admin">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="stat-data">
                                    <h3><?= number_format($stats['total_ips'] ?? 0) ?></h3>
                                    <p>IPs Total</p>
                                    <span class="stat-trend positive">
                                        <i class="fas fa-network-wired"></i> Toutes IPs suivies
                                    </span>
                                </div>
                            </div>
                            
                            <div class="stat-card-admin card-reports">
                                <div class="stat-icon-admin">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="stat-data">
                                    <h3><?= number_format($stats['blocked_ips'] ?? 0) ?></h3>
                                    <p>IPs Bloquées</p>
                                    <span class="stat-trend <?= ($stats['blocked_ips'] ?? 0) > 0 ? 'negative' : 'positive' ?>">
                                        <i class="fas fa-<?= ($stats['blocked_ips'] ?? 0) > 0 ? 'exclamation-triangle' : 'check-circle' ?>"></i> 
                                        <?= ($stats['blocked_ips'] ?? 0) > 0 ? 'En cours' : 'Aucune' ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="stat-card-admin card-tutorials">
                                <div class="stat-icon-admin">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stat-data">
                                    <h3><?= number_format($stats['suspicious_ips'] ?? 0) ?></h3>
                                    <p>IPs Suspectes</p>
                                    <span class="stat-trend <?= ($stats['suspicious_ips'] ?? 0) > 0 ? 'negative' : 'positive' ?>">
                                        <i class="fas fa-<?= ($stats['suspicious_ips'] ?? 0) > 0 ? 'eye' : 'shield-alt' ?>"></i> 
                                        Surveillance active
                                    </span>
                                </div>
                            </div>
                            
                            <div class="stat-card-admin card-posts">
                                <div class="stat-icon-admin">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="stat-data">
                                    <h3><?= number_format($stats['avg_suspicious_score'] ?? 0, 1) ?></h3>
                                    <p>Score Moyen</p>
                                    <span class="stat-trend positive">
                                        <i class="fas fa-balance-scale"></i> Risque évalué
                                    </span>
                                </div>
                            </div>
                        </div>
                    
                        <!-- IPs Bloquées -->
                        <div class="dashboard-section" style="background: white; padding: 30px; border-radius: 18px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); margin-bottom: 30px;">
                            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; color: var(--dark-color);">
                                <i class="fas fa-ban" style="color: #dc3545;"></i> IPs Actuellement Bloquées
                                <span style="margin-left: auto; font-size: 0.9rem; color: #6c757d; font-weight: 500;">
                                    <?= count($blockedIps) ?> IP(s)
                                </span>
                            </h2>
                            <div class="ip-list" style="max-height: 500px; overflow-y: auto;">
                                <?php if (empty($blockedIps)): ?>
                                    <div style="text-align: center; padding: 40px 20px; background: #f8f9fa; border-radius: 12px;">
                                        <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745; margin-bottom: 15px;"></i>
                                        <p style="font-size: 1.1rem; color: #6c757d; font-weight: 600;">Aucune IP bloquée actuellement</p>
                                        <p style="font-size: 0.9rem; color: #adb5bd; margin-top: 5px;">Le système fonctionne normalement</p>
                                    </div>
                                <?php else: ?>
                                    <div style="display: grid; gap: 15px;">
                                        <?php foreach ($blockedIps as $ip): ?>
                                            <div class="ip-item" style="background: #fff; border: 1px solid #e9ecef; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                                <div style="flex: 1;">
                                                    <div class="ip-address" style="font-family: 'Courier New', monospace; font-weight: 700; font-size: 1.1rem; color: var(--dark-color); margin-bottom: 8px;">
                                                        <?= htmlspecialchars($ip['ip_address']) ?>
                                                    </div>
                                                    <div style="display: flex; gap: 20px; font-size: 0.9rem; color: #6c757d; flex-wrap: wrap;">
                                                        <span><i class="fas fa-chart-line"></i> Score: <strong><?= $ip['suspicious_score'] ?></strong></span>
                                                        <span><i class="fas fa-clock"></i> Déblocage: <strong><?= date('H:i', strtotime($ip['blocked_until'])) ?></strong></span>
                                                        <span><i class="fas fa-hashtag"></i> Requêtes/min: <strong><?= $ip['request_count_minute'] ?? 0 ?></strong></span>
                                                    </div>
                                                </div>
                                                <div style="margin-left: 20px;">
                                                    <span class="ip-status status-blocked" style="padding: 10px 18px; border-radius: 25px; font-size: 0.95rem; font-weight: 700; background: linear-gradient(135deg, #dc3545, #c82333); color: white; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);">
                                                        <i class="fas fa-hourglass-half"></i> <?= gmdate('H:i:s', max(0, $ip['remaining_seconds'])) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- IPs Suspectes -->
                        <?php if (!empty($suspiciousIps)): ?>
                        <div class="dashboard-section" style="background: white; padding: 30px; border-radius: 18px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); margin-bottom: 30px;">
                            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; color: var(--dark-color);">
                                <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i> IPs Suspectes
                                <span style="margin-left: auto; font-size: 0.9rem; color: #6c757d; font-weight: 500;">
                                    <?= count($suspiciousIps) ?> IP(s)
                                </span>
                            </h2>
                            <div class="ip-list" style="max-height: 400px; overflow-y: auto;">
                                <div style="display: grid; gap: 15px;">
                                    <?php foreach ($suspiciousIps as $ip): ?>
                                        <div class="ip-item" style="background: #fff; border: 1px solid #ffeaa7; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(255, 193, 7, 0.1);">
                                            <div style="flex: 1;">
                                                <div class="ip-address" style="font-family: 'Courier New', monospace; font-weight: 700; font-size: 1.1rem; color: var(--dark-color); margin-bottom: 8px;">
                                                    <?= htmlspecialchars($ip['ip_address']) ?>
                                                </div>
                                                <div style="display: flex; gap: 20px; font-size: 0.9rem; color: #6c757d; flex-wrap: wrap;">
                                                    <span><i class="fas fa-chart-line"></i> Score: <strong style="color: #ff9800;"><?= $ip['suspicious_score'] ?></strong></span>
                                                    <span><i class="fas fa-hashtag"></i> Requêtes/min: <strong><?= $ip['request_count_minute'] ?? 0 ?></strong></span>
                                                    <span><i class="fas fa-calendar"></i> Dernière activité: <strong><?= date('d/m H:i', strtotime($ip['updated_at'])) ?></strong></span>
                                                </div>
                                            </div>
                                            <div style="margin-left: 20px;">
                                                <span class="ip-status status-suspicious" style="padding: 10px 18px; border-radius: 25px; font-size: 0.95rem; font-weight: 700; background: linear-gradient(135deg, #ffc107, #ff9800); color: white; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);">
                                                    <i class="fas fa-eye"></i> Surveillée
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
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

        .stat-trend.negative {
            color: #dc3545;
        }

        .stat-trend.warning {
            color: #ffc107;
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

            .stats-grid-admin {
                grid-template-columns: 1fr;
            }
        }

        /* ================================
           STYLES SPÉCIFIQUES DDoS
           ================================ */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        
        .alert {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 18px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.1);
        }
        
        .alert-danger {
            background: #f8d7da;
            border-color: #dc3545;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.1);
        }
        
        .alert i {
            font-size: 1.3rem;
        }
        
        .ip-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Scrollbar personnalisée */
        .ip-list::-webkit-scrollbar {
            width: 8px;
        }
        
        .ip-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .ip-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .ip-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        </style>
        
        <script>
        function exportLogs(event) {
            if (event) {
                event.preventDefault();
            }
            
            // Trouver le bouton
            const btn = event ? event.target.closest('.btn-warning') : document.querySelector('.btn-warning');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Export en cours...';
                btn.disabled = true;
                
                // Restaurer le bouton après un délai
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 3000);
            }
            
            // Rediriger vers l'URL d'export
            window.location.href = '<?= BASE_URL ?>/ddosmonitoring/exportLogs';
        }
        </script>
        
        <?php
        require_once __DIR__ . '/../Views/layouts/admin_footer.php';
    }
    
    /**
     * Exporter les logs DDoS en CSV
     */
    public function exportLogs()
    {
        // Vérifier les permissions admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            die('Accès refusé - Permissions administrateur requises');
        }
        
        try {
            require_once __DIR__ . '/../../app/Helpers/DDoSProtection.php';
            $db = Database::getInstance();
            
            // Récupérer tous les logs DDoS
            $logs = $db->query("
                SELECT 
                    ip_address,
                    request_count_minute,
                    request_count_hour,
                    request_count_day,
                    last_request_minute,
                    last_request_hour,
                    last_request_day,
                    blocked_until,
                    suspicious_score,
                    created_at,
                    updated_at,
                    CASE 
                        WHEN blocked_until > NOW() THEN 'Bloquée'
                        WHEN blocked_until IS NOT NULL AND blocked_until <= NOW() THEN 'Débloquée'
                        WHEN suspicious_score > 0 THEN 'Suspecte'
                        ELSE 'Normale'
                    END as status
                FROM ddos_protection 
                ORDER BY updated_at DESC
            ");
            
            // Nom du fichier avec date
            $filename = 'ddos_logs_' . date('Y-m-d_H-i-s') . '.csv';
            
            // Headers pour le téléchargement CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Ouvrir le flux de sortie
            $output = fopen('php://output', 'w');
            
            // Ajouter BOM UTF-8 pour Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes CSV
            fputcsv($output, [
                'Adresse IP',
                'Requêtes/Minute',
                'Requêtes/Heure',
                'Requêtes/Jour',
                'Dernière Requête (Minute)',
                'Dernière Requête (Heure)',
                'Dernière Requête (Jour)',
                'Bloquée Jusqu\'à',
                'Score Suspect',
                'Statut',
                'Date de Création',
                'Dernière Mise à Jour'
            ], ';');
            
            // Écrire les données
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['ip_address'],
                    $log['request_count_minute'] ?? 0,
                    $log['request_count_hour'] ?? 0,
                    $log['request_count_day'] ?? 0,
                    $log['last_request_minute'] ?? 'N/A',
                    $log['last_request_hour'] ?? 'N/A',
                    $log['last_request_day'] ?? 'N/A',
                    $log['blocked_until'] ?? 'N/A',
                    $log['suspicious_score'] ?? 0,
                    $log['status'],
                    $log['created_at'],
                    $log['updated_at']
                ], ';');
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            http_response_code(500);
            die('Erreur lors de l\'export: ' . htmlspecialchars($e->getMessage()));
        }
    }
    
    /**
     * Afficher une version simplifiée du monitoring si le fichier principal n'existe pas
     */
    private function showFallbackMonitoring()
    {
        try {
            require_once __DIR__ . '/../../app/Helpers/DDoSProtection.php';
            $ddosProtection = new DDoSProtection();
            $stats = $ddosProtection->getStats();
        } catch (Exception $e) {
            $stats = [
                'total_ips' => 0,
                'blocked_ips' => 0,
                'suspicious_ips' => 0,
                'avg_suspicious_score' => 0
            ];
        }
        
        $pageTitle = 'Monitoring DDoS - AlgoCodeBF';
        require_once __DIR__ . '/../Views/layouts/header.php';
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
                        <i class="fas fa-arrow-left"></i>
                        <span>Retour Dashboard</span>
                    </a>
                </nav>
            </aside>
            
            <!-- Main Content -->
            <main class="admin-content-ultra">
                <div class="admin-section-content">
                    <div class="section-header">
                        <h1><i class="fas fa-shield-halved"></i> Monitoring DDoS Protection</h1>
                        <p>Surveillance en temps réel de la protection contre les attaques DDoS</p>
                    </div>
                    
                    <!-- Statistiques principales -->
                    <div class="stats-grid-ultra" style="margin-bottom: 30px;">
                        <div class="stat-card-ultra burkina-green">
                            <div class="stat-icon-ultra">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="stat-content-ultra">
                                <div class="stat-main">
                                    <h3><?= $stats['total_ips'] ?? 0 ?></h3>
                                    <span class="stat-label">IPs Total</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card-ultra burkina-red">
                            <div class="stat-icon-ultra">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div class="stat-content-ultra">
                                <div class="stat-main">
                                    <h3><?= $stats['blocked_ips'] ?? 0 ?></h3>
                                    <span class="stat-label">IPs Bloquées</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card-ultra burkina-white">
                            <div class="stat-icon-ultra">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-content-ultra">
                                <div class="stat-main">
                                    <h3><?= $stats['suspicious_ips'] ?? 0 ?></h3>
                                    <span class="stat-label">IPs Suspectes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Message d'information -->
                    <div class="alert" style="background: rgba(255, 193, 7, 0.2); border: 2px solid #FFC107; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Information :</strong> Le dashboard complet de monitoring DDoS est en cours de développement. 
                        Les statistiques de base sont affichées ci-dessus.
                    </div>
                    
                    <!-- Actions -->
                    <div class="actions" style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <button class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour Dashboard
                        </a>
                    </div>
                </div>
            </main>
        </div>
        
        <style>
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #424242, #616161);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        </style>
        
        <?php
        require_once __DIR__ . '/../Views/layouts/admin_footer.php';
    }
}
