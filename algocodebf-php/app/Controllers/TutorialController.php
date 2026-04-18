<?php
/**
 * Contrôleur des tutoriels
 */

class TutorialController extends Controller
{
    private $tutorialModel;

    public function __construct()
    {
        $this->tutorialModel = $this->model('Tutorial');
    }

    /**
     * Liste des tutoriels (style YouTube)
     */
    public function index()
    {
        // Ne pas exiger la connexion pour voir les tutoriels
        // $this->requireLogin();
        
        // Récupérer les paramètres de recherche et filtres
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? null;
        $type = $_GET['type'] ?? null;
        $level = $_GET['level'] ?? null;
        $sort = $_GET['sort'] ?? 'recent'; // recent, popular, views, likes
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 24; // Plus de tutoriels par page pour un style YouTube
        $offset = ($page - 1) * $limit;

        $db = Database::getInstance();
        
        // Construire la requête avec filtres
        $where = ["t.status = 'active'"];
        $params = [];
        
        if ($search) {
            $where[] = "(t.title LIKE ? OR t.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($category) {
            $where[] = "t.category = ?";
            $params[] = $category;
        }
        
        if ($type) {
            $where[] = "t.type = ?";
            $params[] = $type;
        }
        
        if ($level) {
            $where[] = "t.level = ?";
            $params[] = $level;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Déterminer l'ordre de tri (utiliser des sous-requêtes pour les agrégations)
        $orderBy = "t.created_at DESC";
        switch ($sort) {
            case 'popular':
                $orderBy = "t.views DESC, (SELECT COUNT(*) FROM likes l WHERE l.likeable_type = 'tutorial' AND l.likeable_id = t.id) DESC";
                break;
            case 'views':
                $orderBy = "t.views DESC";
                break;
            case 'likes':
                $orderBy = "(SELECT COUNT(*) FROM likes l WHERE l.likeable_type = 'tutorial' AND l.likeable_id = t.id) DESC";
                break;
            case 'recent':
            default:
                $orderBy = "t.created_at DESC";
                break;
        }
        
        // Récupérer les tutoriels avec sous-requêtes pour les agrégations
        $query = "
            SELECT t.*, 
                   u.prenom, u.nom, u.photo_path,
                   (SELECT COUNT(*) FROM likes l WHERE l.likeable_type = 'tutorial' AND l.likeable_id = t.id) as likes_count,
                   (SELECT COUNT(*) FROM comments c WHERE c.commentable_type = 'tutorial' AND c.commentable_id = t.id AND c.status = 'active') as comments_count
            FROM tutorials t
            INNER JOIN users u ON t.user_id = u.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT ? OFFSET ?
        ";
        
        $queryParams = array_merge($params, [$limit, $offset]);
        
        $tutorials = $db->query($query, $queryParams);
        
        // Récupérer le nombre total pour la pagination
        $countQuery = "
            SELECT COUNT(DISTINCT t.id) as total
            FROM tutorials t
            WHERE {$whereClause}
        ";
        $totalResult = $db->queryOne($countQuery, $params);
        $totalTutorials = $totalResult['total'] ?? 0;
        $totalPages = ceil($totalTutorials / $limit);

        // Récupérer toutes les catégories uniques pour les filtres
        $categories = $db->query("SELECT DISTINCT category FROM tutorials WHERE status = 'active' AND category IS NOT NULL ORDER BY category");
        
        // Récupérer toutes les catégories disponibles
        $availableCategories = [
            'Programmation',
            'Web',
            'Mobile',
            'Réseau',
            'Cybersécurité',
            'Intelligence Artificielle',
            'Base de données',
            'Système',
            'Design',
            'DevOps',
            'Cloud'
        ];

        $data = [
            'title' => 'Tutoriels - AlgoCodeBF',
            'tutorials' => $tutorials,
            'current_search' => $search,
            'current_category' => $category,
            'current_type' => $type,
            'current_level' => $level,
            'current_sort' => $sort,
            'categories' => $availableCategories,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_tutorials' => $totalTutorials
        ];

        $this->view('tutorial/index', $data);
    }

    /**
     * Afficher un tutoriel
     */
    public function show($tutorialId)
    {
        $tutorial = $this->tutorialModel->getWithDetails($tutorialId);

        if (!$tutorial) {
            $_SESSION['error'] = "Tutoriel introuvable";
            $this->redirect('tutorial/index');
        }

        // Incrémenter les vues
        $this->tutorialModel->incrementViews($tutorialId);

        // Récupérer les tags
        $tags = $this->tutorialModel->getTags($tutorialId);

        // Récupérer les vidéos du tutoriel
        $videos = $this->tutorialModel->getVideos($tutorialId);

        // Récupérer les chapitres du tutoriel
        $chapters = $this->tutorialModel->getChapters($tutorialId);

        // Vérifier si l'utilisateur a liké
        $hasLiked = false;
        if ($this->isLoggedIn()) {
            $postModel = $this->model('Post');
            $hasLiked = $postModel->hasLiked($_SESSION['user_id'], 'tutorial', $tutorialId);
        }

        $data = [
            'title' => $tutorial['title'] . ' - Tutoriels - AlgoCodeBF',
            'tutorial' => $tutorial,
            'tags' => $tags,
            'videos' => $videos,
            'chapters' => $chapters,
            'has_liked' => $hasLiked,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('tutorial/show', $data);
    }

    /**
     * Tracker les vues d'une vidéo individuelle
     */
    public function trackVideoView($videoId)
    {
        $db = Database::getInstance();
        
        // Incrémenter les vues de la vidéo
        $db->execute(
            "UPDATE tutorial_videos SET views = views + 1 WHERE id = ?",
            [$videoId]
        );
        
        $this->json(['success' => true]);
    }

    /**
     * Créer un nouveau tutoriel
     */
    public function create()
    {
        $this->requireLogin();
        
        // Fonction helper pour convertir en bytes
        if (!function_exists('convertToBytes')) {
            function convertToBytes($val) {
                if (empty($val)) return 0;
                $val = trim($val);
                $last = strtolower($val[strlen($val)-1]);
                $val = (int)$val;
                switch($last) {
                    case 'g': $val *= 1024;
                    case 'm': $val *= 1024;
                    case 'k': $val *= 1024;
                }
                return $val;
            }
        }
        
        // Vérifier si l'utilisateur a la permission de créer des tutoriels
        $userModel = $this->model('User');
        if (!$userModel->canCreateTutorial($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous n'avez pas la permission de créer des tutoriels. Contactez un administrateur.";
            $this->redirect('tutorial/index');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier la taille de la requête POST avant traitement
            $postMaxSize = convertToBytes(ini_get('post_max_size'));
            $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
            
            if ($contentLength > $postMaxSize) {
                $postMaxSizeMB = round($postMaxSize / 1024 / 1024);
                $contentSizeMB = round($contentLength / 1024 / 1024);
                $_SESSION['error'] = "Erreur: La taille totale des fichiers ({$contentSizeMB}MB) dépasse la limite autorisée ({$postMaxSizeMB}MB). 
                    <br><strong>Solution:</strong> <a href='" . BASE_URL . "/public/fix_upload_limits.php' style='color: #c8102e; text-decoration: underline;'>Configurer PHP pour 500MB</a>";
                $this->redirect('tutorial/create');
                return;
            }
            
            // Vérifier si des fichiers sont uploadés et calculer la taille totale
            $totalUploadSize = 0;
            if (!empty($_FILES['videos']['name'][0])) {
                foreach ($_FILES['videos']['size'] as $size) {
                    if ($size > 0) {
                        $totalUploadSize += $size;
                    }
                }
            }
            if (!empty($_FILES['file']['size']) && $_FILES['file']['size'] > 0) {
                $totalUploadSize += $_FILES['file']['size'];
            }
            
            // Vérifier que la taille totale ne dépasse pas post_max_size
            if ($totalUploadSize > $postMaxSize) {
                $postMaxSizeMB = round($postMaxSize / 1024 / 1024);
                $totalSizeMB = round($totalUploadSize / 1024 / 1024);
                $_SESSION['error'] = "Erreur: La taille totale des fichiers ({$totalSizeMB}MB) dépasse la limite autorisée ({$postMaxSizeMB}MB). 
                    <br><strong>Solution:</strong> <a href='" . BASE_URL . "/public/fix_upload_limits.php' style='color: #c8102e; text-decoration: underline;'>Configurer PHP pour 500MB</a>";
                $this->redirect('tutorial/create');
                return;
            }
            
            // Vérifier les erreurs d'upload (erreur 413 peut se manifester ici)
            if (!empty($_FILES['videos']['error'])) {
                foreach ($_FILES['videos']['error'] as $error) {
                    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                        $_SESSION['error'] = "Erreur: Un ou plusieurs fichiers sont trop volumineux pour la configuration actuelle de PHP. 
                            <br><strong>Solution:</strong> <a href='" . BASE_URL . "/public/fix_upload_limits.php' style='color: #c8102e; text-decoration: underline;'>Configurer PHP pour 500MB</a>";
                        $this->redirect('tutorial/create');
                        return;
                    } elseif ($error !== UPLOAD_ERR_OK && $error !== UPLOAD_ERR_NO_FILE) {
                        $errorMessages = [
                            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (dépasse upload_max_filesize)',
                            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (dépasse MAX_FILE_SIZE du formulaire)',
                            UPLOAD_ERR_PARTIAL => 'Fichier partiellement uploadé',
                            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                            UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire le fichier',
                            UPLOAD_ERR_EXTENSION => 'Extension PHP a arrêté l\'upload'
                        ];
                        $_SESSION['error'] = "Erreur lors de l'upload: " . ($errorMessages[$error] ?? 'Erreur inconnue');
                        $this->redirect('tutorial/create');
                        return;
                    }
                }
            }
            
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('tutorial/create');
            }

            $data = [
                'user_id' => $_SESSION['user_id'],
                'title' => Security::clean($_POST['title'] ?? ''),
                'description' => Security::clean($_POST['description'] ?? ''),
                'content' => Security::clean($_POST['content'] ?? ''),
                'type' => Security::clean($_POST['type'] ?? ''),
                'category' => Security::clean($_POST['category'] ?? ''),
                'external_link' => Security::clean($_POST['external_link'] ?? '')
            ];

            $validator = new Validator($data);
            $validator->required('title')->min('title', 5)
                     ->required('description')->min('description', 20)
                     ->required('type')
                     ->required('category');

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = $data;
                $this->redirect('tutorial/create');
            }

            // Définir le type sur 'video' si des vidéos sont uploadées
            if (!empty($_FILES['videos']['name'][0])) {
                $data['type'] = 'video';
            }

            // Créer le tutoriel
            $tutorialId = $this->tutorialModel->createTutorial($data);

            if ($tutorialId) {
                $db = Database::getInstance();
                $uploadDir = ROOT . '/public/uploads/tutorials/' . $tutorialId . '/';
                
                // Créer le répertoire pour les vidéos du tutoriel
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Upload de plusieurs vidéos
                if (!empty($_FILES['videos']['name'][0])) {
                    $videoCount = count($_FILES['videos']['name']);
                    
                    // Vérifier le nombre maximum de vidéos
                    if ($videoCount > MAX_VIDEOS_PER_TUTORIAL) {
                        $_SESSION['error'] = "Nombre maximum de vidéos dépassé (max " . MAX_VIDEOS_PER_TUTORIAL . ")";
                        // Supprimer le tutoriel créé
                        $db->execute("DELETE FROM tutorials WHERE id = ?", [$tutorialId]);
                        $this->redirect('tutorial/create');
                        return;
                    }

                    $videoTitles = $_POST['video_titles'] ?? [];
                    $videoDescriptions = $_POST['video_descriptions'] ?? [];
                    $videoOrders = $_POST['video_orders'] ?? [];
                    
                    $uploadedCount = 0;

                    for ($i = 0; $i < $videoCount; $i++) {
                        if ($_FILES['videos']['error'][$i] === UPLOAD_ERR_OK) {
                            // Vérifier le type de fichier
                            $mimeType = mime_content_type($_FILES['videos']['tmp_name'][$i]);
                            if (!in_array($mimeType, ALLOWED_VIDEO_TYPES)) {
                                continue; // Ignorer les fichiers non autorisés
                            }

                            // Vérifier la taille (500MB max)
                            if ($_FILES['videos']['size'][$i] > MAX_VIDEO_SIZE) {
                                continue; // Ignorer les fichiers trop volumineux
                            }

                            // Générer un nom de fichier sécurisé
                            $originalName = $_FILES['videos']['name'][$i];
                            $filename = Security::generateSecureFileName($originalName);
                            $filepath = $uploadDir . $filename;

                            // Upload du fichier
                            if (move_uploaded_file($_FILES['videos']['tmp_name'][$i], $filepath)) {
                                $videoIndex = $uploadedCount++;
                                $videoTitle = !empty($videoTitles[$videoIndex]) ? Security::clean($videoTitles[$videoIndex]) : 'Vidéo ' . ($videoIndex + 1);
                                $videoDescription = !empty($videoDescriptions[$videoIndex]) ? Security::clean($videoDescriptions[$videoIndex]) : null;
                                $videoOrder = !empty($videoOrders[$videoIndex]) ? (int)$videoOrders[$videoIndex] : $videoIndex;

                                // Insérer la vidéo dans la base de données
                                $db->execute(
                                    "INSERT INTO tutorial_videos (tutorial_id, title, description, file_path, file_name, file_size, order_index) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                                    [
                                        $tutorialId,
                                        $videoTitle,
                                        $videoDescription,
                                        'uploads/tutorials/' . $tutorialId . '/' . $filename,
                                        $originalName,
                                        $_FILES['videos']['size'][$i],
                                        $videoOrder
                                    ]
                                );
                            }
                        }
                    }
                }

                // Gérer le sommaire/chapitres
                if (!empty($_POST['chapters'])) {
                    $chapters = json_decode($_POST['chapters'], true);
                    if (is_array($chapters)) {
                        foreach ($chapters as $index => $chapter) {
                            if (!empty($chapter['title'])) {
                                $chapterTitle = Security::clean($chapter['title']);
                                $chapterDescription = !empty($chapter['description']) ? Security::clean($chapter['description']) : null;
                                $chapterNumber = !empty($chapter['chapter_number']) ? (int)$chapter['chapter_number'] : ($index + 1);
                                $videoId = !empty($chapter['video_id']) ? (int)$chapter['video_id'] : null;
                                $orderIndex = !empty($chapter['order_index']) ? (int)$chapter['order_index'] : $index;

                                // Insérer le chapitre dans la base de données
                                $db->execute(
                                    "INSERT INTO tutorial_chapters (tutorial_id, chapter_number, title, description, video_id, order_index) 
                                     VALUES (?, ?, ?, ?, ?, ?)",
                                    [
                                        $tutorialId,
                                        $chapterNumber,
                                        $chapterTitle,
                                        $chapterDescription,
                                        $videoId,
                                        $orderIndex
                                    ]
                                );
                            }
                        }
                    }
                }

                // Upload de fichier unique (pour compatibilité avec l'ancien système)
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOC_TYPES, ALLOWED_VIDEO_TYPES);
                    
                    if (Security::validateMimeType($_FILES['file']['tmp_name'], $allowedTypes)) {
                        $mimeType = mime_content_type($_FILES['file']['tmp_name']);
                        $maxSize = in_array($mimeType, ALLOWED_VIDEO_TYPES) ? MAX_VIDEO_SIZE : MAX_FILE_SIZE;
                        
                        if (Security::validateFileSize($_FILES['file']['size'], $maxSize)) {
                            $filename = Security::generateSecureFileName($_FILES['file']['name']);
                            $filepath = $uploadDir . $filename;

                            if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
                                // Mettre à jour le tutoriel avec le chemin du fichier
                                $db->execute(
                                    "UPDATE tutorials SET file_path = ? WHERE id = ?",
                                    ['uploads/tutorials/' . $tutorialId . '/' . $filename, $tutorialId]
                                );
                            }
                        }
                    }
                }

                // Ajouter les tags
                if (!empty($_POST['tags'])) {
                    $tags = array_map('trim', explode(',', $_POST['tags']));
                    $this->tutorialModel->addTags($tutorialId, $tags);
                }

                // Vérifier et attribuer les badges
                $badgeModel = $this->model('Badge');
                $badgeModel->checkAndAwardBadges($_SESSION['user_id']);

                $_SESSION['success'] = "Tutoriel créé avec succès";
                $this->redirect('tutorial/show/' . $tutorialId);
            } else {
                $_SESSION['error'] = "Erreur lors de la création du tutoriel";
                $this->redirect('tutorial/create');
            }
        }

        $categories = [
            'Programmation',
            'Web',
            'Mobile',
            'Réseau',
            'Cybersécurité',
            'Intelligence Artificielle',
            'Base de données',
            'Système',
            'Design',
            'DevOps',
            'Cloud'
        ];

        $data = [
            'title' => 'Créer un tutoriel - AlgoCodeBF',
            'categories' => $categories,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('tutorial/create', $data);
    }

    /**
     * Tutoriels populaires
     */
    public function popular()
    {
        $tutorials = $this->tutorialModel->getPopular(20);

        $data = [
            'title' => 'Tutoriels populaires - AlgoCodeBF',
            'tutorials' => $tutorials
        ];

        $this->view('tutorial/popular', $data);
    }

    /**
     * Récupérer les tutoriels similaires (AJAX)
     */
    public function getSimilar($tutorialId)
    {
        header('Content-Type: application/json');
        
        try {
            // Récupérer le tutoriel actuel
            $current = $this->tutorialModel->findById($tutorialId);
            
            if (!$current) {
                echo json_encode(['success' => false, 'tutorials' => []]);
                return;
            }
            
            // Récupérer des tutoriels de la même catégorie
            $db = Database::getInstance();
            $similar = $db->query(
                "SELECT t.id, t.title, t.type, t.views,
                        u.prenom, u.nom, u.photo_path,
                        COUNT(DISTINCT l.id) as likes_count
                 FROM tutorials t
                 INNER JOIN users u ON t.user_id = u.id
                 LEFT JOIN likes l ON l.likeable_type = 'tutorial' AND l.likeable_id = t.id
                 WHERE t.category = ? 
                   AND t.id != ? 
                   AND t.status = 'active'
                 GROUP BY t.id
                 ORDER BY t.views DESC, likes_count DESC
                 LIMIT 5",
                [$current['category'], $tutorialId]
            );
            
            // Formater les résultats
            $formatted = [];
            foreach ($similar as $tuto) {
                $formatted[] = [
                    'id' => $tuto['id'],
                    'title' => $tuto['title'],
                    'type' => $tuto['type'],
                    'views' => $tuto['views'],
                    'likes_count' => $tuto['likes_count'],
                    'author' => $tuto['prenom'] . ' ' . $tuto['nom'],
                    'photo_path' => $tuto['photo_path']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'tutorials' => $formatted
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'tutorials' => []
            ]);
        }
    }

    /**
     * Modifier un tutoriel
     */
    public function edit($tutorialId)
    {
        $this->requireLogin();

        $tutorial = $this->tutorialModel->findById($tutorialId);

        if (!$tutorial) {
            $_SESSION['error'] = "Tutoriel introuvable";
            $this->redirect('tutorial/index');
        }

        // Vérifier que l'utilisateur est le propriétaire
        if ($tutorial['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin') {
            $_SESSION['error'] = "Vous n'avez pas la permission de modifier ce tutoriel";
            $this->redirect('tutorial/show/' . $tutorialId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('tutorial/edit/' . $tutorialId);
            }

            $data = [
                'title' => Security::clean($_POST['title'] ?? ''),
                'description' => Security::clean($_POST['description'] ?? ''),
                'content' => Security::clean($_POST['content'] ?? ''),
                'type' => Security::clean($_POST['type'] ?? ''),
                'category' => Security::clean($_POST['category'] ?? ''),
                'level' => Security::clean($_POST['level'] ?? ''),
                'external_link' => Security::clean($_POST['external_link'] ?? '')
            ];

            $validator = new Validator($data);
            $validator->required('title')->min('title', 5)
                     ->required('description')->min('description', 20)
                     ->required('type')
                     ->required('category')
                     ->required('level');

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = $data;
                $this->redirect('tutorial/edit/' . $tutorialId);
            }

            // Upload de nouveau fichier si présent
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/tutorials/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $allowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOC_TYPES, ALLOWED_VIDEO_TYPES);
                
                if (!Security::validateMimeType($_FILES['file']['tmp_name'], $allowedTypes)) {
                    $_SESSION['error'] = "Type de fichier non autorisé";
                    $this->redirect('tutorial/edit/' . $tutorialId);
                }

                // Déterminer la limite de taille selon le type de fichier
                $mimeType = mime_content_type($_FILES['file']['tmp_name']);
                $maxSize = in_array($mimeType, ALLOWED_VIDEO_TYPES) ? MAX_VIDEO_SIZE : MAX_FILE_SIZE;
                $maxSizeMB = $maxSize / 1024 / 1024;

                if (!Security::validateFileSize($_FILES['file']['size'], $maxSize)) {
                    $_SESSION['error'] = "Fichier trop volumineux (max {$maxSizeMB}MB)";
                    $this->redirect('tutorial/edit/' . $tutorialId);
                }

                // Supprimer l'ancien fichier si existe
                if (!empty($tutorial['file_path']) && file_exists($tutorial['file_path'])) {
                    unlink($tutorial['file_path']);
                }

                $filename = Security::generateSecureFileName($_FILES['file']['name']);
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
                    $data['file_path'] = 'uploads/tutorials/' . $filename;
                }
            }

            // Mettre à jour le tutoriel
            $updated = $this->tutorialModel->update($tutorialId, $data);

            if ($updated) {
                $db = Database::getInstance();
                $uploadDir = ROOT . '/public/uploads/tutorials/' . $tutorialId . '/';
                
                // Créer le répertoire pour les vidéos si nécessaire
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // 1. Mettre à jour les vidéos existantes
                if (isset($_POST['existing_video_titles']) && is_array($_POST['existing_video_titles'])) {
                    foreach ($_POST['existing_video_titles'] as $videoId => $title) {
                        $videoId = (int)$videoId;
                        $title = Security::clean($title);
                        $description = Security::clean($_POST['existing_video_descriptions'][$videoId] ?? '');
                        $orderIndex = (int)($_POST['existing_video_orders'][$videoId] ?? 0);
                        
                        $db->execute(
                            "UPDATE tutorial_videos SET title = ?, description = ?, order_index = ? WHERE id = ? AND tutorial_id = ?",
                            [$title, $description, $orderIndex, $videoId, $tutorialId]
                        );
                    }
                }

                // 2. Supprimer les vidéos marquées pour suppression
                if (isset($_POST['delete_videos']) && is_array($_POST['delete_videos'])) {
                    foreach ($_POST['delete_videos'] as $videoId) {
                        $videoId = (int)$videoId;
                        
                        // Récupérer le chemin du fichier avant suppression
                        $video = $db->queryOne("SELECT file_path FROM tutorial_videos WHERE id = ? AND tutorial_id = ?", [$videoId, $tutorialId]);
                        
                        if ($video && !empty($video['file_path']) && file_exists(ROOT . '/public/' . $video['file_path'])) {
                            unlink(ROOT . '/public/' . $video['file_path']);
                        }
                        
                        // Supprimer de la base de données
                        $db->execute("DELETE FROM tutorial_videos WHERE id = ? AND tutorial_id = ?", [$videoId, $tutorialId]);
                    }
                }

                // 3. Mettre à jour les chapitres existants
                if (isset($_POST['existing_chapter_titles']) && is_array($_POST['existing_chapter_titles'])) {
                    foreach ($_POST['existing_chapter_titles'] as $chapterId => $title) {
                        $chapterId = (int)$chapterId;
                        $title = Security::clean($title);
                        $description = Security::clean($_POST['existing_chapter_descriptions'][$chapterId] ?? '');
                        $videoId = !empty($_POST['existing_chapter_videos'][$chapterId]) ? (int)$_POST['existing_chapter_videos'][$chapterId] : null;
                        $orderIndex = (int)($_POST['existing_chapter_orders'][$chapterId] ?? 0);
                        
                        $db->execute(
                            "UPDATE tutorial_chapters SET title = ?, description = ?, video_id = ?, order_index = ? WHERE id = ? AND tutorial_id = ?",
                            [$title, $description, $videoId, $orderIndex, $chapterId, $tutorialId]
                        );
                    }
                }

                // 4. Supprimer les chapitres marqués pour suppression
                if (isset($_POST['delete_chapters']) && is_array($_POST['delete_chapters'])) {
                    foreach ($_POST['delete_chapters'] as $chapterId) {
                        $chapterId = (int)$chapterId;
                        $db->execute("DELETE FROM tutorial_chapters WHERE id = ? AND tutorial_id = ?", [$chapterId, $tutorialId]);
                    }
                }

                // 5. Ajouter les nouveaux chapitres
                if (!empty($_POST['chapters'])) {
                    $chapters = json_decode($_POST['chapters'], true);
                    if (is_array($chapters)) {
                        // Obtenir le dernier chapter_number pour continuer la numérotation
                        $lastChapter = $db->queryOne("SELECT MAX(chapter_number) as max_chapter FROM tutorial_chapters WHERE tutorial_id = ?", [$tutorialId]);
                        $currentChapterNum = ($lastChapter['max_chapter'] ?? 0) + 1;
                        
                        // Obtenir le dernier order_index
                        $lastOrder = $db->queryOne("SELECT MAX(order_index) as max_order FROM tutorial_chapters WHERE tutorial_id = ?", [$tutorialId]);
                        $currentOrder = ($lastOrder['max_order'] ?? -1) + 1;
                        
                        foreach ($chapters as $chapter) {
                            if (!empty($chapter['title'])) {
                                $chapterTitle = Security::clean($chapter['title']);
                                $chapterDescription = !empty($chapter['description']) ? Security::clean($chapter['description']) : null;
                                $chapterNumber = !empty($chapter['chapter_number']) ? (int)$chapter['chapter_number'] : $currentChapterNum++;
                                $videoId = !empty($chapter['video_id']) ? (int)$chapter['video_id'] : null;
                                $orderIndex = !empty($chapter['order_index']) ? (int)$chapter['order_index'] : $currentOrder++;

                                $db->execute(
                                    "INSERT INTO tutorial_chapters (tutorial_id, chapter_number, title, description, video_id, order_index) 
                                     VALUES (?, ?, ?, ?, ?, ?)",
                                    [$tutorialId, $chapterNumber, $chapterTitle, $chapterDescription, $videoId, $orderIndex]
                                );
                            }
                        }
                    }
                }

                // 6. Ajouter les nouvelles vidéos
                if (!empty($_FILES['new_videos']['name'][0])) {
                    $videoCount = count($_FILES['new_videos']['name']);
                    $newVideoTitles = $_POST['new_video_titles'] ?? [];
                    $newVideoDescriptions = $_POST['new_video_descriptions'] ?? [];
                    $newVideoOrders = $_POST['new_video_orders'] ?? [];
                    
                    // Obtenir le dernier order_index pour continuer la numérotation
                    $lastOrder = $db->queryOne("SELECT MAX(order_index) as max_order FROM tutorial_videos WHERE tutorial_id = ?", [$tutorialId]);
                    $currentOrder = ($lastOrder['max_order'] ?? -1) + 1;
                    
                    for ($i = 0; $i < $videoCount; $i++) {
                        if ($_FILES['new_videos']['error'][$i] === UPLOAD_ERR_OK) {
                            // Vérifier le type de fichier
                            $mimeType = mime_content_type($_FILES['new_videos']['tmp_name'][$i]);
                            if (!in_array($mimeType, ALLOWED_VIDEO_TYPES)) {
                                continue;
                            }

                            // Vérifier la taille (500MB max)
                            if ($_FILES['new_videos']['size'][$i] > MAX_VIDEO_SIZE) {
                                continue;
                            }

                            // Générer un nom de fichier sécurisé
                            $originalName = $_FILES['new_videos']['name'][$i];
                            $filename = Security::generateSecureFileName($originalName);
                            $filepath = $uploadDir . $filename;

                            // Upload du fichier
                            if (move_uploaded_file($_FILES['new_videos']['tmp_name'][$i], $filepath)) {
                                $videoTitle = !empty($newVideoTitles[$i]) ? Security::clean($newVideoTitles[$i]) : 'Vidéo ' . ($currentOrder + 1);
                                $videoDescription = !empty($newVideoDescriptions[$i]) ? Security::clean($newVideoDescriptions[$i]) : null;
                                $videoOrder = !empty($newVideoOrders[$i]) ? (int)$newVideoOrders[$i] : $currentOrder++;

                                // Insérer la vidéo dans la base de données
                                $db->execute(
                                    "INSERT INTO tutorial_videos (tutorial_id, title, description, file_path, file_name, file_size, order_index) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                                    [
                                        $tutorialId,
                                        $videoTitle,
                                        $videoDescription,
                                        'uploads/tutorials/' . $tutorialId . '/' . $filename,
                                        $originalName,
                                        $_FILES['new_videos']['size'][$i],
                                        $videoOrder
                                    ]
                                );
                            }
                        }
                    }
                }

                // Mettre à jour les tags
                if (isset($_POST['tags'])) {
                    // Supprimer les anciens tags
                    $this->tutorialModel->removeTags($tutorialId);
                    
                    // Ajouter les nouveaux tags
                    if (!empty($_POST['tags'])) {
                        $tags = array_map('trim', explode(',', $_POST['tags']));
                        $this->tutorialModel->addTags($tutorialId, $tags);
                    }
                }

                $_SESSION['success'] = "Tutoriel modifié avec succès";
                $this->redirect('tutorial/show/' . $tutorialId);
            } else {
                $_SESSION['error'] = "Erreur lors de la modification du tutoriel";
                $this->redirect('tutorial/edit/' . $tutorialId);
            }
        }

        // Récupérer les tags actuels
        $tags = $this->tutorialModel->getTags($tutorialId);
        $tagsString = implode(', ', array_column($tags, 'name'));

        // Récupérer les vidéos du tutoriel
        $videos = $this->tutorialModel->getVideos($tutorialId);

        // Récupérer les chapitres du tutoriel
        $chapters = $this->tutorialModel->getChapters($tutorialId);

        $categories = [
            'Programmation',
            'Web',
            'Mobile',
            'Réseau',
            'Cybersécurité',
            'Intelligence Artificielle',
            'Base de données',
            'Système',
            'Design',
            'DevOps',
            'Cloud'
        ];

        $data = [
            'title' => 'Modifier le tutoriel - AlgoCodeBF',
            'tutorial' => $tutorial,
            'categories' => $categories,
            'tags_string' => $tagsString,
            'videos' => $videos,
            'chapters' => $chapters,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('tutorial/edit', $data);
    }

    /**
     * Supprimer un tutoriel (soft delete)
     */
    public function delete($tutorialId)
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Méthode non autorisée";
            $this->redirect('tutorial/index');
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('tutorial/index');
        }

        $tutorial = $this->tutorialModel->findById($tutorialId);

        if (!$tutorial) {
            $_SESSION['error'] = "Tutoriel introuvable";
            $this->redirect('tutorial/index');
        }

        // Vérifier que l'utilisateur est le propriétaire
        if ($tutorial['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin') {
            $_SESSION['error'] = "Vous n'avez pas la permission de supprimer ce tutoriel";
            $this->redirect('tutorial/show/' . $tutorialId);
        }

        // Soft delete
        $deleted = $this->tutorialModel->update($tutorialId, ['status' => 'deleted']);

        if ($deleted) {
            $_SESSION['success'] = "Tutoriel supprimé avec succès";
            $this->redirect('user/profile');
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du tutoriel";
            $this->redirect('tutorial/show/' . $tutorialId);
        }
    }
}

