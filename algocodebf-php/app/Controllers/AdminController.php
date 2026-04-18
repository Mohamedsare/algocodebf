<?php
/**
 * Contrôleur d'administration
 */

class AdminController extends Controller
{
    /**
     * Index - Redirige vers dashboard
     */
    public function index()
    {
        $this->dashboard();
    }

    /**
     * Dashboard admin
     */
    public function dashboard()
    {
        $this->requireAdmin();

        $db = Database::getInstance();

        // Statistiques générales ultra précises
        $stats = [
            // Utilisateurs
            'total_users' => $db->queryOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
            'active_users' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0,
            'pending_users' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE status = 'pending'")['count'] ?? 0,
            'banned_users' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE status = 'banned'")['count'] ?? 0,
            'admin_users' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count'] ?? 0,
            'verified_users' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE email_verified = 1")['count'] ?? 0,
            
            // Contenu
            'total_posts' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE status = 'active'")['count'] ?? 0,
            'pending_posts' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE status = 'pending'")['count'] ?? 0,
            'hidden_posts' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE status = 'hidden'")['count'] ?? 0,
            'pinned_posts' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE is_pinned = 1")['count'] ?? 0,
            
            'total_tutorials' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE status = 'active'")['count'] ?? 0,
            'pending_tutorials' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE status = 'pending'")['count'] ?? 0,
            'video_tutorials' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE type = 'video' AND status = 'active'")['count'] ?? 0,
            'pdf_tutorials' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE type = 'pdf' AND status = 'active'")['count'] ?? 0,
            
            'total_projects' => $db->queryOne("SELECT COUNT(*) as count FROM projects WHERE status != 'deleted'")['count'] ?? 0,
            'active_projects' => $db->queryOne("SELECT COUNT(*) as count FROM projects WHERE status = 'in_progress'")['count'] ?? 0,
            'completed_projects' => $db->queryOne("SELECT COUNT(*) as count FROM projects WHERE status = 'completed'")['count'] ?? 0,
            'public_projects' => $db->queryOne("SELECT COUNT(*) as count FROM projects WHERE visibility = 'public' AND status != 'deleted'")['count'] ?? 0,
            
            'total_jobs' => $db->queryOne("SELECT COUNT(*) as count FROM jobs WHERE status = 'active'")['count'] ?? 0,
            'pending_jobs' => $db->queryOne("SELECT COUNT(*) as count FROM jobs WHERE status = 'pending'")['count'] ?? 0,
            'expired_jobs' => $db->queryOne("SELECT COUNT(*) as count FROM jobs WHERE status = 'expired'")['count'] ?? 0,
            
            'total_blog_posts' => $db->queryOne("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'")['count'] ?? 0,
            'draft_blog_posts' => $db->queryOne("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'draft'")['count'] ?? 0,
            
            // Interactions
            'total_comments' => $db->queryOne("SELECT COUNT(*) as count FROM comments WHERE status = 'active'")['count'] ?? 0,
            'pending_comments' => $db->queryOne("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'")['count'] ?? 0,
            'hidden_comments' => $db->queryOne("SELECT COUNT(*) as count FROM comments WHERE status = 'hidden'")['count'] ?? 0,
            
            'total_likes' => $db->queryOne("SELECT COUNT(*) as count FROM likes")['count'] ?? 0,
            'post_likes' => $db->queryOne("SELECT COUNT(*) as count FROM likes WHERE likeable_type = 'post'")['count'] ?? 0,
            'tutorial_likes' => $db->queryOne("SELECT COUNT(*) as count FROM likes WHERE likeable_type = 'tutorial'")['count'] ?? 0,
            
            'total_applications' => $db->queryOne("SELECT COUNT(*) as count FROM applications")['count'] ?? 0,
            'pending_applications' => $db->queryOne("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'")['count'] ?? 0,
            
            // Newsletter et engagement
            'total_subscribers' => $db->queryOne("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active'")['count'] ?? 0,
            'unsubscribed_users' => $db->queryOne("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'unsubscribed'")['count'] ?? 0,
            
            // Signalements et modération
            'pending_reports' => $db->queryOne("SELECT COUNT(*) as count FROM reports WHERE status = 'pending'")['count'] ?? 0,
            'resolved_reports' => $db->queryOne("SELECT COUNT(*) as count FROM reports WHERE status = 'resolved'")['count'] ?? 0,
            'dismissed_reports' => $db->queryOne("SELECT COUNT(*) as count FROM reports WHERE status = 'dismissed'")['count'] ?? 0,
            
            // Activité récente
            'new_users_today' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
            'new_users_week' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0,
            'new_users_month' => $db->queryOne("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0,
            
            'new_posts_today' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
            'new_posts_week' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0,
            
            'new_tutorials_today' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
            'new_tutorials_week' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0,
            
            // Visiteurs et trafic
            'online_users' => $db->queryOne("SELECT COUNT(DISTINCT session_id) as count FROM online_users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)")['count'] ?? 0,
            'visitors_today' => $db->queryOne("SELECT COUNT(DISTINCT session_id) as count FROM visitor_logs WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
            'visitors_week' => $db->queryOne("SELECT COUNT(DISTINCT session_id) as count FROM visitor_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0,
            'visitors_month' => $db->queryOne("SELECT COUNT(DISTINCT session_id) as count FROM visitor_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0,
            
            // Top pays (30 derniers jours)
            'top_countries' => $db->query("SELECT country, COUNT(DISTINCT session_id) as visitors FROM visitor_logs WHERE country IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY country ORDER BY visitors DESC LIMIT 5"),
            
            // Répartition des appareils
            'mobile_users' => $db->queryOne("SELECT COUNT(DISTINCT session_id) as count FROM visitor_logs WHERE device_type = 'mobile' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0,
            'desktop_users' => $db->queryOne("SELECT COUNT(DISTINCT session_id) as count FROM visitor_logs WHERE device_type = 'desktop' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0,
            'tablet_users' => $db->queryOne("SELECT COUNT(DISTINCT session_id) as count FROM visitor_logs WHERE device_type = 'tablet' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0,
            
            // Top navigateurs
            'top_browsers' => $db->query("SELECT browser, COUNT(DISTINCT session_id) as users FROM visitor_logs WHERE browser IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY browser ORDER BY users DESC LIMIT 5"),
            
            // Système
            'database_size' => $this->getDatabaseSize(),
            'cache_size' => $this->getCacheSize(),
            'php_version' => phpversion(),
            'mysql_version' => $this->getMySQLVersion(),
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get()
        ];

        // Utilisateurs récents avec plus de détails
        $recentUsers = $db->query("
            SELECT u.*, 
                   COUNT(DISTINCT p.id) as posts_count,
                   COUNT(DISTINCT t.id) as tutorials_count,
                   COUNT(DISTINCT pr.id) as projects_count,
                   COUNT(DISTINCT c.id) as comments_count
            FROM users u
            LEFT JOIN posts p ON u.id = p.user_id AND p.status = 'active'
            LEFT JOIN tutorials t ON u.id = t.user_id AND t.status = 'active'
            LEFT JOIN projects pr ON u.id = pr.owner_id AND pr.status != 'deleted'
            LEFT JOIN comments c ON u.id = c.user_id AND c.status = 'active'
            GROUP BY u.id
            ORDER BY u.created_at DESC 
            LIMIT 10
        ");

        // Top contributeurs (30 derniers jours)
        $topContributors = $db->query("
            SELECT u.id, u.prenom, u.nom, u.photo_path, u.university, u.city,
                   COUNT(DISTINCT p.id) as posts_count,
                   COUNT(DISTINCT t.id) as tutorials_count,
                   COUNT(DISTINCT pr.id) as projects_count,
                   COUNT(DISTINCT c.id) as comments_count,
                   COUNT(DISTINCT l.id) as likes_received,
                   (COUNT(DISTINCT p.id) * 5 + 
                    COUNT(DISTINCT t.id) * 10 + 
                    COUNT(DISTINCT pr.id) * 8 + 
                    COUNT(DISTINCT c.id) * 2 + 
                    COUNT(DISTINCT l.id)) as score
            FROM users u
            LEFT JOIN posts p ON u.id = p.user_id AND p.status = 'active' AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            LEFT JOIN tutorials t ON u.id = t.user_id AND t.status = 'active' AND t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            LEFT JOIN projects pr ON u.id = pr.owner_id AND pr.status != 'deleted' AND pr.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            LEFT JOIN comments c ON u.id = c.user_id AND c.status = 'active' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            LEFT JOIN likes l ON (l.likeable_type = 'post' AND l.likeable_id IN (SELECT id FROM posts WHERE user_id = u.id))
                               OR (l.likeable_type = 'tutorial' AND l.likeable_id IN (SELECT id FROM tutorials WHERE user_id = u.id))
            WHERE u.status = 'active' AND u.role = 'user'
            GROUP BY u.id
            ORDER BY score DESC
            LIMIT 10
        ");

        // Activité par jour (7 derniers jours)
        $dailyActivity = $db->query("
            SELECT DATE(created_at) as date, 
                   COUNT(*) as users,
                   (SELECT COUNT(*) FROM posts WHERE DATE(created_at) = DATE(u.created_at)) as posts,
                   (SELECT COUNT(*) FROM tutorials WHERE DATE(created_at) = DATE(u.created_at)) as tutorials,
                   (SELECT COUNT(*) FROM comments WHERE DATE(created_at) = DATE(u.created_at)) as comments
            FROM users u
            WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");

        // Contenu le plus populaire
        $popularContent = $db->query("
            (SELECT 'post' as type, p.id, p.title, p.views, 
                    COALESCE(COUNT(l.id), 0) as likes_count, p.created_at 
             FROM posts p 
             LEFT JOIN likes l ON l.likeable_type = 'post' AND l.likeable_id = p.id 
             WHERE p.status = 'active' 
             GROUP BY p.id 
             ORDER BY p.views DESC 
             LIMIT 5)
            UNION ALL
            (SELECT 'tutorial' as type, t.id, t.title, t.views, 
                    COALESCE(COUNT(l.id), 0) as likes_count, t.created_at 
             FROM tutorials t 
             LEFT JOIN likes l ON l.likeable_type = 'tutorial' AND l.likeable_id = t.id 
             WHERE t.status = 'active' 
             GROUP BY t.id 
             ORDER BY t.views DESC 
             LIMIT 5)
            ORDER BY views DESC
            LIMIT 10
        ");

        $data = [
            'title' => 'Administration - HubTech',
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'top_contributors' => $topContributors,
            'daily_activity' => $dailyActivity,
            'popular_content' => $popularContent
        ];

        $this->view('admin/dashboard', $data);
    }

    /**
     * Page de gestion des permissions
     */
    public function permissions()
    {
        $this->requireAdmin();
        
        $db = Database::getInstance();
        
        // Récupérer tous les utilisateurs avec leurs permissions
        $users = $db->query("
            SELECT 
                id, 
                prenom, 
                nom, 
                email, 
                status,
                role,
                can_create_tutorial,
                can_create_project,
                CONCAT(prenom, ' ', nom) as full_name
            FROM users
            WHERE role != 'admin'
            ORDER BY prenom ASC, nom ASC
        ");
        
        // Convertir les valeurs boolean
        foreach ($users as &$user) {
            $user['can_create_tutorial'] = (bool)$user['can_create_tutorial'];
            $user['can_create_project'] = (bool)$user['can_create_project'];
        }
        
        // Générer un token CSRF
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $data = [
            'title' => 'Gestion des Permissions - Admin',
            'users' => $users,
            'csrf_token' => $_SESSION['csrf_token'],
            'is_admin_page' => true
        ];
        
        $this->view('admin/permissions', $data);
    }

    /**
     * Gestion des utilisateurs
     */
    public function users()
    {
        $this->requireAdmin();

        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;

        $db = Database::getInstance();
        $query = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        if ($search) {
            $query .= " AND (prenom LIKE ? OR nom LIKE ? OR email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " ORDER BY created_at DESC";
        $users = $db->query($query, $params);

        $data = [
            'title' => 'Gestion des utilisateurs - Admin - HubTech',
            'users' => $users,
            'current_status' => $status,
            'search' => $search
        ];

        $this->view('admin/users', $data);
    }

    /**
     * Modifier le statut d'un utilisateur
     */
    public function updateUserStatus($userId)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/users');
        }

        $status = Security::clean($_POST['status'] ?? '');
        $role = Security::clean($_POST['role'] ?? '');

        $userModel = $this->model('User');
        $updateData = [];

        if (!empty($status)) {
            $updateData['status'] = $status;
        }

        if (!empty($role)) {
            $updateData['role'] = $role;
        }

        if ($userModel->update($userId, $updateData)) {
            $_SESSION['success'] = "Utilisateur mis à jour";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour";
        }

        $this->redirect('admin/users');
    }

    /**
     * Gestion des signalements
     */
    public function reports()
    {
        $this->requireAdmin();

        $status = $_GET['status'] ?? 'pending';

        $reportModel = $this->model('Report');
        $reports = $reportModel->getAllWithDetails($status, 100, 0);

        $data = [
            'title' => 'Gestion des signalements - Admin - HubTech',
            'reports' => $reports,
            'current_status' => $status
        ];

        $this->view('admin/reports', $data);
    }

    /**
     * Traiter un signalement
     */
    public function handleReport($reportId)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/reports');
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('admin/reports');
        }

        $action = Security::clean($_POST['action'] ?? '');
        $adminNote = Security::clean($_POST['admin_note'] ?? '');

        $reportModel = $this->model('Report');
        $report = $reportModel->findById($reportId);

        if (!$report) {
            $_SESSION['error'] = "Signalement introuvable";
            $this->redirect('admin/reports');
        }

        // Effectuer l'action sur le contenu signalé
        $db = Database::getInstance();
        
        if ($action === 'hide') {
            // Masquer le contenu
            if ($report['reportable_type'] === 'post') {
                $db->execute("UPDATE posts SET status = 'hidden' WHERE id = ?", [$report['reportable_id']]);
            } elseif ($report['reportable_type'] === 'comment') {
                $db->execute("UPDATE comments SET status = 'hidden' WHERE id = ?", [$report['reportable_id']]);
            } elseif ($report['reportable_type'] === 'tutorial') {
                $db->execute("UPDATE tutorials SET status = 'hidden' WHERE id = ?", [$report['reportable_id']]);
            }
            $reportModel->updateStatus($reportId, 'resolved', $adminNote);
            $_SESSION['success'] = "Contenu masqué et signalement résolu";
        } elseif ($action === 'delete') {
            // Supprimer le contenu
            if ($report['reportable_type'] === 'post') {
                $db->execute("UPDATE posts SET status = 'deleted' WHERE id = ?", [$report['reportable_id']]);
            } elseif ($report['reportable_type'] === 'comment') {
                $db->execute("UPDATE comments SET status = 'deleted' WHERE id = ?", [$report['reportable_id']]);
            } elseif ($report['reportable_type'] === 'tutorial') {
                $db->execute("UPDATE tutorials SET status = 'deleted' WHERE id = ?", [$report['reportable_id']]);
            }
            $reportModel->updateStatus($reportId, 'resolved', $adminNote);
            $_SESSION['success'] = "Contenu supprimé et signalement résolu";
        } elseif ($action === 'dismiss') {
            // Ignorer le signalement
            $reportModel->updateStatus($reportId, 'dismissed', $adminNote);
            $_SESSION['success'] = "Signalement ignoré";
        }

        $this->redirect('admin/reports');
    }

    /**
     * Gestion des contenus
     */
    public function content()
    {
        $this->requireAdmin();

        $type = $_GET['type'] ?? 'posts';

        $db = Database::getInstance();

        $content = [];
        if ($type === 'posts') {
            $content = $db->query("SELECT p.*, u.prenom, u.nom FROM posts p INNER JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 50");
        } elseif ($type === 'tutorials') {
            $content = $db->query("SELECT t.*, u.prenom, u.nom FROM tutorials t INNER JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 50");
        } elseif ($type === 'jobs') {
            $content = $db->query("SELECT j.*, u.prenom, u.nom FROM jobs j INNER JOIN users u ON j.company_id = u.id ORDER BY j.created_at DESC LIMIT 50");
        } elseif ($type === 'blog') {
            $content = $db->query("SELECT bp.*, u.prenom, u.nom FROM blog_posts bp INNER JOIN users u ON bp.author_id = u.id ORDER BY bp.created_at DESC LIMIT 50");
        }

        $data = [
            'title' => 'Gestion des contenus - Admin - HubTech',
            'content' => $content,
            'current_type' => $type
        ];

        $this->view('admin/content', $data);
    }

    /**
     * Statistiques détaillées
     */
    public function stats()
    {
        $this->requireAdmin();

        $db = Database::getInstance();

        // Statistiques par période
        $statsDaily = [
            'new_users' => $db->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date"),
            'new_posts' => $db->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date"),
            'new_tutorials' => $db->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM tutorials WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date")
        ];

        // Statistiques par catégorie
        $statsByCategory = [
            'posts' => $db->query("SELECT category, COUNT(*) as count FROM posts WHERE status = 'active' GROUP BY category ORDER BY count DESC"),
            'tutorials' => $db->query("SELECT category, COUNT(*) as count FROM tutorials WHERE status = 'active' GROUP BY category ORDER BY count DESC"),
            'jobs' => $db->query("SELECT type, COUNT(*) as count FROM jobs WHERE status = 'active' GROUP BY type ORDER BY count DESC")
        ];

        // Top contributeurs
        $topContributors = $db->query("
            SELECT u.id, u.prenom, u.nom, u.photo_path,
                   COUNT(DISTINCT p.id) as posts_count,
                   COUNT(DISTINCT t.id) as tutorials_count
            FROM users u
            LEFT JOIN posts p ON u.id = p.user_id AND p.status = 'active'
            LEFT JOIN tutorials t ON u.id = t.user_id AND t.status = 'active'
            WHERE u.status = 'active'
            GROUP BY u.id
            ORDER BY (posts_count + tutorials_count) DESC
            LIMIT 20
        ");

        $data = [
            'title' => 'Statistiques - Admin - HubTech',
            'stats_daily' => $statsDaily,
            'stats_by_category' => $statsByCategory,
            'top_contributors' => $topContributors
        ];

        $this->view('admin/stats', $data);
    }

    /**
     * Logs d'activité
     */
    public function logs()
    {
        $this->requireAdmin();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * 100;

        $db = Database::getInstance();
        $logs = $db->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100 OFFSET ?", [$offset]);

        $data = [
            'title' => 'Logs d\'activité - Admin - HubTech',
            'logs' => $logs,
            'page' => $page
        ];

        $this->view('admin/logs', $data);
    }

    /**
     * API: Récupérer les données des utilisateurs (JSON)
     */
    public function getUsersData()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;

        $db = Database::getInstance();
        // Sélectionner TOUS les champs nécessaires pour afficher dans le modal
        $query = "SELECT 
            id, prenom, nom, email, email_verified, 
            phone, university, faculty, city, 
            bio, photo_path, cv_path, document_path, 
            role, status, 
            last_login, created_at, updated_at 
            FROM users WHERE 1=1";
        $params = [];

        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        if ($search) {
            $query .= " AND (prenom LIKE ? OR nom LIKE ? OR email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " ORDER BY created_at DESC LIMIT 100";
        $users = $db->query($query, $params);

        echo json_encode(['success' => true, 'users' => $users]);
    }

    /**
     * API: Mettre à jour un utilisateur
     */
    public function updateUser()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $status = Security::clean($_POST['status'] ?? '');
        $role = Security::clean($_POST['role'] ?? '');

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide']);
            return;
        }

        $userModel = $this->model('User');
        $updateData = [];

        if (!empty($status)) $updateData['status'] = $status;
        if (!empty($role)) $updateData['role'] = $role;

        if ($userModel->update($userId, $updateData)) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Supprimer un utilisateur (définitivement)
     */
    public function deleteUser()
    {
        // Appeler la méthode de suppression
        $this->performUserDeletion();
    }


    /**
     * Méthode privée pour effectuer la suppression d'un utilisateur
     */
    private function performUserDeletion()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide']);
            return;
        }

        // Vérifier que ce n'est pas un admin
        $db = Database::getInstance();
        $user = $db->queryOne("SELECT role FROM users WHERE id = ?", [$userId]);
        
        if ($user && $user['role'] === 'admin') {
            echo json_encode(['success' => false, 'message' => 'Impossible de supprimer un administrateur']);
            return;
        }

        // Supprimer l'utilisateur et tout son contenu
        try {
            // Supprimer les posts
            $db->execute("UPDATE posts SET status = 'deleted' WHERE user_id = ?", [$userId]);
            
            // Supprimer les tutoriels
            $db->execute("UPDATE tutorials SET status = 'deleted' WHERE user_id = ?", [$userId]);
            
            // Supprimer les commentaires
            $db->execute("UPDATE comments SET status = 'deleted' WHERE user_id = ?", [$userId]);
            
            // Supprimer les projets (soft delete)
            $db->execute("UPDATE projects SET status = 'deleted' WHERE owner_id = ?", [$userId]);
            
            // Marquer l'utilisateur comme supprimé
            $db->execute("UPDATE users SET status = 'banned', email = CONCAT('deleted_', id, '_', email) WHERE id = ?", [$userId]);
            
            echo json_encode(['success' => true, 'message' => 'Utilisateur et son contenu supprimés']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    /**
     * ======================================
     * GESTION DES CATÉGORIES DU FORUM
     * ======================================
     */

    /**
     * API: Récupérer toutes les catégories du forum
     */
    public function getForumCategories()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $categoryModel = $this->model('ForumCategory');
        $categories = $categoryModel->getAll();

        echo json_encode(['success' => true, 'categories' => $categories]);
    }

    /**
     * API: Créer une catégorie de forum
     */
    public function createForumCategory()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $name = Security::clean($_POST['name'] ?? '');
        $description = Security::clean($_POST['description'] ?? '');
        $icon = Security::clean($_POST['icon'] ?? 'fa-folder');
        $color = Security::clean($_POST['color'] ?? '#667eea');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Le nom est requis']);
            return;
        }

        $categoryModel = $this->model('ForumCategory');
        $categoryId = $categoryModel->createCategory([
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'color' => $color
        ]);

        if ($categoryId) {
            echo json_encode(['success' => true, 'message' => 'Catégorie créée avec succès', 'id' => $categoryId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
        }
    }

    /**
     * API: Mettre à jour une catégorie de forum
     */
    public function updateForumCategory()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = Security::clean($_POST['name'] ?? '');
        $description = Security::clean($_POST['description'] ?? '');
        $icon = Security::clean($_POST['icon'] ?? '');
        $color = Security::clean($_POST['color'] ?? '');
        $is_active = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : null;

        if (!$id || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $categoryModel = $this->model('ForumCategory');
        $data = ['name' => $name];
        
        if ($description !== '') $data['description'] = $description;
        if ($icon) $data['icon'] = $icon;
        if ($color) $data['color'] = $color;
        if ($is_active !== null) $data['is_active'] = $is_active ? 1 : 0;

        if ($categoryModel->updateCategory($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Catégorie mise à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Supprimer une catégorie de forum
     */
    public function deleteForumCategory()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $categoryModel = $this->model('ForumCategory');
        
        if ($categoryModel->deleteCategory($id)) {
            echo json_encode(['success' => true, 'message' => 'Catégorie supprimée']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Impossible de supprimer : des posts utilisent cette catégorie']);
        }
    }

    /**
     * API: Activer/Désactiver une catégorie
     */
    public function toggleForumCategory()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 1);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $categoryModel = $this->model('ForumCategory');
        
        if ($categoryModel->update($id, ['is_active' => $is_active])) {
            $status = $is_active ? 'activée' : 'désactivée';
            echo json_encode(['success' => true, 'message' => "Catégorie $status"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * ======================================
     * GESTION DES DISCUSSIONS DU FORUM
     * ======================================
     */

    /**
     * API: Récupérer les discussions du forum
     */
    public function getForumPosts()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $status = $_GET['status'] ?? null;
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;

        $db = Database::getInstance();
        
        $query = "SELECT 
            p.id, p.title, p.category, p.body, 
            p.views, p.is_pinned, p.is_locked, p.status,
            p.created_at, p.updated_at,
            CONCAT(u.prenom, ' ', u.nom) as author_name,
            u.photo_path as author_photo,
            COUNT(DISTINCT c.id) as comments_count,
            COUNT(DISTINCT l.id) as likes_count
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN comments c ON c.commentable_type = 'post' AND c.commentable_id = p.id AND c.status = 'active'
            LEFT JOIN likes l ON l.likeable_type = 'post' AND l.likeable_id = p.id
            WHERE 1=1";
        
        $params = [];

        if ($status) {
            $query .= " AND p.status = ?";
            $params[] = $status;
        } else {
            // Par défaut, ne pas afficher les posts supprimés
            $query .= " AND p.status != 'deleted'";
        }

        if ($category) {
            $query .= " AND p.category = ?";
            $params[] = $category;
        }

        if ($search) {
            $query .= " AND (p.title LIKE ? OR p.body LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " GROUP BY p.id ORDER BY p.is_pinned DESC, p.created_at DESC LIMIT 100";
        $posts = $db->query($query, $params);

        echo json_encode(['success' => true, 'posts' => $posts]);
    }

    /**
     * API: Épingler/Désépingler une discussion
     */
    public function togglePinPost()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $isPinned = (int)($_POST['is_pinned'] ?? 0);

        if (!$postId) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("UPDATE posts SET is_pinned = ? WHERE id = ?", [$isPinned, $postId])) {
            $status = $isPinned ? 'épinglée' : 'désépinglée';
            echo json_encode(['success' => true, 'message' => "Discussion $status"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Verrouiller/Déverrouiller une discussion
     */
    public function toggleLockPost()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $isLocked = (int)($_POST['is_locked'] ?? 0);

        if (!$postId) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("UPDATE posts SET is_locked = ? WHERE id = ?", [$isLocked, $postId])) {
            $status = $isLocked ? 'verrouillée' : 'déverrouillée';
            echo json_encode(['success' => true, 'message' => "Discussion $status"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Changer le statut d'une discussion
     */
    public function updatePostStatus()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $status = Security::clean($_POST['status'] ?? '');

        if (!$postId || !in_array($status, ['active', 'hidden', 'deleted'])) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("UPDATE posts SET status = ? WHERE id = ?", [$status, $postId])) {
            echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * ======================================
     * GESTION DES TUTORIELS
     * ======================================
     */

    /**
     * API: Récupérer les tutoriels
     */
    public function getTutorialsData()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;
        $category = $_GET['category'] ?? null;

        $db = Database::getInstance();
        
        $query = "SELECT 
            t.id, t.title, t.description, t.type, t.category, 
            t.file_path, t.views, t.downloads, t.status, t.level,
            t.created_at, t.updated_at,
            CONCAT(u.prenom, ' ', u.nom) as author_name,
            u.photo_path as author_photo,
            COUNT(DISTINCT c.id) as comments_count,
            COUNT(DISTINCT l.id) as likes_count
            FROM tutorials t
            INNER JOIN users u ON t.user_id = u.id
            LEFT JOIN comments c ON c.commentable_type = 'tutorial' AND c.commentable_id = t.id AND c.status = 'active'
            LEFT JOIN likes l ON l.likeable_type = 'tutorial' AND l.likeable_id = t.id
            WHERE 1=1";
        
        $params = [];

        if ($status) {
            $query .= " AND t.status = ?";
            $params[] = $status;
        }

        if ($type) {
            $query .= " AND t.type = ?";
            $params[] = $type;
        }

        if ($category) {
            $query .= " AND t.category = ?";
            $params[] = $category;
        }

        $query .= " GROUP BY t.id ORDER BY t.created_at DESC LIMIT 100";
        $tutorials = $db->query($query, $params);

        echo json_encode(['success' => true, 'tutorials' => $tutorials]);
    }

    /**
     * API: Changer le statut d'un tutoriel
     */
    public function updateTutorialStatus()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $tutorialId = (int)($_POST['tutorial_id'] ?? 0);
        $status = Security::clean($_POST['status'] ?? '');

        if (!$tutorialId || !in_array($status, ['pending', 'active', 'hidden', 'deleted'])) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("UPDATE tutorials SET status = ? WHERE id = ?", [$status, $tutorialId])) {
            echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Approuver un tutoriel en attente
     */
    public function approveTutorial()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $tutorialId = (int)($_POST['tutorial_id'] ?? 0);

        if (!$tutorialId) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("UPDATE tutorials SET status = 'active' WHERE id = ?", [$tutorialId])) {
            echo json_encode(['success' => true, 'message' => 'Tutoriel approuvé et publié']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'approbation']);
        }
    }

    /**
     * ======================================
     * GESTION DES PROJETS
     * ======================================
     */

    /**
     * API: Récupérer les projets
     */
    public function getProjectsData()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $status = $_GET['status'] ?? null;
        $visibility = $_GET['visibility'] ?? null;

        $db = Database::getInstance();
        
        $query = "SELECT 
            p.id, p.title, p.description, 
            p.github_link, p.demo_link,
            p.status, p.visibility, p.looking_for_members,
            p.created_at, p.updated_at,
            CONCAT(u.prenom, ' ', u.nom) as owner_name,
            u.photo_path as owner_photo,
            COUNT(DISTINCT pm.id) as members_count
            FROM projects p
            INNER JOIN users u ON p.owner_id = u.id
            LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.status = 'active'
            WHERE p.status != 'deleted'";
        
        $params = [];

        if ($status) {
            $query .= " AND p.status = ?";
            $params[] = $status;
        }

        if ($visibility) {
            $query .= " AND p.visibility = ?";
            $params[] = $visibility;
        }

        $query .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT 100";
        $projects = $db->query($query, $params);

        echo json_encode(['success' => true, 'projects' => $projects]);
    }

    /**
     * API: Changer le statut d'un projet
     */
    public function updateProjectStatus()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $status = Security::clean($_POST['status'] ?? '');

        if (!$projectId || !in_array($status, ['planning', 'in_progress', 'completed', 'archived', 'deleted'])) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("UPDATE projects SET status = ? WHERE id = ?", [$status, $projectId])) {
            echo json_encode(['success' => true, 'message' => 'Statut du projet mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Changer la visibilité d'un projet
     */
    public function toggleProjectVisibility()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $visibility = Security::clean($_POST['visibility'] ?? 'public');

        if (!$projectId || !in_array($visibility, ['public', 'private'])) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("UPDATE projects SET visibility = ? WHERE id = ?", [$visibility, $projectId])) {
            $status = $visibility === 'public' ? 'public' : 'privé';
            echo json_encode(['success' => true, 'message' => "Projet rendu $status"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * ======================================
     * GESTION DU BLOG
     * ======================================
     */

    /**
     * API: Récupérer les articles de blog
     */
    public function blog()
    {
        $this->getBlogPostsData();
    }

    /**
     * API: Récupérer les articles de blog
     */
    public function getBlogPostsData()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $status = $_GET['status'] ?? null;
        $category = $_GET['category'] ?? null;

        $db = Database::getInstance();
        
        $query = "SELECT 
            bp.id, bp.title, bp.slug, bp.excerpt, 
            bp.category, bp.featured_image,
            bp.status, bp.views, 
            bp.published_at, bp.created_at, bp.updated_at,
            CONCAT(u.prenom, ' ', u.nom) as author_name,
            u.photo_path as author_photo,
            COUNT(DISTINCT c.id) as comments_count,
            COUNT(DISTINCT l.id) as likes_count
            FROM blog_posts bp
            INNER JOIN users u ON bp.author_id = u.id
            LEFT JOIN comments c ON c.commentable_type = 'blog' AND c.commentable_id = bp.id AND c.status = 'active'
            LEFT JOIN likes l ON l.likeable_type = 'blog' AND l.likeable_id = bp.id
            WHERE 1=1";
        
        $params = [];

        if ($status) {
            $query .= " AND bp.status = ?";
            $params[] = $status;
        }

        if ($category) {
            $query .= " AND bp.category = ?";
            $params[] = $category;
        }

        $query .= " GROUP BY bp.id ORDER BY bp.created_at DESC LIMIT 100";
        $posts = $db->query($query, $params);

        echo json_encode(['success' => true, 'posts' => $posts]);
    }

    /**
     * API: Changer le statut d'un article de blog
     */
    public function blogStatus()
    {
        $this->updateBlogPostStatus();
    }

    /**
     * API: Changer le statut d'un article de blog
     */
    public function updateBlogPostStatus()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $status = Security::clean($_POST['status'] ?? '');

        if (!$postId || !in_array($status, ['draft', 'published', 'archived'])) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $db = Database::getInstance();
        
        // Si on publie un brouillon, mettre à jour published_at
        if ($status === 'published') {
            $db->execute("UPDATE blog_posts SET status = ?, published_at = NOW() WHERE id = ?", [$status, $postId]);
        } else {
            $db->execute("UPDATE blog_posts SET status = ? WHERE id = ?", [$status, $postId]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Statut de l\'article mis à jour']);
    }

    /**
     * API: Supprimer un article de blog
     */
    public function deleteBlog()
    {
        $this->deleteBlogPost();
    }

    /**
     * API: Supprimer un article de blog
     */
    public function deleteBlogPost()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $postId = (int)($_POST['post_id'] ?? 0);

        if (!$postId) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();
        
        // Supprimer l'article et ses commentaires/likes associés
        try {
            $db->execute("DELETE FROM comments WHERE commentable_type = 'blog' AND commentable_id = ?", [$postId]);
            $db->execute("DELETE FROM likes WHERE likeable_type = 'blog' AND likeable_id = ?", [$postId]);
            $db->execute("DELETE FROM blog_posts WHERE id = ?", [$postId]);
            
            echo json_encode(['success' => true, 'message' => 'Article supprimé']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    }

    /**
     * ======================================
     * GESTION DES CATÉGORIES DE BLOG
     * ======================================
     */

    /**
     * Générer un slug à partir d'un texte
     */
    private function generateSlug($text)
    {
        // Remplacer les caractères accentués
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        
        // Convertir en minuscules
        $text = strtolower($text);
        
        // Remplacer tout ce qui n'est pas alphanumérique par un tiret
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Supprimer les tirets en début et fin
        $text = trim($text, '-');
        
        return $text;
    }

    /**
     * API: Récupérer toutes les catégories de blog
     */
    public function blogCategories()
    {
        $this->getBlogCategories();
    }

    /**
     * API: Récupérer toutes les catégories de blog
     */
    public function getBlogCategories()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $db = Database::getInstance();
        $categories = $db->query("
            SELECT * FROM blog_categories 
            ORDER BY name ASC
        ");

        echo json_encode(['success' => true, 'categories' => $categories]);
    }

    /**
     * API: Créer une catégorie de blog
     */
    public function createBlogCategory()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $name = Security::clean($_POST['name'] ?? '');
        $description = Security::clean($_POST['description'] ?? '');
        $icon = Security::clean($_POST['icon'] ?? 'fa-folder');
        $color = Security::clean($_POST['color'] ?? '#667eea');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Le nom est requis']);
            return;
        }

        $slug = $this->generateSlug($name);
        $db = Database::getInstance();

        try {
            $db->execute(
                "INSERT INTO blog_categories (name, slug, description, icon, color) VALUES (?, ?, ?, ?, ?)",
                [$name, $slug, $description, $icon, $color]
            );

            echo json_encode(['success' => true, 'message' => 'Catégorie créée avec succès']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Mettre à jour une catégorie de blog
     */
    public function updateBlogCategory()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = Security::clean($_POST['name'] ?? '');
        $description = Security::clean($_POST['description'] ?? '');
        $icon = Security::clean($_POST['icon'] ?? 'fa-folder');
        $color = Security::clean($_POST['color'] ?? '#667eea');

        if (!$id || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $slug = $this->generateSlug($name);
        $db = Database::getInstance();

        try {
            $db->execute(
                "UPDATE blog_categories SET name = ?, slug = ?, description = ?, icon = ?, color = ? WHERE id = ?",
                [$name, $slug, $description, $icon, $color, $id]
            );

            echo json_encode(['success' => true, 'message' => 'Catégorie mise à jour']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Supprimer une catégorie de blog
     */
    public function deleteBlogCategory()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();

        // Vérifier s'il y a des articles dans cette catégorie
        $count = $db->queryOne("SELECT COUNT(*) as count FROM blog_posts WHERE category_id = ?", [$id]);
        
        if ($count && $count['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Impossible de supprimer : cette catégorie contient des articles']);
            return;
        }

        try {
            $db->execute("DELETE FROM blog_categories WHERE id = ?", [$id]);
            echo json_encode(['success' => true, 'message' => 'Catégorie supprimée']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Activer/Désactiver une catégorie de blog
     */
    public function toggleBlogCategory()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->execute(
                "UPDATE blog_categories SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?",
                [$id]
            );

            echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * ======================================
     * GESTION DES OPPORTUNITÉS (JOBS)
     * ======================================
     */

    /**
     * API: Récupérer toutes les opportunités
     */
    public function getJobsData()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $type = $_GET['type'] ?? null;
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;

        $db = Database::getInstance();
        
        $query = "SELECT 
            j.id, j.type, j.title, j.city, j.salary_range, 
            j.deadline, j.status, j.views, j.created_at,
            CONCAT(u.prenom, ' ', u.nom) as company_name,
            u.photo_path as company_photo,
            COUNT(DISTINCT a.id) as applications_count
            FROM jobs j
            INNER JOIN users u ON j.company_id = u.id
            LEFT JOIN applications a ON j.id = a.job_id
            WHERE 1=1";
        
        $params = [];

        if ($type) {
            $query .= " AND j.type = ?";
            $params[] = $type;
        }

        if ($status) {
            $query .= " AND j.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $query .= " AND (j.title LIKE ? OR j.description LIKE ? OR j.city LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " GROUP BY j.id ORDER BY j.created_at DESC LIMIT 100";
        $jobs = $db->query($query, $params);

        echo json_encode(['success' => true, 'jobs' => $jobs]);
    }

    /**
     * API: Mettre à jour le statut d'une opportunité
     */
    public function updateJobStatus()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $jobId = (int)($_POST['job_id'] ?? 0);
        $status = Security::clean($_POST['status'] ?? '');

        if (!$jobId || !in_array($status, ['pending', 'active', 'closed', 'expired'])) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("UPDATE jobs SET status = ? WHERE id = ?", [$status, $jobId])) {
            echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Supprimer une opportunité
     */
    public function deleteJob()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $jobId = (int)($_POST['job_id'] ?? 0);

        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();
        
        try {
            // Supprimer les candidatures associées
            $db->execute("DELETE FROM applications WHERE job_id = ?", [$jobId]);
            // Supprimer l'opportunité
            $db->execute("DELETE FROM jobs WHERE id = ?", [$jobId]);
            
            echo json_encode(['success' => true, 'message' => 'Opportunité supprimée']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    }

    /**
     * API: Récupérer les candidatures pour une opportunité
     */
    public function getJobApplications()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $jobId = (int)($_GET['job_id'] ?? 0);

        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();
        
        $applications = $db->query(
            "SELECT 
                a.id, a.cover_letter, a.status, a.created_at,
                CONCAT(u.prenom, ' ', u.nom) as applicant_name,
                u.photo_path as applicant_photo,
                u.email as applicant_email,
                u.phone as applicant_phone
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            WHERE a.job_id = ?
            ORDER BY a.created_at DESC",
            [$jobId]
        );

        echo json_encode(['success' => true, 'applications' => $applications]);
    }

    /**
     * ======================================
     * MODÉRATION DES COMMENTAIRES
     * ======================================
     */

    /**
     * API: Récupérer tous les commentaires
     */
    public function getCommentsData()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;

        $db = Database::getInstance();
        
        $query = "SELECT 
            c.id, c.body, c.commentable_type, c.commentable_id,
            c.status, c.created_at, c.updated_at,
            CONCAT(u.prenom, ' ', u.nom) as user_name,
            u.photo_path as user_photo,
            u.id as user_id
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE 1=1";
        
        $params = [];

        if ($status) {
            $query .= " AND c.status = ?";
            $params[] = $status;
        }

        if ($type) {
            $query .= " AND c.commentable_type = ?";
            $params[] = $type;
        }

        $query .= " ORDER BY c.created_at DESC LIMIT 200";
        $comments = $db->query($query, $params);

        // Enrichir avec les informations du contenu commenté
        foreach ($comments as &$comment) {
            $resourceTitle = $this->getResourceTitle($comment['commentable_type'], $comment['commentable_id']);
            $comment['resource_title'] = $resourceTitle;
        }

        echo json_encode(['success' => true, 'comments' => $comments]);
    }

    /**
     * Obtenir le titre de la ressource commentée
     */
    private function getResourceTitle($type, $id)
    {
        $db = Database::getInstance();
        
        switch ($type) {
            case 'post':
                $result = $db->queryOne("SELECT title FROM posts WHERE id = ?", [$id]);
                return $result ? $result['title'] : 'Post supprimé';
            
            case 'tutorial':
                $result = $db->queryOne("SELECT title FROM tutorials WHERE id = ?", [$id]);
                return $result ? $result['title'] : 'Tutoriel supprimé';
            
            case 'blog':
                $result = $db->queryOne("SELECT title FROM blog_posts WHERE id = ?", [$id]);
                return $result ? $result['title'] : 'Article supprimé';
            
            default:
                return 'Contenu inconnu';
        }
    }

    /**
     * API: Changer le statut d'un commentaire
     */
    public function updateCommentStatus()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $status = Security::clean($_POST['status'] ?? '');

        if (!$commentId || !in_array($status, ['active', 'hidden', 'deleted'])) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("UPDATE comments SET status = ? WHERE id = ?", [$status, $commentId])) {
            echo json_encode(['success' => true, 'message' => 'Commentaire modéré']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Supprimer définitivement un commentaire
     */
    public function deleteComment()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);

        if (!$commentId) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("DELETE FROM comments WHERE id = ?", [$commentId])) {
            echo json_encode(['success' => true, 'message' => 'Commentaire supprimé']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    }

    /**
     * ======================================
     * GESTION DES SIGNALEMENTS
     * ======================================
     */

    /**
     * API: Récupérer tous les signalements
     */
    public function getReportsData()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $status = $_GET['status'] ?? 'pending';
        $type = $_GET['type'] ?? null;

        $db = Database::getInstance();
        
        $query = "SELECT 
            r.id, r.reportable_type, r.reportable_id,
            r.reason, r.status, r.admin_note,
            r.created_at, r.updated_at,
            CONCAT(u.prenom, ' ', u.nom) as reporter_name,
            u.photo_path as reporter_photo,
            u.id as reporter_id
            FROM reports r
            INNER JOIN users u ON r.reporter_id = u.id
            WHERE 1=1";
        
        $params = [];

        if ($status) {
            $query .= " AND r.status = ?";
            $params[] = $status;
        }

        if ($type) {
            $query .= " AND r.reportable_type = ?";
            $params[] = $type;
        }

        $query .= " ORDER BY 
            CASE r.status 
                WHEN 'pending' THEN 1 
                WHEN 'reviewed' THEN 2 
                WHEN 'resolved' THEN 3 
                WHEN 'dismissed' THEN 4 
            END,
            r.created_at DESC 
            LIMIT 200";
        $reports = $db->query($query, $params);

        // Enrichir avec les informations du contenu signalé
        foreach ($reports as &$report) {
            $resourceInfo = $this->getReportableInfo($report['reportable_type'], $report['reportable_id']);
            $report['resource_title'] = $resourceInfo['title'];
            $report['resource_author'] = $resourceInfo['author'];
        }

        echo json_encode(['success' => true, 'reports' => $reports]);
    }

    /**
     * Obtenir les infos du contenu signalé
     */
    private function getReportableInfo($type, $id)
    {
        $db = Database::getInstance();
        
        switch ($type) {
            case 'post':
                $result = $db->queryOne("SELECT p.title, CONCAT(u.prenom, ' ', u.nom) as author 
                                        FROM posts p 
                                        INNER JOIN users u ON p.user_id = u.id 
                                        WHERE p.id = ?", [$id]);
                return $result ?: ['title' => 'Post supprimé', 'author' => '-'];
            
            case 'tutorial':
                $result = $db->queryOne("SELECT t.title, CONCAT(u.prenom, ' ', u.nom) as author 
                                        FROM tutorials t 
                                        INNER JOIN users u ON t.user_id = u.id 
                                        WHERE t.id = ?", [$id]);
                return $result ?: ['title' => 'Tutoriel supprimé', 'author' => '-'];
            
            case 'comment':
                $result = $db->queryOne("SELECT c.body as title, CONCAT(u.prenom, ' ', u.nom) as author 
                                        FROM comments c 
                                        INNER JOIN users u ON c.user_id = u.id 
                                        WHERE c.id = ?", [$id]);
                if ($result) {
                    $result['title'] = substr($result['title'], 0, 50) . '...';
                }
                return $result ?: ['title' => 'Commentaire supprimé', 'author' => '-'];
            
            case 'user':
                $result = $db->queryOne("SELECT CONCAT(prenom, ' ', nom) as title, email as author 
                                        FROM users WHERE id = ?", [$id]);
                return $result ?: ['title' => 'Utilisateur supprimé', 'author' => '-'];
            
            default:
                return ['title' => 'Contenu inconnu', 'author' => '-'];
        }
    }

    /**
     * API: Changer le statut d'un signalement
     */
    public function updateReportStatus()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $reportId = (int)($_POST['report_id'] ?? 0);
        $status = Security::clean($_POST['status'] ?? '');
        $adminNote = Security::clean($_POST['admin_note'] ?? '');

        if (!$reportId || !in_array($status, ['pending', 'reviewed', 'resolved', 'dismissed'])) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $db = Database::getInstance();
        
        $data = ['status' => $status];
        if ($adminNote) {
            $data['admin_note'] = $adminNote;
        }
        
        $query = "UPDATE reports SET status = ?, admin_note = ?, updated_at = NOW() WHERE id = ?";
        
        if ($db->execute($query, [$status, $adminNote, $reportId])) {
            echo json_encode(['success' => true, 'message' => 'Signalement traité']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Supprimer un signalement
     */
    public function deleteReport()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $reportId = (int)($_POST['report_id'] ?? 0);

        if (!$reportId) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        $db = Database::getInstance();
        
        if ($db->execute("DELETE FROM reports WHERE id = ?", [$reportId])) {
            echo json_encode(['success' => true, 'message' => 'Signalement supprimé']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    }

    /**
     * ======================================
     * GESTION DES PARAMÈTRES SYSTÈME
     * ======================================
     */

    /**
     * API: Récupérer tous les paramètres
     */
    public function getSettings()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        try {
            $db = Database::getInstance();
            $settings = $db->query("SELECT * FROM system_settings ORDER BY category, setting_key");

            // Organiser par catégorie
            $organized = [];
            foreach ($settings as $setting) {
                $category = $setting['category'];
                if (!isset($organized[$category])) {
                    $organized[$category] = [];
                }
                $organized[$category][] = $setting;
            }

            echo json_encode(['success' => true, 'settings' => $organized]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Mettre à jour un paramètre
     */
    public function updateSetting()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $key = Security::clean($_POST['key'] ?? '');
        $value = Security::clean($_POST['value'] ?? '');
        $category = Security::clean($_POST['category'] ?? 'general');

        if (empty($key)) {
            echo json_encode(['success' => false, 'message' => 'Clé invalide']);
            return;
        }

        $settingModel = $this->model('SystemSetting');
        $db = Database::getInstance();
        
        // Vérifier si le paramètre existe
        $existing = $db->queryOne("SELECT * FROM system_settings WHERE setting_key = ?", [$key]);
        
        if (!$existing) {
            // Créer le paramètre s'il n'existe pas
            $settingType = is_numeric($value) ? 'number' : (in_array(strtolower($value), ['true', 'false', '1', '0']) ? 'boolean' : 'text');
            $description = $this->getDefaultDescription($key);
            
            $db->execute(
                "INSERT INTO system_settings (setting_key, setting_value, setting_type, category, description, updated_by) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$key, $value, $settingType, $category, $description, $_SESSION['user_id']]
            );
            
            echo json_encode(['success' => true, 'message' => 'Paramètre créé et sauvegardé']);
        } else {
            // Mettre à jour le paramètre existant
            if ($settingModel->set($key, $value, $_SESSION['user_id'])) {
                echo json_encode(['success' => true, 'message' => 'Paramètre mis à jour']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            }
        }
    }

    /**
     * API: Mettre à jour plusieurs paramètres
     */
    public function updateSettings()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $settings = $_POST['settings'] ?? [];

        if (empty($settings) || !is_array($settings)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        $settingModel = $this->model('SystemSetting');
        
        if ($settingModel->updateMultiple($settings, $_SESSION['user_id'])) {
            echo json_encode(['success' => true, 'message' => 'Paramètres mis à jour avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * API: Obtenir les statistiques système
     */
    public function getSystemStats()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $db = Database::getInstance();
        
        $stats = [
            'database_size' => $this->getDatabaseSize(),
            'total_tables' => $this->getTableCount(),
            'disk_usage' => $this->getDiskUsage(),
            'cache_size' => $this->getCacheSize(),
            'php_version' => phpversion(),
            'mysql_version' => $this->getMySQLVersion()
        ];

        echo json_encode(['success' => true, 'stats' => $stats]);
    }

    /**
     * Helper: Taille de la base de données
     */
    private function getDatabaseSize()
    {
        $db = Database::getInstance();
        $result = $db->queryOne("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.TABLES 
            WHERE table_schema = DATABASE()
        ");
        return ($result['size_mb'] ?? 0) . ' MB';
    }

    /**
     * Helper: Nombre de tables
     */
    private function getTableCount()
    {
        $db = Database::getInstance();
        $result = $db->queryOne("
            SELECT COUNT(*) as count
            FROM information_schema.TABLES 
            WHERE table_schema = DATABASE()
        ");
        return $result['count'] ?? 0;
    }

    /**
     * Helper: Utilisation disque
     */
    private function getDiskUsage()
    {
        $uploadsPath = __DIR__ . '/../../uploads';
        if (is_dir($uploadsPath)) {
            $size = $this->getDirSize($uploadsPath);
            return round($size / 1024 / 1024, 2) . ' MB';
        }
        return '0 MB';
    }

    /**
     * Helper: Taille d'un dossier
     */
    private function getDirSize($directory)
    {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    /**
     * Helper: Taille du cache
     */
    private function getCacheSize()
    {
        return '0 MB'; // Placeholder
    }

    /**
     * Helper: Version MySQL
     */
    private function getMySQLVersion()
    {
        $db = Database::getInstance();
        $result = $db->queryOne("SELECT VERSION() as version");
        return $result['version'] ?? 'Inconnu';
    }

    /**
     * Helper: Description par défaut pour un paramètre
     */
    private function getDefaultDescription($key)
    {
        $descriptions = [
            'auto_moderate_posts' => 'Modération automatique des posts (nécessite validation avant publication)',
            'auto_moderate_comments' => 'Modération automatique des commentaires',
            'moderation_keywords' => 'Mots-clés à surveiller (séparés par des virgules)',
            'max_reports_before_hide' => 'Nombre de signalements avant masquage automatique',
            'require_email_verification' => 'Vérification email requise pour publier',
            'spam_detection_enabled' => 'Activer la détection de spam',
            'min_account_age_to_post' => 'Âge minimum du compte pour publier (en jours)',
            'max_posts_per_day' => 'Nombre maximum de posts par jour par utilisateur'
        ];
        
        return $descriptions[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * ======================================
     * STATISTIQUES AVANCÉES
     * ======================================
     */

    /**
     * API: Statistiques complètes (en ligne, visiteurs, top users, etc.)
     */
    public function getAdvancedStatistics()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $db = Database::getInstance();

        // Nettoyer les anciennes entrées (> 5 minutes = offline)
        $db->execute("DELETE FROM online_users WHERE last_seen < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");

        // Utilisateurs en ligne maintenant
        $onlineCount = $db->queryOne("SELECT COUNT(DISTINCT session_id) as count FROM online_users")['count'] ?? 0;

        // Visites aujourd'hui
        $todayVisits = $db->queryOne("
            SELECT COUNT(DISTINCT session_id) as count 
            FROM visitor_logs 
            WHERE DATE(created_at) = CURDATE()
        ")['count'] ?? 0;

        // Nombre de pays différents (30 derniers jours)
        $countriesCount = $db->queryOne("
            SELECT COUNT(DISTINCT country) as count 
            FROM visitor_logs 
            WHERE country IS NOT NULL 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")['count'] ?? 0;

        // Top user (7 derniers jours)
        $topUser = $db->queryOne("
            SELECT 
                u.prenom, 
                u.nom,
                SUM(ua.count) as total_activities
            FROM user_activities ua
            JOIN users u ON ua.user_id = u.id
            WHERE ua.activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY ua.user_id
            ORDER BY total_activities DESC
            LIMIT 1
        ");

        $topUserName = $topUser ? $topUser['prenom'] . ' ' . $topUser['nom'] : '-';

        // Utilisateurs en ligne (détails)
        $onlineUsers = $db->query("
            SELECT 
                ou.session_id,
                ou.user_id,
                ou.ip_address,
                ou.last_seen,
                ou.page_url,
                u.prenom,
                u.nom,
                u.email,
                u.photo_path
            FROM online_users ou
            LEFT JOIN users u ON ou.user_id = u.id
            ORDER BY ou.last_seen DESC
            LIMIT 50
        ");

        // Top utilisateurs actifs (7 derniers jours)
        $topUsers = $db->query("
            SELECT 
                u.id,
                u.prenom,
                u.nom,
                u.email,
                u.photo_path,
                SUM(ua.count) as total_activities,
                SUM(CASE WHEN ua.activity_type = 'post' THEN ua.count ELSE 0 END) as posts,
                SUM(CASE WHEN ua.activity_type = 'comment' THEN ua.count ELSE 0 END) as comments,
                SUM(CASE WHEN ua.activity_type = 'tutorial' THEN ua.count ELSE 0 END) as tutorials,
                SUM(CASE WHEN ua.activity_type = 'project' THEN ua.count ELSE 0 END) as projects
            FROM user_activities ua
            JOIN users u ON ua.user_id = u.id
            WHERE ua.activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY ua.user_id
            ORDER BY total_activities DESC
            LIMIT 10
        ");

        // Visiteurs par pays (30 derniers jours)
        $countriesData = $db->query("
            SELECT 
                country, 
                COUNT(DISTINCT session_id) as visitors
            FROM visitor_logs
            WHERE country IS NOT NULL 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY country
            ORDER BY visitors DESC
            LIMIT 10
        ");

        // Répartition des appareils (30 derniers jours)
        $devicesData = $db->query("
            SELECT 
                device_type, 
                COUNT(DISTINCT session_id) as count
            FROM visitor_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY device_type
        ");

        // Historique des visites (200 plus récentes)
        $visitorLogs = $db->query("
            SELECT 
                vl.id,
                vl.user_id,
                vl.ip_address,
                vl.country,
                vl.city,
                vl.device_type,
                vl.browser,
                vl.os,
                vl.page_url,
                vl.created_at,
                u.prenom,
                u.nom,
                u.email
            FROM visitor_logs vl
            LEFT JOIN users u ON vl.user_id = u.id
            ORDER BY vl.created_at DESC
            LIMIT 200
        ");

        echo json_encode([
            'success' => true,
            'summary' => [
                'online' => $onlineCount,
                'today_visits' => $todayVisits,
                'countries' => $countriesCount,
                'top_user' => $topUserName
            ],
            'online_users' => $onlineUsers,
            'top_users' => $topUsers,
            'countries' => $countriesData,
            'devices' => $devicesData,
            'visitor_logs' => $visitorLogs
        ]);
    }

    /**
     * ======================================
     * DONNÉES POUR LES GRAPHIQUES
     * ======================================
     */

    /**
     * API: Données pour les graphiques du dashboard
     */
    public function getChartData()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $db = Database::getInstance();
        
        // Activité des utilisateurs (7 derniers jours)
        $activityData = $db->query("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM users
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        
        // Préparer les données pour les 7 derniers jours (même si aucune inscription)
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $last7Days[$date] = 0;
        }
        
        foreach ($activityData as $data) {
            $last7Days[$data['date']] = (int)$data['count'];
        }
        
        // Répartition du contenu
        $contentStats = [
            'posts' => $db->queryOne("SELECT COUNT(*) as count FROM posts WHERE status = 'active'")['count'] ?? 0,
            'tutorials' => $db->queryOne("SELECT COUNT(*) as count FROM tutorials WHERE status = 'active'")['count'] ?? 0,
            'projects' => $db->queryOne("SELECT COUNT(*) as count FROM projects WHERE status != 'deleted'")['count'] ?? 0,
            'blog' => $db->queryOne("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'")['count'] ?? 0
        ];
        
        echo json_encode([
            'success' => true,
            'activity' => [
                'labels' => array_keys($last7Days),
                'data' => array_values($last7Days)
            ],
            'content' => $contentStats
        ]);
    }

    /**
     * ======================================
     * SAUVEGARDE DE LA BASE DE DONNÉES
     * ======================================
     */

    /**
     * Télécharger un dump SQL de la base de données
     */
    public function downloadDatabaseBackup()
    {
        $this->requireAdmin();

        $type = $_GET['type'] ?? 'full'; // 'full' ou 'structure'
        
        $dbName = DB_NAME;
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "hubtech_backup_{$timestamp}.sql";
        
        // Headers pour le téléchargement
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $db = Database::getInstance();
        
        echo "-- ================================================================\n";
        echo "-- Sauvegarde de la base de données HubTech\n";
        echo "-- Date: " . date('Y-m-d H:i:s') . "\n";
        echo "-- Base de données: {$dbName}\n";
        echo "-- Type: " . ($type === 'structure' ? 'Structure uniquement' : 'Structure + Données') . "\n";
        echo "-- ================================================================\n\n";
        echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        echo "SET time_zone = \"+00:00\";\n\n";
        echo "CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
        echo "USE `{$dbName}`;\n\n";
        
        // Récupérer toutes les tables
        $tables = $db->query("SHOW TABLES");
        
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            
            echo "-- ================================================================\n";
            echo "-- Table: {$tableName}\n";
            echo "-- ================================================================\n";
            
            // Structure de la table
            echo "DROP TABLE IF EXISTS `{$tableName}`;\n";
            
            $createTable = $db->queryOne("SHOW CREATE TABLE `{$tableName}`");
            echo $createTable['Create Table'] . ";\n\n";
            
            // Données (si type = 'full')
            if ($type === 'full') {
                $rows = $db->query("SELECT * FROM `{$tableName}`");
                
                if (!empty($rows)) {
                    echo "-- Données pour la table `{$tableName}`\n";
                    
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . addslashes($value) . "'";
                            }
                        }
                        
                        $columns = array_keys($row);
                        echo "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    echo "\n";
                }
            }
        }
        
        echo "-- ================================================================\n";
        echo "-- Fin de la sauvegarde\n";
        echo "-- ================================================================\n";
        
        // Enregistrer la date de dernière sauvegarde
        $settingModel = $this->model('SystemSetting');
        $settingModel->set('last_backup_date', date('Y-m-d H:i:s'), $_SESSION['user_id']);
        
        exit;
    }

    /**
     * API: Toggle une permission spécifique (grant/revoke)
     */
    public function togglePermission()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Méthode non autorisée'
            ]);
            return;
        }
        
        $userId = $_POST['user_id'] ?? '';
        $permissionType = $_POST['permission_type'] ?? '';
        $action = $_POST['action'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        // Vérifier le token CSRF
        if (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
            $this->json([
                'success' => false,
                'message' => 'Token de sécurité invalide'
            ]);
            return;
        }
        
        if (empty($userId) || empty($permissionType) || empty($action)) {
            $this->json([
                'success' => false,
                'message' => 'Paramètres manquants'
            ]);
            return;
        }
        
        $db = Database::getInstance();
        $userModel = $this->model('User');
        
        try {
            if ($permissionType === 'tutorial') {
                if ($action === 'grant') {
                    $userModel->grantTutorialPermission($userId);
                    $message = 'Permission de créer des tutoriels accordée';
                } else {
                    $userModel->revokeTutorialPermission($userId);
                    $message = 'Permission de créer des tutoriels retirée';
                }
            } elseif ($permissionType === 'project') {
                if ($action === 'grant') {
                    $userModel->grantProjectPermission($userId);
                    $message = 'Permission de créer des projets accordée';
                } else {
                    $userModel->revokeProjectPermission($userId);
                    $message = 'Permission de créer des projets retirée';
                }
            } else {
                throw new Exception('Type de permission invalide');
            }
            
            $this->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Accorder toutes les permissions à un utilisateur
     */
    public function grantAllPermissions()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Méthode non autorisée'
            ]);
            return;
        }
        
        $userId = $_POST['user_id'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        // Vérifier le token CSRF
        if (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
            $this->json([
                'success' => false,
                'message' => 'Token de sécurité invalide'
            ]);
            return;
        }
        
        if (empty($userId)) {
            $this->json([
                'success' => false,
                'message' => 'ID utilisateur manquant'
            ]);
            return;
        }
        
        try {
            $userModel = $this->model('User');
            $userModel->grantTutorialPermission($userId);
            $userModel->grantProjectPermission($userId);
            
            $this->json([
                'success' => true,
                'message' => 'Toutes les permissions ont été accordées'
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Retirer toutes les permissions à un utilisateur
     */
    public function revokeAllPermissions()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Méthode non autorisée'
            ]);
            return;
        }
        
        $userId = $_POST['user_id'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        // Vérifier le token CSRF
        if (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
            $this->json([
                'success' => false,
                'message' => 'Token de sécurité invalide'
            ]);
            return;
        }
        
        if (empty($userId)) {
            $this->json([
                'success' => false,
                'message' => 'ID utilisateur manquant'
            ]);
            return;
        }
        
        try {
            $userModel = $this->model('User');
            $userModel->revokeTutorialPermission($userId);
            $userModel->revokeProjectPermission($userId);
            
            $this->json([
                'success' => true,
                'message' => 'Toutes les permissions ont été retirées'
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
}