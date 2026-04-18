<?php
/**
 * Classe FileValidator - Validation robuste des fichiers uploadés
 * Protection contre les fichiers malveillants
 */

class FileValidator
{
    // Tailles maximales par défaut (en octets)
    const MAX_IMAGE_SIZE = 5 * 1024 * 1024;      // 5 MB
    const MAX_DOCUMENT_SIZE = 10 * 1024 * 1024;  // 10 MB
    const MAX_AVATAR_SIZE = 2 * 1024 * 1024;     // 2 MB
    const MAX_CV_SIZE = 5 * 1024 * 1024;         // 5 MB
    
    // Types MIME autorisés par catégorie
    private static $allowedTypes = [
        'image' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ],
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
            'text/plain'
        ],
        'archive' => [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
            'application/gzip'
        ],
        'code' => [
            'text/plain',
            'text/x-php',
            'text/x-python',
            'text/x-java',
            'text/x-c',
            'application/json',
            'application/xml'
        ]
    ];
    
    // Extensions autorisées par catégorie
    private static $allowedExtensions = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'archive' => ['zip', 'rar', '7z', 'gz'],
        'code' => ['php', 'py', 'java', 'c', 'cpp', 'js', 'html', 'css', 'json', 'xml', 'txt']
    ];
    
    /**
     * Valider un fichier uploadé (validation complète)
     * 
     * @param array $file Fichier $_FILES
     * @param string $category Catégorie (image, document, archive, code)
     * @param int $maxSize Taille maximale en octets
     * @return array ['valid' => bool, 'error' => string|null, 'mime' => string, 'extension' => string]
     */
    public static function validate($file, $category = 'image', $maxSize = null)
    {
        // Vérifier que le fichier existe
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'valid' => false,
                'error' => 'Aucun fichier uploadé ou fichier invalide'
            ];
        }
        
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'error' => self::getUploadError($file['error'])
            ];
        }
        
        // Définir la taille max selon la catégorie
        if ($maxSize === null) {
            $maxSize = self::getDefaultMaxSize($category);
        }
        
        // 1. Vérifier la taille du fichier
        $sizeCheck = self::validateSize($file['size'], $maxSize);
        if (!$sizeCheck['valid']) {
            return $sizeCheck;
        }
        
        // 2. Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $extensionCheck = self::validateExtension($extension, $category);
        if (!$extensionCheck['valid']) {
            return $extensionCheck;
        }
        
        // 3. Vérifier le type MIME réel du fichier
        $mimeCheck = self::validateMime($file['tmp_name'], $category);
        if (!$mimeCheck['valid']) {
            return $mimeCheck;
        }
        
        // 4. Vérifier que l'extension correspond au MIME type
        $consistencyCheck = self::validateMimeExtensionConsistency($mimeCheck['mime'], $extension);
        if (!$consistencyCheck['valid']) {
            return $consistencyCheck;
        }
        
        // 5. Vérifications de sécurité supplémentaires pour les images
        if ($category === 'image') {
            $imageCheck = self::validateImage($file['tmp_name']);
            if (!$imageCheck['valid']) {
                return $imageCheck;
            }
        }
        
        // Tout est OK !
        return [
            'valid' => true,
            'error' => null,
            'mime' => $mimeCheck['mime'],
            'extension' => $extension,
            'size' => $file['size'],
            'original_name' => $file['name']
        ];
    }
    
    /**
     * Valider la taille du fichier
     */
    private static function validateSize($size, $maxSize)
    {
        if ($size > $maxSize) {
            $maxSizeMB = round($maxSize / (1024 * 1024), 2);
            $actualSizeMB = round($size / (1024 * 1024), 2);
            
            return [
                'valid' => false,
                'error' => "Le fichier est trop volumineux ({$actualSizeMB} MB). Taille maximale : {$maxSizeMB} MB"
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Valider l'extension du fichier
     */
    private static function validateExtension($extension, $category)
    {
        if (!isset(self::$allowedExtensions[$category])) {
            return [
                'valid' => false,
                'error' => "Catégorie de fichier invalide : {$category}"
            ];
        }
        
        if (!in_array($extension, self::$allowedExtensions[$category])) {
            $allowed = implode(', ', self::$allowedExtensions[$category]);
            return [
                'valid' => false,
                'error' => "Extension non autorisée (.{$extension}). Extensions autorisées : {$allowed}"
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Valider le type MIME réel du fichier
     */
    private static function validateMime($filePath, $category)
    {
        if (!isset(self::$allowedTypes[$category])) {
            return [
                'valid' => false,
                'error' => "Catégorie de fichier invalide : {$category}"
            ];
        }
        
        // Détecter le MIME type réel
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::$allowedTypes[$category])) {
            return [
                'valid' => false,
                'error' => "Type de fichier non autorisé ({$mimeType}). Ce type de fichier n'est pas accepté."
            ];
        }
        
        return [
            'valid' => true,
            'mime' => $mimeType
        ];
    }
    
    /**
     * Vérifier la cohérence entre MIME type et extension
     */
    private static function validateMimeExtensionConsistency($mimeType, $extension)
    {
        $mimeToExtension = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp'],
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/zip' => ['zip'],
            'text/plain' => ['txt']
        ];
        
        if (isset($mimeToExtension[$mimeType])) {
            if (!in_array($extension, $mimeToExtension[$mimeType])) {
                return [
                    'valid' => false,
                    'error' => "Incohérence détectée : le contenu du fichier ne correspond pas à son extension"
                ];
            }
        }
        
        return ['valid' => true];
    }
    
    /**
     * Valider qu'un fichier image est valide
     */
    private static function validateImage($filePath)
    {
        // Essayer de charger l'image
        $imageInfo = @getimagesize($filePath);
        
        if ($imageInfo === false) {
            return [
                'valid' => false,
                'error' => "Le fichier n'est pas une image valide ou est corrompu"
            ];
        }
        
        // Vérifier que c'est bien une image
        $allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        if (!in_array($imageInfo[2], $allowedImageTypes)) {
            return [
                'valid' => false,
                'error' => "Format d'image non supporté"
            ];
        }
        
        return [
            'valid' => true,
            'width' => $imageInfo[0],
            'height' => $imageInfo[1]
        ];
    }
    
    /**
     * Obtenir la taille maximale par défaut selon la catégorie
     */
    private static function getDefaultMaxSize($category)
    {
        switch ($category) {
            case 'image':
                return self::MAX_IMAGE_SIZE;
            case 'document':
                return self::MAX_DOCUMENT_SIZE;
            case 'archive':
            case 'code':
                return self::MAX_DOCUMENT_SIZE;
            default:
                return self::MAX_DOCUMENT_SIZE;
        }
    }
    
    /**
     * Obtenir le message d'erreur d'upload
     */
    private static function getUploadError($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'Le fichier est trop volumineux';
            case UPLOAD_ERR_PARTIAL:
                return 'Le fichier n\'a été que partiellement téléchargé';
            case UPLOAD_ERR_NO_FILE:
                return 'Aucun fichier n\'a été téléchargé';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Dossier temporaire manquant';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Échec de l\'écriture du fichier sur le disque';
            case UPLOAD_ERR_EXTENSION:
                return 'Une extension PHP a arrêté l\'upload du fichier';
            default:
                return 'Erreur inconnue lors de l\'upload';
        }
    }
    
    /**
     * Générer un nom de fichier sécurisé et unique
     * 
     * @param string $originalName Nom original du fichier
     * @param string $prefix Préfixe optionnel
     * @return string
     */
    public static function generateSecureFileName($originalName, $prefix = '')
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $randomString = bin2hex(random_bytes(16));
        $timestamp = time();
        
        if ($prefix) {
            return "{$prefix}_{$timestamp}_{$randomString}.{$extension}";
        }
        
        return "{$timestamp}_{$randomString}.{$extension}";
    }
    
    /**
     * Valider un avatar (validation spécifique)
     * 
     * @param array $file Fichier $_FILES
     * @return array
     */
    public static function validateAvatar($file)
    {
        return self::validate($file, 'image', self::MAX_AVATAR_SIZE);
    }
    
    /**
     * Valider un CV (validation spécifique)
     * 
     * @param array $file Fichier $_FILES
     * @return array
     */
    public static function validateCV($file)
    {
        return self::validate($file, 'document', self::MAX_CV_SIZE);
    }
    
    /**
     * Obtenir les informations sur les types de fichiers autorisés
     * 
     * @param string $category Catégorie
     * @return array
     */
    public static function getAllowedInfo($category = 'image')
    {
        return [
            'extensions' => self::$allowedExtensions[$category] ?? [],
            'mime_types' => self::$allowedTypes[$category] ?? [],
            'max_size' => self::getDefaultMaxSize($category),
            'max_size_mb' => round(self::getDefaultMaxSize($category) / (1024 * 1024), 2)
        ];
    }
}

