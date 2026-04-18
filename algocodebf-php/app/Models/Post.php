<?php
/**
 * Modèle Post - Gestion des posts du forum
 */

class Post extends Model
{
    protected $table = 'posts';

    /**
     * Créer un nouveau post
     * 
     * @param array $data Données du post
     * @return int|false ID du post créé ou false
     */
    public function createPost($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    /**
     * Obtenir tous les posts avec les informations de l'auteur
     * 
     * @param string $category Catégorie (optionnel)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getAllWithAuthor($category = null, $limit = 20, $offset = 0)
    {
        $categoryFilter = $category ? "AND p.category = ?" : "";
        
        $query = "
            SELECT p.*, 
                   u.prenom, u.nom, u.photo_path,
                   COUNT(DISTINCT c.id) as comments_count,
                   COUNT(DISTINCT l.id) as likes_count
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN comments c ON c.commentable_type = 'post' AND c.commentable_id = p.id AND c.status = 'active'
            LEFT JOIN likes l ON l.likeable_type = 'post' AND l.likeable_id = p.id
            WHERE p.status = 'active' {$categoryFilter}
            GROUP BY p.id
            ORDER BY p.is_pinned DESC, p.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params = $category ? [$category, $limit, $offset] : [$limit, $offset];
        return $this->db->query($query, $params);
    }

    /**
     * Obtenir un post avec ses détails complets
     * 
     * @param int $postId ID du post
     * @return array|false
     */
    public function getWithDetails($postId)
    {
        $query = "
            SELECT p.*, 
                   u.id as author_id, u.prenom, u.nom, u.photo_path, u.university,
                   COUNT(DISTINCT c.id) as comments_count,
                   COUNT(DISTINCT l.id) as likes_count
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN comments c ON c.commentable_type = 'post' AND c.commentable_id = p.id AND c.status = 'active'
            LEFT JOIN likes l ON l.likeable_type = 'post' AND l.likeable_id = p.id
            WHERE p.id = ? AND p.status = 'active'
            GROUP BY p.id
        ";
        
        return $this->db->queryOne($query, [$postId]);
    }

    /**
     * Obtenir les commentaires d'un post
     * 
     * @param int $postId ID du post
     * @return array
     */
    public function getComments($postId)
    {
        $query = "
            SELECT c.*, 
                   u.id as author_id, u.prenom, u.nom, u.photo_path,
                   COUNT(l.id) as likes_count
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            LEFT JOIN likes l ON l.likeable_type = 'comment' AND l.likeable_id = c.id
            WHERE c.commentable_type = 'post' AND c.commentable_id = ? AND c.status = 'active'
            GROUP BY c.id
            ORDER BY c.created_at ASC
        ";
        
        return $this->db->query($query, [$postId]);
    }

    /**
     * Ajouter un commentaire à un post
     * 
     * @param int $postId ID du post
     * @param int $userId ID de l'utilisateur
     * @param string $body Contenu du commentaire
     * @return int|false ID du commentaire créé ou false
     */
    public function addComment($postId, $userId, $body)
    {
        $query = "INSERT INTO comments (commentable_type, commentable_id, user_id, body, created_at) VALUES (?, ?, ?, ?, ?)";
        
        if ($this->db->execute($query, ['post', $postId, $userId, $body, date('Y-m-d H:i:s')])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Ajouter ou retirer un like
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $type Type (post ou comment)
     * @param int $id ID du post ou commentaire
     * @return bool
     */
    public function toggleLike($userId, $type, $id)
    {
        // Vérifier si l'utilisateur a déjà liké
        $existing = $this->db->queryOne(
            "SELECT id FROM likes WHERE user_id = ? AND likeable_type = ? AND likeable_id = ?",
            [$userId, $type, $id]
        );
        
        if ($existing) {
            // Retirer le like
            return $this->db->execute(
                "DELETE FROM likes WHERE user_id = ? AND likeable_type = ? AND likeable_id = ?",
                [$userId, $type, $id]
            );
        } else {
            // Ajouter le like
            return $this->db->execute(
                "INSERT INTO likes (user_id, likeable_type, likeable_id, created_at) VALUES (?, ?, ?, ?)",
                [$userId, $type, $id, date('Y-m-d H:i:s')]
            );
        }
    }

    /**
     * Vérifier si un utilisateur a liké
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $type Type (post ou comment)
     * @param int $id ID du post ou commentaire
     * @return bool
     */
    public function hasLiked($userId, $type, $id)
    {
        $result = $this->db->queryOne(
            "SELECT id FROM likes WHERE user_id = ? AND likeable_type = ? AND likeable_id = ?",
            [$userId, $type, $id]
        );
        
        return $result !== false;
    }

    /**
     * Incrémenter le nombre de vues
     * 
     * @param int $postId ID du post
     * @return bool
     */
    public function incrementViews($postId)
    {
        return $this->db->execute("UPDATE posts SET views = views + 1 WHERE id = ?", [$postId]);
    }

    /**
     * Rechercher des posts
     * 
     * @param string $search Terme de recherche
     * @param int $limit Limite de résultats
     * @return array
     */
    public function search($search, $limit = 20)
    {
        $query = "
            SELECT p.*, 
                   u.prenom, u.nom, u.photo_path,
                   COUNT(DISTINCT c.id) as comments_count,
                   COUNT(DISTINCT l.id) as likes_count
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN comments c ON c.commentable_type = 'post' AND c.commentable_id = p.id AND c.status = 'active'
            LEFT JOIN likes l ON l.likeable_type = 'post' AND l.likeable_id = p.id
            WHERE p.status = 'active' AND (
                MATCH(p.title, p.body) AGAINST(? IN NATURAL LANGUAGE MODE)
                OR p.title LIKE ?
                OR p.body LIKE ?
            )
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT ?
        ";
        
        $searchTerm = "%{$search}%";
        return $this->db->query($query, [$search, $searchTerm, $searchTerm, $limit]);
    }

    /**
     * Obtenir les catégories disponibles avec le nombre de posts
     * 
     * @return array
     */
    public function getCategories()
    {
        $query = "
            SELECT category, COUNT(*) as count
            FROM posts
            WHERE status = 'active'
            GROUP BY category
            ORDER BY count DESC
        ";
        
        return $this->db->query($query);
    }
}

