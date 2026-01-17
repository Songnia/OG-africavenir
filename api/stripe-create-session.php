<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/StripeController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

header('Content-Type: application/json');

// Ensure user is logged in (optional, depending on flow, but usually required for member donation)
// For registration, user might not be logged in yet.
session_start();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$amount = $input['amount'] ?? 0;
$description = $input['description'] ?? 'Contribution';
$email = $input['email'] ?? null;
$userId = $input['userId'] ?? ($_SESSION['user_id'] ?? null);

$registrationID = $input['registrationID'] ?? null;

// If no user ID (e.g. registration), we might need to handle it. 
// For now, assume userId is passed or in session.
if (!$userId && !$registrationID) {
    // If it's registration, the user ID should have been created in the previous step and passed here.
    echo json_encode(['success' => false, 'message' => 'User ID or Registration ID required']);
    exit;
}

$controller = new \App\Controllers\StripeController();
$response = $controller->createSession($userId, $amount, $description, $email, $registrationID);

echo json_encode($response);
