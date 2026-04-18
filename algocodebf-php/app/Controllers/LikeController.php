<?php
/**
 * Contrôleur des likes
 * Gère les likes pour tous les types de ressources (posts, tutorials, comments)
 */

class LikeController extends Controller
{
    /**
     * Toggle like (ajouter ou retirer un like)
     */
    public function toggle()
    {
        header('Content-Type: application/json');
        
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        try {
            // Récupérer les données
            $type = Security::clean($_POST['type'] ?? '');
            $resourceId = (int)($_POST['resource_id'] ?? 0);
            $userId = $_SESSION['user_id'];
            
            // Valider
            if (!in_array($type, ['post', 'tutorial', 'comment', 'blog'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Type invalide']);
                return;
            }
            
            if ($resourceId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID invalide']);
                return;
            }
            
            $db = Database::getInstance();
            
            // Vérifier si l'utilisateur a déjà liké
            $existing = $db->queryOne(
                "SELECT id FROM likes WHERE user_id = ? AND likeable_type = ? AND likeable_id = ?",
                [$userId, $type, $resourceId]
            );
            
            if ($existing) {
                // Retirer le like
                $db->execute(
                    "DELETE FROM likes WHERE user_id = ? AND likeable_type = ? AND likeable_id = ?",
                    [$userId, $type, $resourceId]
                );
                
                $liked = false;
                $message = 'Like retiré';
            } else {
                // Ajouter le like
                $db->execute(
                    "INSERT INTO likes (user_id, likeable_type, likeable_id, created_at) VALUES (?, ?, ?, NOW())",
                    [$userId, $type, $resourceId]
                );
                
                $liked = true;
                $message = 'Liké avec succès';
            }
            
            // Compter le nombre total de likes
            $count = $db->queryOne(
                "SELECT COUNT(*) as total FROM likes WHERE likeable_type = ? AND likeable_id = ?",
                [$type, $resourceId]
            );
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'liked' => $liked,
                'likes_count' => (int)$count['total']
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'opération'
            ]);
        }
    }
}

