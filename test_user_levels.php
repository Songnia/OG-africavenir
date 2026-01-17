<?php
/**
 * Test User Levels
 * Tests the RoleHelper functionality
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Helpers/RoleHelper.php';

use App\Helpers\RoleHelper;

echo "=== Testing RoleHelper ===\n\n";

// Test 1: Set levels for test users
echo "Test 1: Setting User Levels\n";
echo "----------------------------\n";

$testUsers = [
    ['id' => 999, 'level' => RoleHelper::LEVEL_SUPERADMIN, 'category' => null],
    ['id' => 998, 'level' => RoleHelper::LEVEL_ADMIN_MTM, 'category' => null],
    ['id' => 997, 'level' => RoleHelper::LEVEL_MEMBER, 'category' => 'alumni'],
];

foreach ($testUsers as $user) {
    $success = RoleHelper::setUserLevel($user['id'], $user['level']);
    echo "User {$user['id']}: " . ($success ? "✓" : "✗") . " Set level to {$user['level']}\n";
    
    if ($user['category']) {
        $catSuccess = RoleHelper::setMemberCategory($user['id'], $user['category']);
        echo "  Category: " . ($catSuccess ? "✓" : "✗") . " Set to {$user['category']}\n";
    }
}

echo "\n";

// Test 2: Retrieve levels
echo "Test 2: Retrieving User Levels\n";
echo "-------------------------------\n";

foreach ($testUsers as $user) {
    $level = RoleHelper::getUserLevel($user['id']);
    $category = RoleHelper::getMemberCategory($user['id']);
    
    echo "User {$user['id']}:\n";
    echo "  Level: $level\n";
    if ($category) {
        echo "  Category: $category\n";
    }
}

echo "\n";

// Test 3: Permission checks
echo "Test 3: Permission Checks\n";
echo "-------------------------\n";

$capabilities = ['manage_members', 'view_all_payments', 'make_payments'];

foreach ($testUsers as $user) {
    echo "User {$user['id']} ({$user['level']}):\n";
    foreach ($capabilities as $cap) {
        $can = RoleHelper::can($user['id'], $cap);
        echo "  - $cap: " . ($can ? "✓" : "✗") . "\n";
    }
    echo "\n";
}

// Test 4: Admin checks
echo "Test 4: Admin Checks\n";
echo "--------------------\n";

foreach ($testUsers as $user) {
    $isAdmin = RoleHelper::isAdmin($user['id']);
    $isSuperAdmin = RoleHelper::isSuperAdmin($user['id']);
    
    echo "User {$user['id']}:\n";
    echo "  isAdmin: " . ($isAdmin ? "Yes" : "No") . "\n";
    echo "  isSuperAdmin: " . ($isSuperAdmin ? "Yes" : "No") . "\n";
    echo "\n";
}

echo "=== Tests Complete ===\n";
