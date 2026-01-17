<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Services/CinetPayService.php';
require_once __DIR__ . '/src/Models/Contribution.php';

use App\Services\CinetPayService;
use App\Models\Contribution;

// Récupérer les données POST de CinetPay
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Initialiser le service
$cinetpay = new CinetPayService();

// Logger la notification (important pour debug)
$cinetpay->log('NOTIFICATION_RECEIVED', [
    'headers' => getallheaders(),
    'raw' => $rawData,
    'parsed' => $data,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);

// Vérifier que nous avons des données
if (!$data) {
    $cinetpay->log('ERROR', 'No data received');
    http_response_code(400);
    exit('No data');
}

// Vérifier la signature HMAC
// Note: getallheaders() might not work on all servers (e.g. nginx-fpm without config), 
// fallback to $_SERVER needed if issues arise.
$headers = getallheaders();
if (!$cinetpay->verifyHmacToken($headers, $_POST)) { // CinetPay sends data as POST fields for HMAC check usually, but here we decoded JSON input. 
    // Wait, CinetPay documentation says: "Les données sont envoyées en POST". 
    // If they send JSON body, $_POST might be empty.
    // However, the provided example used $postData['cpm_site_id'].
    // Let's assume $data (decoded JSON) is what we need to verify if it's a JSON payload.
    // BUT standard CinetPay notification is often x-www-form-urlencoded.
    // Let's check if $data is populated. If $rawData was JSON, $data is set.
    // If it was form data, $_POST is set.
    
    $payloadToVerify = $data;
    if (empty($payloadToVerify) && !empty($_POST)) {
        $payloadToVerify = $_POST;
    }

    if (!$cinetpay->verifyHmacToken($headers, $payloadToVerify)) {
        $cinetpay->log('ERROR', 'Invalid HMAC signature');
        http_response_code(403);
        exit('Invalid signature');
    }
}

$transaction_id = $data['cpm_custom'] ?? $_POST['cpm_custom'] ?? '';
$cpm_trans_id = $data['cpm_trans_id'] ?? $_POST['cpm_trans_id'] ?? '';
$amount = $data['cpm_amount'] ?? $_POST['cpm_amount'] ?? 0;
$payment_method = $data['payment_method'] ?? $_POST['payment_method'] ?? '';

if (empty($transaction_id)) {
    $cinetpay->log('ERROR', 'Missing transaction_id');
    http_response_code(400);
    exit('Missing transaction_id');
}

// Vérifier l'idempotence (si déjà traité)
$contributionModel = new Contribution();
// We need a method to get by transaction_id to check status
// Assuming updateByTransactionId works, we can also check before updating.
// But let's rely on the status check from CinetPay first.

// Vérifier le statut du paiement auprès de CinetPay
$status = $cinetpay->checkPaymentStatus($transaction_id);

$cinetpay->log('STATUS_CHECK', [
    'transaction_id' => $transaction_id,
    'status_response' => $status
]);

if (isset($status['code']) && $status['code'] === '00' && isset($status['data']['status']) && $status['data']['status'] === 'ACCEPTED') {
    // Paiement réussi
    
    // Check if this is a new member registration
    $metadata = $status['data']['metadata'] ?? '';
    if (strpos($metadata, 'MEMBER_REGISTRATION_TEMP_') === 0) {
        $registrationId = str_replace('MEMBER_REGISTRATION_TEMP_', '', $metadata);
        
        require_once __DIR__ . '/src/Controllers/MemberController.php';
        $memberController = new \App\Controllers\MemberController();
        
        // Finalize registration (create user)
        $userId = $memberController->finalizeRegistration($registrationId);
        
        if ($userId) {
            $cinetpay->log('REGISTRATION_FINALIZED', "User created with ID: $userId");
            
            // Create the contribution record now that user exists
            $contributionModel->create(
                $userId,
                $amount,
                $transaction_id,
                'completed',
                $payment_method ?: 'CinetPay',
                date('Y-m-d H:i:s'),
                'Adhésion'
            );
        } else {
            $cinetpay->log('ERROR', "Failed to finalize registration for ID: $registrationId");
        }
    } else {
        // Standard contribution update
        $result = $contributionModel->updateByTransactionId($transaction_id, [
            'status' => 'completed',
            'payment_method' => $payment_method,
            'cinetpay_trans_id' => $cpm_trans_id
        ]);
        
        if ($result) {
            $cinetpay->log('SUCCESS', [
                'transaction_id' => $transaction_id,
                'cpm_trans_id' => $cpm_trans_id,
                'amount' => $amount
            ]);
            
            // Envoyer email de confirmation
            // Envoyer email de confirmation
            try {
                require_once __DIR__ . '/src/Services/EmailService.php';
                $emailService = new \App\Services\EmailService();
                
                // Try to get email from data
                // CinetPay might return customer info in 'data'
                // Let's try to get it from the contribution/member
                // We need to fetch the contribution to get the user_id
                $contribution = $contributionModel->getByTransactionId($transaction_id);
                
                if ($contribution) {
                    // Get Member
                    require_once __DIR__ . '/src/Models/Member.php';
                    $memberModel = new \App\Models\Member();
                    $member = $memberModel->getProfile($contribution['user_id']);
                    
                    if ($member && !empty($member['user_email'])) {
                        $emailService->sendPaymentReceipt(
                            $member['user_email'],
                            $member['display_name'] ?? 'Membre',
                            $amount,
                            $transaction_id,
                            date('d/m/Y H:i'),
                            'Mobile Money (' . $payment_method . ')'
                        );
                    }
                }
            } catch (Exception $e) {
                $cinetpay->log('EMAIL_ERROR', $e->getMessage());
            }
            
            http_response_code(200);
            echo 'OK';
        } else {
            $cinetpay->log('ERROR', 'Failed to update contribution');
            http_response_code(500);
            echo 'UPDATE_FAILED';
        }
    }
} else {
    // Paiement échoué ou en attente
    $contributionModel->updateByTransactionId($transaction_id, [
        'status' => 'failed'
    ]);
    
    $cinetpay->log('FAILED', [
        'transaction_id' => $transaction_id,
        'status' => $status
    ]);
    
    http_response_code(200);
    echo 'FAILED';
}
