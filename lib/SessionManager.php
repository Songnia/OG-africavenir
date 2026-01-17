<?php
// lib/SessionManager.php
class SessionManager {
    private $db;
    private $wp_prefix;
    
    public function __construct($db_connection = null) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($db_connection) {
            $this->db = $db_connection['pdo'];
            $this->wp_prefix = $db_connection['prefix'];
        }
    }
    
    /**
     * Vérifie l'authentification WordPress et synchronise avec la session de l'application
     */
    public function checkWPAuth($user_id = null) {
        // Si user_id fourni, vérifier spécifiquement cet utilisateur
        if ($user_id) {
            return $this->validateSpecificUser($user_id);
        }
        
        // Vérifier la session existante
        if ($this->isAppAuthenticated()) {
            return true;
        }
        
        // Tenter la synchronisation avec WordPress
        return $this->syncWithWordPress();
    }
    
    /**
     * Synchronise la session avec l'authentification WordPress
     */
    public function syncWithWordPress() {
        // Méthode 1: Via cookie WordPress (si même domaine)
        $wp_user = $this->getWPUserFromCookie();
        
        if ($wp_user) {
            $this->createAppSession($wp_user);
            return true;
        }
        
        // Méthode 2: Via token d'authentification
        $wp_user = $this->getWPUserFromToken();
        
        if ($wp_user) {
            $this->createAppSession($wp_user);
            return true;
        }
        
        return false;
    }
    
    /**
     * Crée une session d'application pour un utilisateur WordPress
     */
    public function createAppSession($wp_user) {
        $_SESSION['app_auth'] = true;
        $_SESSION['wp_user_id'] = $wp_user['ID'];
        $_SESSION['user_login'] = $wp_user['user_login'];
        $_SESSION['user_email'] = $wp_user['user_email'];
        $_SESSION['display_name'] = $wp_user['display_name'];
        $_SESSION['last_activity'] = time();
        
        // Récupérer les données membre supplémentaires
        $membre_data = $this->getMembreData($wp_user['ID']);
        if ($membre_data) {
            $_SESSION['membre_data'] = $membre_data;
        }
        
        // Régénérer l'ID de session pour la sécurité
        session_regenerate_id(true);
    }
    
    /**
     * Démarre une session 2FA
     */
    public function start2FASession($user_id) {
        $_SESSION['2fa_pending'] = true;
        $_SESSION['2fa_user_id'] = $user_id;
        $_SESSION['2fa_start_time'] = time();
    }
    
    /**
     * Complète l'authentification 2FA
     */
    public function complete2FAAuth($user_id) {
        if (isset($_SESSION['2fa_pending']) && $_SESSION['2fa_user_id'] == $user_id) {
            unset($_SESSION['2fa_pending']);
            unset($_SESSION['2fa_user_id']);
            unset($_SESSION['2fa_start_time']);
            
            // Créer la session complète
            $wp_user = $this->getWPUserData($user_id);
            if ($wp_user) {
                $this->createAppSession($wp_user);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Vérifie si l'utilisateur a besoin de 2FA
     */
    public function requires2FA() {
        return isset($_SESSION['2fa_pending']) && $_SESSION['2fa_pending'] === true;
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        // Supprimer toutes les variables de session
        $_SESSION = [];
        
        // Supprimer le cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Détruire la session
        session_destroy();
    }
    
    /**
     * Vérifie l'activité de la session et expire si nécessaire
     */
    public function checkSessionActivity() {
        $max_idle_time = 3600; // 1 heure
        
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity']) > $max_idle_time) {
            $this->logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Méthodes privées utilitaires
    private function isAppAuthenticated() {
        return isset($_SESSION['app_auth']) && 
               $_SESSION['app_auth'] === true && 
               $this->checkSessionActivity();
    }
    
    private function validateSpecificUser($user_id) {
        return $this->isAppAuthenticated() && 
               isset($_SESSION['wp_user_id']) && 
               $_SESSION['wp_user_id'] == $user_id;
    }
    
    private function getWPUserFromCookie() {
        // Implémenter la lecture du cookie WordPress
        // Ceci nécessite la compréhension de la structure du cookie WordPress
        
        if (isset($_COOKIE['wordpress_logged_in_'])) {
            // Décoder le cookie WordPress (simplifié)
            $cookie_parts = explode('|', $_COOKIE['wordpress_logged_in_']);
            if (count($cookie_parts) >= 3) {
                $username = $cookie_parts[0];
                return $this->getWPUserByLogin($username);
            }
        }
        
        return null;
    }
    
    private function getWPUserFromToken() {
        $token = $_GET['auth_token'] ?? $_POST['auth_token'] ?? $_SERVER['HTTP_AUTH_TOKEN'] ?? '';
        
        if ($token) {
            // Valider le token contre la base de données
            $query = "SELECT user_id FROM {$this->wp_prefix}usermeta 
                     WHERE meta_key = 'app_auth_token' AND meta_value = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([hash('sha256', $token)]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $this->getWPUserData($result['user_id']);
            }
        }
        
        return null;
    }
    
    private function getWPUserByLogin($username) {
        $query = "SELECT ID, user_login, user_email, display_name 
                 FROM {$this->wp_prefix}users 
                 WHERE user_login = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getWPUserData($user_id) {
        $query = "SELECT ID, user_login, user_email, display_name 
                 FROM {$this->wp_prefix}users 
                 WHERE ID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getMembreData($user_id) {
        $membre = new Membre(['pdo' => $this->db, 'prefix' => $this->wp_prefix]);
        return $membre->getMembreByWPId($user_id);
    }
    
    /**
     * Génère un token d'authentification pour l'application
     */
    public function generateAuthToken($user_id, $expires_hours = 24) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + ($expires_hours * 3600));
        
        $query = "INSERT INTO {$this->wp_prefix}usermeta 
                 (user_id, meta_key, meta_value) 
                 VALUES (?, 'app_auth_token', ?) 
                 ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id, hash('sha256', $token)]);
        
        return [
            'token' => $token,
            'expires' => $expires
        ];
    }
}
?>