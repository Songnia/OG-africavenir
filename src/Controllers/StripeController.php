<?php
namespace App\Controllers;

require_once __DIR__ . '/../Services/StripeService.php';
require_once __DIR__ . '/../Models/Contribution.php';
require_once __DIR__ . '/../Services/EmailService.php';
require_once __DIR__ . '/../Models/Member.php';

use App\Services\StripeService;
use App\Models\Contribution;
use App\Services\EmailService;
use App\Models\Member;

class StripeController {
    private $stripeService;
    private $contributionModel;
    private $emailService;
    private $memberModel;

    public function __construct() {
        $this->stripeService = new StripeService();
        $this->contributionModel = new Contribution();
        $this->emailService = new EmailService();
        $this->memberModel = new Member();
    }

    /**
     * Create a Stripe Checkout Session and record pending transaction
     */
    /**
     * Create a Stripe Checkout Session and record pending transaction
     */
    public function createSession($userId, $amount, $description = 'Contribution', $email = null, $registrationID = null) {
        try {
            // 1. Basic Validation
            if ($amount < 100) { // Stripe minimum is usually around $0.50 equivalent
                return ['success' => false, 'message' => 'Montant invalide'];
            }

            // 2. Create Reference
            $reference = 'STRIPE-' . date('YmdHis') . '-' . uniqid();

            $contributionId = null;

            // 3. Create Contribution in DB (Pending) ONLY if not registration
            if (!$registrationID) {
                $contributionId = $this->contributionModel->create(
                    $userId,
                    $amount,
                    $reference,
                    'pending',
                    'Carte bancaire',
                    date('Y-m-d H:i:s'),
                    $description // Add motif
                );

                if (!$contributionId) {
                    return ['success' => false, 'message' => 'Erreur BDD: Impossible de créer la contribution'];
                }
            } else {
                // Update registration with transaction ID
                // We need to do this so we can find it back on return
                $db = new \Database();
                $conn = $db->getConnection();
                $stmt = $conn->prepare("UPDATE app_registrations SET transaction_id = ? WHERE id = ?");
                $stmt->execute([$reference, $registrationID]);
            }

            // 4. Call Stripe Service
            require_once __DIR__ . '/../Helpers/UrlHelper.php';
            
            $paymentData = [
                'transaction_id' => $reference,
                'amount' => $amount,
                'description' => $description,
                'customer_email' => $email,
                'contribution_id' => $contributionId,
                'registration_id' => $registrationID, // Pass registration ID to service
                'success_url' => $registrationID 
                    ? \UrlHelper::buildUrl('registration-success.php?session_id={CHECKOUT_SESSION_ID}')
                    : null
            ];

            $response = $this->stripeService->initiatePayment($paymentData);

            if ($response['success']) {
                return [
                    'success' => true,
                    'sessionId' => $response['session_id'],
                    'paymentUrl' => $response['payment_url'],
                    'contributionID' => $contributionId,
                    'reference' => $reference
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur Stripe: ' . $response['message']
                ];
            }

        } catch (\Exception $e) {
            // Log the detailed error
            error_log("Stripe Controller Error: " . $e->getMessage());
            
            return [
                'success' => false, 
                'message' => "Une erreur est survenue lors du traitement de votre demande. Veuillez réessayer."
            ];
        }
    }

    /**
     * Handle Webhook Event
     */
    public function handleWebhook($payload, $sig_header) {
        $result = $this->stripeService->handleWebhook($payload, $sig_header);

        if ($result['success'] && isset($result['type']) && $result['type'] === 'checkout.session.completed') {
            $data = $result['data'];
            $transactionId = $data['transaction_id']; // This is client_reference_id usually
            
            // Check metadata for registration ID
            $metadata = $data['metadata'] ?? [];
            $registrationID = $metadata['registration_id'] ?? null;

            if ($registrationID) {
                // Handle Registration Finalization
                require_once __DIR__ . '/MemberController.php';
                $memberController = new MemberController();
                
                $userId = $memberController->finalizeRegistration($registrationID);
                
                if ($userId) {
                    // Create Contribution
                    $amount = $data['amount_total'] / 100; // Assuming currency is correct
                    // Use actual Stripe payment intent ID as transaction ID if available
                    $stripePaymentId = $data['payment_intent'] ?? $transactionId;

                    $this->contributionModel->create(
                        $userId,
                        $amount,
                        $stripePaymentId,
                        'completed',
                        'Carte bancaire',
                        date('Y-m-d H:i:s'),
                        'Adhésion'
                    );
                    
                    // Send Email (handled in finalizeRegistration usually, but receipt here?)
                    // finalizeRegistration sends credentials. Receipt can be sent here too.
                }
            } else {
                // Standard Contribution Update
                $this->contributionModel->updateByTransactionId($transactionId, [
                    'status' => 'completed',
                    'payment_method' => 'Carte bancaire'
                ]);
            }
            
            // Send Receipt Email (Common for both)
            try {
                $customerEmail = $data['customer_details']['email'] ?? null;
                $customerName = $data['customer_details']['name'] ?? 'Membre';
                $amount = $data['amount_total'] / 100;
                
                if ($customerEmail) {
                    $this->emailService->sendPaymentReceipt(
                        $customerEmail,
                        $customerName,
                        $amount,
                        $transactionId,
                        date('d/m/Y H:i')
                    );
                }
            } catch (\Exception $e) {
                error_log("Error sending receipt email: " . $e->getMessage());
            }
        }

        return $result;
    }
}
