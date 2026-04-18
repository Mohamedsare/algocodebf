<?php
/**
 * Point d'entrée principal de l'application AlgoCodeBF
 * Architecture MVC sécurisée et modulaire
 * 
 * @author Mohamed SARE
 * @date 09/10/2025
 */

// Détecter l'environnement de production (HTTPS)
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true, // Mettre à true en production avec HTTPS
    'cookie_samesite' => 'Strict'
]);

// Démarrer la session de manière sécurisée
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => $isProduction, // TRUE en production HTTPS, FALSE en local
    'cookie_samesite' => 'Strict',
    'cookie_lifetime' => 86400 // 24 heures
]);

// Définir les constantes de l'application
define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('VIEWS', APP . '/Views');
define('UPLOADS', ROOT . '/public/uploads');  // UPLOADS dans public pour être accessible
define('BASE_URL', 'https://algocodebf.com/');

// Charger le fichier de configuration
require_once ROOT . '/config/config.php';

// Charger les fonctions helper
require_once APP . '/Helpers/functions.php';
require_once APP . '/Helpers/VisitorTracker.php';
require_once APP . '/Helpers/SiteSettings.php';

// Charger l'autoloader
require_once APP . '/Core/Autoloader.php';
spl_autoload_register(['Autoloader', 'load']);

// Charger les paramètres du site et les rendre disponibles globalement
$GLOBALS['site_settings'] = SiteSettings::all();

// Tracker les visiteurs (analytics)
VisitorTracker::track();

// Initialiser l'application
$app = new App();