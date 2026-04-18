<?php
/**
 * Modèle Job - Gestion des offres d'emploi, stages, hackathons
 */

class Job extends Model
{
    protected $table = 'jobs';

    /**
     * Créer une nouvelle offre
     * 
     * @param array $data Données de l'offre
     * @return int|false ID de l'offre créée ou false
     */
    public function createJob($data)
    {
        return $this->create($data);
    }

    /**
     * Obtenir toutes les offres avec les informations de l'entreprise
     * 
     * @param string $type Type d'offre (optionnel)
     * @param string $city Ville (optionnel)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getAllWithCompany($type = null, $city = null, $limit = 20, $offset = 0)
    {
        $filters = ["j.status = 'active'"];
        $params = [];
        
        if ($type) {
            $filters[] = "j.type = ?";
            $params[] = $type;
        }
        
        if ($city) {
            $filters[] = "j.city = ?";
            $params[] = $city;
        }
        
        $whereClause = implode(" AND ", $filters);
        
        $query = "
            SELECT j.*, 
                   u.prenom, u.nom, u.photo_path,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) as company_name,
                   COUNT(DISTINCT a.id) as applications_count
            FROM jobs j
            LEFT JOIN users u ON j.company_id = u.id
            LEFT JOIN applications a ON j.id = a.job_id
            WHERE {$whereClause}
            GROUP BY j.id
            ORDER BY j.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $jobs = $this->db->query($query, $params);
        
        // Normaliser les noms d'entreprise pour les offres scrapées
        foreach ($jobs as &$job) {
            if (empty($job['company_name']) || trim($job['company_name']) === '' || $job['company_name'] === 'Bot Scraper') {
                // Extraire le nom de l'entreprise depuis external_link ou utiliser une source par défaut
                $externalLink = $job['external_link'] ?? '';
                if (strpos($externalLink, 'emploiburkina') !== false) {
                    $job['company_name'] = 'EmploiBurkina.com';
                } elseif (strpos($externalLink, 'recrutor') !== false || strpos($externalLink, 'globalexpertise') !== false) {
                    $job['company_name'] = 'Global Expertise';
                } elseif (strpos($externalLink, 'travail-burkina') !== false) {
                    $job['company_name'] = 'Travail-Burkina.com';
                } else {
                    $job['company_name'] = 'Source externe';
                }
            }
        }
        
        return $jobs;
    }
    
    /**
     * Rechercher des offres avec filtres
     * 
     * @param string $search Terme de recherche
     * @param string $type Type d'offre (optionnel)
     * @param string $city Ville (optionnel)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function searchWithFilters($search, $type = null, $city = null, $limit = 20, $offset = 0)
    {
        $filters = ["j.status = 'active'"];
        $params = [];
        
        // Recherche textuelle
        $searchTerm = "%{$search}%";
        $filters[] = "(j.title LIKE ? OR j.description LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        
        if ($type) {
            $filters[] = "j.type = ?";
            $params[] = $type;
        }
        
        if ($city) {
            $filters[] = "j.city = ?";
            $params[] = $city;
        }
        
        $whereClause = implode(" AND ", $filters);
        
        $query = "
            SELECT j.*, 
                   u.prenom, u.nom, u.photo_path,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) as company_name,
                   COUNT(DISTINCT a.id) as applications_count
            FROM jobs j
            LEFT JOIN users u ON j.company_id = u.id
            LEFT JOIN applications a ON j.id = a.job_id
            WHERE {$whereClause}
            GROUP BY j.id
            ORDER BY j.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $jobs = $this->db->query($query, $params);
        
        // Normaliser les noms d'entreprise pour les offres scrapées
        foreach ($jobs as &$job) {
            if (empty($job['company_name']) || trim($job['company_name']) === '' || $job['company_name'] === 'Bot Scraper') {
                $externalLink = $job['external_link'] ?? '';
                if (strpos($externalLink, 'emploiburkina') !== false) {
                    $job['company_name'] = 'EmploiBurkina.com';
                } elseif (strpos($externalLink, 'recrutor') !== false || strpos($externalLink, 'globalexpertise') !== false) {
                    $job['company_name'] = 'Global Expertise';
                } elseif (strpos($externalLink, 'travail-burkina') !== false) {
                    $job['company_name'] = 'Travail-Burkina.com';
                } else {
                    $job['company_name'] = 'Source externe';
                }
            }
        }
        
        return $jobs;
    }

    /**
     * Obtenir une offre avec ses détails complets
     * 
     * @param int $jobId ID de l'offre
     * @return array|false
     */
    public function getWithDetails($jobId)
    {
        $query = "
            SELECT j.*, 
                   u.id as company_user_id, u.prenom, u.nom, u.photo_path,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) as company_name,
                   COUNT(DISTINCT a.id) as applications_count
            FROM jobs j
            LEFT JOIN users u ON j.company_id = u.id
            LEFT JOIN applications a ON j.id = a.job_id
            WHERE j.id = ? AND j.status = 'active'
            GROUP BY j.id
        ";
        
        $job = $this->db->queryOne($query, [$jobId]);
        
