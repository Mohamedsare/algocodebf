<?php
/**
 * Modèle Newsletter - Gestion des abonnés à la newsletter
 */

class Newsletter extends Model
{
    protected $table = 'newsletter_subscribers';

    /**
     * Ajouter un nouvel abonné
     * @param string $email
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return int|false ID de l'abonné ou false
     */
    public function subscribe($email, $ipAddress = null, $userAgent = null)
    {
        // Vérifier si l'email existe déjà
        $existing = $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );

        if ($existing) {
            // Si l'abonné était désabonné, le réactiver
            if ($existing['status'] === 'unsubscribed') {
                $this->db->execute(
                    "UPDATE {$this->table} SET status = 'active', subscribed_at = NOW(), unsubscribed_at = NULL WHERE email = ?",
                    [$email]
                );
                return $existing['id'];
            }
            return false; // Déjà abonné
        }

        // Nouvel abonné
        return $this->db->execute(
            "INSERT INTO {$this->table} (email, ip_address, user_agent) VALUES (?, ?, ?)",
            [$email, $ipAddress, $userAgent]
        );
    }

    /**
     * Désabonner un email
     * @param string $email
     * @return bool
     */
    public function unsubscribe($email)
    {
        return $this->db->execute(
            "UPDATE {$this->table} SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE email = ?",
            [$email]
        );
    }

    /**
     * Obtenir tous les abonnés actifs
     * @return array
     */
    public function getAllActive()
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY subscribed_at DESC"
        );
    }

    /**
     * Obtenir le nombre total d'abonnés actifs
     * @return int
     */
    public function countActive()
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'"
        );
        return $result['count'] ?? 0;
    }

    /**
     * Obtenir le nombre total d'abonnés par statut
     * @return array
     */
    public function getStats()
    {
        $result = $this->db->query(
            "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status"
        );
        
        $stats = [
            'active' => 0,
            'unsubscribed' => 0,
            'bounced' => 0
        ];
        
        foreach ($result as $row) {
            $stats[$row['status']] = $row['count'];
        }
        
        return $stats;
    }

    /**
     * Vérifier si un email est abonné
     * @param string $email
     * @return bool
     */
    public function isSubscribed($email)
    {
        $result = $this->db->queryOne(
            "SELECT status FROM {$this->table} WHERE email = ?",
            [$email]
        );
        
        return $result && $result['status'] === 'active';
    }

    /**
     * Mettre à jour la date du dernier envoi
     * @param string $email
     * @return bool
     */
    public function updateLastSent($email)
    {
        return $this->db->execute(
            "UPDATE {$this->table} SET last_sent_at = NOW(), total_sent = total_sent + 1 WHERE email = ?",
            [$email]
        );
    }

    /**
     * Marquer un email comme bounced (rebondi)
     * @param string $email
     * @return bool
     */
    public function markBounced($email)
    {
        return $this->db->execute(
            "UPDATE {$this->table} SET status = 'bounced' WHERE email = ?",
            [$email]
        );
    }

    /**
     * Supprimer un abonné
     * @param int $id
     * @return bool
     */
    public function deleteSubscriber($id)
    {
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Obtenir tous les abonnés avec pagination
     * @param int $limit
     * @param int $offset
     * @param string|null $status
     * @return array
     */
    public function getAllWithPagination($limit = 50, $offset = 0, $status = null)
    {
        $query = "SELECT * FROM {$this->table}";
        $params = [];
        
        if ($status) {
            $query .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY subscribed_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($query, $params);
    }
}

