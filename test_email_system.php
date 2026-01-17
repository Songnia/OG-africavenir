<?php
/**
 * Test Complete Email Credential System
 */

require_once __DIR__ . '/src/Controllers/MemberController.php';

use App\Controllers\MemberController;

echo "=== Testing Email Credential System ===\n\n";

// Test data for new member
$testMember = [
    'nom' => 'Jean Dupont',
    'email' => 'jean.dupont.test@example.com',
    'telephone' => '+237 690 00 00 00',
    'adresse' => '123 Rue Test',
    'ville' => 'Yaoundé',
    'categorie' => 'alumni'
];

echo "Test: Create New Member\n";
echo "-----------------------\n";
echo "Name: {$testMember['nom']}\n";
echo "Email: {$testMember['email']}\n";
echo "Category: {$testMember['categorie']}\n\n";

try {
    $memberController = new MemberController();
    $result = $memberController->create($testMember);
    
    if ($result['success']) {
        echo "✓ SUCCESS!\n";
        echo "Message: {$result['message']}\n";
        
        if (isset($result['credentials'])) {
            echo "\nGenerated Credentials:\n";
            echo "  Username: {$result['credentials']['username']}\n";
            echo "  Password: {$result['credentials']['password']}\n";
        }
        
        if (isset($result['id'])) {
            echo "\nUser ID: {$result['id']}\n";
        }
    } else {
        echo "✗ FAILED\n";
        echo "Error: {$result['message']}\n";
    }
} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n\n";

echo "Check the following:\n";
echo "1. Email log: logs/email.log\n";
echo "2. Database: SELECT * FROM wp_users WHERE user_email = '{$testMember['email']}';\n";
echo "3. User meta: SELECT * FROM wp_usermeta WHERE user_id = (SELECT ID FROM wp_users WHERE user_email = '{$testMember['email']}');\n";
