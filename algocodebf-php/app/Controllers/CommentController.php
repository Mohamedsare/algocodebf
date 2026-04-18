<?php
/**
 * Contrôleur des commentaires
 * Gère les commentaires pour tous les types de ressources (posts, tutorials, blogs, projects)
 */

class CommentController extends Controller
{
    private $commentModel;

    public function __construct()
    {
        $this->commentModel = $this->model('Comment');
    }

    /**
     * Récupérer les commentaires d'une ressource (AJAX)
     */
    public function getComments($type, $resourceId)
    {
        header('Content-Type: application/json');
        
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $comments = $this->commentModel->getComments($type, $resourceId, $limit, $offset);
            $total = $this->commentModel->countComments($type, $resourceId);
            
            // Formater les commentaires pour l'affichage
            $formatted = [];
            foreach ($comments as $comment) {
                $formatted[] = [
                    'id' => $comment['id'],
                    'body' => $comment['body'],
                    'created_at' => $comment['created_at'],
                    'time_ago' => timeAgo($comment['created_at']),
                    'user' => [
                        'id' => $comment['user_id'],
                        'name' => $comment['prenom'] . ' ' . $comment['nom'],
                        'photo' => $comment['photo_path'] ? BASE_URL . '/' . $comment['photo_path'] : null,
                        'role' => $comment['role']
                    ],
                    'can_edit' => $this->isLoggedIn() && 
                                  ($comment['user_id'] == $_SESSION['user_id'] || 
                                   (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'))
                ];
            }
            
            echo json_encode([
                'success' => true,
                'comments' => $formatted,
                'total' => $total,
                'page' => $page,
                'has_more' => count($comments) === $limit
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors du chargement des commentaires'
            ]);
        }
    }

    /**
     * Créer un nouveau commentaire (AJAX)
     */
    public function create()
    {
        header('Content-Type: application/json');
        
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        try {
            // Vérifier le CSRF token
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide']);
                return;
            }
            
            // Valider les données
            $body = Security::cleanContent($_POST['body'] ?? '');
            $type = Security::clean($_POST['type'] ?? '');
            $resourceId = (int)($_POST['resource_id'] ?? 0);
            
            if (empty($body) || strlen($body) < 3) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Le commentaire doit contenir au moins 3 caractères']);
                return;
            }
            
            if (!in_array($type, ['post', 'tutorial', 'blog', 'project'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Type de ressource invalide']);
                return;
            }
            
            if ($resourceId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de ressource invalide']);
                return;
            }
            
            // Créer le commentaire
            $data = [
                'user_id' => $_SESSION['user_id'],
                'commentable_type' => $type,
                'commentable_id' => $resourceId,
                'body' => $body,
                'status' => 'active'
            ];
            
            $commentId = $this->commentModel->createComment($data);
            
            if ($commentId) {
                // Récupérer le commentaire créé avec les infos de l'auteur
                $comment = $this->commentModel->getWithAuthor($commentId);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Commentaire ajouté avec succès',
                    'comment' => [
                        'id' => $comment['id'],
                        'body' => $comment['body'],
                        'created_at' => $comment['created_at'],
                        'time_ago' => timeAgo($comment['created_at']),
                        'user' => [
                            'id' => $comment['user_id'],
                            'name' => $comment['prenom'] . ' ' . $comment['nom'],
                            'photo' => $comment['photo_path'] ? BASE_URL . '/' . $comment['photo_path'] : null,
                            'role' => $comment['role']
                        ],
                        'can_edit' => true
                    ]
                ]);
            } else {
                throw new Exception('Erreur lors de la création du commentaire');
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du commentaire'
            ]);
        }
    }

    /**
     * Mettre à jour un commentaire (AJAX)
     */
    public function update($commentId)
    {
        header('Content-Type: application/json');
        
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        try {
            // Vérifier les permissions
            if (!$this->commentModel->canEdit($commentId, $_SESSION['user_id'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas la permission de modifier ce commentaire']);
                return;
            }
            
            // Valider les données
            $body = Security::cleanContent($_POST['body'] ?? '');
            
            if (empty($body) || strlen($body) < 3) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Le commentaire doit contenir au moins 3 caractères']);
                return;
            }
            
            // Mettre à jour
            $updated = $this->commentModel->updateComment($commentId, ['body' => $body]);
            
            if ($updated) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Commentaire modifié avec succès'
                ]);
            } else {
                throw new Exception('Erreur lors de la mise à jour');
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la modification du commentaire'
            ]);
        }
    }

    /**
     * Supprimer un commentaire (AJAX)
     */
    public function delete($commentId)
    {
        header('Content-Type: application/json');
        
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        try {
            // Vérifier les permissions
            if (!$this->commentModel->canEdit($commentId, $_SESSION['user_id'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas la permission de supprimer ce commentaire']);
                return;
            }
            
            // Supprimer (soft delete)
            $deleted = $this->commentModel->deleteComment($commentId);
            
            if ($deleted) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Commentaire supprimé avec succès'
                ]);
            } else {
                throw new Exception('Erreur lors de la suppression');
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la suppression du commentaire'
            ]);
        }
    }
}

