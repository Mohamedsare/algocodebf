<?php
/**
 * Contrôleur du blog/actualités
 */

class BlogController extends Controller
{
    private $blogModel;

    public function __construct()
    {
        $this->blogModel = $this->model('BlogPost');
    }

    /**
     * Liste des articles
     */
    public function index()
    {
        $this->requireLogin();
        $category = $_GET['category'] ?? null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * 12;

        $posts = $this->blogModel->getAllPublished($category, 12, $offset);
        $popular = $this->blogModel->getPopular(5);
        $featured_post = $this->blogModel->getFeaturedPost();
        
        // Charger les catégories depuis la base de données
        $categoryModel = $this->model('BlogCategory');
        $categories = $categoryModel->getAllWithCount();

        $data = [
            'title' => 'Blog - AlgoCodeBF',
            'posts' => $posts,
            'popular' => $popular,
            'popular_posts' => $popular, // Alias pour compatibilité
            'featured_post' => $featured_post,
            'categories' => $categories,
            'current_category' => $category,
            'page' => $page
        ];

        $this->view('blog/index', $data);
    }

    /**
     * Recherche asynchrone d'articles
     */
    public function search()
    {
        // Vérifier que c'est une requête AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée']);
            return;
        }

        // Pas besoin de connexion pour la recherche publique
        // $this->requireLogin();

