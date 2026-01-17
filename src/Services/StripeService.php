<?php
namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeService {
    private $config;
    private $secretKey;

    public function __construct() {
        $this->config = require __DIR__ . '/../../config/stripe.php';
        $env = $this->config['environment'];
        $this->secretKey = $this->config[$env]['secret_key'];
        
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Initiate a Stripe Checkout Session
     * 
     * @param array $data Payment data
     * @return array Response with session ID and URL
     */
    public function initiatePayment($data) {
        try {
            $checkout_session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $this->config['currency'],
                        'product_data' => [
                            'name' => $data['description'] ?? 'Contribution',
                        ],
                        'unit_amount' => $data['amount'], // Stripe expects integer (e.g., cents), but for XAF it's usually 1:1 since it's a zero-decimal currency? 
                        // WAIT: XAF is a zero-decimal currency in Stripe? 
                        // Checking Stripe docs: XAF is a zero-decimal currency. So 1000 XAF = 1000.
                        // However, usually Stripe expects smallest currency unit. 
                        // For USD, 100 = $1.00.
                        // For XAF, 100 = 100 XAF.
                        // Let's assume the input amount is already in XAF integer.
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $data['success_url'] ?? $this->config['success_url'],
                'cancel_url' => $this->config['cancel_url'],
                'client_reference_id' => $data['transaction_id'],
                'customer_email' => $data['customer_email'] ?? null,
                'metadata' => [
                    'transaction_id' => $data['transaction_id'],
                    'contribution_id' => $data['contribution_id'] ?? null,
                    'registration_id' => $data['registration_id'] ?? null // Add registration ID
                ]
            ]);

            return [
                'success' => true,
                'payment_url' => $checkout_session->url,
                'session_id' => $checkout_session->id
            ];

        } catch (\Exception $e) {
            // Log the detailed error for debugging
            error_log("Stripe Payment Init Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => "Une erreur est survenue lors de la connexion au service de paiement. Veuillez vérifier votre connexion internet ou réessayer plus tard."
            ];
        }
    }

    /**
     * Verify a Stripe Checkout Session
     * 
     * @param string $sessionId
     * @return array Payment status
     */
    public function checkPaymentStatus($sessionId) {
        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                return [
                    'success' => true,
                    'status' => 'ACCEPTED',
                    'amount' => $session->amount_total,
                    'currency' => $session->currency,
                    'transaction_id' => $session->client_reference_id,
                    'metadata' => $session->metadata
                ];
            } else {
                return [
                    'success' => false,
                    'status' => $session->payment_status,
                    'message' => 'Payment not completed'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle Stripe Webhook
     * 
     * @param string $payload Raw request body
     * @param string $sig_header Stripe-Signature header
     * @return array Result
     */
    public function handleWebhook($payload, $sig_header) {
        $env = $this->config['environment'];
        $endpoint_secret = $this->config[$env]['webhook_secret'];

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return ['success' => false, 'status' => 400, 'message' => 'Invalid payload'];
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return ['success' => false, 'status' => 400, 'message' => 'Invalid signature'];
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                
                // Fulfill the purchase...
                return [
                    'success' => true,
                    'type' => 'checkout.session.completed',
                    'data' => [
                        'transaction_id' => $session->client_reference_id,
                        'amount' => $session->amount_total,
                        'currency' => $session->currency,
                        'status' => 'completed',
                        'metadata' => $session->metadata, // Return metadata
                        'payment_intent' => $session->payment_intent,
                        'customer_details' => $session->customer_details
                    ]
                ];
            default:
                // Unexpected event type
                return ['success' => true, 'status' => 200, 'message' => 'Event ignored'];
        }
    }
}
