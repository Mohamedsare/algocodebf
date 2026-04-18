<?php
/**
 * Contrôleur des projets collaboratifs
 */

class ProjectController extends Controller
{
    private $projectModel;

    public function __construct()
    {
        $this->projectModel = $this->model('Project');
    }

    /**
     * Liste des projets
     */
    public function index()
    {
        $this->requireLogin();
        $status = $_GET['status'] ?? null;
        $lookingForMembers = isset($_GET['recruiting']) ? true : null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * POSTS_PER_PAGE;

        $projects = $this->projectModel->getAllWithOwner($status, $lookingForMembers, POSTS_PER_PAGE, $offset);

        $data = [
            'title' => 'Projets collaboratifs - AlgoCodeBF',
            'projects' => $projects,
            'current_status' => $status,
            'recruiting' => $lookingForMembers,
            'page' => $page
        ];

        $this->view('project/index', $data);
    }

    /**
     * Afficher un projet
     */
    public function show($projectId)
    {
        $project = $this->projectModel->getWithDetails($projectId);

        if (!$project) {
            $_SESSION['error'] = "Projet introuvable";
            $this->redirect('project/index');
        }

        // Récupérer les membres
        $members = $this->projectModel->getMembers($projectId);

        // Vérifier si l'utilisateur est membre
        $isMember = false;
        $isOwner = false;
        if ($this->isLoggedIn()) {
            $isMember = $this->projectModel->isMember($projectId, $_SESSION['user_id']);
            $isOwner = $project['owner_id'] == $_SESSION['user_id'];
        }

        $data = [
            'title' => ($project['title'] ?? 'Projet') . ' - Projets - AlgoCodeBF',
            'project' => $project,
            'members' => $members,
            'is_member' => $isMember,
            'is_owner' => $isOwner,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('project/show', $data);
    }

    /**
     * Créer un nouveau projet
     */
    public function create()
    {
        $this->requireLogin();
        
        // Vérifier si l'utilisateur a la permission de créer des projets
        $userModel = $this->model('User');
        if (!$userModel->canCreateProject($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous n'avez pas la permission de créer des projets. Contactez un administrateur.";
            $this->redirect('project/index');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('project/create');
            }

            $data = [
                'owner_id' => $_SESSION['user_id'],
                'title' => Security::clean($_POST['title'] ?? ''),
                'description' => Security::clean($_POST['description'] ?? ''),
                'github_link' => Security::clean($_POST['github_link'] ?? ''),
                'demo_link' => Security::clean($_POST['demo_link'] ?? ''),
                'status' => Security::clean($_POST['status'] ?? 'planning'),
                'visibility' => Security::clean($_POST['visibility'] ?? 'public'),
                'looking_for_members' => isset($_POST['looking_for_members']) ? 1 : 0
            ];

            $validator = new Validator($data);
            $validator->required('title')->min('title', 5)
                     ->required('description')->min('description', 20);

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = $data;
                $this->redirect('project/create');
            }

            $projectId = $this->projectModel->createProject($data);

            if ($projectId) {
                // Vérifier et attribuer les badges
                $badgeModel = $this->model('Badge');
                $badgeModel->checkAndAwardBadges($_SESSION['user_id']);

                $_SESSION['success'] = "Projet créé avec succès";
                $this->redirect('project/show/' . $projectId);
            } else {
                $_SESSION['error'] = "Erreur lors de la création du projet";
                $this->redirect('project/create');
            }
        }

        $data = [
            'title' => 'Créer un projet - AlgoCodeBF',
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('project/create', $data);
    }

    /**
     * Modifier un projet
     */
    public function edit($projectId)
    {
        $this->requireLogin();

        $project = $this->projectModel->findById($projectId);

        if (!$project) {
            $_SESSION['error'] = "Projet introuvable";
            $this->redirect('project/index');
            return;
        }

        // Vérifier que l'utilisateur est le propriétaire
        if ($project['owner_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin') {
            $_SESSION['error'] = "Vous n'avez pas la permission de modifier ce projet";
            $this->redirect('project/show/' . $projectId);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('project/edit/' . $projectId);
            }

            $data = [
                'title' => Security::clean($_POST['title'] ?? ''),
                'description' => Security::clean($_POST['description'] ?? ''),
                'github_link' => Security::clean($_POST['github_link'] ?? ''),
                'demo_link' => Security::clean($_POST['demo_link'] ?? ''),
                'status' => Security::clean($_POST['status'] ?? 'planning'),
                'visibility' => Security::clean($_POST['visibility'] ?? 'public'),
                'looking_for_members' => isset($_POST['looking_for_members']) ? 1 : 0
            ];

            $validator = new Validator($data);
            $validator->required('title')->min('title', 5)
                     ->required('description')->min('description', 20);

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = array_merge($project, $data);
                $this->redirect('project/edit/' . $projectId);
                return;
            }

            $updated = $this->projectModel->update($projectId, $data);

            if ($updated) {
                $_SESSION['success'] = "Projet modifié avec succès";
                $this->redirect('project/show/' . $projectId);
            } else {
                $_SESSION['error'] = "Erreur lors de la modification du projet";
                $this->redirect('project/edit/' . $projectId);
            }
            return;
        }

        // Récupérer les anciennes valeurs ou les données du projet
        $old = $_SESSION['old'] ?? $project;
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['old'], $_SESSION['errors']);

