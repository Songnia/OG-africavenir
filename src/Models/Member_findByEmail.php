    
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
