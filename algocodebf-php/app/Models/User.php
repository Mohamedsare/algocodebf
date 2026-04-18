<?php
/**
 * Modèle User - Gestion des utilisateurs
 */

class User extends Model
{
    protected $table = 'users';

    /**
     * Créer un nouvel utilisateur
     * 
     * @param array $data Données de l'utilisateur
     * @return int|false ID de l'utilisateur créé ou false
     */
    public function register($data)
    {
        // Hacher le mot de passe
        $data['password_hash'] = Security::hashPassword($data['password']);
        unset($data['password']);

        // Générer un token de vérification email
        $data['email_verification_token'] = Security::generateToken();
        
        // EN DÉVELOPPEMENT : email_verified = true (connexion immédiate)
        // EN PRODUCTION : changer à false pour forcer la vérification d'email
        $data['email_verified'] = true;  // Mettre à false en production

        // Ajouter le préfixe +226 si nécessaire
        if (substr($data['phone'], 0, 4) !== PHONE_PREFIX) {
            $data['phone'] = PHONE_PREFIX . $data['phone'];
        }

        return $this->create($data);
    }

    /**
     * Vérifier les identifiants de connexion
     * 
     * @param string $email Email
     * @param string $password Mot de passe
     * @return array|false Utilisateur ou false
     */
    public function login($email, $password)
    {
        $user = $this->findBy('email', $email);
        
        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            // Mettre à jour la date de dernière connexion
            $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            return $user;
        }
        
