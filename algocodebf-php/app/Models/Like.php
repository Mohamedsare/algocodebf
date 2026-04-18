<?php
/**
 * Modèle Like - Gestion des likes polymorphiques
 */

class Like extends Model
{
    protected $table = 'likes';

    /**
     * Vérifier si un utilisateur a liké une ressource
     * 
     * @param string $type Type de ressource (post, tutorial, blog, comment)
     * @param int $resourceId ID de la ressource
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function hasLiked($type, $resourceId, $userId)
    {
        $result = $this->db->queryOne(
            "SELECT id FROM likes WHERE user_id = ? AND likeable_type = ? AND likeable_id = ?",
            [$userId, $type, $resourceId]
        );
        
        return $result !== false;
    }

    /**
     * Compter les likes d'une ressource
     * 
     * @param string $type Type de ressource
     * @param int $resourceId ID de la ressource
     * @return int
     */
    public function countLikes($type, $resourceId)
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as total FROM likes WHERE likeable_type = ? AND likeable_id = ?",
            [$type, $resourceId]
        );
        
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Ajouter un like
     * 
     * @param string $type Type de ressource
     * @param int $resourceId ID de la ressource
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function add($type, $resourceId, $userId)
    {
        return $this->db->execute(
            "INSERT INTO likes (user_id, likeable_type, likeable_id, created_at) VALUES (?, ?, ?, NOW())",
            [$userId, $type, $resourceId]
        );
    }

    /**
     * Retirer un like
     * 
     * @param string $type Type de ressource
     * @param int $resourceId ID de la ressource
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function remove($type, $resourceId, $userId)
    {
        return $this->db->execute(
            "DELETE FROM likes WHERE user_id = ? AND likeable_type = ? AND likeable_id = ?",
            [$userId, $type, $resourceId]
        );
    }
}

