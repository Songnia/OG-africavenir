<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../src/Models/Contribution.php';

header('Content-Type: application/json');

// Get filter parameters
$search = $_GET['search'] ?? '';
$date = $_GET['date'] ?? ''; // Format: YYYY-MM
$type = $_GET['type'] ?? 'contributions'; // contributions or dons

$contributionModel = new \App\Models\Contribution();
$allContributions = $contributionModel->getByUserId($_SESSION['user_id']);

// Filter contributions based on criteria
$filteredContributions = array_filter($allContributions, function($contrib) use ($search, $date, $type) {
    // Type filter (contributions vs dons)
    // Note: assuming we have a 'type' field in contributions table
    // If not, we might need to filter based on description or another field
    // For now, we'll show all as contributions (adjust logic as needed)
    
    // Search filter
    if ($search !== '') {
        $matchesSearch = (
            stripos($contrib['transaction_id'] ?? '', $search) !== false ||
            stripos($contrib['amount'] ?? '', $search) !== false ||
            stripos($contrib['id'] ?? '', $search) !== false
        );
        if (!$matchesSearch) {
            return false;
        }
    }
    
    // Date filter (by month)
    if ($date !== '') {
        $contribDate = date('Y-m', strtotime($contrib['created_at'] ?? ''));
        if ($contribDate !== $date) {
            return false;
        }
    }
    
    return true;
});

// Re-index array
$filteredContributions = array_values($filteredContributions);

// Calculate totals
$totalAmount = 0;
$completedAmount = 0;
$pendingAmount = 0;

foreach ($filteredContributions as $contrib) {
    $totalAmount += $contrib['amount'];
    if ($contrib['status'] === 'completed') {
        $completedAmount += $contrib['amount'];
    } elseif ($contrib['status'] === 'pending') {
        $pendingAmount += $contrib['amount'];
    }
}

// Return JSON response
echo json_encode([
    'success' => true,
    'data' => $filteredContributions,
    'total' => count($filteredContributions),
    'totals' => [
        'total_amount' => $totalAmount,
        'completed_amount' => $completedAmount,
        'pending_amount' => $pendingAmount
    ],
    'filters' => [
        'search' => $search,
        'date' => $date,
        'type' => $type
    ]
]);
?>
