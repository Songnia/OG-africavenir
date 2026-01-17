<?php
require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../src/Helpers/RoleHelper.php';
require_once __DIR__ . '/../src/Controllers/MemberController.php';

use App\Helpers\RoleHelper;

// Strict Super Admin Check
if (!RoleHelper::isSuperAdmin($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Get filter parameters
$search = $_GET['search'] ?? '';

$memberController = new \App\Controllers\MemberController();
$allUsers = $memberController->index();

// Add role information to each user
foreach ($allUsers as &$user) {
    $role = RoleHelper::getUserLevel($user['ID']);
    $user['role'] = $role;
    $user['role_label'] = $role;
    
    if ($role === RoleHelper::LEVEL_SUPERADMIN) {
        $user['role_label'] = 'Super Admin';
    } elseif ($role === RoleHelper::LEVEL_ADMIN_MTM) {
        $user['role_label'] = 'Admin MTM';
    } elseif ($role === RoleHelper::LEVEL_MEMBER) {
        $user['role_label'] = 'Membre';
    }
}
unset($user); // Break reference

// Filter users based on search
$filteredUsers = array_filter($allUsers, function($user) use ($search) {
    if ($search !== '') {
        $matchesSearch = (
            stripos($user['user_email'] ?? '', $search) !== false ||
            stripos($user['user_login'] ?? '', $search) !== false ||
            stripos($user['display_name'] ?? '', $search) !== false ||
            stripos($user['ID'] ?? '', $search) !== false
        );
        if (!$matchesSearch) {
            return false;
        }
    }
    
    return true;
});

// Re-index array
$filteredUsers = array_values($filteredUsers);

// Return JSON response
echo json_encode([
    'success' => true,
    'data' => $filteredUsers,
    'total' => count($filteredUsers),
    'filters' => [
        'search' => $search
    ]
]);
?>
