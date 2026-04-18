<?php
/**
 * Modèle ForumCategory - Gestion des catégories du forum
 */

class ForumCategory extends Model
{
    protected $table = 'forum_categories';

    /**
     * Obtenir toutes les catégories actives
     * 
     * @return array
     */
    public function getAllActive()
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE is_active = 1 
                  ORDER BY display_order ASC, name ASC";
        return $this->db->query($query);
    }

    /**
     * Obtenir toutes les catégories (actives et inactives)
     * 
     * @return array
     */
    public function getAll()
    {
        $query = "SELECT * FROM {$this->table} 
                  ORDER BY display_order ASC, name ASC";
        return $this->db->query($query);
    }

    /**
     * Obtenir une catégorie par son slug
     * 
     * @param string $slug
     * @return array|false
     */
    public function findBySlug($slug)
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Créer une nouvelle catégorie
     * 
     * @param array $data
     * @return int|false
     */
    public function createCategory($data)
    {
        // Générer le slug si non fourni
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        
        // Valeurs par défaut
        $data['icon'] = $data['icon'] ?? 'fa-folder';
        $data['color'] = $data['color'] ?? '#667eea';
        $data['display_order'] = $data['display_order'] ?? $this->getNextOrder();
        $data['is_active'] = $data['is_active'] ?? 1;
        
        return $this->create($data);
    }

    /**
     * Mettre à jour une catégorie
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCategory($id, $data)
    {
        // Régénérer le slug si le nom a changé
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        
        return $this->update($id, $data);
    }

    /**
     * Supprimer une catégorie (si aucun post associé)
     * 
     * @param int $id
     * @return bool
     */
    public function deleteCategory($id)
    {
        // Vérifier s'il y a des posts avec cette catégorie
        $category = $this->findById($id);
        if (!$category) {
            return false;
        }

        $postCount = $this->db->queryOne(
            "SELECT COUNT(*) as count FROM posts WHERE category = ?",
            [$category['slug']]
        );

        if ($postCount['count'] > 0) {
            return false; // Ne pas supprimer si des posts existent
        }

        return $this->delete($id);
    }

    /**
     * Mettre à jour le compteur de posts d'une catégorie
     * 
     * @param string $slug
     * @return bool
     */
    public function updatePostCount($slug)
    {
        $query = "UPDATE {$this->table} 
                  SET post_count = (
                      SELECT COUNT(*) FROM posts 
                      WHERE category = ? AND status = 'active'
                  )
                  WHERE slug = ?";
        
        return $this->db->execute($query, [$slug, $slug]);
    }

    /**
     * Mettre à jour tous les compteurs de posts
     * 
     * @return bool
     */
    public function updateAllPostCounts()
    {
        $query = "UPDATE {$this->table} fc
                  SET post_count = (
                      SELECT COUNT(*) FROM posts p 
                      WHERE p.category = fc.slug AND p.status = 'active'
                  )";
        
        return $this->db->execute($query);
    }

    /**
     * Réorganiser l'ordre des catégories
     * 
     * @param array $order Tableau [id => order]
     * @return bool
     */
    public function reorder($order)
    {
        $db = $this->db;
        
        try {
            foreach ($order as $id => $position) {
                $db->execute(
                    "UPDATE {$this->table} SET display_order = ? WHERE id = ?",
                    [$position, $id]
                );
            }
            return true;
        } catch (Exception $e) {
            error_log("Erreur reorder categories: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer un slug à partir du nom
     * 
     * @param string $name
     * @return string
     */
    private function generateSlug($name)
    {
        // Remplacer les caractères accentués
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        // Mettre en minuscule
        $slug = strtolower($slug);
        // Remplacer les espaces et caractères spéciaux par des tirets
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        // Supprimer les tirets en début et fin
        $slug = trim($slug, '-');
        
        return $slug;
    }

    /**
     * Obtenir le prochain ordre disponible
     * 
     * @return int
     */
    private function getNextOrder()
    {
        $result = $this->db->queryOne("SELECT MAX(display_order) as max_order FROM {$this->table}");
        return ($result['max_order'] ?? 0) + 1;
    }
}

