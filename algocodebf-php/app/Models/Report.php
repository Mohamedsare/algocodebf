<?php
/**
 * Modèle Report - Gestion des signalements
 */

class Report extends Model
{
    protected $table = 'reports';

    /**
     * Créer un nouveau signalement
     * 
     * @param int $reporterId ID du rapporteur
     * @param string $type Type (post, comment, tutorial, message, user)
     * @param int $id ID de l'élément signalé
     * @param string $reason Raison du signalement
     * @return int|false ID du signalement créé ou false
     */
    public function createReport($reporterId, $type, $id, $reason)
    {
        return $this->create([
            'reporter_id' => $reporterId,
            'reportable_type' => $type,
            'reportable_id' => $id,
            'reason' => $reason,
            'status' => 'pending'
        ]);
    }

    /**
     * Obtenir tous les signalements avec détails
     * 
     * @param string $status Statut (optionnel)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getAllWithDetails($status = 'pending', $limit = 50, $offset = 0)
    {
        $statusFilter = $status ? "WHERE r.status = ?" : "";
        
        $query = "
            SELECT r.*, 
                   u.prenom as reporter_prenom, u.nom as reporter_nom
            FROM reports r
            INNER JOIN users u ON r.reporter_id = u.id
            {$statusFilter}
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params = $status ? [$status, $limit, $offset] : [$limit, $offset];
        return $this->db->query($query, $params);
    }

    /**
     * Mettre à jour le statut d'un signalement
     * 
     * @param int $reportId ID du signalement
     * @param string $status Nouveau statut
     * @param string $adminNote Note de l'admin
     * @return bool
     */
    public function updateStatus($reportId, $status, $adminNote = null)
    {
        $data = ['status' => $status];
        if ($adminNote) {
            $data['admin_note'] = $adminNote;
        }
        return $this->update($reportId, $data);
    }
}

