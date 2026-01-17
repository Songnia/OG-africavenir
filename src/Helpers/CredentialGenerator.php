<?php
namespace App\Helpers;

require_once __DIR__ . '/../../config/database.php';

/**
 * Credential Generator
 * Generates usernames and secure passwords for new members
 */
class CredentialGenerator {
    
    /**
     * Generate username from email or name
     * Format: firstname.lastname or email prefix
     */
    public static function generateUsername($email, $firstName = '', $lastName = '') {
        // Try to use firstname.lastname first
        if (!empty($firstName) && !empty($lastName)) {
            $username = strtolower($firstName . '.' . $lastName);
            $username = self::sanitizeUsername($username);
            
            // Check uniqueness
            if (self::isUsernameUnique($username)) {
                return $username;
            }
            
            // If not unique, add number
            $counter = 1;
            while (!self::isUsernameUnique($username . $counter)) {
                $counter++;
            }
            return $username . $counter;
        }
        
        // Fallback to email prefix
        $emailPrefix = explode('@', $email)[0];
        $username = self::sanitizeUsername($emailPrefix);
        
        // Check uniqueness
        if (self::isUsernameUnique($username)) {
            return $username;
        }
        
        // If not unique, add number
        $counter = 1;
        while (!self::isUsernameUnique($username . $counter)) {
            $counter++;
        }
        return $username . $counter;
    }
    
    /**
     * Sanitize username - remove special characters, keep only alphanumeric and dots
     */
    private static function sanitizeUsername($username) {
        // Remove accents
        $username = iconv('UTF-8', 'ASCII//TRANSLIT', $username);
        // Keep only alphanumeric, dots, and underscores
        $username = preg_replace('/[^a-z0-9._]/', '', strtolower($username));
        // Remove multiple dots
        $username = preg_replace('/\.+/', '.', $username);
        // Remove leading/trailing dots
        $username = trim($username, '.');
        return $username;
    }
    
    /**
     * Check if username is unique in database
     */
    public static function isUsernameUnique($username) {
        $database = new \Database();
        $conn = $database->getConnection();
        
        $query = "SELECT ID FROM wp_users WHERE user_login = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute([$username]);
        
        return $stmt->rowCount() === 0;
    }
    
    /**
     * Generate secure random password
     */
    public static function generatePassword($length = 12) {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()-_=+';
        
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        
        // Ensure at least one of each type
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        // Fill the rest randomly
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Shuffle the password
        $password = str_shuffle($password);
        
        return $password;
    }
    
    /**
     * Generate both username and password
     */
    public static function generateCredentials($email, $firstName = '', $lastName = '') {
        return [
            'username' => self::generateUsername($email, $firstName, $lastName),
            'password' => self::generatePassword()
        ];
    }
}
