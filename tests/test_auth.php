<?php
require_once __DIR__ . '/../src/Auth/Auth.php';

use App\Auth\Auth;

// Mock session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth();

echo "Testing Auth...\n";
// We can't easily test login without a real DB and WP, but we can test 2FA logic
$code = $auth->send2FACode(123);
echo "Generated Code: $code\n";

if ($auth->verify2FACode($code)) {
    echo "2FA Verification Passed!\n";
} else {
    echo "2FA Verification Failed!\n";
}

if ($auth->verify2FACode($code + 1)) {
    echo "2FA Verification (Wrong Code) Failed (Expected)!\n";
} else {
    echo "2FA Verification (Wrong Code) Passed (Unexpected)!\n";
}
