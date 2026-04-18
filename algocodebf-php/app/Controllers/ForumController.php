<?php
/**
 * Contrôleur du forum
 */

require_once __DIR__ . '/../Helpers/Cache.php';

class ForumController extends Controller
{
    private $postModel;

    public function __construct()
    {
        $this->postModel = $this->model('Post');
    }

    /**
     * Liste des posts du forum
     */
    public function index()
    {
        $this->requireLogin();
        $category = $_GET['category'] ?? null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * POSTS_PER_PAGE;

        // Utiliser le cache pour les données du forum (cache 2 minutes)
        $cache = Cache::getInstance();
        $cacheKey = 'forum_index_' . ($category ?? 'all') . '_page_' . $page;
        
        $forumData = $cache->remember($cacheKey, function() use ($category, $offset) {
            $posts = $this->postModel->getAllWithAuthor($category, POSTS_PER_PAGE, $offset);
            
            // Charger les catégories depuis la base de données
            $categoryModel = $this->model('ForumCategory');
            $categories = $categoryModel->getAllActive();

            // Calculer les statistiques
            $db = Database::getInstance();
            
            // Membres actifs (utilisateurs ayant posté dans les 30 derniers jours)
            $activeMembers = $db->queryOne(
                "SELECT COUNT(DISTINCT user_id) as count 
                 FROM posts 
                 WHERE status = 'active' 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            )['count'] ?? 0;
            
            // Topics tendances (posts avec plus de 5 vues dans les 7 derniers jours)
            $trendingTopics = $db->queryOne(
                "SELECT COUNT(*) as count 
                 FROM posts 
                 WHERE status = 'active' 
                 AND views > 5 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            )['count'] ?? 0;
            
            // Posts d'aujourd'hui
            $todayPosts = $db->queryOne(
                "SELECT COUNT(*) as count 
                 FROM posts 
                 WHERE status = 'active' 
                 AND DATE(created_at) = CURDATE()"
            )['count'] ?? 0;
            
            $stats = [
                'total_posts' => $this->postModel->count('status', 'active'),
                'active_members' => $activeMembers,
                'trending_topics' => $trendingTopics,
                'today_posts' => $todayPosts
            ];
            
            return [
                'posts' => $posts,
                'categories' => $categories,
                'stats' => $stats
            ];
        }, 120); // Cache 2 minutes

        $data = [
            'title' => 'Forum - AlgoCodeBF',
            'posts' => $forumData['posts'],
            'categories' => $forumData['categories'],
            'current_category' => $category,
            'page' => $page,
            'stats' => $forumData['stats']
        ];

        $this->view('forum/index', $data);
    }

    /**
     * Afficher un post
     */
    public function show($postId)
    {
        $post = $this->postModel->getWithDetails($postId);

        if (!$post) {
            $_SESSION['error'] = "Post introuvable";
            $this->redirect('forum/index');
        }

        // Incrémenter les vues
        $this->postModel->incrementViews($postId);

        // Récupérer les commentaires
        $comments = $this->postModel->getComments($postId);

        // Récupérer les pièces jointes
        $db = Database::getInstance();
        $attachments = $db->query(
            "SELECT * FROM post_attachments WHERE post_id = ? ORDER BY created_at ASC",
            [$postId]
        );

        // Vérifier si l'utilisateur a liké
        $hasLiked = false;
        if ($this->isLoggedIn()) {
            $hasLiked = $this->postModel->hasLiked($_SESSION['user_id'], 'post', $postId);
        }

        $data = [
            'title' => $post['title'] . ' - Forum - AlgoCodeBF',
            'post' => $post,
            'comments' => $comments,
            'attachments' => $attachments,
            'has_liked' => $hasLiked,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('forum/show', $data);
    }

    /**
     * Créer un nouveau post
     */
    public function create()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Debug: Vérifier que les données sont bien reçues
            if (APP_ENV === 'development') {
                error_log("POST data received: " . print_r($_POST, true));
            }

            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "❌ Token de sécurité invalide. Veuillez réessayer.";
                $this->redirect('forum/create');
            }

            $data = [
                'user_id' => $_SESSION['user_id'],
                'category' => Security::clean($_POST['category'] ?? ''),
                'title' => Security::clean($_POST['title'] ?? ''),
                'body' => Security::cleanContent($_POST['body'] ?? '')
            ];

            // Debug: Vérifier les données nettoyées
            if (APP_ENV === 'development') {
                error_log("Cleaned data: " . print_r($data, true));
            }

