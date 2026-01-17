<?php
/**
 * URL Helper Functions
 * Provides utilities for generating dynamic URLs
 */

class UrlHelper {
    /**
     * Get the base URL of the application
     * Works with localhost, ngrok, and production domains
     * 
     * @return string Base URL (e.g., "https://example.ngrok.io" or "http://localhost/OG-afrcavenir")
     */
    public static function getBaseUrl() {
        // Determine protocol
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        
        // Get host (works with ngrok, localhost, production)
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Get the base path (for subdirectory installations like /OG-afrcavenir)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname(dirname($scriptName)); // Go up two levels from current script
        
        // Clean up the path
        $basePath = str_replace('\\', '/', $basePath);
        $basePath = rtrim($basePath, '/');
        
        // If we're in the root, basePath will be empty
        if ($basePath === '/.') {
            $basePath = '';
        }
        
        return $protocol . '://' . $host . $basePath;
    }
    
    /**
     * Build a full URL from a relative path
     * 
     * @param string $path Relative path (e.g., "registration-success.php")
     * @return string Full URL
     */
    public static function buildUrl($path) {
        $baseUrl = self::getBaseUrl();
        $path = ltrim($path, '/');
        return $baseUrl . '/' . $path;
    }
}
