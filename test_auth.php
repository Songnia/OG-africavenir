<?php
/**
 * Comprehensive Authentication System Test
 * Tests login, session management, and role integration
 */

require_once __DIR__ . '/src/Auth/Auth.php';
require_once __DIR__ . '/src/Helpers/RoleHelper.php';

use App\Helpers\RoleHelper;

echo "=== Authentication System Check ===\n\n";

// Test 1: Check Auth class instantiation
echo "Test 1: Auth Class Instantiation\n";
echo "---------------------------------\n";
try {
    $auth = new \App\Auth\Auth();
    echo "✓ Auth class instantiated successfully\n\n";
} catch (Exception $e) {
    echo "✗ Failed to instantiate Auth class: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check database connection
echo "Test 2: Database Connection\n";
echo "---------------------------\n";
try {
    $database = new \Database();
    $conn = $database->getConnection();
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Check if test user exists
echo "Test 3: Test User Existence\n";
echo "---------------------------\n";
$query = "SELECT ID, user_login, user_email FROM wp_users WHERE user_login = 'testuser' LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$testUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($testUser) {
    echo "✓ Test user found:\n";
    echo "  ID: {$testUser['ID']}\n";
    echo "  Login: {$testUser['user_login']}\n";
    echo "  Email: {$testUser['user_email']}\n\n";
} else {
    echo "✗ Test user 'testuser' not found in database\n";
    echo "  Creating test user...\n";
    
    // Create test user
    $insertQuery = "INSERT INTO wp_users (user_login, user_pass, user_email, user_registered, display_name) 
                    VALUES ('testuser', MD5('password'), 'test@example.com', NOW(), 'Test User')";
    try {
        $conn->exec($insertQuery);
        echo "✓ Test user created\n\n";
        
        // Get the new user
        $stmt->execute();
        $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "✗ Failed to create test user: " . $e->getMessage() . "\n\n";
    }
}

// Test 4: Check user level
if ($testUser) {
    echo "Test 4: User Level Check\n";
    echo "------------------------\n";
    $userLevel = RoleHelper::getUserLevel($testUser['ID']);
    echo "Current level: $userLevel\n";
    
    if ($userLevel === 'member') {
        echo "Setting test user to admin-mtm level...\n";
        RoleHelper::setUserLevel($testUser['ID'], RoleHelper::LEVEL_ADMIN_MTM);
        $userLevel = RoleHelper::getUserLevel($testUser['ID']);
        echo "✓ New level: $userLevel\n\n";
    } else {
        echo "✓ User already has level: $userLevel\n\n";
    }
}

// Test 5: Login attempt
echo "Test 5: Login Test\n";
echo "------------------\n";
$username = 'testuser';
$password = 'password';

echo "Attempting login with username: $username\n";

if ($auth->login($username, $password)) {
    echo "✓ Login successful!\n";
    echo "\nSession Data:\n";
    echo "  user_id: " . ($_SESSION['user_id'] ?? 'not set') . "\n";
    echo "  user_login: " . ($_SESSION['user_login'] ?? 'not set') . "\n";
    echo "  user_email: " . ($_SESSION['user_email'] ?? 'not set') . "\n";
    echo "  user_level: " . ($_SESSION['user_level'] ?? 'not set') . "\n";
    echo "  member_category: " . ($_SESSION['member_category'] ?? 'not set') . "\n";
    echo "\n";
} else {
    echo "✗ Login failed\n";
    echo "  Possible reasons:\n";
    echo "  - Incorrect password\n";
    echo "  - User not found\n";
    echo "  - Database connection issue\n\n";
}

// Test 6: Check if logged in
echo "Test 6: Login Status Check\n";
echo "--------------------------\n";
if ($auth->isLoggedIn()) {
    echo "✓ User is logged in\n\n";
} else {
    echo "✗ User is not logged in\n\n";
}

// Test 7: Get current user
echo "Test 7: Get Current User\n";
echo "------------------------\n";
$currentUser = $auth->getCurrentUser();
if ($currentUser) {
    echo "✓ Current user retrieved:\n";
    print_r($currentUser);
    echo "\n";
} else {
    echo "✗ No current user\n\n";
}

// Test 8: Role helper integration
echo "Test 8: Role Helper Integration\n";
echo "-------------------------------\n";
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "User ID: $userId\n";
    echo "Is Admin: " . (RoleHelper::isAdmin($userId) ? "Yes" : "No") . "\n";
    echo "Is SuperAdmin: " . (RoleHelper::isSuperAdmin($userId) ? "Yes" : "No") . "\n";
    echo "Can manage members: " . (RoleHelper::can($userId, 'manage_members') ? "Yes" : "No") . "\n";
    echo "Can view all payments: " . (RoleHelper::can($userId, 'view_all_payments') ? "Yes" : "No") . "\n";
    echo "\n";
}

// Test 9: Logout
echo "Test 9: Logout Test\n";
echo "-------------------\n";
$auth->logout();
if (!$auth->isLoggedIn()) {
    echo "✓ Logout successful\n\n";
} else {
    echo "✗ Logout failed - user still logged in\n\n";
}

echo "=== Authentication System Check Complete ===\n";
