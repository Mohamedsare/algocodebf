<?php
/**
 * Contrôleur de la messagerie privée
 */

class MessageController extends Controller
{
    private $messageModel;

    public function __construct()
    {
        $this->messageModel = $this->model('Message');
    }

    /**
     * Boîte de réception
     */
    public function inbox()
    {
        $this->requireLogin();

        $messages = $this->messageModel->getInbox($_SESSION['user_id']);
        $unreadCount = $this->messageModel->countUnread($_SESSION['user_id']);

        $data = [
            'title' => 'Messages reçus - AlgoCodeBF',
            'messages' => $messages,
            'unread_count' => $unreadCount,
            'active_tab' => 'inbox'
        ];

        $this->view('message/inbox', $data);
    }

    /**
     * Messages envoyés
     */
    public function sent()
    {
        $this->requireLogin();

        // Récupérer les messages envoyés, mais exclure les messages système (demandes automatiques)
        $messages = $this->messageModel->getSent($_SESSION['user_id'], true);

        $data = [
            'title' => 'Messages envoyés - AlgoCodeBF',
            'messages' => $messages,
            'active_tab' => 'sent'
        ];

        $this->view('message/sent', $data);
    }

    /**
     * Afficher un message (retourne JSON pour affichage dans le modal)
     */
    public function show($messageId)
    {
        header('Content-Type: application/json');
        $this->requireLogin();

        $message = $this->messageModel->findById($messageId);

        if (!$message) {
            echo json_encode(['success' => false, 'message' => 'Message introuvable']);
            return;
        }

        // Vérifier que l'utilisateur est bien l'expéditeur ou le destinataire
        if ($message['sender_id'] != $_SESSION['user_id'] && $message['receiver_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
            return;
        }

        // Marquer comme lu si c'est le destinataire
        if ($message['receiver_id'] == $_SESSION['user_id'] && !$message['is_read']) {
            $this->messageModel->markAsRead($messageId);
        }

        // Récupérer les informations de l'expéditeur
        $userModel = $this->model('User');
        $sender = $userModel->findById($message['sender_id']);

        // Préparer les données pour le JSON
        $response = [
            'success' => true,
            'id' => $message['id'],
            'subject' => $message['subject'],
            'body' => $message['body'],
            'sender_id' => $message['sender_id'],
            'sender_name' => $sender['prenom'] . ' ' . $sender['nom'],
            'sender_email' => $sender['email'],
            'sender_photo' => $sender['photo_path'] ?? '',
            'created_at' => timeAgo($message['created_at']),
            'action_type' => $message['action_type'] ?? null,
            'action_data' => $message['action_data'] ?? null,
            'action_status' => $message['action_status'] ?? null
        ];

        echo json_encode($response);
    }

    /**
     * Composer un nouveau message
     */
    public function compose($receiverId = null)
    {
        $this->requireLogin();

        // Si l'ID n'est pas dans l'URL, vérifier les paramètres GET
        if (!$receiverId && isset($_GET['receiver'])) {
            $receiverId = $_GET['receiver'];
        }

        // Récupérer les informations du destinataire si spécifié
        $receiver = null;
        if ($receiverId) {
            $userModel = $this->model('User');
            $receiver = $userModel->findById($receiverId);
        }

        // Récupérer le sujet si c'est une réponse
        $replySubject = $_GET['subject'] ?? '';
        $replyToId = $_GET['reply_to'] ?? null;

        $data = [
            'title' => 'Nouveau message - AlgoCodeBF',
            'receiver' => $receiver,
            'reply_subject' => $replySubject,
            'reply_to' => $replyToId,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('message/compose', $data);
    }

    /**
     * Envoyer un message
     */
    public function send()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('message/compose');
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('message/compose');
        }

        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $subject = Security::clean($_POST['subject'] ?? 'Sans sujet');
        $body = Security::clean($_POST['body'] ?? '');

        $validator = new Validator([
            'receiver_id' => $receiverId,
            'body' => $body
        ]);
        $validator->required('receiver_id')
                 ->required('body')->min('body', 10);

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old'] = ['subject' => $subject, 'body' => $body];
            $this->redirect('message/compose/' . $receiverId);
        }

        // Vérifier que le destinataire existe
        $userModel = $this->model('User');
        $receiver = $userModel->findById($receiverId);
        if (!$receiver) {
            $_SESSION['error'] = "Destinataire introuvable";
            $this->redirect('message/compose');
        }

        $messageId = $this->messageModel->send($_SESSION['user_id'], $receiverId, $subject, $body);

