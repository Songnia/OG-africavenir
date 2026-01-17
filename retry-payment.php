<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Services/CinetPayService.php';
require_once __DIR__ . '/src/Models/Contribution.php';

use App\Services\CinetPayService;
use App\Models\Contribution;

// Get data from POST
$transactionId = $_POST['transaction_id'] ?? null;
$userId = $_POST['user_id'] ?? null;

if (!$transactionId || !$userId) {
    header('Location: index.php');
    exit;
}

// Get contribution details
$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("
    SELECT c.*, u.display_name, u.user_email, u.user_login
    FROM app_contributions c
    JOIN wp_users u ON c.user_id = u.ID
    WHERE c.transaction_id = :transaction_id AND c.user_id = :user_id
");
$stmt->execute([
    ':transaction_id' => $transactionId,
    ':user_id' => $userId
]);
$contribution = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contribution) {
    header('Location: index.php');
    exit;
}

// Re-initiate payment
$cinetpay = new CinetPayService();

$paymentData = [
    'transaction_id' => $transactionId,
    'amount' => $contribution['amount'],
    'description' => 'Contribution Membre ' . $contribution['display_name'] . ' (Retry)',
    'customer_name' => $contribution['display_name'],
    'customer_surname' => '',
    'customer_email' => $contribution['user_email'],
    'customer_phone' => '', // We don't have this stored, might need to add
    'customer_address' => 'Cameroun',
    'customer_city' => 'Douala',
    'metadata' => 'MEMBER_REGISTRATION_RETRY'
];

$paymentResponse = $cinetpay->initiatePayment($paymentData);

if (isset($paymentResponse['code']) && $paymentResponse['code'] == '201') {
    // Redirect to payment page
    header('Location: ' . $paymentResponse['data']['payment_url']);
    exit;
} else {
    // Error re-initiating payment
    $_SESSION['payment_error'] = 'Erreur lors de la réinitialisation du paiement. Veuillez contacter le support.';
    header('Location: register-response.php?transaction_id=' . urlencode($transactionId));
    exit;
}
?>
