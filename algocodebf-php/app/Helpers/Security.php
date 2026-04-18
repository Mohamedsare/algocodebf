<?php
/**
 * Classe Security - Fonctions de sécurité pour l'application
 * Protection contre CSRF, XSS, validation des données
 */

class Security
{
    /**
     * Générer un token CSRF
     * 
     * @return string
     */
    public static function generateCSRFToken()
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Vérifier le token CSRF
     * 
     * @param string $token Token à vérifier
     * @return bool
     */
    public static function verifyCSRFToken($token)
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Nettoyer une chaîne contre les attaques XSS
     * 
     * @param string $data Données à nettoyer
     * @return string
     */
    public static function clean($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    /**
     * Nettoyer le contenu sans encoder les caractères HTML
     * (pour les contenus qui seront affichés avec html_entity_decode)
     * 
     * @param string $data Données à nettoyer
     * @return string
     */
    public static function cleanContent($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        // Ne pas encoder - le contenu sera encodé à l'affichage
        return $data;
    }

    /**
     * Hacher un mot de passe
     * 
     * @param string $password Mot de passe en clair
     * @return string
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Vérifier un mot de passe
     * 
     * @param string $password Mot de passe en clair
     * @param string $hash Hash du mot de passe
     * @return bool
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Valider une adresse email
     * 
     * @param string $email Email à valider
     * @return bool
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valider un numéro de téléphone burkinabè (+226)
     * 
     * @param string $phone Numéro de téléphone
     * @return bool
     */
    public static function validatePhone($phone)
    {
        // Format attendu: +226 XX XX XX XX (8 chiffres après +226)
        $pattern = '/^\+226[0-9]{8}$/';
        return preg_match($pattern, str_replace(' ', '', $phone)) === 1;
    }

    /**
     * Valider la force d'un mot de passe
     * 
     * @param string $password Mot de passe à valider
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validatePassword($password)
    {
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return [
                'valid' => false,
                'message' => 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères'
            ];
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Le mot de passe doit contenir au moins une majuscule'
            ];
        }

        if (!preg_match('/[a-z]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Le mot de passe doit contenir au moins une minuscule'
            ];
        }

        if (!preg_match('/[0-9]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Le mot de passe doit contenir au moins un chiffre'
            ];
        }

        return ['valid' => true, 'message' => 'Mot de passe valide'];
    }

    /**
     * Générer un token aléatoire
     * 
     * @param int $length Longueur du token
     * @return string
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Valider le type MIME d'un fichier
     * 
     * @param string $filePath Chemin du fichier
     * @param array $allowedTypes Types MIME autorisés
     * @return bool
     */
    public static function validateMimeType($filePath, $allowedTypes)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Valider la taille d'un fichier
     * 
     * @param int $fileSize Taille du fichier en octets
     * @param int $maxSize Taille maximale autorisée
     * @return bool
     */
    public static function validateFileSize($fileSize, $maxSize)
    {
        return $fileSize <= $maxSize;
    }

    /**
     * Générer un nom de fichier sécurisé
     * 
     * @param string $originalName Nom original du fichier
     * @return string
     */
    public static function generateSecureFileName($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        return $filename;
    }
}

