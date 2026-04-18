<?php
/**
 * Modèle Message - Gestion de la messagerie privée
 */

class Message extends Model
{
    protected $table = 'messages';

    /**
     * Envoyer un message
     * 
     * @param int $senderId ID de l'expéditeur
     * @param int $receiverId ID du destinataire
     * @param string $subject Sujet
     * @param string $body Corps du message
     * @return int|false ID du message créé ou false
     */
    public function send($senderId, $receiverId, $subject, $body)
    {
        return $this->create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Envoyer un message avec des actions (pour les demandes)
     * 
     * @param int $senderId ID de l'expéditeur
     * @param int $receiverId ID du destinataire
     * @param string $subject Sujet
     * @param string $body Corps du message
     * @param string $actionType Type d'action (ex: 'project_join_request')
     * @param array $actionData Données pour l'action (ex: project_id, user_id, role)
     * @return int|false ID du message créé ou false
     */
    public function sendWithActions($senderId, $receiverId, $subject, $body, $actionType, $actionData)
    {
        return $this->create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'body' => $body,
            'action_type' => $actionType,
            'action_data' => json_encode($actionData),
            'action_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mettre à jour le statut d'une action
     * 
     * @param int $messageId ID du message
     * @param string $status Statut ('accepted', 'rejected', 'cancelled')
     * @return bool
     */
    public function updateActionStatus($messageId, $status)
    {
        return $this->update($messageId, ['action_status' => $status]);
    }

    /**
     * Obtenir les messages reçus
     * 
     * @param int $userId ID de l'utilisateur
     * @param bool $unreadOnly Messages non lus uniquement
     * @return array
     */
    public function getInbox($userId, $unreadOnly = false)
    {
        $unreadFilter = $unreadOnly ? "AND m.is_read = FALSE" : "";
        
        $query = "
            SELECT m.*, 
                   u.id as sender_user_id, 
                   CONCAT(u.prenom, ' ', u.nom) as sender_name,
                   u.email as sender_email,
                   u.photo_path as sender_photo
            FROM messages m
            INNER JOIN users u ON m.sender_id = u.id
            WHERE m.receiver_id = ? AND m.is_deleted_by_receiver = FALSE {$unreadFilter}
            ORDER BY m.created_at DESC
        ";
        
        return $this->db->query($query, [$userId]);
    }

    /**
     * Obtenir les messages envoyés
     * 
     * @param int $userId ID de l'utilisateur
     * @param bool $excludeSystemMessages Exclure les messages système automatiques
     * @return array
     */
    public function getSent($userId, $excludeSystemMessages = false)
    {
        $systemFilter = $excludeSystemMessages ? "AND m.action_type IS NULL" : "";
        
        $query = "
            SELECT m.*, 
                   u.id as receiver_user_id, 
                   CONCAT(u.prenom, ' ', u.nom) as receiver_name,
                   u.email as receiver_email,
                   u.photo_path as receiver_photo
            FROM messages m
            INNER JOIN users u ON m.receiver_id = u.id
            WHERE m.sender_id = ? AND m.is_deleted_by_sender = FALSE {$systemFilter}
            ORDER BY m.created_at DESC
        ";
        
        return $this->db->query($query, [$userId]);
    }

    /**
     * Obtenir une conversation entre deux utilisateurs
     * 
     * @param int $userId1 ID du premier utilisateur
     * @param int $userId2 ID du deuxième utilisateur
     * @return array
     */
    public function getConversation($userId1, $userId2)
    {
        $query = "
            SELECT m.*, 
                   sender.id as sender_user_id, sender.prenom as sender_prenom, 
                   sender.nom as sender_nom, sender.photo_path as sender_photo,
                   receiver.id as receiver_user_id, receiver.prenom as receiver_prenom, 
                   receiver.nom as receiver_nom, receiver.photo_path as receiver_photo
            FROM messages m
            INNER JOIN users sender ON m.sender_id = sender.id
            INNER JOIN users receiver ON m.receiver_id = receiver.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ";
        
        return $this->db->query($query, [$userId1, $userId2, $userId2, $userId1]);
    }

    /**
     * Marquer un message comme lu
     * 
     * @param int $messageId ID du message
     * @return bool
     */
    public function markAsRead($messageId)
    {
        return $this->update($messageId, ['is_read' => true]);
    }

    /**
     * Marquer tous les messages d'un utilisateur comme lus
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function markAllAsRead($userId)
    {
        return $this->db->execute(
            "UPDATE messages SET is_read = TRUE WHERE receiver_id = ? AND is_read = FALSE",
            [$userId]
        );
    }

    /**
     * Supprimer un message (pour un utilisateur)
     * 
     * @param int $messageId ID du message
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function deleteForUser($messageId, $userId)
    {
        $message = $this->findById($messageId);
        
        if (!$message) {
            return false;
        }
        
        if ($message['sender_id'] == $userId) {
            return $this->update($messageId, ['is_deleted_by_sender' => true]);
        } elseif ($message['receiver_id'] == $userId) {
            return $this->update($messageId, ['is_deleted_by_receiver' => true]);
        }
        
        return false;
    }

    /**
     * Compter les messages non lus
     * 
     * @param int $userId ID de l'utilisateur
     * @return int
     */
    public function countUnread($userId)
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = FALSE AND is_deleted_by_receiver = FALSE",
            [$userId]
        );
        
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Obtenir les contacts récents
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $limit Limite de résultats
     * @return array
     */
    public function getRecentContacts($userId, $limit = 10)
    {
        $query = "
            SELECT DISTINCT u.id, u.prenom, u.nom, u.photo_path,
                   MAX(m.created_at) as last_message_date
            FROM users u
            INNER JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
            WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
            GROUP BY u.id
            ORDER BY last_message_date DESC
            LIMIT ?
        ";
        
        return $this->db->query($query, [$userId, $userId, $userId, $limit]);
    }
}

