<?php
/**
 * Test Role-Based Redirects
 * Tests login redirect logic for different user levels
 */

require_once __DIR__ . '/src/Auth/Auth.php';
require_once __DIR__ . '/src/Helpers/RoleHelper.php';

use App\Helpers\RoleHelper;

echo "=== Testing Role-Based Redirects ===\n\n";

// Test users with different levels
$testUsers = [
    ['username' => 'testuser', 'password' => 'password', 'expected_level' => 'admin-mtm'],
];

foreach ($testUsers as $testUser) {
    echo "Test User: {$testUser['username']}\n";
    echo "Expected Level: {$testUser['expected_level']}\n";
    
    // Create new auth instance
    $auth = new \App\Auth\Auth();
    
    // Attempt login
    if ($auth->login($testUser['username'], $testUser['password'])) {
        echo "✓ Login successful\n";
        
        // Check session data
        $userLevel = $_SESSION['user_level'] ?? 'not set';
        echo "User Level: $userLevel\n";
        
        // Determine redirect
        if (RoleHelper::currentUserIsAdmin()) {
            $redirect = 'dashboard-admin.php';
            echo "✓ Should redirect to: $redirect (ADMIN)\n";
        } else {
            $redirect = 'member-contribution.php';
            echo "✓ Should redirect to: $redirect (MEMBER)\n";
        }
        
        // Logout
        $auth->logout();
        echo "✓ Logged out\n";
    } else {
        echo "✗ Login failed\n";
    }
    
    echo "\n";
}

echo "=== Test Complete ===\n\n";

echo "Manual Test Instructions:\n";
echo "1. Open browser and navigate to: http://localhost/OG-afrcavenir/login.php\n";
echo "2. Login with username: testuser, password: password\n";
echo "3. Verify redirect to dashboard-admin.php (admin user)\n";
echo "4. Logout and create a member user\n";
echo "5. Login as member and verify redirect to member-contribution.php\n";
