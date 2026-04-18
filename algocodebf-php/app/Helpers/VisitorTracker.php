<?php

class VisitorTracker
{
    private static $tracked = false;

    /**
     * Track la visite actuelle
     */
    public static function track()
    {
        if (self::$tracked) {
            return; // Déjà tracké
        }

        try {
            $db = Database::getInstance();
            
            // Créer ou récupérer session_id
            if (!isset($_SESSION['visitor_session_id'])) {
                $_SESSION['visitor_session_id'] = self::generateSessionId();
            }
            
            $sessionId = $_SESSION['visitor_session_id'];
            $userId = $_SESSION['user_id'] ?? null;
            $ipAddress = self::getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $pageUrl = $_SERVER['REQUEST_URI'] ?? '/';
            $referrer = $_SERVER['HTTP_REFERER'] ?? null;
            
            // Détecter le type d'appareil
            $deviceType = self::detectDeviceType($userAgent);
            $browser = self::detectBrowser($userAgent);
            $os = self::detectOS($userAgent);
            
            // Géolocalisation (simplifié - peut être amélioré avec une API)
            $geoData = self::getGeoData($ipAddress);
            
            // Vérifier si une entrée existe déjà pour cette session
            $existing = $db->queryOne(
                "SELECT id FROM visitor_logs WHERE session_id = ? ORDER BY created_at DESC LIMIT 1",
                [$sessionId]
            );
            
            if (!$existing) {
                // Nouvelle visite
                $db->execute(
                    "INSERT INTO visitor_logs 
                    (user_id, ip_address, user_agent, country, city, region, page_url, referrer, device_type, browser, os, session_id, last_activity) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $userId,
                        $ipAddress,
                        $userAgent,
                        $geoData['country'] ?? null,
                        $geoData['city'] ?? null,
                        $geoData['region'] ?? null,
                        $pageUrl,
                        $referrer,
                        $deviceType,
                        $browser,
                        $os,
                        $sessionId
                    ]
                );
            } else {
                // Mettre à jour l'activité
                $db->execute(
                    "UPDATE visitor_logs SET last_activity = NOW(), page_url = ? WHERE session_id = ?",
                    [$pageUrl, $sessionId]
                );
            }
            
            // Mettre à jour les utilisateurs en ligne
            if ($userId) {
                $db->execute(
                    "INSERT INTO online_users (user_id, session_id, ip_address, last_seen, page_url) 
                     VALUES (?, ?, ?, NOW(), ?)
                     ON DUPLICATE KEY UPDATE last_seen = NOW(), page_url = ?",
                    [$userId, $sessionId, $ipAddress, $pageUrl, $pageUrl]
                );
            } else {
                $db->execute(
                    "INSERT INTO online_users (user_id, session_id, ip_address, last_seen, page_url) 
                     VALUES (NULL, ?, ?, NOW(), ?)
                     ON DUPLICATE KEY UPDATE last_seen = NOW(), page_url = ?",
                    [$sessionId, $ipAddress, $pageUrl, $pageUrl]
                );
            }
            
            self::$tracked = true;
            
        } catch (Exception $e) {
            error_log("VisitorTracker Error: " . $e->getMessage());
        }
    }

    /**
     * Enregistre une activité utilisateur
     */
    public static function trackActivity($userId, $activityType)
    {
        if (!$userId || !in_array($activityType, ['post', 'comment', 'tutorial', 'project', 'like', 'download', 'login'])) {
            return;
        }

        try {
            $db = Database::getInstance();
            $today = date('Y-m-d');
            
            $db->execute(
                "INSERT INTO user_activities (user_id, activity_type, activity_date, count) 
                 VALUES (?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE count = count + 1",
                [$userId, $activityType, $today]
            );
        } catch (Exception $e) {
            error_log("TrackActivity Error: " . $e->getMessage());
        }
    }

    private static function generateSessionId()
    {
        return bin2hex(random_bytes(32));
    }

    private static function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
    }

    private static function detectDeviceType($userAgent)
    {
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            return 'tablet';
        }
        if (preg_match('/(up\.browser|up\.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            return 'mobile';
        }
        if (preg_match('/(bot|crawler|spider|scraper|slurp)/i', $userAgent)) {
            return 'bot';
        }
        return 'desktop';
    }

    private static function detectBrowser($userAgent)
    {
        if (preg_match('/Edg/i', $userAgent)) return 'Edge';
        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/MSIE|Trident/i', $userAgent)) return 'IE';
        return 'Unknown';
    }

    private static function detectOS($userAgent)
    {
        if (preg_match('/windows|win32/i', $userAgent)) return 'Windows';
        if (preg_match('/macintosh|mac os x/i', $userAgent)) return 'Mac';
        if (preg_match('/linux/i', $userAgent)) return 'Linux';
        if (preg_match('/android/i', $userAgent)) return 'Android';
        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) return 'iOS';
        return 'Unknown';
    }

    private static function getGeoData($ip)
    {
        // Pour l'instant, on retourne des données vides
        // Dans un environnement de production, utilisez une API comme ip-api.com ou ipapi.co
        // Exemple: $data = @file_get_contents("http://ip-api.com/json/{$ip}");
        
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return [
                'country' => 'Localhost',
                'city' => 'Local',
                'region' => 'Development'
            ];
        }
        
        return [
            'country' => null,
            'city' => null,
            'region' => null
        ];
    }
}

