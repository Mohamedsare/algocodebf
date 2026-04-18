<?php
/**
 * Script d'installation des tables analytics
 * À exécuter une seule fois via : http://localhost/AlgoCodeBF/public/install_analytics.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../app/Core/Database.php';

try {
    $db = Database::getInstance();
    
    $sql = file_get_contents(__DIR__ . '/../database/add_analytics_tables.sql');
    
    if ($sql === false) {
        throw new Exception("Impossible de lire le fichier SQL");
    }
    
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->execute($statement);
        }
    }
    
    echo "✅ Tables d'analytics créées avec succès !<br>";
    echo "✅ visitor_logs<br>";
    echo "✅ user_activities<br>";
    echo "✅ online_users<br><br>";
    echo "🎉 Installation terminée ! Vous pouvez maintenant supprimer ce fichier.";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
    error_log("Erreur installation analytics: " . $e->getMessage());
}

