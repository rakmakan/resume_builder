<?php
/**
 * Centralized configuration loader for Resume Builder
 */

class ConfigLoader {
    private static $config = null;
    private static $dbConfig = null;
    
    /**
     * Load application configuration
     */
    public static function getAppConfig() {
        if (self::$config === null) {
            $configPath = dirname(__DIR__) . '/config/app.json';
            if (!file_exists($configPath)) {
                throw new Exception("App configuration file not found: " . $configPath);
            }
            
            $configContent = file_get_contents($configPath);
            self::$config = json_decode($configContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON in app configuration: " . json_last_error_msg());
            }
        }
        
        return self::$config;
    }
    
    /**
     * Load database configuration
     */
    public static function getDbConfig() {
        if (self::$dbConfig === null) {
            $configPath = dirname(__DIR__) . '/config/database.json';
            if (!file_exists($configPath)) {
                throw new Exception("Database configuration file not found: " . $configPath);
            }
            
            $configContent = file_get_contents($configPath);
            self::$dbConfig = json_decode($configContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON in database configuration: " . json_last_error_msg());
            }
        }
        
        return self::$dbConfig;
    }
    
    /**
     * Get database path
     */
    public static function getDatabasePath() {
        $dbConfig = self::getDbConfig();
        
        // Check if running in Docker
        if (isset($_ENV['DOCKER_MODE']) && $_ENV['DOCKER_MODE'] === 'true') {
            return '/var/www/html/database_docker/resume.sqlite';
        }
        
        // Use environment variable if set
        if (isset($_ENV['DATABASE_PATH'])) {
            return $_ENV['DATABASE_PATH'];
        }
        
        return $dbConfig['database']['absolute_path'];
    }
    
    /**
     * Get application root path
     */
    public static function getRootPath() {
        $appConfig = self::getAppConfig();
        return $appConfig['paths']['root'];
    }
    
    /**
     * Get specific path from configuration
     */
    public static function getPath($pathKey) {
        $appConfig = self::getAppConfig();
        return $appConfig['paths'][$pathKey] ?? null;
    }
    
    /**
     * Check if backup is enabled
     */
    public static function isBackupEnabled() {
        $dbConfig = self::getDbConfig();
        return $dbConfig['backup']['enabled'] ?? false;
    }
    
    /**
     * Get backup directory
     */
    public static function getBackupDirectory() {
        $dbConfig = self::getDbConfig();
        $backupDir = $dbConfig['backup']['directory'] ?? '../database/backups';
        
        // Convert relative path to absolute
        if (!is_absolute_path($backupDir)) {
            $backupDir = dirname(__DIR__) . '/' . $backupDir;
        }
        
        return $backupDir;
    }
}

/**
 * Helper function to check if path is absolute
 */
function is_absolute_path($path) {
    return (PHP_OS_FAMILY === 'Windows') 
        ? (preg_match('/^[A-Za-z]:\\\\/', $path) === 1)
        : (strpos($path, '/') === 0);
}