<?php
// API Endpoint: /api/payment/paypal/capture.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../src/Controllers/PayPalController.php';

use App\Controllers\PayPalController;

// Vérifier authentification (Optionnel pour capture si on a l'orderID valide)
// On laisse passer si pas connecté car le callback peut venir après expiration session
// ou lors de l'inscription publique
$userId = $_SESSION['user_id'] ?? null;

// Vérifier méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer données
$input = json_decode(file_get_contents('php://input'), true);
$orderID = $input['orderID'] ?? $_POST['orderID'] ?? null;
$contributionID = $input['contributionID'] ?? $_POST['contributionID'] ?? null;
$registrationID = $input['registrationID'] ?? $_POST['registrationID'] ?? null;

if (!$orderID || (!$contributionID && !$registrationID)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants (orderID, contributionID ou registrationID)']);
    exit;
}

// Instancier contrôleur et exécuter
try {
    $controller = new PayPalController();
    $response = $controller->captureOrder($orderID, $contributionID, $registrationID);
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
