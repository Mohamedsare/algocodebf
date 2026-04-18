<?php
/**
 * Classe RateLimiter - Limitation du nombre de tentatives
 * Protection contre les attaques par force brute
 */

class RateLimiter
{
    private $db;
    private $table = 'rate_limits';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->createTableIfNotExists();
    }
    
    /**
     * Créer la table rate_limits si elle n'existe pas
     */
    private function createTableIfNotExists()
    {
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            action VARCHAR(50) NOT NULL,
            attempts INT DEFAULT 1,
            last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
            blocked_until DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_action (ip_address, action),
            INDEX idx_blocked_until (blocked_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->execute($query);
    }
    
    /**
     * Vérifier si l'IP est bloquée pour une action
     * 
     * @param string $action Type d'action (login, register, etc.)
     * @param int $maxAttempts Nombre max de tentatives autorisées
     * @param int $blockDuration Durée du blocage en secondes
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_at' => string]
     */
    public function check($action = 'login', $maxAttempts = 5, $blockDuration = 900)
    {
        $ipAddress = $this->getIpAddress();
        
        // Nettoyer les anciennes entrées (plus de 24h)
        $this->cleanup();
        
        // Récupérer l'enregistrement existant
        $query = "SELECT * FROM {$this->table} 
                  WHERE ip_address = ? AND action = ? 
                  LIMIT 1";
        $record = $this->db->queryOne($query, [$ipAddress, $action]);
        
        if (!$record) {
            return [
                'allowed' => true,
                'remaining' => $maxAttempts,
                'reset_at' => null
            ];
        }
        
        // Vérifier si l'IP est actuellement bloquée
        if ($record['blocked_until'] && strtotime($record['blocked_until']) > time()) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_at' => $record['blocked_until'],
                'blocked_seconds' => strtotime($record['blocked_until']) - time()
            ];
        }
        
        // Si le blocage est expiré, réinitialiser
        if ($record['blocked_until'] && strtotime($record['blocked_until']) <= time()) {
            $this->reset($action);
            return [
                'allowed' => true,
                'remaining' => $maxAttempts,
                'reset_at' => null
            ];
        }
        
        // Vérifier le nombre de tentatives
        $remaining = $maxAttempts - $record['attempts'];
        
        return [
            'allowed' => $remaining > 0,
            'remaining' => max(0, $remaining),
            'reset_at' => $remaining <= 0 ? date('Y-m-d H:i:s', time() + $blockDuration) : null
        ];
    }
    
    /**
     * Enregistrer une tentative
     * 
     * @param string $action Type d'action
     * @param int $maxAttempts Nombre max de tentatives
     * @param int $blockDuration Durée du blocage en secondes
     * @return bool
     */
    public function hit($action = 'login', $maxAttempts = 5, $blockDuration = 900)
    {
        $ipAddress = $this->getIpAddress();
        
        $query = "SELECT * FROM {$this->table} 
                  WHERE ip_address = ? AND action = ? 
                  LIMIT 1";
        $record = $this->db->queryOne($query, [$ipAddress, $action]);
        
        if (!$record) {
            // Première tentative
            $insertQuery = "INSERT INTO {$this->table} (ip_address, action, attempts, last_attempt) 
                           VALUES (?, ?, 1, NOW())";
            return $this->db->execute($insertQuery, [$ipAddress, $action]);
        }
        
        // Incrémenter le compteur
        $newAttempts = $record['attempts'] + 1;
        $blockedUntil = null;
        
        // Si dépassement, bloquer
        if ($newAttempts >= $maxAttempts) {
            $blockedUntil = date('Y-m-d H:i:s', time() + $blockDuration);
        }
        
        $updateQuery = "UPDATE {$this->table} 
                       SET attempts = ?, 
                           last_attempt = NOW(),
                           blocked_until = ?
                       WHERE ip_address = ? AND action = ?";
        
        return $this->db->execute($updateQuery, [$newAttempts, $blockedUntil, $ipAddress, $action]);
    }
    
    /**
     * Réinitialiser le compteur pour une action
     * 
     * @param string $action Type d'action
     * @return bool
     */
    public function reset($action = 'login')
    {
        $ipAddress = $this->getIpAddress();
        
        $query = "DELETE FROM {$this->table} 
                  WHERE ip_address = ? AND action = ?";
        
        return $this->db->execute($query, [$ipAddress, $action]);
    }
    
    /**
     * Nettoyer les anciennes entrées (plus de 24h)
     */
    private function cleanup()
    {
        $query = "DELETE FROM {$this->table} 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $this->db->execute($query);
    }
    
    /**
     * Obtenir l'adresse IP du client
     * 
     * @return string
     */
    private function getIpAddress()
    {
        // Vérifier les en-têtes de proxy
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Si plusieurs IPs (proxy), prendre la première
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Valider l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Bloquer manuellement une IP
     * 
     * @param string $action Type d'action
     * @param int $duration Durée en secondes
     * @return bool
     */
    public function block($action = 'login', $duration = 3600)
    {
        $ipAddress = $this->getIpAddress();
        $blockedUntil = date('Y-m-d H:i:s', time() + $duration);
        
        $query = "INSERT INTO {$this->table} (ip_address, action, attempts, blocked_until, last_attempt)
                  VALUES (?, ?, 999, ?, NOW())
                  ON DUPLICATE KEY UPDATE 
                  attempts = 999,
                  blocked_until = ?,
                  last_attempt = NOW()";
        
        return $this->db->execute($query, [$ipAddress, $action, $blockedUntil, $blockedUntil]);
    }
    
    /**
     * Débloquer une IP
     * 
     * @param string $ipAddress Adresse IP à débloquer
     * @param string $action Type d'action
     * @return bool
     */
    public function unblock($ipAddress, $action = 'login')
    {
        $query = "DELETE FROM {$this->table} 
                  WHERE ip_address = ? AND action = ?";
        
        return $this->db->execute($query, [$ipAddress, $action]);
    }
}

