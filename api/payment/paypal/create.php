<?php
// API Endpoint: /api/payment/paypal/create.php
header('Content-Type: application/json');

// Gestion CORS si nécessaire (à adapter selon config serveur)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../src/Controllers/PayPalController.php';

use App\Controllers\PayPalController;

// Vérifier authentification ou ID membre explicite (pour inscription)
$userId = $_SESSION['user_id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);
$explicitUserId = $input['memberID'] ?? $_POST['memberID'] ?? null;

$registrationID = $input['registrationID'] ?? $_POST['registrationID'] ?? null;

if (!$userId && $explicitUserId) {
    // TODO: Ajouter une vérification de sécurité ici (token temporaire d'inscription)
    $userId = $explicitUserId;
}

// Allow if we have a registrationID (new user flow)
if (!$userId && !$registrationID) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié et aucun ID membre ou inscription fourni']);
    exit;
}

// Vérifier méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer données
$input = json_decode(file_get_contents('php://input'), true);
// Support pour form-data aussi
$amount = $input['amount'] ?? $_POST['amount'] ?? 0;
$description = $input['description'] ?? $_POST['description'] ?? 'Contribution Membre';

// Instancier contrôleur et exécuter
try {
    $controller = new PayPalController();
    $response = $controller->createOrder($userId, $amount, $description, $registrationID);
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
