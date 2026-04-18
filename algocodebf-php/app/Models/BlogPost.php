<?php
/**
 * Modèle BlogPost - Gestion des articles de blog
 */

class BlogPost extends Model
{
    protected $table = 'blog_posts';

    /**
     * Créer un nouvel article
     * 
     * @param array $data Données de l'article
     * @return int|false ID de l'article créé ou false
     */
    public function createPost($data)
    {
        // Générer un slug unique à partir du titre
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        return $this->create($data);
    }

    /**
     * Générer un slug unique
     * 
     * @param string $title Titre de l'article
     * @param int $excludeId ID à exclure de la vérification (pour l'édition)
     * @return string
     */
    public function generateUniqueSlug($title, $excludeId = null)
    {
        // Convertir en minuscules et remplacer les espaces par des tirets
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        
        // Vérifier l'unicité
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $existing = $this->findBy('slug', $slug);
            
            // Si pas trouvé, ou si c'est le même article (édition), OK
            if (!$existing || ($excludeId && $existing['id'] == $excludeId)) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Obtenir tous les articles publiés avec l'auteur
     * 
     * @param string $category Catégorie (optionnel)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getAllPublished($category = null, $limit = 10, $offset = 0)
    {
        $categoryFilter = $category ? "AND bp.category = ?" : "";
        $params = [];
        
        if ($category) {
            $params[] = $category;
        }
        
        $query = "
            SELECT bp.*, 
                   u.prenom, u.nom, u.photo_path,
                   CONCAT(u.prenom, ' ', u.nom) as author_name,
                   u.photo_path as author_photo,
                   COUNT(DISTINCT l.id) as likes_count,
                   COUNT(DISTINCT c.id) as comments_count
            FROM blog_posts bp
            INNER JOIN users u ON bp.author_id = u.id
            LEFT JOIN likes l ON l.likeable_type = 'blog' AND l.likeable_id = bp.id
            LEFT JOIN comments c ON c.commentable_type = 'blog' AND c.commentable_id = bp.id AND c.status = 'active'
            WHERE bp.status = 'published' {$categoryFilter}
            GROUP BY bp.id
            ORDER BY bp.published_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($query, $params);
    }

    /**
     * Obtenir un article par son slug
     * 
     * @param string $slug Slug de l'article
     * @return array|false
     */
    public function findBySlug($slug)
    {
        $query = "
            SELECT bp.*, 
                   u.id as author_user_id, u.prenom, u.nom, u.photo_path
            FROM blog_posts bp
            INNER JOIN users u ON bp.author_id = u.id
            WHERE bp.slug = ?
        ";
        
        return $this->db->queryOne($query, [$slug]);
    }

    /**
     * Obtenir un article avec tous ses détails (likes, commentaires)
     * 
     * @param string $slug Slug de l'article
     * @return array|false
     */
    public function getWithDetails($slug)
    {
        $query = "
            SELECT bp.*, 
                   u.id as author_id,
                   u.prenom, 
                   u.nom, 
                   u.email, 
                   u.bio as author_bio,
                   u.photo_path,
                   CONCAT(u.prenom, ' ', u.nom) as author_name,
                   u.photo_path as author_photo,
                   COUNT(DISTINCT l.id) as likes_count,
                   COUNT(DISTINCT c.id) as comments_count
            FROM blog_posts bp
            INNER JOIN users u ON bp.author_id = u.id
            LEFT JOIN likes l ON l.likeable_type = 'blog' AND l.likeable_id = bp.id
            LEFT JOIN comments c ON c.commentable_type = 'blog' AND c.commentable_id = bp.id AND c.status = 'active'
            WHERE bp.slug = ?
            GROUP BY bp.id
        ";
        
        return $this->db->queryOne($query, [$slug]);
    }

    /**
     * Obtenir les articles similaires
     * 
     * @param int $postId ID de l'article actuel
     * @param string $category Catégorie
     * @param int $limit Limite de résultats
     * @return array
     */
    public function getRelated($postId, $category, $limit = 3)
    {
        $query = "
            SELECT bp.*, 
                   CONCAT(u.prenom, ' ', u.nom) as author_name
            FROM blog_posts bp
            INNER JOIN users u ON bp.author_id = u.id
            WHERE bp.status = 'published' 
            AND bp.id != ? 
            AND bp.category = ?
            ORDER BY bp.created_at DESC
            LIMIT ?
        ";
        
        return $this->db->query($query, [$postId, $category, $limit]);
    }

    /**
     * Incrémenter le nombre de vues
     * 
     * @param int $postId ID de l'article
     * @return bool
     */
    public function incrementViews($postId)
    {
        return $this->db->execute("UPDATE blog_posts SET views = views + 1 WHERE id = ?", [$postId]);
    }

    /**
     * Obtenir les articles les plus populaires
     * 
     * @param int $limit Limite de résultats
     * @return array
     */
    public function getPopular($limit = 5)
    {
        $query = "
            SELECT bp.*, 
                   u.prenom, u.nom, u.photo_path
            FROM blog_posts bp
            INNER JOIN users u ON bp.author_id = u.id
            WHERE bp.status = 'published'
            ORDER BY bp.views DESC
            LIMIT ?
        ";
        
        return $this->db->query($query, [$limit]);
    }

    /**
     * Obtenir l'article à la une (le plus récent)
     * 
     * @return array|false
     */
    public function getFeaturedPost()
    {
        $query = "
            SELECT bp.*, 
                   CONCAT(u.prenom, ' ', u.nom) as author_name,
                   u.photo_path as author_photo
            FROM blog_posts bp
            INNER JOIN users u ON bp.author_id = u.id
            WHERE bp.status = 'published'
            ORDER BY bp.published_at DESC
            LIMIT 1
        ";
        
        return $this->db->queryOne($query);
    }

    /**
     * Rechercher des articles
     * 
     * @param string $search Terme de recherche
     * @param int $limit Limite de résultats
     * @return array
     */
    public function search($search, $limit = 20)
    {
        $query = "
            SELECT bp.*, 
                   u.prenom, u.nom, u.photo_path
            FROM blog_posts bp
            INNER JOIN users u ON bp.author_id = u.id
            WHERE bp.status = 'published' AND (
                MATCH(bp.title, bp.excerpt, bp.content) AGAINST(? IN NATURAL LANGUAGE MODE)
                OR bp.title LIKE ?
                OR bp.excerpt LIKE ?
            )
            ORDER BY bp.published_at DESC
            LIMIT ?
        ";
        
        $searchTerm = "%{$search}%";
        return $this->db->query($query, [$search, $searchTerm, $searchTerm, $limit]);
    }
}

