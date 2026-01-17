<?php
namespace App\Auth;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Helpers/RoleHelper.php';

use App\Helpers\RoleHelper;

class Auth {
    private $db;

    public function __construct() {
        $database = new \Database();
        $this->db = $database->getConnection();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password) {
        // Try to authenticate against wp_users
        // Note: WP passwords are hashed using PHPass. 
        // For this standalone implementation without loading WP core, verification is tricky.
        // Ideally, we should include wp-load.php if possible.
        
        // Check if we can find wp-load.php
        $wp_load_path = __DIR__ . '/../../../../wp-load.php'; // Adjust path as needed
        
        if (file_exists($wp_load_path)) {
            define('WP_USE_THEMES', false);
            require_once($wp_load_path);
            
            $creds = array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => true
            );
            
            $user = wp_signon($creds, false);
            
            if (is_wp_error($user)) {
                return false;
            }
            
            $_SESSION['user_id'] = $user->ID;
            $_SESSION['user_login'] = $user->user_login;
            $_SESSION['user_email'] = $user->user_email;
            
            // Get custom user level and category
            $_SESSION['user_level'] = RoleHelper::getUserLevel($user->ID);
            $_SESSION['member_category'] = RoleHelper::getMemberCategory($user->ID);
            
            // Get WP roles for compatibility
            $user_meta = get_userdata($user->ID);
            $_SESSION['user_roles'] = $user_meta->roles;
            
            return true;
        } else {
            // Fallback: Direct DB check
            // WARNING: This uses MD5 which is only for legacy WP passwords or test users created with MD5.
            // Modern WP uses PHPass. If you need to support modern WP hashes without loading WP, 
            // you need to include the PHPass library.
            
            $query = "SELECT ID, user_login, user_email, user_pass FROM wp_users WHERE user_login = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $username);
            $stmt->execute();
            
            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                // Check MD5 (for test user)
                if (md5($password) === $row['user_pass']) {
                    $_SESSION['user_id'] = $row['ID'];
                    $_SESSION['user_login'] = $row['user_login'];
                    $_SESSION['user_email'] = $row['user_email'];
                    
                    // Get custom user level and category
                    $_SESSION['user_level'] = RoleHelper::getUserLevel($row['ID']);
                    $_SESSION['member_category'] = RoleHelper::getMemberCategory($row['ID']);
                    
                    // Fetch WP roles manually from usermeta for compatibility
                    $queryMeta = "SELECT meta_value FROM wp_usermeta WHERE user_id = ? AND meta_key = 'wp_capabilities'";
                    $stmtMeta = $this->db->prepare($queryMeta);
                    $stmtMeta->bindParam(1, $row['ID']);
                    $stmtMeta->execute();
                    
                    if ($metaRow = $stmtMeta->fetch(\PDO::FETCH_ASSOC)) {
                        $_SESSION['user_roles'] = unserialize($metaRow['meta_value']);
                    }
                    
                    return true;
                }
            }
            
            return false;
        }
    }

    public function logout() {
        session_destroy();
        // If WP loaded
        if (function_exists('wp_logout')) {
            wp_logout();
        }
    }

    public function send2FACode($user_id) {
        $code = rand(100000, 999999);
        $_SESSION['2fa_code'] = $code;
        $_SESSION['2fa_user_id'] = $user_id;
        // In real app, send via SMS/Email
        error_log("2FA Code for user $user_id: $code");
        return $code;
    }

    public function verify2FACode($code) {
        if (isset($_SESSION['2fa_code']) && $_SESSION['2fa_code'] == $code) {
            $_SESSION['is_2fa_verified'] = true;
            unset($_SESSION['2fa_code']);
            return true;
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']); // Add && isset($_SESSION['is_2fa_verified']) if enforcing 2FA strictly
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'login' => $_SESSION['user_login'],
                'email' => $_SESSION['user_email'],
                'roles' => $_SESSION['user_roles'] ?? []
            ];
        }
        return null;
    }
}
