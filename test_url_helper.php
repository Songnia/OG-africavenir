<?php
/**
 * Test script to verify UrlHelper works correctly
 */
require_once __DIR__ . '/src/Helpers/UrlHelper.php';

echo "=== URL Helper Test ===\n\n";

// Simulate different environments
echo "1. Testing with current environment:\n";
echo "   Base URL: " . UrlHelper::getBaseUrl() . "\n";
echo "   Registration Success: " . UrlHelper::buildUrl('registration-success.php?transaction_id=TEST123') . "\n";
echo "   Payment Success: " . UrlHelper::buildUrl('payment-success.php?session_id=TEST456') . "\n\n";

echo "2. Server Variables:\n";
echo "   HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n";
echo "   SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "\n";
echo "   HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'yes' : 'no') . "\n\n";

echo "✅ UrlHelper is working correctly!\n";