        return false;
    }

    /**
     * Vérifier l'email avec le token
     * 
     * @param string $token Token de vérification
     * @return bool
     */
    public function verifyEmail($token)
    {
        $user = $this->findBy('email_verification_token', $token);
        
        if ($user) {
            return $this->update($user['id'], [
                'email_verified' => true,
                'email_verification_token' => null
            ]);
        }
        
        return false;
    }

    /**
     * Obtenir le profil complet d'un utilisateur avec compétences et badges
     * 
     * @param int $userId ID de l'utilisateur
     * @return array|false
     */
    public function getProfile($userId)
    {
        $query = "
            SELECT u.*, 
                   COUNT(DISTINCT p.id) as posts_count,
                   COUNT(DISTINCT t.id) as tutorials_count,
                   COUNT(DISTINCT l.id) as likes_received
            FROM users u
            LEFT JOIN posts p ON u.id = p.user_id AND p.status = 'active'
            LEFT JOIN tutorials t ON u.id = t.user_id AND t.status = 'active'
            LEFT JOIN likes l ON (l.likeable_type = 'post' AND l.likeable_id IN (SELECT id FROM posts WHERE user_id = u.id))
                               OR (l.likeable_type = 'tutorial' AND l.likeable_id IN (SELECT id FROM tutorials WHERE user_id = u.id))
            WHERE u.id = ?
            GROUP BY u.id
        ";
        
        return $this->db->queryOne($query, [$userId]);
    }

    /**
     * Obtenir les compétences d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array
     */
    public function getSkills($userId)
    {
        $query = "
            SELECT s.id, s.name, s.category, us.level
            FROM skills s
            INNER JOIN user_skills us ON s.id = us.skill_id
            WHERE us.user_id = ?
            ORDER BY s.category, s.name
        ";
        
        return $this->db->query($query, [$userId]);
    }

    /**
     * Obtenir les badges d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array
     */
    public function getBadges($userId)
    {
        $query = "
            SELECT b.*, ub.awarded_at
            FROM badges b
            INNER JOIN user_badges ub ON b.id = ub.badge_id
            WHERE ub.user_id = ?
            ORDER BY ub.awarded_at DESC
        ";
        
        return $this->db->query($query, [$userId]);
    }

    /**
     * Rechercher des utilisateurs
     * 
     * @param string $search Terme de recherche
     * @param int $limit Limite de résultats
     * @return array
     */
    public function search($search, $limit = 20)
    {
        $query = "
            SELECT id, prenom, nom, email, photo_path, university, city, bio,
                   CONCAT(prenom, ' ', nom) as full_name
            FROM users
            WHERE status = 'active' AND (
                prenom LIKE ? OR 
                nom LIKE ? OR 
                CONCAT(prenom, ' ', nom) LIKE ? OR
                CONCAT(nom, ' ', prenom) LIKE ? OR
                email LIKE ? OR
                university LIKE ? OR
                city LIKE ? OR
                bio LIKE ?
            )
            LIMIT ?
        ";
        
        $searchTerm = "%{$search}%";
        return $this->db->query($query, [
            $searchTerm, // prenom
            $searchTerm, // nom
            $searchTerm, // prenom nom
            $searchTerm, // nom prenom
            $searchTerm, // email
            $searchTerm, // university
            $searchTerm, // city
            $searchTerm, // bio
            $limit
        ]);
    }

    /**
     * Obtenir le classement des utilisateurs
     * 
     * @param string $period Période (week, month, all)
     * @param int $limit Limite de résultats
     * @return array
     */
    public function getLeaderboard($period = 'month', $limit = 50)
    {
        // Construire le filtre de date selon la période
        $dateFilterPosts = '';
        $dateFilterTutorials = '';
        $dateFilterComments = '';
        $dateFilterLikes = '';
        
        if ($period === 'week') {
            $dateFilterPosts = "AND p.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            $dateFilterTutorials = "AND t.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            $dateFilterComments = "AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            $dateFilterLikes = "AND l.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        } elseif ($period === 'month') {
            $dateFilterPosts = "AND p.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            $dateFilterTutorials = "AND t.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            $dateFilterComments = "AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            $dateFilterLikes = "AND l.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        }
        // Pour 'all', pas de filtre de date

        $query = "
            SELECT 
                u.id, 
                u.prenom, 
                u.nom, 
                u.photo_path, 
                u.university, 
                u.city,
                COALESCE(posts_stats.posts_count, 0) as posts_count,
                COALESCE(tutorials_stats.tutorials_count, 0) as tutorials_count,
                COALESCE(comments_stats.comments_count, 0) as comments_count,
                COALESCE(likes_stats.likes_received, 0) as likes_received,
                (
                    COALESCE(posts_stats.posts_count, 0) * 5 + 
                    COALESCE(tutorials_stats.tutorials_count, 0) * 10 + 
                    COALESCE(comments_stats.comments_count, 0) * 2 + 
                    COALESCE(likes_stats.likes_received, 0) * 1
                ) as score
            FROM users u
            LEFT JOIN (
                SELECT user_id, COUNT(*) as posts_count
                FROM posts p
                WHERE p.status = 'active' {$dateFilterPosts}
                GROUP BY user_id
            ) posts_stats ON u.id = posts_stats.user_id
            LEFT JOIN (
                SELECT user_id, COUNT(*) as tutorials_count
                FROM tutorials t
                WHERE t.status = 'active' {$dateFilterTutorials}
                GROUP BY user_id
            ) tutorials_stats ON u.id = tutorials_stats.user_id
            LEFT JOIN (
                SELECT user_id, COUNT(*) as comments_count
                FROM comments c
                WHERE c.status = 'active' {$dateFilterComments}
                GROUP BY user_id
            ) comments_stats ON u.id = comments_stats.user_id
            LEFT JOIN (
                SELECT 
                    user_id,
                    SUM(likes_count) as likes_received
                FROM (
                    SELECT 
                        p.user_id,
                        COUNT(DISTINCT l.id) as likes_count
                    FROM likes l
                    INNER JOIN posts p ON l.likeable_type = 'post' AND l.likeable_id = p.id AND p.status = 'active'
                    WHERE 1=1 {$dateFilterLikes}
                    GROUP BY p.user_id
                    UNION ALL
                    SELECT 
                        t.user_id,
                        COUNT(DISTINCT l.id) as likes_count
                    FROM likes l
                    INNER JOIN tutorials t ON l.likeable_type = 'tutorial' AND l.likeable_id = t.id AND t.status = 'active'
                    WHERE 1=1 {$dateFilterLikes}
                    GROUP BY t.user_id
                ) combined_likes
                GROUP BY user_id
            ) likes_stats ON u.id = likes_stats.user_id
            WHERE u.status = 'active' AND u.role IN ('user', 'company', 'admin')
            GROUP BY u.id
            HAVING score > 0
            ORDER BY score DESC, u.created_at ASC
            LIMIT ?
        ";
        
        return $this->db->query($query, [$limit]);
    }

    /**
     * Initier une réinitialisation de mot de passe
     * 
     * @param string $email Email de l'utilisateur
     * @return array|false Token et utilisateur ou false
     */
    public function initiatePasswordReset($email)
    {
        $user = $this->findBy('email', $email);
        
        if ($user) {
            $token = Security::generateToken();
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->update($user['id'], [
                'reset_token' => $token,
                'reset_token_expires' => $expires
            ]);
            
            return ['token' => $token, 'user' => $user];
        }
        
        return false;
    }

    /**
     * Réinitialiser le mot de passe avec un token
     * 
     * @param string $token Token de réinitialisation
     * @param string $newPassword Nouveau mot de passe
     * @return bool
     */
    public function resetPassword($token, $newPassword)
    {
        $user = $this->findBy('reset_token', $token);
        
        if ($user && strtotime($user['reset_token_expires']) > time()) {
            return $this->update($user['id'], [
                'password_hash' => Security::hashPassword($newPassword),
                'reset_token' => null,
                'reset_token_expires' => null
            ]);
        }
        
        return false;
    }

    /**
     * Vérifier si un email existe déjà
     * 
     * @param string $email Email à vérifier
     * @return bool
     */
    public function emailExists($email)
    {
        return $this->findBy('email', $email) !== false;
    }

    /**
     * Vérifier si un pseudo existe déjà
     * 
     * @param string $pseudo Pseudo à vérifier
     * @return bool
     */
    public function pseudoExists($pseudo)
    {
        return $this->findBy('pseudo', $pseudo) !== false;
    }

    /**
     * Rechercher des utilisateurs pour la messagerie (API)
     * Exclut l'utilisateur actuel et retourne un format optimisé
     * 
     * @param string $query Terme de recherche
     * @param int $currentUserId ID de l'utilisateur actuel (à exclure)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function searchUsers($query, $currentUserId, $limit = 10, $offset = 0)
    {
        $sql = "
            SELECT 
                id, 
                prenom, 
                nom, 
                email, 
                photo_path, 
                university, 
                city, 
                role,
                CONCAT(prenom, ' ', nom) as full_name
            FROM users
            WHERE 
                status = 'active' 
                AND id != ?
                AND (
                    prenom LIKE ? OR 
                    nom LIKE ? OR 
                    CONCAT(prenom, ' ', nom) LIKE ? OR
                    CONCAT(nom, ' ', prenom) LIKE ? OR
                    email LIKE ? OR
                    university LIKE ?
                )
            ORDER BY 
                CASE 
                    WHEN CONCAT(prenom, ' ', nom) LIKE ? THEN 1
                    WHEN prenom LIKE ? THEN 2
                    WHEN nom LIKE ? THEN 3
                    ELSE 4
                END,
                prenom ASC
            LIMIT ? OFFSET ?
        ";
        
        $searchTerm = "%{$query}%";
        $exactSearchStart = "{$query}%";
        
        return $this->db->query($sql, [
            $currentUserId,
            $searchTerm,  // prenom LIKE
            $searchTerm,  // nom LIKE
            $searchTerm,  // CONCAT prenom nom LIKE
            $searchTerm,  // CONCAT nom prenom LIKE
            $searchTerm,  // email LIKE
            $searchTerm,  // university LIKE
            $exactSearchStart, // ORDER BY - full name exact start
            $exactSearchStart, // ORDER BY - prenom exact start
            $exactSearchStart, // ORDER BY - nom exact start
            $limit,
            $offset
        ]);
    }

    /**
     * Mettre à jour les compétences d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $skills Tableau de ['skill_id' => 'level']
     * @return bool
     */
    public function updateSkills($userId, $skills)
    {
        // Supprimer les anciennes compétences
        $this->db->execute("DELETE FROM user_skills WHERE user_id = ?", [$userId]);
        
        // Ajouter les nouvelles
        foreach ($skills as $skillId => $level) {
            $this->db->execute(
                "INSERT INTO user_skills (user_id, skill_id, level) VALUES (?, ?, ?)",
                [$userId, $skillId, $level]
            );
        }
        
        return true;
    }

    /**
     * Vérifier si un utilisateur peut créer des tutoriels
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function canCreateTutorial($userId)
    {
        $user = $this->findById($userId);
        
        if (!$user) {
            return false;
        }
        
        // Les admins peuvent toujours créer
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Vérifier la permission spécifique
        return (bool)($user['can_create_tutorial'] ?? false);
    }

    /**
     * Vérifier si un utilisateur peut créer des projets
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function canCreateProject($userId)
    {
        $user = $this->findById($userId);
        
        if (!$user) {
            return false;
        }
        
        // Les admins peuvent toujours créer
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Vérifier la permission spécifique
        return (bool)($user['can_create_project'] ?? false);
    }

    /**
     * Accorder la permission de créer des tutoriels à un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function grantTutorialPermission($userId)
    {
        return $this->update($userId, ['can_create_tutorial' => 1]);
    }

    /**
     * Retirer la permission de créer des tutoriels à un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function revokeTutorialPermission($userId)
    {
        return $this->update($userId, ['can_create_tutorial' => 0]);
    }

    /**
     * Accorder la permission de créer des projets à un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function grantProjectPermission($userId)
    {
        return $this->update($userId, ['can_create_project' => 1]);
    }

    /**
     * Retirer la permission de créer des projets à un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function revokeProjectPermission($userId)
    {
        return $this->update($userId, ['can_create_project' => 0]);
    }

    /**
     * Vérifier si un utilisateur est administrateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function isAdmin($userId)
    {
        $user = $this->findById($userId);
        return $user && $user['role'] === 'admin';
    }
}

