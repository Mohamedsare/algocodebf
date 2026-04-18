<?php
/**
 * Contrôleur pour gérer le scraping automatique des offres d'emploi
 */

class JobScraperController extends Controller
{
    private $scraper;
    
    public function __construct()
    {
        require_once APP . '/Helpers/JobScraper.php';
        $this->scraper = new JobScraper();
    }
    
    /**
     * Exécuter le scraping (accessible via URL avec token de sécurité)
     */
    public function run()
    {
        // Sécurité : Vérifier le token ou l'IP
        $token = $_GET['token'] ?? '';
        $validToken = 'hubtech_scraper_2025'; // Token secret
        $allowedIPs = ['127.0.0.1', '::1', 'localhost'];
        
        if ($token !== $validToken && !in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIPs)) {
            http_response_code(403);
            die('❌ Accès refusé. Token invalide.');
        }
        
        // Augmenter le temps d'exécution
        set_time_limit(300); // 5 minutes
        
        $pagesToScrape = isset($_GET['pages']) ? (int)$_GET['pages'] : 3;
        
        // Exécuter le scraping
        $results = $this->scraper->scrapeEmploiBurkinaAll($pagesToScrape);
        
        // Retourner les résultats en JSON
        header('Content-Type: application/json');
        echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Exécuter le scraping de tous les sites
     */
    public function runAll()
    {
        // Sécurité
        $token = $_GET['token'] ?? '';
        $validToken = 'hubtech_scraper_2025';
        $allowedIPs = ['127.0.0.1', '::1', 'localhost'];
        
        if ($token !== $validToken && !in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIPs)) {
            http_response_code(403);
            die('❌ Accès refusé. Token invalide.');
        }
        
        set_time_limit(300);
        
        $pagesToScrape = isset($_GET['pages']) ? (int)$_GET['pages'] : 3;
        
        $results = $this->scraper->scrapeAll($pagesToScrape);
        
        header('Content-Type: application/json');
        echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

