<?php
/**
 * Classe DDoSProtection - Protection contre les attaques DDoS
 * Limitation globale des requêtes par IP et détection des patterns suspects
 */

class DDoSProtection
{
    private $db;
    private $table = 'ddos_protection';
    
    // Limites par défaut
    private $limits = [
        'requests_per_minute' => 60,      // 60 requêtes par minute
        'requests_per_hour' => 1000,     // 1000 requêtes par heure
        'requests_per_day' => 10000,     // 10000 requêtes par jour
        'concurrent_connections' => 10,   // 10 connexions simultanées
        'suspicious_threshold' => 5       // Seuil de suspicion
    ];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->createTableIfNotExists();
    }
    
    /**
     * Créer la table de protection DDoS
     */
    private function createTableIfNotExists()
    {
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            request_count_minute INT DEFAULT 0,
            request_count_hour INT DEFAULT 0,
            request_count_day INT DEFAULT 0,
            last_request_minute DATETIME DEFAULT NULL,
            last_request_hour DATETIME DEFAULT NULL,
            last_request_day DATETIME DEFAULT NULL,
            blocked_until DATETIME DEFAULT NULL,
            suspicious_score INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_ip (ip_address),
            INDEX idx_blocked (blocked_until),
            INDEX idx_suspicious (suspicious_score)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->execute($query);
    }
    
    /**
     * Vérifier si l'IP est autorisée à faire des requêtes
     * 
     * @return array ['allowed' => bool, 'reason' => string, 'blocked_until' => string|null]
     */
    public function checkRequest()
    {
        $ipAddress = $this->getClientIp();
        
        // Nettoyer les anciennes entrées
        $this->cleanup();
        
        // Récupérer ou créer l'enregistrement
        $record = $this->getOrCreateRecord($ipAddress);
        
        // Vérifier si l'IP est bloquée
        if ($record['blocked_until'] && strtotime($record['blocked_until']) > time()) {
            return [
                'allowed' => false,
                'reason' => 'IP temporairement bloquée',
                'blocked_until' => $record['blocked_until']
            ];
        }
        
        // Vérifier les limites par minute
        if ($this->isLimitExceeded($record, 'minute')) {
            $this->blockIp($ipAddress, 300); // Bloquer 5 minutes
            return [
                'allowed' => false,
                'reason' => 'Limite de requêtes par minute dépassée',
                'blocked_until' => date('Y-m-d H:i:s', time() + 300)
            ];
        }
        
        // Vérifier les limites par heure
        if ($this->isLimitExceeded($record, 'hour')) {
            $this->blockIp($ipAddress, 3600); // Bloquer 1 heure
            return [
                'allowed' => false,
                'reason' => 'Limite de requêtes par heure dépassée',
                'blocked_until' => date('Y-m-d H:i:s', time() + 3600)
            ];
        }
        
        // Vérifier les limites par jour
        if ($this->isLimitExceeded($record, 'day')) {
            $this->blockIp($ipAddress, 86400); // Bloquer 24 heures
            return [
                'allowed' => false,
                'reason' => 'Limite de requêtes par jour dépassée',
                'blocked_until' => date('Y-m-d H:i:s', time() + 86400)
            ];
        }
        
        // Enregistrer la requête
        $this->recordRequest($ipAddress);
        
        // Analyser les patterns suspects
        $this->analyzeSuspiciousActivity($ipAddress);
        
        return [
            'allowed' => true,
            'reason' => 'Requête autorisée',
            'blocked_until' => null
        ];
    }
    
    /**
     * Vérifier si une limite est dépassée
     */
    private function isLimitExceeded($record, $period)
    {
        $now = time();
        
        switch ($period) {
            case 'minute':
                if ($record['last_request_minute']) {
                    $lastMinute = strtotime($record['last_request_minute']);
                    if ($now - $lastMinute < 60) {
                        return $record['request_count_minute'] >= $this->limits['requests_per_minute'];
                    }
                }
                return false;
                
            case 'hour':
                if ($record['last_request_hour']) {
                    $lastHour = strtotime($record['last_request_hour']);
                    if ($now - $lastHour < 3600) {
                        return $record['request_count_hour'] >= $this->limits['requests_per_hour'];
                    }
                }
                return false;
                
            case 'day':
                if ($record['last_request_day']) {
                    $lastDay = strtotime($record['last_request_day']);
                    if ($now - $lastDay < 86400) {
                        return $record['request_count_day'] >= $this->limits['requests_per_day'];
                    }
                }
                return false;
        }
        
        return false;
    }
    
    /**
     * Récupérer ou créer un enregistrement pour l'IP
     */
    private function getOrCreateRecord($ipAddress)
    {
        $query = "SELECT * FROM {$this->table} WHERE ip_address = ? LIMIT 1";
        $record = $this->db->queryOne($query, [$ipAddress]);
        
        if (!$record) {
            $insertQuery = "INSERT INTO {$this->table} (ip_address) VALUES (?)";
            $this->db->execute($insertQuery, [$ipAddress]);
            
            $record = $this->db->queryOne($query, [$ipAddress]);
        }
        
        return $record;
    }
    
    /**
     * Enregistrer une requête
     */
    private function recordRequest($ipAddress)
    {
        $now = date('Y-m-d H:i:s');
        
        $query = "UPDATE {$this->table} SET 
                    request_count_minute = CASE 
                        WHEN last_request_minute IS NULL OR last_request_minute < DATE_SUB(NOW(), INTERVAL 1 MINUTE) 
                        THEN 1 
                        ELSE request_count_minute + 1 
                    END,
                    request_count_hour = CASE 
                        WHEN last_request_hour IS NULL OR last_request_hour < DATE_SUB(NOW(), INTERVAL 1 HOUR) 
                        THEN 1 
                        ELSE request_count_hour + 1 
                    END,
                    request_count_day = CASE 
                        WHEN last_request_day IS NULL OR last_request_day < DATE_SUB(NOW(), INTERVAL 1 DAY) 
                        THEN 1 
                        ELSE request_count_day + 1 
                    END,
                    last_request_minute = ?,
                    last_request_hour = ?,
                    last_request_day = ?,
                    updated_at = NOW()
                  WHERE ip_address = ?";
        
        $this->db->execute($query, [$now, $now, $now, $ipAddress]);
    }
    
    /**
     * Analyser l'activité suspecte
     */
    private function analyzeSuspiciousActivity($ipAddress)
    {
        // Analyser les patterns de requêtes
        $query = "SELECT * FROM {$this->table} WHERE ip_address = ?";
        $record = $this->db->queryOne($query, [$ipAddress]);
        
        $suspiciousScore = 0;
        
        // Score basé sur la fréquence des requêtes
        if ($record['request_count_minute'] > 30) $suspiciousScore += 2;
        if ($record['request_count_hour'] > 500) $suspiciousScore += 3;
        if ($record['request_count_day'] > 5000) $suspiciousScore += 5;
        
        // Mettre à jour le score de suspicion
        $updateQuery = "UPDATE {$this->table} SET suspicious_score = ? WHERE ip_address = ?";
        $this->db->execute($updateQuery, [$suspiciousScore, $ipAddress]);
        
        // Si score trop élevé, bloquer temporairement
        if ($suspiciousScore >= $this->limits['suspicious_threshold']) {
            $this->blockIp($ipAddress, 1800); // Bloquer 30 minutes
        }
    }
    
    /**
     * Bloquer une IP
     */
    private function blockIp($ipAddress, $duration)
    {
        $blockedUntil = date('Y-m-d H:i:s', time() + $duration);
        
        $query = "UPDATE {$this->table} SET blocked_until = ? WHERE ip_address = ?";
        $this->db->execute($query, [$blockedUntil, $ipAddress]);
        
        // Logger l'événement
        error_log("DDoS Protection: IP {$ipAddress} blocked until {$blockedUntil}");
    }
    
    /**
     * Nettoyer les anciennes entrées
     */
    private function cleanup()
    {
        $query = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $this->db->execute($query);
    }
    
    /**
     * Obtenir l'IP du client
     */
    private function getClientIp()
    {
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
                
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Obtenir les statistiques de protection
     */
    public function getStats()
    {
        $query = "SELECT 
                    COUNT(*) as total_ips,
                    COUNT(CASE WHEN blocked_until > NOW() THEN 1 END) as blocked_ips,
                    COUNT(CASE WHEN suspicious_score > 0 THEN 1 END) as suspicious_ips,
                    AVG(suspicious_score) as avg_suspicious_score
                  FROM {$this->table}";
        
        return $this->db->queryOne($query);
    }
    
    /**
     * Débloquer une IP manuellement
     */
    public function unblockIp($ipAddress)
    {
        $query = "UPDATE {$this->table} SET blocked_until = NULL WHERE ip_address = ?";
        return $this->db->execute($query, [$ipAddress]);
    }
}
