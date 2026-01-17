<?php
// API endpoint pour créer une contribution (pour PayPal workflow)
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Non authentifié'
    ]);
    exit;
}

require_once __DIR__ . '/../src/Models/Contribution.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

$amount = $_POST['amount'] ?? 0;
$motif = $_POST['motif'] ?? 'Contribution';
$mode = $_POST['mode'] ?? 'paypal';

// Validate amount
if ($amount < 1000) {
    echo json_encode([
        'success' => false,
        'message' => 'Le montant minimum est de 1000 FCFA'
    ]);
    exit;
}

try {
    $contributionModel = new \App\Models\Contribution();
    
    // Map payment mode
    $paymentMethod = match($mode) {
        'mobile_money' => 'Mobile Money',
        'card' => 'Carte bancaire',
        'paypal' => 'PayPal',
        'bank_transfer' => 'Virement bancaire',
        default => 'PayPal'
    };
    
    // Create contribution with pending status
    $contributionID = $contributionModel->create(
        $_SESSION['user_id'],
        $amount,
        $motif,
        'pending',
        $paymentMethod,
        date('Y-m-d H:i:s')
    );
    
    if ($contributionID) {
        echo json_encode([
            'success' => true,
            'contributionID' => $contributionID,
            'message' => 'Contribution créée avec succès'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la création de la contribution'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
