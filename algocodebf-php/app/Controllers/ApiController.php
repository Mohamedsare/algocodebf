<?php
/**
 * Contrôleur API pour les requêtes AJAX
 * Fournit des endpoints JSON pour diverses fonctionnalités
 */

class ApiController extends Controller
{
    /**
     * Rechercher des utilisateurs (pour l'auto-complétion)
     * Endpoint: /api/searchUsers?q=recherche
     */
    public function searchUsers()
    {
        // Définir le header JSON
        header('Content-Type: application/json');
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            return;
        }

        // Récupérer le terme de recherche
        $query = $_GET['q'] ?? '';
        $query = trim($query);

        // Minimum 2 caractères pour la recherche
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }

        // Rechercher les utilisateurs
        $userModel = $this->model('User');
        $users = $userModel->searchUsers($query, $_SESSION['user_id']);

        // Formater les résultats pour le JSON
        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user['id'],
                'name' => $user['prenom'] . ' ' . $user['nom'],
                'prenom' => $user['prenom'],
                'nom' => $user['nom'],
                'email' => $user['email'],
                'university' => $user['university'] ?? '',
                'photo' => $user['photo_path'] ?? '',
                'role' => $user['role'] ?? 'user'
            ];
        }

        echo json_encode($results);
    }

    /**
     * Rechercher des utilisateurs avec pagination
     * Endpoint: /api/users?q=recherche&page=1&limit=10
     */
    public function users()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            return;
        }

        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 10), 50); // Max 50 résultats

        $userModel = $this->model('User');
        $users = $userModel->searchUsers($query, $_SESSION['user_id'], $limit, ($page - 1) * $limit);

        echo json_encode([
            'success' => true,
            'users' => $users,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * Vérifier la disponibilité d'un pseudo/email
     * Endpoint: /api/checkAvailability
     */
    public function checkAvailability()
    {
        header('Content-Type: application/json');
        
        $type = $_POST['type'] ?? ''; // 'email' ou 'pseudo'
        $value = $_POST['value'] ?? '';
        
        if (empty($value)) {
            echo json_encode(['available' => false]);
            return;
        }

        $userModel = $this->model('User');
        
        if ($type === 'email') {
            $exists = $userModel->emailExists($value);
        } else if ($type === 'pseudo') {
            $exists = $userModel->pseudoExists($value);
        } else {
            echo json_encode(['available' => false]);
            return;
        }

        echo json_encode(['available' => !$exists]);
    }

    /**
     * Obtenir les notifications non lues
     * Endpoint: /api/notifications
     */
    public function notifications()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'notifications' => []]);
            return;
        }

        // TODO: Implémenter le système de notifications
        echo json_encode([
            'success' => true,
            'notifications' => [],
            'unread_count' => 0
        ]);
    }

    /**
     * Obtenir le nombre de messages non lus
     * Endpoint: /api/unreadMessages
     */
    public function unreadMessages()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['count' => 0]);
            return;
        }

        $messageModel = $this->model('Message');
        $count = $messageModel->countUnread($_SESSION['user_id']);

        echo json_encode(['count' => $count]);
    }
}

