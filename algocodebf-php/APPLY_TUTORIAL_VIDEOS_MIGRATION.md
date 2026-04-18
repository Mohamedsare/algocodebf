# 🚀 Guide rapide : Appliquer la migration des vidéos de tutoriels

## Problème
L'erreur indique que les tables `tutorial_videos` et `tutorial_chapters` n'existent pas dans la base de données.

## ✅ Solution : Exécuter la migration

### Méthode 1 : Via script PHP automatique (RECOMMANDÉ)

1. **Ouvrir votre navigateur**
2. **Visitez l'URL suivante :**
   ```
   http://localhost/HubTech/database/migrations/execute_tutorial_videos_migration.php
   ```
3. **Vérifiez le résultat** - Vous devriez voir :
   ```
   ✓ Connexion à la base de données réussie
   ✓ Table créée: tutorial_videos
   ✓ Table créée: tutorial_chapters
   ✅ Migration complétée avec succès!
   ```

### Méthode 2 : Via phpMyAdmin (Alternative)

1. **Ouvrir phpMyAdmin** : http://localhost/phpmyadmin
2. **Sélectionner la base** : Cliquer sur `hub` dans la colonne de gauche
3. **Aller dans SQL** : Cliquer sur l'onglet "SQL" en haut
4. **Copier le script** ci-dessous et le coller dans la zone de texte
5. **Exécuter** : Cliquer sur "Exécuter" en bas à droite

#### Script SQL à copier :

```sql
-- Migration: Ajout des tables pour plusieurs vidéos et chapitres
USE `hub`;

-- Table: tutorial_videos
CREATE TABLE IF NOT EXISTS `tutorial_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutorial_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Titre de la vidéo (ex: Chapitre 1, Introduction)',
  `description` text DEFAULT NULL COMMENT 'Description de la vidéo',
  `file_path` varchar(500) NOT NULL COMMENT 'Chemin du fichier vidéo',
  `file_name` varchar(255) NOT NULL COMMENT 'Nom original du fichier',
  `file_size` bigint(20) NOT NULL COMMENT 'Taille du fichier en octets',
  `duration` int(11) DEFAULT NULL COMMENT 'Durée en secondes',
  `order_index` int(11) DEFAULT 0 COMMENT 'Ordre d\'affichage',
  `views` int(11) DEFAULT 0 COMMENT 'Nombre de vues',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tutorial_id` (`tutorial_id`),
  KEY `idx_order_index` (`order_index`),
  CONSTRAINT `tutorial_videos_ibfk_1` FOREIGN KEY (`tutorial_id`) REFERENCES `tutorials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tutorial_chapters
CREATE TABLE IF NOT EXISTS `tutorial_chapters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutorial_id` int(11) NOT NULL,
  `chapter_number` int(11) NOT NULL COMMENT 'Numéro du chapitre',
  `title` varchar(255) NOT NULL COMMENT 'Titre du chapitre',
  `description` text DEFAULT NULL COMMENT 'Description du chapitre',
  `video_id` int(11) DEFAULT NULL COMMENT 'ID de la vidéo associée (optionnel)',
  `order_index` int(11) DEFAULT 0 COMMENT 'Ordre d\'affichage',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tutorial_id` (`tutorial_id`),
  KEY `idx_video_id` (`video_id`),
  KEY `idx_order_index` (`order_index`),
  CONSTRAINT `tutorial_chapters_ibfk_1` FOREIGN KEY (`tutorial_id`) REFERENCES `tutorials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tutorial_chapters_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `tutorial_videos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Méthode 3 : Via ligne de commande MySQL

```bash
# Ouvrir CMD
cd C:\xampp\mysql\bin

# Se connecter à MySQL
mysql.exe -u root -p

# Entrer le mot de passe (par défaut vide, juste appuyer sur Entrée)

# Ensuite dans MySQL :
USE hub;
SOURCE C:/xampp/htdocs/HubTech/database/migrations/add_tutorial_videos_and_chapters.sql;

# Vérification
SHOW TABLES LIKE 'tutorial_%';

# Quitter
EXIT;
```

---

## ✅ Vérification après migration

### 1. Vérifier que les tables existent :

```sql
SHOW TABLES LIKE 'tutorial_%';
```

Vous devriez voir :
- `tutorial_videos`
- `tutorial_chapters`

### 2. Vérifier la structure des tables :

```sql
DESCRIBE tutorial_videos;
DESCRIBE tutorial_chapters;
```

### 3. Tester la fonctionnalité :

1. Visitez : `http://localhost/HubTech/tutorial/create`
2. Essayez de créer un tutoriel avec des vidéos
3. ✅ L'erreur ne devrait plus apparaître

---

## 🔧 Dépannage

### Problème : "Table already exists"

**Solution :** C'est normal si les tables existent déjà. La migration utilise `CREATE TABLE IF NOT EXISTS`, donc elle ne recréera pas les tables existantes.

### Problème : "Foreign key constraint fails"

**Solution :** Vérifiez que la table `tutorials` existe :
```sql
SHOW TABLES LIKE 'tutorials';
```

Si elle n'existe pas, vous devez d'abord créer la base de données complète avec `hubtech_organized.sql`.

### Problème : "Access denied"

**Solution :** Vérifiez les identifiants de connexion dans `config/config.php` :
- `DB_HOST` : doit être `localhost`
- `DB_NAME` : doit être `hub` (ou votre nom de base)
- `DB_USER` : doit être `root` (ou votre utilisateur MySQL)
- `DB_PASS` : doit être vide `''` (ou votre mot de passe)

---

## 📝 Notes

- Les tables sont créées avec des contraintes de clé étrangère pour maintenir l'intégrité des données
- La suppression d'un tutoriel supprimera automatiquement ses vidéos et chapitres (CASCADE)
- La suppression d'une vidéo mettra à NULL le `video_id` dans les chapitres associés (SET NULL)

---

## ✅ Checklist finale

- [ ] Migration exécutée (script PHP ou SQL)
- [ ] Tables `tutorial_videos` et `tutorial_chapters` créées
- [ ] Vérification de la structure réussie
- [ ] Test de création de tutoriel avec vidéos réussi

---

**Une fois la migration appliquée, vous pourrez créer des tutoriels avec plusieurs vidéos et un sommaire !** 🎉

