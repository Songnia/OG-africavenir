<?php
namespace App\Models;

require_once __DIR__ . '/../../config/database.php';

class Member {
    private $conn;
    private $table_name = "wp_users"; // Assuming standard WP table

    public $id;
    public $user_login;
    public $user_email;
    public $display_name;

    public function __construct() {
        $database = new \Database();
        $this->conn = $database->getConnection();
    }

    public function getProfile($id) {
        $query = "SELECT ID, user_login, user_email, display_name FROM " . $this->table_name . " WHERE ID = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['ID'];
            $this->user_login = $row['user_login'];
            $this->user_email = $row['user_email'];
            $this->display_name = $row['display_name'];
            return $row;
        }

        return null;
    }
    
    // Add methods to get user meta (address, phone, etc.) from wp_usermeta
    public function getMeta($user_id, $key) {
        $query = "SELECT meta_value FROM wp_usermeta WHERE user_id = ? AND meta_key = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $key);
        $stmt->execute();
        
        if($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row['meta_value'];
        }
        return null;
    }

    public function getAllMembers() {
        // 1. Get all users
        $query = "SELECT ID, user_email, display_name, user_login FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 2. Get all metadata for these users
        // Optimization: In a huge DB, you might want to filter by user_ids, but for now fetch all is fine or join.
        // Let's do a fetch all from wp_usermeta for simplicity and completeness as requested.
        $metaQuery = "SELECT user_id, meta_key, meta_value FROM wp_usermeta";
        $metaStmt = $this->conn->prepare($metaQuery);
        $metaStmt->execute();
        $allMeta = $metaStmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Group metadata by user_id
        $userMeta = [];
        foreach ($allMeta as $meta) {
            $userMeta[$meta['user_id']][$meta['meta_key']] = $meta['meta_value'];
        }

        // 4. Merge metadata into user arrays
        foreach ($users as &$user) {
            $id = $user['ID'];
            if (isset($userMeta[$id])) {
                // Merge meta into user. Meta keys will overwrite user columns if duplicate (unlikely except ID)
                $user = array_merge($user, $userMeta[$id]);
            }
        }

        return $users;
    }
    public function getMemberCountsByCategory() {
        $query = "SELECT meta_value as category, COUNT(user_id) as count 
                  FROM wp_usermeta 
                  WHERE meta_key = 'categorie' 
                  GROUP BY meta_value";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $counts = [];
        foreach ($results as $row) {
            $counts[$row['category']] = $row['count'];
        }
        return $counts;
    }

    public function createMember($data) {
        // 1. Insert into wp_users
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_login, user_pass, user_nicename, user_email, user_registered, display_name) 
                  VALUES (:user_login, :user_pass, :user_nicename, :user_email, NOW(), :display_name)";
        
        $stmt = $this->conn->prepare($query);
        
        // Use provided username and password
        $username = $data['username'];
        $password = md5($data['password']); // Using MD5 for WP compatibility
        $nicename = $username;
        $email = $data['email'];
        $displayName = $data['display_name'];
        
        $stmt->bindParam(':user_login', $username);
        $stmt->bindParam(':user_pass', $password);
        $stmt->bindParam(':user_nicename', $nicename);
        $stmt->bindParam(':user_email', $email);
        $stmt->bindParam(':display_name', $displayName);
        
        if (!$stmt->execute()) {
            return false;
        }
        
        $user_id = $this->conn->lastInsertId();
        
        // 2. Insert ALL metadata into wp_usermeta (using French field names)
        $metaData = [
            // Basic info
            'first_name' => $data['first_name'] ?? $data['prenom'] ?? '',
            'last_name' => $data['last_name'] ?? $data['nom'] ?? '',
            
            // Contact info
            'telephone' => $data['telephone'] ?? $data['phone'] ?? '',
            'adresse' => $data['adresse'] ?? $data['address'] ?? '',
            'boite_postale' => $data['boite_postale'] ?? '',
            'ville' => $data['ville'] ?? $data['city'] ?? '',
            'quartier' => $data['quartier'] ?? '',
            'pays' => $data['pays'] ?? '',
            
            // Personal info
            'date_naissance' => $data['date_naissance'] ?? '',
            'etat_civil' => $data['etat_civil'] ?? '',
            
            // Professional/Academic info
            'activite' => $data['activite'] ?? $data['type_activite'] ?? '',
            'statut_professionnel' => $data['statut_professionnel'] ?? '',
            'parcours_type' => $data['parcours_type'] ?? '',
            'etablissement_scolaire' => $data['etablissement_scolaire'] ?? '',
            'dernier_diplome' => $data['dernier_diplome'] ?? '',
            'centre_interet' => $data['centre_interet'] ?? '',
            
            // Membership info
            'categorie' => $data['categorie'] ?? $data['category'] ?? 'member',
            'montant_contribution' => $data['montant_contribution'] ?? '',
            'montant_libre_val' => $data['montant_libre_val'] ?? '',
            'mode_paiement_contribution' => $data['mode_paiement_contribution'] ?? '',
            
            // WordPress capabilities
            'wp_capabilities' => serialize(['subscriber' => true])
        ];
        
        foreach ($metaData as $key => $value) {
            // Insert even if empty to maintain consistency
            $queryMeta = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES (?, ?, ?)";
            $stmtMeta = $this->conn->prepare($queryMeta);
            $stmtMeta->execute([$user_id, $key, $value]);
        }
        
        return $user_id;
    }
    
    /**
     * Find member by email
     */
    public function findByEmail($email) {
        $query = "SELECT ID, user_login, user_email, display_name 
                  FROM " . $this->table_name . " 
                  WHERE user_email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function deleteMember($id) {
        // Delete from wp_usermeta
        $queryMeta = "DELETE FROM wp_usermeta WHERE user_id = ?";
        $stmtMeta = $this->conn->prepare($queryMeta);
        $stmtMeta->execute([$id]);

        // Delete from wp_users
        $queryUser = "DELETE FROM " . $this->table_name . " WHERE ID = ?";
        $stmtUser = $this->conn->prepare($queryUser);
        if ($stmtUser->execute([$id])) {
            return true;
        }
        return false;
    }

    public function getMemberDetails($id) {
        // Get user basic info
        $query = "SELECT ID, user_login, user_email, display_name, user_registered FROM " . $this->table_name . " WHERE ID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) return null;

        // Get meta
        $queryMeta = "SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id = ?";
        $stmtMeta = $this->conn->prepare($queryMeta);
        $stmtMeta->execute([$id]);
        $meta = $stmtMeta->fetchAll(\PDO::FETCH_KEY_PAIR); // Returns [key => value]

        return array_merge($user, $meta);
    }

    public function updateMember($id, $data) {
        // Update wp_users (basic info)
        $query = "UPDATE " . $this->table_name . " SET user_email = :email, display_name = :display_name WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        $displayName = ($data['nom'] ?? '') . ' ' . ($data['prenom'] ?? '');
        $stmt->execute([
            ':email' => $data['email'] ?? '',
            ':display_name' => $displayName,
            ':id' => $id
        ]);

        // Update wp_usermeta
        // For simplicity, we delete relevant keys and re-insert, or update if exists. 
        // WP has update_user_meta, here we do manual UPSERT or just update known keys.
        $meta_fields = [
            'first_name' => $data['prenom'] ?? '',
            'last_name' => $data['nom'] ?? '',
            'telephone' => $data['telephone'] ?? '',
            'ville' => $data['ville'] ?? '',
            'activite' => $data['activite'] ?? $data['type_activite'] ?? '',
            'categorie' => $data['categorie'] ?? 'member',
            'date_naissance' => $data['date_naissance'] ?? '',
            'etat_civil' => $data['etat_civil'] ?? '',
            'adresse' => $data['adresse'] ?? '',
            'boite_postale' => $data['boite_postale'] ?? '',
            'pays' => $data['pays'] ?? '',
            'quartier' => $data['quartier'] ?? '',
            'parcours_type' => $data['parcours_type'] ?? '',
            'etablissement_scolaire' => $data['etablissement_scolaire'] ?? '',
            'dernier_diplome' => $data['dernier_diplome'] ?? '',
            'statut_professionnel' => $data['statut_professionnel'] ?? '',
            'centre_interet' => $data['centre_interet'] ?? '',
            'montant_contribution' => $data['montant_contribution'] ?? '',
            'montant_libre_val' => $data['montant_libre_val'] ?? '',
            'mode_paiement_contribution' => $data['mode_paiement_contribution'] ?? ''
        ];

        $queryMeta = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES (?, ?, ?) 
                      ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)"; 
        // Note: wp_usermeta usually doesn't have a unique constraint on (user_id, meta_key) in standard WP, 
        // it relies on meta_id. So DELETE then INSERT or specific UPDATE is safer if we don't track meta_id.
        // Let's use a helper to update each key.
        
        foreach ($meta_fields as $key => $value) {
            $this->updateMeta($id, $key, $value);
        }

        return true;
    }

    private function updateMeta($user_id, $key, $value) {
        // Check if exists
        $check = "SELECT umeta_id FROM wp_usermeta WHERE user_id = ? AND meta_key = ?";
        $stmt = $this->conn->prepare($check);
        $stmt->execute([$user_id, $key]);
        if ($stmt->fetch()) {
            $update = "UPDATE wp_usermeta SET meta_value = ? WHERE user_id = ? AND meta_key = ?";
            $stmtUpd = $this->conn->prepare($update);
            $stmtUpd->execute([$value, $user_id, $key]);
        } else {
            $insert = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES (?, ?, ?)";
            $stmtIns = $this->conn->prepare($insert);
            $stmtIns->execute([$user_id, $key, $value]);
        }
    }
}
