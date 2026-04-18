<?php

/**
 * Script de scraping des offres d'emploi
 * À exécuter manuellement ou via cron job
 * 
 * Usage: 
 * - En production: https://algocodebf.com/scrape_jobs?token=hubtech_scraper_2025
 * - En local: http://localhost/HubTech/scrape_jobs?token=hubtech_scraper_2025
 * - Via CLI: php public/scrape_jobs.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300); // 5 minutes max

// Définir les constantes nécessaires
define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');

// Détecter automatiquement BASE_URL selon l'environnement
// En production (algocodebf.com), BASE_URL est vide (racine)
// En développement local, BASE_URL est '/HubTech'
$isProduction = (
    isset($_SERVER['HTTP_HOST']) &&
    (strpos($_SERVER['HTTP_HOST'], 'algocodebf.com') !== false ||
        strpos($_SERVER['HTTP_HOST'], 'www.algocodebf.com') !== false)
);
define('BASE_URL', $isProduction ? '' : '/HubTech');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Helpers/JobScraper.php';

// Sécurité : Vérifier qu'on est en local ou avec un token
$isCLI = php_sapi_name() === 'cli';
$allowedIPs = ['127.0.0.1', '::1', 'localhost'];
$token = $_GET['token'] ?? '';
$validToken = 'hubtech_scraper_2025'; // Token secret pour exécution

if (!$isCLI) {
    if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIPs) && $token !== $validToken) {
        http_response_code(403);
        die('❌ Accès refusé. Ce script ne peut être exécuté qu\'en local ou avec un token valide.');
    }
}

// Interface HTML pour le navigateur
if (!$isCLI) {
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <title>Scraping Jobs - AlgoCodeBF</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .success { color: #28a745; }
            .error { color: #dc3545; }
            .info { color: #17a2b8; }
            pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
            .stats { margin: 20px 0; padding: 15px; background: #e9ecef; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h1>🔍 Scraping des Offres d'Emploi</h1>
        <p class='info'>Début du scraping... Cela peut prendre quelques minutes.</p>
        <hr>";
}

// Exécuter le scraping
try {
    $scraper = new JobScraper();

    $pagesToScrape = isset($_GET['pages']) ? (int)$_GET['pages'] : 3;

    if (!$isCLI) {
        echo "<p>📄 Nombre de pages à scraper: <strong>$pagesToScrape</strong></p>";
        echo "<p>🌐 Site: <strong>EmploiBurkina.com</strong></p>";
        echo "<hr>";
    }

    $startTime = microtime(true);
    $results = $scraper->scrapeEmploiBurkinaAll($pagesToScrape);
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    // Afficher les résultats
    if ($isCLI) {
        echo "=== RÉSULTATS DU SCRAPING ===\n\n";
        echo "Total d'offres ajoutées: " . $results['total_jobs'] . "\n";
        echo "Durée: {$duration}s\n\n";

        foreach ($results['details'] as $source => $detail) {
            if ($detail['success']) {
                echo "✓ {$detail['source']}: {$detail['count']} offres\n";
            } else {
                echo "✗ {$detail['source']}: Erreur - " . ($detail['error'] ?? 'Inconnue') . "\n";
            }
        }

        if (!empty($results['errors'])) {
            echo "\nErreurs:\n";
            foreach ($results['errors'] as $error) {
                echo "  - $error\n";
            }
        }
    } else {
        echo "<div class='stats'>";
        echo "<h2>📊 Résultats</h2>";
        echo "<p><strong>Total d'offres ajoutées:</strong> <span class='success'>{$results['total_jobs']}</span></p>";
        echo "<p><strong>Durée:</strong> {$duration}s</p>";
        echo "</div>";

        echo "<h3>Détails par source:</h3>";
        echo "<ul>";
        foreach ($results['details'] as $source => $detail) {
            if ($detail['success']) {
                echo "<li class='success'>✓ <strong>{$detail['source']}</strong>: {$detail['count']} offres ajoutées";
                if (isset($detail['total_found'])) {
                    echo " (sur {$detail['total_found']} trouvées)";
                }
                echo "</li>";
            } else {
                echo "<li class='error'>✗ <strong>{$detail['source']}</strong>: Erreur - " . htmlspecialchars($detail['error'] ?? 'Inconnue') . "</li>";
            }
        }
        echo "</ul>";

        if (!empty($results['errors'])) {
            echo "<h3 class='error'>Erreurs:</h3>";
            echo "<ul>";
            foreach ($results['errors'] as $error) {
                echo "<li class='error'>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }

        echo "<h3>Réponse JSON complète:</h3>";
        echo "<pre>" . htmlspecialchars(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";

        echo "<hr>";
        echo "<p><a href='" . BASE_URL . "/job/index'>Voir les offres</a></p>";
        echo "<p><a href='?token=$validToken&pages=$pagesToScrape'>Relancer le scraping</a></p>";
    }
} catch (Exception $e) {
    $errorMsg = "Erreur fatale: " . $e->getMessage();

    if ($isCLI) {
        echo "❌ $errorMsg\n";
    } else {
        echo "<p class='error'>❌ $errorMsg</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }

    error_log($errorMsg);
}

if (!$isCLI) {
    echo "</body></html>";
}