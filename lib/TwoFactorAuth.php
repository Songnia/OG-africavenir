<?php
// lib/TwoFactorAuth.php
require_once 'vendor/autoload.php'; // Pour l'envoi d'emails avancé

class TwoFactorAuth {
    private $db;
    private $wp_prefix;
    private $code_validity_minutes = 10;
    
    public function __construct($db_connection = null) {
        if ($db_connection) {
            $this->db = $db_connection['pdo'];
            $this->wp_prefix = $db_connection['prefix'];
        }
    }
    
    /**
     * Génère et envoie un code 2FA
     */
    public function genererEtEnvoyerCode($user_id, $email = null) {
        try {
            // Générer le code
            $code = $this->genererCode();
            
            // Si l'email n'est pas fourni, le récupérer depuis WordPress
            if (!$email) {
                $email = $this->getUserEmail($user_id);
            }
            
            if (!$email) {
                throw new Exception("Impossible de récupérer l'email de l'utilisateur");
            }
            
            // Stocker le code en base de données
            $this->store2FACode($user_id, $code);
            
            // Envoyer le code par email
            $sent = $this->envoyerCodeEmail($email, $code, $user_id);
            
            if ($sent) {
                return [
                    'success' => true,
                    'code' => $code, // À retirer en production - pour debug seulement
                    'valid_until' => date('Y-m-d H:i:s', time() + ($this->code_validity_minutes * 60))
                ];
            } else {
                throw new Exception("Échec de l'envoi du code par email");
            }
            
        } catch (Exception $e) {
            error_log("Erreur 2FA: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Vérifie un code 2FA
     */
    public function verifierCode($user_id, $code_saisi) {
        try {
            // Récupérer le code stocké
            $stored_code = $this->getStored2FACode($user_id);
            
            if (!$stored_code) {
                return [
                    'success' => false,
                    'error' => 'Aucun code actif trouvé'
                ];
            }
            
            // Vérifier l'expiration
            if (time() > strtotime($stored_code['expires_at'])) {
                $this->clear2FACode($user_id);
                return [
                    'success' => false,
                    'error' => 'Code expiré'
                ];
            }
            
            // Vérifier le code
            if ($stored_code['code'] === $code_saisi) {
                // Code valide - le supprimer et retourner succès
                $this->clear2FACode($user_id);
                $this->recordSuccessfulAuth($user_id);
                
                return [
                    'success' => true,
                    'message' => 'Authentification réussie'
                ];
            } else {
                // Code invalide - incrémenter les tentatives
                $this->incrementFailedAttempts($user_id);
                
                return [
                    'success' => false,
                    'error' => 'Code incorrect',
                    'attempts_remaining' => $this->getRemainingAttempts($user_id)
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erreur vérification 2FA: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors de la vérification'
            ];
        }
    }
    
    /**
     * Génère un code numérique sécurisé
     */
    private function genererCode() {
        // Générer un code à 6 chiffres
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Envoie le code par email
     */
    private function envoyerCodeEmail($email, $code, $user_id) {
        $subject = "Votre code de vérification - Fondation AfricAvenir";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
                .container { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; }
                .code { font-size: 32px; font-weight: bold; color: #2c5aa0; text-align: center; letter-spacing: 5px; margin: 20px 0; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Vérification de connexion</h2>
                <p>Bonjour,</p>
                <p>Utilisez le code suivant pour accéder à votre espace membre :</p>
                <div class='code'>$code</div>
                <p>Ce code expirera dans {$this->code_validity_minutes} minutes.</p>
                <div class='warning'>
                    <strong>⚠️ Sécurité :</strong> Ne partagez jamais ce code avec qui que ce soit.
                </div>
                <p>Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email.</p>
                <hr>
                <p style='color: #666; font-size: 12px;'>
                    Fondation AfricAvenir<br>
                    Email automatique - ne pas répondre
                </p>
            </div>
        </body>
        </html>
        ";
        
        $headers = [
            'From: ' . $this->getFromEmail(),
            'Content-Type: text/html; charset=UTF-8',
            'X-Priority: 1',
            'Importance: High'
        ];
        
        // Essayer d'abord avec wp_mail si disponible
        if (function_exists('wp_mail')) {
            return wp_mail($email, $subject, $message, $headers);
        } else {
            // Fallback avec mail() standard
            $headers_string = implode("\r\n", $headers);
            return mail($email, $subject, $message, $headers_string);
        }
    }
    
    // Méthodes de gestion de la base de données pour les codes 2FA
    private function store2FACode($user_id, $code) {
        // Supprimer d'abord les anciens codes
        $this->clear2FACode($user_id);
        
        $expires_at = date('Y-m-d H:i:s', time() + ($this->code_validity_minutes * 60));
        
        $query = "INSERT INTO membre_2fa_codes 
                 (user_id, code, expires_at, created_at) 
                 VALUES (?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$user_id, $code, $expires_at]);
    }
    
    private function getStored2FACode($user_id) {
        $query = "SELECT code, expires_at, attempts 
                 FROM membre_2fa_codes 
                 WHERE user_id = ? AND expires_at > NOW() AND attempts < 5
                 ORDER BY created_at DESC 
                 LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function clear2FACode($user_id) {
        $query = "DELETE FROM membre_2fa_codes WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$user_id]);
    }
    
    private function incrementFailedAttempts($user_id) {
        $query = "UPDATE membre_2fa_codes 
                 SET attempts = attempts + 1 
                 WHERE user_id = ? AND expires_at > NOW()";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$user_id]);
    }
    
    private function getRemainingAttempts($user_id) {
        $query = "SELECT attempts FROM membre_2fa_codes 
                 WHERE user_id = ? AND expires_at > NOW() 
                 ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (5 - $result['attempts']) : 0;
    }
    
    private function recordSuccessfulAuth($user_id) {
        $query = "UPDATE membre_profiles 
                 SET last_login = NOW(), login_count = COALESCE(login_count, 0) + 1 
                 WHERE user_id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$user_id]);
    }
    
    private function getUserEmail($user_id) {
        $query = "SELECT user_email FROM {$this->wp_prefix}users WHERE ID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['user_email'] : null;
    }
    
    private function getFromEmail() {
        // Essayer de récupérer l'email du site WordPress
        if (function_exists('get_bloginfo')) {
            $admin_email = get_bloginfo('admin_email');
            if ($admin_email) {
                return "Fondation AfricAvenir <$admin_email>";
            }
        }
        
        // Fallback
        return "Fondation AfricAvenir <noreply@africavenir.org>";
    }
    
    /**
     * Vérifie si l'utilisateur a activé la 2FA
     */
    public function is2FAEnabled($user_id) {
        $query = "SELECT meta_value FROM {$this->wp_prefix}usermeta 
                 WHERE user_id = ? AND meta_key = 'membre_2fa_enabled'";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['meta_value'] === '1';
    }
    
    /**
     * Active/désactive la 2FA pour un utilisateur
     */
    public function toggle2FA($user_id, $enable = true) {
        $value = $enable ? '1' : '0';
        
        $query = "INSERT INTO {$this->wp_prefix}usermeta (user_id, meta_key, meta_value) 
                 VALUES (?, 'membre_2fa_enabled', ?) 
                 ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$user_id, $value]);
    }
}
?>