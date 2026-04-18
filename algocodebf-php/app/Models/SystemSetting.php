<?php
/**
 * Modèle SystemSetting - Gestion des paramètres système
 */

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    /**
     * Obtenir un paramètre par sa clé
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $setting = $this->findBy('setting_key', $key);
        
        if (!$setting) {
            return $default;
        }
        
        // Convertir selon le type
        switch ($setting['setting_type']) {
            case 'boolean':
                return (bool)$setting['setting_value'];
            case 'number':
                return (int)$setting['setting_value'];
            case 'json':
                return json_decode($setting['setting_value'], true);
            default:
                return $setting['setting_value'];
        }
    }

    /**
     * Définir un paramètre
     * 
     * @param string $key
     * @param mixed $value
     * @param int $userId ID de l'admin qui modifie
     * @return bool
     */
    public function set($key, $value, $userId = null)
    {
        $existing = $this->findBy('setting_key', $key);
        
        // Convertir la valeur en string pour stockage
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_array($value)) {
            $value = json_encode($value);
        }
        
        $data = [
            'setting_value' => $value,
            'updated_by' => $userId
        ];
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['setting_key'] = $key;
            return $this->create($data);
        }
    }

    /**
     * Obtenir tous les paramètres par catégorie
     * 
     * @param string $category
     * @return array
     */
    public function getByCategory($category)
    {
        $query = "SELECT * FROM {$this->table} WHERE category = ? ORDER BY setting_key";
        return $this->db->query($query, [$category]);
    }

    /**
     * Obtenir toutes les catégories
     * 
     * @return array
     */
    public function getAllCategories()
    {
        $query = "SELECT DISTINCT category FROM {$this->table} ORDER BY category";
        return $this->db->query($query);
    }

    /**
     * Mettre à jour plusieurs paramètres à la fois
     * 
     * @param array $settings Tableau ['key' => 'value']
     * @param int $userId
     * @return bool
     */
    public function updateMultiple($settings, $userId = null)
    {
        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value, $userId);
            }
            return true;
        } catch (Exception $e) {
            error_log("Erreur updateMultiple settings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Réinitialiser les paramètres par défaut
     * 
     * @return bool
     */
    public function resetToDefaults()
    {
        // Cette méthode pourrait réexécuter le SQL d'installation
        // Pour l'instant, on retourne juste true
        return true;
    }
}

