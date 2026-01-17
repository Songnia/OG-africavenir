<?php
// webhook-stripe.php

require_once 'vendor/autoload.php';
require_once 'src/Services/StripeService.php';
require_once 'src/Models/Contribution.php';

// Set response code to 200 by default
http_response_code(200);

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

require_once __DIR__ . '/src/Controllers/StripeController.php';
$controller = new \App\Controllers\StripeController();

$result = $controller->handleWebhook($payload, $sig_header);

if (!$result['success']) {
    http_response_code($result['status'] ?? 400);
    echo $result['message'];
    exit();
}

// DB update is handled by the controller
// if (isset($result['type']) && $result['type'] === 'checkout.session.completed') { ... }

echo 'Received';
