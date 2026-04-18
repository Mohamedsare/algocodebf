<?php
/**
 * Contrôleur des opportunités (emplois, stages, hackathons)
 */

class JobController extends Controller
{
    private $jobModel;

    public function __construct()
    {
        $this->jobModel = $this->model('Job');
    }

    /**
     * Liste des opportunités
     */
    public function index()
    {
        // Ne plus exiger la connexion pour voir les offres
        // $this->requireLogin();
        
        $type = $_GET['type'] ?? null;
        $city = $_GET['city'] ?? null;
        $search = trim($_GET['q'] ?? '');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * JOBS_PER_PAGE;

        // Fermer les offres expirées
        $this->jobModel->closeExpired();

        // Si recherche textuelle, utiliser la méthode search, sinon getAllWithCompany
        if (!empty($search)) {
            $jobs = $this->jobModel->searchWithFilters($search, $type, $city, JOBS_PER_PAGE, $offset);
        } else {
            $jobs = $this->jobModel->getAllWithCompany($type, $city, JOBS_PER_PAGE, $offset);
        }
        
        $cities = $this->jobModel->getCities();
        
        // Statistiques pour la vue
        $stats = [
            'total_jobs' => $this->jobModel->getTotalCount($type, $city, $search),
            'companies' => $this->jobModel->getCompaniesCount(),
            'hired' => 0, // À implémenter si nécessaire
            'new_this_week' => $this->jobModel->getNewThisWeekCount(),
        ];

        // Calculer le nombre total de pages
        $totalJobs = $stats['total_jobs'];
        $totalPages = ceil($totalJobs / JOBS_PER_PAGE);
        
        $data = [
            'title' => 'Opportunités - AlgoCodeBF',
            'jobs' => $jobs,
            'cities' => $cities,
            'current_type' => $type,
            'current_city' => $city,
            'current_search' => $search,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_jobs' => $totalJobs,
            'jobs_per_page' => JOBS_PER_PAGE,
            'stats' => $stats,
            'featured_companies' => [] // À implémenter si nécessaire
        ];

        $this->view('job/index', $data);
    }

    /**
     * Afficher une offre
     */
    public function show($jobId)
    {
        $job = $this->jobModel->getWithDetails($jobId);

        if (!$job) {
            $_SESSION['error'] = "Offre introuvable";
            $this->redirect('job/index');
        }

        // Incrémenter les vues
        $this->jobModel->incrementViews($jobId);

        // Vérifier si l'utilisateur a déjà postulé
        $hasApplied = false;
        if ($this->isLoggedIn()) {
            $hasApplied = $this->jobModel->hasApplied($jobId, $_SESSION['user_id']);
        }

        // Décoder les compétences requises
        $skills = json_decode($job['skills_required'], true) ?? [];

        $data = [
            'title' => $job['title'] . ' - Opportunités - AlgoCodeBF',
            'job' => $job,
            'skills' => $skills,
            'has_applied' => $hasApplied,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('job/show', $data);
    }

    /**
     * Créer une nouvelle offre (entreprises uniquement)
     */
    public function create()
    {
        $this->requireLogin();

        // Vérifier si l'utilisateur est une entreprise ou admin
        if ($_SESSION['user_role'] !== 'company' && $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Seules les entreprises peuvent publier des offres";
            $this->redirect('job/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('job/create');
            }

            $data = [
                'company_id' => $_SESSION['user_id'],
                'type' => Security::clean($_POST['type'] ?? ''),
                'title' => Security::clean($_POST['title'] ?? ''),
                'description' => Security::clean($_POST['description'] ?? ''),
                'city' => Security::clean($_POST['city'] ?? ''),
                'skills_required' => json_encode($_POST['skills'] ?? []),
                'salary_range' => Security::clean($_POST['salary_range'] ?? ''),
                'deadline' => Security::clean($_POST['deadline'] ?? ''),
                'contact_email' => Security::clean($_POST['contact_email'] ?? ''),
                'contact_phone' => Security::clean($_POST['contact_phone'] ?? ''),
                'external_link' => Security::clean($_POST['external_link'] ?? '')
            ];

            $validator = new Validator($data);
            $validator->required('type')
                     ->required('title')->min('title', 5)
                     ->required('description')->min('description', 50)
                     ->required('city');

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = $data;
                $this->redirect('job/create');
            }

            $jobId = $this->jobModel->createJob($data);

            if ($jobId) {
                $_SESSION['success'] = "Offre créée avec succès";
                $this->redirect('job/show/' . $jobId);
            } else {
                $_SESSION['error'] = "Erreur lors de la création de l'offre";
                $this->redirect('job/create');
            }
        }

        // Récupérer toutes les compétences
        $db = Database::getInstance();
        $allSkills = $db->query("SELECT * FROM skills ORDER BY category, name");

        $data = [
            'title' => 'Publier une offre - AlgoCodeBF',
            'all_skills' => $allSkills,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('job/create', $data);
    }

    /**
     * Postuler à une offre
     */
    public function apply($jobId)
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('job/show/' . $jobId);
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('job/show/' . $jobId);
        }

        // Vérifier si déjà postulé
        if ($this->jobModel->hasApplied($jobId, $_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous avez déjà postulé à cette offre";
            $this->redirect('job/show/' . $jobId);
        }

        $coverLetter = Security::clean($_POST['cover_letter'] ?? '');

        if ($this->jobModel->apply($jobId, $_SESSION['user_id'], $coverLetter)) {
            $_SESSION['success'] = "Candidature envoyée avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de l'envoi de la candidature";
        }

        $this->redirect('job/show/' . $jobId);
    }

    /**
     * Voir les candidatures (entreprises uniquement)
     */
    public function applications($jobId)
    {
        $this->requireLogin();

        $job = $this->jobModel->findById($jobId);

        if (!$job || $job['company_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Accès non autorisé";
            $this->redirect('job/index');
        }

        $applications = $this->jobModel->getApplications($jobId);

        $data = [
            'title' => 'Candidatures - ' . $job['title'] . ' - AlgoCodeBF',
            'job' => $job,
            'applications' => $applications
        ];

        $this->view('job/applications', $data);
    }
}

