<?php
/**
 * Script d'intégration de la protection DDoS
 * À exécuter une seule fois pour configurer la protection
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/ddos_config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Helpers/DDoSProtection.php';

echo "🛡️ Configuration de la protection DDoS pour HubTech...\n\n";

try {
    // Initialiser la base de données
    $db = Database::getInstance();
    
    // Créer la table de protection DDoS
    $ddosProtection = new DDoSProtection();
    echo "✅ Table de protection DDoS créée avec succès\n";
    
    // Créer le dossier de logs s'il n'existe pas
    $logDir = dirname(DDOS_LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
        echo "✅ Dossier de logs créé : {$logDir}\n";
    }
    
    // Créer le fichier .htaccess pour la protection
    $htaccessContent = "# Protection DDoS - HubTech
# Limitation des requêtes par IP

# Limiter les requêtes par IP
<RequireAll>
    Require all granted
    Require not ip 192.168.1.0/24
</RequireAll>

# Limiter la taille des requêtes
LimitRequestBody 10485760

# Limiter les méthodes HTTP
<LimitExcept GET POST>
    Require all denied
</LimitExcept>

# Headers de sécurité supplémentaires
Header always set X-DDoS-Protection \"Active\"
Header always set X-Rate-Limit \"60/minute\"

# Protection contre les bots malveillants
RewriteEngine On
RewriteCond %{HTTP_USER_AGENT} ^$ [OR]
RewriteCond %{HTTP_USER_AGENT} (bot|crawler|spider|scraper) [NC]
RewriteRule ^(.*)$ - [F,L]

# Bloquer les requêtes suspectes
RewriteCond %{QUERY_STRING} (union|select|insert|delete|update|drop|create|alter) [NC]
RewriteRule ^(.*)$ - [F,L]
";
    
    file_put_contents(__DIR__ . '/../public/.htaccess_ddos', $htaccessContent);
    echo "✅ Fichier .htaccess de protection créé\n";
    
    // Créer un script de monitoring
    $monitoringScript = '<?php
/**
 * Script de monitoring DDoS
 * À exécuter périodiquement pour surveiller les attaques
 */

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/ddos_config.php";
require_once __DIR__ . "/../app/Core/Database.php";
require_once __DIR__ . "/../app/Helpers/DDoSProtection.php";

if (!DDOS_MONITORING_ENABLED) {
    exit("Monitoring DDoS désactivé\n");
}

try {
    $ddosProtection = new DDoSProtection();
    $stats = $ddosProtection->getStats();
    
    echo "📊 Statistiques DDoS Protection:\n";
    echo "   - IPs totales: " . $stats["total_ips"] . "\n";
    echo "   - IPs bloquées: " . $stats["blocked_ips"] . "\n";
    echo "   - IPs suspectes: " . $stats["suspicious_ips"] . "\n";
    echo "   - Score moyen: " . round($stats["avg_suspicious_score"], 2) . "\n";
    
    // Alerter si trop d\'IPs bloquées
    if ($stats["blocked_ips"] > DDOS_ALERT_THRESHOLD) {
        echo "⚠️  ALERTE: " . $stats["blocked_ips"] . " IPs bloquées!\n";
        
        if (DDOS_NOTIFICATIONS_ENABLED) {
            $subject = "Alerte DDoS - HubTech";
            $message = "Nombre élevé d\'IPs bloquées: " . $stats["blocked_ips"];
            // mail(DDOS_ADMIN_EMAIL, $subject, $message);
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur monitoring: " . $e->getMessage() . "\n";
}
';
    
    file_put_contents(__DIR__ . '/monitor_ddos.php', $monitoringScript);
    echo "✅ Script de monitoring créé\n";
    
    // Créer un script de nettoyage
    $cleanupScript = '<?php
/**
 * Script de nettoyage DDoS
 * À exécuter quotidiennement pour nettoyer les anciennes données
 */

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/ddos_config.php";
require_once __DIR__ . "/../app/Core/Database.php";
require_once __DIR__ . "/../app/Helpers/DDoSProtection.php";

try {
    $ddosProtection = new DDoSProtection();
    
    // Nettoyer les anciennes entrées (appelé automatiquement)
    echo "🧹 Nettoyage des anciennes données DDoS...\n";
    
    $db = Database::getInstance();
    $query = "DELETE FROM ddos_protection WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $result = $db->execute($query);
    
    echo "✅ Nettoyage terminé\n";
    
} catch (Exception $e) {
    echo "❌ Erreur nettoyage: " . $e->getMessage() . "\n";
}
';
    
    file_put_contents(__DIR__ . '/cleanup_ddos.php', $cleanupScript);
    echo "✅ Script de nettoyage créé\n";
    
    echo "\n🎉 Protection DDoS configurée avec succès!\n\n";
    
    echo "📋 Instructions d'utilisation:\n";
    echo "1. Intégrez le middleware dans index.php:\n";
    echo "   require_once 'app/Middleware/DDoSProtectionMiddleware.php';\n\n";
    
    echo "2. Configurez un cron job pour le monitoring:\n";
    echo "   */5 * * * * php " . __DIR__ . "/monitor_ddos.php\n\n";
    
    echo "3. Configurez un cron job pour le nettoyage:\n";
    echo "   0 2 * * * php " . __DIR__ . "/cleanup_ddos.php\n\n";
    
    echo "4. Surveillez les logs dans: " . DDOS_LOG_FILE . "\n\n";
    
    echo "⚠️  Important: Testez la protection en mode développement avant la production!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la configuration: " . $e->getMessage() . "\n";
    exit(1);
}
