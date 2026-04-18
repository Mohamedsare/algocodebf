<?php
/**
 * Script de Monitoring DDoS avec Alertes
 * À exécuter toutes les 5 minutes via cron job
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/ddos_config.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Helpers/DDoSProtection.php';
require_once __DIR__ . '/app/Helpers/DDoSAlerts.php';

// Configuration du logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/ddos_monitoring.log');

echo "🛡️ Monitoring DDoS Protection - " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 60) . "\n";

try {
    // Initialiser les classes
    $ddosProtection = new DDoSProtection();
    $ddosAlerts = new DDoSAlerts();
    $db = Database::getInstance();
    
    // Récupérer les statistiques actuelles
    $stats = $ddosProtection->getStats();
    
    echo "📊 Statistiques Actuelles:\n";
    echo "   - IPs Total: " . $stats['total_ips'] . "\n";
    echo "   - IPs Bloquées: " . $stats['blocked_ips'] . "\n";
    echo "   - IPs Suspectes: " . $stats['suspicious_ips'] . "\n";
    echo "   - Score Moyen: " . round($stats['avg_suspicious_score'], 2) . "\n";
    echo "\n";
    
    // Vérifier et envoyer les alertes
    echo "🔔 Vérification des Alertes...\n";
    $alertSent = $ddosAlerts->checkAndSendAlerts();
    
    if ($alertSent) {
        echo "   ✅ Alertes vérifiées et envoyées si nécessaire\n";
    } else {
        echo "   ℹ️  Aucune alerte nécessaire\n";
    }
    echo "\n";
    
    // Analyser les tendances
    echo "📈 Analyse des Tendances:\n";
    
    // Comparer avec la dernière heure
    $lastHourStats = $db->queryOne("
        SELECT 
            COUNT(*) as total_ips,
            COUNT(CASE WHEN blocked_until > NOW() THEN 1 END) as blocked_ips
        FROM ddos_protection 
        WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    
    $currentBlocked = $stats['blocked_ips'];
    $lastHourBlocked = $lastHourStats['blocked_ips'];
    
    if ($currentBlocked > $lastHourBlocked) {
        $increase = $currentBlocked - $lastHourBlocked;
        echo "   ⚠️  Augmentation de {$increase} IPs bloquées cette heure\n";
        
        if ($increase > 10) {
            echo "   🚨 ALERTE: Augmentation significative détectée!\n";
        }
    } elseif ($currentBlocked < $lastHourBlocked) {
        $decrease = $lastHourBlocked - $currentBlocked;
        echo "   ✅ Diminution de {$decrease} IPs bloquées cette heure\n";
    } else {
        echo "   ➡️  Stabilité des IPs bloquées\n";
    }
    echo "\n";
    
    // Vérifier les IPs les plus actives
    echo "🔍 Top 5 IPs les Plus Actives:\n";
    $topIps = $db->query("
        SELECT ip_address, request_count_minute, request_count_hour, 
               suspicious_score, blocked_until
        FROM ddos_protection 
        ORDER BY request_count_hour DESC 
        LIMIT 5
    ");
    
    foreach ($topIps as $index => $ip) {
        $status = $ip['blocked_until'] && strtotime($ip['blocked_until']) > time() ? '🚫 BLOQUÉE' : '✅ ACTIVE';
        echo "   " . ($index + 1) . ". {$ip['ip_address']} - {$ip['request_count_hour']} req/h - Score: {$ip['suspicious_score']} - {$status}\n";
    }
    echo "\n";
    
    // Vérifier la santé du système
    echo "🏥 Santé du Système:\n";
    
    // Vérifier l'espace disque
    $diskUsage = disk_free_space(__DIR__);
    $diskUsagePercent = (1 - ($diskUsage / disk_total_space(__DIR__))) * 100;
    
    if ($diskUsagePercent > 90) {
        echo "   🚨 ALERTE: Espace disque critique ({$diskUsagePercent}% utilisé)\n";
    } elseif ($diskUsagePercent > 80) {
        echo "   ⚠️  Attention: Espace disque élevé ({$diskUsagePercent}% utilisé)\n";
    } else {
        echo "   ✅ Espace disque OK ({$diskUsagePercent}% utilisé)\n";
    }
    
    // Vérifier la charge de la base de données
    $dbSize = $db->queryOne("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
    ");
    
    echo "   📊 Taille DB: {$dbSize['size_mb']} MB\n";
    
    // Vérifier les performances
    $slowQueries = $db->queryOne("SHOW STATUS LIKE 'Slow_queries'");
    echo "   🐌 Requêtes lentes: " . $slowQueries['Value'] . "\n";
    echo "\n";
    
    // Recommandations
    echo "💡 Recommandations:\n";
    
    if ($stats['blocked_ips'] > 50) {
        echo "   - Considérer l'augmentation des limites de protection\n";
        echo "   - Vérifier si une attaque DDoS est en cours\n";
    }
    
    if ($stats['avg_suspicious_score'] > 3) {
        echo "   - Surveiller de près les IPs suspectes\n";
        echo "   - Considérer l'ajout d'IPs à la liste noire\n";
    }
    
    if ($diskUsagePercent > 80) {
        echo "   - Nettoyer les anciens logs\n";
        echo "   - Considérer l'augmentation de l'espace disque\n";
    }
    
    echo "\n";
    
    // Résumé final
    $status = 'OK';
    if ($stats['blocked_ips'] > DDOS_ALERT_THRESHOLD) {
        $status = 'CRITIQUE';
    } elseif ($stats['blocked_ips'] > 0) {
        $status = 'ATTENTION';
    }
    
    echo "📋 Résumé Final:\n";
    echo "   - Statut: {$status}\n";
    echo "   - Protection: ACTIVE\n";
    echo "   - Prochaine vérification: " . date('H:i:s', time() + 300) . "\n";
    echo "   - Dashboard: https://" . $_SERVER['SERVER_NAME'] . "/admin/ddos_monitoring.php\n";
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ Monitoring terminé avec succès\n";
    
    // Sauvegarder les statistiques pour l'historique
    $this->saveMonitoringStats($stats);
    
} catch (Exception $e) {
    echo "❌ Erreur lors du monitoring: " . $e->getMessage() . "\n";
    error_log("DDoS Monitoring Error: " . $e->getMessage());
    exit(1);
}

/**
 * Sauvegarder les statistiques de monitoring
 */
function saveMonitoringStats($stats)
{
    try {
        $db = Database::getInstance();
        
        // Créer la table de monitoring si elle n'existe pas
        $createTable = "
            CREATE TABLE IF NOT EXISTS ddos_monitoring_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                total_ips INT,
                blocked_ips INT,
                suspicious_ips INT,
                avg_suspicious_score DECIMAL(5,2),
                INDEX idx_timestamp (timestamp)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $db->execute($createTable);
        
        // Insérer les statistiques actuelles
        $insertQuery = "
            INSERT INTO ddos_monitoring_history 
            (total_ips, blocked_ips, suspicious_ips, avg_suspicious_score) 
            VALUES (?, ?, ?, ?)
        ";
        
        $db->execute($insertQuery, [
            $stats['total_ips'],
            $stats['blocked_ips'],
            $stats['suspicious_ips'],
            $stats['avg_suspicious_score']
        ]);
        
        // Nettoyer les anciennes entrées (garder 30 jours)
        $cleanupQuery = "
            DELETE FROM ddos_monitoring_history 
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ";
        
        $db->execute($cleanupQuery);
        
    } catch (Exception $e) {
        error_log("Error saving monitoring stats: " . $e->getMessage());
    }
}
