<?php
/**
 * Fonctions helper globales
 */

/**
 * Nettoyer le double encodage des apostrophes et guillemets
 * 
 * @param string $text Texte à nettoyer
 * @return string
 */
function cleanApostrophes($text)
{
    if (empty($text)) {
        return '';
    }
    
    // Nettoyer le double encodage HTML
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Double décodage pour les cas &amp;#039;
    
    // Remplacer les entités spécifiques
    $text = str_replace(['&amp;#039;', '&#039;', '&quot;', '&amp;quot;'], ["'", "'", '"', '"'], $text);
    
    return $text;
}

/**
 * Nettoyer et sécuriser le texte pour l'affichage
 * 
 * @param string $text Texte à nettoyer et sécuriser
 * @return string
 */
function cleanAndSecure($text)
{
    if (empty($text)) {
        return '';
    }
    
    // Nettoyer le double encodage
    $text = cleanApostrophes($text);
    
    // Sécuriser pour l'affichage HTML (mais garder les apostrophes)
    $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
    
    return $text;
}

/**
 * Convertir un timestamp en format "il y a X temps"
 * 
 * @param string $timestamp Date au format MySQL
 * @return string
 */
function timeAgo($timestamp)
{
    if (empty($timestamp)) {
        return 'Date inconnue';
    }
    
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    
    // Si la différence est négative mais petite (moins d'une heure), 
    // c'est probablement un décalage d'horloge - afficher "à l'instant"
    if ($time_difference < 0) {
        if (abs($time_difference) < 3600) {
            return 'À l\'instant';
        }
        return 'À venir';
    }
    
    if ($time_difference < 60) {
        return 'À l\'instant';
    } elseif ($time_difference < 3600) {
        $minutes = round($time_difference / 60);
        return $minutes . ' min';
    } elseif ($time_difference < 86400) {
        $hours = round($time_difference / 3600);
        return $hours . ' h';
    } elseif ($time_difference < 604800) {
        $days = round($time_difference / 86400);
        return $days . ' j';
    } elseif ($time_difference < 2592000) {
        $weeks = round($time_difference / 604800);
        return $weeks . ' sem';
    } elseif ($time_difference < 31536000) {
        $months = round($time_difference / 2592000);
        return $months . ' mois';
    } else {
        $years = round($time_difference / 31536000);
        return $years . ' an' . ($years > 1 ? 's' : '');
    }
}

/**
 * Formater un nombre avec séparateurs
 * 
 * @param int|float $number Nombre à formater
 * @param int $decimals Nombre de décimales
 * @return string
 */
function formatNumber($number, $decimals = 0)
{
    return number_format($number, $decimals, ',', ' ');
}

/**
 * Tronquer un texte à une longueur donnée
 * 
 * @param string $text Texte à tronquer
 * @param int $length Longueur maximale
 * @param string $suffix Suffixe à ajouter si tronqué
 * @return string
 */
function truncate($text, $length = 100, $suffix = '...')
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Convertir le Markdown en HTML (simple)
 * 
 * @param string $markdown Texte Markdown
 * @return string HTML
 */
function markdownToHtml($markdown)
{
    if (empty($markdown)) {
        return '';
    }
    
    // Nettoyer le double-encodage HTML
    $markdown = html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $markdown = html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Double décodage pour les cas &amp;#039;
    $markdown = str_replace(['&amp;#039;', '&#039;', '&quot;', '&amp;quot;'], ["'", "'", '"', '"'], $markdown);
    
    $html = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');
    
    // Titres
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
    
    // Gras et italique
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
    
    // Listes
    $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
    
    // Code inline
    $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
    
    // Blocs de code
    $html = preg_replace('/```([^`]+)```/s', '<pre><code>$1</code></pre>', $html);
    
    // Liens
    $html = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" target="_blank">$1</a>', $html);
    
    // Paragraphes
    $html = nl2br($html);
    
    // Corriger les apostrophes et guillemets
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
    
    return $html;
}

/**
 * Générer un slug à partir d'un texte
 * 
 * @param string $text Texte source
 * @return string Slug
 */
function slugify($text)
{
    // Remplacer les caractères accentués
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    
    // Convertir en minuscules
    $text = strtolower($text);
    
    // Remplacer les caractères non alphanumériques par des tirets
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Supprimer les tirets en début et fin
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Vérifier si une URL est une URL YouTube
 * 
 * @param string $url URL à vérifier
 * @return bool
 */
function isYoutubeUrl($url)
{
    return preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/', $url);
}

/**
 * Extraire l'ID d'une vidéo YouTube
 * 
 * @param string $url URL YouTube
 * @return string|null ID de la vidéo ou null
 */
function getYoutubeId($url)
{
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches);
    return $matches[1] ?? null;
}