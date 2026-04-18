<?php
/**
 * Modèle de base dont tous les modèles héritent
 * Fournit des méthodes communes pour interagir avec la base de données
 */

class Model
{
    protected $db;
    protected $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupérer tous les enregistrements
     * 
     * @param string $orderBy Colonne de tri
     * @param string $order Ordre de tri (ASC/DESC)
     * @return array
     */
    public function findAll($orderBy = 'id', $order = 'DESC')
    {
        $query = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}";
        return $this->db->query($query);
    }

    /**
     * Récupérer un enregistrement par ID
     * 
     * @param int $id ID de l'enregistrement
     * @return array|false
     */
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->queryOne($query, [$id]);
    }

    /**
     * Récupérer un enregistrement selon une condition
     * 
     * @param string $column Colonne de recherche
     * @param mixed $value Valeur recherchée
     * @return array|false
     */
    public function findBy($column, $value)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return $this->db->queryOne($query, [$value]);
    }

    /**
     * Récupérer plusieurs enregistrements selon une condition
     * 
     * @param string $column Colonne de recherche
     * @param mixed $value Valeur recherchée
     * @return array
     */
    public function findAllBy($column, $value)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return $this->db->query($query, [$value]);
    }

    /**
     * Créer un nouvel enregistrement
     * 
     * @param array $data Données à insérer
     * @return bool|int ID du nouvel enregistrement ou false
     */
    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        // Préparer les paramètres
        $params = [];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }

        if ($this->db->execute($query, $params)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Mettre à jour un enregistrement
     * 
     * @param int $id ID de l'enregistrement
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function update($id, $data)
    {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        
        $query = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        
        // Préparer les paramètres
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }

        return $this->db->execute($query, $params);
    }

    /**
     * Supprimer un enregistrement
     * 
     * @param int $id ID de l'enregistrement
     * @return bool
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($query, [$id]);
    }

    /**
     * Compter les enregistrements
     * 
     * @param string $column Colonne pour la condition (optionnel)
     * @param mixed $value Valeur pour la condition (optionnel)
     * @return int
     */
    public function count($column = null, $value = null)
    {
        if ($column && $value) {
            $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$column} = ?";
            $result = $this->db->queryOne($query, [$value]);
        } else {
            $query = "SELECT COUNT(*) as total FROM {$this->table}";
            $result = $this->db->queryOne($query);
        }
        return $result ? (int)$result['total'] : 0;
    }
}

