<?php
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/Controllers/PaymentController.php';

header('Content-Type: application/json');

// Get filter parameters
$search = $_GET['search'] ?? '';
$month = $_GET['month'] ?? '';
$status = $_GET['status'] ?? '';

$paymentController = new \App\Controllers\PaymentController();
$allPayments = $paymentController->index();

// Filter payments based on criteria
$filteredPayments = array_filter($allPayments, function($payment) use ($search, $month, $status) {
    // Search filter (check member name and transaction ID)
    if ($search !== '') {
        $matchesSearch = (
            stripos($payment['member_name'] ?? '', $search) !== false ||
            stripos($payment['transaction_id'] ?? '', $search) !== false ||
            stripos($payment['amount'] ?? '', $search) !== false
        );
        if (!$matchesSearch) {
            return false;
        }
    }
    
    // Month filter
    if ($month !== '') {
        $paymentMonth = date('m', strtotime($payment['created_at']));
        if ($paymentMonth !== $month) {
            return false;
        }
    }
    
    // Status filter
    if ($status !== '') {
        $paymentStatus = strtolower($payment['status'] ?? '');
        // Normalize status values
        if ($status === 'paye' && !in_array($paymentStatus, ['paye', 'completed'])) {
            return false;
        } elseif ($status === 'a-payer' && $paymentStatus !== 'a-payer') {
            return false;
        } elseif ($status === 'en-attente' && !in_array($paymentStatus, ['en-attente', 'pending'])) {
            return false;
        } elseif ($status === 'annule' && !in_array($paymentStatus, ['annule', 'failed'])) {
            return false;
        }
    }
    
    return true;
});

// Re-index array
$filteredPayments = array_values($filteredPayments);

// Return JSON response
echo json_encode([
    'success' => true,
    'data' => $filteredPayments,
    'total' => count($filteredPayments),
    'filters' => [
        'search' => $search,
        'month' => $month,
        'status' => $status
    ]
]);
?>