        // Normaliser le nom d'entreprise pour les offres scrapées
        if ($job && (empty($job['company_name']) || trim($job['company_name']) === '' || $job['company_name'] === 'Bot Scraper')) {
            $externalLink = $job['external_link'] ?? '';
            if (strpos($externalLink, 'emploiburkina') !== false) {
                $job['company_name'] = 'EmploiBurkina.com';
            } elseif (strpos($externalLink, 'recrutor') !== false || strpos($externalLink, 'globalexpertise') !== false) {
                $job['company_name'] = 'Global Expertise';
            } elseif (strpos($externalLink, 'travail-burkina') !== false) {
                $job['company_name'] = 'Travail-Burkina.com';
            } else {
                $job['company_name'] = 'Source externe';
            }
        }
        
        return $job;
    }

    /**
     * Postuler à une offre
     * 
     * @param int $jobId ID de l'offre
     * @param int $userId ID de l'utilisateur
     * @param string $coverLetter Lettre de motivation
     * @return bool
     */
    public function apply($jobId, $userId, $coverLetter = null)
    {
        return $this->db->execute(
            "INSERT INTO applications (job_id, user_id, cover_letter, created_at) VALUES (?, ?, ?, ?)",
            [$jobId, $userId, $coverLetter, date('Y-m-d H:i:s')]
        );
    }

    /**
     * Vérifier si un utilisateur a déjà postulé
     * 
     * @param int $jobId ID de l'offre
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function hasApplied($jobId, $userId)
    {
        $result = $this->db->queryOne(
            "SELECT id FROM applications WHERE job_id = ? AND user_id = ?",
            [$jobId, $userId]
        );
        
        return $result !== false;
    }

    /**
     * Obtenir les candidatures pour une offre
     * 
     * @param int $jobId ID de l'offre
     * @return array
     */
    public function getApplications($jobId)
    {
        $query = "
            SELECT a.*, 
                   u.id as user_id, u.prenom, u.nom, u.email, u.phone, 
                   u.photo_path, u.cv_path, u.university, u.city
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            WHERE a.job_id = ?
            ORDER BY a.created_at DESC
        ";
        
        return $this->db->query($query, [$jobId]);
    }

    /**
     * Mettre à jour le statut d'une candidature
     * 
     * @param int $applicationId ID de la candidature
     * @param string $status Nouveau statut
     * @return bool
     */
    public function updateApplicationStatus($applicationId, $status)
    {
        return $this->db->execute(
            "UPDATE applications SET status = ? WHERE id = ?",
            [$status, $applicationId]
        );
    }

    /**
     * Incrémenter le nombre de vues
     * 
     * @param int $jobId ID de l'offre
     * @return bool
     */
    public function incrementViews($jobId)
    {
        return $this->db->execute("UPDATE jobs SET views = views + 1 WHERE id = ?", [$jobId]);
    }

    /**
     * Rechercher des offres
     * 
     * @param string $search Terme de recherche
     * @param int $limit Limite de résultats
     * @return array
     */
    public function search($search, $limit = 20)
    {
        $query = "
            SELECT j.*, 
                   u.prenom, u.nom, u.photo_path,
                   COUNT(DISTINCT a.id) as applications_count
            FROM jobs j
            INNER JOIN users u ON j.company_id = u.id
            LEFT JOIN applications a ON j.id = a.job_id
            WHERE j.status = 'active' AND (
                MATCH(j.title, j.description) AGAINST(? IN NATURAL LANGUAGE MODE)
                OR j.title LIKE ?
                OR j.description LIKE ?
            )
            GROUP BY j.id
            ORDER BY j.created_at DESC
            LIMIT ?
        ";
        
        $searchTerm = "%{$search}%";
        return $this->db->query($query, [$search, $searchTerm, $searchTerm, $limit]);
    }

    /**
     * Obtenir les villes disponibles
     * 
     * @return array
     */
    public function getCities()
    {
        $query = "
            SELECT DISTINCT city, COUNT(*) as count
            FROM jobs
            WHERE status = 'active' AND city IS NOT NULL
            GROUP BY city
            ORDER BY count DESC
        ";
        
        return $this->db->query($query);
    }

    /**
     * Fermer les offres expirées
     * 
     * @return bool
     */
    public function closeExpired()
    {
        return $this->db->execute(
            "UPDATE jobs SET status = 'expired' WHERE deadline < CURDATE() AND status = 'active'"
        );
    }
    
    /**
     * Obtenir le nombre total d'offres
     * 
     * @param string $type Type d'offre (optionnel)
     * @param string $city Ville (optionnel)
     * @param string $search Terme de recherche (optionnel)
     * @return int
     */
    public function getTotalCount($type = null, $city = null, $search = null)
    {
        $filters = ["status = 'active'"];
        $params = [];
        
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $filters[] = "(title LIKE ? OR description LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($type) {
            $filters[] = "type = ?";
            $params[] = $type;
        }
        
        if ($city) {
            $filters[] = "city = ?";
            $params[] = $city;
        }
        
        $whereClause = implode(" AND ", $filters);
        
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as total FROM jobs WHERE {$whereClause}",
            $params
        );
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Obtenir le nombre d'entreprises
     * 
     * @return int
     */
    public function getCompaniesCount()
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(DISTINCT company_id) as total FROM jobs WHERE status = 'active'"
        );
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Obtenir le nombre d'offres créées cette semaine
     * 
     * @return int
     */
    public function getNewThisWeekCount()
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as total FROM jobs 
             WHERE status = 'active' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        return $result ? (int)$result['total'] : 0;
    }
}

