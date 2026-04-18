<?php
/**
 * Contrôleur de la page d'accueil
 */

require_once __DIR__ . '/../Helpers/Cache.php';

class HomeController extends Controller
{
    /**
     * Page d'accueil - Redirection selon le statut de connexion
     */
    public function index()
    {
        // Si l'utilisateur n'est pas connecté, afficher la landing page
        if (!isset($_SESSION['user_id'])) {
            $this->landing();
            return;
        }
        
        // Si l'utilisateur est connecté, afficher le tableau de bord
        $this->dashboard();
    }

    /**
     * Landing page pour les visiteurs non connectés
     */
    public function landing()
    {
        // Utiliser le cache pour les statistiques (cache 5 minutes)
        $cache = Cache::getInstance();
        $cacheKey = 'landing_stats';
        
        $stats = $cache->remember($cacheKey, function() {
            $db = Database::getInstance();
            
            return [
                'total_users' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0,
                'total_posts' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE status = 'active'")['count'] ?? 0,
                'total_tutorials' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE status = 'active'")['count'] ?? 0,
                'total_projects' => $db->queryOne("SELECT COUNT(*) as count FROM projects WHERE visibility = 'public'")['count'] ?? 0
            ];
        }, 300); // Cache 5 minutes

        $data = [
            'title' => 'Bienvenue sur AlgoCodeBF - HubTech',
            'stats' => $stats
        ];

        $this->view('home/landing', $data);
    }

    /**
     * Tableau de bord pour les utilisateurs connectés
     */
    public function dashboard()
    {
        // Utiliser le cache pour les données du dashboard (cache 2 minutes)
        $cache = Cache::getInstance();
        $cacheKey = 'dashboard_data_' . ($_SESSION['user_id'] ?? 'guest');
        
        $dashboardData = $cache->remember($cacheKey, function() {
            $db = Database::getInstance();
            
            // Récupérer quelques statistiques pour l'accueil
            $stats = [
                'total_users' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0,
                'total_posts' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE status = 'active'")['count'] ?? 0,
                'total_tutorials' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE status = 'active'")['count'] ?? 0,
                'total_projects' => $db->queryOne("SELECT COUNT(*) as count FROM projects WHERE visibility = 'public'")['count'] ?? 0
            ];

            // Récupérer les posts récents
            $postModel = $this->model('Post');
            $recentPosts = $postModel->getAllWithAuthor(null, 5);

            // Récupérer les tutoriels populaires
            $tutorialModel = $this->model('Tutorial');
            $popularTutorials = $tutorialModel->getPopular(5);

            // Récupérer les derniers articles de blog
            $blogModel = $this->model('BlogPost');
            $recentBlogs = $blogModel->getAllPublished(null, 3);
            
            return [
                'stats' => $stats,
                'recent_posts' => $recentPosts,
                'popular_tutorials' => $popularTutorials,
                'recent_blogs' => $recentBlogs
            ];
        }, 120); // Cache 2 minutes

        $data = [
            'title' => 'Accueil - AlgoCodeBF',
            'stats' => $dashboardData['stats'],
            'recent_posts' => $dashboardData['recent_posts'],
            'popular_tutorials' => $dashboardData['popular_tutorials'],
            'recent_blogs' => $dashboardData['recent_blogs']
        ];

        $this->view('home/index', $data);
    }

    /**
     * Page À propos
     */
    public function about()
    {
        $data = [
            'title' => 'À propos - AlgoCodeBF'
        ];

        $this->view('home/about', $data);
    }

    /**
     * Page de recherche globale
     */
    public function search()
    {
        $this->requireLogin();
        
        $query = Security::clean($_GET['q'] ?? '');

        if (empty($query)) {
            $this->redirect('home/index');
        }

        // Utiliser le cache pour les résultats de recherche (cache 1 minute)
        $cache = Cache::getInstance();
        $cacheKey = 'search_results_' . md5($query);
        
        $searchData = $cache->remember($cacheKey, function() use ($query) {
            // Rechercher dans toutes les catégories
            $userModel = $this->model('User');
            $postModel = $this->model('Post');
            $tutorialModel = $this->model('Tutorial');
            $projectModel = $this->model('Project');
            $jobModel = $this->model('Job');

            $results = [
                'users' => $userModel->search($query, 10),
                'posts' => $postModel->search($query, 10),
                'tutorials' => $tutorialModel->search($query, 10),
                'projects' => $projectModel->search($query, 10),
                'jobs' => $jobModel->search($query, 10)
            ];

            // Calculer le nombre de résultats par catégorie
            $results_count = [
                'users' => count($results['users']),
                'posts' => count($results['posts']),
                'tutorials' => count($results['tutorials']),
                'projects' => count($results['projects']),
                'jobs' => count($results['jobs'])
            ];

            // Calculer le total
            $total_results = array_sum($results_count);
            
            return [
                'results' => $results,
                'results_count' => $results_count,
                'total_results' => $total_results
            ];
        }, 60); // Cache 1 minute

        $data = [
            'title' => 'Recherche : ' . $query . ' - AlgoCodeBF',
            'query' => $query,
            'results' => $searchData['results'],
            'results_count' => $searchData['results_count'],
            'total_results' => $searchData['total_results']
        ];

        $this->view('home/search', $data);
    }
}

