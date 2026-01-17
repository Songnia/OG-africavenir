<?php
// API endpoint pour vérifier et capturer les paiements PayPal
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

// Vérifier que l'utilisateur est connecté (optionnel selon votre logique)
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode(['success' => false, 'message' => 'Non autorisé']);
//     exit;
// }

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
if (empty($data['orderID'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Order ID manquant'
    ]);
    exit;
}

$orderId = $data['orderID'];
$contributionId = $data['contributionID'] ?? null;
$memberId = $data['memberID'] ?? null;

try {
    // Initialiser le service PayPal
    $paypal = new PayPalService();
    
    // Capturer le paiement
    $captureResponse = $paypal->captureOrder($orderId);
    
    if (!isset($captureResponse['success']) || !$captureResponse['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la capture du paiement',
            'details' => $captureResponse
        ]);
        exit;
    }
    
    // Vérifier le statut
    if (isset($captureResponse['status']) && $captureResponse['status'] === 'COMPLETED') {
        // Le paiement est confirmé
        $contributionModel = new Contribution();
        
        // Mettre à jour la contribution
        if ($contributionId) {
            $updateData = [
                'status' => 'completed',
                'transaction_id' => $orderId,
                'paypal_capture_id' => $captureResponse['id'] ?? $orderId
            ];
            
            $updated = $contributionModel->update($contributionId, $updateData);
            
            if ($updated) {
                // Si c'est pour un nouveau membre, activer le compte
                if ($memberId) {
                    $memberModel = new Member();
                    $memberModel->activateMember($memberId);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Paiement confirmé et enregistré',
                    'contribution_id' => $contributionId,
                    'order_id' => $orderId,
                    'capture_id' => $captureResponse['id'] ?? $orderId
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour de la contribution'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ID de contribution manquant'
            ]);
        }
    } else {
        // Paiement non complété
        echo json_encode([
            'success' => false,
            'message' => 'Le paiement n\'a pas été complété',
            'status' => $captureResponse['status'] ?? 'UNKNOWN'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
