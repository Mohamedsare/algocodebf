<?php
/**
 * Classe principale de l'application (Routeur)
 * Gère le routing MVC et l'initialisation de l'application
 */

class App
{
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();
        $controllerFound = false;

        // Vérifier si le contrôleur existe
        if (isset($url[0]) && !empty($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $controllerFile = APP . '/Controllers/' . $controllerName . '.php';
            if (file_exists($controllerFile)) {
                $this->controller = $controllerName;
                $controllerFound = true;
                unset($url[0]);
            }
        } else {
            // URL vide ou juste "/" - utiliser le contrôleur par défaut
            $controllerFound = true;
        }

        // Si le contrôleur n'existe pas, afficher 404
        if (!$controllerFound) {
            $this->show404();
            return;
        }

        // Instancier le contrôleur
        require_once APP . '/Controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Vérifier si la méthode existe
        if (isset($url[1]) && !empty($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                // Méthode demandée mais n'existe pas
                $this->show404();
                return;
            }
        }

        // Récupérer les paramètres
        $this->params = $url ? array_values($url) : [];

        // Appeler la méthode du contrôleur avec les paramètres
        try {
            call_user_func_array([$this->controller, $this->method], $this->params);
        } catch (Exception $e) {
            // En cas d'erreur lors de l'appel, afficher 404
            $this->show404();
        }
    }

    /**
     * Afficher la page 404
     */
    protected function show404()
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

    /**
     * Parse l'URL et retourne un tableau de segments
     * 
     * @return array|null
     */
    protected function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return null;
    }
}

