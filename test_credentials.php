<?php
/**
 * Test Credential Generator
 */

require_once __DIR__ . '/src/Helpers/CredentialGenerator.php';

use App\Helpers\CredentialGenerator;

echo "=== Testing Credential Generator ===\n\n";

// Test 1: Generate username from name
echo "Test 1: Generate Username from Name\n";
echo "------------------------------------\n";
$username1 = CredentialGenerator::generateUsername('jean.dupont@example.com', 'Jean', 'Dupont');
echo "Email: jean.dupont@example.com\n";
echo "Name: Jean Dupont\n";
echo "Generated Username: $username1\n\n";

// Test 2: Generate username from email only
echo "Test 2: Generate Username from Email\n";
echo "-------------------------------------\n";
$username2 = CredentialGenerator::generateUsername('marie.martin@gmail.com');
echo "Email: marie.martin@gmail.com\n";
echo "Generated Username: $username2\n\n";

// Test 3: Generate password
echo "Test 3: Generate Secure Password\n";
echo "---------------------------------\n";
for ($i = 1; $i <= 3; $i++) {
    $password = CredentialGenerator::generatePassword();
    echo "Password $i: $password (length: " . strlen($password) . ")\n";
}
echo "\n";

// Test 4: Generate complete credentials
echo "Test 4: Generate Complete Credentials\n";
echo "--------------------------------------\n";
$credentials = CredentialGenerator::generateCredentials('test@example.com', 'Test', 'User');
echo "Email: test@example.com\n";
echo "Name: Test User\n";
echo "Username: {$credentials['username']}\n";
echo "Password: {$credentials['password']}\n\n";

// Test 5: Test with special characters in name
echo "Test 5: Special Characters Handling\n";
echo "------------------------------------\n";
$username3 = CredentialGenerator::generateUsername('françois.côté@example.com', 'François', 'Côté');
echo "Email: françois.côté@example.com\n";
echo "Name: François Côté\n";
echo "Generated Username: $username3\n\n";

echo "=== Tests Complete ===\n";
