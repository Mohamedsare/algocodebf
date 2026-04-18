<?php
/**
 * Script de création d'un administrateur
 * Ce script permet de créer un compte administrateur avec les informations spécifiées
 * 
 * ATTENTION : Ce script doit être supprimé après utilisation pour des raisons de sécurité
 */

// Inclure les fichiers nécessaires
require_once '../config/config.php';
require_once '../app/Core/Database.php';
require_once '../app/Core/Model.php';
require_once '../app/Models/User.php';
require_once '../app/Helpers/Security.php';

// Configuration spécifique pour la production
// Vérifier si nous sommes en production et ajuster la config si nécessaire
if (strpos($_SERVER['HTTP_HOST'], 'yasmis.com') !== false) {
    // Configuration pour le serveur de production
    if (!defined('DB_HOST')) {
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'u330028981_algodb');
        define('DB_USER', 'u330028981_algodb');
        define('DB_PASS', 'votre_mot_de_passe_db'); // À remplacer par le vrai mot de passe
        define('DB_CHARSET', 'utf8mb4');
    }
}

// Configuration de sécurité - désactiver après utilisation
$script_enabled = true; // Mettre à false après utilisation

if (!$script_enabled) {
    die('❌ Ce script a été désactivé pour des raisons de sécurité.');
}

// Informations de l'administrateur à créer
$admin_data = [
    'nom' => 'SARE',
    'prenom' => 'MOHAMED',
    'email' => 'mhdcode7@gmail.com',
    'password' => 'Mohamedsare1!',
    'phone' => '+212 771 668 079',
    'role' => 'admin',
    'status' => 'active',
    'email_verified' => true,
    'can_create_tutorial' => 1,
    'can_create_project' => 1,
    'university' => 'Administrateur',
    'city' => 'Casablanca',
    'bio' => 'Administrateur principal de HubTech',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

try {
    // Initialiser la connexion à la base de données
    $db = Database::getInstance();
    $userModel = new User();
    
    echo "<h2>🔧 Script de création d'administrateur HubTech</h2>\n";
    echo "<hr>\n";
    
    // Vérifier si l'email existe déjà
    if ($userModel->emailExists($admin_data['email'])) {
        echo "⚠️ <strong>Attention :</strong> Un utilisateur avec l'email {$admin_data['email']} existe déjà.\n";
        echo "<br>Voulez-vous continuer et mettre à jour cet utilisateur en administrateur ? (Oui/Non)\n";
        
        // En mode web, on continue automatiquement
        echo "<br><strong>Continuité automatique...</strong>\n";
        
        // Mettre à jour l'utilisateur existant
        $existingUser = $userModel->findBy('email', $admin_data['email']);
        if ($existingUser) {
            $updateData = [
                'role' => 'admin',
                'status' => 'active',
                'email_verified' => true,
                'can_create_tutorial' => 1,
                'can_create_project' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($userModel->update($existingUser['id'], $updateData)) {
                echo "✅ <strong>Succès :</strong> L'utilisateur existant a été promu administrateur.\n";
                echo "<br>📧 Email : {$admin_data['email']}\n";
                echo "<br>👤 Nom : {$admin_data['prenom']} {$admin_data['nom']}\n";
                echo "<br>📱 Téléphone : {$admin_data['phone']}\n";
                echo "<br>🔑 Mot de passe : {$admin_data['password']}\n";
            } else {
                echo "❌ <strong>Erreur :</strong> Impossible de mettre à jour l'utilisateur.\n";
            }
        }
    } else {
        // Créer un nouvel administrateur
        echo "🆕 <strong>Création d'un nouvel administrateur...</strong>\n";
        
        // Utiliser la méthode register du modèle User
        $userId = $userModel->register($admin_data);
        
        if ($userId) {
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
    
} catch (Exception $e) {
    echo "❌ <strong>Erreur fatale :</strong> " . $e->getMessage() . "\n";
    echo "<br>Vérifiez la configuration de la base de données.\n";
}

// Désactiver le script après utilisation (décommentez la ligne suivante)
// $script_enabled = false;
?>