            $validator = new Validator($data);
            $validator->required('category')
                     ->required('title')->min('title', 5)
                     ->required('body')->min('body', 20);

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = $data;
                $_SESSION['error'] = "❌ Veuillez corriger les erreurs dans le formulaire.";
                $this->redirect('forum/create');
            }

            try {
                $postId = $this->postModel->createPost($data);

                if ($postId) {
                    // Gérer les pièces jointes si présentes
                    if (!empty($_FILES['attachments']['name'][0])) {
                        $this->handleAttachments($postId);
                    }

                    // Vérifier et attribuer les badges
                    $badgeModel = $this->model('Badge');
                    $badgeModel->checkAndAwardBadges($_SESSION['user_id']);

                    $_SESSION['success'] = "✅ Discussion créée avec succès !";
                    $this->redirect('forum/show/' . $postId);
                } else {
                    $_SESSION['error'] = "❌ Erreur lors de la création de la discussion. Impossible d'insérer dans la base de données.";
                    $_SESSION['old'] = $data;
                    $this->redirect('forum/create');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "❌ Erreur : " . $e->getMessage();
                $_SESSION['old'] = $data;
                if (APP_ENV === 'development') {
                    error_log("Exception in createPost: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                }
                $this->redirect('forum/create');
            }
        }

        // Charger les catégories depuis la base de données
        $categoryModel = $this->model('ForumCategory');
        $categoriesData = $categoryModel->getAllActive();
        
        // Extraire seulement les noms pour le select
        $categories = array_column($categoriesData, 'name');

        $data = [
            'title' => 'Créer un post - Forum - AlgoCodeBF',
            'categories' => $categories,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('forum/create', $data);
    }

    /**
     * Ajouter un commentaire
     */
    public function addComment($postId)
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('forum/show/' . $postId);
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('forum/show/' . $postId);
        }

        $body = Security::clean($_POST['body'] ?? '');

        if (empty($body) || strlen($body) < 5) {
            $_SESSION['error'] = "Le commentaire doit contenir au moins 5 caractères";
            $this->redirect('forum/show/' . $postId);
        }

        $commentId = $this->postModel->addComment($postId, $_SESSION['user_id'], $body);

        if ($commentId) {
            $_SESSION['success'] = "Commentaire ajouté";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du commentaire";
        }

        $this->redirect('forum/show/' . $postId);
    }

    /**
     * Liker/Unliker un post ou commentaire
     */
    public function toggleLike()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Méthode non autorisée'], 405);
        }

        $type = $_POST['type'] ?? '';
        $id = (int)($_POST['id'] ?? 0);

        if (!in_array($type, ['post', 'comment']) || $id <= 0) {
            $this->json(['success' => false, 'message' => 'Données invalides'], 400);
        }

        $result = $this->postModel->toggleLike($_SESSION['user_id'], $type, $id);

        if ($result) {
            $this->json(['success' => true, 'message' => 'Statut mis à jour']);
        } else {
            $this->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }

    /**
     * Signaler un post ou commentaire
     */
    public function report()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('forum/index');
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('forum/index');
        }

        $type = $_POST['type'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        $reason = Security::clean($_POST['reason'] ?? '');

        if (!in_array($type, ['post', 'comment']) || $id <= 0 || empty($reason)) {
            $_SESSION['error'] = "Données invalides";
            $this->redirect('forum/index');
        }

        $reportModel = $this->model('Report');
        $reportId = $reportModel->createReport($_SESSION['user_id'], $type, $id, $reason);

        if ($reportId) {
            $_SESSION['success'] = "Signalement envoyé";
        } else {
            $_SESSION['error'] = "Erreur lors du signalement";
        }

        // Rediriger vers la page précédente
        $referer = $_SERVER['HTTP_REFERER'] ?? 'forum/index';
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Modifier un post
     */
    public function edit($postId)
    {
        $this->requireLogin();

        $post = $this->postModel->findById($postId);

        if (!$post) {
            $_SESSION['error'] = "Discussion introuvable";
            $this->redirect('forum/index');
        }

        // Vérifier que l'utilisateur est bien l'auteur
        if ($post['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à modifier cette discussion";
            $this->redirect('forum/show/' . $postId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('forum/edit/' . $postId);
            }

            $data = [
                'category' => Security::clean($_POST['category'] ?? ''),
                'title' => Security::clean($_POST['title'] ?? ''),
                'body' => Security::cleanContent($_POST['body'] ?? '')
            ];

            $validator = new Validator($data);
            $validator->required('category')
                     ->required('title')->min('title', 5)
                     ->required('body')->min('body', 20);

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = $data;
                $this->redirect('forum/edit/' . $postId);
            }

            if ($this->postModel->update($postId, $data)) {
                // Gérer les nouvelles pièces jointes si présentes
                if (!empty($_FILES['attachments']['name'][0])) {
                    $this->handleAttachments($postId);
                }
                
                $_SESSION['success'] = "✅ Discussion modifiée avec succès";
                $this->redirect('forum/show/' . $postId);
            } else {
                $_SESSION['error'] = "❌ Erreur lors de la modification";
                $this->redirect('forum/edit/' . $postId);
            }
        }

        // Charger les catégories depuis la base de données
        $categoryModel = $this->model('ForumCategory');
        $categoriesData = $categoryModel->getAllActive();
        
        // Extraire seulement les noms pour le select
        $categories = array_column($categoriesData, 'name');

        $data = [
            'title' => 'Modifier la discussion - Forum - AlgoCodeBF',
            'post' => $post,
            'categories' => $categories,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('forum/edit', $data);
    }

    /**
     * Supprimer un post
     */
    public function delete($postId)
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('forum/index');
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('forum/show/' . $postId);
        }

        $post = $this->postModel->findById($postId);

        if (!$post) {
            $_SESSION['error'] = "Discussion introuvable";
            $this->redirect('forum/index');
        }

        // Vérifier que l'utilisateur est bien l'auteur ou un admin
        if ($post['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à supprimer cette discussion";
            $this->redirect('forum/show/' . $postId);
        }

        // Supprimer le post (suppression douce - changer le statut)
        if ($this->postModel->update($postId, ['status' => 'deleted'])) {
            $_SESSION['success'] = "✅ Discussion supprimée avec succès";
            $this->redirect('user/profile');
        } else {
            $_SESSION['error'] = "❌ Erreur lors de la suppression";
            $this->redirect('forum/show/' . $postId);
        }
    }

    /**
     * Supprimer une pièce jointe
     */
    public function deleteAttachment($attachmentId)
    {
        $this->requireLogin();
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }
        
        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide']);
            exit;
        }
        
        $db = Database::getInstance();
        
        // Récupérer l'attachment et vérifier le propriétaire
        $attachment = $db->queryOne(
            "SELECT pa.*, p.user_id 
             FROM post_attachments pa 
             INNER JOIN posts p ON pa.post_id = p.id 
             WHERE pa.id = ?",
            [$attachmentId]
        );
        
        if (!$attachment) {
            echo json_encode(['success' => false, 'message' => 'Pièce jointe introuvable']);
            exit;
        }
        
        // Vérifier que l'utilisateur est bien l'auteur
        if ($attachment['user_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            exit;
        }
        
        // Supprimer le fichier physique
        if (file_exists($attachment['file_path'])) {
            unlink($attachment['file_path']);
        }
        
        // Supprimer de la base de données
        $result = $db->execute(
            "DELETE FROM post_attachments WHERE id = ?",
            [$attachmentId]
        );
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Pièce jointe supprimée']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
        exit;
    }

    /**
     * Gérer les pièces jointes uploadées
     */
    private function handleAttachments($postId)
    {
        $uploadDir = 'uploads/forum/';
        
        // Créer le dossier s'il n'existe pas
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($_FILES['attachments']['name'] as $key => $filename) {
            if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                // Créer un tableau de fichier individuel pour la validation
                $file = [
                    'name' => $_FILES['attachments']['name'][$key],
                    'type' => $_FILES['attachments']['type'][$key],
                    'tmp_name' => $_FILES['attachments']['tmp_name'][$key],
                    'error' => $_FILES['attachments']['error'][$key],
                    'size' => $_FILES['attachments']['size'][$key]
                ];
                
                // Validation robuste - on accepte images et documents
                $validation = FileValidator::validate($file, 'document', 10 * 1024 * 1024); // 10 MB
                
                // Si pas valide en tant que document, essayer en tant qu'image
                if (!$validation['valid']) {
                    $validation = FileValidator::validate($file, 'image', 5 * 1024 * 1024); // 5 MB
                }
                
                // Si toujours pas valide, essayer archive
                if (!$validation['valid']) {
                    $validation = FileValidator::validate($file, 'archive', 10 * 1024 * 1024);
                }
                
                if (!$validation['valid']) {
                    $_SESSION['warning'] = "❌ " . $filename . " : " . $validation['error'];
                    continue;
                }

                // Générer un nom sécurisé
                $newFilename = FileValidator::generateSecureFileName($filename, 'forum');
                $filePath = $uploadDir . $newFilename;

                // Déplacer le fichier
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Enregistrer dans la base de données
                    $db = Database::getInstance();
                    $query = "INSERT INTO post_attachments (post_id, filename, original_name, file_path, file_size, mime_type, created_at) 
                              VALUES (:post_id, :filename, :original_name, :file_path, :file_size, :mime_type, NOW())";
                    
                    $db->execute($query, [
                        ':post_id' => $postId,
                        ':filename' => $newFilename,
                        ':original_name' => $filename,
                        ':file_path' => $filePath,
                        ':file_size' => $fileSize,
                        ':mime_type' => $mimeType
                    ]);
                }
            }
        }
    }
}

