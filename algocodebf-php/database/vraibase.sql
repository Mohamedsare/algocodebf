-- ================================================================
-- Sauvegarde de la base de données HubTech
-- Date: 2025-10-23 18:17:54
-- Base de données: hubtech
-- Type: Structure + Données
-- ================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


USE `u330028981_algodb`;

-- ================================================================
-- Table: activity_logs
-- ================================================================
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: applications
-- ================================================================
DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` enum('pending','viewed','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_application` (`job_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: badges
-- ================================================================
DROP TABLE IF EXISTS `badges`;
CREATE TABLE `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `requirement_type` varchar(50) DEFAULT NULL COMMENT 'posts_count, tutorials_count, etc.',
  `requirement_value` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `badges`
INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `requirement_type`, `requirement_value`, `created_at`) VALUES ('1', 'Nouveau Membre', 'Bienvenue dans la communauté!', '👋', 'registration', '1', '2025-10-09 20:37:37');
INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `requirement_type`, `requirement_value`, `created_at`) VALUES ('2', 'Contributeur Actif', 'A publié au moins 10 posts', '💬', 'posts_count', '10', '2025-10-09 20:37:37');
INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `requirement_type`, `requirement_value`, `created_at`) VALUES ('3', 'Expert BF', 'A publié au moins 5 tutoriels', '🎓', 'tutorials_count', '5', '2025-10-09 20:37:37');
INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `requirement_type`, `requirement_value`, `created_at`) VALUES ('4', 'Mentor', 'A aidé plus de 20 membres', '🏆', 'helpful_answers', '20', '2025-10-09 20:37:37');
INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `requirement_type`, `requirement_value`, `created_at`) VALUES ('5', 'Top Codeur', 'A reçu plus de 50 likes', '⭐', 'likes_received', '50', '2025-10-09 20:37:37');
INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `requirement_type`, `requirement_value`, `created_at`) VALUES ('6', 'Collaborateur', 'A participé à au moins 3 projets', '🤝', 'projects_count', '3', '2025-10-09 20:37:37');

-- ================================================================
-- Table: blog_categories
-- ================================================================
DROP TABLE IF EXISTS `blog_categories`;
CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fa-folder',
  `color` varchar(20) DEFAULT '#667eea',
  `posts_count` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `blog_categories`
INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `posts_count`, `status`, `created_at`, `updated_at`) VALUES ('1', 'Actualités', 'actualit-es', 'Les dernières nouvelles du monde tech', 'fa-newspaper', '#66ead0', '0', 'active', '2025-10-11 04:42:54', '2025-10-11 04:47:47');
INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `posts_count`, `status`, `created_at`, `updated_at`) VALUES ('4', 'Carrière', 'carriere', 'Conseils carrière et développement professionnel', 'fa-briefcase', '#ffc107', '0', 'active', '2025-10-11 04:42:54', '2025-10-11 04:42:54');
INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `posts_count`, `status`, `created_at`, `updated_at`) VALUES ('6', 'Événements', 'evenements', 'Conférences, hackathons et événements tech', 'fa-calendar-alt', '#fd7e14', '0', 'active', '2025-10-11 04:42:54', '2025-10-11 04:42:54');

