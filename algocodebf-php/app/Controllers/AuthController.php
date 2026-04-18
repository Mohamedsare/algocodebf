<?php
/**
 * Contrôleur d'authentification
 * Gestion de l'inscription, connexion, déconnexion, vérification email
 */

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Page de connexion
     */
    public function login()
    {
        // Si déjà connecté, rediriger
        if ($this->isLoggedIn()) {
            $this->redirect('home/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier le rate limiting
            $rateLimiter = new RateLimiter();
            $rateCheck = $rateLimiter->check('login', 5, 900); // 5 tentatives, 15 minutes de blocage
            
            if (!$rateCheck['allowed']) {
                $minutes = ceil($rateCheck['blocked_seconds'] / 60);
                $_SESSION['error'] = "⚠️ Trop de tentatives de connexion. Veuillez réessayer dans {$minutes} minute(s).";
                $_SESSION['old'] = ['email' => $_POST['email'] ?? ''];
                $this->redirect('auth/login');
            }
            
            // Vérifier le token CSRF
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('auth/login');
            }

            $email = Security::clean($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validation
            $validator = new Validator(['email' => $email, 'password' => $password]);
            $validator->required('email')->email('email')
                     ->required('password');

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = ['email' => $email];
                $rateLimiter->hit('login', 5, 900); // Compter comme tentative échouée
                $this->redirect('auth/login');
            }

            // Tentative de connexion
            $user = $this->userModel->login($email, $password);

            if ($user) {
                // Réinitialiser le compteur en cas de succès
                $rateLimiter->reset('login');
                // Vérification de l'email désactivée en développement
                // À activer en production en décommentant ces lignes :
                /*
                if (!$user['email_verified']) {
                    $_SESSION['error'] = "Veuillez vérifier votre email avant de vous connecter";
                    $this->redirect('auth/login');
                }
                */

                if ($user['status'] !== 'active') {
                    $_SESSION['error'] = "Votre compte a été suspendu";
                    $this->redirect('auth/login');
                }

                // Créer la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['success'] = "Bienvenue, " . $user['prenom'] . "!";

                // Rediriger vers la page demandée ou l'accueil
                $redirect = $_SESSION['redirect_after_login'] ?? 'home/index';
                unset($_SESSION['redirect_after_login']);
                $this->redirect($redirect);
            } else {
                // Incrémenter le compteur en cas d'échec
                $rateLimiter->hit('login', 5, 900);
                $remaining = $rateCheck['remaining'] - 1;
                
                if ($remaining > 0) {
                    $_SESSION['error'] = "❌ Email ou mot de passe incorrect. Il vous reste {$remaining} tentative(s).";
                } else {
                    $_SESSION['error'] = "❌ Email ou mot de passe incorrect. Compte temporairement bloqué.";
                }
                
                $_SESSION['old'] = ['email' => $email];
                $this->redirect('auth/login');
            }
        }

        $data = [
            'title' => 'Connexion - AlgoCodeBF',
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('auth/login', $data);
    }

    /**
     * Page d'inscription
     */
    public function register()
    {
        // Si déjà connecté, rediriger
        if ($this->isLoggedIn()) {
            $this->redirect('home/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier le rate limiting pour les inscriptions
            $rateLimiter = new RateLimiter();
            $rateCheck = $rateLimiter->check('register', 3, 1800); // 3 tentatives, 30 minutes de blocage
            
            if (!$rateCheck['allowed']) {
                $minutes = ceil($rateCheck['blocked_seconds'] / 60);
                $_SESSION['error'] = "⚠️ Trop de tentatives d'inscription. Veuillez réessayer dans {$minutes} minute(s).";
                $this->redirect('auth/register');
            }
            
            // Vérifier le token CSRF
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $rateLimiter->hit('register', 3, 1800);
                $this->redirect('auth/register');
            }

            // Récupérer et nettoyer les données
            $data = [
                'prenom' => Security::clean($_POST['prenom'] ?? ''),
                'nom' => Security::clean($_POST['nom'] ?? ''),
                'email' => Security::clean($_POST['email'] ?? ''),
                'phone' => Security::clean($_POST['phone'] ?? ''),
                'university' => Security::clean($_POST['university'] ?? ''),
                'faculty' => Security::clean($_POST['faculty'] ?? ''),
                'city' => Security::clean($_POST['city'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'password_confirm' => $_POST['password_confirm'] ?? ''
            ];

            // Validation
            $validator = new Validator($data);
            $validator->required('prenom')->min('prenom', 2)
                     ->required('nom')->min('nom', 2)
                     ->required('email')->email('email')
                     ->required('phone')
                     ->required('university')
                     ->required('faculty')
                     ->required('city')
                     ->required('password')->min('password', PASSWORD_MIN_LENGTH)
                     ->required('password_confirm')
                     ->match('password', 'password_confirm', 'Les mots de passe ne correspondent pas');

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $_SESSION['old'] = $data;
                $this->redirect('auth/register');
            }

            // Validation du mot de passe
            $passwordValidation = Security::validatePassword($data['password']);
            if (!$passwordValidation['valid']) {
                $_SESSION['error'] = $passwordValidation['message'];
                $_SESSION['old'] = $data;
                $this->redirect('auth/register');
            }

            // Validation du téléphone
            $phone = $data['phone'];
            if (substr($phone, 0, 4) !== PHONE_PREFIX) {
                $phone = PHONE_PREFIX . $phone;
            }
            if (!Security::validatePhone($phone)) {
                $_SESSION['error'] = "Numéro de téléphone invalide (format: +226 XX XX XX XX)";
                $_SESSION['old'] = $data;
                $this->redirect('auth/register');
            }
            $data['phone'] = $phone;

            // Vérifier si l'email existe déjà
            if ($this->userModel->emailExists($data['email'])) {
                $_SESSION['error'] = "Cet email est déjà utilisé";
                $_SESSION['old'] = $data;
                $this->redirect('auth/register');
            }

            // Retirer password_confirm avant l'insertion en base
            unset($data['password_confirm']);

            // Créer l'utilisateur
            $userId = $this->userModel->register($data);

            if ($userId) {
                // Récupérer l'utilisateur créé
                $user = $this->userModel->findById($userId);

                // ========================================
                // ENVOI DE MAIL DE BIENVENUE
                // Commenté en développement - À activer en production
                // ========================================
                /*
                try {
                    // Envoyer l'email de bienvenue avec lien de vérification
                    Mailer::sendWelcomeEmail(
                        $user['email'],
                        $user['prenom'],
                        $user['email_verification_token']
                    );
                } catch (Exception $e) {
                    // En cas d'erreur d'envoi, continuer quand même (ne pas bloquer l'inscription)
                    error_log("Erreur envoi email de bienvenue: " . $e->getMessage());
                }
                */

                // Attribuer le badge de nouveau membre
                $badgeModel = $this->model('Badge');
                $badgeModel->checkAndAwardBadges($userId);

                $_SESSION['success'] = "Inscription réussie! Vous pouvez maintenant vous connecter.";
                $this->redirect('auth/login');
            } else {
                $_SESSION['error'] = "Une erreur s'est produite lors de l'inscription";
                $_SESSION['old'] = $data;
                $this->redirect('auth/register');
            }
        }

        $data = [
            'title' => 'Inscription - AlgoCodeBF',
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('auth/register', $data);
    }

    /**
     * Vérification de l'email
     */
    public function verify()
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = "Token de vérification manquant";
            $this->redirect('auth/login');
        }

        if ($this->userModel->verifyEmail($token)) {
            $_SESSION['success'] = "Email vérifié avec succès! Vous pouvez maintenant vous connecter.";
        } else {
            $_SESSION['error'] = "Token de vérification invalide ou expiré";
        }

        $this->redirect('auth/login');
    }

    /**
     * Page de demande de réinitialisation de mot de passe
     */
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('auth/forgotPassword');
            }

            $email = Security::clean($_POST['email'] ?? '');

            $validator = new Validator(['email' => $email]);
            $validator->required('email')->email('email');

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $this->redirect('auth/forgotPassword');
            }

            $result = $this->userModel->initiatePasswordReset($email);

            if ($result) {
                Mailer::sendPasswordResetEmail(
                    $result['user']['email'],
                    $result['user']['prenom'],
                    $result['token']
                );
            }

            // Message générique pour la sécurité
            $_SESSION['success'] = "Si cet email existe, un lien de réinitialisation a été envoyé.";
            $this->redirect('auth/login');
        }

        $data = [
            'title' => 'Mot de passe oublié - AlgoCodeBF',
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('auth/forgot-password', $data);
    }

    /**
     * Page de réinitialisation de mot de passe
     */
    public function resetPassword()
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = "Token de réinitialisation manquant";
            $this->redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('auth/resetPassword?token=' . $token);
            }

            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $validator = new Validator(['password' => $password, 'password_confirm' => $passwordConfirm]);
            $validator->required('password')->min('password', PASSWORD_MIN_LENGTH)
                     ->required('password_confirm')
                     ->match('password', 'password_confirm');

            if ($validator->fails()) {
                $_SESSION['errors'] = $validator->errors();
                $this->redirect('auth/resetPassword?token=' . $token);
            }

            $passwordValidation = Security::validatePassword($password);
            if (!$passwordValidation['valid']) {
                $_SESSION['error'] = $passwordValidation['message'];
                $this->redirect('auth/resetPassword?token=' . $token);
            }

            if ($this->userModel->resetPassword($token, $password)) {
                $_SESSION['success'] = "Mot de passe réinitialisé avec succès!";
                $this->redirect('auth/login');
            } else {
                $_SESSION['error'] = "Token invalide ou expiré";
                $this->redirect('auth/forgotPassword');
            }
        }

        $data = [
            'title' => 'Réinitialiser le mot de passe - AlgoCodeBF',
            'token' => $token,
            'csrf_token' => Security::generateCSRFToken()
        ];

        $this->view('auth/reset-password', $data);
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        session_destroy();
        $this->redirect('home/index');
    }
}

