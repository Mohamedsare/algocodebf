<?php
/**
 * JobScraper - Scraper d'offres d'emploi depuis plusieurs sites burkinabés
 * 
 * Sites supportés :
 * - EmploiBurkina.com (offres IT, stages, freelance, hackathons, formations)
 * - Global Expertise (recrutor.pro)
 * - Travail-Burkina.com
 * 
 * Inspiré du scraper Python pour une extraction précise
 */

class JobScraper
{
    private $db;
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    private $timeout = 30;
    
    // Configuration du bot scraper (utilisé comme company_id)
    private $botUserId = 1; // ID de l'administrateur ou créer un user "Bot Scraper"
    private $baseUrl = "https://www.emploiburkina.com";
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        // Vérifier et créer l'utilisateur bot si nécessaire
        $this->ensureBotUser();
    }
    
    /**
     * S'assurer que l'utilisateur bot existe
     */
    private function ensureBotUser()
    {
        $bot = $this->db->queryOne(
            "SELECT id FROM users WHERE email = ?",
            ['scraper@hubtech.bf']
        );
        
        if (!$bot) {
            // Créer l'utilisateur bot avec tous les champs requis
            $passwordHash = password_hash('bot_scraper_' . time(), PASSWORD_DEFAULT);
            $this->db->execute(
                "INSERT INTO users (email, password_hash, prenom, nom, phone, role, status, email_verified, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    'scraper@hubtech.bf',
                    $passwordHash,
                    'Bot',
                    'Scraper',
                    '+22600000000', // Numéro de téléphone par défaut pour le bot
                    'company',
                    'active',
                    1, // email_verified = true
                    date('Y-m-d H:i:s')
                ]
            );
            $bot = $this->db->queryOne("SELECT id FROM users WHERE email = ?", ['scraper@hubtech.bf']);
        }
        
        if ($bot) {
            $this->botUserId = $bot['id'];
        }
    }

    /**
     * Récupérer le contenu HTML d'une URL
     */
    private function fetchContent($url)
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: fr-FR,fr;q=0.9,en;q=0.8',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
            ],
            CURLOPT_ENCODING => 'gzip',
        ]);
        
        $html = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Erreur cURL: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Code HTTP: $httpCode");
        }
        
        return $html;
    }

    /**
     * Extraire du texte entre deux délimiteurs
     */
    private function extractBetween($html, $start, $end)
    {
        $startPos = strpos($html, $start);
        if ($startPos === false) return '';
        
        $startPos += strlen($start);
        $endPos = strpos($html, $end, $startPos);
        
        if ($endPos === false) return '';
        
        return substr($html, $startPos, $endPos - $startPos);
    }

    /**
     * Nettoyer le texte HTML
     */
    private function cleanText($text)
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Vérifier si une offre existe déjà (par URL externe ou titre similaire)
     */
    private function jobExists($title, $externalLink)
    {
        // Vérifier par URL externe (plus fiable)
        if (!empty($externalLink)) {
            $result = $this->db->queryOne(
                "SELECT id FROM jobs WHERE external_link = ?",
                [$externalLink]
            );
            if ($result !== false) {
                return true;
            }
        }
        
        // Vérifier par titre similaire (pour éviter les doublons avec titres légèrement différents)
        $result = $this->db->queryOne(
            "SELECT id FROM jobs WHERE title = ? AND company_id = ?",
            [$title, $this->botUserId]
        );
        return $result !== false;
    }

    /**
     * Insérer une offre dans la base de données
     */
    private function insertJob($data)
    {
        // Vérifier si l'offre existe déjà
        if ($this->jobExists($data['title'], $data['external_link'] ?? '')) {
            return false; // Offre déjà existante
        }

        // Valeurs par défaut
        $defaults = [
            'company_id' => $this->botUserId,
            'type' => 'emploi',
            'city' => 'Ouagadougou',
            'skills_required' => json_encode([]),
            'salary_range' => null,
            'deadline' => null,
            'contact_email' => null,
            'contact_phone' => null,
            'status' => 'active',
        ];

        $jobData = array_merge($defaults, $data);

        try {
            $this->db->execute(
                "INSERT INTO jobs (company_id, type, title, description, city, skills_required, 
                 salary_range, deadline, contact_email, contact_phone, external_link, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $jobData['company_id'],
                    $jobData['type'],
                    $jobData['title'],
                    $jobData['description'],
                    $jobData['city'],
                    $jobData['skills_required'],
                    $jobData['salary_range'],
                    $jobData['deadline'],
                    $jobData['contact_email'],
                    $jobData['contact_phone'],
                    $jobData['external_link'],
                    $jobData['status']
                ]
            );
            return true;
        } catch (Exception $e) {
            error_log("Erreur insertion job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * SCRAPER 1: Global Expertise (recrutor.pro)
     */
    public function scrapeGlobalExpertise()
    {
        $url = 'https://www.recrutor.pro/globalexpertise/metier/offres_emploi_informatique_9.html';
        $source = 'globalexpertise';
        $count = 0;

        try {
            $html = $this->fetchContent($url);
            
            // Parser le HTML pour extraire les offres
            // Structure: <div class="offre"> avec titre, lieu, type, description
            preg_match_all('/<h3[^>]*>(.*?)<\/h3>.*?<span[^>]*>(.*?)<\/span>.*?<p[^>]*>(.*?)<\/p>/s', $html, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $title = $this->cleanText($match[1]);
                $location = $this->cleanText($match[2]);
                $description = $this->cleanText($match[3]);
                
                if (empty($title) || strlen($title) < 5) continue;
                
                // Déterminer le type
                $type = 'emploi';
                if (stripos($title, 'stage') !== false) $type = 'stage';
                
                // Déterminer la ville
                $city = 'Ouagadougou';
                if (stripos($location, 'Bobo') !== false) $city = 'Bobo-Dioulasso';
                elseif (!empty($location)) $city = $location;
                
                $jobData = [
                    'title' => $title,
                    'description' => $description ?: 'Voir le lien externe pour plus de détails.',
                    'type' => $type,
                    'city' => $city,
                    'external_link' => $url,
                    'source' => $source,
                ];
                
                if ($this->insertJob($jobData)) {
                    $count++;
                }
            }
            
            return ['success' => true, 'count' => $count, 'source' => 'Global Expertise'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'source' => 'Global Expertise'];
        }
    }

    /**
     * SCRAPER 2: EmploiBurkina.com - Offres IT avec pagination
     * Inspiré du scraper Python
     */
    public function scrapeEmploiBurkinaIT($pagesToScrape = 3)
    {
        $source = 'emploiburkina';
        $count = 0;
        $allJobs = [];

        try {
            for ($page = 0; $page < $pagesToScrape; $page++) {
                // Construire l'URL avec pagination
                if ($page == 0) {
                    $url = $this->baseUrl . "/recherche-jobs-burkina-faso/Ouagadougou?f%5B0%5D=im_field_offre_metiers%3A31";
                } else {
                    $url = $this->baseUrl . "/recherche-jobs-burkina-faso/Ouagadougou?f%5B0%5D=im_field_offre_metiers%3A31&page=" . $page;
                }

                try {
                    $html = $this->fetchContent($url);
                    $jobs = $this->extractJobsFromEmploiBurkinaPage($html, $source);
                    $allJobs = array_merge($allJobs, $jobs);
                    
                    // Vérifier s'il y a une page suivante
                    if (strpos($html, 'pager-next') === false && $page > 0) {
                        break;
                    }
                    
                    // Délai respectueux entre les requêtes
                    sleep(1);
                    
                } catch (Exception $e) {
                    error_log("Erreur scraping page $page: " . $e->getMessage());
                    continue;
                }
            }
            
            // Insérer les offres dans la base
            foreach ($allJobs as $jobData) {
                if ($this->insertJob($jobData)) {
                    $count++;
                }
            }
            
            return ['success' => true, 'count' => $count, 'source' => 'EmploiBurkina IT', 'total_found' => count($allJobs)];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'source' => 'EmploiBurkina IT'];
        }
    }
    
    /**
     * Extraire les offres d'une page EmploiBurkina
     */
    private function extractJobsFromEmploiBurkinaPage($html, $source)
    {
        $jobs = [];
        
        // Utiliser DOMDocument pour parser le HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Trouver toutes les cartes d'offres
        $jobCards = $xpath->query("//div[contains(@class, 'card') and contains(@class, 'card-job')]");
        
        foreach ($jobCards as $card) {
            try {
                $job = [];
                
                // Titre et URL
                $titleLink = $xpath->query(".//h3//a", $card)->item(0);
                if ($titleLink) {
                    $job['titre'] = $this->cleanText($titleLink->textContent);
                    $href = $titleLink->getAttribute('href');
                    $job['url'] = $this->resolveUrl($href);
                }
                
                // Entreprise
                $companyElem = $xpath->query(".//div[contains(@class, 'card-job-company') and contains(@class, 'company-name')]", $card)->item(0);
                if ($companyElem) {
                    $job['entreprise'] = $this->cleanText($companyElem->textContent);
                    $companyLink = $xpath->query(".//a", $companyElem)->item(0);
                    if ($companyLink) {
                        $job['entreprise_url'] = $this->resolveUrl($companyLink->getAttribute('href'));
                    }
                }
                
                // Logo
                $logoElem = $xpath->query(".//picture//img | .//img[contains(@class, 'logo')]", $card)->item(0);
                if ($logoElem) {
                    $job['logo'] = $this->resolveUrl($logoElem->getAttribute('src'));
                }
                
                // Description
                $descElem = $xpath->query(".//div[contains(@class, 'card-job-description')]//p", $card)->item(0);
                if ($descElem) {
                    $job['description'] = $this->cleanText($descElem->textContent);
                }
                
                // Détails (niveau d'études, expérience, contrat, région, compétences)
                $details = $xpath->query(".//ul//li", $card);
                foreach ($details as $detail) {
                    $text = $this->cleanText($detail->textContent);
                    $strong = $xpath->query(".//strong", $detail)->item(0);
                    $value = $strong ? $this->cleanText($strong->textContent) : '';
                    
                    if (stripos($text, 'Niveau d') !== false && stripos($text, 'études') !== false) {
                        $job['niveau_etudes'] = $value;
                    } elseif (stripos($text, 'Niveau d') !== false && stripos($text, 'expérience') !== false) {
                        $job['experience'] = $value;
                    } elseif (stripos($text, 'Contrat proposé') !== false) {
                        $job['type_contrat'] = $value;
                    } elseif (stripos($text, 'Région de') !== false) {
                        $job['region'] = $value;
                    } elseif (stripos($text, 'Compétences clés') !== false) {
                        $job['competences'] = $value;
                    }
                }
                
                // Date de publication
                $timeElem = $xpath->query(".//time", $card)->item(0);
                if ($timeElem) {
                    $job['date_publication'] = $this->cleanText($timeElem->textContent);
                    $job['datetime'] = $timeElem->getAttribute('datetime');
                }
                
                // Identifier le type (emploi, freelance, stage, hackathon, formation)
                $jobType = 'emploi';
                if (isset($job['type_contrat'])) {
                    if (stripos($job['type_contrat'], 'Freelance') !== false) {
                        $jobType = 'freelance';
                    } elseif (stripos($job['type_contrat'], 'Stage') !== false) {
                        $jobType = 'stage';
                    }
                }
                
                // Vérifier dans le titre
                if (stripos($job['titre'] ?? '', 'stage') !== false) {
                    $jobType = 'stage';
                } elseif (stripos($job['titre'] ?? '', 'hackathon') !== false) {
                    $jobType = 'hackathon';
                } elseif (stripos($job['titre'] ?? '', 'formation') !== false) {
                    $jobType = 'formation';
                }
                
                // Préparer les données pour l'insertion
                if (!empty($job['titre']) && strlen($job['titre']) >= 5) {
                    $jobData = [
                        'title' => $job['titre'],
                        'description' => $job['description'] ?? 'Offre d\'emploi. Consultez le lien externe pour plus de détails.',
                        'type' => $jobType,
                        'city' => $this->extractCity($job['region'] ?? 'Ouagadougou'),
                        'external_link' => $job['url'] ?? $this->baseUrl,
                        'source' => $source,
                        'skills_required' => $this->extractSkills($job['competences'] ?? ''),
                        'salary_range' => null,
                        'contact_email' => null,
                        'contact_phone' => null,
                    ];
                    
                    $jobs[] = $jobData;
                }
                
            } catch (Exception $e) {
                error_log("Erreur extraction offre: " . $e->getMessage());
                continue;
            }
        }
        
        return $jobs;
    }
    
    /**
     * Scraper spécifiquement les offres freelance IT
     */
    public function scrapeFreelanceIT()
    {
        $source = 'emploiburkina';
        $count = 0;

        try {
            $url = $this->baseUrl . "/offres-it-freelance";
            $html = $this->fetchContent($url);
            
            $freelanceJobs = $this->extractJobsFromEmploiBurkinaPage($html, $source);
            
            // Filtrer pour ne garder que les freelances
            $freelanceJobs = array_filter($freelanceJobs, function($job) {
                return ($job['type'] ?? '') === 'freelance';
            });
            
            // Insérer les offres
            foreach ($freelanceJobs as $jobData) {
                if ($this->insertJob($jobData)) {
                    $count++;
                }
            }
            
            return ['success' => true, 'count' => $count, 'source' => 'EmploiBurkina Freelance'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'source' => 'EmploiBurkina Freelance'];
        }
    }
    
    /**
     * Résoudre une URL relative en URL absolue
     */
    private function resolveUrl($url)
    {
        if (empty($url)) return $this->baseUrl;
        
        if (strpos($url, 'http') === 0) {
            return $url;
        }
        
        return rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
    }
    
    /**
     * Extraire la ville depuis le texte de région
     */
    private function extractCity($regionText)
    {
        $cities = ['Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Ouahigouya'];
        
        foreach ($cities as $city) {
            if (stripos($regionText, $city) !== false) {
                return $city;
            }
        }
        
        return 'Ouagadougou'; // Par défaut
    }
    
    /**
     * Extraire les compétences depuis le texte
     */
    private function extractSkills($competencesText)
    {
        if (empty($competencesText)) {
            return json_encode([]);
        }
        
        // Séparer par virgule, point-virgule, ou "et"
        $skills = preg_split('/[,;]|\bet\b/i', $competencesText);
        $skills = array_map('trim', $skills);
        $skills = array_filter($skills, function($skill) {
            return strlen($skill) > 2;
        });
        
        return json_encode(array_values($skills));
    }

    /**
     * SCRAPER 3: Travail-Burkina.com
     */
    public function scrapeTravailBurkina()
    {
        $url = 'https://www.travail-burkina.com/offres-de-stages/';
        $source = 'travailburkina';
        $count = 0;

        try {
            $html = $this->fetchContent($url);
            
            // Parser les offres de stages
            preg_match_all('/<div[^>]*class="[^"]*job-item[^"]*"[^>]*>.*?<h3[^>]*>(.*?)<\/h3>.*?<span[^>]*class="[^"]*location[^"]*"[^>]*>(.*?)<\/span>.*?<p[^>]*>(.*?)<\/p>/s', $html, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $title = $this->cleanText($match[1]);
                $location = $this->cleanText($match[2]);
                $description = $this->cleanText($match[3]);
                
                if (empty($title) || strlen($title) < 5) continue;
                
                $jobData = [
                    'title' => $title,
                    'description' => $description ?: 'Offre de stage. Voir le lien externe pour plus d\'informations.',
                    'type' => 'stage',
                    'city' => $location ?: 'Ouagadougou',
                    'external_link' => $url,
                    'source' => $source,
                ];
                
                if ($this->insertJob($jobData)) {
                    $count++;
                }
            }
            
            return ['success' => true, 'count' => $count, 'source' => 'Travail-Burkina'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'source' => 'Travail-Burkina'];
        }
    }

    /**
     * Exécuter tous les scrapers
     */
    public function scrapeAll($pagesToScrape = 3)
    {
        $results = [
            'emploiburkina_it' => $this->scrapeEmploiBurkinaIT($pagesToScrape),
            'emploiburkina_freelance' => $this->scrapeFreelanceIT(),
            'globalexpertise' => $this->scrapeGlobalExpertise(),
            'travailburkina' => $this->scrapeTravailBurkina(),
        ];

        $totalCount = 0;
        $errors = [];

        foreach ($results as $key => $result) {
            if ($result['success']) {
                $totalCount += $result['count'];
            } else {
                $errors[] = $result['source'] . ': ' . ($result['error'] ?? 'Erreur inconnue');
            }
        }

        return [
            'total_jobs' => $totalCount,
            'details' => $results,
            'errors' => $errors,
            'success' => $totalCount > 0,
        ];
    }
    
    /**
     * Scraper uniquement EmploiBurkina (IT + Freelance)
     */
    public function scrapeEmploiBurkinaAll($pagesToScrape = 3)
    {
        $results = [
            'emploiburkina_it' => $this->scrapeEmploiBurkinaIT($pagesToScrape),
            'emploiburkina_freelance' => $this->scrapeFreelanceIT(),
        ];

        $totalCount = 0;
        $errors = [];

        foreach ($results as $key => $result) {
            if ($result['success']) {
                $totalCount += $result['count'];
            } else {
                $errors[] = $result['source'] . ': ' . ($result['error'] ?? 'Erreur inconnue');
            }
        }

        return [
            'total_jobs' => $totalCount,
            'details' => $results,
            'errors' => $errors,
            'success' => $totalCount > 0,
        ];
    }
}

