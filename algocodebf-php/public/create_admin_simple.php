<?php

/**
 * Script simple de création d'administrateur
 * Version simplifiée qui utilise directement PDO
 * 
 * ATTENTION : Ce script doit être supprimé après utilisation pour des raisons de sécurité
 */

// Configuration de sécurité - désactiver après utilisation
$script_enabled = true; // Mettre à false après utilisation

if (!$script_enabled) {
    die('❌ Ce script a été désactivé pour des raisons de sécurité.');
}

// Configuration de la base de données pour la production
$db_config = [
    'host' => 'localhost',
    'dbname' => 'hub',
    'username' => 'root',
    'password' => '', // REMPLACEZ par votre vrai mot de passe
    'charset' => 'utf8mb4'
];

// Informations de l'administrateur à créer
$admin_data = [
    'nom' => 'SARE',
    'prenom' => 'MOHAMED',
    'email' => 'mhdcode7@gmail.com',
    'password' => 'Mohamedsare1!',
    'phone' => '+212 771 668 079',
    'role' => 'admin',
    'status' => 'active',
    'email_verified' => 1,
    'can_create_tutorial' => 1,
    'can_create_project' => 1,
    'university' => 'Administrateur',
    'city' => 'Casablanca',
    'bio' => 'Administrateur principal de HubTech',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

try {
    // Connexion directe à la base de données avec PDO
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "<h2>🔧 Script de création d'administrateur HubTech</h2>\n";
    echo "<hr>\n";

    // Vérifier si l'email existe déjà
    $checkQuery = "SELECT id, email, role FROM users WHERE email = ?";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([$admin_data['email']]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo "⚠️ <strong>Attention :</strong> Un utilisateur avec l'email {$admin_data['email']} existe déjà.\n";
        echo "<br>ID utilisateur : {$existingUser['id']}\n";
        echo "<br>Rôle actuel : {$existingUser['role']}\n";
        echo "<br><strong>Mise à jour en cours...</strong>\n";

        // Mettre à jour l'utilisateur existant
        $updateQuery = "UPDATE users SET 
            role = ?, 
            status = ?, 
            email_verified = ?, 
            can_create_tutorial = ?, 
            can_create_project = ?, 
            updated_at = ?
            WHERE id = ?";

        $stmt = $pdo->prepare($updateQuery);
        $result = $stmt->execute([
            $admin_data['role'],
            $admin_data['status'],
            $admin_data['email_verified'],
            $admin_data['can_create_tutorial'],
            $admin_data['can_create_project'],
            $admin_data['updated_at'],
            $existingUser['id']
        ]);

        if ($result) {
            echo "✅ <strong>Succès :</strong> L'utilisateur existant a été promu administrateur.\n";
            echo "<br>📧 Email : {$admin_data['email']}\n";
            echo "<br>👤 Nom : {$admin_data['prenom']} {$admin_data['nom']}\n";
            echo "<br>📱 Téléphone : {$admin_data['phone']}\n";
            echo "<br>🔑 Mot de passe : {$admin_data['password']}\n";
        } else {
            echo "❌ <strong>Erreur :</strong> Impossible de mettre à jour l'utilisateur.\n";
        }
    } else {
        // Créer un nouvel administrateur
        echo "🆕 <strong>Création d'un nouvel administrateur...</strong>\n";

        // Hacher le mot de passe
        $hashedPassword = password_hash($admin_data['password'], PASSWORD_DEFAULT);

        // Générer un token de vérification email
        $emailToken = bin2hex(random_bytes(32));

        // Requête d'insertion
        $insertQuery = "INSERT INTO users (
            nom, prenom, email, password_hash, phone, role, status, 
            email_verified, email_verification_token, can_create_tutorial, 
            can_create_project, university, city, bio, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($insertQuery);
        $result = $stmt->execute([
            $admin_data['nom'],
            $admin_data['prenom'],
            $admin_data['email'],
            $hashedPassword,
            $admin_data['phone'],
            $admin_data['role'],
            $admin_data['status'],
            $admin_data['email_verified'],
            $emailToken,
            $admin_data['can_create_tutorial'],
            $admin_data['can_create_project'],
            $admin_data['university'],
            $admin_data['city'],
            $admin_data['bio'],
            $admin_data['created_at'],
            $admin_data['updated_at']
        ]);

        if ($result) {
            $userId = $pdo->lastInsertId();
            echo "✅ <strong>Succès :</strong> Administrateur créé avec succès !\n";
            echo "<br>🆔 ID : {$userId}\n";
            echo "<br>📧 Email : {$admin_data['email']}\n";
            echo "<br>👤 Nom : {$admin_data['prenom']} {$admin_data['nom']}\n";
            echo "<br>📱 Téléphone : {$admin_data['phone']}\n";
            echo "<br>🔑 Mot de passe : {$admin_data['password']}\n";
            echo "<br>🎯 Rôle : Administrateur\n";
            echo "<br>✅ Email vérifié : Oui\n";
            echo "<br>📚 Permission tutoriels : Oui\n";
            echo "<br>🚀 Permission projets : Oui\n";
        } else {
            echo "❌ <strong>Erreur :</strong> Impossible de créer l'administrateur.\n";
        }
    }

    echo "<hr>\n";
    echo "<h3>🔐 Informations de connexion :</h3>\n";
    echo "<strong>URL de connexion :</strong> <a href='../auth/login'>Se connecter</a>\n";
    echo "<br><strong>Email :</strong> {$admin_data['email']}\n";
    echo "<br><strong>Mot de passe :</strong> {$admin_data['password']}\n";

    echo "<hr>\n";
    echo "<h3>⚠️ IMPORTANT - Sécurité :</h3>\n";
    echo "<p style='color: red; font-weight: bold;'>\n";
    echo "1. <strong>SUPPRIMEZ ce fichier</strong> après utilisation pour des raisons de sécurité\n";
    echo "<br>2. Changez le mot de passe par défaut lors de la première connexion\n";
    echo "<br>3. Activez l'authentification à deux facteurs si possible\n";
    echo "<br>4. Vérifiez que l'email est correctement configuré\n";
    echo "</p>\n";

    echo "<hr>\n";
    echo "<p><strong>Script exécuté le :</strong> " . date('Y-m-d H:i:s') . "</p>\n";
} catch (PDOException $e) {
    echo "❌ <strong>Erreur de base de données :</strong> " . $e->getMessage() . "\n";
    echo "<br>Vérifiez la configuration de la base de données.\n";
    echo "<br><strong>Détails :</strong>\n";
    echo "<br>- Host : {$db_config['host']}\n";
    echo "<br>- Database : {$db_config['dbname']}\n";
    echo "<br>- Username : {$db_config['username']}\n";
} catch (Exception $e) {
    echo "❌ <strong>Erreur fatale :</strong> " . $e->getMessage() . "\n";
}

// Désactiver le script après utilisation (décommentez la ligne suivante)
// $script_enabled = false;