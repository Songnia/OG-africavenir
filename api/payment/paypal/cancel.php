<?php
// API Endpoint: /api/payment/paypal/cancel.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../src/Controllers/PayPalController.php';

use App\Controllers\PayPalController;

// Vérifier méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer données
$input = json_decode(file_get_contents('php://input'), true);
$contributionID = $input['contributionID'] ?? $_POST['contributionID'] ?? null;

if (!$contributionID) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID contribution manquant']);
    exit;
}

// Instancier contrôleur et exécuter
try {
    $controller = new PayPalController();
    $response = $controller->cancelOrder($contributionID);
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
