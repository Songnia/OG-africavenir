<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Member.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Helpers/CredentialGenerator.php';
require_once __DIR__ . '/../Helpers/RoleHelper.php';
require_once __DIR__ . '/../Services/EmailService.php';

use App\Models\Member;
use App\Helpers\CredentialGenerator;
use App\Helpers\RoleHelper;
use App\Services\EmailService;
use App\Services\CinetPayService;
use App\Models\Contribution;

require_once __DIR__ . '/../Services/CinetPayService.php';
require_once __DIR__ . '/../Models/Contribution.php';

class MemberController {
    private $memberModel;
    private $auth;

    public function __construct() {
        $this->memberModel = new Member();
        $this->auth = new AuthController();
        // Removed global requireLogin to allow public registration via create()
    }

    public function index() {
        $this->auth->requireLogin();
        // Fetch all members (users)
        // In WP, users are in wp_users, meta in wp_usermeta
        // We'll need a custom query in Member model to get a list with meta
        return $this->memberModel->getAllMembers();
    }
    public function create($data) {
        // Basic validation
        if (empty($data['nom']) || empty($data['email'])) {
            return ['success' => false, 'message' => 'Nom et Email sont requis.'];
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Format d\'email invalide.'];
        }

        // Check if email already exists in wp_users
        $existingMember = $this->memberModel->findByEmail($data['email']);
        if ($existingMember) {
            return ['success' => false, 'message' => 'Un membre avec cet email existe déjà.'];
        }

