<?php
/**
 * Contrôleur Newsletter - Gestion des abonnements newsletter
 */

class NewsletterController extends Controller
{
    private $newsletterModel;

    public function __construct()
    {
        $this->newsletterModel = $this->model('Newsletter');
    }

    /**
     * API: S'abonner à la newsletter
     */
    public function subscribe()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $email = Security::clean($_POST['email'] ?? '');

        // Validation de l'email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email invalide']);
            return;
        }

        // Récupérer l'IP et le user agent
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // Vérifier si déjà abonné
        if ($this->newsletterModel->isSubscribed($email)) {
            echo json_encode(['success' => false, 'message' => 'Vous êtes déjà abonné à la newsletter']);
            return;
        }

        // S'abonner
        $result = $this->newsletterModel->subscribe($email, $ipAddress, $userAgent);

        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Merci ! Vous êtes maintenant abonné à notre newsletter 🎉'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ]);
        }
    }

    /**
     * API: Se désabonner de la newsletter
     */
    public function unsubscribe()
    {
        $email = Security::clean($_GET['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email invalide';
            $this->redirect('home/index');
            return;
        }

        if ($this->newsletterModel->unsubscribe($email)) {
            $_SESSION['success'] = 'Vous avez été désabonné de la newsletter';
        } else {
            $_SESSION['error'] = 'Impossible de vous désabonner';
        }

        $this->redirect('home/index');
    }

    /**
     * Page de confirmation de désabonnement
     */
    public function unsubscribeConfirm()
    {
        $email = Security::clean($_GET['email'] ?? '');

        $data = [
            'title' => 'Se désabonner - AlgoCodeBF',
            'email' => $email
        ];

        $this->view('newsletter/unsubscribe', $data);
    }

    /**
     * API: Obtenir le nombre d'abonnés
     */
    public function getCount()
    {
        header('Content-Type: application/json');
        $count = $this->newsletterModel->countActive();
        echo json_encode(['success' => true, 'count' => $count]);
    }

    /**
     * API Admin: Obtenir tous les abonnés
     */
    public function getSubscribers()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $status = $_GET['status'] ?? null;
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);

        $subscribers = $this->newsletterModel->getAllWithPagination($limit, $offset, $status);
        $stats = $this->newsletterModel->getStats();

        echo json_encode([
            'success' => true,
            'subscribers' => $subscribers,
            'stats' => $stats
        ]);
    }

    /**
     * API Admin: Supprimer un abonné
     */
    public function deleteSubscriber()
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

        if ($this->newsletterModel->deleteSubscriber($id)) {
            echo json_encode(['success' => true, 'message' => 'Abonné supprimé']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    }

    /**
     * API Admin: Exporter les abonnés en CSV
     */
    public function exportSubscribers()
    {
        $this->requireAdmin();

        $subscribers = $this->newsletterModel->getAllActive();

        // Générer le CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        
        // En-têtes
        fputcsv($output, ['Email', 'Statut', 'Date d\'inscription', 'Dernière newsletter', 'Total envoyé']);

        // Données
        foreach ($subscribers as $sub) {
            fputcsv($output, [
                $sub['email'],
                $sub['status'],
                $sub['subscribed_at'],
                $sub['last_sent_at'] ?? 'Jamais',
                $sub['total_sent']
            ]);
        }

        fclose($output);
        exit;
    }
}

