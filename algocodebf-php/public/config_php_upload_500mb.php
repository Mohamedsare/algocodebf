<?php
/**
 * Configuration PHP pour supporter les uploads de 500MB
 * 
 * Ce fichier permet de vérifier et configurer PHP pour supporter les uploads de vidéos jusqu'à 500MB
 * 
 * INSTRUCTIONS:
 * 1. Exécutez ce fichier dans votre navigateur: http://localhost/HubTech/public/config_php_upload_500mb.php
 * 2. Suivez les instructions affichées
 * 3. Modifiez votre php.ini selon les recommandations
 */

// Définir BASE_URL si non défini
if (!defined('BASE_URL')) {
    // Déterminer BASE_URL depuis le chemin du script
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $scriptPath = str_replace('/public', '', $scriptPath);
    define('BASE_URL', $scriptPath ?: '/HubTech');
}

// Valeurs recommandées
$requiredValues = [
    'upload_max_filesize' => '500M',
    'post_max_size' => '510M',
    'max_execution_time' => '3600', // 1 heure
    'max_input_time' => '3600',
    'memory_limit' => '512M'
];

// Valeurs actuelles (peuvent être modifiées par .htaccess)
$currentValues = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
    'memory_limit' => ini_get('memory_limit')
];

// Convertir les valeurs en bytes pour comparaison
function convertToBytes($val) {
    if (empty($val)) return 0;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .ok {
            color: #28a745;
            font-weight: 600;
        }
        .warning {
            color: #ffc107;
            font-weight: 600;
        }
        .error {
            color: #dc3545;
            font-weight: 600;
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
        .instructions {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin-top: 0;
            color: #0066cc;
        }
        .instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Configuration PHP pour Uploads de 500MB</h1>
        
        <p>Cette page vérifie la configuration PHP pour supporter les uploads de vidéos jusqu'à 500MB.</p>
        
        <h2>État actuel de la configuration</h2>
        
        <?php
        // Vérifier si les paramètres .htaccess sont appliqués
        $htaccessApplied = false;
        $allOk = true;
        foreach ($requiredValues as $key => $required) {
            $current = $currentValues[$key];
            $currentBytes = convertToBytes($current);
            $requiredBytes = convertToBytes($required);
            if ($currentBytes < $requiredBytes) {
                $allOk = false;
                break;
            }
        }
        
        // Vérifier le mode PHP
        $phpMode = php_sapi_name();
        $isModPhp = ($phpMode === 'apache2handler' || $phpMode === 'apache');
        ?>
        
        <?php if (!$allOk): ?>
            <div class="warning" style="margin-bottom: 20px;">
                <strong>⚠️ ATTENTION:</strong> Les paramètres sont insuffisants pour les uploads de 500MB.
                <br><strong>Mode PHP:</strong> <?= htmlspecialchars($phpMode) ?> 
                <?php if ($isModPhp): ?>
                    ✅ (mod_php - .htaccess devrait fonctionner)
                <?php else: ?>
                    ⚠️ (<?= htmlspecialchars($phpMode) ?> - .htaccess peut ne pas fonctionner, modifiez php.ini directement)
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="success" style="margin-bottom: 20px;">
                <strong>✅ PARFAIT!</strong> Tous les paramètres sont correctement configurés.
                <br>Vous pouvez maintenant uploader des vidéos jusqu'à 500MB!
            </div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Paramètre</th>
                    <th>Valeur actuelle</th>
                    <th>Valeur recommandée</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requiredValues as $key => $required): ?>
                    <?php
                    $current = $currentValues[$key];
                    $currentBytes = convertToBytes($current);
                    $requiredBytes = convertToBytes($required);
                    $status = $currentBytes >= $requiredBytes ? 'ok' : ($currentBytes >= $requiredBytes * 0.5 ? 'warning' : 'error');
                    ?>
                    <tr>
                        <td><strong><?= $key ?></strong></td>
                        <td><?= htmlspecialchars($current) ?></td>
                        <td><?= htmlspecialchars($required) ?></td>
                        <td class="<?= $status ?>">
                            <?php if ($status === 'ok'): ?>
                                ✅ OK
                            <?php elseif ($status === 'warning'): ?>
                                ⚠️ Attention
                            <?php else: ?>
                                ❌ Insuffisant
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (!$allOk): ?>
            <div class="instructions" style="background: #fff3cd; border: 2px solid #ffc107; margin-top: 20px;">
                <h3>🚀 Solution rapide</h3>
                <p><strong>Étape 1:</strong> Redémarrez Apache dans XAMPP</p>
                <p><strong>Étape 2:</strong> Actualisez cette page (F5)</p>
                <p><strong>Si les valeurs sont toujours insuffisantes:</strong></p>
                <ol>
                    <li>Ouvrez <code>C:\xampp\php\php.ini</code></li>
                    <li>Recherchez et modifiez les paramètres ci-dessus</li>
                    <li>Redémarrez Apache</li>
                    <li>Actualisez cette page</li>
                </ol>
            </div>
        <?php endif; ?>
        
        <div class="instructions">
            <h3>📝 Instructions pour configurer PHP</h3>
            
            <h4>Pour XAMPP (Windows):</h4>
            <ol>
                <li>Ouvrez le fichier <code>php.ini</code> situé dans <code>C:\xampp\php\php.ini</code></li>
                <li>Recherchez les paramètres suivants et modifiez-les:</li>
            </ol>
            
            <div class="code-block">
upload_max_filesize = 500M<br>
post_max_size = 510M<br>
max_execution_time = 3600<br>
max_input_time = 3600<br>
memory_limit = 512M
            </div>
            
            <ol start="3">
                <li>Redémarrez Apache dans le panneau de contrôle XAMPP</li>
                <li>Actualisez cette page pour vérifier les changements</li>
            </ol>
            
            <h4>Pour Linux/Mac:</h4>
            <ol>
                <li>Localisez votre fichier <code>php.ini</code>:
                    <div class="code-block">php --ini</div>
                </li>
                <li>Ouvrez le fichier avec un éditeur de texte (nécessite sudo)</li>
                <li>Modifiez les mêmes paramètres que ci-dessus</li>
                <li>Redémarrez votre serveur web (Apache/Nginx)</li>
            </ol>
            
            <h4>Vérification:</h4>
            <p>Après avoir modifié php.ini et redémarré le serveur, actualisez cette page pour vérifier que les changements ont été appliqués.</p>
        </div>
        
        <div class="instructions">
            <h3>✅ Configuration via .htaccess (DÉJÀ APPLIQUÉE)</h3>
            <p>Les paramètres ont été <strong>automatiquement ajoutés</strong> dans le fichier <code>public/.htaccess</code>.</p>
            <p>Ces paramètres fonctionnent si:</p>
            <ul>
                <li>✅ PHP est en mode <strong>mod_php</strong> (XAMPP par défaut)</li>
                <li>✅ <strong>AllowOverride</strong> est activé dans la configuration Apache</li>
                <li>✅ Les directives <strong>php_value</strong> sont autorisées</li>
            </ul>
            <p><strong>Si les paramètres ne s'appliquent pas:</strong></p>
            <ol>
                <li>Redémarrez Apache</li>
                <li>Vérifiez que <code>AllowOverride All</code> est configuré dans Apache</li>
                <li>Utilisez l'option de configuration manuelle ci-dessus</li>
            </ol>
        </div>
        
        <div class="instructions">
            <h3>🔧 Script de configuration automatique</h3>
            <p>Un script est disponible pour modifier automatiquement php.ini:</p>
            <p><a href="<?= BASE_URL ?? '/HubTech' ?>/public/setup_php_upload.php" class="btn" style="display: inline-block; padding: 10px 20px; background: #c8102e; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0;">
                ⚙️ Configuration automatique de php.ini
            </a></p>
            <p><strong>⚠️ ATTENTION:</strong> Ce script nécessite des permissions d'écriture sur php.ini et doit être exécuté en tant qu'administrateur.</p>
        </div>
        
        <div class="instructions">
            <h3>⚠️ Note importante</h3>
            <p>Si vous ne pouvez pas modifier php.ini (par exemple sur un hébergement mutualisé), contactez votre hébergeur pour augmenter ces limites.</p>
            <p>Les directives <code>php_value</code> dans .htaccess fonctionnent uniquement si votre hébergeur le permet (mode mod_php).</p>
        </div>
        
        <div class="instructions">
            <h3>📝 Guide complet de configuration</h3>
            <p>Un guide détaillé étape par étape est disponible:</p>
            <p><a href="<?= BASE_URL ?>/GUIDE_CONFIGURATION_XAMPP_500MB.md" target="_blank" style="color: #c8102e; font-weight: 600; text-decoration: underline;">
                📖 Guide de Configuration XAMPP pour Uploads de 500MB
            </a></p>
        </div>
        
        <div class="instructions">
            <h3>🔍 Vérification des paramètres .htaccess</h3>
            <p>Pour vérifier si les paramètres .htaccess sont appliqués, vérifiez les valeurs ci-dessus.</p>
            <p>Si les valeurs sont toujours insuffisantes après avoir redémarré Apache:</p>
            <ol>
                <li>Vérifiez que <code>AllowOverride All</code> est configuré dans Apache</li>
                <li>Vérifiez que PHP est en mode <strong>mod_php</strong> (pas PHP-FPM)</li>
                <li>Utilisez la <strong>Méthode 1</strong> (modification manuelle de php.ini) - la plus fiable</li>
            </ol>
        </div>
        
        <p style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">
            <a href="<?= BASE_URL ?>/tutorial/create" style="color: #c8102e; font-weight: 600; text-decoration: none; padding: 10px 20px; background: #f8f9fa; border-radius: 5px; display: inline-block;">
                ← Retour à la création de tutoriel
            </a>
            <a href="<?= BASE_URL ?>/public/setup_php_upload.php" style="color: white; font-weight: 600; text-decoration: none; padding: 10px 20px; background: #c8102e; border-radius: 5px; display: inline-block; margin-left: 10px;">
                ⚙️ Configuration automatique
            </a>
        </p>
    </div>
</body>
</html>

