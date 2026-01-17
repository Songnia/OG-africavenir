<?php
// API Endpoint: /api/update-payment-status.php
header('Content-Type: application/json');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication (Admin only)
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Non autorisé - Session expirée']);
    exit;
}

// Check if user is admin or superadmin
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT meta_value FROM wp_usermeta WHERE user_id = ? AND meta_key = 'user_role'");
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

// Allow admin-mtm and superadmin roles
$allowedRoles = ['admin-mtm', 'superadmin'];
if (!in_array($userRole, $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Non autorisé - Accès administrateur requis']);
    exit;
}

require_once __DIR__ . '/../src/Models/Contribution.php';

use App\Models\Contribution;

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$status = $input['status'] ?? null;

if (!$id || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID et statut requis']);
    exit;
}

// Validate status
$allowedStatuses = ['pending', 'completed', 'failed', 'cancelled', 'paye', 'a-payer', 'en-attente', 'annule'];
if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Statut invalide']);
    exit;
}

try {
    $contributionModel = new Contribution();
    
    // Update status
    $result = $contributionModel->update($id, ['status' => $status]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Impossible de mettre à jour le statut']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
