<?php
/**
 * Modèle Project - Gestion des projets collaboratifs
 */

class Project extends Model
{
    protected $table = 'projects';

    /**
     * Créer un nouveau projet
     * 
     * @param array $data Données du projet
     * @return int|false ID du projet créé ou false
     */
    public function createProject($data)
    {
        $projectId = $this->create($data);
        
        if ($projectId) {
            // Ajouter le créateur comme membre du projet
            $this->db->execute(
                "INSERT INTO project_members (project_id, user_id, role, status) VALUES (?, ?, ?, ?)",
                [$projectId, $data['owner_id'], 'Chef de projet', 'active']
            );
        }
        
        return $projectId;
    }

    /**
     * Obtenir tous les projets avec les informations du propriétaire
     * 
     * @param string $status Statut (optionnel)
     * @param bool $lookingForMembers Cherche des membres (optionnel)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getAllWithOwner($status = null, $lookingForMembers = null, $limit = 20, $offset = 0)
    {
        $filters = ["p.visibility = 'public'"];
        $params = [];
        
        if ($status) {
            $filters[] = "p.status = ?";
            $params[] = $status;
        }
        
        if ($lookingForMembers !== null) {
            $filters[] = "p.looking_for_members = ?";
            $params[] = $lookingForMembers ? 1 : 0;
        }
        
        $whereClause = implode(" AND ", $filters);
        
        $query = "
            SELECT p.*, 
                   u.prenom as owner_prenom, 
                   u.nom as owner_nom, 
                   u.photo_path as owner_photo,
                   COUNT(DISTINCT pm.id) as members_count
            FROM projects p
            INNER JOIN users u ON p.owner_id = u.id
            LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.status = 'active'
            WHERE {$whereClause}
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($query, $params);
    }

    /**
     * Obtenir un projet avec ses détails complets
     * 
     * @param int $projectId ID du projet
     * @return array|false
     */
    public function getWithDetails($projectId)
    {
        $query = "
            SELECT p.*, 
                   u.id as owner_user_id, 
                   u.prenom as owner_prenom, 
                   u.nom as owner_nom, 
                   u.photo_path as owner_photo, 
                   u.university as owner_university,
                   COUNT(DISTINCT pm.id) as members_count
            FROM projects p
            INNER JOIN users u ON p.owner_id = u.id
            LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.status = 'active'
            WHERE p.id = ?
            GROUP BY p.id
        ";
        
        return $this->db->queryOne($query, [$projectId]);
    }

    /**
     * Obtenir les membres d'un projet
     * 
     * @param int $projectId ID du projet
     * @return array
     */
    public function getMembers($projectId)
    {
        $query = "
            SELECT pm.*, 
                   u.id as user_id, u.prenom, u.nom, u.photo_path, u.university, u.city
            FROM project_members pm
            INNER JOIN users u ON pm.user_id = u.id
            WHERE pm.project_id = ? AND pm.status = 'active'
            ORDER BY pm.joined_at ASC
        ";
        
        return $this->db->query($query, [$projectId]);
    }

    /**
     * Ajouter un membre au projet
     * 
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @param string $role Rôle dans le projet
     * @return bool
     */
    public function addMember($projectId, $userId, $role = 'Contributeur')
    {
        return $this->db->execute(
            "INSERT INTO project_members (project_id, user_id, role, status) VALUES (?, ?, ?, ?)",
            [$projectId, $userId, $role, 'pending']
        );
    }

    /**
     * Accepter un membre dans le projet
     * 
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function acceptMember($projectId, $userId)
    {
        return $this->db->execute(
            "UPDATE project_members SET status = 'active' WHERE project_id = ? AND user_id = ?",
            [$projectId, $userId]
        );
    }

    /**
     * Retirer un membre du projet
     * 
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function removeMember($projectId, $userId)
    {
        return $this->db->execute(
            "UPDATE project_members SET status = 'left' WHERE project_id = ? AND user_id = ?",
            [$projectId, $userId]
        );
    }

    /**
     * Vérifier si un utilisateur est membre d'un projet
     * 
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function isMember($projectId, $userId)
    {
        $result = $this->db->queryOne(
            "SELECT id FROM project_members WHERE project_id = ? AND user_id = ? AND status = 'active'",
            [$projectId, $userId]
        );
        
        return $result !== false;
    }

    /**
     * Vérifier si un utilisateur a une demande en attente
     * 
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function hasPendingRequest($projectId, $userId)
    {
        $result = $this->db->queryOne(
            "SELECT id FROM project_members WHERE project_id = ? AND user_id = ? AND status = 'pending'",
            [$projectId, $userId]
        );
        
        return $result !== false;
    }

    /**
     * Refuser un membre du projet
     * 
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function rejectMember($projectId, $userId)
    {
        return $this->db->execute(
            "UPDATE project_members SET status = 'rejected' WHERE project_id = ? AND user_id = ? AND status = 'pending'",
            [$projectId, $userId]
        );
    }

    /**
     * Rechercher des projets
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
                   COUNT(DISTINCT pm.id) as members_count
            FROM projects p
            INNER JOIN users u ON p.owner_id = u.id
            LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.status = 'active'
            WHERE p.visibility = 'public' AND (
                MATCH(p.title, p.description) AGAINST(? IN NATURAL LANGUAGE MODE)
                OR p.title LIKE ?
                OR p.description LIKE ?
            )
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT ?
        ";
        
        $searchTerm = "%{$search}%";
        return $this->db->query($query, [$search, $searchTerm, $searchTerm, $limit]);
    }
}

