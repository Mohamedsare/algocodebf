<?php

/**
 * Fichier de configuration principal de HubTech
 * Contient tous les paramètres de configuration de l'application
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'u330028981_algo');
define('DB_USER', 'u330028981_db');
define('DB_PASS', 'Mohamedsare1!');
define('DB_CHARSET', 'utf8mb4');

// Configuration de l'application
define('APP_NAME', 'AlgoCodeBF');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'production'); // development ou production

// Configuration de sécurité
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Configuration des uploads
define('MAX_FILE_SIZE', 5242880); // 5 MB
define('MAX_VIDEO_SIZE', 524288000); // 500 MB pour les vidéos de formation
define('MAX_CV_SIZE', 2097152); // 2 MB
define('MAX_VIDEOS_PER_TUTORIAL', 50); // Nombre maximum de vidéos par tutoriel
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/mpeg', 'video/webm', 'video/x-msvideo', 'video/quicktime', 'video/x-ms-wmv']);

// Configuration email (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre-email@gmail.com');
define('SMTP_PASS', 'votre-mot-de-passe');
define('SMTP_FROM', 'noreply@algocodebf.bf');
define('SMTP_FROM_NAME', 'AlgoCodeBF BF');

// Préfixe téléphonique du Burkina Faso
define('PHONE_PREFIX', '+226');

// Pagination
define('POSTS_PER_PAGE', 20);
define('USERS_PER_PAGE', 30);
define('JOBS_PER_PAGE', 12); // Nombre d'offres d'emploi par page

// Activer le mode debug en développement
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Africa/Ouagadougou');