-- ================================================================
-- Table: blog_posts
-- ================================================================
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` text NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author_id` (`author_id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_published` (`published_at`),
  KEY `idx_category` (`category_id`),
  FULLTEXT KEY `ft_search` (`title`,`excerpt`,`content`),
  CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blog_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: cache_store
-- ================================================================
DROP TABLE IF EXISTS `cache_store`;
CREATE TABLE `cache_store` (
  `cache_key` varchar(255) NOT NULL,
  `cache_value` longtext NOT NULL,
  `expires_at` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `access_count` int(11) DEFAULT 0,
  `last_accessed` int(11) NOT NULL,
  PRIMARY KEY (`cache_key`),
  KEY `idx_expires` (`expires_at`),
  KEY `idx_access_count` (`access_count`),
  KEY `idx_last_accessed` (`last_accessed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `cache_store`
INSERT INTO `cache_store` (`cache_key`, `cache_value`, `expires_at`, `created_at`, `access_count`, `last_accessed`) VALUES ('dashboard_data_5', 'a:4:{s:5:\"stats\";a:4:{s:11:\"total_users\";i:1;s:11:\"total_posts\";i:0;s:15:\"total_tutorials\";i:1;s:14:\"total_projects\";i:0;}s:12:\"recent_posts\";a:0:{}s:17:\"popular_tutorials\";a:1:{i:0;a:19:{s:2:\"id\";i:3;s:7:\"user_id\";i:5;s:5:\"title\";s:36:\"HTML5 — DU DÉBUTANT À L’EXPERT\";s:11:\"description\";s:160:\"Maîtriser HTML5 à un niveau professionnel et construire un Portfolio personnel entièrement en HTML5 (avant le design CSS qui viendra dans le prochain cours).\";s:7:\"content\";s:17930:\"&lt;p&gt;&amp;nbsp;&lt;/p&gt;
&lt;hr&gt;
&lt;h1&gt;🌐 COURS COMPLET HTML5 &amp;mdash; DU D&amp;Eacute;BUTANT &amp;Agrave; L&amp;rsquo;EXPERT&lt;/h1&gt;
&lt;p&gt;&lt;strong&gt;Objectif :&lt;/strong&gt; Ma&amp;icirc;triser HTML5 &amp;agrave; un niveau professionnel et construire un &lt;strong&gt;Portfolio personnel&lt;/strong&gt; enti&amp;egrave;rement en HTML5 (avant le design CSS qui viendra dans le prochain cours).&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;🧭 INTRODUCTION G&amp;Eacute;N&amp;Eacute;RALE&lt;/h2&gt;
&lt;h3&gt;🔹 Qu&amp;rsquo;est-ce que HTML ?&lt;/h3&gt;
&lt;p&gt;&lt;strong&gt;HTML (HyperText Markup Language)&lt;/strong&gt; est le &lt;strong&gt;langage de base du web&lt;/strong&gt;.&lt;br&gt;Il sert &amp;agrave; &lt;strong&gt;structurer le contenu&lt;/strong&gt; d&amp;rsquo;une page : titres, paragraphes, images, liens, tableaux, formulaires, etc.&lt;/p&gt;
&lt;p&gt;👉 &lt;strong&gt;HTML &amp;ne; Langage de programmation&lt;/strong&gt;&lt;br&gt;C&amp;rsquo;est un &lt;strong&gt;langage de balisage&lt;/strong&gt; : il d&amp;eacute;crit le r&amp;ocirc;le du contenu, pas son apparence.&lt;/p&gt;
&lt;h3&gt;🔹 Outils n&amp;eacute;cessaires&lt;/h3&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;&amp;Eacute;diteur de texte&lt;/strong&gt; : Visual Studio Code &amp;amp;&amp;amp; SublimeText(recommand&amp;eacute;)&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Navigateur moderne&lt;/strong&gt; : Chrome, Firefox, Edge&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Extension Live Server&lt;/strong&gt; (optionnelle pour rafra&amp;icirc;chir automatiquement la page)&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;📘 MODULE 1 &amp;mdash; STRUCTURE DE BASE D&amp;rsquo;UNE PAGE HTML5&lt;/h2&gt;
&lt;h3&gt;🔹 Syntaxe de base&lt;/h3&gt;
&lt;p&gt;Chaque document HTML5 commence ainsi :&lt;/p&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;!DOCTYPE html&amp;gt;
&amp;lt;html lang=&quot;fr&quot;&amp;gt;
&amp;lt;head&amp;gt;
  &amp;lt;meta charset=&quot;UTF-8&quot;&amp;gt;
  &amp;lt;title&amp;gt;Ma premi&amp;egrave;re page&amp;lt;/title&amp;gt;
&amp;lt;/head&amp;gt;
&amp;lt;body&amp;gt;
  &amp;lt;h1&amp;gt;Bonjour le monde !&amp;lt;/h1&amp;gt;
  &amp;lt;p&amp;gt;Ceci est ma premi&amp;egrave;re page HTML5.&amp;lt;/p&amp;gt;
&amp;lt;/body&amp;gt;
&amp;lt;/html&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧩 &lt;strong&gt;Analyse&lt;/strong&gt;&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;!DOCTYPE html&amp;gt;&lt;/code&gt; &amp;rarr; indique la version HTML5.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;html lang=&quot;fr&quot;&amp;gt;&lt;/code&gt; &amp;rarr; langue du document.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;head&amp;gt;&lt;/code&gt; &amp;rarr; contient les m&amp;eacute;tadonn&amp;eacute;es (titre, encodage, liens vers CSS/JS).&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;body&amp;gt;&lt;/code&gt; &amp;rarr; contient le contenu visible.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e un fichier &lt;code&gt;index.html&lt;/code&gt; avec :&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;ton nom comme titre de page ;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;un &lt;code&gt;&amp;lt;h1&amp;gt;&lt;/code&gt; &quot;Bienvenue sur mon site&quot; ;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;un paragraphe de pr&amp;eacute;sentation.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;📗 MODULE 2 &amp;mdash; TEXTE ET CONTENU&lt;/h2&gt;
&lt;h3&gt;🔹 Titres et paragraphes&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;h1&amp;gt;Titre principal&amp;lt;/h1&amp;gt;
&amp;lt;h2&amp;gt;Sous-titre&amp;lt;/h2&amp;gt;
&amp;lt;h3&amp;gt;Sous-sous-titre&amp;lt;/h3&amp;gt;
&amp;lt;p&amp;gt;Voici un paragraphe de texte.&amp;lt;/p&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧩 Les titres vont de &lt;code&gt;&amp;lt;h1&amp;gt;&lt;/code&gt; &amp;agrave; &lt;code&gt;&amp;lt;h6&amp;gt;&lt;/code&gt; (le plus important au moins important).&lt;/p&gt;
&lt;h3&gt;🔹 Mise en valeur&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;p&amp;gt;Voici un &amp;lt;strong&amp;gt;texte important&amp;lt;/strong&amp;gt; et un &amp;lt;em&amp;gt;texte en italique&amp;lt;/em&amp;gt;.&amp;lt;/p&amp;gt;
&amp;lt;p&amp;gt;Un texte &amp;lt;mark&amp;gt;surlign&amp;eacute;&amp;lt;/mark&amp;gt; et un &amp;lt;small&amp;gt;texte plus petit&amp;lt;/small&amp;gt;.&amp;lt;/p&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Sauts de ligne et s&amp;eacute;parations&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;p&amp;gt;Premi&amp;egrave;re ligne&amp;lt;br&amp;gt;Deuxi&amp;egrave;me ligne&amp;lt;/p&amp;gt;
&amp;lt;hr&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e une section avec ton nom, ta biographie courte, et des mots mis en valeur.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📙 MODULE 3 &amp;mdash; LES LIENS ET LES IMAGES&lt;/h2&gt;
&lt;h3&gt;🔹 Les liens (hyperliens)&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;a href=&quot;https://openai.com&quot; target=&quot;_blank&quot;&amp;gt;Visitez OpenAI&amp;lt;/a&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;href&lt;/code&gt; = destination du lien.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;target=&quot;_blank&quot;&lt;/code&gt; = ouvre dans un nouvel onglet.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;h3&gt;🔹 Liens internes&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;a href=&quot;contact.html&quot;&amp;gt;Page de contact&amp;lt;/a&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Images&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;img src=&quot;images/photo.jpg&quot; alt=&quot;Description de l&#039;image&quot;&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;alt&lt;/code&gt; = description (accessibilit&amp;eacute; / SEO).&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Utiliser toujours un texte alternatif pertinent.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e une page avec :&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;un lien vers ton profil GitHub ;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;une image avec une l&amp;eacute;gende sous forme de paragraphe.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;📒 MODULE 4 &amp;mdash; LES LISTES&lt;/h2&gt;
&lt;h3&gt;🔹 Liste &amp;agrave; puces&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;ul&amp;gt;
  &amp;lt;li&amp;gt;HTML&amp;lt;/li&amp;gt;
  &amp;lt;li&amp;gt;CSS&amp;lt;/li&amp;gt;
  &amp;lt;li&amp;gt;JavaScript&amp;lt;/li&amp;gt;
&amp;lt;/ul&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Liste num&amp;eacute;rot&amp;eacute;e&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;ol&amp;gt;
  &amp;lt;li&amp;gt;&amp;Eacute;tape 1&amp;lt;/li&amp;gt;
  &amp;lt;li&amp;gt;&amp;Eacute;tape 2&amp;lt;/li&amp;gt;
&amp;lt;/ol&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Liste imbriqu&amp;eacute;e&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;ul&amp;gt;
  &amp;lt;li&amp;gt;Frontend
    &amp;lt;ul&amp;gt;
      &amp;lt;li&amp;gt;HTML&amp;lt;/li&amp;gt;
      &amp;lt;li&amp;gt;CSS&amp;lt;/li&amp;gt;
    &amp;lt;/ul&amp;gt;
  &amp;lt;/li&amp;gt;
&amp;lt;/ul&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e une liste de tes comp&amp;eacute;tences techniques et hobbies.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📘 MODULE 5 &amp;mdash; LES TABLEAUX&lt;/h2&gt;
&lt;h3&gt;🔹 Structure&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;table border=&quot;1&quot;&amp;gt;
  &amp;lt;caption&amp;gt;Emploi du temps&amp;lt;/caption&amp;gt;
  &amp;lt;tr&amp;gt;
    &amp;lt;th&amp;gt;Jour&amp;lt;/th&amp;gt;
    &amp;lt;th&amp;gt;Cours&amp;lt;/th&amp;gt;
  &amp;lt;/tr&amp;gt;
  &amp;lt;tr&amp;gt;
    &amp;lt;td&amp;gt;Lundi&amp;lt;/td&amp;gt;
    &amp;lt;td&amp;gt;HTML5&amp;lt;/td&amp;gt;
  &amp;lt;/tr&amp;gt;
&amp;lt;/table&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Fusion de cellules&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;td rowspan=&quot;2&quot;&amp;gt;Fusion verticale&amp;lt;/td&amp;gt;
&amp;lt;td colspan=&quot;2&quot;&amp;gt;Fusion horizontale&amp;lt;/td&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e un tableau simple de ton planning de la semaine.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📕 MODULE 6 &amp;mdash; FORMULAIRES&lt;/h2&gt;
&lt;h3&gt;🔹 Structure&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;form action=&quot;#&quot; method=&quot;post&quot;&amp;gt;
  &amp;lt;label for=&quot;name&quot;&amp;gt;Nom :&amp;lt;/label&amp;gt;
  &amp;lt;input type=&quot;text&quot; id=&quot;name&quot; name=&quot;name&quot; required&amp;gt;

  &amp;lt;label for=&quot;email&quot;&amp;gt;Email :&amp;lt;/label&amp;gt;
  &amp;lt;input type=&quot;email&quot; id=&quot;email&quot; name=&quot;email&quot; required&amp;gt;

  &amp;lt;label for=&quot;message&quot;&amp;gt;Message :&amp;lt;/label&amp;gt;
  &amp;lt;textarea id=&quot;message&quot; name=&quot;message&quot;&amp;gt;&amp;lt;/textarea&amp;gt;

  &amp;lt;button type=&quot;submit&quot;&amp;gt;Envoyer&amp;lt;/button&amp;gt;
&amp;lt;/form&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Types modernes HTML5&lt;/h3&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;email&lt;/code&gt;, &lt;code&gt;date&lt;/code&gt;, &lt;code&gt;color&lt;/code&gt;, &lt;code&gt;range&lt;/code&gt;, &lt;code&gt;file&lt;/code&gt;, &lt;code&gt;number&lt;/code&gt;, &lt;code&gt;url&lt;/code&gt;, etc.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e un formulaire de contact avec les champs nom, email, message et un bouton &amp;ldquo;Envoyer&amp;rdquo;.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📗 MODULE 7 &amp;mdash; MULTIM&amp;Eacute;DIA&lt;/h2&gt;
&lt;h3&gt;🔹 Vid&amp;eacute;o&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;video controls width=&quot;400&quot;&amp;gt;
  &amp;lt;source src=&quot;video.mp4&quot; type=&quot;video/mp4&quot;&amp;gt;
  Votre navigateur ne supporte pas la vid&amp;eacute;o.
&amp;lt;/video&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Audio&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;audio controls&amp;gt;
  &amp;lt;source src=&quot;musique.mp3&quot; type=&quot;audio/mp3&quot;&amp;gt;
&amp;lt;/audio&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Int&amp;egrave;gre une courte vid&amp;eacute;o ou un fichier audio sur ta page.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📙 MODULE 8 &amp;mdash; STRUCTURE S&amp;Eacute;MANTIQUE HTML5&lt;/h2&gt;
&lt;h3&gt;🔹 Pourquoi ?&lt;/h3&gt;
&lt;p&gt;Les balises s&amp;eacute;mantiques donnent &lt;strong&gt;du sens&lt;/strong&gt; au contenu &amp;rarr; important pour SEO, accessibilit&amp;eacute;, et clart&amp;eacute;.&lt;/p&gt;
&lt;h3&gt;🔹 Balises principales&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;header&amp;gt;En-t&amp;ecirc;te du site&amp;lt;/header&amp;gt;
&amp;lt;nav&amp;gt;Navigation&amp;lt;/nav&amp;gt;
&amp;lt;main&amp;gt;
  &amp;lt;section&amp;gt;
    &amp;lt;article&amp;gt;
      &amp;lt;h2&amp;gt;Article 1&amp;lt;/h2&amp;gt;
      &amp;lt;p&amp;gt;Contenu...&amp;lt;/p&amp;gt;
    &amp;lt;/article&amp;gt;
  &amp;lt;/section&amp;gt;
&amp;lt;/main&amp;gt;
&amp;lt;aside&amp;gt;Informations compl&amp;eacute;mentaires&amp;lt;/aside&amp;gt;
&amp;lt;footer&amp;gt;Pied de page&amp;lt;/footer&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Structure ta page avec ces balises sans encore les styliser.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📘 MODULE 9 &amp;mdash; APIs HTML5 ESSENTIELLES&lt;/h2&gt;
&lt;h3&gt;🔹 Stockage local&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;script&amp;gt;
  localStorage.setItem(&#039;nom&#039;, &#039;Alice&#039;);
  alert(localStorage.getItem(&#039;nom&#039;));
&amp;lt;/script&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 G&amp;eacute;olocalisation (introduction)&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;script&amp;gt;
navigator.geolocation.getCurrentPosition(pos =&amp;gt; {
  console.log(pos.coords.latitude, pos.coords.longitude);
});
&amp;lt;/script&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Teste le stockage local avec ton nom et affiche-le dans une alerte.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📗 MODULE 10 &amp;mdash; BONNES PRATIQUES&lt;/h2&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;Utiliser des balises s&amp;eacute;mantiques appropri&amp;eacute;es.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Toujours fournir des textes alternatifs aux images.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Valider le code sur &lt;a href=&quot;https://validator.w3.org/&quot;&gt;https://validator.w3.org/&lt;/a&gt;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Indenter correctement ton code.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;S&amp;eacute;parer contenu (HTML), style (CSS) et logique (JS).&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;🎯 MODULE 11 &amp;mdash; MINI-PROJETS&lt;/h2&gt;
&lt;ol&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Page de blog simple&lt;/strong&gt; : titre, paragraphe, image.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Page de contact&lt;/strong&gt; : formulaire complet.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Galerie d&amp;rsquo;images&lt;/strong&gt; : images organis&amp;eacute;es en grilles (bient&amp;ocirc;t stylis&amp;eacute;es avec CSS).&lt;/p&gt;
&lt;/li&gt;
&lt;/ol&gt;
&lt;hr&gt;
&lt;h2&gt;💼 PROJET FINAL &amp;mdash; PORTFOLIO HTML5 PUR&lt;/h2&gt;
&lt;h3&gt;🔹 Objectif :&lt;/h3&gt;
&lt;p&gt;Cr&amp;eacute;er ton &lt;strong&gt;Portfolio personnel&lt;/strong&gt; enti&amp;egrave;rement structur&amp;eacute; en HTML5, pr&amp;ecirc;t pour le design CSS.&lt;/p&gt;
&lt;h3&gt;🔹 Structure de fichiers&lt;/h3&gt;
&lt;pre&gt;&lt;code&gt;/portfolio
│
├── index.html
├── about.html
├── projects.html
└── contact.html
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Exemple : &lt;code&gt;index.html&lt;/code&gt;&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;!DOCTYPE html&amp;gt;
&amp;lt;html lang=&quot;fr&quot;&amp;gt;
&amp;lt;head&amp;gt;
  &amp;lt;meta charset=&quot;UTF-8&quot;&amp;gt;
  &amp;lt;title&amp;gt;Mon Portfolio&amp;lt;/title&amp;gt;
&amp;lt;/head&amp;gt;
&amp;lt;body&amp;gt;
  &amp;lt;header&amp;gt;
    &amp;lt;h1&amp;gt;Mon Portfolio&amp;lt;/h1&amp;gt;
    &amp;lt;nav&amp;gt;
      &amp;lt;a href=&quot;index.html&quot;&amp;gt;Accueil&amp;lt;/a&amp;gt;
      &amp;lt;a href=&quot;about.html&quot;&amp;gt;&amp;Agrave; propos&amp;lt;/a&amp;gt;
      &amp;lt;a href=&quot;projects.html&quot;&amp;gt;Projets&amp;lt;/a&amp;gt;
      &amp;lt;a href=&quot;contact.html&quot;&amp;gt;Contact&amp;lt;/a&amp;gt;
    &amp;lt;/nav&amp;gt;
  &amp;lt;/header&amp;gt;

  &amp;lt;main&amp;gt;
    &amp;lt;section&amp;gt;
      &amp;lt;h2&amp;gt;Bienvenue&amp;lt;/h2&amp;gt;
      &amp;lt;p&amp;gt;Je suis &amp;lt;strong&amp;gt;Nom Pr&amp;eacute;nom&amp;lt;/strong&amp;gt;, d&amp;eacute;veloppeur web passionn&amp;eacute;.&amp;lt;/p&amp;gt;
    &amp;lt;/section&amp;gt;

    &amp;lt;section&amp;gt;
      &amp;lt;h2&amp;gt;Mes projets r&amp;eacute;cents&amp;lt;/h2&amp;gt;
      &amp;lt;article&amp;gt;
        &amp;lt;h3&amp;gt;Projet 1&amp;lt;/h3&amp;gt;
        &amp;lt;p&amp;gt;Description du projet...&amp;lt;/p&amp;gt;
      &amp;lt;/article&amp;gt;
    &amp;lt;/section&amp;gt;
  &amp;lt;/main&amp;gt;

  &amp;lt;footer&amp;gt;
    &amp;lt;p&amp;gt;&amp;amp;copy; 2025 Mon Nom &amp;mdash; Tous droits r&amp;eacute;serv&amp;eacute;s.&amp;lt;/p&amp;gt;
  &amp;lt;/footer&amp;gt;
&amp;lt;/body&amp;gt;
&amp;lt;/html&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Les autres pages&lt;/h3&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;about.html&lt;/code&gt; &amp;rarr; pr&amp;eacute;sentation d&amp;eacute;taill&amp;eacute;e de toi.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;projects.html&lt;/code&gt; &amp;rarr; liste de projets avec descriptions.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;contact.html&lt;/code&gt; &amp;rarr; formulaire de contact.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice final :&lt;/strong&gt;&lt;br&gt;Construis ton Portfolio complet avec 4 pages et navigation fonctionnelle.&lt;br&gt;Teste-le sur ton navigateur, puis pr&amp;eacute;pare-toi &amp;agrave; l&amp;rsquo;&amp;eacute;tape suivante : le &lt;strong&gt;design CSS&lt;/strong&gt;.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;🏁 F&amp;Eacute;LICITATIONS !&lt;/h2&gt;
&lt;p&gt;Tu ma&amp;icirc;trises maintenant :&lt;br&gt;✅ La structure HTML5&lt;br&gt;✅ Les balises s&amp;eacute;mantiques&lt;br&gt;✅ Les formulaires, tableaux, multim&amp;eacute;dia&lt;br&gt;✅ Les bonnes pratiques professionnelles&lt;/p&gt;
&lt;p&gt;👉 &lt;strong&gt;Prochaine &amp;eacute;tape :&lt;/strong&gt; on stylisera tout &amp;ccedil;a avec &lt;strong&gt;un cours CSS complet&lt;/strong&gt; (couleurs, mise en page, animations, responsive design).&lt;/p&gt;
&lt;hr&gt;
&lt;p&gt;&amp;nbsp;&lt;/p&gt;\";s:4:\"type\";s:0:\"\";s:8:\"category\";s:3:\"Web\";s:5:\"level\";s:9:\"Débutant\";s:9:\"file_path\";N;s:13:\"external_link\";s:0:\"\";s:5:\"views\";i:2;s:9:\"downloads\";i:0;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-23 16:27:12\";s:10:\"updated_at\";s:19:\"2025-10-23 16:34:29\";s:6:\"prenom\";s:7:\"MOHAMED\";s:3:\"nom\";s:4:\"SARE\";s:10:\"photo_path\";s:73:\"uploads/profiles/profile_1761235456_94e43f9634f444c9e8b2239d6c5b694f.jpeg\";s:11:\"likes_count\";i:1;}}s:12:\"recent_blogs\";a:0:{}}', '1761243570', '1761243450', '3', '1761243460');
INSERT INTO `cache_store` (`cache_key`, `cache_value`, `expires_at`, `created_at`, `access_count`, `last_accessed`) VALUES ('dashboard_data_6', 'a:4:{s:5:\"stats\";a:4:{s:11:\"total_users\";i:2;s:11:\"total_posts\";i:1;s:15:\"total_tutorials\";i:1;s:14:\"total_projects\";i:0;}s:12:\"recent_posts\";a:1:{i:0;a:16:{s:2:\"id\";i:8;s:7:\"user_id\";i:5;s:8:\"category\";s:5:\"Autre\";s:5:\"title\";s:36:\"Programme de DUT Génie Informatique\";s:4:\"body\";s:142:\"Ce programme vous permet d\'avoir des bases solides en informatique de façon générale et de vous spécialiser plus tard dans votre formation\";s:5:\"views\";i:3;s:9:\"is_pinned\";i:0;s:9:\"is_locked\";i:0;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-23 14:48:58\";s:10:\"updated_at\";s:19:\"2025-10-23 17:08:44\";s:6:\"prenom\";s:7:\"MOHAMED\";s:3:\"nom\";s:4:\"SARE\";s:10:\"photo_path\";s:73:\"uploads/profiles/profile_1761235456_94e43f9634f444c9e8b2239d6c5b694f.jpeg\";s:14:\"comments_count\";i:0;s:11:\"likes_count\";i:0;}}s:17:\"popular_tutorials\";a:1:{i:0;a:19:{s:2:\"id\";i:3;s:7:\"user_id\";i:5;s:5:\"title\";s:36:\"HTML5 — DU DÉBUTANT À L’EXPERT\";s:11:\"description\";s:160:\"Maîtriser HTML5 à un niveau professionnel et construire un Portfolio personnel entièrement en HTML5 (avant le design CSS qui viendra dans le prochain cours).\";s:7:\"content\";s:17930:\"&lt;p&gt;&amp;nbsp;&lt;/p&gt;
&lt;hr&gt;
&lt;h1&gt;🌐 COURS COMPLET HTML5 &amp;mdash; DU D&amp;Eacute;BUTANT &amp;Agrave; L&amp;rsquo;EXPERT&lt;/h1&gt;
&lt;p&gt;&lt;strong&gt;Objectif :&lt;/strong&gt; Ma&amp;icirc;triser HTML5 &amp;agrave; un niveau professionnel et construire un &lt;strong&gt;Portfolio personnel&lt;/strong&gt; enti&amp;egrave;rement en HTML5 (avant le design CSS qui viendra dans le prochain cours).&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;🧭 INTRODUCTION G&amp;Eacute;N&amp;Eacute;RALE&lt;/h2&gt;
&lt;h3&gt;🔹 Qu&amp;rsquo;est-ce que HTML ?&lt;/h3&gt;
&lt;p&gt;&lt;strong&gt;HTML (HyperText Markup Language)&lt;/strong&gt; est le &lt;strong&gt;langage de base du web&lt;/strong&gt;.&lt;br&gt;Il sert &amp;agrave; &lt;strong&gt;structurer le contenu&lt;/strong&gt; d&amp;rsquo;une page : titres, paragraphes, images, liens, tableaux, formulaires, etc.&lt;/p&gt;
&lt;p&gt;👉 &lt;strong&gt;HTML &amp;ne; Langage de programmation&lt;/strong&gt;&lt;br&gt;C&amp;rsquo;est un &lt;strong&gt;langage de balisage&lt;/strong&gt; : il d&amp;eacute;crit le r&amp;ocirc;le du contenu, pas son apparence.&lt;/p&gt;
&lt;h3&gt;🔹 Outils n&amp;eacute;cessaires&lt;/h3&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;&amp;Eacute;diteur de texte&lt;/strong&gt; : Visual Studio Code &amp;amp;&amp;amp; SublimeText(recommand&amp;eacute;)&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Navigateur moderne&lt;/strong&gt; : Chrome, Firefox, Edge&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Extension Live Server&lt;/strong&gt; (optionnelle pour rafra&amp;icirc;chir automatiquement la page)&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;📘 MODULE 1 &amp;mdash; STRUCTURE DE BASE D&amp;rsquo;UNE PAGE HTML5&lt;/h2&gt;
&lt;h3&gt;🔹 Syntaxe de base&lt;/h3&gt;
&lt;p&gt;Chaque document HTML5 commence ainsi :&lt;/p&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;!DOCTYPE html&amp;gt;
&amp;lt;html lang=&quot;fr&quot;&amp;gt;
&amp;lt;head&amp;gt;
  &amp;lt;meta charset=&quot;UTF-8&quot;&amp;gt;
  &amp;lt;title&amp;gt;Ma premi&amp;egrave;re page&amp;lt;/title&amp;gt;
&amp;lt;/head&amp;gt;
&amp;lt;body&amp;gt;
  &amp;lt;h1&amp;gt;Bonjour le monde !&amp;lt;/h1&amp;gt;
  &amp;lt;p&amp;gt;Ceci est ma premi&amp;egrave;re page HTML5.&amp;lt;/p&amp;gt;
&amp;lt;/body&amp;gt;
&amp;lt;/html&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧩 &lt;strong&gt;Analyse&lt;/strong&gt;&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;!DOCTYPE html&amp;gt;&lt;/code&gt; &amp;rarr; indique la version HTML5.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;html lang=&quot;fr&quot;&amp;gt;&lt;/code&gt; &amp;rarr; langue du document.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;head&amp;gt;&lt;/code&gt; &amp;rarr; contient les m&amp;eacute;tadonn&amp;eacute;es (titre, encodage, liens vers CSS/JS).&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;body&amp;gt;&lt;/code&gt; &amp;rarr; contient le contenu visible.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e un fichier &lt;code&gt;index.html&lt;/code&gt; avec :&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;ton nom comme titre de page ;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;un &lt;code&gt;&amp;lt;h1&amp;gt;&lt;/code&gt; &quot;Bienvenue sur mon site&quot; ;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;un paragraphe de pr&amp;eacute;sentation.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;📗 MODULE 2 &amp;mdash; TEXTE ET CONTENU&lt;/h2&gt;
&lt;h3&gt;🔹 Titres et paragraphes&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;h1&amp;gt;Titre principal&amp;lt;/h1&amp;gt;
&amp;lt;h2&amp;gt;Sous-titre&amp;lt;/h2&amp;gt;
&amp;lt;h3&amp;gt;Sous-sous-titre&amp;lt;/h3&amp;gt;
&amp;lt;p&amp;gt;Voici un paragraphe de texte.&amp;lt;/p&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧩 Les titres vont de &lt;code&gt;&amp;lt;h1&amp;gt;&lt;/code&gt; &amp;agrave; &lt;code&gt;&amp;lt;h6&amp;gt;&lt;/code&gt; (le plus important au moins important).&lt;/p&gt;
&lt;h3&gt;🔹 Mise en valeur&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;p&amp;gt;Voici un &amp;lt;strong&amp;gt;texte important&amp;lt;/strong&amp;gt; et un &amp;lt;em&amp;gt;texte en italique&amp;lt;/em&amp;gt;.&amp;lt;/p&amp;gt;
&amp;lt;p&amp;gt;Un texte &amp;lt;mark&amp;gt;surlign&amp;eacute;&amp;lt;/mark&amp;gt; et un &amp;lt;small&amp;gt;texte plus petit&amp;lt;/small&amp;gt;.&amp;lt;/p&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Sauts de ligne et s&amp;eacute;parations&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;p&amp;gt;Premi&amp;egrave;re ligne&amp;lt;br&amp;gt;Deuxi&amp;egrave;me ligne&amp;lt;/p&amp;gt;
&amp;lt;hr&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e une section avec ton nom, ta biographie courte, et des mots mis en valeur.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📙 MODULE 3 &amp;mdash; LES LIENS ET LES IMAGES&lt;/h2&gt;
&lt;h3&gt;🔹 Les liens (hyperliens)&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;a href=&quot;https://openai.com&quot; target=&quot;_blank&quot;&amp;gt;Visitez OpenAI&amp;lt;/a&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;href&lt;/code&gt; = destination du lien.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;target=&quot;_blank&quot;&lt;/code&gt; = ouvre dans un nouvel onglet.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;h3&gt;🔹 Liens internes&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;a href=&quot;contact.html&quot;&amp;gt;Page de contact&amp;lt;/a&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Images&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;img src=&quot;images/photo.jpg&quot; alt=&quot;Description de l&#039;image&quot;&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;alt&lt;/code&gt; = description (accessibilit&amp;eacute; / SEO).&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Utiliser toujours un texte alternatif pertinent.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e une page avec :&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;un lien vers ton profil GitHub ;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;une image avec une l&amp;eacute;gende sous forme de paragraphe.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;📒 MODULE 4 &amp;mdash; LES LISTES&lt;/h2&gt;
&lt;h3&gt;🔹 Liste &amp;agrave; puces&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;ul&amp;gt;
  &amp;lt;li&amp;gt;HTML&amp;lt;/li&amp;gt;
  &amp;lt;li&amp;gt;CSS&amp;lt;/li&amp;gt;
  &amp;lt;li&amp;gt;JavaScript&amp;lt;/li&amp;gt;
&amp;lt;/ul&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Liste num&amp;eacute;rot&amp;eacute;e&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;ol&amp;gt;
  &amp;lt;li&amp;gt;&amp;Eacute;tape 1&amp;lt;/li&amp;gt;
  &amp;lt;li&amp;gt;&amp;Eacute;tape 2&amp;lt;/li&amp;gt;
&amp;lt;/ol&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Liste imbriqu&amp;eacute;e&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;ul&amp;gt;
  &amp;lt;li&amp;gt;Frontend
    &amp;lt;ul&amp;gt;
      &amp;lt;li&amp;gt;HTML&amp;lt;/li&amp;gt;
      &amp;lt;li&amp;gt;CSS&amp;lt;/li&amp;gt;
    &amp;lt;/ul&amp;gt;
  &amp;lt;/li&amp;gt;
&amp;lt;/ul&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e une liste de tes comp&amp;eacute;tences techniques et hobbies.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📘 MODULE 5 &amp;mdash; LES TABLEAUX&lt;/h2&gt;
&lt;h3&gt;🔹 Structure&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;table border=&quot;1&quot;&amp;gt;
  &amp;lt;caption&amp;gt;Emploi du temps&amp;lt;/caption&amp;gt;
  &amp;lt;tr&amp;gt;
    &amp;lt;th&amp;gt;Jour&amp;lt;/th&amp;gt;
    &amp;lt;th&amp;gt;Cours&amp;lt;/th&amp;gt;
  &amp;lt;/tr&amp;gt;
  &amp;lt;tr&amp;gt;
    &amp;lt;td&amp;gt;Lundi&amp;lt;/td&amp;gt;
    &amp;lt;td&amp;gt;HTML5&amp;lt;/td&amp;gt;
  &amp;lt;/tr&amp;gt;
&amp;lt;/table&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Fusion de cellules&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;td rowspan=&quot;2&quot;&amp;gt;Fusion verticale&amp;lt;/td&amp;gt;
&amp;lt;td colspan=&quot;2&quot;&amp;gt;Fusion horizontale&amp;lt;/td&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e un tableau simple de ton planning de la semaine.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📕 MODULE 6 &amp;mdash; FORMULAIRES&lt;/h2&gt;
&lt;h3&gt;🔹 Structure&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;form action=&quot;#&quot; method=&quot;post&quot;&amp;gt;
  &amp;lt;label for=&quot;name&quot;&amp;gt;Nom :&amp;lt;/label&amp;gt;
  &amp;lt;input type=&quot;text&quot; id=&quot;name&quot; name=&quot;name&quot; required&amp;gt;

  &amp;lt;label for=&quot;email&quot;&amp;gt;Email :&amp;lt;/label&amp;gt;
  &amp;lt;input type=&quot;email&quot; id=&quot;email&quot; name=&quot;email&quot; required&amp;gt;

  &amp;lt;label for=&quot;message&quot;&amp;gt;Message :&amp;lt;/label&amp;gt;
  &amp;lt;textarea id=&quot;message&quot; name=&quot;message&quot;&amp;gt;&amp;lt;/textarea&amp;gt;

  &amp;lt;button type=&quot;submit&quot;&amp;gt;Envoyer&amp;lt;/button&amp;gt;
&amp;lt;/form&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Types modernes HTML5&lt;/h3&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;email&lt;/code&gt;, &lt;code&gt;date&lt;/code&gt;, &lt;code&gt;color&lt;/code&gt;, &lt;code&gt;range&lt;/code&gt;, &lt;code&gt;file&lt;/code&gt;, &lt;code&gt;number&lt;/code&gt;, &lt;code&gt;url&lt;/code&gt;, etc.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e un formulaire de contact avec les champs nom, email, message et un bouton &amp;ldquo;Envoyer&amp;rdquo;.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📗 MODULE 7 &amp;mdash; MULTIM&amp;Eacute;DIA&lt;/h2&gt;
&lt;h3&gt;🔹 Vid&amp;eacute;o&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;video controls width=&quot;400&quot;&amp;gt;
  &amp;lt;source src=&quot;video.mp4&quot; type=&quot;video/mp4&quot;&amp;gt;
  Votre navigateur ne supporte pas la vid&amp;eacute;o.
&amp;lt;/video&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Audio&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;audio controls&amp;gt;
  &amp;lt;source src=&quot;musique.mp3&quot; type=&quot;audio/mp3&quot;&amp;gt;
&amp;lt;/audio&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Int&amp;egrave;gre une courte vid&amp;eacute;o ou un fichier audio sur ta page.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📙 MODULE 8 &amp;mdash; STRUCTURE S&amp;Eacute;MANTIQUE HTML5&lt;/h2&gt;
&lt;h3&gt;🔹 Pourquoi ?&lt;/h3&gt;
&lt;p&gt;Les balises s&amp;eacute;mantiques donnent &lt;strong&gt;du sens&lt;/strong&gt; au contenu &amp;rarr; important pour SEO, accessibilit&amp;eacute;, et clart&amp;eacute;.&lt;/p&gt;
&lt;h3&gt;🔹 Balises principales&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;header&amp;gt;En-t&amp;ecirc;te du site&amp;lt;/header&amp;gt;
&amp;lt;nav&amp;gt;Navigation&amp;lt;/nav&amp;gt;
&amp;lt;main&amp;gt;
  &amp;lt;section&amp;gt;
    &amp;lt;article&amp;gt;
      &amp;lt;h2&amp;gt;Article 1&amp;lt;/h2&amp;gt;
      &amp;lt;p&amp;gt;Contenu...&amp;lt;/p&amp;gt;
    &amp;lt;/article&amp;gt;
  &amp;lt;/section&amp;gt;
&amp;lt;/main&amp;gt;
&amp;lt;aside&amp;gt;Informations compl&amp;eacute;mentaires&amp;lt;/aside&amp;gt;
&amp;lt;footer&amp;gt;Pied de page&amp;lt;/footer&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Structure ta page avec ces balises sans encore les styliser.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📘 MODULE 9 &amp;mdash; APIs HTML5 ESSENTIELLES&lt;/h2&gt;
&lt;h3&gt;🔹 Stockage local&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;script&amp;gt;
  localStorage.setItem(&#039;nom&#039;, &#039;Alice&#039;);
  alert(localStorage.getItem(&#039;nom&#039;));
&amp;lt;/script&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 G&amp;eacute;olocalisation (introduction)&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;script&amp;gt;
navigator.geolocation.getCurrentPosition(pos =&amp;gt; {
  console.log(pos.coords.latitude, pos.coords.longitude);
});
&amp;lt;/script&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Teste le stockage local avec ton nom et affiche-le dans une alerte.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📗 MODULE 10 &amp;mdash; BONNES PRATIQUES&lt;/h2&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;Utiliser des balises s&amp;eacute;mantiques appropri&amp;eacute;es.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Toujours fournir des textes alternatifs aux images.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Valider le code sur &lt;a href=&quot;https://validator.w3.org/&quot;&gt;https://validator.w3.org/&lt;/a&gt;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Indenter correctement ton code.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;S&amp;eacute;parer contenu (HTML), style (CSS) et logique (JS).&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;🎯 MODULE 11 &amp;mdash; MINI-PROJETS&lt;/h2&gt;
&lt;ol&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Page de blog simple&lt;/strong&gt; : titre, paragraphe, image.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Page de contact&lt;/strong&gt; : formulaire complet.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Galerie d&amp;rsquo;images&lt;/strong&gt; : images organis&amp;eacute;es en grilles (bient&amp;ocirc;t stylis&amp;eacute;es avec CSS).&lt;/p&gt;
&lt;/li&gt;
&lt;/ol&gt;
&lt;hr&gt;
&lt;h2&gt;💼 PROJET FINAL &amp;mdash; PORTFOLIO HTML5 PUR&lt;/h2&gt;
&lt;h3&gt;🔹 Objectif :&lt;/h3&gt;
&lt;p&gt;Cr&amp;eacute;er ton &lt;strong&gt;Portfolio personnel&lt;/strong&gt; enti&amp;egrave;rement structur&amp;eacute; en HTML5, pr&amp;ecirc;t pour le design CSS.&lt;/p&gt;
&lt;h3&gt;🔹 Structure de fichiers&lt;/h3&gt;
&lt;pre&gt;&lt;code&gt;/portfolio
│
├── index.html
├── about.html
├── projects.html
└── contact.html
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Exemple : &lt;code&gt;index.html&lt;/code&gt;&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;!DOCTYPE html&amp;gt;
&amp;lt;html lang=&quot;fr&quot;&amp;gt;
&amp;lt;head&amp;gt;
  &amp;lt;meta charset=&quot;UTF-8&quot;&amp;gt;
  &amp;lt;title&amp;gt;Mon Portfolio&amp;lt;/title&amp;gt;
&amp;lt;/head&amp;gt;
&amp;lt;body&amp;gt;
  &amp;lt;header&amp;gt;
    &amp;lt;h1&amp;gt;Mon Portfolio&amp;lt;/h1&amp;gt;
    &amp;lt;nav&amp;gt;
      &amp;lt;a href=&quot;index.html&quot;&amp;gt;Accueil&amp;lt;/a&amp;gt;
      &amp;lt;a href=&quot;about.html&quot;&amp;gt;&amp;Agrave; propos&amp;lt;/a&amp;gt;
      &amp;lt;a href=&quot;projects.html&quot;&amp;gt;Projets&amp;lt;/a&amp;gt;
      &amp;lt;a href=&quot;contact.html&quot;&amp;gt;Contact&amp;lt;/a&amp;gt;
    &amp;lt;/nav&amp;gt;
  &amp;lt;/header&amp;gt;

  &amp;lt;main&amp;gt;
    &amp;lt;section&amp;gt;
      &amp;lt;h2&amp;gt;Bienvenue&amp;lt;/h2&amp;gt;
      &amp;lt;p&amp;gt;Je suis &amp;lt;strong&amp;gt;Nom Pr&amp;eacute;nom&amp;lt;/strong&amp;gt;, d&amp;eacute;veloppeur web passionn&amp;eacute;.&amp;lt;/p&amp;gt;
    &amp;lt;/section&amp;gt;

    &amp;lt;section&amp;gt;
      &amp;lt;h2&amp;gt;Mes projets r&amp;eacute;cents&amp;lt;/h2&amp;gt;
      &amp;lt;article&amp;gt;
        &amp;lt;h3&amp;gt;Projet 1&amp;lt;/h3&amp;gt;
        &amp;lt;p&amp;gt;Description du projet...&amp;lt;/p&amp;gt;
      &amp;lt;/article&amp;gt;
    &amp;lt;/section&amp;gt;
  &amp;lt;/main&amp;gt;

  &amp;lt;footer&amp;gt;
    &amp;lt;p&amp;gt;&amp;amp;copy; 2025 Mon Nom &amp;mdash; Tous droits r&amp;eacute;serv&amp;eacute;s.&amp;lt;/p&amp;gt;
  &amp;lt;/footer&amp;gt;
&amp;lt;/body&amp;gt;
&amp;lt;/html&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Les autres pages&lt;/h3&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;about.html&lt;/code&gt; &amp;rarr; pr&amp;eacute;sentation d&amp;eacute;taill&amp;eacute;e de toi.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;projects.html&lt;/code&gt; &amp;rarr; liste de projets avec descriptions.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;contact.html&lt;/code&gt; &amp;rarr; formulaire de contact.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice final :&lt;/strong&gt;&lt;br&gt;Construis ton Portfolio complet avec 4 pages et navigation fonctionnelle.&lt;br&gt;Teste-le sur ton navigateur, puis pr&amp;eacute;pare-toi &amp;agrave; l&amp;rsquo;&amp;eacute;tape suivante : le &lt;strong&gt;design CSS&lt;/strong&gt;.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;🏁 F&amp;Eacute;LICITATIONS !&lt;/h2&gt;
&lt;p&gt;Tu ma&amp;icirc;trises maintenant :&lt;br&gt;✅ La structure HTML5&lt;br&gt;✅ Les balises s&amp;eacute;mantiques&lt;br&gt;✅ Les formulaires, tableaux, multim&amp;eacute;dia&lt;br&gt;✅ Les bonnes pratiques professionnelles&lt;/p&gt;
&lt;p&gt;👉 &lt;strong&gt;Prochaine &amp;eacute;tape :&lt;/strong&gt; on stylisera tout &amp;ccedil;a avec &lt;strong&gt;un cours CSS complet&lt;/strong&gt; (couleurs, mise en page, animations, responsive design).&lt;/p&gt;
&lt;hr&gt;
&lt;p&gt;&amp;nbsp;&lt;/p&gt;\";s:4:\"type\";s:0:\"\";s:8:\"category\";s:3:\"Web\";s:5:\"level\";s:9:\"Débutant\";s:9:\"file_path\";N;s:13:\"external_link\";s:0:\"\";s:5:\"views\";i:2;s:9:\"downloads\";i:0;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-23 16:27:12\";s:10:\"updated_at\";s:19:\"2025-10-23 16:34:29\";s:6:\"prenom\";s:7:\"MOHAMED\";s:3:\"nom\";s:4:\"SARE\";s:10:\"photo_path\";s:73:\"uploads/profiles/profile_1761235456_94e43f9634f444c9e8b2239d6c5b694f.jpeg\";s:11:\"likes_count\";i:1;}}s:12:\"recent_blogs\";a:0:{}}', '1761236286', '1761236166', '2', '1761236167');
INSERT INTO `cache_store` (`cache_key`, `cache_value`, `expires_at`, `created_at`, `access_count`, `last_accessed`) VALUES ('forum_index_all_page_1', 'a:3:{s:5:\"posts\";a:0:{}s:10:\"categories\";a:11:{i:0;a:11:{s:2:\"id\";i:1;s:4:\"name\";s:13:\"Programmation\";s:4:\"slug\";s:13:\"programmation\";s:11:\"description\";s:53:\"Discussions sur la programmation et le développement\";s:4:\"icon\";s:7:\"fa-code\";s:5:\"color\";s:7:\"#667eea\";s:13:\"display_order\";i:1;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:1;a:11:{s:2:\"id\";i:2;s:4:\"name\";s:32:\"Réseaux et Télécommunications\";s:4:\"slug\";s:26:\"reseaux-telecommunications\";s:11:\"description\";s:52:\"Discussions sur les réseaux et télécommunications\";s:4:\"icon\";s:16:\"fa-network-wired\";s:5:\"color\";s:7:\"#28a745\";s:13:\"display_order\";i:2;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:2;a:11:{s:2:\"id\";i:3;s:4:\"name\";s:15:\"Cybersécurité\";s:4:\"slug\";s:13:\"cybersecurite\";s:11:\"description\";s:42:\"Sécurité informatique et cybersécurité\";s:4:\"icon\";s:13:\"fa-shield-alt\";s:5:\"color\";s:7:\"#dc3545\";s:13:\"display_order\";i:3;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:3;a:11:{s:2:\"id\";i:4;s:4:\"name\";s:25:\"Intelligence Artificielle\";s:4:\"slug\";s:25:\"intelligence-artificielle\";s:11:\"description\";s:36:\"IA, Machine Learning et Data Science\";s:4:\"icon\";s:8:\"fa-brain\";s:5:\"color\";s:7:\"#6f42c1\";s:13:\"display_order\";i:4;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:4;a:11:{s:2:\"id\";i:5;s:4:\"name\";s:15:\"Web Development\";s:4:\"slug\";s:15:\"web-development\";s:11:\"description\";s:38:\"Développement web et technologies web\";s:4:\"icon\";s:8:\"fa-globe\";s:5:\"color\";s:7:\"#17a2b8\";s:13:\"display_order\";i:5;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:5;a:11:{s:2:\"id\";i:6;s:4:\"name\";s:18:\"Mobile Development\";s:4:\"slug\";s:18:\"mobile-development\";s:11:\"description\";s:36:\"Développement mobile iOS et Android\";s:4:\"icon\";s:13:\"fa-mobile-alt\";s:5:\"color\";s:7:\"#fd7e14\";s:13:\"display_order\";i:6;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:6;a:11:{s:2:\"id\";i:7;s:4:\"name\";s:16:\"Base de Données\";s:4:\"slug\";s:15:\"base-de-donnees\";s:11:\"description\";s:33:\"SGBD, SQL et gestion des données\";s:4:\"icon\";s:11:\"fa-database\";s:5:\"color\";s:7:\"#20c997\";s:13:\"display_order\";i:7;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:7;a:11:{s:2:\"id\";i:8;s:4:\"name\";s:6:\"DevOps\";s:4:\"slug\";s:6:\"devops\";s:11:\"description\";s:37:\"Déploiement, CI/CD et infrastructure\";s:4:\"icon\";s:9:\"fa-server\";s:5:\"color\";s:7:\"#6c757d\";s:13:\"display_order\";i:8;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:8;a:11:{s:2:\"id\";i:9;s:4:\"name\";s:15:\"Cloud Computing\";s:4:\"slug\";s:15:\"cloud-computing\";s:11:\"description\";s:32:\"Services cloud et virtualisation\";s:4:\"icon\";s:8:\"fa-cloud\";s:5:\"color\";s:7:\"#007bff\";s:13:\"display_order\";i:9;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:9;a:11:{s:2:\"id\";i:10;s:4:\"name\";s:9:\"Général\";s:4:\"slug\";s:7:\"general\";s:11:\"description\";s:37:\"Discussions générales et hors-sujet\";s:4:\"icon\";s:11:\"fa-comments\";s:5:\"color\";s:7:\"#6c757d\";s:13:\"display_order\";i:10;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}i:10;a:11:{s:2:\"id\";i:11;s:4:\"name\";s:5:\"Autre\";s:4:\"slug\";s:5:\"autre\";s:11:\"description\";s:31:\"Autres sujets non catégorisés\";s:4:\"icon\";s:9:\"fa-folder\";s:5:\"color\";s:7:\"#6c757d\";s:13:\"display_order\";i:11;s:10:\"post_count\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 20:37:37\";s:10:\"updated_at\";s:19:\"2025-10-09 20:37:37\";}}s:5:\"stats\";a:4:{s:11:\"total_posts\";i:0;s:14:\"active_members\";i:0;s:15:\"trending_topics\";i:0;s:11:\"today_posts\";i:0;}}', '1761242022', '1761241902', '0', '1761241902');
INSERT INTO `cache_store` (`cache_key`, `cache_value`, `expires_at`, `created_at`, `access_count`, `last_accessed`) VALUES ('landing_stats', 'a:4:{s:11:\"total_users\";i:1;s:11:\"total_posts\";i:0;s:15:\"total_tutorials\";i:1;s:14:\"total_projects\";i:0;}', '1761242279', '1761241979', '5', '1761242187');
INSERT INTO `cache_store` (`cache_key`, `cache_value`, `expires_at`, `created_at`, `access_count`, `last_accessed`) VALUES ('search_results_3e2bb9492bc3a32d5f8c9aafe88894c6', 'a:3:{s:7:\"results\";a:5:{s:5:\"users\";a:1:{i:0;a:9:{s:2:\"id\";i:5;s:6:\"prenom\";s:7:\"MOHAMED\";s:3:\"nom\";s:4:\"SARE\";s:5:\"email\";s:18:\"mhdcode7@gmail.com\";s:10:\"photo_path\";s:73:\"uploads/profiles/profile_1761235456_94e43f9634f444c9e8b2239d6c5b694f.jpeg\";s:10:\"university\";s:46:\"Ecole Supérieure de Technologie de Casablanca\";s:4:\"city\";s:5:\"Autre\";s:3:\"bio\";s:0:\"\";s:9:\"full_name\";s:12:\"MOHAMED SARE\";}}s:5:\"posts\";a:0:{}s:9:\"tutorials\";a:0:{}s:8:\"projects\";a:0:{}s:4:\"jobs\";a:0:{}}s:13:\"results_count\";a:5:{s:5:\"users\";i:1;s:5:\"posts\";i:0;s:9:\"tutorials\";i:0;s:8:\"projects\";i:0;s:4:\"jobs\";i:0;}s:13:\"total_results\";i:1;}', '1761242255', '1761242195', '0', '1761242195');
INSERT INTO `cache_store` (`cache_key`, `cache_value`, `expires_at`, `created_at`, `access_count`, `last_accessed`) VALUES ('search_results_9e3669d19b675bd57058fd4664205d2a', 'a:3:{s:7:\"results\";a:5:{s:5:\"users\";a:0:{}s:5:\"posts\";a:2:{i:0;a:16:{s:2:\"id\";i:8;s:7:\"user_id\";i:5;s:8:\"category\";s:5:\"Autre\";s:5:\"title\";s:36:\"Programme de DUT Génie Informatique\";s:4:\"body\";s:142:\"Ce programme vous permet d\'avoir des bases solides en informatique de façon générale et de vous spécialiser plus tard dans votre formation\";s:5:\"views\";i:2;s:9:\"is_pinned\";i:0;s:9:\"is_locked\";i:0;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-23 14:48:58\";s:10:\"updated_at\";s:19:\"2025-10-23 15:49:15\";s:6:\"prenom\";s:7:\"MOHAMED\";s:3:\"nom\";s:4:\"SARE\";s:10:\"photo_path\";N;s:14:\"comments_count\";i:0;s:11:\"likes_count\";i:0;}i:1;a:16:{s:2:\"id\";i:7;s:7:\"user_id\";i:5;s:8:\"category\";s:5:\"Autre\";s:5:\"title\";s:36:\"Programme de DUT Génie Informatique\";s:4:\"body\";s:142:\"Ce programme vous permet d\'avoir des bases solides en informatique de façon générale et de vous spécialiser plus tard dans votre formation\";s:5:\"views\";i:0;s:9:\"is_pinned\";i:0;s:9:\"is_locked\";i:0;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-23 14:48:48\";s:10:\"updated_at\";s:19:\"2025-10-23 15:48:48\";s:6:\"prenom\";s:7:\"MOHAMED\";s:3:\"nom\";s:4:\"SARE\";s:10:\"photo_path\";N;s:14:\"comments_count\";i:0;s:11:\"likes_count\";i:0;}}s:9:\"tutorials\";a:0:{}s:8:\"projects\";a:0:{}s:4:\"jobs\";a:0:{}}s:13:\"results_count\";a:5:{s:5:\"users\";i:0;s:5:\"posts\";i:2;s:9:\"tutorials\";i:0;s:8:\"projects\";i:0;s:4:\"jobs\";i:0;}s:13:\"total_results\";i:2;}', '1761232122', '1761232062', '0', '1761232062');
INSERT INTO `cache_store` (`cache_key`, `cache_value`, `expires_at`, `created_at`, `access_count`, `last_accessed`) VALUES ('search_results_fc37fbde490e37c1258738a18b9aa4c7', 'a:3:{s:7:\"results\";a:5:{s:5:\"users\";a:0:{}s:5:\"posts\";a:0:{}s:9:\"tutorials\";a:0:{}s:8:\"projects\";a:0:{}s:4:\"jobs\";a:0:{}}s:13:\"results_count\";a:5:{s:5:\"users\";i:0;s:5:\"posts\";i:0;s:9:\"tutorials\";i:0;s:8:\"projects\";i:0;s:4:\"jobs\";i:0;}s:13:\"total_results\";i:0;}', '1761230675', '1761230615', '0', '1761230615');

-- ================================================================
-- Table: comments
-- ================================================================
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `commentable_type` enum('post','tutorial','blog','project') NOT NULL DEFAULT 'post',
  `commentable_id` int(11) NOT NULL DEFAULT 0,
  `body` text NOT NULL,
  `status` enum('active','hidden','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_commentable` (`commentable_type`,`commentable_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_comments_user_id` (`user_id`),
  KEY `idx_comments_status` (`status`),
  KEY `idx_comments_created_at` (`created_at`),
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: downloads
-- ================================================================
DROP TABLE IF EXISTS `downloads`;
CREATE TABLE `downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `downloadable_type` enum('tutorial','project','document') NOT NULL,
  `downloadable_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_downloadable` (`downloadable_type`,`downloadable_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_date` (`downloaded_at`),
  CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: forum_categories
-- ================================================================
DROP TABLE IF EXISTS `forum_categories`;
CREATE TABLE `forum_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fa-folder',
  `color` varchar(7) DEFAULT '#667eea',
  `display_order` int(11) DEFAULT 0,
  `post_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_active` (`is_active`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `forum_categories`
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Programmation', 'programmation', 'Discussions sur la programmation et le développement', 'fa-code', '#667eea', '1', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'Réseaux et Télécommunications', 'reseaux-telecommunications', 'Discussions sur les réseaux et télécommunications', 'fa-network-wired', '#28a745', '2', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'Cybersécurité', 'cybersecurite', 'Sécurité informatique et cybersécurité', 'fa-shield-alt', '#dc3545', '3', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'Intelligence Artificielle', 'intelligence-artificielle', 'IA, Machine Learning et Data Science', 'fa-brain', '#6f42c1', '4', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('5', 'Web Development', 'web-development', 'Développement web et technologies web', 'fa-globe', '#17a2b8', '5', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('6', 'Mobile Development', 'mobile-development', 'Développement mobile iOS et Android', 'fa-mobile-alt', '#fd7e14', '6', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('7', 'Base de Données', 'base-de-donnees', 'SGBD, SQL et gestion des données', 'fa-database', '#20c997', '7', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('8', 'DevOps', 'devops', 'Déploiement, CI/CD et infrastructure', 'fa-server', '#6c757d', '8', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('9', 'Cloud Computing', 'cloud-computing', 'Services cloud et virtualisation', 'fa-cloud', '#007bff', '9', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('10', 'Général', 'general', 'Discussions générales et hors-sujet', 'fa-comments', '#6c757d', '10', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');
INSERT INTO `forum_categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `post_count`, `is_active`, `created_at`, `updated_at`) VALUES ('11', 'Autre', 'autre', 'Autres sujets non catégorisés', 'fa-folder', '#6c757d', '11', '0', '1', '2025-10-09 20:37:37', '2025-10-09 20:37:37');

-- ================================================================
-- Table: jobs
-- ================================================================
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL COMMENT 'user_id de l''entreprise',
  `type` enum('stage','emploi','hackathon','formation','freelance') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `skills_required` text DEFAULT NULL COMMENT 'JSON array des compétences',
  `salary_range` varchar(100) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `external_link` varchar(500) DEFAULT NULL,
  `status` enum('pending','active','closed','expired') DEFAULT 'active',
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_deadline` (`deadline`),
  KEY `idx_jobs_type` (`type`),
  KEY `idx_jobs_status` (`status`),
  KEY `idx_jobs_created_at` (`created_at`),
  FULLTEXT KEY `ft_search` (`title`,`description`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: likes
-- ================================================================
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `likeable_type` enum('post','comment','tutorial') NOT NULL,
  `likeable_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`user_id`,`likeable_type`,`likeable_id`),
  KEY `idx_likeable` (`likeable_type`,`likeable_id`),
  KEY `idx_likes_user_id` (`user_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `likes`
INSERT INTO `likes` (`id`, `user_id`, `likeable_type`, `likeable_id`, `created_at`) VALUES ('19', '5', 'post', '7', '2025-10-23 15:07:56');
INSERT INTO `likes` (`id`, `user_id`, `likeable_type`, `likeable_id`, `created_at`) VALUES ('20', '5', 'tutorial', '3', '2025-10-23 16:27:16');

-- ================================================================
-- Table: messages
-- ================================================================
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_deleted_by_sender` tinyint(1) DEFAULT 0,
  `is_deleted_by_receiver` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `action_type` varchar(50) DEFAULT NULL COMMENT 'Type d action (project_join_request, etc.)',
  `action_data` text DEFAULT NULL COMMENT 'Donnees JSON pour l action',
  `action_status` enum('pending','accepted','rejected','cancelled') DEFAULT NULL COMMENT 'Statut de l action',
  PRIMARY KEY (`id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_receiver` (`receiver_id`),
  KEY `idx_read` (`is_read`),
  KEY `idx_action_status` (`action_status`),
  KEY `idx_messages_sender_id` (`sender_id`),
  KEY `idx_messages_receiver_id` (`receiver_id`),
  KEY `idx_messages_is_read` (`is_read`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: newsletter_campaigns
-- ================================================================
DROP TABLE IF EXISTS `newsletter_campaigns`;
CREATE TABLE `newsletter_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('draft','sent','scheduled') DEFAULT 'draft',
  `sent_count` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  `scheduled_for` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `newsletter_campaigns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: newsletter_subscribers
-- ================================================================
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `status` enum('active','unsubscribed','bounced') DEFAULT 'active',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `total_sent` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_subscribed_at` (`subscribed_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: notifications
-- ================================================================
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'new_comment, new_message, badge_earned, etc.',
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  KEY `idx_created` (`created_at`),
  KEY `idx_notifications_user_id` (`user_id`),
  KEY `idx_notifications_is_read` (`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: online_users
-- ================================================================
DROP TABLE IF EXISTS `online_users`;
CREATE TABLE `online_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_seen` datetime NOT NULL,
  `page_url` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_session` (`session_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_last_seen` (`last_seen`),
  CONSTRAINT `online_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57847 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `online_users`
INSERT INTO `online_users` (`id`, `user_id`, `session_id`, `ip_address`, `last_seen`, `page_url`) VALUES ('57147', NULL, '9ca4c3015113ef82760baa481ee16660f3a8e11069408130954a35446a8a0969', '::1', '2025-10-23 15:29:37', '/HubTech/auth/logout');
INSERT INTO `online_users` (`id`, `user_id`, `session_id`, `ip_address`, `last_seen`, `page_url`) VALUES ('57200', NULL, '1e9f6a4c50385ebc1ce6831fdf9e6b658f66ad63dad2eb6db7728a0feb4c6486', '::1', '2025-10-23 17:05:25', '/HubTech/auth/logout');
INSERT INTO `online_users` (`id`, `user_id`, `session_id`, `ip_address`, `last_seen`, `page_url`) VALUES ('57501', NULL, '642eb2c85c479d129c425b250ef113a2f55c2f6b36c02d0b46f61d259a599f2c', '::1', '2025-10-23 17:16:10', '/HubTech/auth/logout');
INSERT INTO `online_users` (`id`, `user_id`, `session_id`, `ip_address`, `last_seen`, `page_url`) VALUES ('57554', NULL, '20ef613311ea054b65e56d979b8a7d619bae9b696392158c158e1761981589b5', '::1', '2025-10-23 17:17:30', '/HubTech/public/images/favicon.png');
INSERT INTO `online_users` (`id`, `user_id`, `session_id`, `ip_address`, `last_seen`, `page_url`) VALUES ('57573', NULL, '76b41ac6ee5146f6b7289bac3da86fb865e91064e7bf5dd610164217da949e72', '::1', '2025-10-23 17:51:06', '/HubTech/auth/logout');
INSERT INTO `online_users` (`id`, `user_id`, `session_id`, `ip_address`, `last_seen`, `page_url`) VALUES ('57643', NULL, '0003ea71e972fcb2816731a413fd4e7d904b5f77e6d503389c8a11d05d7b924b', '::1', '2025-10-23 17:50:53', '/HubTech/public/images/favicon.png');
INSERT INTO `online_users` (`id`, `user_id`, `session_id`, `ip_address`, `last_seen`, `page_url`) VALUES ('57672', NULL, '54defda96ad2d14678f5c5289b12f4133484fa233d7764d7487c44c19180666d', '::1', '2025-10-23 18:45:19', '/HubTech/auth/logout');
INSERT INTO `online_users` (`id`, `user_id`, `session_id`, `ip_address`, `last_seen`, `page_url`) VALUES ('57730', NULL, '2cccc54b94173ca64273cefa8cd1dde81ec51edd6003bc770cf61db06c2eb898', '::1', '2025-10-23 18:52:59', '/HubTech/auth/logout');
INSERT INTO `online_users` (`id`, `user_id`, `session_id`, `ip_address`, `last_seen`, `page_url`) VALUES ('57819', NULL, '7a06f3639a2e39f2df903fdf860b51b4d3c02ea12fdaadb4ed3ff8e3b546bd93', '::1', '2025-10-23 19:17:54', '/HubTech/admin/downloadDatabaseBackup?type=full');

-- ================================================================
-- Table: post_attachments
-- ================================================================
DROP TABLE IF EXISTS `post_attachments`;
CREATE TABLE `post_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_post_id` (`post_id`),
  CONSTRAINT `post_attachments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: posts
-- ================================================================
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `views` int(11) DEFAULT 0,
  `is_pinned` tinyint(1) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `status` enum('active','hidden','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_posts_user_id` (`user_id`),
  KEY `idx_posts_category` (`category`),
  KEY `idx_posts_status` (`status`),
  KEY `idx_posts_created_at` (`created_at`),
  FULLTEXT KEY `ft_search` (`title`,`body`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `posts`
INSERT INTO `posts` (`id`, `user_id`, `category`, `title`, `body`, `views`, `is_pinned`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES ('7', '5', 'Autre', 'Programme de DUT Génie Informatique', 'Ce programme vous permet d\'avoir des bases solides en informatique de façon générale et de vous spécialiser plus tard dans votre formation', '3', '0', '0', 'deleted', '2025-10-23 14:48:48', '2025-10-23 17:04:26');
INSERT INTO `posts` (`id`, `user_id`, `category`, `title`, `body`, `views`, `is_pinned`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES ('8', '5', 'Autre', 'Programme de DUT Génie Informatique', 'Ce programme vous permet d\'avoir des bases solides en informatique de façon générale et de vous spécialiser plus tard dans votre formation', '4', '0', '0', 'deleted', '2025-10-23 14:48:58', '2025-10-23 18:47:36');

-- ================================================================
-- Table: project_members
-- ================================================================
DROP TABLE IF EXISTS `project_members`;
CREATE TABLE `project_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(100) DEFAULT NULL COMMENT 'Frontend, Backend, Design, etc.',
  `status` enum('pending','active','left') DEFAULT 'active',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_project_member` (`project_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: projects
-- ================================================================
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `github_link` varchar(500) DEFAULT NULL,
  `demo_link` varchar(500) DEFAULT NULL,
  `status` enum('planning','in_progress','completed','archived') DEFAULT 'planning',
  `visibility` enum('public','private') DEFAULT 'public',
  `looking_for_members` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `idx_status` (`status`),
  KEY `idx_visibility` (`visibility`),
  KEY `idx_projects_status` (`status`),
  KEY `idx_projects_created_at` (`created_at`),
  FULLTEXT KEY `ft_search` (`title`,`description`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: rate_limits
-- ================================================================
DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `action` varchar(50) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `last_attempt` datetime DEFAULT current_timestamp(),
  `blocked_until` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_action` (`ip_address`,`action`),
  KEY `idx_blocked_until` (`blocked_until`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: reports
-- ================================================================
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_id` int(11) NOT NULL,
  `reportable_type` enum('post','comment','tutorial','message','user') NOT NULL,
  `reportable_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reporter_id` (`reporter_id`),
  KEY `idx_status` (`status`),
  KEY `idx_reportable` (`reportable_type`,`reportable_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: site_stats
-- ================================================================
DROP TABLE IF EXISTS `site_stats`;
CREATE TABLE `site_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stat_key` varchar(100) NOT NULL,
  `stat_value` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `stat_key` (`stat_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: skills
-- ================================================================
DROP TABLE IF EXISTS `skills`;
CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL COMMENT 'Programmation, Réseau, Cybersécurité, etc.',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `skills`
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('1', 'Python', 'Programmation', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('2', 'JavaScript', 'Programmation', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('3', 'Java', 'Programmation', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('4', 'C++', 'Programmation', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('5', 'PHP', 'Programmation', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('6', 'React', 'Web Development', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('7', 'Vue.js', 'Web Development', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('8', 'Angular', 'Web Development', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('9', 'Node.js', 'Web Development', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('10', 'Laravel', 'Web Development', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('11', 'MySQL', 'Base de Données', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('12', 'PostgreSQL', 'Base de Données', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('13', 'MongoDB', 'Base de Données', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('14', 'Redis', 'Base de Données', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('15', 'Docker', 'DevOps', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('16', 'Kubernetes', 'DevOps', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('17', 'AWS', 'Cloud Computing', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('18', 'Azure', 'Cloud Computing', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('19', 'Git', 'Outils de Développement', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('20', 'Linux', 'Système', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('21', 'Network Security', 'Cybersécurité', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('22', 'Ethical Hacking', 'Cybersécurité', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('23', 'Machine Learning', 'Intelligence Artificielle', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('24', 'Deep Learning', 'Intelligence Artificielle', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('25', 'Data Science', 'Intelligence Artificielle', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('26', 'Flutter', 'Mobile Development', '2025-10-09 20:37:37');
INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES ('27', 'React Native', 'Mobile Development', '2025-10-09 20:37:37');

-- ================================================================
-- Table: system_settings
-- ================================================================
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `category` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_category` (`category`),
  KEY `idx_key` (`setting_key`),
  CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `system_settings`
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('1', 'site_name', 'AlgoCodeBF', 'text', 'general', 'Nom du site', '2025-10-23 15:43:10', '5');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('2', 'site_description', 'Plateforme communautaire pour étudiants en informatique au Burkina Faso', 'text', 'general', 'Description du site', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('3', 'site_keywords', 'informatique, programmation, Burkina Faso, étudiants, tech, communauté', 'text', 'general', 'Mots-clés SEO', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('4', 'admin_email', 'mhdcode7@gmail.comadmin@hubtech.bf', 'text', 'general', 'Email de l\'administrateur', '2025-10-23 15:25:32', '5');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('5', 'posts_per_page', '10', 'number', 'content', 'Nombre de posts par page', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('6', 'tutorials_per_page', '8', 'number', 'content', 'Nombre de tutoriels par page', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('7', 'blog_posts_per_page', '6', 'number', 'content', 'Nombre d\'articles de blog par page', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('8', 'max_file_size', '10485760', 'number', 'upload', 'Taille maximale des fichiers (en bytes)', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('9', 'allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,txt', 'text', 'upload', 'Types de fichiers autorisés', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('10', 'registration_enabled', 'true', 'boolean', 'user', 'Autoriser les inscriptions', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('11', 'email_verification_required', 'true', 'boolean', 'user', 'Vérification email requise', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('12', 'maintenance_mode', 'false', 'boolean', 'general', 'Mode maintenance', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('13', 'max_login_attempts', '5', 'number', 'security', 'Nombre maximum de tentatives de connexion', '2025-10-23 15:26:17', '5');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('14', 'session_timeout', '3600', 'number', 'security', 'Timeout de session (en secondes)', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('15', 'cache_enabled', 'true', 'boolean', 'performance', 'Activer le cache', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('16', 'cache_duration', '3600', 'number', 'performance', 'Durée du cache (en secondes)', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('17', 'google_analytics_id', '', 'text', 'analytics', 'ID Google Analytics', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('18', 'facebook_app_id', '', 'text', 'social', 'ID Application Facebook', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('19', 'twitter_handle', '', 'text', 'social', 'Compte Twitter', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('20', 'linkedin_url', '', 'text', 'social', 'URL LinkedIn', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('21', 'github_url', '', 'text', 'social', 'URL GitHub', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('22', 'contact_email', 'mhdcode7@gmail.com', 'text', 'contact', 'Email de contact', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('23', 'contact_phone', '+226 64 71 20 44', 'text', 'contact', 'Téléphone de contact', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('24', 'contact_address', 'Ouagadougou, Burkina Faso', 'text', 'contact', 'Adresse de contact', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('25', 'newsletter_enabled', 'true', 'boolean', 'newsletter', 'Activer la newsletter', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('26', 'auto_approve_tutorials', 'false', 'boolean', 'content', 'Approuver automatiquement les tutoriels', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('27', 'auto_approve_projects', 'false', 'boolean', 'content', 'Approuver automatiquement les projets', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('28', 'max_tags_per_post', '5', 'number', 'content', 'Nombre maximum de tags par post', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('29', 'max_skills_per_user', '10', 'number', 'user', 'Nombre maximum de compétences par utilisateur', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('30', 'default_user_role', 'user', 'text', 'user', 'Rôle par défaut des nouveaux utilisateurs', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('31', 'welcome_message', 'Bienvenue sur HubTech! Partagez vos connaissances et apprenez avec la communauté.', 'text', 'content', 'Message de bienvenue', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('32', 'terms_of_service', 'Conditions d\'utilisation de la plateforme HubTech', 'text', 'legal', 'Conditions d\'utilisation', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('33', 'privacy_policy', 'Politique de confidentialité de HubTech', 'text', 'legal', 'Politique de confidentialité', '2025-10-09 20:37:37', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`, `updated_by`) VALUES ('34', 'cookie_policy', 'Politique d\'utilisation des cookies', 'text', 'legal', 'Politique des cookies', '2025-10-09 20:37:37', NULL);

-- ================================================================
-- Table: tags
-- ================================================================
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: tutorial_tags
-- ================================================================
DROP TABLE IF EXISTS `tutorial_tags`;
CREATE TABLE `tutorial_tags` (
  `tutorial_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`tutorial_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `tutorial_tags_ibfk_1` FOREIGN KEY (`tutorial_id`) REFERENCES `tutorials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tutorial_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: tutorials
-- ================================================================
DROP TABLE IF EXISTS `tutorials`;
CREATE TABLE `tutorials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `type` enum('video','pdf','code','article') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `level` enum('Débutant','Intermédiaire','Avancé','Expert') DEFAULT 'Débutant',
  `file_path` varchar(255) DEFAULT NULL,
  `external_link` varchar(500) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `downloads` int(11) DEFAULT 0,
  `status` enum('pending','active','hidden','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_tutorials_user_id` (`user_id`),
  KEY `idx_tutorials_category` (`category`),
  KEY `idx_tutorials_status` (`status`),
  KEY `idx_tutorials_created_at` (`created_at`),
  FULLTEXT KEY `ft_search` (`title`,`description`,`content`),
  CONSTRAINT `tutorials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `tutorials`
INSERT INTO `tutorials` (`id`, `user_id`, `title`, `description`, `content`, `type`, `category`, `level`, `file_path`, `external_link`, `views`, `downloads`, `status`, `created_at`, `updated_at`) VALUES ('3', '5', 'HTML5 — DU DÉBUTANT À L’EXPERT', 'Maîtriser HTML5 à un niveau professionnel et construire un Portfolio personnel entièrement en HTML5 (avant le design CSS qui viendra dans le prochain cours).', '&lt;p&gt;&amp;nbsp;&lt;/p&gt;
&lt;hr&gt;
&lt;h1&gt;🌐 COURS COMPLET HTML5 &amp;mdash; DU D&amp;Eacute;BUTANT &amp;Agrave; L&amp;rsquo;EXPERT&lt;/h1&gt;
&lt;p&gt;&lt;strong&gt;Objectif :&lt;/strong&gt; Ma&amp;icirc;triser HTML5 &amp;agrave; un niveau professionnel et construire un &lt;strong&gt;Portfolio personnel&lt;/strong&gt; enti&amp;egrave;rement en HTML5 (avant le design CSS qui viendra dans le prochain cours).&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;🧭 INTRODUCTION G&amp;Eacute;N&amp;Eacute;RALE&lt;/h2&gt;
&lt;h3&gt;🔹 Qu&amp;rsquo;est-ce que HTML ?&lt;/h3&gt;
&lt;p&gt;&lt;strong&gt;HTML (HyperText Markup Language)&lt;/strong&gt; est le &lt;strong&gt;langage de base du web&lt;/strong&gt;.&lt;br&gt;Il sert &amp;agrave; &lt;strong&gt;structurer le contenu&lt;/strong&gt; d&amp;rsquo;une page : titres, paragraphes, images, liens, tableaux, formulaires, etc.&lt;/p&gt;
&lt;p&gt;👉 &lt;strong&gt;HTML &amp;ne; Langage de programmation&lt;/strong&gt;&lt;br&gt;C&amp;rsquo;est un &lt;strong&gt;langage de balisage&lt;/strong&gt; : il d&amp;eacute;crit le r&amp;ocirc;le du contenu, pas son apparence.&lt;/p&gt;
&lt;h3&gt;🔹 Outils n&amp;eacute;cessaires&lt;/h3&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;&amp;Eacute;diteur de texte&lt;/strong&gt; : Visual Studio Code &amp;amp;&amp;amp; SublimeText(recommand&amp;eacute;)&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Navigateur moderne&lt;/strong&gt; : Chrome, Firefox, Edge&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Extension Live Server&lt;/strong&gt; (optionnelle pour rafra&amp;icirc;chir automatiquement la page)&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;📘 MODULE 1 &amp;mdash; STRUCTURE DE BASE D&amp;rsquo;UNE PAGE HTML5&lt;/h2&gt;
&lt;h3&gt;🔹 Syntaxe de base&lt;/h3&gt;
&lt;p&gt;Chaque document HTML5 commence ainsi :&lt;/p&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;!DOCTYPE html&amp;gt;
&amp;lt;html lang=&quot;fr&quot;&amp;gt;
&amp;lt;head&amp;gt;
  &amp;lt;meta charset=&quot;UTF-8&quot;&amp;gt;
  &amp;lt;title&amp;gt;Ma premi&amp;egrave;re page&amp;lt;/title&amp;gt;
&amp;lt;/head&amp;gt;
&amp;lt;body&amp;gt;
  &amp;lt;h1&amp;gt;Bonjour le monde !&amp;lt;/h1&amp;gt;
  &amp;lt;p&amp;gt;Ceci est ma premi&amp;egrave;re page HTML5.&amp;lt;/p&amp;gt;
&amp;lt;/body&amp;gt;
&amp;lt;/html&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧩 &lt;strong&gt;Analyse&lt;/strong&gt;&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;!DOCTYPE html&amp;gt;&lt;/code&gt; &amp;rarr; indique la version HTML5.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;html lang=&quot;fr&quot;&amp;gt;&lt;/code&gt; &amp;rarr; langue du document.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;head&amp;gt;&lt;/code&gt; &amp;rarr; contient les m&amp;eacute;tadonn&amp;eacute;es (titre, encodage, liens vers CSS/JS).&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;&amp;lt;body&amp;gt;&lt;/code&gt; &amp;rarr; contient le contenu visible.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e un fichier &lt;code&gt;index.html&lt;/code&gt; avec :&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;ton nom comme titre de page ;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;un &lt;code&gt;&amp;lt;h1&amp;gt;&lt;/code&gt; &quot;Bienvenue sur mon site&quot; ;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;un paragraphe de pr&amp;eacute;sentation.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;📗 MODULE 2 &amp;mdash; TEXTE ET CONTENU&lt;/h2&gt;
&lt;h3&gt;🔹 Titres et paragraphes&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;h1&amp;gt;Titre principal&amp;lt;/h1&amp;gt;
&amp;lt;h2&amp;gt;Sous-titre&amp;lt;/h2&amp;gt;
&amp;lt;h3&amp;gt;Sous-sous-titre&amp;lt;/h3&amp;gt;
&amp;lt;p&amp;gt;Voici un paragraphe de texte.&amp;lt;/p&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧩 Les titres vont de &lt;code&gt;&amp;lt;h1&amp;gt;&lt;/code&gt; &amp;agrave; &lt;code&gt;&amp;lt;h6&amp;gt;&lt;/code&gt; (le plus important au moins important).&lt;/p&gt;
&lt;h3&gt;🔹 Mise en valeur&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;p&amp;gt;Voici un &amp;lt;strong&amp;gt;texte important&amp;lt;/strong&amp;gt; et un &amp;lt;em&amp;gt;texte en italique&amp;lt;/em&amp;gt;.&amp;lt;/p&amp;gt;
&amp;lt;p&amp;gt;Un texte &amp;lt;mark&amp;gt;surlign&amp;eacute;&amp;lt;/mark&amp;gt; et un &amp;lt;small&amp;gt;texte plus petit&amp;lt;/small&amp;gt;.&amp;lt;/p&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Sauts de ligne et s&amp;eacute;parations&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;p&amp;gt;Premi&amp;egrave;re ligne&amp;lt;br&amp;gt;Deuxi&amp;egrave;me ligne&amp;lt;/p&amp;gt;
&amp;lt;hr&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e une section avec ton nom, ta biographie courte, et des mots mis en valeur.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📙 MODULE 3 &amp;mdash; LES LIENS ET LES IMAGES&lt;/h2&gt;
&lt;h3&gt;🔹 Les liens (hyperliens)&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;a href=&quot;https://openai.com&quot; target=&quot;_blank&quot;&amp;gt;Visitez OpenAI&amp;lt;/a&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;href&lt;/code&gt; = destination du lien.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;target=&quot;_blank&quot;&lt;/code&gt; = ouvre dans un nouvel onglet.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;h3&gt;🔹 Liens internes&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;a href=&quot;contact.html&quot;&amp;gt;Page de contact&amp;lt;/a&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Images&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;img src=&quot;images/photo.jpg&quot; alt=&quot;Description de l&#039;image&quot;&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;alt&lt;/code&gt; = description (accessibilit&amp;eacute; / SEO).&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Utiliser toujours un texte alternatif pertinent.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e une page avec :&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;un lien vers ton profil GitHub ;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;une image avec une l&amp;eacute;gende sous forme de paragraphe.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;📒 MODULE 4 &amp;mdash; LES LISTES&lt;/h2&gt;
&lt;h3&gt;🔹 Liste &amp;agrave; puces&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;ul&amp;gt;
  &amp;lt;li&amp;gt;HTML&amp;lt;/li&amp;gt;
  &amp;lt;li&amp;gt;CSS&amp;lt;/li&amp;gt;
  &amp;lt;li&amp;gt;JavaScript&amp;lt;/li&amp;gt;
&amp;lt;/ul&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Liste num&amp;eacute;rot&amp;eacute;e&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;ol&amp;gt;
  &amp;lt;li&amp;gt;&amp;Eacute;tape 1&amp;lt;/li&amp;gt;
  &amp;lt;li&amp;gt;&amp;Eacute;tape 2&amp;lt;/li&amp;gt;
&amp;lt;/ol&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Liste imbriqu&amp;eacute;e&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;ul&amp;gt;
  &amp;lt;li&amp;gt;Frontend
    &amp;lt;ul&amp;gt;
      &amp;lt;li&amp;gt;HTML&amp;lt;/li&amp;gt;
      &amp;lt;li&amp;gt;CSS&amp;lt;/li&amp;gt;
    &amp;lt;/ul&amp;gt;
  &amp;lt;/li&amp;gt;
&amp;lt;/ul&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e une liste de tes comp&amp;eacute;tences techniques et hobbies.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📘 MODULE 5 &amp;mdash; LES TABLEAUX&lt;/h2&gt;
&lt;h3&gt;🔹 Structure&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;table border=&quot;1&quot;&amp;gt;
  &amp;lt;caption&amp;gt;Emploi du temps&amp;lt;/caption&amp;gt;
  &amp;lt;tr&amp;gt;
    &amp;lt;th&amp;gt;Jour&amp;lt;/th&amp;gt;
    &amp;lt;th&amp;gt;Cours&amp;lt;/th&amp;gt;
  &amp;lt;/tr&amp;gt;
  &amp;lt;tr&amp;gt;
    &amp;lt;td&amp;gt;Lundi&amp;lt;/td&amp;gt;
    &amp;lt;td&amp;gt;HTML5&amp;lt;/td&amp;gt;
  &amp;lt;/tr&amp;gt;
&amp;lt;/table&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Fusion de cellules&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;td rowspan=&quot;2&quot;&amp;gt;Fusion verticale&amp;lt;/td&amp;gt;
&amp;lt;td colspan=&quot;2&quot;&amp;gt;Fusion horizontale&amp;lt;/td&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e un tableau simple de ton planning de la semaine.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📕 MODULE 6 &amp;mdash; FORMULAIRES&lt;/h2&gt;
&lt;h3&gt;🔹 Structure&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;form action=&quot;#&quot; method=&quot;post&quot;&amp;gt;
  &amp;lt;label for=&quot;name&quot;&amp;gt;Nom :&amp;lt;/label&amp;gt;
  &amp;lt;input type=&quot;text&quot; id=&quot;name&quot; name=&quot;name&quot; required&amp;gt;

  &amp;lt;label for=&quot;email&quot;&amp;gt;Email :&amp;lt;/label&amp;gt;
  &amp;lt;input type=&quot;email&quot; id=&quot;email&quot; name=&quot;email&quot; required&amp;gt;

  &amp;lt;label for=&quot;message&quot;&amp;gt;Message :&amp;lt;/label&amp;gt;
  &amp;lt;textarea id=&quot;message&quot; name=&quot;message&quot;&amp;gt;&amp;lt;/textarea&amp;gt;

  &amp;lt;button type=&quot;submit&quot;&amp;gt;Envoyer&amp;lt;/button&amp;gt;
&amp;lt;/form&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Types modernes HTML5&lt;/h3&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;email&lt;/code&gt;, &lt;code&gt;date&lt;/code&gt;, &lt;code&gt;color&lt;/code&gt;, &lt;code&gt;range&lt;/code&gt;, &lt;code&gt;file&lt;/code&gt;, &lt;code&gt;number&lt;/code&gt;, &lt;code&gt;url&lt;/code&gt;, etc.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Cr&amp;eacute;e un formulaire de contact avec les champs nom, email, message et un bouton &amp;ldquo;Envoyer&amp;rdquo;.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📗 MODULE 7 &amp;mdash; MULTIM&amp;Eacute;DIA&lt;/h2&gt;
&lt;h3&gt;🔹 Vid&amp;eacute;o&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;video controls width=&quot;400&quot;&amp;gt;
  &amp;lt;source src=&quot;video.mp4&quot; type=&quot;video/mp4&quot;&amp;gt;
  Votre navigateur ne supporte pas la vid&amp;eacute;o.
&amp;lt;/video&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Audio&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;audio controls&amp;gt;
  &amp;lt;source src=&quot;musique.mp3&quot; type=&quot;audio/mp3&quot;&amp;gt;
&amp;lt;/audio&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Int&amp;egrave;gre une courte vid&amp;eacute;o ou un fichier audio sur ta page.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📙 MODULE 8 &amp;mdash; STRUCTURE S&amp;Eacute;MANTIQUE HTML5&lt;/h2&gt;
&lt;h3&gt;🔹 Pourquoi ?&lt;/h3&gt;
&lt;p&gt;Les balises s&amp;eacute;mantiques donnent &lt;strong&gt;du sens&lt;/strong&gt; au contenu &amp;rarr; important pour SEO, accessibilit&amp;eacute;, et clart&amp;eacute;.&lt;/p&gt;
&lt;h3&gt;🔹 Balises principales&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;header&amp;gt;En-t&amp;ecirc;te du site&amp;lt;/header&amp;gt;
&amp;lt;nav&amp;gt;Navigation&amp;lt;/nav&amp;gt;
&amp;lt;main&amp;gt;
  &amp;lt;section&amp;gt;
    &amp;lt;article&amp;gt;
      &amp;lt;h2&amp;gt;Article 1&amp;lt;/h2&amp;gt;
      &amp;lt;p&amp;gt;Contenu...&amp;lt;/p&amp;gt;
    &amp;lt;/article&amp;gt;
  &amp;lt;/section&amp;gt;
&amp;lt;/main&amp;gt;
&amp;lt;aside&amp;gt;Informations compl&amp;eacute;mentaires&amp;lt;/aside&amp;gt;
&amp;lt;footer&amp;gt;Pied de page&amp;lt;/footer&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Structure ta page avec ces balises sans encore les styliser.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📘 MODULE 9 &amp;mdash; APIs HTML5 ESSENTIELLES&lt;/h2&gt;
&lt;h3&gt;🔹 Stockage local&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;script&amp;gt;
  localStorage.setItem(&#039;nom&#039;, &#039;Alice&#039;);
  alert(localStorage.getItem(&#039;nom&#039;));
&amp;lt;/script&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 G&amp;eacute;olocalisation (introduction)&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;script&amp;gt;
navigator.geolocation.getCurrentPosition(pos =&amp;gt; {
  console.log(pos.coords.latitude, pos.coords.longitude);
});
&amp;lt;/script&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice :&lt;/strong&gt;&lt;br&gt;Teste le stockage local avec ton nom et affiche-le dans une alerte.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;📗 MODULE 10 &amp;mdash; BONNES PRATIQUES&lt;/h2&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;Utiliser des balises s&amp;eacute;mantiques appropri&amp;eacute;es.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Toujours fournir des textes alternatifs aux images.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Valider le code sur &lt;a href=&quot;https://validator.w3.org/&quot;&gt;https://validator.w3.org/&lt;/a&gt;&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;Indenter correctement ton code.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;S&amp;eacute;parer contenu (HTML), style (CSS) et logique (JS).&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;hr&gt;
&lt;h2&gt;🎯 MODULE 11 &amp;mdash; MINI-PROJETS&lt;/h2&gt;
&lt;ol&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Page de blog simple&lt;/strong&gt; : titre, paragraphe, image.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Page de contact&lt;/strong&gt; : formulaire complet.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;strong&gt;Galerie d&amp;rsquo;images&lt;/strong&gt; : images organis&amp;eacute;es en grilles (bient&amp;ocirc;t stylis&amp;eacute;es avec CSS).&lt;/p&gt;
&lt;/li&gt;
&lt;/ol&gt;
&lt;hr&gt;
&lt;h2&gt;💼 PROJET FINAL &amp;mdash; PORTFOLIO HTML5 PUR&lt;/h2&gt;
&lt;h3&gt;🔹 Objectif :&lt;/h3&gt;
&lt;p&gt;Cr&amp;eacute;er ton &lt;strong&gt;Portfolio personnel&lt;/strong&gt; enti&amp;egrave;rement structur&amp;eacute; en HTML5, pr&amp;ecirc;t pour le design CSS.&lt;/p&gt;
&lt;h3&gt;🔹 Structure de fichiers&lt;/h3&gt;
&lt;pre&gt;&lt;code&gt;/portfolio
│
├── index.html
├── about.html
├── projects.html
└── contact.html
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Exemple : &lt;code&gt;index.html&lt;/code&gt;&lt;/h3&gt;
&lt;pre&gt;&lt;code class=&quot;language-html&quot;&gt;&amp;lt;!DOCTYPE html&amp;gt;
&amp;lt;html lang=&quot;fr&quot;&amp;gt;
&amp;lt;head&amp;gt;
  &amp;lt;meta charset=&quot;UTF-8&quot;&amp;gt;
  &amp;lt;title&amp;gt;Mon Portfolio&amp;lt;/title&amp;gt;
&amp;lt;/head&amp;gt;
&amp;lt;body&amp;gt;
  &amp;lt;header&amp;gt;
    &amp;lt;h1&amp;gt;Mon Portfolio&amp;lt;/h1&amp;gt;
    &amp;lt;nav&amp;gt;
      &amp;lt;a href=&quot;index.html&quot;&amp;gt;Accueil&amp;lt;/a&amp;gt;
      &amp;lt;a href=&quot;about.html&quot;&amp;gt;&amp;Agrave; propos&amp;lt;/a&amp;gt;
      &amp;lt;a href=&quot;projects.html&quot;&amp;gt;Projets&amp;lt;/a&amp;gt;
      &amp;lt;a href=&quot;contact.html&quot;&amp;gt;Contact&amp;lt;/a&amp;gt;
    &amp;lt;/nav&amp;gt;
  &amp;lt;/header&amp;gt;

  &amp;lt;main&amp;gt;
    &amp;lt;section&amp;gt;
      &amp;lt;h2&amp;gt;Bienvenue&amp;lt;/h2&amp;gt;
      &amp;lt;p&amp;gt;Je suis &amp;lt;strong&amp;gt;Nom Pr&amp;eacute;nom&amp;lt;/strong&amp;gt;, d&amp;eacute;veloppeur web passionn&amp;eacute;.&amp;lt;/p&amp;gt;
    &amp;lt;/section&amp;gt;

    &amp;lt;section&amp;gt;
      &amp;lt;h2&amp;gt;Mes projets r&amp;eacute;cents&amp;lt;/h2&amp;gt;
      &amp;lt;article&amp;gt;
        &amp;lt;h3&amp;gt;Projet 1&amp;lt;/h3&amp;gt;
        &amp;lt;p&amp;gt;Description du projet...&amp;lt;/p&amp;gt;
      &amp;lt;/article&amp;gt;
    &amp;lt;/section&amp;gt;
  &amp;lt;/main&amp;gt;

  &amp;lt;footer&amp;gt;
    &amp;lt;p&amp;gt;&amp;amp;copy; 2025 Mon Nom &amp;mdash; Tous droits r&amp;eacute;serv&amp;eacute;s.&amp;lt;/p&amp;gt;
  &amp;lt;/footer&amp;gt;
&amp;lt;/body&amp;gt;
&amp;lt;/html&amp;gt;
&lt;/code&gt;&lt;/pre&gt;
&lt;h3&gt;🔹 Les autres pages&lt;/h3&gt;
&lt;ul&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;about.html&lt;/code&gt; &amp;rarr; pr&amp;eacute;sentation d&amp;eacute;taill&amp;eacute;e de toi.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;projects.html&lt;/code&gt; &amp;rarr; liste de projets avec descriptions.&lt;/p&gt;
&lt;/li&gt;
&lt;li&gt;
&lt;p&gt;&lt;code&gt;contact.html&lt;/code&gt; &amp;rarr; formulaire de contact.&lt;/p&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;🧠 &lt;strong&gt;Exercice final :&lt;/strong&gt;&lt;br&gt;Construis ton Portfolio complet avec 4 pages et navigation fonctionnelle.&lt;br&gt;Teste-le sur ton navigateur, puis pr&amp;eacute;pare-toi &amp;agrave; l&amp;rsquo;&amp;eacute;tape suivante : le &lt;strong&gt;design CSS&lt;/strong&gt;.&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;🏁 F&amp;Eacute;LICITATIONS !&lt;/h2&gt;
&lt;p&gt;Tu ma&amp;icirc;trises maintenant :&lt;br&gt;✅ La structure HTML5&lt;br&gt;✅ Les balises s&amp;eacute;mantiques&lt;br&gt;✅ Les formulaires, tableaux, multim&amp;eacute;dia&lt;br&gt;✅ Les bonnes pratiques professionnelles&lt;/p&gt;
&lt;p&gt;👉 &lt;strong&gt;Prochaine &amp;eacute;tape :&lt;/strong&gt; on stylisera tout &amp;ccedil;a avec &lt;strong&gt;un cours CSS complet&lt;/strong&gt; (couleurs, mise en page, animations, responsive design).&lt;/p&gt;
&lt;hr&gt;
&lt;p&gt;&amp;nbsp;&lt;/p&gt;', '', 'Web', 'Débutant', NULL, '', '2', '0', 'active', '2025-10-23 16:27:12', '2025-10-23 16:34:29');

-- ================================================================
-- Table: user_activities
-- ================================================================
DROP TABLE IF EXISTS `user_activities`;
CREATE TABLE `user_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('post','comment','tutorial','project','like','download','login') NOT NULL,
  `activity_date` date NOT NULL,
  `count` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_activity` (`user_id`,`activity_type`,`activity_date`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`activity_type`),
  KEY `idx_date` (`activity_date`),
  CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: user_badges
-- ================================================================
DROP TABLE IF EXISTS `user_badges`;
CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `awarded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`),
  KEY `badge_id` (`badge_id`),
  CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `user_badges`
INSERT INTO `user_badges` (`id`, `user_id`, `badge_id`, `awarded_at`) VALUES ('5', '5', '1', '2025-10-23 14:48:58');
INSERT INTO `user_badges` (`id`, `user_id`, `badge_id`, `awarded_at`) VALUES ('6', '6', '1', '2025-10-23 16:07:37');
INSERT INTO `user_badges` (`id`, `user_id`, `badge_id`, `awarded_at`) VALUES ('7', '7', '1', '2025-10-23 16:36:45');

-- ================================================================
-- Table: user_follows
-- ================================================================
DROP TABLE IF EXISTS `user_follows`;
CREATE TABLE `user_follows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `follower_id` int(11) NOT NULL COMMENT 'ID de l''utilisateur qui suit',
  `following_id` int(11) NOT NULL COMMENT 'ID de l''utilisateur suivi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_follow` (`follower_id`,`following_id`),
  KEY `idx_follower` (`follower_id`),
  KEY `idx_following` (`following_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `user_follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_follows_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Table: user_skills
-- ================================================================
DROP TABLE IF EXISTS `user_skills`;
CREATE TABLE `user_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `level` enum('débutant','intermédiaire','avancé','expert') DEFAULT 'intermédiaire',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_skill` (`user_id`,`skill_id`),
  KEY `skill_id` (`skill_id`),
  CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `user_skills`
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('34', '5', '11', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('35', '5', '12', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('36', '5', '21', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('37', '5', '23', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('38', '5', '26', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('39', '5', '27', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('40', '5', '4', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('41', '5', '3', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('42', '5', '2', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('43', '5', '5', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('44', '5', '1', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('45', '5', '20', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('46', '5', '10', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('47', '5', '9', '', '2025-10-23 17:04:16');
INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`) VALUES ('48', '5', '6', '', '2025-10-23 17:04:16');

-- ================================================================
-- Table: users
-- ================================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prenom` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `university` varchar(255) DEFAULT NULL,
  `faculty` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL COMMENT 'Carte étudiant ou attestation',
  `role` enum('user','admin','company') DEFAULT 'user',
  `status` enum('pending','active','suspended','banned') DEFAULT 'active',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `can_create_tutorial` tinyint(1) DEFAULT 0 COMMENT 'Permission de créer des tutoriels',
  `can_create_project` tinyint(1) DEFAULT 0 COMMENT 'Permission de créer des projets',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_role` (`role`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `users`
INSERT INTO `users` (`id`, `prenom`, `nom`, `email`, `email_verified`, `email_verification_token`, `phone`, `password_hash`, `university`, `faculty`, `city`, `bio`, `photo_path`, `cv_path`, `document_path`, `role`, `status`, `reset_token`, `reset_token_expires`, `last_login`, `created_at`, `updated_at`, `can_create_tutorial`, `can_create_project`) VALUES ('5', 'MOHAMED', 'SARE', 'mhdcode7@gmail.com', '1', NULL, '+212 771 668 079', '$2y$10$e3REO9Zi4qOxSaBuvi4tqu8S6GeZbThX4UVsz9nyy6N6CCCReq3N2', 'Ecole Supérieure de Technologie de Casablanca', 'Génie informatique', 'Autre', '', 'uploads/profiles/profile_1761235456_94e43f9634f444c9e8b2239d6c5b694f.jpeg', NULL, NULL, 'admin', 'active', NULL, NULL, '2025-10-23 17:56:28', '2025-10-23 15:21:15', '2025-10-23 18:56:28', '1', '1');
INSERT INTO `users` (`id`, `prenom`, `nom`, `email`, `email_verified`, `email_verification_token`, `phone`, `password_hash`, `university`, `faculty`, `city`, `bio`, `photo_path`, `cv_path`, `document_path`, `role`, `status`, `reset_token`, `reset_token_expires`, `last_login`, `created_at`, `updated_at`, `can_create_tutorial`, `can_create_project`) VALUES ('6', 'MOHAMED', 'SARE', 'deleted_6_mohamedsare078@gmail.com', '1', '2357558562bc9ec0a45bc4296cebb8ff62cb5e4a8737c5f38294b52c379e4d53', '+22664 71 20 44', '$2y$12$/ly3qh0RbFihZxR/m5KSGOub8Mp1RMUOXXwG43EqO83ghoKteEZ7m', 'Ecole Supérieure de Technologie de Casablanca', 'Génie informatique', 'Autre', NULL, NULL, NULL, NULL, 'user', 'banned', NULL, NULL, '2025-10-23 16:07:47', '2025-10-23 17:07:37', '2025-10-23 17:43:26', '0', '0');
INSERT INTO `users` (`id`, `prenom`, `nom`, `email`, `email_verified`, `email_verification_token`, `phone`, `password_hash`, `university`, `faculty`, `city`, `bio`, `photo_path`, `cv_path`, `document_path`, `role`, `status`, `reset_token`, `reset_token_expires`, `last_login`, `created_at`, `updated_at`, `can_create_tutorial`, `can_create_project`) VALUES ('7', 'MHD', 'RESA', 'deleted_7_mhd@gmail.com', '1', '3437d00170595e29c87ef639ed498fd23c0fc446eb7f55c02396101dde5cf851', '+22664712044', '$2y$12$2FgpaI0WAGTTzjgVnPchZ.Xxc5speKBzw2q9h1Oqucs7oJ8/izcvm', 'IDS', 'SEG', 'Fada N&#039;Gourma', NULL, NULL, NULL, NULL, 'user', 'banned', NULL, NULL, NULL, '2025-10-23 17:36:45', '2025-10-23 17:47:14', '0', '0');

-- ================================================================
-- Table: visitor_logs
-- ================================================================
DROP TABLE IF EXISTS `visitor_logs`;
CREATE TABLE `visitor_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `page_url` text DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet','bot') DEFAULT 'desktop',
  `browser` varchar(50) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_session` (`session_id`),
  KEY `idx_activity` (`last_activity`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `visitor_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données pour la table `visitor_logs`
INSERT INTO `visitor_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `country`, `city`, `region`, `latitude`, `longitude`, `page_url`, `referrer`, `device_type`, `browser`, `os`, `session_id`, `last_activity`, `created_at`) VALUES ('76', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Localhost', 'Local', 'Development', NULL, NULL, '/HubTech/auth/logout', 'http://localhost/HubTech/user/edit', 'desktop', 'Edge', 'Windows', '9ca4c3015113ef82760baa481ee16660f3a8e11069408130954a35446a8a0969', '2025-10-23 15:29:37', '2025-10-23 15:20:03');
INSERT INTO `visitor_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `country`, `city`, `region`, `latitude`, `longitude`, `page_url`, `referrer`, `device_type`, `browser`, `os`, `session_id`, `last_activity`, `created_at`) VALUES ('77', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Localhost', 'Local', 'Development', NULL, NULL, '/HubTech/auth/logout', 'http://localhost/HubTech/home/index', 'desktop', 'Edge', 'Windows', '1e9f6a4c50385ebc1ce6831fdf9e6b658f66ad63dad2eb6db7728a0feb4c6486', '2025-10-23 17:05:25', '2025-10-23 15:29:37');
INSERT INTO `visitor_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `country`, `city`, `region`, `latitude`, `longitude`, `page_url`, `referrer`, `device_type`, `browser`, `os`, `session_id`, `last_activity`, `created_at`) VALUES ('78', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Localhost', 'Local', 'Development', NULL, NULL, '/HubTech/auth/logout', 'http://localhost/HubTech/user/profile/5', 'desktop', 'Edge', 'Windows', '642eb2c85c479d129c425b250ef113a2f55c2f6b36c02d0b46f61d259a599f2c', '2025-10-23 17:16:10', '2025-10-23 17:05:25');
INSERT INTO `visitor_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `country`, `city`, `region`, `latitude`, `longitude`, `page_url`, `referrer`, `device_type`, `browser`, `os`, `session_id`, `last_activity`, `created_at`) VALUES ('79', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'Localhost', 'Local', 'Development', NULL, NULL, '/HubTech/public/images/favicon.png', NULL, 'desktop', 'Chrome', 'Windows', '20ef613311ea054b65e56d979b8a7d619bae9b696392158c158e1761981589b5', '2025-10-23 17:17:30', '2025-10-23 17:10:01');
INSERT INTO `visitor_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `country`, `city`, `region`, `latitude`, `longitude`, `page_url`, `referrer`, `device_type`, `browser`, `os`, `session_id`, `last_activity`, `created_at`) VALUES ('80', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Localhost', 'Local', 'Development', NULL, NULL, '/HubTech/auth/logout', 'http://localhost/HubTech/home/index', 'desktop', 'Edge', 'Windows', '76b41ac6ee5146f6b7289bac3da86fb865e91064e7bf5dd610164217da949e72', '2025-10-23 17:51:06', '2025-10-23 17:16:10');
INSERT INTO `visitor_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `country`, `city`, `region`, `latitude`, `longitude`, `page_url`, `referrer`, `device_type`, `browser`, `os`, `session_id`, `last_activity`, `created_at`) VALUES ('81', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'Localhost', 'Local', 'Development', NULL, NULL, '/HubTech/public/images/favicon.png', NULL, 'desktop', 'Chrome', 'Windows', '0003ea71e972fcb2816731a413fd4e7d904b5f77e6d503389c8a11d05d7b924b', '2025-10-23 17:50:53', '2025-10-23 17:35:59');
INSERT INTO `visitor_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `country`, `city`, `region`, `latitude`, `longitude`, `page_url`, `referrer`, `device_type`, `browser`, `os`, `session_id`, `last_activity`, `created_at`) VALUES ('82', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Localhost', 'Local', 'Development', NULL, NULL, '/HubTech/auth/logout', 'http://localhost/HubTech/admin/index', 'desktop', 'Edge', 'Windows', '54defda96ad2d14678f5c5289b12f4133484fa233d7764d7487c44c19180666d', '2025-10-23 18:45:19', '2025-10-23 17:51:06');
INSERT INTO `visitor_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `country`, `city`, `region`, `latitude`, `longitude`, `page_url`, `referrer`, `device_type`, `browser`, `os`, `session_id`, `last_activity`, `created_at`) VALUES ('83', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Localhost', 'Local', 'Development', NULL, NULL, '/HubTech/auth/logout', 'http://localhost/HubTech/admin', 'desktop', 'Edge', 'Windows', '2cccc54b94173ca64273cefa8cd1dde81ec51edd6003bc770cf61db06c2eb898', '2025-10-23 18:52:59', '2025-10-23 18:45:19');
INSERT INTO `visitor_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `country`, `city`, `region`, `latitude`, `longitude`, `page_url`, `referrer`, `device_type`, `browser`, `os`, `session_id`, `last_activity`, `created_at`) VALUES ('84', NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', 'Localhost', 'Local', 'Development', NULL, NULL, '/HubTech/admin/downloadDatabaseBackup?type=full', 'http://localhost/HubTech/job/index', 'mobile', 'Edge', 'Linux', '7a06f3639a2e39f2df903fdf860b51b4d3c02ea12fdaadb4ed3ff8e3b546bd93', '2025-10-23 19:17:54', '2025-10-23 18:52:59');

-- ================================================================
-- Fin de la sauvegarde
-- ================================================================
