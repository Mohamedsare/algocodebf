<?php

class SiteSettings
{
    private static $settings = null;

    /**
     * Charger tous les paramètres du site
     */
    public static function load()
    {
        if (self::$settings === null) {
            $db = Database::getInstance();
            $results = $db->query("SELECT setting_key, setting_value FROM system_settings");
            
            self::$settings = [];
            foreach ($results as $row) {
                self::$settings[$row['setting_key']] = $row['setting_value'];
            }
            
            // Valeurs par défaut si non définies
            self::$settings = array_merge([
                'site_name' => 'AlgoCodeBF',
                'site_description' => 'Plateforme collaborative pour développeurs',
                'site_keywords' => 'développement, programmation, tutoriels, projets',
                'contact_email' => 'contact@hubtech.bf',
                'contact_phone' => '+226 64 71 20 44',
                'contact_address' => 'Ouagadougou, Burkina Faso',
                'social_facebook' => '',
                'social_twitter' => '',
                'social_linkedin' => '',
                'social_github' => '',
                'enable_registration' => '1',
                'enable_email_verification' => '0',
                'enable_comments_moderation' => '0',
                'enable_file_uploads' => '1',
                'max_upload_size' => '10',
                'auto_approve_tutorials' => '1'
            ], self::$settings);
        }
        
        return self::$settings;
    }

    /**
     * Obtenir un paramètre spécifique
     */
    public static function get($key, $default = null)
    {
        if (self::$settings === null) {
            self::load();
        }
        
        return self::$settings[$key] ?? $default;
    }

    /**
     * Obtenir tous les paramètres
     */
    public static function all()
    {
        if (self::$settings === null) {
            self::load();
        }
        
        return self::$settings;
    }

    /**
     * Vérifier si un paramètre est activé (pour les booléens)
     */
    public static function isEnabled($key)
    {
        return self::get($key, '0') === '1';
    }

    /**
     * Rafraîchir les paramètres depuis la base de données
     */
    public static function refresh()
    {
        self::$settings = null;
        self::load();
    }
}

