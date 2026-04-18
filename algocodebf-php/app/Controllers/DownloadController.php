<?php
/**
 * Contrôleur des téléchargements
 * Gère le tracking des téléchargements de fichiers
 */

class DownloadController extends Controller
{
    /**
     * Télécharger un fichier et enregistrer le tracking
     */
    public function track()
    {
        header('Content-Type: application/json');
        
        try {
            // Récupérer les données
            $type = Security::clean($_POST['type'] ?? '');
            $resourceId = (int)($_POST['resource_id'] ?? 0);
            $filePath = Security::clean($_POST['file_path'] ?? '');
            
            // Valider
            if (!in_array($type, ['tutorial', 'project', 'document'])) {
                echo json_encode(['success' => false, 'message' => 'Type invalide']);
                return;
            }
            
            if ($resourceId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID invalide']);
                return;
            }
            
            if (empty($filePath)) {
                echo json_encode(['success' => false, 'message' => 'Fichier invalide']);
                return;
            }
            
            $db = Database::getInstance();
            
            // Incrémenter le compteur dans la table principale
            if ($type === 'tutorial') {
                $db->execute(
                    "UPDATE tutorials SET downloads = downloads + 1 WHERE id = ?",
                    [$resourceId]
                );
            } elseif ($type === 'project') {
                $db->execute(
                    "UPDATE projects SET downloads = downloads + 1 WHERE id = ?",
                    [$resourceId]
                );
            }
            
            // Enregistrer le téléchargement dans la table de tracking
            $userId = $_SESSION['user_id'] ?? null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $db->execute(
                "INSERT INTO downloads (user_id, downloadable_type, downloadable_id, file_path, ip_address, user_agent, downloaded_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$userId, $type, $resourceId, $filePath, $ipAddress, $userAgent]
            );
            
            // Récupérer le nouveau total
            $result = $db->queryOne(
                "SELECT downloads FROM tutorials WHERE id = ?",
                [$resourceId]
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Téléchargement enregistré',
                'downloads_count' => (int)($result['downloads'] ?? 0)
            ]);
            
        } catch (Exception $e) {
            error_log('Erreur download tracking: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement'
            ]);
        }
    }

    /**
     * Obtenir les statistiques de téléchargement
     */
    public function stats($type, $resourceId)
    {
        header('Content-Type: application/json');
        
        try {
            $db = Database::getInstance();
            
            // Stats générales
            $total = $db->queryOne(
                "SELECT COUNT(*) as total FROM downloads WHERE downloadable_type = ? AND downloadable_id = ?",
                [$type, $resourceId]
            );
            
            // Téléchargements uniques (utilisateurs différents)
            $unique = $db->queryOne(
                "SELECT COUNT(DISTINCT user_id) as total FROM downloads WHERE downloadable_type = ? AND downloadable_id = ? AND user_id IS NOT NULL",
                [$type, $resourceId]
            );
            
            // Téléchargements récents (7 derniers jours)
            $recent = $db->queryOne(
                "SELECT COUNT(*) as total FROM downloads WHERE downloadable_type = ? AND downloadable_id = ? AND downloaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
                [$type, $resourceId]
            );
            
            echo json_encode([
                'success' => true,
                'stats' => [
                    'total' => (int)($total['total'] ?? 0),
                    'unique_users' => (int)($unique['total'] ?? 0),
                    'last_7_days' => (int)($recent['total'] ?? 0)
                ]
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la récupération des stats'
            ]);
        }
    }
}

