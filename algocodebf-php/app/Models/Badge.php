<?php
/**
 * Modèle Badge - Gestion des badges et récompenses
 */

class Badge extends Model
{
    protected $table = 'badges';

    /**
     * Attribuer un badge à un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $badgeId ID du badge
     * @return bool
     */
    public function award($userId, $badgeId)
    {
        // Vérifier si l'utilisateur a déjà ce badge
        $existing = $this->db->queryOne(
            "SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?",
            [$userId, $badgeId]
        );
        
        if ($existing) {
            return false; // Badge déjà attribué
        }
        
        return $this->db->execute(
            "INSERT INTO user_badges (user_id, badge_id, awarded_at) VALUES (?, ?, ?)",
            [$userId, $badgeId, date('Y-m-d H:i:s')]
        );
    }

    /**
     * Vérifier et attribuer automatiquement les badges à un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Badges nouvellement attribués
     */
    public function checkAndAwardBadges($userId)
    {
        $newBadges = [];
        
        // Récupérer tous les badges non encore attribués
        $query = "
            SELECT b.* 
            FROM badges b
            LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = ?
            WHERE ub.id IS NULL
        ";
        
        $badges = $this->db->query($query, [$userId]);
        
        foreach ($badges as $badge) {
            $earned = false;
            
            switch ($badge['requirement_type']) {
                case 'posts_count':
                    $count = $this->db->queryOne(
                        "SELECT COUNT(*) as count FROM posts WHERE user_id = ? AND status = 'active'",
                        [$userId]
                    );
                    $earned = $count && $count['count'] >= $badge['requirement_value'];
                    break;
                    
                case 'tutorials_count':
                    $count = $this->db->queryOne(
                        "SELECT COUNT(*) as count FROM tutorials WHERE user_id = ? AND status = 'active'",
                        [$userId]
                    );
                    $earned = $count && $count['count'] >= $badge['requirement_value'];
                    break;
                    
                case 'likes_received':
                    $count = $this->db->queryOne(
                        "SELECT COUNT(*) as count FROM likes WHERE 
                         (likeable_type = 'post' AND likeable_id IN (SELECT id FROM posts WHERE user_id = ?))
                         OR (likeable_type = 'tutorial' AND likeable_id IN (SELECT id FROM tutorials WHERE user_id = ?))",
                        [$userId, $userId]
                    );
                    $earned = $count && $count['count'] >= $badge['requirement_value'];
                    break;
                    
                case 'projects_count':
                    $count = $this->db->queryOne(
                        "SELECT COUNT(*) as count FROM project_members WHERE user_id = ? AND status = 'active'",
                        [$userId]
                    );
                    $earned = $count && $count['count'] >= $badge['requirement_value'];
                    break;
                    
                case 'registration':
                    $earned = true; // Badge de bienvenue
                    break;
            }
            
            if ($earned) {
                if ($this->award($userId, $badge['id'])) {
                    $newBadges[] = $badge;
                }
            }
        }
        
        return $newBadges;
    }

    /**
     * Obtenir tous les badges avec le statut pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array
     */
    public function getAllWithUserStatus($userId)
    {
        $query = "
            SELECT b.*, 
                   ub.awarded_at,
                   CASE WHEN ub.id IS NOT NULL THEN TRUE ELSE FALSE END as earned
            FROM badges b
            LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = ?
            ORDER BY earned DESC, b.id ASC
        ";
        
        return $this->db->query($query, [$userId]);
    }
}