        // Récupérer les paramètres
        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? '');
        $sort = trim($_GET['sort'] ?? 'recent');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 12;
        $offset = ($page - 1) * $limit;

        try {
            $db = Database::getInstance();
            
            // Construire la requête de base
            $whereConditions = ["bp.status = 'published'"];
            $params = [];
            
            // Ajouter la recherche par texte
            if (!empty($search)) {
                $whereConditions[] = "(bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            // Ajouter le filtre par catégorie
            if (!empty($category)) {
                $whereConditions[] = "bp.category = ?";
                $params[] = $category;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Déterminer l'ordre de tri
            switch ($sort) {
                case 'views':
                    $orderBy = "bp.views DESC";
                    break;
                case 'popular':
                    $orderBy = "bp.views DESC, bp.created_at DESC";
                    break;
                case 'recent':
                default:
                    $orderBy = "bp.created_at DESC";
                    break;
            }

            // Requête pour compter le total
            $countSql = "
                SELECT COUNT(*) as total
                FROM blog_posts bp
                LEFT JOIN users u ON bp.author_id = u.id
                WHERE {$whereClause}
            ";
            
            $totalResult = $db->queryOne($countSql, $params);
            $total = $totalResult['total'];

            // Requête principale pour récupérer les articles
            $sql = "
                SELECT 
                    bp.id,
                    bp.title,
                    bp.slug,
                    bp.excerpt,
                    bp.content,
                    bp.featured_image,
                    bp.category,
                    bp.views,
                    bp.created_at,
                    bp.published_at,
                    u.prenom as author_name,
                    u.photo_path as author_photo,
                    (SELECT COUNT(*) FROM likes l WHERE l.likeable_type = 'blog' AND l.likeable_id = bp.id) as likes_count
                FROM blog_posts bp
                LEFT JOIN users u ON bp.author_id = u.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT ? OFFSET ?
            ";

            $params[] = $limit;
            $params[] = $offset;

            $posts = $db->query($sql, $params);

            // Formater les données pour la réponse
            foreach ($posts as &$post) {
                // Ajouter l'URL de l'image
                if (!empty($post['featured_image'])) {
                    $post['featured_image'] = $post['featured_image'];
                } else {
                    $post['featured_image'] = 'uploads/blog/default.jpg';
                }
                
                // Ajouter l'URL de la photo de l'auteur
                if (!empty($post['author_photo'])) {
                    $post['author_photo'] = $post['author_photo'];
                } else {
                    $post['author_photo'] = null;
                }
                
                // Calculer le temps de lecture
                $post['reading_time'] = ceil(str_word_count($post['content'] ?? '') / 200);
            }

            // Calculer la pagination
            $totalPages = ceil($total / $limit);

            // Réponse JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'posts' => $posts,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_posts' => $total,
                    'limit' => $limit,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ],
                'filters' => [
                    'search' => $search,
                    'category' => $category,
                    'sort' => $sort
                ]
            ]);

        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la recherche: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Récupérer les options de filtres pour la recherche
     */
    public function getFilterOptions()
    {
        // Vérifier que c'est une requête AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée']);
            return;
        }

        // Pas besoin de connexion pour les options de filtres publiques
        // $this->requireLogin();

        try {
            $db = Database::getInstance();
            
            // Récupérer les catégories disponibles
            $categories = $db->query("
                SELECT DISTINCT category as name, category as slug, COUNT(*) as count
                FROM blog_posts 
                WHERE status = 'published' AND category IS NOT NULL AND category != ''
                GROUP BY category
                ORDER BY count DESC, category ASC
            ");

            // Réponse JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors du chargement des options: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Afficher un article
     */
    public function show($slug)
    {
        $this->requireLogin();
        $post = $this->blogModel->getWithDetails($slug);

        if (!$post) {
            $_SESSION['error'] = "Article introuvable";
            $this->redirect('blog/index');
        }

        // Incrémenter les vues
        $this->blogModel->incrementViews($post['id']);

        // Vérifier si l'utilisateur a liké
        if ($this->isLoggedIn()) {
            $likeModel = $this->model('Like');
            $post['user_liked'] = $likeModel->hasLiked('blog', $post['id'], $_SESSION['user_id']);
        }

        // Récupérer les articles populaires et similaires
        $popular = $this->blogModel->getPopular(5);
        $related_posts = $this->blogModel->getRelated($post['id'], $post['category'], 3);

        $data = [
            'title' => $post['title'] . ' - Blog - AlgoCodeBF',
            'post' => $post,
            'popular' => $popular,
            'related_posts' => $related_posts,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('blog/show', $data);
    }

    /**
     * Créer un article (admin uniquement)
     */
    public function create()
    {
        $this->requireLogin();
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('blog/create');
            }

            $data = [
                'author_id' => $_SESSION['user_id'],
                'title' => Security::clean($_POST['title'] ?? ''),
                'excerpt' => Security::clean($_POST['excerpt'] ?? ''),
                'content' => Security::clean($_POST['content'] ?? ''),
                'category' => Security::clean($_POST['category'] ?? ''),
                'status' => Security::clean($_POST['status'] ?? 'draft')
            ];

            if ($data['status'] === 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            }

            $validator = new Validator($data);
            $validator->required('title')->min('title', 5)
                     ->required('excerpt')->min('excerpt', 20)
                     ->required('content')->min('content', 100)
                     ->required('category');

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = $data;
                $this->redirect('blog/create');
            }

            // Upload de l'image à la une
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = UPLOADS . '/blog/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (!Security::validateMimeType($_FILES['featured_image']['tmp_name'], ALLOWED_IMAGE_TYPES)) {
                    $_SESSION['error'] = "Format d'image non autorisé";
                    $this->redirect('blog/create');
                }

                if (!Security::validateFileSize($_FILES['featured_image']['size'], MAX_FILE_SIZE)) {
                    $_SESSION['error'] = "Image trop volumineuse (max 5MB)";
                    $this->redirect('blog/create');
                }

                $filename = Security::generateSecureFileName($_FILES['featured_image']['name']);
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $filepath)) {
                    $data['featured_image'] = 'uploads/blog/' . $filename;
                }
            }

            $postId = $this->blogModel->createPost($data);

            if ($postId) {
                $_SESSION['success'] = "Article créé avec succès";
                $post = $this->blogModel->findById($postId);
                $this->redirect('blog/show/' . $post['slug']);
            } else {
                $_SESSION['error'] = "Erreur lors de la création de l'article";
                $this->redirect('blog/create');
            }
        }

        // Charger les catégories depuis la base de données
        $categoryModel = $this->model('BlogCategory');
        $categoriesData = $categoryModel->getAllActive();
        
        // Extraire seulement les noms pour le select
        $categories = array_column($categoriesData, 'name');

        $data = [
            'title' => 'Créer un article - Blog - AlgoCodeBF',
            'categories' => $categories,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('blog/create', $data);
    }

    /**
     * Modifier un article
     */
    public function edit($slug)
    {
        $this->requireLogin();

        $post = $this->blogModel->findBySlug($slug);

        if (!$post) {
            $_SESSION['error'] = "Article introuvable";
            $this->redirect('blog/index');
        }

        // Vérifier les permissions (admin ou auteur)
        if ($_SESSION['user_role'] !== 'admin' && $post['author_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous n'avez pas la permission de modifier cet article";
            $this->redirect('blog/show/' . $slug);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('blog/edit/' . $slug);
            }

            $data = [
                'title' => Security::clean($_POST['title'] ?? ''),
                'excerpt' => Security::clean($_POST['excerpt'] ?? ''),
                'content' => Security::clean($_POST['content'] ?? ''),
                'category' => Security::clean($_POST['category'] ?? ''),
                'status' => Security::clean($_POST['status'] ?? 'draft')
            ];

            // Si le statut passe à "published" et n'était pas publié avant
            if ($data['status'] === 'published' && $post['status'] !== 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            }

            $validator = new Validator($data);
            $validator->required('title')->min('title', 5)
                     ->required('excerpt')->min('excerpt', 20)
                     ->required('content')->min('content', 100)
                     ->required('category');

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $this->redirect('blog/edit/' . $slug);
            }

            // Gérer l'upload d'image si nouvelle image
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = UPLOADS . '/blog/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (!Security::validateMimeType($_FILES['featured_image']['tmp_name'], ALLOWED_IMAGE_TYPES)) {
                    $_SESSION['error'] = "Format d'image non autorisé";
                    $this->redirect('blog/edit/' . $slug);
                }

                if (!Security::validateFileSize($_FILES['featured_image']['size'], MAX_FILE_SIZE)) {
                    $_SESSION['error'] = "Image trop volumineuse (max 5MB)";
                    $this->redirect('blog/edit/' . $slug);
                }

                // Supprimer l'ancienne image si elle existe
                if (!empty($post['featured_image']) && file_exists($post['featured_image'])) {
                    @unlink($post['featured_image']);
                }

                $filename = Security::generateSecureFileName($_FILES['featured_image']['name']);
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $filepath)) {
                    $data['featured_image'] = 'uploads/blog/' . $filename;
                }
            }

            // Regénérer le slug si le titre a changé
            if ($data['title'] !== $post['title']) {
                $data['slug'] = $this->blogModel->generateUniqueSlug($data['title'], $post['id']);
            }

            if ($this->blogModel->update($post['id'], $data)) {
                $_SESSION['success'] = "Article modifié avec succès";
                $newSlug = $data['slug'] ?? $post['slug'];
                $this->redirect('blog/show/' . $newSlug);
            } else {
                $_SESSION['error'] = "Erreur lors de la modification";
                $this->redirect('blog/edit/' . $slug);
            }
        }

        // Charger les catégories depuis la base de données
        $categoryModel = $this->model('BlogCategory');
        $categoriesData = $categoryModel->getAllActive();
        
        // Extraire seulement les noms pour le select
        $categories = array_column($categoriesData, 'name');

        $data = [
            'title' => 'Modifier l\'article - Blog - AlgoCodeBF',
            'post' => $post,
            'categories' => $categories,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('blog/edit', $data);
    }

    /**
     * Supprimer un article
     */
    public function delete($slug)
    {
        $this->requireLogin();

        $post = $this->blogModel->findBySlug($slug);

        if (!$post) {
            $_SESSION['error'] = "Article introuvable";
            $this->redirect('blog/index');
        }

        // Vérifier les permissions
        if ($_SESSION['user_role'] !== 'admin' && $post['author_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous n'avez pas la permission de supprimer cet article";
            $this->redirect('blog/show/' . $slug);
        }

        // Supprimer l'image si elle existe
        if (!empty($post['featured_image']) && file_exists($post['featured_image'])) {
            @unlink($post['featured_image']);
        }

        // Soft delete : marquer comme archivé
        if ($this->blogModel->update($post['id'], ['status' => 'archived'])) {
            $_SESSION['success'] = "Article supprimé avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression";
        }

        $this->redirect('blog/index');
    }
}


