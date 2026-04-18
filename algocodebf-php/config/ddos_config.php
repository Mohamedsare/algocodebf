<?php
/**
 * Configuration de la protection DDoS
 * Paramètres personnalisables pour HubTech
 */

// Activer/désactiver la protection DDoS
define('DDOS_PROTECTION_ENABLED', true);

// Limites de requêtes par IP
define('DDOS_LIMITS', [
    'requests_per_minute' => 60,      // 60 requêtes par minute
    'requests_per_hour' => 1000,       // 1000 requêtes par heure  
    'requests_per_day' => 10000,      // 10000 requêtes par jour
    'concurrent_connections' => 10,    // 10 connexions simultanées
    'suspicious_threshold' => 5        // Seuil de suspicion (0-10)
]);

// Durées de blocage (en secondes)
define('DDOS_BLOCK_DURATIONS', [
    'minute_limit_exceeded' => 300,    // 5 minutes
    'hour_limit_exceeded' => 3600,    // 1 heure
    'day_limit_exceeded' => 86400,    // 24 heures
    'suspicious_activity' => 1800     // 30 minutes
]);

// IPs exemptées de la protection DDoS (admins, services)
define('DDOS_EXEMPTED_IPS', [
    '127.0.0.1',           // Localhost
    '::1',                 // IPv6 localhost
    // Ajoutez ici les IPs des administrateurs
    // '192.168.1.100',
    // '10.0.0.50'
]);

// Pages exemptées de la protection DDoS
define('DDOS_EXEMPTED_PAGES', [
    '/api/health',          // Endpoint de santé
    '/api/status',          // Statut de l'API
    '/admin/login',         // Connexion admin
    '/auth/login'           // Connexion utilisateur
]);

// Configuration du logging
define('DDOS_LOGGING_ENABLED', true);
define('DDOS_LOG_FILE', __DIR__ . '/../logs/ddos.log');

// Configuration du monitoring
define('DDOS_MONITORING_ENABLED', true);
define('DDOS_ALERT_THRESHOLD', 100);  // Nombre d'IPs bloquées pour alerter

// Configuration des notifications
define('DDOS_NOTIFICATIONS_ENABLED', true);
define('DDOS_ADMIN_EMAIL', 'mhdcode7@gmail.com');

// Mode de fonctionnement
define('DDOS_MODE', 'production'); // 'development' ou 'production'

// En mode développement, les limites sont plus souples
if (DDOS_MODE === 'development') {
    define('DDOS_LIMITS_DEV', [
        'requests_per_minute' => 120,
        'requests_per_hour' => 2000,
        'requests_per_day' => 20000,
        'concurrent_connections' => 20,
        'suspicious_threshold' => 8
    ]);
}
