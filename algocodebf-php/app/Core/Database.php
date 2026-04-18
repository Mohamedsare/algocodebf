<?php
/**
 * Classe Database - Gestion de la connexion à la base de données
 * Utilise PDO avec des requêtes préparées pour la sécurité
 */

class Database
{
    private static $instance = null;
    private $pdo;

    /**
     * Constructeur privé pour le pattern Singleton
     */
    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    /**
     * Obtenir l'instance unique de Database (Singleton)
     * 
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtenir la connexion PDO
     * 
     * @return PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Exécuter une requête SELECT
     * 
     * @param string $query Requête SQL
     * @param array $params Paramètres de la requête
     * @return array
     */
    public function query($query, $params = [])
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Exécuter une requête SELECT et retourner une seule ligne
     * 
     * @param string $query Requête SQL
     * @param array $params Paramètres de la requête
     * @return array|false
     */
    public function queryOne($query, $params = [])
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Exécuter une requête INSERT, UPDATE ou DELETE
     * 
     * @param string $query Requête SQL
     * @param array $params Paramètres de la requête
     * @return bool
     */
    public function execute($query, $params = [])
    {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Obtenir le dernier ID inséré
     * 
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Empêcher le clonage de l'instance
     */
    private function __clone() {}

    /**
     * Empêcher la désérialisation de l'instance
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}