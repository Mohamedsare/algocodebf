<?php
/**
 * Autoloader PSR-4 pour charger automatiquement les classes
 */

class Autoloader
{
    /**
     * Charge automatiquement une classe
     * 
     * @param string $className Nom de la classe à charger
     */
    public static function load($className)
    {
        // Chemins possibles pour les classes
        $paths = [
            APP . '/Controllers/',
            APP . '/Models/',
            APP . '/Core/',
            APP . '/Helpers/'
        ];

        foreach ($paths as $path) {
            $file = $path . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}

