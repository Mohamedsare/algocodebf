<?php
/**
 * Dashboard de Monitoring DDoS en Temps Réel
 * Interface web pour surveiller la protection DDoS
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/ddos_config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Helpers/DDoSProtection.php';

// Vérifier les permissions admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Accès refusé - Admin requis');
}

try {
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
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring DDoS - HubTech</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #1B5E20, #2E7D32);
        color: white;
        min-height: 100vh;
    }

    .header {
        background: rgba(0, 0, 0, 0.2);
        padding: 20px;
        text-align: center;
        backdrop-filter: blur(10px);
    }

    .header h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .header p {
        opacity: 0.8;
        font-size: 1.1rem;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.1);
        padding: 25px;
        border-radius: 15px;
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .stat-label {
        font-size: 1.1rem;
        opacity: 0.8;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .dashboard-section {
        background: rgba(255, 255, 255, 0.1);
        padding: 25px;
        border-radius: 15px;
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .section-title {
        font-size: 1.5rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ip-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .ip-item {
        background: rgba(255, 255, 255, 0.05);
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ip-address {
        font-family: monospace;
        font-weight: bold;
    }

    .ip-status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: bold;
    }

    .status-blocked {
        background: #B71C1C;
        color: white;
    }

    .status-suspicious {
        background: #FF9800;
        color: white;
    }

    .status-normal {
        background: #4CAF50;
        color: white;
    }

    .chart-container {
        height: 300px;
        margin-top: 20px;
    }

    .actions {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

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

    .btn-danger {
        background: linear-gradient(135deg, #B71C1C, #D32F2F);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, #FF9800, #FFC107);
        color: white;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .alert {
        background: rgba(255, 193, 7, 0.2);
        border: 2px solid #FFC107;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-danger {
        background: rgba(183, 28, 28, 0.2);
        border-color: #B71C1C;
    }

    .refresh-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.5);
        padding: 10px 15px;
        border-radius: 25px;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>🛡️ Monitoring DDoS Protection</h1>
        <p>Surveillance en temps réel de la protection contre les attaques DDoS</p>
    </div>

    <div class="container">
        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Alertes -->
        <?php if ($stats['blocked_ips'] > DDOS_ALERT_THRESHOLD): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>ALERTE DDoS !</strong> <?= $stats['blocked_ips'] ?> IPs actuellement bloquées (seuil:
            <?= DDOS_ALERT_THRESHOLD ?>)
        </div>
        <?php elseif ($stats['blocked_ips'] > 0): ?>
        <div class="alert">
            <i class="fas fa-info-circle"></i>
            <strong>Attention :</strong> <?= $stats['blocked_ips'] ?> IPs actuellement bloquées
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="actions">
            <button class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <button class="btn btn-warning" onclick="exportLogs()">
                <i class="fas fa-download"></i> Exporter Logs
            </button>
            <button class="btn btn-danger" onclick="clearAllBlocks()">
                <i class="fas fa-unlock"></i> Débloquer Tout
            </button>
            <a href="javascript:history.back()" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <!-- Statistiques principales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🌐</div>
                <div class="stat-value"><?= $stats['total_ips'] ?? 0 ?></div>
                <div class="stat-label">IPs Total</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🚫</div>
                <div class="stat-value"><?= $stats['blocked_ips'] ?? 0 ?></div>
                <div class="stat-label">IPs Bloquées</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value"><?= $stats['suspicious_ips'] ?? 0 ?></div>
                <div class="stat-label">IPs Suspectes</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value"><?= round($stats['avg_suspicious_score'] ?? 0, 1) ?></div>
                <div class="stat-label">Score Moyen</div>
            </div>
        </div>

        <!-- Dashboard principal -->
        <div class="dashboard-grid">
            <!-- IPs Bloquées -->
            <div class="dashboard-section">
                <h2 class="section-title">
                    <i class="fas fa-ban"></i> IPs Actuellement Bloquées
                </h2>
                <div class="ip-list">
                    <?php if (empty($blockedIps)): ?>
                    <div style="text-align: center; padding: 20px; opacity: 0.7;">
                        <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <p>Aucune IP bloquée actuellement</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($blockedIps as $ip): ?>
                    <div class="ip-item">
                        <div>
                            <div class="ip-address"><?= htmlspecialchars($ip['ip_address']) ?></div>
                            <div style="font-size: 0.9rem; opacity: 0.7;">
                                Score: <?= $ip['suspicious_score'] ?> |
                                Déblocage: <?= date('H:i', strtotime($ip['blocked_until'])) ?>
                            </div>
                        </div>
                        <div>
                            <span class="ip-status status-blocked">
                                <?= gmdate('H:i:s', $ip['remaining_seconds']) ?>
                            </span>
                            <button class="btn btn-primary"
                                style="margin-left: 10px; padding: 5px 10px; font-size: 0.8rem;"
                                onclick="unblockIp('<?= $ip['ip_address'] ?>')">
                                Débloquer
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- IPs Suspectes -->
            <div class="dashboard-section">
                <h2 class="section-title">
                    <i class="fas fa-exclamation-triangle"></i> IPs Suspectes
                </h2>
                <div class="ip-list">
                    <?php if (empty($suspiciousIps)): ?>
                    <div style="text-align: center; padding: 20px; opacity: 0.7;">
                        <i class="fas fa-shield-alt" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <p>Aucune activité suspecte détectée</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($suspiciousIps as $ip): ?>
                    <div class="ip-item">
                        <div>
                            <div class="ip-address"><?= htmlspecialchars($ip['ip_address']) ?></div>
                            <div style="font-size: 0.9rem; opacity: 0.7;">
                                Req/min: <?= $ip['request_count_minute'] ?> |
                                Req/h: <?= $ip['request_count_hour'] ?>
                            </div>
                        </div>
                        <div>
                            <span class="ip-status status-suspicious">
                                Score: <?= $ip['suspicious_score'] ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="dashboard-section">
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i> Activité des 24 Dernières Heures
            </h2>
            <div class="chart-container">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        <!-- Activité Récente -->
        <div class="dashboard-section">
            <h2 class="section-title">
                <i class="fas fa-calendar-week"></i> Activité des 7 Derniers Jours
            </h2>
            <div class="chart-container">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="refresh-indicator" id="refreshIndicator">
        <i class="fas fa-sync-alt"></i> Dernière actualisation: <span id="lastRefresh"><?= date('H:i:s') ?></span>
    </div>

    <script>
    // Données pour les graphiques
    const hourlyData = <?= json_encode($hourlyStats) ?>;
    const weeklyData = <?= json_encode($recentActivity) ?>;

    // Graphique activité horaire
    const ctx1 = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: hourlyData.map(h => h.hour + ':00'),
            datasets: [{
                label: 'Requêtes',
                data: hourlyData.map(h => h.requests),
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                tension: 0.4
            }, {
                label: 'Blocages',
                data: hourlyData.map(h => h.blocks),
                borderColor: '#B71C1C',
                backgroundColor: 'rgba(183, 28, 28, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: 'white'
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: 'white'
                    }
                },
                y: {
                    ticks: {
                        color: 'white'
                    }
                }
            }
        }
    });

    // Graphique activité hebdomadaire
    const ctx2 = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: weeklyData.map(w => w.date),
            datasets: [{
                label: 'IPs Total',
                data: weeklyData.map(w => w.total_ips),
                backgroundColor: 'rgba(27, 94, 32, 0.7)'
            }, {
                label: 'IPs Bloquées',
                data: weeklyData.map(w => w.blocked_count),
                backgroundColor: 'rgba(183, 28, 28, 0.7)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: 'white'
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: 'white'
                    }
                },
                y: {
                    ticks: {
                        color: 'white'
                    }
                }
            }
        }
    });

    // Fonctions
    function refreshData() {
        location.reload();
    }

    function exportLogs() {
        window.open('export_ddos_logs.php', '_blank');
    }

    function unblockIp(ip) {
        if (confirm('Débloquer l\'IP ' + ip + ' ?')) {
            fetch('ddos_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'unblock',
                        ip: ip
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('IP débloquée avec succès');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                });
        }
    }

    function clearAllBlocks() {
        if (confirm('Débloquer toutes les IPs ? Cette action est irréversible.')) {
            fetch('ddos_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'clear_all'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Toutes les IPs ont été débloquées');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                });
        }
    }

    // Actualisation automatique toutes les 30 secondes
    setInterval(() => {
        document.getElementById('lastRefresh').textContent = new Date().toLocaleTimeString();
    }, 30000);

    // Actualisation des données toutes les 2 minutes
    setInterval(() => {
        location.reload();
    }, 120000);
    </script>
</body>

</html>