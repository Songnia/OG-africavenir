<?php
return [
    'sandbox' => [
        'apikey' => '4401509536807a7c0088988.13583170',
        'site_id' => '105892954',
        'secret_key' => '5026478456807a7fbd757f9.44029532',
        'base_url' => 'https://api-checkout.cinetpay.com/v2/',
    ],
    'production' => [
        'apikey' => '4401509536807a7c0088988.13583170',
        'site_id' => '105892954',
        'secret_key' => '5026478456807a7fbd757f9.44029532',
        'base_url' => 'https://api-checkout.cinetpay.com/v2/',
    ],
    'environment' => 'sandbox', // 'sandbox' ou 'production'
    'currency' => 'XAF', // Franc CFA
    'notify_url' => 'https://5798414d7c32.ngrok-free.app/OG-afrcavenir/cinetpay-notify.php',
    'return_url' => 'https://5798414d7c32.ngrok-free.app/OG-afrcavenir/register-response.php',
    'cancel_url' => 'https://5798414d7c32.ngrok-free.app/OG-afrcavenir/payment-cancel.php',
];
