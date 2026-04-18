<?php
/**
 * Script d'actions DDoS
 * Gestion des IPs bloquées et des actions administratives
 */

header('Content-Type: application/json');

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/ddos_config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Helpers/DDoSProtection.php';

// Vérifier les permissions admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

// Vérifier la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Action non spécifiée']);
    exit;
}

try {
    $ddosProtection = new DDoSProtection();
    $db = Database::getInstance();
    
    switch ($input['action']) {
        case 'unblock':
            if (!isset($input['ip'])) {
                echo json_encode(['success' => false, 'message' => 'IP non spécifiée']);
                exit;
            }
            
            $result = $ddosProtection->unblockIp($input['ip']);
            
            if ($result) {
                // Logger l'action
                error_log("DDoS Admin Action: IP {$input['ip']} unblocked by admin {$_SESSION['user_id']}");
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'IP débloquée avec succès'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du déblocage']);
            }
            break;
            
        case 'clear_all':
            $query = "UPDATE ddos_protection SET blocked_until = NULL WHERE blocked_until > NOW()";
            $result = $db->execute($query);
            
            if ($result) {
                // Logger l'action
                error_log("DDoS Admin Action: All IPs unblocked by admin {$_SESSION['user_id']}");
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Toutes les IPs ont été débloquées'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du déblocage global']);
            }
            break;
            
        case 'block_ip':
            if (!isset($input['ip']) || !isset($input['duration'])) {
                echo json_encode(['success' => false, 'message' => 'IP ou durée non spécifiée']);
                exit;
            }
            
            $ip = filter_var($input['ip'], FILTER_VALIDATE_IP);
            if (!$ip) {
                echo json_encode(['success' => false, 'message' => 'Adresse IP invalide']);
                exit;
            }
            
            $duration = intval($input['duration']);
            if ($duration <= 0 || $duration > 86400) {
                echo json_encode(['success' => false, 'message' => 'Durée invalide (1-86400 secondes)']);
                exit;
            }
            
            // Bloquer manuellement l'IP
            $blockedUntil = date('Y-m-d H:i:s', time() + $duration);
            $query = "INSERT INTO ddos_protection (ip_address, blocked_until, suspicious_score, attempts) 
                      VALUES (?, ?, 10, 999)
                      ON DUPLICATE KEY UPDATE 
                      blocked_until = ?, suspicious_score = 10, attempts = 999";
            
            $result = $db->execute($query, [$ip, $blockedUntil, $blockedUntil]);
            
            if ($result) {
                // Logger l'action
                error_log("DDoS Admin Action: IP {$ip} manually blocked for {$duration}s by admin {$_SESSION['user_id']}");
                
                echo json_encode([
                    'success' => true, 
                    'message' => "IP bloquée pour " . gmdate('H:i:s', $duration)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du blocage']);
            }
            break;
            
        case 'get_stats':
            $stats = $ddosProtection->getStats();
            
            // Ajouter des statistiques supplémentaires
            $additionalStats = $db->queryOne("
                SELECT 
                    COUNT(CASE WHEN blocked_until > NOW() THEN 1 END) as currently_blocked,
                    COUNT(CASE WHEN suspicious_score > 5 THEN 1 END) as highly_suspicious,
                    MAX(suspicious_score) as max_suspicious_score,
                    AVG(request_count_minute) as avg_requests_per_minute
                FROM ddos_protection
            ");
            
            $stats = array_merge($stats, $additionalStats);
            
            echo json_encode([
                'success' => true, 
                'data' => $stats
            ]);
            break;
            
        case 'export_logs':
            $logs = $db->query("
                SELECT ip_address, suspicious_score, request_count_minute, 
                       request_count_hour, request_count_day, blocked_until, 
                       created_at, updated_at
                FROM ddos_protection 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC
            ");
            
            // Créer le fichier CSV
            $filename = 'ddos_logs_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = __DIR__ . '/../logs/' . $filename;
            
            $file = fopen($filepath, 'w');
            fputcsv($file, ['IP Address', 'Suspicious Score', 'Requests/Min', 'Requests/Hour', 'Requests/Day', 'Blocked Until', 'Created At', 'Updated At']);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log['ip_address'],
                    $log['suspicious_score'],
                    $log['request_count_minute'],
                    $log['request_count_hour'],
                    $log['request_count_day'],
                    $log['blocked_until'],
                    $log['created_at'],
                    $log['updated_at']
                ]);
            }
            
            fclose($file);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Logs exportés avec succès',
                'filename' => $filename,
                'download_url' => 'logs/' . $filename
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
            break;
    }
    
} catch (Exception $e) {
    error_log("DDoS Actions Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
