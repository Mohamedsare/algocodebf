<?php
/**
 * Modèle Tutorial - Gestion des tutoriels
 */

class Tutorial extends Model
{
    protected $table = 'tutorials';

    /**
     * Créer un nouveau tutoriel
     * 
     * @param array $data Données du tutoriel
     * @return int|false ID du tutoriel créé ou false
     */
    public function createTutorial($data)
    {
        return $this->create($data);
    }

    /**
     * Obtenir tous les tutoriels avec les informations de l'auteur
     * 
     * @param string $category Catégorie (optionnel)
     * @param string $type Type (optionnel)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getAllWithAuthor($category = null, $type = null, $limit = 20, $offset = 0)
    {
        $filters = [];
        $params = [];
        
        if ($category) {
            $filters[] = "t.category = ?";
            $params[] = $category;
        }
        
        if ($type) {
            $filters[] = "t.type = ?";
            $params[] = $type;
        }
        
        $whereClause = $filters ? "AND " . implode(" AND ", $filters) : "";
        
        $query = "
            SELECT t.*, 
                   u.prenom, u.nom, u.photo_path,
                   COUNT(DISTINCT l.id) as likes_count
            FROM tutorials t
            INNER JOIN users u ON t.user_id = u.id
            LEFT JOIN likes l ON l.likeable_type = 'tutorial' AND l.likeable_id = t.id
            WHERE t.status = 'active' {$whereClause}
            GROUP BY t.id
            ORDER BY t.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($query, $params);
    }

    /**
     * Obtenir un tutoriel avec ses détails complets
     * 
     * @param int $tutorialId ID du tutoriel
     * @return array|false
     */
    public function getWithDetails($tutorialId)
    {
        $query = "
            SELECT t.*, 
                   u.id as author_id, u.prenom, u.nom, u.photo_path, u.university, u.bio,
                   COUNT(DISTINCT l.id) as likes_count,
                   COUNT(DISTINCT c.id) as comments_count
            FROM tutorials t
            INNER JOIN users u ON t.user_id = u.id
            LEFT JOIN likes l ON l.likeable_type = 'tutorial' AND l.likeable_id = t.id
            LEFT JOIN comments c ON c.commentable_type = 'tutorial' AND c.commentable_id = t.id AND c.status = 'active'
            WHERE t.id = ? AND t.status = 'active'
            GROUP BY t.id
        ";
        
        return $this->db->queryOne($query, [$tutorialId]);
    }

    /**
     * Obtenir les tags d'un tutoriel
     * 
     * @param int $tutorialId ID du tutoriel
     * @return array
     */
    public function getTags($tutorialId)
    {
        $query = "
            SELECT t.id, t.name
            FROM tags t
            INNER JOIN tutorial_tags tt ON t.id = tt.tag_id
            WHERE tt.tutorial_id = ?
        ";
        
        return $this->db->query($query, [$tutorialId]);
    }

    /**
     * Obtenir les vidéos d'un tutoriel
     * 
     * @param int $tutorialId ID du tutoriel
     * @return array
     */
    public function getVideos($tutorialId)
    {
        $query = "
            SELECT id, tutorial_id, title, description, file_path, file_name, file_size, 
                   duration, order_index, views, created_at
            FROM tutorial_videos
            WHERE tutorial_id = ?
            ORDER BY order_index ASC, id ASC
        ";
        
        return $this->db->query($query, [$tutorialId]);
    }

    /**
     * Obtenir les chapitres d'un tutoriel
     * 
     * @param int $tutorialId ID du tutoriel
     * @return array
     */
    public function getChapters($tutorialId)
    {
        $query = "
            SELECT id, tutorial_id, chapter_number, title, description, video_id, order_index, created_at
            FROM tutorial_chapters
            WHERE tutorial_id = ?
            ORDER BY order_index ASC, chapter_number ASC
        ";
        
        return $this->db->query($query, [$tutorialId]);
    }

    /**
     * Ajouter des tags à un tutoriel
     * 
     * @param int $tutorialId ID du tutoriel
     * @param array $tagNames Noms des tags
     * @return bool
     */
    public function addTags($tutorialId, $tagNames)
    {
        foreach ($tagNames as $tagName) {
            // Vérifier si le tag existe
            $tag = $this->db->queryOne("SELECT id FROM tags WHERE name = ?", [$tagName]);
            
            if (!$tag) {
                // Créer le tag
                $this->db->execute("INSERT INTO tags (name) VALUES (?)", [$tagName]);
                $tagId = $this->db->lastInsertId();
            } else {
                $tagId = $tag['id'];
            }
            
            // Lier le tag au tutoriel
            $this->db->execute(
                "INSERT IGNORE INTO tutorial_tags (tutorial_id, tag_id) VALUES (?, ?)",
                [$tutorialId, $tagId]
            );
        }
        
        return true;
    }

    /**
     * Supprimer tous les tags d'un tutoriel
     * 
     * @param int $tutorialId ID du tutoriel
     * @return bool
     */
    public function removeTags($tutorialId)
    {
        return $this->db->execute(
            "DELETE FROM tutorial_tags WHERE tutorial_id = ?",
            [$tutorialId]
        );
    }

    /**
     * Incrémenter le nombre de vues
     * 
     * @param int $tutorialId ID du tutoriel
     * @return bool
     */
    public function incrementViews($tutorialId)
    {
        return $this->db->execute("UPDATE tutorials SET views = views + 1 WHERE id = ?", [$tutorialId]);
    }

    /**
     * Rechercher des tutoriels
     * 
     * @param string $search Terme de recherche
     * @param int $limit Limite de résultats
     * @return array
     */
    public function search($search, $limit = 20)
    {
        $query = "
            SELECT t.*, 
                   u.prenom, u.nom, u.photo_path,
                   COUNT(DISTINCT l.id) as likes_count
            FROM tutorials t
            INNER JOIN users u ON t.user_id = u.id
            LEFT JOIN likes l ON l.likeable_type = 'tutorial' AND l.likeable_id = t.id
            WHERE t.status = 'active' AND (
                MATCH(t.title, t.description, t.content) AGAINST(? IN NATURAL LANGUAGE MODE)
                OR t.title LIKE ?
                OR t.description LIKE ?
            )
            GROUP BY t.id
            ORDER BY t.created_at DESC
            LIMIT ?
        ";
        
        $searchTerm = "%{$search}%";
        return $this->db->query($query, [$search, $searchTerm, $searchTerm, $limit]);
    }

    /**
     * Obtenir les tutoriels les plus populaires
     * 
     * @param int $limit Limite de résultats
     * @return array
     */
    public function getPopular($limit = 10)
    {
        $query = "
            SELECT t.*, 
                   u.prenom, u.nom, u.photo_path,
                   COUNT(DISTINCT l.id) as likes_count
            FROM tutorials t
            INNER JOIN users u ON t.user_id = u.id
            LEFT JOIN likes l ON l.likeable_type = 'tutorial' AND l.likeable_id = t.id
            WHERE t.status = 'active'
            GROUP BY t.id
            ORDER BY t.views DESC, likes_count DESC
            LIMIT ?
        ";
        
        return $this->db->query($query, [$limit]);
    }
}

