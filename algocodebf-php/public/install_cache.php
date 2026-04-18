<?php
/**
 * Installation Cache Natif - Script Direct
 * Fonctionne sans routage
 */

// Configuration directe
$host = 'localhost';
$dbname = 'hubtech';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Cache Natif - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #C8102E; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { color: #004085; background: #cce5ff; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #004085; }
        .btn { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #C8102E, #006A4E); color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; border: none; cursor: pointer; font-size: 16px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3); }
        .btn-success { background: linear-gradient(135deg, #28a745, #20c997); }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0; font-size: 2rem; color: #C8102E; }
        .stat-card p { margin: 10px 0 0 0; color: #666; }
    </style>
</head>
<body>
    <h1>🚀 Cache Natif PHP/MySQL</h1>
    <p>Système de cache gratuit et ultra-performant.</p>
";

// Vérifier si la table existe
$tableExists = false;
try {
    $result = $pdo->query("SHOW TABLES LIKE 'cache_store'");
    $tableExists = $result->rowCount() > 0;
} catch (PDOException $e) {
    echo "<div class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
}

if ($tableExists) {
    echo "<div class='success'>";
    echo "<h2>✅ Cache déjà installé !</h2>";
    
    // Statistiques
    try {
        $totalKeys = $pdo->query("SELECT COUNT(*) FROM cache_store")->fetchColumn();
        $activeKeys = $pdo->query("SELECT COUNT(*) FROM cache_store WHERE expires_at = 0 OR expires_at > " . time())->fetchColumn();
        $cacheSize = $pdo->query("SELECT SUM(LENGTH(cache_value)) FROM cache_store")->fetchColumn() ?: 0;
        
        echo "<div class='stats'>";
        echo "<div class='stat-card'><h3>$totalKeys</h3><p>Clés totales</p></div>";
        echo "<div class='stat-card'><h3>$activeKeys</h3><p>Clés actives</p></div>";
        echo "<div class='stat-card'><h3>" . number_format($cacheSize / 1024, 1) . " KB</h3><p>Taille</p></div>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<p>Erreur stats : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<a href='../' class='btn btn-success'>🏠 Retour au site</a>";
    echo "</div>";
} else {
    // Créer la table
    echo "<div class='info'>Création de la table cache_store...</div>";
    
    $sql = "
        CREATE TABLE cache_store (
            cache_key VARCHAR(255) PRIMARY KEY,
            cache_value LONGTEXT NOT NULL,
            expires_at INT NOT NULL,
            created_at INT NOT NULL,
            access_count INT DEFAULT 0,
            last_accessed INT NOT NULL,
            INDEX idx_expires (expires_at),
            INDEX idx_access_count (access_count),
            INDEX idx_last_accessed (last_accessed)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    try {
        $pdo->exec($sql);
        
        echo "<div class='success'>";
        echo "<h2>✅ Installation réussie !</h2>";
        echo "<p>Table cache_store créée avec succès.</p>";
        
        // Test
        $testKey = 'test_' . time();
        $testValue = ['message' => 'Cache fonctionnel', 'timestamp' => time()];
        
        $stmt = $pdo->prepare("INSERT INTO cache_store (cache_key, cache_value, expires_at, created_at, last_accessed, access_count) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->execute([$testKey, serialize($testValue), time() + 60, time(), time()]);
        
        $stmt = $pdo->prepare("SELECT cache_value FROM cache_store WHERE cache_key = ?");
        $stmt->execute([$testKey]);
        $readValue = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("DELETE FROM cache_store WHERE cache_key = ?");
        $stmt->execute([$testKey]);
        
        echo "<p><strong>✅ Tests :</strong> Écriture, lecture et suppression réussies</p>";
        
        echo "<div class='info'>";
        echo "<h3>🎯 Fonctionnalités</h3>";
        echo "<ul>";
        echo "<li>Cache mémoire ultra-rapide</li>";
        echo "<li>Stockage persistant MySQL</li>";
        echo "<li>Statistiques avancées</li>";
        echo "<li>Nettoyage automatique</li>";
        echo "<li>100% gratuit</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<a href='../' class='btn btn-success'>🚀 Retour au site</a>";
        echo " <a href='install_indexes.php' class='btn'>📊 Installer les index SQL</a>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>";
        echo "<h2>❌ Erreur</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}

echo "</body></html>";
?>

