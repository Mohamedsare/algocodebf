<?php
// Charger les paramètres du site
$site_name = $GLOBALS['site_settings']['site_name'] ?? 'AlgoCodeBF';
$site_description = $GLOBALS['site_settings']['site_description'] ?? 'Hub numérique des informaticiens du Burkina Faso';
$site_keywords = $GLOBALS['site_settings']['site_keywords'] ?? 'développement, programmation, tutoriels, projets';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($site_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($site_keywords) ?>">
    <title><?= $title ?? htmlspecialchars($site_name) . ' - ' . htmlspecialchars($site_description) ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/footer-legal.css?v=<?= time() ?>">

    <!-- Thème Patriotique Burkinabè 🇧🇫 (exclu de l'admin) -->
    <?php if (!isset($is_admin_page) && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === false): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/burkinabe-theme.css?v=<?= time() ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- FIX Navigation Mobile EN BAS - PRIORITÉ MAXIMALE -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/mobile-nav-fix.css?v=<?= time() ?>">

    <!-- FIX Navigation Mobile EN BAS - Script de secours -->
    <script src="<?= BASE_URL ?>/public/js/force-mobile-nav-bottom.js?v=<?= time() ?>"></script>

    <!-- Animation du drapeau burkinabè 🇧🇫 (exclu de l'admin) -->
    <?php if (!isset($is_admin_page) && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === false): ?>
    <script defer src="<?= BASE_URL ?>/public/js/burkina-flag-animation.js?v=<?= time() ?>"></script>
    <script defer src="<?= BASE_URL ?>/public/js/scroll-to-top.js?v=<?= time() ?>"></script>
    <?php endif; ?>

    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/y9lugfpj1jxq696s71q9d8t99y49ir7sxho2ogultsj83j8u/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/images/favicon.png">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="<?= BASE_URL ?>/home/index">
                    <?php
                    // Utiliser le texte du logo personnalisé ou le nom complet du site
                    $logo_text = $GLOBALS['site_settings']['site_logo_text'] ?? '';
                    
                    if (empty($logo_text)) {
                        // Utiliser le nom complet du site
                        $logo_text = $site_name;
                    }
                    ?>
                    <span class="logo"><?= htmlspecialchars($logo_text) ?></span>
                </a>
            </div>

            <button class="nav-toggle" id="navToggle" aria-label="Menu">
                <div class="hamburger-box">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </div>
            </button>

            <ul class="nav-menu" id="navMenu">
                <!-- Liens principaux visibles sur desktop ET dans le Dock mobile -->
                <li><a href="<?= BASE_URL ?>/home/index" data-label="Accueil"><i class="fas fa-home"></i><span
                            class="dock-label">Accueil</span></a></li>
                <li><a href="<?= BASE_URL ?>/forum/index" data-label="Forum"><i class="fas fa-comments"></i><span
                            class="dock-label">Forum</span></a></li>
                <li><a href="<?= BASE_URL ?>/tutorial/index" data-label="Tutoriels"><i
                            class="fas fa-graduation-cap"></i><span class="dock-label">Tutos</span></a></li>
                <li><a href="<?= BASE_URL ?>/job/index" data-label="Opportunités"><i class="fas fa-briefcase"></i><span
                            class="dock-label">Jobs</span></a></li>
                <li class="secondary-link"><a href="<?= BASE_URL ?>/project/index" data-label="Projets"><i
                            class="fas fa-code-branch"></i><span class="dock-label">Projets</span></a></li>
                <li class="secondary-link"><a href="<?= BASE_URL ?>/user/index" data-label="Membres"><i
                            class="fas fa-users"></i><span class="dock-label">Membres</span></a></li>
                <li><a href="<?= BASE_URL ?>/blog/index" data-label="Blog"><i class="fas fa-blog"></i><span
                            class="dock-label">Blog</span></a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Desktop : Dropdown -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-user-circle"></i> <?= $_SESSION['user_name'] ?? 'Mon Compte' ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= BASE_URL ?>/user/profile"><i class="fas fa-user"></i> Mon Profil</a></li>
                        <li><a href="<?= BASE_URL ?>/user/edit"><i class="fas fa-edit"></i> Modifier</a></li>
                        <li><a href="<?= BASE_URL ?>/message/inbox"><i class="fas fa-envelope"></i> Messages</a></li>
                        <li><a href="<?= BASE_URL ?>/user/leaderboard"><i class="fas fa-trophy"></i> Classement</a></li>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li>
                            <hr>
                        </li>
                        <li><a href="<?= BASE_URL ?>/admin/index"><i class="fas fa-cog"></i> Administration</a></li>
                        <?php endif; ?>
                        <li>
                            <hr>
                        </li>
                        <li><a href="<?= BASE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                <?php endif; ?>
            </ul>

            <!-- Menu burger - Liens secondaires -->
            <div class="burger-menu" id="burgerMenu">
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/user/profile"><i class="fas fa-user"></i> Mon Profil</a>
                <a href="<?= BASE_URL ?>/user/edit"><i class="fas fa-edit"></i> Modifier mon profil</a>
                <a href="<?= BASE_URL ?>/message/inbox"><i class="fas fa-envelope"></i> Messages</a>
                <hr>
                <a href="<?= BASE_URL ?>/job/index"><i class="fas fa-briefcase"></i> Opportunités</a>
                <a href="<?= BASE_URL ?>/project/index"><i class="fas fa-code-branch"></i> Projets</a>
                <a href="<?= BASE_URL ?>/user/index"><i class="fas fa-users"></i> Membres</a>
                <a href="<?= BASE_URL ?>/user/leaderboard"><i class="fas fa-trophy"></i> Classement</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <hr>
                <a href="<?= BASE_URL ?>/admin/index"><i class="fas fa-cog"></i> Administration</a>
                <?php endif; ?>
                <hr>
                <a href="<?= BASE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/job/index"><i class="fas fa-briefcase"></i> Opportunités</a>
                <a href="<?= BASE_URL ?>/project/index"><i class="fas fa-code-branch"></i> Projets</a>
                <a href="<?= BASE_URL ?>/user/index"><i class="fas fa-users"></i> Membres</a>
                <hr>
                <a href="<?= BASE_URL ?>/auth/login"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                <a href="<?= BASE_URL ?>/auth/register"><i class="fas fa-user-plus"></i> Créer un compte</a>
                <?php endif; ?>
            </div>

            <!-- Icônes desktop uniquement (utilisateur non connecté) -->
            <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="desktop-auth-icons">
                <a href="<?= BASE_URL ?>/auth/register" title="Créer un compte"><i class="fas fa-user-circle"></i></a>
                <a href="<?= BASE_URL ?>/auth/login" title="Se connecter"><i class="fas fa-sign-in-alt"></i></a>
            </div>
            <?php endif; ?>

            <!-- Icône de recherche -->
            <button class="search-toggle" id="searchToggle" type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <!-- Barre de recherche cachée -->
        <div class="search-overlay" id="searchOverlay">
            <div class="search-overlay-content">
                <form action="<?= BASE_URL ?>/home/search" method="GET" class="search-form-overlay">
                    <input type="text" name="q" placeholder="Rechercher des membres, posts, tutoriels..."
                        id="searchInput" required>
                    <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                </form>
                <button class="search-close" id="searchClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Messages flash -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <div class="container">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <div class="container">
            <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Contenu principal -->
    <main class="main-content">