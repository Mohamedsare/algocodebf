<?php
// S'assurer que BASE_URL est défini
if (!defined('BASE_URL')) {
    define('BASE_URL', '/HubTech'); // Le chemin reste /HubTech mais le nom du site est AlgoCodeBF
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Non Trouvée | AlgoCodeBF</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --dark-color: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .error-container {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            max-width: 700px;
        }

        .error-code {
            font-size: 12rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #fff, rgba(255, 255, 255, 0.5));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .error-icon {
            font-size: 8rem;
            margin-bottom: 30px;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }

        .error-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .error-message {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: white;
            color: #3498db;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .floating-element {
            position: absolute;
            font-size: 2rem;
            opacity: 0.1;
            animation: float-random 10s linear infinite;
        }

        @keyframes float-random {
            from {
                transform: translateY(100vh) rotate(0deg);
            }
            to {
                transform: translateY(-100px) rotate(360deg);
            }
        }

        .floating-element:nth-child(1) {
            left: 10%;
            animation-delay: 0s;
            animation-duration: 15s;
        }

        .floating-element:nth-child(2) {
            left: 20%;
            animation-delay: 2s;
            animation-duration: 12s;
        }

        .floating-element:nth-child(3) {
            left: 30%;
            animation-delay: 4s;
            animation-duration: 18s;
        }

        .floating-element:nth-child(4) {
            left: 50%;
            animation-delay: 1s;
            animation-duration: 14s;
        }

        .floating-element:nth-child(5) {
            left: 70%;
            animation-delay: 3s;
            animation-duration: 16s;
        }

        .floating-element:nth-child(6) {
            left: 80%;
            animation-delay: 5s;
            animation-duration: 13s;
        }

        .floating-element:nth-child(7) {
            left: 90%;
            animation-delay: 6s;
            animation-duration: 17s;
        }

        .search-box {
            margin-top: 40px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-input {
            width: 100%;
            padding: 15px 20px;
            border-radius: 30px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 8rem;
            }

            .error-icon {
                font-size: 5rem;
            }

            .error-title {
                font-size: 1.8rem;
            }

            .error-message {
                font-size: 1rem;
            }

            .error-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <div class="floating-element"><i class="fas fa-code"></i></div>
        <div class="floating-element"><i class="fas fa-laptop-code"></i></div>
        <div class="floating-element"><i class="fas fa-terminal"></i></div>
        <div class="floating-element"><i class="fas fa-bug"></i></div>
        <div class="floating-element"><i class="fas fa-server"></i></div>
        <div class="floating-element"><i class="fas fa-database"></i></div>
        <div class="floating-element"><i class="fas fa-cogs"></i></div>
    </div>

    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-robot"></i>
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title">Oups ! Page Non Trouvée</h1>
        <p class="error-message">
            La page que vous recherchez semble avoir disparu dans le cyberespace.<br>
            Elle a peut-être été déplacée, supprimée ou n'a jamais existé.
        </p>

        <div class="search-box">
            <input type="text" 
                   class="search-input" 
                   placeholder="Rechercher sur AlgoCodeBF..."
                   id="searchInput">
        </div>

        <div class="error-actions">
            <a href="<?= BASE_URL ?>/home/index" class="btn btn-primary">
                <i class="fas fa-home"></i> Retour à l'Accueil
            </a>
            <a href="<?= BASE_URL ?>/forum/index" class="btn btn-secondary">
                <i class="fas fa-comments"></i> Aller au Forum
            </a>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = `<?= BASE_URL ?>/search?q=${encodeURIComponent(query)}`;
                }
            }
        });

        // Auto-focus search after 2 seconds
        setTimeout(() => {
            document.getElementById('searchInput').focus();
        }, 2000);
    </script>
</body>
</html>

