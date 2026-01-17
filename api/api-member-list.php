<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../src/Models/Member.php';

header('Content-Type: application/json');

// Get filter parameters
$search = $_GET['search'] ?? '';
$metier = $_GET['metier'] ?? '';
$date = $_GET['date'] ?? ''; // Format: YYYY-MM

$memberModel = new \App\Models\Member();
$allMembers = $memberModel->getAllMembers();

// Filter members based on criteria
$filteredMembers = array_filter($allMembers, function($member) use ($search, $metier, $date) {
    // Search filter
    if ($search !== '') {
        $matchesSearch = (
            stripos($member['display_name'] ?? '', $search) !== false ||
            stripos($member['user_email'] ?? '', $search) !== false ||
            stripos($member['telephone'] ?? '', $search) !== false ||
            stripos($member['ville'] ?? '', $search) !== false ||
            stripos($member['first_name'] ?? '', $search) !== false ||
            stripos($member['last_name'] ?? '', $search) !== false
        );
        if (!$matchesSearch) {
            return false;
        }
    }
    
    // Métier/Activity filter
    if ($metier !== '' && strtolower($member['activite'] ?? '') !== strtolower($metier)) {
        return false;
    }
    
    // Date filter (registration date)
    if ($date !== '') {
        $memberDate = date('Y-m', strtotime($member['user_registered'] ?? ''));
        if ($memberDate !== $date) {
            return false;
        }
    }
    
    return true;
});

// Re-index array
$filteredMembers = array_values($filteredMembers);

// Return JSON response
echo json_encode([
    'success' => true,
    'data' => $filteredMembers,
    'total' => count($filteredMembers),
    'filters' => [
        'search' => $search,
        'metier' => $metier,
        'date' => $date
    ]
]);
?>
