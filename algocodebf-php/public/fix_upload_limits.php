<?php
/**
 * Script de diagnostic et correction pour l'erreur "Request Entity Too Large" (413)
 * 
 * Ce script diagnostique les problèmes de configuration PHP et Apache
 * et propose des solutions pour résoudre l'erreur 413 lors des uploads
 * 
 * URL: http://localhost/HubTech/public/fix_upload_limits.php
 */

// Définir BASE_URL si non défini
if (!defined('BASE_URL')) {
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $scriptPath = str_replace('/public', '', $scriptPath);
    define('BASE_URL', $scriptPath ?: '/HubTech');
}

// Valeurs recommandées
$requiredValues = [
    'upload_max_filesize' => '500M',
    'post_max_size' => '510M',
    'max_execution_time' => '3600',
    'max_input_time' => '3600',
    'memory_limit' => '512M'
];

// Valeurs actuelles
$currentValues = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
    'memory_limit' => ini_get('memory_limit')
];

// Convertir en bytes
function convertToBytes($val) {
    if (empty($val)) return 0;
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

// Vérifier les problèmes
$problems = [];
$phpMode = php_sapi_name();
$phpIniPath = php_ini_loaded_file();
$isModPhp = ($phpMode === 'apache2handler' || $phpMode === 'apache');

foreach ($requiredValues as $key => $required) {
    $current = $currentValues[$key];
    $currentBytes = convertToBytes($current);
    $requiredBytes = convertToBytes($required);
    
    if ($currentBytes < $requiredBytes) {
        $problems[] = [
            'setting' => $key,
            'current' => $current,
            'required' => $required,
            'current_bytes' => $currentBytes,
            'required_bytes' => $requiredBytes
        ];
    }
}

// Vérifier si php.ini est accessible en écriture
$phpIniWritable = $phpIniPath && is_writable($phpIniPath);

// Valeur requise pour LimitRequestBody (500MB en bytes)
$requiredLimitRequestBody = 536870912; // 500MB

// Obtenir le chemin httpd.conf (pour XAMPP)
$httpdConfPath = null;
$allowOverrideOk = false;
$limitRequestBodyOk = false;
$limitRequestBodyValue = null;
$limitRequestBodyFound = false;
$httpdConfWritable = false;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Windows - chercher dans les chemins XAMPP typiques
    $possiblePaths = [
        'C:/xampp/apache/conf/httpd.conf',
        'C:/xampp/apache2/conf/httpd.conf',
        dirname($phpIniPath) . '/../apache/conf/httpd.conf',
        dirname($phpIniPath) . '/../../apache/conf/httpd.conf'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $httpdConfPath = $path;
            $httpdConfWritable = is_writable($path);
            
            // Lire le fichier pour vérifier AllowOverride et LimitRequestBody
            $httpdContent = @file_get_contents($path);
            if ($httpdContent) {
                // Chercher AllowOverride dans la section Directory
                if (preg_match('/<Directory\s+["\']?C:.*?xampp.*?htdocs["\']?\s*>/is', $httpdContent)) {
                    if (preg_match('/<Directory\s+["\']?C:.*?xampp.*?htdocs["\']?\s*>.*?AllowOverride\s+(All|None)/is', $httpdContent, $matches)) {
                        $allowOverrideOk = (strtolower($matches[1]) === 'all');
                    }
                }
                
                // Chercher LimitRequestBody dans tout le fichier
                // Il peut être dans un bloc <Directory>, <VirtualHost>, ou au niveau global
                if (preg_match('/LimitRequestBody\s+(\d+)/i', $httpdContent, $matches)) {
                    $limitRequestBodyFound = true;
                    $limitRequestBodyValue = (int)$matches[1];
                    $limitRequestBodyOk = ($limitRequestBodyValue >= $requiredLimitRequestBody);
                } else {
                    // LimitRequestBody non trouvé - la valeur par défaut d'Apache est 0 (illimité)
                    // Mais certains serveurs peuvent avoir une limite par défaut plus faible
                    // On considère qu'il faut l'ajouter pour être sûr
                    $limitRequestBodyFound = false;
                    $limitRequestBodyOk = false; // Par précaution, on suggère de l'ajouter
                }
            }
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Correctif Erreur 413 - Uploads 500MB</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #c8102e 0%, #006a4e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .alert {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }
        .alert-error {
            background: #fee;
            border-color: #dc3545;
            color: #721c24;
        }
        .alert-warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .alert-info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .status-ok { color: #28a745; font-weight: 600; }
        .status-error { color: #dc3545; font-weight: 600; }
        .status-warning { color: #ffc107; font-weight: 600; }
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            border-left: 4px solid #c8102e;
        }
        .code-block code {
            display: block;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #c8102e;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover {
            background: #a00d24;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(200,16,46,0.3);
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .solution-box {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .solution-box h3 {
            color: #c8102e;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .steps {
            counter-reset: step-counter;
            list-style: none;
            padding: 0;
        }
        .steps li {
            counter-increment: step-counter;
            padding: 15px;
            margin: 10px 0;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #c8102e;
            position: relative;
            padding-left: 60px;
        }
        .steps li::before {
            content: counter(step-counter);
            position: absolute;
            left: 15px;
            top: 15px;
            width: 30px;
            height: 30px;
            background: #c8102e;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .info-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #c8102e;
        }
        .info-card strong {
            display: block;
            color: #c8102e;
            margin-bottom: 5px;
        }
        .copy-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-left: 10px;
        }
        .copy-btn:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Correctif Erreur 413 - Request Entity Too Large</h1>
            <p>Diagnostic et solutions pour les uploads de 500MB</p>
        </div>
        
        <div class="content">
            <?php if (empty($problems) && $limitRequestBodyOk): ?>
                <div class="alert alert-success">
                    <strong>✅ Configuration parfaite!</strong>
                    <p>Tous les paramètres PHP et Apache sont correctement configurés pour supporter les uploads de 500MB.</p>
                </div>
            <?php elseif (empty($problems) && !$limitRequestBodyOk): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Problème Apache détecté!</strong>
                    <p>Les paramètres PHP sont corrects, mais la directive Apache <code>LimitRequestBody</code> est insuffisante ou absente.</p>
                    <p>C'est probablement la cause de l'erreur 413. Suivez les instructions ci-dessous pour corriger cela.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    <strong>❌ Problème détecté!</strong>
                    <p>Les paramètres PHP sont insuffisants pour supporter les uploads de 500MB.</p>
                    <p>L'erreur "Request Entity Too Large" (413) se produit car les limites sont trop faibles.</p>
                </div>
            <?php endif; ?>
            
            <h2>📊 Diagnostic de la configuration</h2>
            
            <div class="info-grid">
                <div class="info-card">
                    <strong>Mode PHP</strong>
                    <?= htmlspecialchars($phpMode) ?>
                    <?php if ($isModPhp): ?>
                        <span class="status-ok">✅ (mod_php - .htaccess fonctionne)</span>
                    <?php else: ?>
                        <span class="status-warning">⚠️ (<?= htmlspecialchars($phpMode) ?> - .htaccess peut ne pas fonctionner)</span>
                    <?php endif; ?>
                </div>
                <div class="info-card">
                    <strong>Fichier php.ini</strong>
                    <?php if ($phpIniPath): ?>
                        <?= htmlspecialchars($phpIniPath) ?>
                        <?php if ($phpIniWritable): ?>
                            <span class="status-ok">✅ (accessible en écriture)</span>
                        <?php else: ?>
                            <span class="status-error">❌ (lecture seule - exécuter en admin)</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="status-error">❌ (non trouvé)</span>
                    <?php endif; ?>
                </div>
                <div class="info-card">
                    <strong>httpd.conf</strong>
                    <?php if ($httpdConfPath): ?>
                        <?= htmlspecialchars($httpdConfPath) ?>
                        <?php if ($allowOverrideOk): ?>
                            <div class="status-ok">✅ AllowOverride All</div>
                        <?php else: ?>
                            <div class="status-warning">⚠️ AllowOverride peut être None</div>
                        <?php endif; ?>
                        <?php if ($limitRequestBodyFound): ?>
                            <?php if ($limitRequestBodyOk): ?>
                                <div class="status-ok">✅ LimitRequestBody: <?= number_format($limitRequestBodyValue / 1024 / 1024, 0) ?>MB</div>
                            <?php else: ?>
                                <div class="status-error">❌ LimitRequestBody: <?= number_format($limitRequestBodyValue / 1024 / 1024, 0) ?>MB (insuffisant)</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="status-warning">⚠️ LimitRequestBody: non trouvé (à ajouter)</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="status-warning">⚠️ (non trouvé automatiquement)</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <h2>📋 État des paramètres PHP</h2>
            <table>
                <thead>
                    <tr>
                        <th>Paramètre</th>
                        <th>Valeur actuelle</th>
                        <th>Valeur requise</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requiredValues as $key => $required): ?>
                        <?php
                        $current = $currentValues[$key];
                        $currentBytes = convertToBytes($current);
                        $requiredBytes = convertToBytes($required);
                        $isProblem = $currentBytes < $requiredBytes;
                        $statusClass = $isProblem ? 'status-error' : 'status-ok';
                        $statusText = $isProblem ? '❌ Insuffisant' : '✅ OK';
                        ?>
                        <tr>
                            <td><strong><?= $key ?></strong></td>
                            <td><?= htmlspecialchars($current) ?></td>
                            <td><?= htmlspecialchars($required) ?></td>
                            <td class="<?= $statusClass ?>"><?= $statusText ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (!empty($problems)): ?>
                <div class="solution-box">
                    <h3>🔧 Solutions pour corriger l'erreur 413</h3>
                    
                    <?php if ($phpIniPath && $phpIniWritable): ?>
                        <div class="alert alert-info">
                            <strong>✅ Solution automatique disponible!</strong>
                            <p>Votre fichier php.ini est accessible en écriture. Vous pouvez utiliser le script automatique:</p>
                            <p><a href="<?= BASE_URL ?>/public/setup_php_upload.php" class="btn btn-success">⚙️ Configuration automatique</a></p>
                        </div>
                    <?php endif; ?>
                    
                    <h4>📝 Solution manuelle (RECOMMANDÉE - Plus fiable)</h4>
                    <ol class="steps">
                        <li>
                            <strong>Ouvrir php.ini</strong>
                            <p>Ouvrez le fichier suivant en tant qu'<strong>administrateur</strong>:</p>
                            <div class="code-block">
<?php if ($phpIniPath): ?>
<?= htmlspecialchars($phpIniPath) ?>
<?php else: ?>
C:\xampp\php\php.ini
<?php endif; ?>
                            </div>
                            <button class="copy-btn" onclick="copyToClipboard('<?= $phpIniPath ?: 'C:\\xampp\\php\\php.ini' ?>')">Copier</button>
                        </li>
                        
                        <li>
                            <strong>Rechercher et modifier les paramètres</strong>
                            <p>Utilisez Ctrl+F pour trouver chaque paramètre et modifiez-les:</p>
                            <div class="code-block">
upload_max_filesize = 500M
post_max_size = 510M
max_execution_time = 3600
max_input_time = 3600
memory_limit = 512M
                            </div>
                            <button class="copy-btn" onclick="copyCodeToClipboard(this)">Copier tout</button>
                            <p><strong>Note:</strong> Si une ligne est commentée (commence par <code>;</code>), supprimez le <code>;</code> pour l'activer.</p>
                        </li>
                        
                        <li>
                            <strong>Sauvegarder le fichier</strong>
                            <p>Appuyez sur Ctrl+S pour sauvegarder. Si vous ne pouvez pas sauvegarder, fermez et rouvrez en tant qu'administrateur.</p>
                        </li>
                        
                        <li>
                            <strong>Redémarrer Apache</strong>
                            <p>Dans le panneau de contrôle XAMPP:</p>
                            <ol>
                                <li>Cliquez sur <strong>Stop</strong> pour Apache</li>
                                <li>Attendez 5 secondes</li>
                                <li>Cliquez sur <strong>Start</strong> pour Apache</li>
                            </ol>
                        </li>
                        
                        <li>
                            <strong>Vérifier la configuration</strong>
                            <p>Actualisez cette page (F5) ou visitez:</p>
                            <p><a href="<?= BASE_URL ?>/public/config_php_upload_500mb.php" class="btn btn-secondary">🔍 Vérifier la configuration</a></p>
                        </li>
                    </ol>
                    
                    <?php if ($httpdConfPath && !$limitRequestBodyOk): ?>
                        <h4>🔧 Correction de LimitRequestBody dans httpd.conf (IMPORTANT)</h4>
                        <div class="alert alert-warning">
                            <strong>⚠️ La directive Apache LimitRequestBody est insuffisante ou absente!</strong>
                            <p>C'est probablement la cause de l'erreur 413. Suivez les étapes ci-dessous pour la corriger:</p>
                        </div>
                        <ol class="steps">
                            <li>
                                <strong>Ouvrir httpd.conf en tant qu'administrateur</strong>
                                <p>Ouvrez le fichier suivant en tant qu'<strong>administrateur</strong>:</p>
                                <div class="code-block">
<?= htmlspecialchars($httpdConfPath) ?>
                                </div>
                                <button class="copy-btn" onclick="copyToClipboard('<?= str_replace('/', '\\', $httpdConfPath) ?>')">Copier le chemin</button>
                            </li>
                            <li>
                                <strong>Rechercher la section Directory</strong>
                                <p>Utilisez Ctrl+F pour rechercher:</p>
                                <div class="code-block">
&lt;Directory "C:/xampp/htdocs"&gt;
                                </div>
                                <p><strong>Ou</strong> si vous ne trouvez pas cette section exacte, cherchez toute section <code>&lt;Directory&gt;</code> qui contient votre dossier htdocs.</p>
                            </li>
                            <li>
                                <strong>Ajouter ou modifier LimitRequestBody</strong>
                                <?php if ($limitRequestBodyFound): ?>
                                    <p><strong>LimitRequestBody existe déjà</strong> avec une valeur de <?= number_format($limitRequestBodyValue / 1024 / 1024, 0) ?>MB, mais elle est insuffisante.</p>
                                    <p>Recherchez la ligne:</p>
                                    <div class="code-block">
LimitRequestBody <?= $limitRequestBodyValue ?>
                                    </div>
                                    <p>Et remplacez-la par:</p>
                                <?php else: ?>
                                    <p><strong>LimitRequestBody n'existe pas</strong> dans la section Directory. Ajoutez cette ligne:</p>
                                <?php endif; ?>
                                <div class="code-block">
LimitRequestBody 536870912
                                </div>
                                <button class="copy-btn" onclick="copyToClipboard('LimitRequestBody 536870912')">Copier</button>
                                <p><strong>Note:</strong> 536870912 = 500MB en bytes. Placez cette ligne à l'intérieur du bloc <code>&lt;Directory&gt;</code>.</p>
                            </li>
                            <li>
                                <strong>Exemple de configuration complète</strong>
                                <p>La section Directory devrait ressembler à ceci:</p>
                                <div class="code-block">
&lt;Directory "C:/xampp/htdocs"&gt;
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
    LimitRequestBody 536870912
&lt;/Directory&gt;
                                </div>
                            </li>
                            <li>
                                <strong>Sauvegarder le fichier</strong>
                                <p>Appuyez sur Ctrl+S pour sauvegarder. Si vous ne pouvez pas sauvegarder, fermez et rouvrez en tant qu'administrateur.</p>
                            </li>
                            <li>
                                <strong>Redémarrer Apache</strong>
                                <p>Dans le panneau de contrôle XAMPP:</p>
                                <ol>
                                    <li>Cliquez sur <strong>Stop</strong> pour Apache</li>
                                    <li>Attendez 5 secondes</li>
                                    <li>Cliquez sur <strong>Start</strong> pour Apache</li>
                                </ol>
                                <p><strong>Important:</strong> Apache doit être redémarré pour que les changements dans httpd.conf prennent effet.</p>
                            </li>
                            <li>
                                <strong>Vérifier les changements</strong>
                                <p>Actualisez cette page (F5) pour vérifier que <code>LimitRequestBody</code> est maintenant correctement configuré.</p>
                            </li>
                        </ol>
                    <?php endif; ?>
                    
                    <?php if ($httpdConfPath && !$allowOverrideOk): ?>
                        <h4>⚠️ Configuration Apache - AllowOverride (Optionnel)</h4>
                        <div class="alert alert-warning">
                            <strong>AllowOverride peut être configuré sur None</strong>
                            <p>Pour que les paramètres .htaccess fonctionnent, vérifiez httpd.conf:</p>
                            <div class="code-block">
Fichier: <?= htmlspecialchars($httpdConfPath) ?>

Recherchez: &lt;Directory "C:/xampp/htdocs"&gt;
Vérifiez que: AllowOverride All
                            </div>
                            <p>Si <code>AllowOverride None</code>, changez en <code>AllowOverride All</code> et redémarrez Apache.</p>
                        </div>
                    <?php endif; ?>
                    
                    <h4>🔍 Vérification supplémentaire</h4>
                    <p>Si après modification de php.ini et redémarrage d'Apache, les valeurs sont toujours insuffisantes:</p>
                    <ol>
                        <li>Créez un fichier <code>test_phpinfo.php</code> dans <code>public/</code> avec:</li>
                        <div class="code-block">
&lt;?php phpinfo(); ?&gt;
                        </div>
                        <li>Visitez: <code>http://localhost/HubTech/public/test_phpinfo.php</code></li>
                        <li>Cherchez "Loaded Configuration File" pour vérifier que c'est le bon php.ini</li>
                        <li>Cherchez les valeurs ci-dessus dans la section "Core"</li>
                    </ol>
                </div>
                
                <div class="solution-box">
                    <h3>🚨 Solution d'urgence</h3>
                    <p>Si vous devez tester immédiatement, réduisez temporairement la taille des vidéos à moins de 40MB, ou:</p>
                    <ol>
                        <li>Uploadez une seule vidéo à la fois</li>
                        <li>Compressez les vidéos avant l'upload</li>
                        <li>Utilisez un service externe (YouTube, Vimeo) et ajoutez le lien</li>
                    </ol>
                </div>
            <?php endif; ?>
            
            <?php if (empty($problems) && !$limitRequestBodyOk && $httpdConfPath): ?>
                <div class="solution-box">
                    <h3>🔧 Correction de LimitRequestBody dans httpd.conf (OBLIGATOIRE)</h3>
                    <div class="alert alert-warning">
                        <strong>⚠️ La directive Apache LimitRequestBody est insuffisante ou absente!</strong>
                        <p>C'est probablement la cause de l'erreur 413. Les paramètres PHP sont corrects, mais Apache bloque les requêtes volumineuses.</p>
                    </div>
                    <ol class="steps">
                        <li>
                            <strong>Ouvrir httpd.conf en tant qu'administrateur</strong>
                            <p>Ouvrez le fichier suivant en tant qu'<strong>administrateur</strong>:</p>
                            <div class="code-block">
<?= htmlspecialchars($httpdConfPath) ?>
                            </div>
                            <button class="copy-btn" onclick="copyToClipboard('<?= str_replace('/', '\\', $httpdConfPath) ?>')">Copier le chemin</button>
                        </li>
                        <li>
                            <strong>Rechercher la section Directory</strong>
                            <p>Utilisez Ctrl+F pour rechercher:</p>
                            <div class="code-block">
&lt;Directory "C:/xampp/htdocs"&gt;
                            </div>
                            <p><strong>Ou</strong> si vous ne trouvez pas cette section exacte, cherchez toute section <code>&lt;Directory&gt;</code> qui contient votre dossier htdocs.</p>
                        </li>
                        <li>
                            <strong>Ajouter ou modifier LimitRequestBody</strong>
                            <?php if ($limitRequestBodyFound): ?>
                                <p><strong>LimitRequestBody existe déjà</strong> avec une valeur de <?= number_format($limitRequestBodyValue / 1024 / 1024, 0) ?>MB, mais elle est insuffisante.</p>
                                <p>Recherchez la ligne:</p>
                                <div class="code-block">
LimitRequestBody <?= $limitRequestBodyValue ?>
                                </div>
                                <p>Et remplacez-la par:</p>
                            <?php else: ?>
                                <p><strong>LimitRequestBody n'existe pas</strong> dans la section Directory. Ajoutez cette ligne:</p>
                            <?php endif; ?>
                            <div class="code-block">
LimitRequestBody 536870912
                            </div>
                            <button class="copy-btn" onclick="copyToClipboard('LimitRequestBody 536870912')">Copier</button>
                            <p><strong>Note:</strong> 536870912 = 500MB en bytes. Placez cette ligne à l'intérieur du bloc <code>&lt;Directory&gt;</code>.</p>
                        </li>
                        <li>
                            <strong>Exemple de configuration complète</strong>
                            <p>La section Directory devrait ressembler à ceci:</p>
                            <div class="code-block">
&lt;Directory "C:/xampp/htdocs"&gt;
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
    LimitRequestBody 536870912
&lt;/Directory&gt;
                            </div>
                        </li>
                        <li>
                            <strong>Sauvegarder le fichier</strong>
                            <p>Appuyez sur Ctrl+S pour sauvegarder. Si vous ne pouvez pas sauvegarder, fermez et rouvrez en tant qu'administrateur.</p>
                        </li>
                        <li>
                            <strong>Redémarrer Apache</strong>
                            <p>Dans le panneau de contrôle XAMPP:</p>
                            <ol>
                                <li>Cliquez sur <strong>Stop</strong> pour Apache</li>
                                <li>Attendez 5 secondes</li>
                                <li>Cliquez sur <strong>Start</strong> pour Apache</li>
                            </ol>
                            <p><strong>Important:</strong> Apache doit être redémarré pour que les changements dans httpd.conf prennent effet.</p>
                        </li>
                        <li>
                            <strong>Vérifier les changements</strong>
                            <p>Actualisez cette page (F5) pour vérifier que <code>LimitRequestBody</code> est maintenant correctement configuré.</p>
                        </li>
                    </ol>
                </div>
            <?php elseif (empty($problems) && $limitRequestBodyOk): ?>
                <div class="alert alert-info">
                    <strong>✅ Configuration complète!</strong>
                    <p>Tous les paramètres PHP et Apache sont correctement configurés. Si l'erreur 413 persiste, vérifiez:</p>
                    <ul>
                        <li>Les logs Apache: <code>C:\xampp\apache\logs\error.log</code></li>
                        <li>Si vous êtes derrière un proxy/load balancer, vérifiez sa configuration</li>
                        <li>Testez avec une vidéo plus petite pour isoler le problème</li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #ddd; text-align: center;">
                <a href="<?= BASE_URL ?>/tutorial/create" class="btn">← Retour à la création de tutoriel</a>
                <a href="<?= BASE_URL ?>/public/config_php_upload_500mb.php" class="btn btn-secondary">🔍 Vérifier la configuration</a>
                <?php if ($phpIniPath && $phpIniWritable): ?>
                    <a href="<?= BASE_URL ?>/public/setup_php_upload.php" class="btn btn-success">⚙️ Configuration automatique</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('✅ Chemin copié dans le presse-papiers!');
            }, function() {
                // Fallback pour les anciens navigateurs
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('✅ Chemin copié dans le presse-papiers!');
            });
        }
        
        function copyCodeToClipboard(button) {
            const codeBlock = button.previousElementSibling;
            const text = codeBlock.textContent || codeBlock.innerText;
            copyToClipboard(text);
        }
    </script>
</body>
</html>

