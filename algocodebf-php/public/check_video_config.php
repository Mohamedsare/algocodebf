<?php
/**
 * Script de vérification de la configuration PHP pour l'upload de vidéos
 * Accès : http://localhost/AlgoCodeBF/check_video_config.php
 * ⚠️ À supprimer en production
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Configuration Upload Vidéos - AlgoCodeBF</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .config-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #6c757d;
        }
        
        .config-item.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        
        .config-item.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        
        .config-item.danger {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        
        .config-label {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .config-value {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        .config-value.success {
            color: #28a745;
        }
        
        .config-value.warning {
            color: #ffc107;
        }
        
        .config-value.danger {
            color: #dc3545;
        }
        
        .status-icon {
            margin-left: 10px;
            font-size: 1.2rem;
        }
        
        .recommendation {
            background: #e7f3ff;
            border: 2px solid #2196F3;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        
        .recommendation h3 {
            color: #1976D2;
            margin-bottom: 15px;
        }
        
        .recommendation ul {
            margin-left: 20px;
        }
        
        .recommendation li {
            margin-bottom: 8px;
            color: #424242;
        }
        
        .btn-return {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        
        .btn-return:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 Configuration Upload Vidéos</h1>
        <p class="subtitle">Vérification de la configuration PHP pour AlgoCodeBF</p>
        
        <div class="warning">
            ⚠️ <strong>Attention :</strong> Supprimez ce fichier en production pour des raisons de sécurité !
        </div>
        
        <h2 style="margin-bottom: 20px; color: #2c3e50;">Paramètres PHP</h2>
        
        <?php
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $maxExecutionTime = ini_get('max_execution_time');
        $maxInputTime = ini_get('max_input_time');
        $memoryLimit = ini_get('memory_limit');
        
        // Convertir en bytes pour comparaison
        function convertToBytes($size) {
            $size = trim($size);
            $last = strtolower($size[strlen($size)-1]);
            $size = (int) $size;
            switch($last) {
                case 'g': $size *= 1024;
                case 'm': $size *= 1024;
                case 'k': $size *= 1024;
            }
            return $size;
        }
        
        $uploadMaxBytes = convertToBytes($uploadMaxFilesize);
        $postMaxBytes = convertToBytes($postMaxSize);
        $memoryLimitBytes = convertToBytes($memoryLimit);
        
        $requiredUpload = 51 * 1024 * 1024; // 51 MB
        $requiredPost = 52 * 1024 * 1024; // 52 MB
        $requiredMemory = 128 * 1024 * 1024; // 128 MB minimum
        
        // Vérifications
        $uploadOk = $uploadMaxBytes >= $requiredUpload;
        $postOk = $postMaxBytes >= $requiredPost;
        $execTimeOk = $maxExecutionTime >= 300 || $maxExecutionTime == 0;
        $inputTimeOk = $maxInputTime >= 300 || $maxInputTime == -1;
        $memoryOk = $memoryLimitBytes >= $requiredMemory || $memoryLimit == '-1';
        
        $allOk = $uploadOk && $postOk && $execTimeOk && $inputTimeOk && $memoryOk;
        ?>
        
        <div class="config-item <?= $uploadOk ? 'success' : 'danger' ?>">
            <span class="config-label">upload_max_filesize</span>
            <span>
                <span class="config-value <?= $uploadOk ? 'success' : 'danger' ?>"><?= $uploadMaxFilesize ?></span>
                <span class="status-icon"><?= $uploadOk ? '✅' : '❌' ?></span>
            </span>
        </div>
        
        <div class="config-item <?= $postOk ? 'success' : 'danger' ?>">
            <span class="config-label">post_max_size</span>
            <span>
                <span class="config-value <?= $postOk ? 'success' : 'danger' ?>"><?= $postMaxSize ?></span>
                <span class="status-icon"><?= $postOk ? '✅' : '❌' ?></span>
            </span>
        </div>
        
        <div class="config-item <?= $execTimeOk ? 'success' : 'warning' ?>">
            <span class="config-label">max_execution_time</span>
            <span>
                <span class="config-value <?= $execTimeOk ? 'success' : 'warning' ?>">
                    <?= $maxExecutionTime == 0 ? 'illimité' : $maxExecutionTime . 's' ?>
                </span>
                <span class="status-icon"><?= $execTimeOk ? '✅' : '⚠️' ?></span>
            </span>
        </div>
        
        <div class="config-item <?= $inputTimeOk ? 'success' : 'warning' ?>">
            <span class="config-label">max_input_time</span>
            <span>
                <span class="config-value <?= $inputTimeOk ? 'success' : 'warning' ?>">
                    <?= $maxInputTime == -1 ? 'illimité' : $maxInputTime . 's' ?>
                </span>
                <span class="status-icon"><?= $inputTimeOk ? '✅' : '⚠️' ?></span>
            </span>
        </div>
        
        <div class="config-item <?= $memoryOk ? 'success' : 'warning' ?>">
            <span class="config-label">memory_limit</span>
            <span>
                <span class="config-value <?= $memoryOk ? 'success' : 'warning' ?>"><?= $memoryLimit ?></span>
                <span class="status-icon"><?= $memoryOk ? '✅' : '⚠️' ?></span>
            </span>
        </div>
        
        <?php if ($allOk): ?>
            <div class="recommendation">
                <h3>✅ Configuration optimale !</h3>
                <p style="color: #28a745; font-weight: 600; margin-top: 10px;">
                    Votre serveur est correctement configuré pour uploader des vidéos jusqu'à 50 MB.
                </p>
                <p style="margin-top: 15px; color: #424242;">
                    Vous pouvez maintenant créer des tutoriels avec des vidéos sur 
                    <a href="tutorial/create" style="color: #1976D2; font-weight: 600;">AlgoCodeBF</a>
                </p>
            </div>
        <?php else: ?>
            <div class="recommendation">
                <h3>⚙️ Configuration requise</h3>
                <p style="margin-bottom: 15px;">Pour permettre l'upload de vidéos, modifiez les valeurs suivantes dans <strong>php.ini</strong> :</p>
                <ul>
                    <?php if (!$uploadOk): ?>
                        <li><code>upload_max_filesize = 51M</code> <span style="color: #dc3545;">(actuellement: <?= $uploadMaxFilesize ?>)</span></li>
                    <?php endif; ?>
                    <?php if (!$postOk): ?>
                        <li><code>post_max_size = 52M</code> <span style="color: #dc3545;">(actuellement: <?= $postMaxSize ?>)</span></li>
                    <?php endif; ?>
                    <?php if (!$execTimeOk): ?>
                        <li><code>max_execution_time = 300</code> <span style="color: #ffc107;">(actuellement: <?= $maxExecutionTime ?>s)</span></li>
                    <?php endif; ?>
                    <?php if (!$inputTimeOk): ?>
                        <li><code>max_input_time = 300</code> <span style="color: #ffc107;">(actuellement: <?= $maxInputTime ?>s)</span></li>
                    <?php endif; ?>
                    <?php if (!$memoryOk): ?>
                        <li><code>memory_limit = 256M</code> <span style="color: #ffc107;">(actuellement: <?= $memoryLimit ?>)</span></li>
                    <?php endif; ?>
                </ul>
                <p style="margin-top: 15px; font-weight: 600; color: #dc3545;">
                    ⚠️ N'oubliez pas de redémarrer Apache après avoir modifié php.ini !
                </p>
                <p style="margin-top: 10px; color: #7f8c8d;">
                    📖 Pour plus d'informations, consultez <strong>CONFIGURATION_VIDEOS.md</strong>
                </p>
            </div>
        <?php endif; ?>
        
        <a href="tutorial/create" class="btn-return">
            📝 Créer un tutoriel
        </a>
    </div>
</body>
</html>

