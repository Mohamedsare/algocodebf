<?php
/**
 * Script de configuration automatique pour les uploads de 500MB
 * 
 * Ce script tente de modifier les paramètres PHP pour supporter les uploads de 500MB
 * ATTENTION: Ce script nécessite les permissions d'écriture sur php.ini
 * 
 * INSTRUCTIONS:
 * 1. Exécutez ce script UNE SEULE FOIS: http://localhost/HubTech/public/setup_php_upload.php
 * 2. Vérifiez que les modifications ont été appliquées
 * 3. SUPPRIMEZ ce fichier après utilisation pour des raisons de sécurité
 */

// Définir BASE_URL si non défini
if (!defined('BASE_URL')) {
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $scriptPath = str_replace('/public', '', $scriptPath);
    define('BASE_URL', $scriptPath ?: '/HubTech');
}

// Sécurité: Vérifier si on est en mode développement
if (php_sapi_name() !== 'cli' && $_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1') {
    die('⚠️ Ce script ne peut être exécuté que sur localhost');
}

// Chemin vers php.ini (XAMPP par défaut)
$phpIniPath = php_ini_loaded_file();

if (!$phpIniPath) {
    die('❌ Impossible de trouver le fichier php.ini');
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration PHP - Uploads 500MB</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #c8102e;
            border-bottom: 3px solid #c8102e;
            padding-bottom: 10px;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .code-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #c8102e;
            margin: 20px 0;
            font-family: monospace;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #c8102e;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #a00d24;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Configuration PHP pour Uploads de 500MB</h1>
        
        <div class="warning">
            <strong>⚠️ ATTENTION:</strong> Ce script tente de modifier automatiquement votre fichier php.ini.
            <br><strong>Faites une sauvegarde de votre php.ini avant de continuer!</strong>
        </div>
        
        <?php
        // Paramètres à modifier
        $requiredSettings = [
            'upload_max_filesize' => '500M',
            'post_max_size' => '510M',
            'max_execution_time' => '3600',
            'max_input_time' => '3600',
            'memory_limit' => '512M'
        ];
        
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_settings'])) {
            echo '<h2>📝 Application des paramètres</h2>';
            
            // Lire le fichier php.ini
            if (!is_writable($phpIniPath)) {
                echo '<div class="error">';
                echo '❌ Le fichier php.ini n\'est pas accessible en écriture.<br>';
                echo 'Chemin: ' . htmlspecialchars($phpIniPath) . '<br>';
                echo 'Vous devez modifier php.ini manuellement ou exécuter ce script en tant qu\'administrateur.';
                echo '</div>';
            } else {
                $phpIniContent = file_get_contents($phpIniPath);
                $modified = false;
                $changes = [];
                
                foreach ($requiredSettings as $setting => $value) {
                    // Pattern pour trouver la ligne (avec ou sans commentaire)
                    $pattern = '/^(' . preg_quote($setting, '/') . '\s*=\s*)[^;]*/m';
                    
                    if (preg_match($pattern, $phpIniContent)) {
                        // La ligne existe, la modifier
                        $phpIniContent = preg_replace(
                            $pattern,
                            '$1' . $value,
                            $phpIniContent
                        );
                        $changes[] = $setting . ' = ' . $value;
                        $modified = true;
                    } else {
                        // La ligne n'existe pas, l'ajouter à la fin
                        $phpIniContent .= "\n; Configuration HubTech - Uploads 500MB\n";
                        $phpIniContent .= $setting . ' = ' . $value . "\n";
                        $changes[] = $setting . ' = ' . $value . ' (ajouté)';
                        $modified = true;
                    }
                }
                
                if ($modified) {
                    // Sauvegarder le fichier
                    if (file_put_contents($phpIniPath, $phpIniContent)) {
                        echo '<div class="success">';
                        echo '✅ Paramètres modifiés avec succès!<br>';
                        echo 'Modifications appliquées:<br>';
                        echo '<ul>';
                        foreach ($changes as $change) {
                            echo '<li>' . htmlspecialchars($change) . '</li>';
                        }
                        echo '</ul>';
                        echo '<strong>⚠️ IMPORTANT: Redémarrez Apache pour que les changements prennent effet!</strong>';
                        echo '</div>';
                    } else {
                        echo '<div class="error">';
                        echo '❌ Impossible d\'écrire dans le fichier php.ini.';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="warning">';
                    echo '⚠️ Aucune modification n\'a été nécessaire (les paramètres sont déjà corrects).';
                    echo '</div>';
                }
            }
        }
        
        // Afficher les paramètres actuels
        echo '<h2>📊 Paramètres actuels</h2>';
        echo '<div class="code-block">';
        echo 'Chemin php.ini: <strong>' . htmlspecialchars($phpIniPath) . '</strong><br>';
        echo 'Accessible en écriture: ' . (is_writable($phpIniPath) ? '✅ Oui' : '❌ Non') . '<br><br>';
        
        echo 'Paramètres actuels:<br>';
        foreach ($requiredSettings as $setting => $value) {
            $current = ini_get($setting);
            $currentBytes = convertToBytes($current);
            $requiredBytes = convertToBytes($value);
            $status = $currentBytes >= $requiredBytes ? '✅' : '❌';
            
            echo $status . ' ' . $setting . ' = ' . $current . ' (recommandé: ' . $value . ')<br>';
        }
        echo '</div>';
        
        // Fonction pour convertir en bytes
        function convertToBytes($val) {
            $val = trim($val);
            $last = strtolower($val[strlen($val)-1]);
            $val = (int)$val;
            switch($last) {
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }
            return $val;
        }
        ?>
        
        <h2>⚙️ Options de configuration</h2>
        
        <div class="warning">
            <strong>Option 1: Configuration automatique (recommandé si accessible en écriture)</strong>
            <form method="POST" style="margin-top: 15px;">
                <button type="submit" name="apply_settings" class="btn" onclick="return confirm('Êtes-vous sûr de vouloir modifier php.ini? Assurez-vous d\'avoir fait une sauvegarde!');">
                    🔧 Appliquer les paramètres automatiquement
                </button>
            </form>
        </div>
        
        <div class="code-block">
            <strong>Option 2: Configuration manuelle</strong><br><br>
            1. Ouvrez le fichier: <strong><?= htmlspecialchars($phpIniPath) ?></strong><br>
            2. Modifiez les paramètres suivants:<br><br>
            
            <?php foreach ($requiredSettings as $setting => $value): ?>
                <?= $setting ?> = <?= $value ?><br>
            <?php endforeach; ?>
            
            <br>3. Redémarrez Apache<br>
            4. Actualisez cette page pour vérifier
        </div>
        
        <div class="code-block">
            <strong>Option 3: Configuration via .htaccess (déjà appliquée)</strong><br><br>
            Les paramètres ont été ajoutés dans <code>public/.htaccess</code><br>
            Si vous utilisez mod_php (XAMPP par défaut), cela devrait fonctionner.<br>
            Si cela ne fonctionne pas, utilisez l'option 1 ou 2.
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">
            <a href="<?= BASE_URL ?? '/HubTech' ?>/tutorial/create" class="btn btn-secondary">
                ← Retour à la création de tutoriel
            </a>
            <a href="<?= BASE_URL ?? '/HubTech' ?>/public/config_php_upload_500mb.php" class="btn btn-secondary">
                🔍 Vérifier la configuration
            </a>
        </div>
        
        <div class="warning" style="margin-top: 30px;">
            <strong>🔒 Sécurité:</strong> Après avoir configuré PHP, <strong>supprimez ce fichier</strong> (setup_php_upload.php) 
            pour éviter qu'il soit utilisé par des personnes non autorisées.
        </div>
    </div>
</body>
</html>

