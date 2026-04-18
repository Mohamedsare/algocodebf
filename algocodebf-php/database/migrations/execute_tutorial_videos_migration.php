<?php
/**
 * Script de migration pour ajouter les tables tutorial_videos et tutorial_chapters
 * Exécution automatique via PHP
 * 
 * @author HubTech
 * @date 2025-01-27
 */

// Charger la configuration de la base de données
require_once __DIR__ . '/../../config/config.php';

echo "====================================================\n";
echo "  MIGRATION: Tables tutorial_videos et tutorial_chapters\n";
echo "====================================================\n\n";

try {
    // Connexion à la base de données
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connexion à la base de données réussie\n\n";
    
    // Vérifier si les tables existent déjà
    echo "→ Vérification des tables existantes...\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'tutorial_videos'");
    $tutorialVideosExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'tutorial_chapters'");
    $tutorialChaptersExists = $stmt->rowCount() > 0;
    
    if ($tutorialVideosExists && $tutorialChaptersExists) {
        echo "⚠ Les tables existent déjà. Migration déjà appliquée.\n\n";
        echo "→ Vérification de la structure...\n";
        
        // Vérifier la structure de tutorial_videos
        $stmt = $pdo->query("DESCRIBE tutorial_videos");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $requiredColumns = ['id', 'tutorial_id', 'title', 'file_path', 'file_name', 'file_size', 'order_index'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (empty($missingColumns)) {
            echo "✓ Table tutorial_videos: Structure correcte\n";
        } else {
            echo "⚠ Table tutorial_videos: Colonnes manquantes: " . implode(', ', $missingColumns) . "\n";
            echo "  → Recréation de la table...\n";
            $tutorialVideosExists = false; // Forcer la recréation
        }
        
        // Vérifier la structure de tutorial_chapters
        $stmt = $pdo->query("DESCRIBE tutorial_chapters");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $requiredColumns = ['id', 'tutorial_id', 'chapter_number', 'title', 'order_index'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (empty($missingColumns)) {
            echo "✓ Table tutorial_chapters: Structure correcte\n";
        } else {
            echo "⚠ Table tutorial_chapters: Colonnes manquantes: " . implode(', ', $missingColumns) . "\n";
            echo "  → Recréation de la table...\n";
            $tutorialChaptersExists = false; // Forcer la recréation
        }
        
        if ($tutorialVideosExists && $tutorialChaptersExists) {
            echo "\n✅ Migration déjà appliquée et structure correcte!\n";
            exit(0);
        }
    }
    
    // Lire le fichier SQL de migration
    $migrationFile = __DIR__ . '/add_tutorial_videos_and_chapters.sql';
    if (!file_exists($migrationFile)) {
        throw new Exception("Fichier de migration non trouvé: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Supprimer les commentaires et les lignes vides pour une exécution propre
    $sql = preg_replace('/--.*$/m', '', $sql); // Supprimer les commentaires
    $sql = preg_replace('/^\s*$/m', '', $sql); // Supprimer les lignes vides
    
    // Diviser en requêtes individuelles
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "→ Exécution de la migration...\n\n";
    
    $executed = 0;
    foreach ($queries as $query) {
        if (empty($query) || strpos($query, 'USE') === 0) {
            continue; // Ignorer USE et les requêtes vides
        }
        
        try {
            $pdo->exec($query);
            $executed++;
            
            // Identifier quelle table a été créée
            if (stripos($query, 'CREATE TABLE') !== false) {
                if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $query, $matches)) {
                    echo "✓ Table créée: {$matches[1]}\n";
                }
            }
        } catch (PDOException $e) {
            // Ignorer les erreurs "table already exists" si on utilise IF NOT EXISTS
            if (stripos($e->getMessage(), 'already exists') === false) {
                echo "⚠ Erreur lors de l'exécution: " . $e->getMessage() . "\n";
                echo "  Requête: " . substr($query, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n✓ Migration terminée! ($executed requête(s) exécutée(s))\n\n";
    
    // Vérification finale
    echo "→ Vérification finale...\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'tutorial_videos'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table tutorial_videos existe\n";
    } else {
        echo "❌ Table tutorial_videos n'existe pas!\n";
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'tutorial_chapters'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table tutorial_chapters existe\n";
    } else {
        echo "❌ Table tutorial_chapters n'existe pas!\n";
    }
    
    echo "\n✅ Migration complétée avec succès!\n";
    echo "\nVous pouvez maintenant utiliser la fonctionnalité d'upload de plusieurs vidéos.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

