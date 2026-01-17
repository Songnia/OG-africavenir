<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Contribution.php';
require_once __DIR__ . '/../Models/Member.php';
require_once __DIR__ . '/AuthController.php';

use App\Models\Contribution;
use App\Models\Member;

class PaymentController {
    private $contributionModel;
    private $memberModel;
    private $auth;
    private $config;

    public function __construct() {
        $this->contributionModel = new Contribution();
        $this->memberModel = new Member();
        $this->auth = new AuthController();
        $this->config = require __DIR__ . '/../../config/cinetpay.php';
    }

    public function index() {
        // $this->auth->requireLogin(); // Optional: restrict to logged in users
        return $this->contributionModel->getAll();
    }

    public function store($data) {
        // $this->auth->requireLogin();
        
        // Validate Required Fields
        if (empty($data['montant']) || empty($data['member'])) {
            return ['success' => false, 'message' => 'Montant et Membre sont requis.'];
        }

        // Validate Amount
        if (!is_numeric($data['montant']) || $data['montant'] <= 0) {
            return ['success' => false, 'message' => 'Le montant doit être un nombre positif.'];
        }

        $input_member = $data['member'];
        $user_id = null;

        // Validate Member Existence
        if (is_numeric($input_member)) {
            // Check by ID
            $member = $this->memberModel->getProfile($input_member);
            if ($member) {
                $user_id = $member['ID'];
            }
        } else {
            // Check by Name
            $results = $this->memberModel->searchMembers($input_member);
            if (!empty($results)) {
                // Use the first match
                $user_id = $results[0]['ID'];
            }
        }

        if (!$user_id) {
            return ['success' => false, 'message' => "Ce membre n'existe pas. Veuillez procéder à la création du membre dans le formulaire d'inscription."];
        }

        $montant = $data['montant'];
        $motif = $data['motif'] ?? 'Contribution';
        $status = $data['status'] ?? 'pending';
        $mode = $data['mode'] ?? 'Cash';
        $date = $data['date'] ?? null; 

        // Validate Date (if provided)
        if ($date && !strtotime($date)) {
             return ['success' => false, 'message' => 'La date fournie est invalide.'];
        }

        if ($this->contributionModel->create($user_id, $montant, $motif, $status, $mode, $date)) {
             // Send Receipt Email if status is completed/paid
             if ($status === 'completed' || $status === 'paid') {
                 try {
                     require_once __DIR__ . '/../Services/EmailService.php';
                     $emailService = new \App\Services\EmailService();
                     
                     // Get Member Email
                     $member = $this->memberModel->getProfile($user_id);
                     if ($member && !empty($member['user_email'])) {
                         $emailService->sendPaymentReceipt(
                             $member['user_email'],
                             $member['display_name'] ?? 'Membre',
                             $montant,
                             'MANUAL-' . time(), // Generate a ref if not available
                             $date ?? date('d/m/Y'),
                             $mode
                         );
                     }
                 } catch (\Exception $e) {
                     error_log("Error sending manual payment receipt: " . $e->getMessage());
                 }
             }
             
             return ['success' => true, 'message' => 'Paiement enregistré.'];
        }
        return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement.'];
    }

    public function update($id, $data) {
        // Map form fields to model expected keys
        if (isset($data['montant'])) {
            $data['amount'] = $data['montant'];
        }
        // 'mode' is mapped inside model update method (Wait, I should probably map it here for consistency)
        // Model update uses $data['mode'] for payment_method.
        // Let's check model update again.
        // Model update: $stmt->bindParam(":payment_method", $data['mode']);
        // So model expects 'mode'.
        // Model update: $stmt->bindParam(":amount", $data['amount']);
        // So model expects 'amount'.
        
        if ($this->contributionModel->update($id, $data)) {
            return ['success' => true, 'message' => 'Paiement mis à jour.'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour.'];
    }

    public function delete($id) {
        if ($this->contributionModel->delete($id)) {
            return ['success' => true, 'message' => 'Paiement supprimé.'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la suppression.'];
    }

    public function get($id) {
        $payment = $this->contributionModel->getById($id);
        if ($payment) {
            return ['success' => true, 'data' => $payment];
        }
        return ['success' => false, 'message' => 'Paiement non trouvé.'];
    }

    public function initiatePayment($contributionId) {
        $this->auth->requireLogin();
        
        // Récupérer la contribution
        $contribution = $this->contributionModel->getById($contributionId);
        if (!$contribution) {
            return ['success' => false, 'message' => 'Contribution non trouvée'];
        }
        
        // Récupérer les infos du membre
        $member = $this->memberModel->getProfile($contribution['user_id']);
        
        // Générer un transaction_id unique
        $transaction_id = 'AFRC_' . time() . '_' . $contributionId;
        
        // Mettre à jour le transaction_id dans la contribution
        $this->contributionModel->update($contributionId, [
            'transaction_id' => $transaction_id,
            'status' => 'pending'
        ]);

        // Check Payment Method
        $paymentMethod = $contribution['payment_method'];

        if ($paymentMethod === 'Carte bancaire') {
            // Use Stripe
            require_once __DIR__ . '/../Services/StripeService.php';
            $stripe = new \App\Services\StripeService();
            
            $paymentData = [
                'transaction_id' => $transaction_id,
                'amount' => $contribution['amount'],
                'description' => $contribution['transaction_id'] ?? 'Contribution',
                'customer_email' => $member['user_email'],
                'contribution_id' => $contributionId
            ];

            return $stripe->initiatePayment($paymentData);

        } else {
            // Use CinetPay (Default for Mobile Money)
            
            // Préparer les données pour CinetPay
            $paymentData = [
                'transaction_id' => $transaction_id,
                'amount' => $contribution['amount'],
                'description' => $contribution['transaction_id'] ?? 'Contribution',
                'customer_name' => $member['prenom'] ?? $member['display_name'],
                'customer_surname' => $member['nom'] ?? '',
                'customer_email' => $member['user_email'],
                'customer_phone' => $member['telephone'] ?? '000000000',
                'customer_city' => $member['ville'] ?? 'Douala',
            ];
            
            // Initier le paiement avec CinetPay
            require_once __DIR__ . '/../Services/CinetPayService.php';
            $cinetpay = new \App\Services\CinetPayService();
            $response = $cinetpay->initiatePayment($paymentData);
            
            if (isset($response['code']) && $response['code'] === '201') {
                // Succès - rediriger vers la page de paiement
                return [
                    'success' => true,
                    'payment_url' => $response['data']['payment_url'],
                    'transaction_id' => $transaction_id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Erreur lors de l\'initiation du paiement'
                ];
            }
        }
    }

    public function handleNotification() {
        // Handle CinetPay IPN
    }
}
