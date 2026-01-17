<?php
namespace App\Models;

require_once __DIR__ . '/../../config/database.php';

class Contribution {
    private $conn;
    private $table_name = "app_contributions";

    public function __construct() {
        $database = new \Database();
        $this->conn = $database->getConnection();
    }

    public function create($user_id, $amount, $transaction_id, $status = 'pending', $payment_method = 'Cash', $created_at = null, $motif = 'Contribution') {
        $query = "INSERT INTO " . $this->table_name . " 
                SET user_id=:user_id, amount=:amount, transaction_id=:transaction_id, status=:status, payment_method=:payment_method, motif=:motif, created_at=:created_at";
        
        $stmt = $this->conn->prepare($query);

        $created_at = $created_at ?? date('Y-m-d H:i:s');

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":transaction_id", $transaction_id);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":payment_method", $payment_method);
        $stmt->bindParam(":motif", $motif);
        $stmt->bindParam(":created_at", $created_at);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }

    public function getAll() {
        $query = "SELECT c.*, u.display_name as member_name, u.user_email 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN wp_users u ON c.user_id = u.ID 
                  ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT c.*, u.display_name as member_name 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN wp_users u ON c.user_id = u.ID 
                  WHERE c.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['amount'])) {
            $fields[] = "amount = :amount";
            $params[':amount'] = $data['amount'];
        }
        
        if (isset($data['payment_method']) || isset($data['mode'])) {
            $fields[] = "payment_method = :payment_method";
            $params[':payment_method'] = $data['payment_method'] ?? $data['mode'];
        }
        
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params[':status'] = $data['status'];
        }
        
        if (isset($data['transaction_id'])) {
            $fields[] = "transaction_id = :transaction_id";
            $params[':transaction_id'] = $data['transaction_id'];
        }
        
        if (isset($data['motif'])) {
            $fields[] = "transaction_id = :motif";
            $params[':motif'] = $data['motif'];
        }
        
        if (isset($data['date']) || isset($data['created_at'])) {
            $fields[] = "created_at = :created_at";
            $params[':created_at'] = $data['date'] ?? $data['created_at'] ?? date('Y-m-d H:i:s');
        }
        
        if (empty($fields)) {
            return false; // Nothing to update
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET " . implode(', ', $fields) . "
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function updateByTransactionId($transaction_id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status";
        
        $params = [':status' => $data['status'], ':transaction_id' => $transaction_id];
        
        if (isset($data['payment_method'])) {
            $query .= ", payment_method = :payment_method";
            $params[':payment_method'] = $data['payment_method'];
        }
        
        if (isset($data['cinetpay_trans_id'])) {
            $query .= ", transaction_id = :cinetpay_trans_id";
            $params[':cinetpay_trans_id'] = $data['cinetpay_trans_id'];
        }
        
        $query .= " WHERE transaction_id = :transaction_id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    public function getByTransactionId($transaction_id) {
        $query = "SELECT c.*, u.display_name as member_name, u.user_email 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN wp_users u ON c.user_id = u.ID 
                  WHERE c.transaction_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$transaction_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getPendingContributions($days = 7) {
        $query = "SELECT c.*, u.display_name as member_name, u.user_email 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN wp_users u ON c.user_id = u.ID 
                  WHERE c.status = 'pending' 
                  AND c.created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                  ORDER BY c.created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getHistory($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByUserId($user_id) {
        // Alias for getHistory() for better semantic clarity
        return $this->getHistory($user_id);
    }
    
    public function getContributionsByCategory($year = null) {
        $yearCondition = "";
        $params = [];
        
        if ($year) {
            $yearCondition = " AND YEAR(c.created_at) = :year";
            $params[':year'] = $year;
        }

        $query = "SELECT m.meta_value as category, SUM(c.amount) as total
                  FROM " . $this->table_name . " c
                  JOIN wp_usermeta m ON c.user_id = m.user_id
                  WHERE m.meta_key = 'categorie' AND c.status = 'completed'
                  $yearCondition
                  GROUP BY m.meta_value";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $totals = [];
        foreach ($results as $row) {
            $totals[$row['category']] = $row['total'];
        }
        return $totals;
    }

    public function getMonthlyStats($year) {
        $query = "SELECT MONTH(created_at) as month, SUM(amount) as total
                  FROM " . $this->table_name . "
                  WHERE YEAR(created_at) = :year AND status = 'completed'
                  GROUP BY MONTH(created_at)
                  ORDER BY month";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':year' => $year]);
        
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Initialize all months to 0
        $monthlyData = array_fill(1, 12, 0);
        
        foreach ($results as $row) {
            $monthlyData[(int)$row['month']] = (float)$row['total'];
        }
        
        return array_values($monthlyData); // Return indexed array 0-11
    }
}
