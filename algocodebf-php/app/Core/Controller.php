<?php
/**
 * Contrôleur de base dont tous les contrôleurs héritent
 * Fournit des méthodes communes pour charger les vues et les modèles
 */

class Controller
{
    /**
     * Charger une vue avec des données
     * 
     * @param string $view Nom de la vue (sans .php)
     * @param array $data Données à passer à la vue
     */
    protected function view($view, $data = [])
    {
        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);

        // Vérifier si la vue existe
        $viewFile = VIEWS . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("La vue {$view} n'existe pas");
        }
    }

    /**
     * Charger un modèle
     * 
     * @param string $model Nom du modèle
     * @return object Instance du modèle
     */
    protected function model($model)
    {
        $modelFile = APP . '/Models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }
        die("Le modèle {$model} n'existe pas");
    }

    /**
     * Rediriger vers une URL
     * 
     * @param string $url URL de redirection
     */
    protected function redirect($url)
    {
        header('Location: ' . BASE_URL . '/' . $url);
        exit;
    }

    /**
     * Retourner une réponse JSON
     * 
     * @param mixed $data Données à retourner
     * @param int $status Code de statut HTTP
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Vérifier si l'utilisateur est connecté
     * 
     * @return bool
     */
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Vérifier si l'utilisateur est admin
     * 
     * @return bool
     */
    protected function isAdmin()
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Exiger que l'utilisateur soit connecté
     */
    protected function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect('auth/login');
        }
    }

    /**
     * Exiger que l'utilisateur soit admin
     */
    protected function requireAdmin()
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            $this->redirect('home/index');
        }
    }

    /**
     * Afficher la page 404
     */
    protected function notFound()
    {
        http_response_code(404);
        $viewFile = VIEWS . '/errors/404.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("404 - Page Non Trouvée");
        }
        exit;
    }
}

