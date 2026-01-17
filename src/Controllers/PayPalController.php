<?php
namespace App\Controllers;

require_once __DIR__ . '/../Services/PayPalService.php';
require_once __DIR__ . '/../Models/Contribution.php';
require_once __DIR__ . '/MemberController.php';

use App\Services\PayPalService;
use App\Models\Contribution;
use App\Controllers\MemberController;

class PayPalController {
    private $paypalService;
    private $contributionModel;

    public function __construct() {
        $this->paypalService = new PayPalService();
        $this->contributionModel = new Contribution();
    }

    /**
     * Crée une commande PayPal et enregistre la transaction en BDD
     */
    public function createOrder($userId, $amount, $description = 'Contribution', $registrationID = null) {
        try {
            // 1. Validation basique
            if ($amount < 1000) {
                return ['success' => false, 'message' => 'Montant minimum invalide (1000 FCFA)'];
            }

            // 2. Créer une référence de transaction unique
            $reference = 'PAYPAL-' . date('YmdHis') . '-' . uniqid();
            
            // 3. Déterminer le motif lisible basé sur la description
            $motif = 'Contribution'; // Valeur par défaut
            if (stripos($description, 'Inscription') !== false || stripos($description, 'Adhésion') !== false) {
                $motif = 'Adhésion';
            } elseif (stripos($description, 'Don') !== false) {
                $motif = 'Don';
            }

            $contributionId = null;

            // 4. Créer la contribution en BDD (Status: pending) ONLY if not registration
            if (!$registrationID) {
                $contributionId = $this->contributionModel->create(
                    $userId,
                    $amount,
                    $reference,
                    'pending',
                    'PayPal',
                    date('Y-m-d H:i:s'),
                    $motif // Motif lisible
                );

                if (!$contributionId) {
                    return ['success' => false, 'message' => 'Erreur BDD: Impossible de créer la contribution'];
                }
            } else {
                // Update registration with transaction ID
                $db = new \Database();
                $conn = $db->getConnection();
                $stmt = $conn->prepare("UPDATE app_registrations SET transaction_id = ? WHERE id = ?");
                $stmt->execute([$reference, $registrationID]);
            }

            // 4. Appeler PayPal pour créer l'ordre
            // Conversion XAF -> EUR (PayPal ne supporte pas XAF)
            // Taux fixe: 1 EUR = 655.957 XAF
            $amountEUR = round($amount / 655.957, 2);

            $orderData = [
                'amount' => $amountEUR,
                'currency' => 'EUR',
                'description' => $description . ($contributionId ? " (Ref: $contributionId)" : ""),
                'transaction_id' => $reference, // Correct key for PayPalService
                'custom_id' => $contributionId ?? $registrationID
            ];

            $paypalResponse = $this->paypalService->createOrder($orderData);

            if (isset($paypalResponse['id'])) {
                // Succès : On retourne l'ID de l'ordre PayPal au frontend
                return [
                    'success' => true,
                    'orderID' => $paypalResponse['id'],
                    'contributionID' => $contributionId,
                    'reference' => $reference
                ];
            } else {
                // Échec PayPal : On pourrait supprimer la contribution ou la marquer failed
                return [
                    'success' => false, 
                    'message' => 'Erreur PayPal: ' . ($paypalResponse['message'] ?? 'Erreur inconnue'),
                    'details' => $paypalResponse
                ];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Capture le paiement après approbation de l'utilisateur
     */
    public function captureOrder($paypalOrderId, $contributionId, $registrationID = null) {
        try {
            // 1. Capturer le paiement via PayPal
            $captureResponse = $this->paypalService->captureOrder($paypalOrderId);

            // 2. Vérifier le statut
            if (isset($captureResponse['status']) && $captureResponse['status'] === 'COMPLETED') {
                
                // 3. Mettre à jour la BDD
                // On met à jour le transaction_id avec le vrai ID de transaction PayPal si disponible
                $paypalTransactionId = $captureResponse['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalOrderId;
                
                $credentials = null;

                if ($contributionId) {
                    $updateData = [
                        'status' => 'completed',
                        'transaction_id' => $paypalTransactionId // Mettre à jour avec l'ID final
                    ];
                    $this->contributionModel->update($contributionId, $updateData);
                } elseif ($registrationID) {
                    // Update registration transaction ID first
                    $db = new \Database();
                    $conn = $db->getConnection();
                    $stmt = $conn->prepare("UPDATE app_registrations SET transaction_id = ? WHERE id = ?");
                    $stmt->execute([$paypalTransactionId, $registrationID]);

                    // Finalize Registration
                    $memberController = new MemberController();
                    $userId = $memberController->finalizeRegistration($registrationID);
                    
                    if ($userId) {
                        // Get credentials to return
                        $reg = $memberController->getRegistrationByTransaction($paypalTransactionId);
                        if ($reg) {
                            $data = json_decode($reg['data'], true);
                            $credentials = $data['generated_credentials'] ?? $data['credentials'] ?? null;
                        }
                    }
                }

                return [
                    'success' => true,
                    'status' => 'COMPLETED',
                    'transaction_id' => $paypalTransactionId,
                    'credentials' => $credentials
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Paiement non complété. Statut: ' . ($captureResponse['status'] ?? 'Inconnu'),
                    'details' => $captureResponse
                ];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
    /**
     * Annule une commande et met à jour le statut en BDD
     */
    public function cancelOrder($contributionId) {
        try {
            $updateData = [
                'status' => 'cancelled'
            ];

            if ($this->contributionModel->update($contributionId, $updateData)) {
                return ['success' => true, 'message' => 'Commande annulée'];
            } else {
                return ['success' => false, 'message' => 'Impossible de mettre à jour le statut'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
}
