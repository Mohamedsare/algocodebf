<?php
/**
 * Middleware de protection DDoS
 * À intégrer dans index.php pour protéger toutes les requêtes
 */

// Inclure la classe de protection DDoS
require_once __DIR__ . '/../app/Helpers/DDoSProtection.php';

/**
 * Initialiser la protection DDoS
 */
function initDDoSProtection()
{
    try {
        $ddosProtection = new DDoSProtection();
        $result = $ddosProtection->checkRequest();
        
        if (!$result['allowed']) {
            // Bloquer la requête
            http_response_code(429); // Too Many Requests
            
            // Afficher une page d'erreur personnalisée
            showDDoSErrorPage($result);
            exit;
        }
        
        return true;
    } catch (Exception $e) {
        // En cas d'erreur, permettre la requête mais logger l'erreur
        error_log("DDoS Protection Error: " . $e->getMessage());
        return true;
    }
}

/**
 * Afficher la page d'erreur DDoS
 */
function showDDoSErrorPage($result)
{
    $blockedUntil = $result['blocked_until'] ? date('d/m/Y à H:i', strtotime($result['blocked_until'])) : 'indéterminée';
    
    ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Temporairement Limité - HubTech</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #1B5E20, #2E7D32);
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .error-container {
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        padding: 40px;
        border-radius: 15px;
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        margin: 20px;
    }

    .error-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #FFD700;
    }

    .error-title {
        font-size: 2rem;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .error-message {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 30px;
        opacity: 0.9;
    }

    .error-details {
        background: rgba(255, 255, 255, 0.1);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .retry-button {
        background: linear-gradient(135deg, #B71C1C, #D32F2F);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 25px;
        font-size: 1.1rem;
        cursor: pointer;
        transition: transform 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .retry-button:hover {
        transform: translateY(-2px);
    }

    .footer {
        margin-top: 30px;
        font-size: 0.9rem;
        opacity: 0.7;
    }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-icon">🛡️</div>
        <h1 class="error-title">Accès Temporairement Limité</h1>

        <div class="error-message">
            Votre adresse IP a été temporairement limitée pour protéger notre serveur contre les attaques.
        </div>

        <div class="error-details">
            <strong>Raison :</strong> <?= htmlspecialchars($result['reason']) ?><br>
            <strong>Déblocage prévu :</strong> <?= $blockedUntil ?>
        </div>

        <a href="javascript:location.reload()" class="retry-button">
            Réessayer
        </a>

        <div class="footer">
            <p>HubTech - Protection DDoS Active</p>
            <p>Si vous pensez qu'il s'agit d'une erreur, contactez notre support.</p>
        </div>
    </div>
</body>

</html>
<?php
}

// Activer la protection DDoS si elle n'est pas désactivée
if (!defined('DISABLE_DDOS_PROTECTION') || !DISABLE_DDOS_PROTECTION) {
    initDDoSProtection();
}