        $data = [
            'title' => 'Modifier le projet - AlgoCodeBF',
            'project' => $project,
            'old' => $old,
            'errors' => $errors,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('project/edit', $data);
    }

    /**
     * Demander à rejoindre un projet (envoie un message au créateur)
     */
    public function requestJoin($projectId)
    {
        header('Content-Type: application/json');
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide']);
            return;
        }

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Projet introuvable']);
            return;
        }

        // Vérifier si l'utilisateur est déjà membre
        if ($this->projectModel->isMember($projectId, $_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vous êtes déjà membre de ce projet']);
            return;
        }

        // Vérifier s'il y a déjà une demande en attente
        if ($this->projectModel->hasPendingRequest($projectId, $_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vous avez déjà une demande en attente pour ce projet']);
            return;
        }

        $role = Security::clean($_POST['role'] ?? 'Contributeur');
        $motivation = Security::clean($_POST['motivation'] ?? '');

        // Créer une demande en attente
        if (!$this->projectModel->addMember($projectId, $_SESSION['user_id'], $role)) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création de la demande']);
            return;
        }

        // Envoyer un message au créateur du projet avec les actions
        $userModel = $this->model('User');
        $requestUser = $userModel->findById($_SESSION['user_id']);
        
        $subject = "Demande de participation au projet: " . $project['title'];
        
        $messageBody = "👋 Bonjour,\n\n";
        $messageBody .= $requestUser['prenom'] . " " . $requestUser['nom'] . " souhaite rejoindre votre projet \"" . $project['title'] . "\".\n\n";
        $messageBody .= "📋 Rôle souhaité: " . $role . "\n\n";
        
        if (!empty($motivation)) {
            $messageBody .= "💬 Message de motivation:\n" . $motivation . "\n\n";
        }
        
        $messageBody .= "Profil de l'utilisateur: " . BASE_URL . "/user/profile/" . $_SESSION['user_id'] . "\n\n";

        // Envoyer le message avec les métadonnées pour les actions
        $messageModel = $this->model('Message');
        $messageId = $messageModel->sendWithActions(
            $_SESSION['user_id'],
            $project['owner_id'],
            $subject,
            $messageBody,
            'project_join_request',
            [
                'project_id' => $projectId,
                'user_id' => $_SESSION['user_id'],
                'role' => $role
            ]
        );

        if ($messageId) {
            echo json_encode([
                'success' => true, 
                'message' => 'Demande envoyée au créateur du projet! Vous recevrez une notification de sa décision.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message']);
        }
    }

    /**
     * Accepter une demande de participation
     */
    public function acceptJoinRequest()
    {
        header('Content-Type: application/json');
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);

        $project = $this->projectModel->findById($projectId);
        
        if (!$project || $project['owner_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas le propriétaire de ce projet']);
            return;
        }

        if ($this->projectModel->acceptMember($projectId, $userId)) {
            // Envoyer un message de confirmation au nouveau membre
            $userModel = $this->model('User');
            $messageModel = $this->model('Message');
            
            $subject = "✅ Votre demande a été acceptée: " . $project['title'];
            $body = "Félicitations! 🎉\n\n";
            $body .= "Votre demande pour rejoindre le projet \"" . $project['title'] . "\" a été acceptée.\n\n";
            $body .= "Vous pouvez maintenant accéder au projet: " . BASE_URL . "/project/show/" . $projectId . "\n\n";
            $body .= "Bon travail! 💪";
            
            $messageModel->send($_SESSION['user_id'], $userId, $subject, $body);
            
            echo json_encode(['success' => true, 'message' => 'Membre accepté avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'acceptation']);
        }
    }

    /**
     * Refuser une demande de participation
     */
    public function rejectJoinRequest()
    {
        header('Content-Type: application/json');
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);
        $reason = Security::clean($_POST['reason'] ?? '');

        $project = $this->projectModel->findById($projectId);
        
        if (!$project || $project['owner_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas le propriétaire de ce projet']);
            return;
        }

        if ($this->projectModel->rejectMember($projectId, $userId)) {
            // Envoyer un message de refus au demandeur
            $messageModel = $this->model('Message');
            
            $subject = "Réponse à votre demande: " . $project['title'];
            $body = "Bonjour,\n\n";
            $body .= "Merci pour votre intérêt pour le projet \"" . $project['title'] . "\".\n\n";
            $body .= "Malheureusement, votre demande n'a pas été retenue pour le moment.\n\n";
            
            if (!empty($reason)) {
                $body .= "Raison: " . $reason . "\n\n";
            }
            
            $body .= "N'hésitez pas à explorer d'autres projets sur AlgoCodeBF!\n\n";
            $body .= "Cordialement";
            
            $messageModel->send($_SESSION['user_id'], $userId, $subject, $body);
            
            echo json_encode(['success' => true, 'message' => 'Demande refusée']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors du refus']);
        }
    }

    /**
     * Accepter un membre (pour le propriétaire)
     */
    public function acceptMember($projectId, $userId)
    {
        $this->requireLogin();

        $project = $this->projectModel->findById($projectId);
        
        if ($project['owner_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous n'êtes pas le propriétaire de ce projet";
            $this->redirect('project/show/' . $projectId);
        }

        if ($this->projectModel->acceptMember($projectId, $userId)) {
            $_SESSION['success'] = "Membre accepté";
        } else {
            $_SESSION['error'] = "Erreur lors de l'acceptation";
        }

        $this->redirect('project/show/' . $projectId);
    }

    /**
     * Quitter un projet
     */
    public function leave($projectId)
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('project/show/' . $projectId);
        }

        if ($this->projectModel->removeMember($projectId, $_SESSION['user_id'])) {
            $_SESSION['success'] = "Vous avez quitté le projet";
            $this->redirect('project/index');
        } else {
            $_SESSION['error'] = "Erreur";
            $this->redirect('project/show/' . $projectId);
        }
    }

    /**
     * Supprimer un projet (soft delete)
     */
    public function delete($projectId)
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Méthode non autorisée";
            $this->redirect('project/index');
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('project/index');
        }

        $project = $this->projectModel->findById($projectId);

        if (!$project) {
            $_SESSION['error'] = "Projet introuvable";
            $this->redirect('project/index');
        }

        // Vérifier que l'utilisateur est le propriétaire
        if ($project['owner_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin') {
            $_SESSION['error'] = "Vous n'avez pas la permission de supprimer ce projet";
            $this->redirect('project/show/' . $projectId);
        }

        // Soft delete
        $deleted = $this->projectModel->update($projectId, ['status' => 'deleted']);

        if ($deleted) {
            $_SESSION['success'] = "Projet supprimé avec succès";
            $this->redirect('user/profile');
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du projet";
            $this->redirect('project/show/' . $projectId);
        }
    }
}

