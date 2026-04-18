<?php
/**
 * Système d'Alertes DDoS
 * Notifications par email et SMS lors des attaques
 */

class DDoSAlerts
{
    private $db;
    private $config;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = [
            'email_enabled' => DDOS_NOTIFICATIONS_ENABLED,
            'admin_email' => DDOS_ADMIN_EMAIL,
            'alert_threshold' => DDOS_ALERT_THRESHOLD,
            'smtp_host' => SMTP_HOST,
            'smtp_port' => SMTP_PORT,
            'smtp_user' => SMTP_USER,
            'smtp_pass' => SMTP_PASS,
            'smtp_from' => SMTP_FROM,
            'smtp_from_name' => SMTP_FROM_NAME
        ];
    }
    
    /**
     * Vérifier et envoyer les alertes si nécessaire
     */
    public function checkAndSendAlerts()
    {
        try {
            $stats = $this->getCurrentStats();
            
            // Vérifier les seuils d'alerte
            if ($stats['blocked_ips'] >= $this->config['alert_threshold']) {
                $this->sendHighAlert($stats);
            } elseif ($stats['blocked_ips'] > 0) {
                $this->sendLowAlert($stats);
            }
            
            // Vérifier les IPs très suspectes
            if ($stats['highly_suspicious'] > 0) {
                $this->sendSuspiciousActivityAlert($stats);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("DDoS Alerts Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir les statistiques actuelles
     */
    private function getCurrentStats()
    {
        $query = "
            SELECT 
                COUNT(*) as total_ips,
                COUNT(CASE WHEN blocked_until > NOW() THEN 1 END) as blocked_ips,
                COUNT(CASE WHEN suspicious_score > 5 THEN 1 END) as highly_suspicious,
                COUNT(CASE WHEN suspicious_score > 0 THEN 1 END) as suspicious_ips,
                AVG(suspicious_score) as avg_suspicious_score,
                MAX(suspicious_score) as max_suspicious_score
            FROM ddos_protection
        ";
        
        return $this->db->queryOne($query);
    }
    
    /**
     * Envoyer une alerte de niveau élevé
     */
    private function sendHighAlert($stats)
    {
        $subject = "🚨 ALERTE DDoS CRITIQUE - HubTech";
        $message = $this->buildAlertMessage($stats, 'CRITIQUE');
        
        $this->sendEmail($subject, $message);
        $this->logAlert('HIGH', $stats);
    }
    
    /**
     * Envoyer une alerte de niveau normal
     */
    private function sendLowAlert($stats)
    {
        $subject = "⚠️ Alerte DDoS - HubTech";
        $message = $this->buildAlertMessage($stats, 'NORMAL');
        
        $this->sendEmail($subject, $message);
        $this->logAlert('LOW', $stats);
    }
    
    /**
     * Envoyer une alerte d'activité suspecte
     */
    private function sendSuspiciousActivityAlert($stats)
    {
        $subject = "🔍 Activité Suspecte Détectée - HubTech";
        $message = $this->buildSuspiciousMessage($stats);
        
        $this->sendEmail($subject, $message);
        $this->logAlert('SUSPICIOUS', $stats);
    }
    
    /**
     * Construire le message d'alerte
     */
    private function buildAlertMessage($stats, $level)
    {
        $levelColor = $level === 'CRITIQUE' ? '#B71C1C' : '#FF9800';
        $levelIcon = $level === 'CRITIQUE' ? '🚨' : '⚠️';
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: {$levelColor}; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .stats { background: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
                .stat-item { display: flex; justify-content: space-between; margin: 5px 0; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 0.9rem; }
                .btn { display: inline-block; background: #1B5E20; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>{$levelIcon} Alerte DDoS - Niveau {$level}</h1>
                <p>HubTech - Protection DDoS Active</p>
            </div>
            
            <div class='content'>
                <h2>📊 Statistiques Actuelles</h2>
                <div class='stats'>
                    <div class='stat-item'>
                        <span><strong>IPs Total:</strong></span>
                        <span>{$stats['total_ips']}</span>
                    </div>
                    <div class='stat-item'>
                        <span><strong>IPs Bloquées:</strong></span>
                        <span style='color: #B71C1C; font-weight: bold;'>{$stats['blocked_ips']}</span>
                    </div>
                    <div class='stat-item'>
                        <span><strong>IPs Suspectes:</strong></span>
                        <span style='color: #FF9800; font-weight: bold;'>{$stats['suspicious_ips']}</span>
                    </div>
                    <div class='stat-item'>
                        <span><strong>Score Moyen:</strong></span>
                        <span>" . round($stats['avg_suspicious_score'], 2) . "</span>
                    </div>
                </div>
                
                <h2>🕐 Détails de l'Alerte</h2>
                <ul>
                    <li><strong>Heure:</strong> " . date('d/m/Y à H:i:s') . "</li>
                    <li><strong>Serveur:</strong> " . $_SERVER['SERVER_NAME'] . "</li>
                    <li><strong>Seuil d'alerte:</strong> {$this->config['alert_threshold']} IPs bloquées</li>
                </ul>
                
                <h2>🛡️ Actions Recommandées</h2>
                <ul>
                    <li>Vérifier le dashboard de monitoring</li>
                    <li>Analyser les logs d'activité</li>
                    <li>Considérer l'augmentation des limites de protection</li>
                    <li>Contacter le support technique si nécessaire</li>
                </ul>
                
                <a href='https://" . $_SERVER['SERVER_NAME'] . "/admin/ddos_monitoring.php' class='btn'>
                    📊 Accéder au Dashboard
                </a>
            </div>
            
            <div class='footer'>
                <p>HubTech - Système de Protection DDoS</p>
                <p>Cette alerte a été générée automatiquement</p>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }
    
    /**
     * Construire le message d'activité suspecte
     */
    private function buildSuspiciousMessage($stats)
    {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #FF9800; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .alert { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🔍 Activité Suspecte Détectée</h1>
                <p>HubTech - Surveillance DDoS</p>
            </div>
            
            <div class='content'>
                <div class='alert'>
                    <h3>⚠️ Attention</h3>
                    <p>Une activité suspecte a été détectée sur votre serveur HubTech.</p>
                </div>
                
                <h2>📊 Détails de l'Activité</h2>
                <ul>
                    <li><strong>IPs Très Suspectes:</strong> {$stats['highly_suspicious']}</li>
                    <li><strong>Score Maximum:</strong> {$stats['max_suspicious_score']}</li>
                    <li><strong>Score Moyen:</strong> " . round($stats['avg_suspicious_score'], 2) . "</li>
                    <li><strong>Heure de Détection:</strong> " . date('d/m/Y à H:i:s') . "</li>
                </ul>
                
                <p>Il est recommandé de vérifier le dashboard de monitoring pour plus de détails.</p>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }
    
    /**
     * Envoyer un email
     */
    private function sendEmail($subject, $message)
    {
        if (!$this->config['email_enabled']) {
            return false;
        }
        
        try {
            // Configuration SMTP
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $this->config['smtp_from_name'] . ' <' . $this->config['smtp_from'] . '>',
                'Reply-To: ' . $this->config['smtp_from'],
                'X-Mailer: HubTech DDoS Protection'
            ];
            
            // Envoyer l'email
            $result = mail($this->config['admin_email'], $subject, $message, implode("\r\n", $headers));
            
            if ($result) {
                error_log("DDoS Alert Email sent successfully to {$this->config['admin_email']}");
                return true;
            } else {
                error_log("Failed to send DDoS alert email to {$this->config['admin_email']}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logger l'alerte
     */
    private function logAlert($level, $stats)
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'blocked_ips' => $stats['blocked_ips'],
            'suspicious_ips' => $stats['suspicious_ips'],
            'total_ips' => $stats['total_ips']
        ];
        
        $logMessage = "DDoS Alert [{$level}]: " . json_encode($logEntry);
        error_log($logMessage);
        
        // Sauvegarder dans un fichier de log spécifique
        $logFile = __DIR__ . '/../logs/ddos_alerts.log';
        file_put_contents($logFile, $logMessage . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Envoyer une notification de test
     */
    public function sendTestAlert()
    {
        $testStats = [
            'total_ips' => 1,
            'blocked_ips' => 1,
            'suspicious_ips' => 1,
            'highly_suspicious' => 1,
            'avg_suspicious_score' => 5.0,
            'max_suspicious_score' => 8
        ];
        
        $subject = "🧪 Test d'Alerte DDoS - HubTech";
        $message = $this->buildAlertMessage($testStats, 'TEST');
        
        return $this->sendEmail($subject, $message);
    }
    
    /**
     * Obtenir l'historique des alertes
     */
    public function getAlertHistory($days = 7)
    {
        $logFile = __DIR__ . '/../logs/ddos_alerts.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $alerts = [];
        
        foreach ($lines as $line) {
            if (strpos($line, 'DDoS Alert') !== false) {
                $alerts[] = $line;
            }
        }
        
        return array_slice($alerts, -50); // Dernières 50 alertes
    }
}
