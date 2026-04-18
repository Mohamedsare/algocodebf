<?php

class BlogCategory
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupérer toutes les catégories actives
     */
    public function getAllActive()
    {
        return $this->db->query("
            SELECT * FROM blog_categories 
            WHERE status = 'active' 
            ORDER BY name ASC
        ");
    }

    /**
     * Récupérer toutes les catégories (pour l'admin)
     */
    public function getAll()
    {
        return $this->db->query("
            SELECT * FROM blog_categories 
            ORDER BY name ASC
        ");
    }

    /**
     * Récupérer une catégorie par son slug
     */
    public function getBySlug($slug)
    {
        return $this->db->queryOne("
            SELECT * FROM blog_categories 
            WHERE slug = ? AND status = 'active'
        ", [$slug]);
    }

    /**
     * Récupérer une catégorie par son ID
     */
    public function getById($id)
    {
        return $this->db->queryOne("
            SELECT * FROM blog_categories 
            WHERE id = ?
        ", [$id]);
    }

    /**
     * Mettre à jour le compteur de posts d'une catégorie
     */
    public function updatePostCount($categoryId)
    {
        $count = $this->db->queryOne("
            SELECT COUNT(*) as count 
            FROM blog_posts 
            WHERE category_id = ? AND status = 'published'
        ", [$categoryId]);

        $this->db->execute("
            UPDATE blog_categories 
            SET posts_count = ? 
            WHERE id = ?
        ", [$count['count'] ?? 0, $categoryId]);
    }

    /**
     * Récupérer les catégories avec le nombre de posts
     */
    public function getAllWithCount()
    {
        return $this->db->query("
            SELECT 
                bc.*,
                COUNT(bp.id) as posts_count
            FROM blog_categories bc
            LEFT JOIN blog_posts bp ON bc.id = bp.category_id AND bp.status = 'published'
            WHERE bc.status = 'active'
            GROUP BY bc.id
            ORDER BY bc.name ASC
        ");
    }
}

