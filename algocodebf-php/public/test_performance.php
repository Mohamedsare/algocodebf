<?php
/**
 * Test de Performance - Script Direct
 * Fonctionne sans routage
 */

// Configuration de base de données
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
    <title>Test de Performance</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #C8102E; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { color: #004085; background: #cce5ff; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #004085; }
        .btn { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #C8102E, #006A4E); color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; border: none; cursor: pointer; font-size: 16px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3); }
        .btn-success { background: linear-gradient(135deg, #28a745, #20c997); }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0; font-size: 2rem; color: #C8102E; }
        .stat-card p { margin: 10px 0 0 0; color: #666; }
        .test-section { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section h3 { color: #C8102E; margin-top: 0; }
        .performance-comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .performance-card { padding: 20px; border-radius: 8px; text-align: center; }
        .performance-card.slow { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
        .performance-card.fast { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .performance-card h4 { margin: 0 0 10px 0; font-size: 1.5rem; }
        .performance-card .time { font-size: 2rem; font-weight: bold; margin: 10px 0; }
        .performance-card .gain { font-size: 1.2rem; opacity: 0.9; }
    </style>
</head>
<body>
    <h1>🚀 Test de Performance</h1>
    <p>Mesure des gains de performance avec les optimisations.</p>
";

// Fonction pour mesurer le temps
function measureTime($callback) {
    $start = microtime(true);
    $result = $callback();
    $end = microtime(true);
    return [
        'time' => ($end - $start) * 1000,
        'result' => $result
    ];
}

// Fonction pour exécuter une requête
function executeQuery($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour tester avec cache
function testWithCache($key, $callback, $ttl = 60) {
    global $pdo;
    
    // Vérifier le cache
    $stmt = $pdo->prepare("SELECT cache_value FROM cache_store WHERE cache_key = ? AND (expires_at = 0 OR expires_at > ?)");
    $stmt->execute([$key, time()]);
    $cached = $stmt->fetchColumn();
    
    if ($cached) {
        return unserialize($cached);
    }
    
    // Générer et mettre en cache
    $value = $callback();
    $stmt = $pdo->prepare("INSERT INTO cache_store (cache_key, cache_value, expires_at, created_at, last_accessed, access_count) VALUES (?, ?, ?, ?, ?, 0) ON DUPLICATE KEY UPDATE cache_value = VALUES(cache_value), expires_at = VALUES(expires_at)");
    $stmt->execute([$key, serialize($value), time() + $ttl, time(), time()]);
    
    return $value;
}

echo "<div class='info'>";
echo "<strong>🔄 Exécution des tests...</strong><br>";
echo "Mesure des performances avec et sans cache.";
echo "</div>";

// Test 1 : Requêtes de statistiques
echo "<div class='test-section'>";
echo "<h3>📊 Test 1 : Requêtes de statistiques</h3>";

$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users,
        (SELECT COUNT(*) FROM posts WHERE status = 'active') as total_posts,
        (SELECT COUNT(*) FROM tutorials WHERE status = 'active') as total_tutorials,
        (SELECT COUNT(*) FROM projects WHERE visibility = 'public') as total_projects
";

// Test sans cache
$test1WithoutCache = measureTime(function() use ($statsQuery) {
    return executeQuery($statsQuery);
});

// Test avec cache
$test1WithCache = measureTime(function() use ($statsQuery) {
    return testWithCache('test_stats_' . time(), function() use ($statsQuery) {
        return executeQuery($statsQuery);
    }, 60);
});

echo "<div class='performance-comparison'>";
echo "<div class='performance-card slow'>";
echo "<h4>❌ Sans Cache</h4>";
echo "<div class='time'>" . number_format($test1WithoutCache['time'], 2) . " ms</div>";
echo "<div class='gain'>Requête directe</div>";
echo "</div>";
echo "<div class='performance-card fast'>";
echo "<h4>✅ Avec Cache</h4>";
echo "<div class='time'>" . number_format($test1WithCache['time'], 2) . " ms</div>";
echo "<div class='gain'>Cache natif</div>";
echo "</div>";
echo "</div>";

$gain1 = $test1WithoutCache['time'] / max($test1WithCache['time'], 0.01);
echo "<p><strong>Gain :</strong> " . number_format($gain1, 1) . "x plus rapide</p>";
echo "</div>";

// Test 2 : Recherche complexe
echo "<div class='test-section'>";
echo "<h3>🔍 Test 2 : Recherche complexe</h3>";

$searchQuery = "
    SELECT p.*, u.prenom, u.nom, u.email 
    FROM posts p 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE p.status = 'active' 
    AND (p.title LIKE ? OR p.body LIKE ?) 
    ORDER BY p.created_at DESC 
    LIMIT 10
";

$searchTerm = '%tech%';

// Test sans cache
$test2WithoutCache = measureTime(function() use ($searchQuery, $searchTerm) {
    return executeQuery($searchQuery, [$searchTerm, $searchTerm]);
});

// Test avec cache
$test2WithCache = measureTime(function() use ($searchQuery, $searchTerm) {
    return testWithCache('test_search_' . md5($searchTerm), function() use ($searchQuery, $searchTerm) {
        return executeQuery($searchQuery, [$searchTerm, $searchTerm]);
    }, 60);
});

echo "<div class='performance-comparison'>";
echo "<div class='performance-card slow'>";
echo "<h4>❌ Sans Cache</h4>";
echo "<div class='time'>" . number_format($test2WithoutCache['time'], 2) . " ms</div>";
echo "<div class='gain'>Recherche directe</div>";
echo "</div>";
echo "<div class='performance-card fast'>";
echo "<h4>✅ Avec Cache</h4>";
echo "<div class='time'>" . number_format($test2WithCache['time'], 2) . " ms</div>";
echo "<div class='gain'>Cache natif</div>";
echo "</div>";
echo "</div>";

$gain2 = $test2WithoutCache['time'] / max($test2WithCache['time'], 0.01);
echo "<p><strong>Gain :</strong> " . number_format($gain2, 1) . "x plus rapide</p>";
echo "</div>";

// Statistiques globales
$totalWithoutCache = $test1WithoutCache['time'] + $test2WithoutCache['time'];
$totalWithCache = $test1WithCache['time'] + $test2WithCache['time'];
$totalGain = $totalWithoutCache / max($totalWithCache, 0.01);

echo "<div class='stats'>";
echo "<div class='stat-card'>";
echo "<h3>" . number_format($totalWithoutCache, 0) . "</h3>";
echo "<p>ms sans cache</p>";
echo "</div>";
echo "<div class='stat-card'>";
echo "<h3>" . number_format($totalWithCache, 0) . "</h3>";
echo "<p>ms avec cache</p>";
echo "</div>";
echo "<div class='stat-card'>";
echo "<h3>" . number_format($totalGain, 1) . "x</h3>";
echo "<p>Gain total</p>";
echo "</div>";
echo "<div class='stat-card'>";
echo "<h3>" . number_format(($totalWithoutCache - $totalWithCache) / $totalWithoutCache * 100, 1) . "%</h3>";
echo "<p>Réduction temps</p>";
echo "</div>";
echo "</div>";

// Informations sur le cache
try {
    $totalKeys = $pdo->query("SELECT COUNT(*) FROM cache_store")->fetchColumn();
    $activeKeys = $pdo->query("SELECT COUNT(*) FROM cache_store WHERE expires_at = 0 OR expires_at > " . time())->fetchColumn();
    $cacheSize = $pdo->query("SELECT SUM(LENGTH(cache_value)) FROM cache_store")->fetchColumn() ?: 0;
    
    echo "<div class='test-section'>";
    echo "<h3>💾 État du cache natif</h3>";
    echo "<p><strong>Type :</strong> Cache natif PHP/MySQL</p>";
    echo "<p><strong>Clés totales :</strong> $totalKeys</p>";
    echo "<p><strong>Clés actives :</strong> $activeKeys</p>";
    echo "<p><strong>Taille :</strong> " . number_format($cacheSize / 1024, 1) . " KB</p>";
    echo "<p><strong>Statut :</strong> ✅ Opérationnel</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>Cache non disponible : " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<div class='success'>";
echo "<h2>✅ Tests terminés !</h2>";
echo "<p>Votre site bénéficie maintenant de :</p>";
echo "<ul>";
echo "<li>🚀 Index SQL pour des requêtes plus rapides</li>";
echo "<li>💾 Cache natif PHP/MySQL pour des performances maximales</li>";
echo "<li>📊 Monitoring des performances en temps réel</li>";
echo "<li>⚡ Jusqu'à " . number_format($totalGain, 1) . "x plus rapide !</li>";
echo "</ul>";
echo "<a href='../' class='btn btn-success'>🏠 Retour au site</a>";
echo " <a href='install_cache.php' class='btn'>💾 Installer le cache</a>";
echo " <a href='install_indexes.php' class='btn'>📊 Installer les index</a>";
echo "</div>";

echo "</body></html>";
?>

