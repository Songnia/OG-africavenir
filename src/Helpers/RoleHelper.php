<?php
namespace App\Helpers;

require_once __DIR__ . '/../../config/database.php';

/**
 * Role Helper - Simple WordPress-based role management
 * Uses wp_usermeta to store custom user levels and categories
 */
class RoleHelper {
    
    // Level constants
    const LEVEL_SUPERADMIN = 'superadmin';
    const LEVEL_ADMIN_MTM = 'admin-mtm';
    const LEVEL_MEMBER = 'member';
    
    /**
     * Get user's custom level from wp_usermeta
     */
    public static function getUserLevel($user_id) {
        if (!$user_id) return self::LEVEL_MEMBER;
        
        $database = new \Database();
        $conn = $database->getConnection();
        
        $query = "SELECT meta_value FROM wp_usermeta 
                  WHERE user_id = ? AND meta_key = 'user_level' LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['meta_value'] : self::LEVEL_MEMBER;
    }
    
    /**
     * Set user's custom level in wp_usermeta
     */
    public static function setUserLevel($user_id, $level) {
        if (!$user_id) return false;
        
        // Validate level
        if (!in_array($level, [self::LEVEL_SUPERADMIN, self::LEVEL_ADMIN_MTM, self::LEVEL_MEMBER])) {
            return false;
        }
        
        $database = new \Database();
        $conn = $database->getConnection();
        
        // Check if exists
        $query = "SELECT umeta_id FROM wp_usermeta 
                  WHERE user_id = ? AND meta_key = 'user_level' LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id]);
        $exists = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($exists) {
            // Update
            $query = "UPDATE wp_usermeta SET meta_value = ? 
                      WHERE user_id = ? AND meta_key = 'user_level'";
            $stmt = $conn->prepare($query);
            return $stmt->execute([$level, $user_id]);
        } else {
            // Insert
            $query = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) 
                      VALUES (?, 'user_level', ?)";
            $stmt = $conn->prepare($query);
            return $stmt->execute([$user_id, $level]);
        }
    }
    
    /**
     * Get member category from wp_usermeta
     */
    public static function getMemberCategory($user_id) {
        if (!$user_id) return null;
        
        $database = new \Database();
        $conn = $database->getConnection();
        
        $query = "SELECT meta_value FROM wp_usermeta 
                  WHERE user_id = ? AND meta_key = 'member_category' LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['meta_value'] : null;
    }
    
    /**
     * Set member category in wp_usermeta
     */
    public static function setMemberCategory($user_id, $category) {
        if (!$user_id) return false;
        
        $database = new \Database();
        $conn = $database->getConnection();
        
        // Check if exists
        $query = "SELECT umeta_id FROM wp_usermeta 
                  WHERE user_id = ? AND meta_key = 'member_category' LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id]);
        $exists = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($exists) {
            // Update
            $query = "UPDATE wp_usermeta SET meta_value = ? 
                      WHERE user_id = ? AND meta_key = 'member_category'";
            $stmt = $conn->prepare($query);
            return $stmt->execute([$category, $user_id]);
        } else {
            // Insert
            $query = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) 
                      VALUES (?, 'member_category', ?)";
            $stmt = $conn->prepare($query);
            return $stmt->execute([$user_id, $category]);
        }
    }
    
    /**
     * Check if user is admin (superadmin or admin-mtm)
     */
    public static function isAdmin($user_id) {
        $level = self::getUserLevel($user_id);
        return in_array($level, [self::LEVEL_SUPERADMIN, self::LEVEL_ADMIN_MTM]);
    }
    
    /**
     * Check if user is superadmin
     */
    public static function isSuperAdmin($user_id) {
        return self::getUserLevel($user_id) === self::LEVEL_SUPERADMIN;
    }
    
    /**
     * Check if user can perform action
     * Simple capability check based on level
     */
    public static function can($user_id, $capability) {
        $level = self::getUserLevel($user_id);
        
        $capabilities = [
            self::LEVEL_SUPERADMIN => [
                'manage_users',
                'manage_members',
                'manage_payments',
                'view_all_payments',
                'delete_payments',
                'view_own_profile',
                'edit_own_profile',
                'make_payments',
            ],
            self::LEVEL_ADMIN_MTM => [
                'manage_members',
                'manage_payments',
                'view_all_payments',
                'delete_payments',
                'view_own_profile',
                'edit_own_profile',
                'make_payments',
            ],
            self::LEVEL_MEMBER => [
                'view_own_profile',
                'edit_own_profile',
                'make_payments',
            ],
        ];
        
        return isset($capabilities[$level]) && in_array($capability, $capabilities[$level]);
    }
    
    /**
     * Get current user's level from session
     */
    public static function getCurrentUserLevel() {
        return $_SESSION['user_level'] ?? self::LEVEL_MEMBER;
    }
    
    /**
     * Check if current user is admin
     */
    public static function currentUserIsAdmin() {
        return in_array(self::getCurrentUserLevel(), [self::LEVEL_SUPERADMIN, self::LEVEL_ADMIN_MTM]);
    }
    
    /**
     * Check if current user is superadmin
     */
    public static function currentUserIsSuperAdmin() {
        return self::getCurrentUserLevel() === self::LEVEL_SUPERADMIN;
    }
}