        // Extract first and last name
        if (isset($data['first_name']) && isset($data['last_name'])) {
            $firstName = $data['first_name'];
            $lastName = $data['last_name'];
        } else {
            $nameParts = explode(' ', trim($data['nom']), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
        }

        // Generate credentials
        $credentials = CredentialGenerator::generateCredentials(
            $data['email'],
            $firstName,
            $lastName
        );

        // Prepare data for member creation
        $memberData = [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'email' => $data['email'],
            'display_name' => $data['nom'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'telephone' => $data['telephone'] ?? '',
            'adresse' => $data['adresse'] ?? '',
            'ville' => $data['ville'] ?? '',
            'pays' => $data['pays'] ?? '',
            'civilite' => $data['civilite'] ?? '',
            'date_naissance' => $data['date_naissance'] ?? '',
            'lieu_naissance' => $data['lieu_naissance'] ?? '',
            'profession' => $data['profession'] ?? '',
            'secteur_activite' => $data['secteur_activite'] ?? '',
            'type_activite' => $data['type_activite'] ?? $data['activite'] ?? '',
            'activite' => $data['type_activite'] ?? $data['activite'] ?? '',
            'statut_professionnel' => $data['statut_professionnel'] ?? '',
            'parcours_type' => $data['parcours_type'] ?? '',
            'etablissement_scolaire' => $data['etablissement_scolaire'] ?? '',
            'dernier_diplome' => $data['dernier_diplome'] ?? '',
            'centre_interet' => $data['centre_interet'] ?? '',
            'category' => $data['categorie'] ?? 'member-ships',
            'montant_contribution' => $data['montant_contribution'] ?? '',
            'montant_libre_val' => $data['montant_libre_val'] ?? '',
            'mode_paiement_contribution' => $data['mode_paiement_contribution'] ?? ''
        ];

        // Check if Admin Registration (via Dashboard)
        $isAdminRegistration = isset($data['admin_registration']) && $data['admin_registration'] == '1';

        if ($isAdminRegistration) {
            // ADMIN REGISTRATION: Create directly in wp_users
            $userId = $this->memberModel->createMember($memberData);

            if (!$userId) {
                return ['success' => false, 'message' => 'Erreur lors de la création du membre.'];
            }

            // Set roles
            $levelToSet = isset($data['role']) ? $data['role'] : RoleHelper::LEVEL_MEMBER;
            RoleHelper::setUserLevel($userId, $levelToSet);
            if ($levelToSet === RoleHelper::LEVEL_MEMBER) {
                RoleHelper::setMemberCategory($userId, $memberData['category']);
            }

            // Send email
            try {
                $emailService = new EmailService();
                $emailSent = $emailService->sendCredentials(
                    $data['email'],
                    $credentials['username'],
                    $credentials['password'],
                    $data['nom']
                );
                return [
                    'success' => true, 
                    'message' => $emailSent ? 'Membre créé avec succès.' : 'Membre créé, échec envoi email.',
                    'id' => $userId,
                    'credentials' => $credentials
                ];
            } catch (\Exception $e) {
                return ['success' => true, 'message' => 'Membre créé, erreur email.', 'id' => $userId, 'credentials' => $credentials];
            }
        } else {
            // SELF-REGISTRATION: Create TEMPORARY registration
            $registrationId = $this->createRegistration($memberData, $credentials);

            if (!$registrationId) {
                return ['success' => false, 'message' => 'Erreur lors de l\'initialisation de l\'inscription.'];
            }

            $response = [
                'success' => true,
                'message' => 'Inscription initiée. Veuillez procéder au paiement.',
                'registrationID' => $registrationId,
                'redirect' => 'registration-success.php'
            ];

            // Handle Payment
            $montant = 0;
            if (isset($memberData['montant_contribution']) && $memberData['montant_contribution'] === 'libre') {
                $montant = (int)($memberData['montant_libre_val'] ?? 0);
            } elseif (isset($memberData['montant_contribution'])) {
                $montant = (int)$memberData['montant_contribution'];
            }

            if ($montant > 0) {
                $paymentMode = strtolower($memberData['mode_paiement_contribution'] ?? '');
                $response['payment_mode'] = $paymentMode;

                if ($paymentMode === 'paypal') {
                    $response['message'] .= ' En attente du paiement PayPal...';
                } elseif ($paymentMode === 'carte' || $paymentMode === 'card') {
                    // Stripe
                    $response['message'] .= ' Initialisation Stripe...';
                } else {
                    // CinetPay
                    try {
                        $transactionId = 'AFR-' . date('YmdHis') . '-' . uniqid();
                        
                        // Update registration with transaction ID
                        $this->updateRegistrationTransaction($registrationId, $transactionId);

                        $description = "Contribution Membre " . $memberData['display_name'];
                        
                        require_once __DIR__ . '/../Helpers/UrlHelper.php';
                        
                        $cinetPay = new CinetPayService();
                        $paymentData = [
                            'transaction_id' => $transactionId,
                            'amount' => $montant,
                            'description' => $description,
                            'customer_name' => $memberData['first_name'],
                            'customer_surname' => $memberData['last_name'],
                            'customer_email' => $memberData['email'],
                            'customer_phone' => $memberData['telephone'],
                            'customer_address' => $memberData['adresse'],
                            'customer_city' => $memberData['ville'],
                            'metadata' => 'MEMBER_REGISTRATION_TEMP_' . $registrationId,
                            'return_url' => \UrlHelper::buildUrl('registration-success.php?transaction_id=' . $transactionId)
                        ];

                        $paymentResponse = $cinetPay->initiatePayment($paymentData);

                        if (isset($paymentResponse['code']) && $paymentResponse['code'] == '201') {
                            $response['payment_url'] = $paymentResponse['data']['payment_url'];
                            $response['message'] .= ' Redirection vers le paiement...';
                        } else {
                            error_log("CinetPay Init Error: " . json_encode($paymentResponse));
                            // Fail the registration response if payment init fails
                            $response['success'] = false;
                            $response['message'] = "Erreur initialisation paiement: " . ($paymentResponse['message'] ?? 'Erreur inconnue');
                        }
                    } catch (\Exception $e) {
                        error_log("Payment Init Error: " . $e->getMessage());
                        $response['success'] = false;
                        $response['message'] = "Erreur système paiement.";
                    }
                }
            } else {
                // Free registration? Finalize immediately
                $this->finalizeRegistration($registrationId);
                $response['message'] = 'Inscription réussie!';
                $response['credentials'] = $credentials;
            }

            return $response;
        }
    }

    /**
     * Create a temporary registration entry
     */
    private function createRegistration($data, $credentials) {
        $database = new \Database();
        $conn = $database->getConnection();
        
        // Add credentials to data for later use
        $data['generated_credentials'] = $credentials;
        
        $stmt = $conn->prepare("INSERT INTO app_registrations (email, data, status) VALUES (?, ?, 'pending')");
        if ($stmt->execute([$data['email'], json_encode($data)])) {
            return $conn->lastInsertId();
        }
        return false;
    }

    /**
     * Update transaction ID for a registration
     */
    public function updateRegistrationTransaction($registrationId, $transactionId) {
        $database = new \Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare("UPDATE app_registrations SET transaction_id = ? WHERE id = ?");
        return $stmt->execute([$transactionId, $registrationId]);
    }

    /**
     * Get registration by transaction ID
     */
    public function getRegistrationByTransaction($transactionId) {
        $database = new \Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare("SELECT * FROM app_registrations WHERE transaction_id = ? LIMIT 1");
        $stmt->execute([$transactionId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Finalize registration after successful payment
     */
    public function finalizeRegistration($registrationId) {
        $database = new \Database();
        $conn = $database->getConnection();
        
        // Get registration data
        $stmt = $conn->prepare("SELECT * FROM app_registrations WHERE id = ?");
        $stmt->execute([$registrationId]);
        $registration = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$registration || $registration['status'] === 'completed') {
            return false; // Already completed or not found
        }
        
        $data = json_decode($registration['data'], true);
        $credentials = $data['generated_credentials'];
        
        // Create Member in wp_users
        // Ensure we don't create duplicate if retry
        $existing = $this->memberModel->findByEmail($data['email']);
        if ($existing) {
            $userId = $existing['ID'];
        } else {
            $userId = $this->memberModel->createMember($data);
        }
        
        if ($userId) {
            // Set roles
            RoleHelper::setUserLevel($userId, RoleHelper::LEVEL_MEMBER);
            RoleHelper::setMemberCategory($userId, $data['category']);
            
            // Store temp password
            $stmtMeta = $conn->prepare("INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES (?, ?, ?)");
            $stmtMeta->execute([$userId, 'temp_password', $credentials['password']]);
            
            // Send Email
            try {
                $emailService = new EmailService();
                $emailService->sendCredentials(
                    $data['email'],
                    $credentials['username'],
                    $credentials['password'],
                    $data['display_name']
                );
            } catch (\Exception $e) {
                error_log("Email send error: " . $e->getMessage());
            }
            
            // Mark registration as completed
            $stmtUpdate = $conn->prepare("UPDATE app_registrations SET status = 'completed' WHERE id = ?");
            $stmtUpdate->execute([$registrationId]);
            
            return $userId;
        }
        
        return false;
    }
    public function delete($id) {
        $this->auth->requireLogin();
        if ($this->memberModel->deleteMember($id)) {
            return ['success' => true, 'message' => 'Membre supprimé.'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la suppression.'];
    }

    public function getMember($id) {
        $this->auth->requireLogin();
        $member = $this->memberModel->getMemberDetails($id);
        if ($member) {
            return ['success' => true, 'data' => $member];
        }
        return ['success' => false, 'message' => 'Membre non trouvé.'];
    }

    public function update($id, $data) {
        $this->auth->requireLogin();
        if ($this->memberModel->updateMember($id, $data)) {
            return ['success' => true, 'message' => 'Membre mis à jour.'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour.'];
    }
}
