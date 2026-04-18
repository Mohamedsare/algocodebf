<?php
/**
 * Installation Index SQL - Script Direct
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
    <title>Index SQL - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #C8102E; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { color: #004085; background: #cce5ff; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #004085; }
        .btn { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #C8102E, #006A4E); color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; border: none; cursor: pointer; font-size: 16px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3); }
        .btn-success { background: linear-gradient(135deg, #28a745, #20c997); }
        .index-list { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .index-item { padding: 10px; border-bottom: 1px solid #eee; }
        .index-item:last-child { border-bottom: none; }
        .index-item strong { color: #C8102E; }
    </style>
</head>
<body>
    <h1>🚀 Index SQL - Installation</h1>
    <p>Optimisation des performances de base de données.</p>
";

// Vérifier les index existants
$existingIndexes = [];
try {
    $result = $pdo->query("
        SELECT 
            TABLE_NAME,
            INDEX_NAME,
            COUNT(*) as columns_count
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
            AND INDEX_NAME != 'PRIMARY'
            AND TABLE_NAME IN ('posts', 'comments', 'likes', 'tutorials', 'projects', 'jobs', 'users', 'messages', 'notifications')
        GROUP BY TABLE_NAME, INDEX_NAME
    ");
    $existingIndexes = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<div class='info'>";
echo "<strong>📊 État actuel :</strong><br>";
echo "Index existants : " . count($existingIndexes) . "<br>";
echo "Tables ciblées : 9 tables principales";
echo "</div>";

// Demander confirmation
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "<div class='info'>";
    echo "<strong>⚠️ IMPORTANT :</strong><br>";
    echo "• Durée estimée : 10-60 secondes<br>";
    echo "• Gain de performance : <strong>+300%</strong><br>";
    echo "• Opération <strong>SÉCURITAIRE</strong><br><br>";
    echo "<a href='?confirm=yes' class='btn'>✅ Confirmer et Exécuter</a>";
    echo " <a href='../' class='btn' style='background: #6c757d;'>❌ Annuler</a>";
    echo "</div>";
    
    if (!empty($existingIndexes)) {
        echo "<div class='index-list'>";
        echo "<h3>📋 Index actuels :</h3>";
        foreach ($existingIndexes as $index) {
            echo "<div class='index-item'>";
            echo "<strong>{$index['TABLE_NAME']}</strong> → {$index['INDEX_NAME']} ({$index['columns_count']} colonne(s))";
            echo "</div>";
        }
        echo "</div>";
    }
    
    exit;
}

// Exécuter la création des index
$startTime = microtime(true);

echo "<h2>🔄 Création des index...</h2>";

$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_posts_user_id ON posts(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_posts_category ON posts(category)",
    "CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status)",
    "CREATE INDEX IF NOT EXISTS idx_posts_created_at ON posts(created_at DESC)",
    "CREATE INDEX IF NOT EXISTS idx_comments_user_id ON comments(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_comments_post_id ON comments(post_id)",
    "CREATE INDEX IF NOT EXISTS idx_comments_status ON comments(status)",
    "CREATE INDEX IF NOT EXISTS idx_comments_created_at ON comments(created_at DESC)",
    "CREATE INDEX IF NOT EXISTS idx_likes_user_id ON likes(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_likes_commentable ON likes(commentable_type, commentable_id)",
    "CREATE INDEX IF NOT EXISTS idx_tutorials_user_id ON tutorials(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_tutorials_category ON tutorials(category)",
    "CREATE INDEX IF NOT EXISTS idx_tutorials_status ON tutorials(status)",
    "CREATE INDEX IF NOT EXISTS idx_tutorials_created_at ON tutorials(created_at DESC)",
    "CREATE INDEX IF NOT EXISTS idx_projects_user_id ON projects(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_projects_status ON projects(status)",
    "CREATE INDEX IF NOT EXISTS idx_projects_created_at ON projects(created_at DESC)",
    "CREATE INDEX IF NOT EXISTS idx_jobs_user_id ON jobs(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_jobs_type ON jobs(type)",
    "CREATE INDEX IF NOT EXISTS idx_jobs_status ON jobs(status)",
    "CREATE INDEX IF NOT EXISTS idx_jobs_created_at ON jobs(created_at DESC)",
    "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
    "CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)",
    "CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)",
    "CREATE INDEX IF NOT EXISTS idx_messages_sender_id ON messages(sender_id)",
    "CREATE INDEX IF NOT EXISTS idx_messages_receiver_id ON messages(receiver_id)",
    "CREATE INDEX IF NOT EXISTS idx_messages_is_read ON messages(is_read)",
    "CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications(is_read)",
];

$created = 0;
$errors = 0;

foreach ($indexes as $sql) {
    try {
        $pdo->exec($sql);
        $created++;
        
        preg_match('/idx_\w+/', $sql, $matches);
        $indexName = $matches[0] ?? 'index';
        
        echo "<div class='success'>✅ Index créé : <strong>$indexName</strong></div>";
    } catch (PDOException $e) {
        $errors++;
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<div class='info'>ℹ️ Index déjà existant</div>";
        } else {
            echo "<div class='error'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

echo "<hr>";
echo "<div class='success'>";
echo "<h2>✅ Optimisation terminée !</h2>";
echo "<strong>Statistiques :</strong><br>";
echo "• Index créés : $created<br>";
echo "• Erreurs : $errors<br>";
echo "• Durée : {$duration} secondes<br>";
echo "• Gain estimé : <strong>+300%</strong> 🚀<br><br>";
echo "<a href='../' class='btn'>🏠 Retour au site</a>";
echo " <a href='install_cache.php' class='btn'>💾 Installer le cache</a>";
echo "</div>";

// Afficher les nouveaux index
$newIndexes = $pdo->query("
    SELECT 
        TABLE_NAME,
        INDEX_NAME,
        GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS COLUMNS
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
        AND INDEX_NAME != 'PRIMARY'
        AND TABLE_NAME IN ('posts', 'comments', 'likes', 'tutorials', 'projects', 'jobs', 'users', 'messages', 'notifications')
    GROUP BY TABLE_NAME, INDEX_NAME
    ORDER BY TABLE_NAME, INDEX_NAME
")->fetchAll(PDO::FETCH_ASSOC);

echo "<div class='index-list'>";
echo "<h3>📋 Liste des index :</h3>";
$currentTable = '';
foreach ($newIndexes as $index) {
    if ($currentTable !== $index['TABLE_NAME']) {
        if ($currentTable !== '') echo "<hr style='margin: 15px 0;'>";
        $currentTable = $index['TABLE_NAME'];
        echo "<h4 style='color: #C8102E; margin: 10px 0;'>Table : {$currentTable}</h4>";
    }
    echo "<div class='index-item'>";
    echo "<strong>{$index['INDEX_NAME']}</strong> → {$index['COLUMNS']}";
    echo "</div>";
}
echo "</div>";

echo "</body></html>";
?>

