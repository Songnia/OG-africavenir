<?php
// Mock Session
session_start();
$_SESSION['user_id'] = 1; // Assume ID 1 is super admin
$_SESSION['user_level'] = 'superadmin';

require_once __DIR__ . '/../src/Controllers/MemberController.php';
require_once __DIR__ . '/../src/Helpers/RoleHelper.php';
require_once __DIR__ . '/../config/database.php';

use App\Controllers\MemberController;
use App\Helpers\RoleHelper;

echo "=== Testing Admin CRUD Logic ===\n";

$controller = new MemberController();

// 0. Setup: Create a temporary Super Admin to act as the current user
echo "\n[SETUP] Creating temporary Super Admin context...\n";
$adminData = [
    'nom' => 'Super Admin Test',
    'email' => 'superadmin_test_' . time() . '@example.com',
    'password' => 'password123',
    'username' => 'superadmin_test_' . time(),
    'display_name' => 'Super Admin Test'
];
// We use the model directly to avoid the controller's logic which we are testing
$memberModel = new \App\Models\Member();
$adminId = $memberModel->createMember($adminData);
RoleHelper::setUserLevel($adminId, RoleHelper::LEVEL_SUPERADMIN);

$_SESSION['user_id'] = $adminId;
$_SESSION['user_level'] = RoleHelper::LEVEL_SUPERADMIN;

echo "  Temporary Admin ID: $adminId\n";

$testEmail = 'test_admin_' . time() . '@example.com';

// 1. Test Create Admin User
echo "\n[TEST] Creating new Admin MTM user...\n";
$data = [
    'nom' => 'Test Admin',
    'first_name' => 'Test',
    'last_name' => 'Admin',
    'email' => $testEmail,
    'role' => RoleHelper::LEVEL_ADMIN_MTM,
    'categorie' => 'member-ships' // Should be ignored/irrelevant for admin
];

// Simulate Admin Registration context
$result = $controller->create($data);

if ($result['success']) {
    echo "✓ User created successfully. ID: " . $result['id'] . "\n";
    $userId = $result['id'];
    
    // Verify Role
    $role = RoleHelper::getUserLevel($userId);
    echo "  Role: " . $role . "\n";
    if ($role === RoleHelper::LEVEL_ADMIN_MTM) {
        echo "✓ Role is correct (admin-mtm)\n";
    } else {
        echo "✗ Role is INCORRECT (expected admin-mtm, got $role)\n";
    }
} else {
    echo "✗ Failed to create user: " . $result['message'] . "\n";
    exit;
}

// 2. Test Update User
echo "\n[TEST] Updating user to Member...\n";
$updateData = [
    'nom' => 'Test Member',
    'prenom' => 'Updated',
    'email' => $testEmail,
    'categorie' => 'alumni'
];

// Update basic info
$updateResult = $controller->update($userId, $updateData);
if ($updateResult['success']) {
    echo "✓ Basic info updated\n";
    
    // Manually update role as the handler does
    RoleHelper::setUserLevel($userId, RoleHelper::LEVEL_MEMBER);
    RoleHelper::setMemberCategory($userId, 'alumni');
    
    $newRole = RoleHelper::getUserLevel($userId);
    $newCat = RoleHelper::getMemberCategory($userId);
    
    echo "  New Role: $newRole\n";
    echo "  New Category: $newCat\n";
    
    if ($newRole === RoleHelper::LEVEL_MEMBER && $newCat === 'alumni') {
        echo "✓ Role and Category updated correctly\n";
    } else {
        echo "✗ Role/Category update failed\n";
    }
} else {
    echo "✗ Update failed: " . $updateResult['message'] . "\n";
}

// 3. Test Delete User
echo "\n[TEST] Deleting user...\n";
$deleteResult = $controller->delete($userId);
if ($deleteResult['success']) {
    echo "✓ User deleted\n";
    
    // Verify
    $check = $controller->getMember($userId);
    if (!$check['success']) {
        echo "✓ User verification confirmed (not found)\n";
    } else {
        echo "✗ User still exists!\n";
    }
} else {
    echo "✗ Delete failed: " . $deleteResult['message'] . "\n";
}

echo "\n=== Test Complete ===\n";
