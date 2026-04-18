<?php
/**
 * Script d'ajout de la colonne level à la table tutorials
 * Accès : http://localhost/AlgoCodeBF/add_tutorial_level.php
 * ⚠️ À supprimer après exécution
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout colonne level - AlgoCodeBF</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 { color: #2c3e50; margin-bottom: 30px; }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Ajout de la colonne level</h1>
        
        <?php
        try {
            $db = Database::getInstance();
            
            // Vérifier si la colonne existe déjà
            $columns = $db->query("SHOW COLUMNS FROM tutorials WHERE Field = 'level'");
            
            if (!empty($columns)) {
                echo '<div class="success">';
                echo '<h3>✅ La colonne level existe déjà!</h3>';
                echo '<p>Aucune action nécessaire.</p>';
                echo '</div>';
            } else {
                // Ajouter la colonne
                $db->execute("ALTER TABLE tutorials ADD COLUMN level ENUM('Débutant', 'Intermédiaire', 'Avancé', 'Expert') DEFAULT 'Débutant' AFTER category");
                
                echo '<div class="success">';
                echo '<h3>✅ Migration réussie!</h3>';
                echo '<p>La colonne <code>level</code> a été ajoutée à la table <code>tutorials</code>.</p>';
                echo '<p style="margin-top: 15px;">Valeurs possibles : Débutant, Intermédiaire, Avancé, Expert</p>';
                echo '<p style="margin-top: 15px; font-weight: 600;">Vous pouvez maintenant supprimer ce fichier pour des raisons de sécurité.</p>';
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h3>❌ Erreur</h3>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
        ?>
        
        <a href="tutorial/index" class="btn">📚 Voir les tutoriels</a>
    </div>
</body>
</html>

