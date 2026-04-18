<?php
/**
 * Modèle Comment - Gestion des commentaires polymorphiques
 * Supporte : posts, tutorials, blogs, projects
 */

class Comment extends Model
{
    protected $table = 'comments';

    /**
     * Créer un nouveau commentaire
     * 
     * @param array $data Données du commentaire
     * @return int|false ID du commentaire créé ou false
     */
    public function createComment($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    /**
     * Obtenir les commentaires d'une ressource
     * 
     * @param string $type Type de ressource (post, tutorial, blog, project)
     * @param int $resourceId ID de la ressource
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getComments($type, $resourceId, $limit = 50, $offset = 0)
    {
        $query = "
            SELECT c.*, 
                   u.prenom, u.nom, u.photo_path, u.role
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE c.commentable_type = ? 
              AND c.commentable_id = ? 
              AND c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        return $this->db->query($query, [$type, $resourceId, $limit, $offset]);
    }

    /**
     * Obtenir un commentaire par son ID avec les infos de l'auteur
     * 
     * @param int $commentId ID du commentaire
     * @return array|false
     */
    public function getWithAuthor($commentId)
    {
        $query = "
            SELECT c.*, 
                   u.prenom, u.nom, u.photo_path, u.role
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ";
        
        return $this->db->queryOne($query, [$commentId]);
    }

    /**
     * Compter les commentaires d'une ressource
     * 
     * @param string $type Type de ressource
     * @param int $resourceId ID de la ressource
     * @return int
     */
    public function countComments($type, $resourceId)
    {
        $query = "
            SELECT COUNT(*) as total
            FROM comments
            WHERE commentable_type = ? 
              AND commentable_id = ? 
              AND status = 'active'
        ";
        
        $result = $this->db->queryOne($query, [$type, $resourceId]);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Mettre à jour un commentaire
     * 
     * @param int $commentId ID du commentaire
     * @param array $data Nouvelles données
     * @return bool
     */
    public function updateComment($commentId, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($commentId, $data);
    }

    /**
     * Supprimer un commentaire (soft delete)
     * 
     * @param int $commentId ID du commentaire
     * @return bool
     */
    public function deleteComment($commentId)
    {
        return $this->update($commentId, [
            'status' => 'deleted',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Obtenir les derniers commentaires d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $limit Limite de résultats
     * @return array
     */
    public function getUserComments($userId, $limit = 10)
    {
        $query = "
            SELECT c.*, 
                   u.prenom, u.nom, u.photo_path
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE c.user_id = ? AND c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT ?
        ";
        
        return $this->db->query($query, [$userId, $limit]);
    }

    /**
     * Vérifier si un utilisateur peut modifier un commentaire
     * 
     * @param int $commentId ID du commentaire
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function canEdit($commentId, $userId)
    {
        $comment = $this->findById($commentId);
        
        if (!$comment) {
            return false;
        }
        
        // Le propriétaire peut éditer son commentaire
        if ($comment['user_id'] == $userId) {
            return true;
        }
        
        // Les admins peuvent éditer tous les commentaires
        $userModel = new User();
        $user = $userModel->findById($userId);
        return $user && $user['role'] === 'admin';
    }

    /**
     * Obtenir les commentaires récents du site
     * 
     * @param int $limit Limite de résultats
     * @return array
     */
    public function getRecentComments($limit = 10)
    {
        $query = "
            SELECT c.*, 
                   u.prenom, u.nom, u.photo_path,
                   CASE c.commentable_type
                       WHEN 'post' THEN (SELECT title FROM posts WHERE id = c.commentable_id)
                       WHEN 'tutorial' THEN (SELECT title FROM tutorials WHERE id = c.commentable_id)
                       WHEN 'blog' THEN (SELECT title FROM blog_posts WHERE id = c.commentable_id)
                       ELSE 'Ressource supprimée'
                   END as resource_title
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT ?
        ";
        
        return $this->db->query($query, [$limit]);
    }
}

