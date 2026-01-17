<?php
/**
 * Test Email Sending
 */

require_once __DIR__ . '/src/Services/EmailService.php';

use App\Services\EmailService;

echo "=== Testing Email Service ===\n\n";

// Test 1: Check mail() function
echo "Test 1: Check mail() availability\n";
echo "----------------------------------\n";
if (function_exists('mail')) {
    echo "✓ mail() function is available\n";
} else {
    echo "✗ mail() function is NOT available\n";
    echo "  You need to configure a mail server or use SMTP\n";
}
echo "\n";

// Test 2: Try to send test email
echo "Test 2: Send Test Email\n";
echo "-----------------------\n";

try {
    $emailService = new EmailService();
    
    // Use a test email - replace with your actual email
    $testEmail = "test@example.com"; // CHANGE THIS to your real email
    $testUsername = "test.user";
    $testPassword = "TestPass123!";
    $testName = "Test User";
    
    echo "Attempting to send email to: $testEmail\n";
    
    $result = $emailService->sendCredentials(
        $testEmail,
        $testUsername,
        $testPassword,
        $testName
    );
    
    if ($result) {
        echo "✓ Email sent successfully!\n";
        echo "  Check your inbox (and spam folder)\n";
    } else {
        echo "✗ Email sending failed\n";
        echo "  This is normal if mail server is not configured\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check email log
echo "Test 3: Check Email Log\n";
echo "-----------------------\n";
if (file_exists('logs/email.log')) {
    echo "Email log contents:\n";
    echo file_get_contents('logs/email.log');
} else {
    echo "No email log file found yet\n";
}

echo "\n=== Test Complete ===\n\n";

echo "IMPORTANT NOTES:\n";
echo "1. PHP mail() requires a mail server configured on the system\n";
echo "2. On localhost, emails often don't send without SMTP\n";
echo "3. For production, configure SMTP in config/email.php\n";
echo "4. For testing, credentials are shown in the success message\n";
