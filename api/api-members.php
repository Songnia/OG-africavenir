<?php
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/Controllers/MemberController.php';

header('Content-Type: application/json');

// Get filter parameters
$search = $_GET['search'] ?? '';
$activity = $_GET['activity'] ?? '';
$category = $_GET['category'] ?? '';

$memberController = new \App\Controllers\MemberController();
$allMembers = $memberController->index();

// Filter members based on criteria
$filteredMembers = array_filter($allMembers, function($member) use ($search, $activity, $category) {
    // Search filter (check multiple fields)
    if ($search !== '') {
        $searchLower = strtolower($search);
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
    
    // Activity filter
    if ($activity !== '' && strtolower($member['activite'] ?? '') !== strtolower($activity)) {
        return false;
    }
    
    // Category filter
    if ($category !== '' && strtolower($member['categorie'] ?? '') !== strtolower($category)) {
        return false;
    }
    
    return true;
});

// Re-index array to remove gaps
$filteredMembers = array_values($filteredMembers);

// Return JSON response
echo json_encode([
    'success' => true,
    'data' => $filteredMembers,
    'total' => count($filteredMembers),
    'filters' => [
        'search' => $search,
        'activity' => $activity,
        'category' => $category
    ]
]);
?>
