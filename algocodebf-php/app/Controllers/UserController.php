<?php
/**
 * Contrôleur des utilisateurs et profils
 */

class UserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Liste des membres
     */
    public function index()
    {
        $this->requireLogin();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * USERS_PER_PAGE;

        $users = $this->userModel->findAll('created_at', 'DESC');
        // Filtrer les utilisateurs actifs
        $users = array_filter($users, function($user) {
            return $user['status'] === 'active';
        });

        // Pagination simple
        $users = array_slice($users, $offset, USERS_PER_PAGE);

        // Calculer les statistiques
        $db = Database::getInstance();
        
        $totalMembers = $db->queryOne("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        $universities = $db->queryOne("SELECT COUNT(DISTINCT university) as total FROM users WHERE status = 'active' AND university IS NOT NULL AND university != ''");
        $skillsCount = $db->queryOne("SELECT COUNT(DISTINCT skill_id) as total FROM user_skills");
        
        // Nouveaux membres ce mois
        $newThisMonth = $db->queryOne("
            SELECT COUNT(*) as total 
            FROM users 
            WHERE status = 'active' 
              AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
        ");
        
        $stats = [
            'total_members' => $totalMembers['total'] ?? 0,
            'universities' => $universities['total'] ?? 0,
            'skills_count' => $skillsCount['total'] ?? 0,
            'new_this_month' => $newThisMonth['total'] ?? 0
        ];

        $data = [
            'title' => 'Membres - AlgoCodeBF',
            'users' => $users,
            'stats' => $stats,
            'page' => $page
        ];

        $this->view('users/index', $data);
    }

    /**
     * Suivre ou ne plus suivre un utilisateur
     */
    public function follow($userId = null)
    {
        // Vérifier la connexion
        $this->requireLogin();
        
        if (!$userId || $userId == $_SESSION['user_id']) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Action non autorisée']);
            return;
        }
        
        $db = Database::getInstance();
        
        // Vérifier si l'utilisateur existe
        $user = $db->queryOne("SELECT id FROM users WHERE id = ? AND status = 'active'", [$userId]);
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
            return;
        }
        
        // Vérifier si déjà suivi
        $isFollowing = $db->queryOne(
            "SELECT id FROM user_follows WHERE follower_id = ? AND following_id = ?",
            [$_SESSION['user_id'], $userId]
        );
        
        if ($isFollowing) {
            // Ne plus suivre
            $success = $db->execute(
                "DELETE FROM user_follows WHERE follower_id = ? AND following_id = ?",
                [$_SESSION['user_id'], $userId]
            );
            
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'action' => 'unfollow',
                    'message' => 'Vous ne suivez plus cet utilisateur'
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
            }
        } else {
            // Suivre
            $success = $db->execute(
                "INSERT INTO user_follows (follower_id, following_id) VALUES (?, ?)",
                [$_SESSION['user_id'], $userId]
            );
            
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'action' => 'follow',
                    'message' => 'Vous suivez maintenant cet utilisateur'
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout']);
            }
        }
    }

    /**
     * Profil d'un utilisateur
     */
    public function profile($userId = null)
    {
        $this->requireLogin();
        
        // Si pas d'ID fourni, afficher le profil de l'utilisateur connecté
        if ($userId === null) {
            $userId = $_SESSION['user_id'];
        }

        $user = $this->userModel->getProfile($userId);

        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable";
            $this->redirect('home/index');
        }

        // Récupérer les compétences
        $skills = $this->userModel->getSkills($userId);

        // Récupérer les badges
        $badgeModel = $this->model('Badge');
        $badges = $this->userModel->getBadges($userId);

        // Récupérer les posts récents (actifs uniquement)
        $postModel = $this->model('Post');
        $db = Database::getInstance();
        $posts = $db->query(
            "SELECT p.*, 
                    COUNT(DISTINCT c.id) as comments_count,
                    COUNT(DISTINCT l.id) as likes_count
             FROM posts p
             LEFT JOIN comments c ON c.commentable_type = 'post' AND c.commentable_id = p.id AND c.status = 'active'
             LEFT JOIN likes l ON l.likeable_type = 'post' AND l.likeable_id = p.id
             WHERE p.user_id = ? AND p.status = 'active'
             GROUP BY p.id
             ORDER BY p.created_at DESC
             LIMIT 5",
            [$userId]
        );

        // Récupérer les tutoriels (exclure les tutoriels supprimés)
        $tutorialModel = $this->model('Tutorial');
        $db = Database::getInstance();
        $tutorials = $db->query(
            "SELECT * FROM tutorials 
             WHERE user_id = ? AND status != 'deleted' 
             ORDER BY created_at DESC 
             LIMIT 5",
            [$userId]
        );

        // Récupérer les projets (exclure les projets supprimés)
        $projectModel = $this->model('Project');
        $db = Database::getInstance();
        $projects = $db->query(
            "SELECT p.*, 
                    COUNT(DISTINCT pm2.id) as members_count
             FROM projects p 
             INNER JOIN project_members pm ON p.id = pm.project_id 
             LEFT JOIN project_members pm2 ON p.id = pm2.project_id AND pm2.status = 'active'
             WHERE pm.user_id = ? AND pm.status = 'active' AND p.status != 'deleted'
             GROUP BY p.id
             ORDER BY pm.joined_at DESC LIMIT 5",
            [$userId]
        );

        // Vérifier si c'est le propriétaire du profil
        $isOwner = $this->isLoggedIn() && $_SESSION['user_id'] == $userId;

        // Vérifier si l'utilisateur connecté suit ce profil (si ce n'est pas son propre profil)
        $isFollowing = false;
        if (!$isOwner) {
            $followStatus = $db->queryOne(
                "SELECT id FROM user_follows WHERE follower_id = ? AND following_id = ?",
                [$_SESSION['user_id'], $userId]
            );
            $isFollowing = (bool) $followStatus;
        }

        $data = [
            'title' => $user['prenom'] . ' ' . $user['nom'] . ' - AlgoCodeBF',
            'user' => $user,
            'skills' => $skills,
            'badges' => $badges,
            'posts' => $posts,
            'tutorials' => $tutorials,
            'projects' => $projects,
            'is_own_profile' => $isOwner,
            'is_owner' => $isOwner,
            'is_following' => $isFollowing
        ];

        $this->view('users/profile', $data);
    }

    /**
     * Modifier le profil
     */
    public function edit()
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('user/edit');
            }

            $data = [
                'bio' => Security::clean($_POST['bio'] ?? ''),
                'university' => Security::clean($_POST['university'] ?? ''),
                'faculty' => Security::clean($_POST['faculty'] ?? ''),
                'city' => Security::clean($_POST['city'] ?? '')
            ];

            // Upload de la photo de profil
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Validation robuste du fichier
                $validation = FileValidator::validateAvatar($_FILES['photo']);
                
                if (!$validation['valid']) {
                    $_SESSION['error'] = "❌ Photo de profil : " . $validation['error'];
                    $this->redirect('user/edit');
                }
                
                $uploadDir = UPLOADS . '/profiles/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = FileValidator::generateSecureFileName($_FILES['photo']['name'], 'profile');
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $filepath)) {
                    // Supprimer l'ancienne photo
                    if ($user['photo_path'] && file_exists(ROOT . '/public/' . $user['photo_path'])) {
                        unlink(ROOT . '/public/' . $user['photo_path']);
                    }
                    $data['photo_path'] = 'uploads/profiles/' . $filename;
                } else {
                    $_SESSION['error'] = "❌ Erreur lors de l'upload de la photo";
                    $this->redirect('user/edit');
                }
            }

            // Upload du CV
            if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Validation robuste du fichier
                $validation = FileValidator::validateCV($_FILES['cv']);
                
                if (!$validation['valid']) {
                    $_SESSION['error'] = "❌ CV : " . $validation['error'];
                    $this->redirect('user/edit');
                }
                
                $uploadDir = UPLOADS . '/cvs/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = FileValidator::generateSecureFileName($_FILES['cv']['name'], 'cv');
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['cv']['tmp_name'], $filepath)) {
                    // Supprimer l'ancien CV
                    if ($user['cv_path'] && file_exists(ROOT . '/public/' . $user['cv_path'])) {
                        unlink(ROOT . '/public/' . $user['cv_path']);
                    }
                    $data['cv_path'] = 'uploads/cvs/' . $filename;
                } else {
                    $_SESSION['error'] = "❌ Erreur lors de l'upload du CV";
                    $this->redirect('user/edit');
                }
            }

            // Mettre à jour le profil
            if ($this->userModel->update($userId, $data)) {
                // Mettre à jour les compétences
                if (isset($_POST['skills']) && is_array($_POST['skills'])) {
                    $this->userModel->updateSkills($userId, $_POST['skills']);
                }

                $_SESSION['success'] = "Profil mis à jour avec succès";
                $this->redirect('user/profile');
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du profil";
                $this->redirect('user/edit');
            }
        }

        // Récupérer toutes les compétences disponibles
        $db = Database::getInstance();
        $allSkills = $db->query("SELECT * FROM skills ORDER BY category, name");

        // Récupérer les compétences de l'utilisateur
        $userSkills = $this->userModel->getSkills($userId);

        $data = [
            'title' => 'Modifier le profil - AlgoCodeBF',
            'user' => $user,
            'all_skills' => $allSkills,
            'user_skills' => $userSkills,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('users/edit', $data);
    }

    /**
     * Classement des utilisateurs
     */
    public function leaderboard()
    {
        $this->requireLogin();
        
        $period = $_GET['period'] ?? 'month';
        $leaderboard = $this->userModel->getLeaderboard($period, 50);

        $data = [
            'title' => 'Classement - AlgoCodeBF',
            'leaderboard' => $leaderboard,
            'period' => $period
        ];

        $this->view('users/leaderboard', $data);
    }

    /**
     * API pour filtrer les membres de manière asynchrone
     */
    public function filterMembers()
    {
        // Vérifier que c'est une requête AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(400);
            echo json_encode(['error' => 'Requête AJAX requise']);
            return;
        }

        // Vérifier la session
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            return;
        }

        try {
            // Récupérer les paramètres de filtrage
            $search = $_GET['search'] ?? '';
            $university = $_GET['university'] ?? '';
            $city = $_GET['city'] ?? '';
            $skill = $_GET['skill'] ?? '';
            $sort = $_GET['sort'] ?? 'recent';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 12; // Nombre de membres par page
            $offset = ($page - 1) * $limit;

            $db = Database::getInstance();
            
            // Construire la requête SQL avec filtres
            $whereConditions = ["u.status = 'active'"];
            $params = [];

            // Filtre par recherche (nom, compétences)
            if (!empty($search)) {
                $whereConditions[] = "(u.prenom LIKE ? OR u.nom LIKE ? OR u.email LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            // Filtre par université
            if (!empty($university)) {
                $whereConditions[] = "u.university = ?";
                $params[] = $university;
            }

            // Filtre par ville
            if (!empty($city)) {
                $whereConditions[] = "u.city = ?";
                $params[] = $city;
            }

            // Filtre par compétence
            if (!empty($skill)) {
                $whereConditions[] = "EXISTS (
                    SELECT 1 FROM user_skills us 
                    JOIN skills s ON us.skill_id = s.id 
                    WHERE us.user_id = u.id AND s.name = ?
                )";
                $params[] = $skill;
            }

            $whereClause = implode(' AND ', $whereConditions);

            // Déterminer l'ordre de tri
            $orderBy = "u.created_at DESC";
            switch ($sort) {
                case 'name':
                    $orderBy = "u.prenom ASC, u.nom ASC";
                    break;
                case 'contributions':
                    $orderBy = "(posts_count + tutorials_count + projects_count) DESC";
                    break;
                case 'reputation':
                    $orderBy = "u.reputation DESC";
                    break;
                default:
                    $orderBy = "u.created_at DESC";
                    break;
            }

            // Requête principale pour récupérer les membres avec statistiques
            $sql = "
                SELECT 
                    u.id,
                    u.prenom,
                    u.nom,
                    u.email,
                    u.university,
                    u.faculty,
                    u.city,
                    u.photo_path,
                    u.created_at,
                    u.last_login as last_activity,
                    CONCAT(u.prenom, ' ', u.nom) as name,
                    COALESCE(posts_count, 0) as posts_count,
                    COALESCE(tutorials_count, 0) as tutorials_count,
                    COALESCE(projects_count, 0) as projects_count,
                    COALESCE(top_skills, '') as top_skills,
                    CASE 
                        WHEN u.last_login > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 
                        ELSE 0 
                    END as is_online
                FROM users u
                LEFT JOIN (
                    SELECT 
                        user_id,
                        COUNT(*) as posts_count
                    FROM posts 
                    WHERE status = 'active'
                    GROUP BY user_id
                ) p ON u.id = p.user_id
                LEFT JOIN (
                    SELECT 
                        user_id,
                        COUNT(*) as tutorials_count
                    FROM tutorials 
                    WHERE status = 'active'
                    GROUP BY user_id
                ) t ON u.id = t.user_id
                LEFT JOIN (
                    SELECT 
                        owner_id,
                        COUNT(*) as projects_count
                    FROM projects 
                    WHERE status = 'planning' OR status = 'in_progress'
                    GROUP BY owner_id
                ) pr ON u.id = pr.owner_id
                LEFT JOIN (
                    SELECT 
                        us.user_id,
                        GROUP_CONCAT(s.name ORDER BY us.level DESC LIMIT 3) as top_skills
                    FROM user_skills us
                    JOIN skills s ON us.skill_id = s.id
                    GROUP BY us.user_id
                ) sk ON u.id = sk.user_id
                WHERE $whereClause
                ORDER BY $orderBy
                LIMIT ? OFFSET ?
            ";

            $params[] = $limit;
            $params[] = $offset;

            $members = $db->query($sql, $params);

            // Compter le total pour la pagination
            $countSql = "
                SELECT COUNT(DISTINCT u.id) as total
                FROM users u
                WHERE $whereClause
            ";

            $countParams = array_slice($params, 0, -2); // Enlever limit et offset
            $totalResult = $db->queryOne($countSql, $countParams);
            $total = $totalResult['total'];

            // Traiter les données des membres
            foreach ($members as &$member) {
                // Convertir les compétences en tableau
                $member['top_skills'] = !empty($member['top_skills']) ? 
                    explode(',', $member['top_skills']) : [];

                // Vérifier les badges
                $badges = $db->query("
                    SELECT b.name, b.icon
                    FROM user_badges ub
                    JOIN badges b ON ub.badge_id = b.id
                    WHERE ub.user_id = ?
                    ORDER BY ub.awarded_at DESC
                    LIMIT 3
                ", [$member['id']]);

                $member['badges'] = $badges;

                // Ajouter l'URL de la photo
                if (!empty($member['photo_path'])) {
                    $member['photo'] = $member['photo_path'];
                } else {
                    $member['photo'] = null;
                }
            }

            // Calculer la pagination
            $totalPages = ceil($total / $limit);

            // Réponse JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'members' => $members,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_members' => $total,
                    'per_page' => $limit
                ],
                'filters' => [
                    'search' => $search,
                    'university' => $university,
                    'city' => $city,
                    'skill' => $skill,
                    'sort' => $sort
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors du filtrage: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API pour récupérer les options de filtres (universités, villes, compétences)
     */
    public function getFilterOptions()
    {
        // Vérifier que c'est une requête AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(400);
            echo json_encode(['error' => 'Requête AJAX requise']);
            return;
        }

        // Vérifier la session
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            return;
        }

        try {
            $db = Database::getInstance();

            // Récupérer les universités
            $universities = $db->query("
                SELECT DISTINCT university 
                FROM users 
                WHERE status = 'active' 
                  AND university IS NOT NULL 
                  AND university != ''
                ORDER BY university
            ");

            // Récupérer les villes
            $cities = $db->query("
                SELECT DISTINCT city 
                FROM users 
                WHERE status = 'active' 
                  AND city IS NOT NULL 
                  AND city != ''
                ORDER BY city
            ");

            // Récupérer les compétences
            $skills = $db->query("
                SELECT DISTINCT s.name 
                FROM skills s
                JOIN user_skills us ON s.id = us.skill_id
                JOIN users u ON us.user_id = u.id
                WHERE u.status = 'active'
                ORDER BY s.name
            ");

            // Réponse JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'universities' => array_column($universities, 'university'),
                'cities' => array_column($cities, 'city'),
                'skills' => array_column($skills, 'name')
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()
            ]);
        }
    }
}

