<?php
// test_email_config.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Services/EmailService.php';

use App\Services\EmailService;

// Check if run from CLI
if (php_sapi_name() !== 'cli') {
    echo "<pre>";
}

echo "Testing Email Configuration...\n";

$emailService = new EmailService();
$config = require __DIR__ . '/config/email.php';

echo "Configuration loaded.\n";
echo "SMTP Enabled: " . ($config['use_smtp'] ? 'Yes' : 'No') . "\n";
echo "From: " . $config['from_name'] . " <" . $config['from_email'] . ">\n";

if ($config['use_smtp']) {
    echo "SMTP Host: " . $config['smtp_host'] . "\n";
    echo "SMTP Port: " . $config['smtp_port'] . "\n";
    echo "SMTP User: " . ($config['smtp_username'] ? 'Set' : 'Empty (Please configure in config/email.php)') . "\n";
}

echo "\nSending test email to 'test@example.com'...\n";

// We use a dummy email, it won't actually arrive unless configured
$result = $emailService->sendWelcomeEmail('africavenir.foundation@gmail.com', 'Test User');

if ($result) {
    echo "Email sent successfully (according to PHPMailer)!\n";
    echo "Check your inbox (or spam folder) if you used a real email address.\n";
} else {
    echo "Failed to send email.\n";
    echo "Check logs/email.log for details.\n";
}

echo "\nLog file content (last 5 lines):\n";
$logFile = __DIR__ . '/logs/email.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -5);
    foreach ($lastLines as $line) {
        echo $line;
    }
} else {
    echo "Log file not found.\n";
}