        if ($messageId) {
            $_SESSION['success'] = "Message envoyé avec succès";
            $this->redirect('message/inbox');
        } else {
            $_SESSION['error'] = "Erreur lors de l'envoi du message";
            $this->redirect('message/compose/' . $receiverId);
        }
    }

    /**
     * Conversation avec un utilisateur
     */
    public function conversation($userId)
    {
        $this->requireLogin();

        $userModel = $this->model('User');
        $otherUser = $userModel->findById($userId);

        if (!$otherUser) {
            $_SESSION['error'] = "Utilisateur introuvable";
            $this->redirect('message/inbox');
        }

        $messages = $this->messageModel->getConversation($_SESSION['user_id'], $userId);

        $data = [
            'title' => 'Conversation avec ' . $otherUser['prenom'] . ' ' . $otherUser['nom'] . ' - AlgoCodeBF',
            'other_user' => $otherUser,
            'messages' => $messages,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('message/conversation', $data);
    }

    /**
     * Supprimer un message
     */
    public function delete($messageId)
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('message/inbox');
        }

        if ($this->messageModel->deleteForUser($messageId, $_SESSION['user_id'])) {
            $_SESSION['success'] = "Message supprimé";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression";
        }

        $this->redirect('message/inbox');
    }

    /**
     * Mettre à jour le statut d'une action de message
     */
    public function updateActionStatus($messageId)
    {
        header('Content-Type: application/json');
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? '';

        if (!in_array($status, ['accepted', 'rejected', 'cancelled'])) {
            echo json_encode(['success' => false, 'message' => 'Statut invalide']);
            return;
        }

        if ($this->messageModel->updateActionStatus($messageId, $status)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * Rechercher des utilisateurs (API JSON pour l'auto-complétion)
     * Endpoint: /message/searchUsers?q=recherche
     */
    public function searchUsers()
    {
        // Définir le header JSON
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        try {
            // Vérifier que l'utilisateur est connecté
            if (!isset($_SESSION['user_id'])) {
                echo json_encode([
                    'error' => 'Non connecté',
                    'data' => []
                ]);
                return;
            }

            // Récupérer le terme de recherche depuis GET
            $query = $_GET['q'] ?? '';
            $query = trim($query);

            // Debug: Log la recherche
            error_log("🔍 Recherche utilisateur: '$query' par user_id: " . $_SESSION['user_id']);

            // Minimum 2 caractères pour la recherche
            if (strlen($query) < 2) {
                echo json_encode([]);
                return;
            }

            // Rechercher les utilisateurs
            $userModel = $this->model('User');
            $users = $userModel->searchUsers($query, $_SESSION['user_id'], 10, 0);

            // Debug: Log les résultats
            error_log("✅ Résultats trouvés: " . count($users));

            // Formater les résultats pour le JSON
            $results = [];
            foreach ($users as $user) {
                $results[] = [
                    'id' => (int)$user['id'],
                    'name' => $user['prenom'] . ' ' . $user['nom'],
                    'prenom' => $user['prenom'] ?? '',
                    'nom' => $user['nom'] ?? '',
                    'email' => $user['email'] ?? '',
                    'university' => $user['university'] ?? '',
                    'photo' => $user['photo_path'] ?? '',
                    'role' => $user['role'] ?? 'user'
                ];
            }

            echo json_encode($results);
            
        } catch (Exception $e) {
            // Log l'erreur
            error_log("❌ Erreur recherche utilisateurs: " . $e->getMessage());
            
            echo json_encode([
                'error' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Page de test de la recherche (pour debug)
     * Endpoint: /message/testSearch
     */
    public function testSearch()
    {
        $this->requireLogin();
        
        echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Recherche Utilisateurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { color: #333; margin-bottom: 20px; }
        input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            margin-bottom: 20px;
        }
        input:focus { outline: none; border-color: #667eea; }
        .result {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 10px;
            background: #f9f9f9;
        }
        .status {
            padding: 15px;
            background: #e3f2fd;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error { background: #ffebee; color: #c62828; }
        .success { background: #e8f5e9; color: #2e7d32; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-search"></i> Test de Recherche Utilisateurs</h1>
        <p>Session User ID: <strong>' . ($_SESSION['user_id'] ?? 'Non connecté') . '</strong></p>
        <p>Session User Name: <strong>' . ($_SESSION['user_name'] ?? 'N/A') . '</strong></p>
        <hr style="margin: 20px 0;">
        <input type="text" id="search" placeholder="Tapez un nom (min 2 caractères)..." />
        <div class="status" id="status">Prêt à rechercher...</div>
        <div id="results"></div>
    </div>
    <script>
        let timeout;
        document.getElementById("search").addEventListener("input", function(e) {
            clearTimeout(timeout);
            const query = e.target.value.trim();
            const status = document.getElementById("status");
            const results = document.getElementById("results");
            
            if (query.length < 2) {
                status.className = "status";
                status.textContent = "Tapez au moins 2 caractères...";
                results.innerHTML = "";
                return;
            }
            
            status.className = "status";
            status.innerHTML = "<i class=\"fas fa-spinner fa-spin\"></i> Recherche...";
            
            timeout = setTimeout(() => {
                const url = "' . BASE_URL . '/message/searchUsers?q=" + encodeURIComponent(query);
                console.log("🔍 Requête:", url);
                
                fetch(url)
                    .then(r => {
                        console.log("📡 Status:", r.status);
                        return r.json();
                    })
                    .then(data => {
                        console.log("✅ Données:", data);
                        
                        if (data.error) {
                            status.className = "status error";
                            status.textContent = "Erreur: " + data.error;
                            results.innerHTML = "<pre>" + JSON.stringify(data, null, 2) + "</pre>";
                            return;
                        }
                        
                        if (data.length === 0) {
                            status.className = "status";
                            status.textContent = "Aucun utilisateur trouvé";
                            results.innerHTML = "";
                            return;
                        }
                        
                        status.className = "status success";
                        status.textContent = data.length + " utilisateur(s) trouvé(s)";
                        
                        results.innerHTML = data.map(u => `
                            <div class="result">
                                <strong>${u.name}</strong><br>
                                <small>Email: ${u.email}</small><br>
                                <small>Université: ${u.university || "N/A"}</small><br>
                                <small>ID: ${u.id} | Role: ${u.role}</small>
                            </div>
                        `).join("");
                    })
                    .catch(err => {
                        console.error("❌ Erreur:", err);
                        status.className = "status error";
                        status.textContent = "Erreur: " + err.message;
                    });
            }, 300);
        });
    </script>
</body>
</html>';
    }
}

