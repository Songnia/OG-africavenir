<?php
// API endpoint pour créer une commande PayPal
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../src/Services/PayPalService.php';
require_once __DIR__ . '/../src/Models/Contribution.php';
require_once __DIR__ . '/../src/Models/Member.php';

use App\Services\PayPalService;
use App\Models\Contribution;
use App\Models\Member;

// Récupérer les données JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Données invalides'
    ]);
    exit;
}

// Valider les paramètres requis
if (empty($data['amount']) || empty($data['contributionID'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Montant et ID de contribution requis'
    ]);
    exit;
}

$amount = $data['amount'];
$contributionId = $data['contributionID'];
$description = $data['description'] ?? 'Contribution AfricAvenir';

try {
    // Récupérer la contribution
    $contributionModel = new Contribution();
    $contribution = $contributionModel->getById($contributionId);
    
    if (!$contribution) {
        echo json_encode([
            'success' => false,
            'message' => 'Contribution introuvable'
        ]);
        exit;
    }
    
    // Générer un transaction_id unique
    $transactionId = 'AFRC_PP_' . time() . '_' . $contributionId;
    
    // Mettre à jour la contribution avec le transaction_id
    $contributionModel->update($contributionId, [
        'transaction_id' => $transactionId
    ]);
    
    // Initialiser le service PayPal
    $paypal = new PayPalService();
    
    // Créer la commande PayPal
    $orderData = [
        'transaction_id' => $transactionId,
        'amount' => $amount,
        'description' => $description
    ];
    
    $response = $paypal->createOrder($orderData);
    
    if (isset($response['success']) && $response['success'] && isset($response['id'])) {
        // Commande créée avec succès
        echo json_encode([
            'success' => true,
            'orderID' => $response['id'],
            'transaction_id' => $transactionId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la création de la commande PayPal',
            'details' => $response
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
