<?php
return [
    'currency' => 'xaf', // Stripe supports XAF, but we might need to check if we want to convert to EUR/USD if XAF is not supported by the account.
    // For now, let's assume XAF or we will handle conversion. Stripe DOES support XAF.
    
    'test' => [
        'publishable_key' => 'pk_test_51ScUR60L3SDTXvtbTx8h3Y82kZ0YDlioes3a229kggKzCgHOL1bX67HNgisYlv0lT1TlQXGuoxCw6Hn5jQwVScT700mPd2f7wz', // Example test key
        'secret_key' => 'sk_test_51ScUR60L3SDTXvtb0JbZmpL5x732ACpP0RzbeYwOptz22kZ67TEOisIaZVR7eCaJrMA9AUkM81atI0ngjAwSFcGW0020eCeJPr', // Example test key
        'webhook_secret' => 'whsec_...', // Replace with actual webhook secret from Stripe Dashboard
    ],
    'live' => [
        'publishable_key' => 'pk_live_...',
        'secret_key' => 'sk_live_...',
        'webhook_secret' => 'whsec_...',
    ],
    
    'environment' => 'test', // 'test' or 'live'
    
    // URLs
    'success_url' => 'http://localhost/OG-afrcavenir/payment-success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'http://localhost/OG-afrcavenir/payment-cancel.php',
];
