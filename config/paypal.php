<?php
return [
    'sandbox' => [
        'client_id' => 'AbVmr3bh_YOJU5KtyvxU_FO_hUhq0C7maKEpnfoUWOzCf-KtfrC-GEK9avkmJnDxHMmGAXJniyLBmd7f',
        'secret' => 'EL0RZDgPDo319bjAtHLpYNAh0dgPMM2CeEIMytHvf5HEIbR5tVl0axthNDhNvZLJH8Osz7cbLulFQopJ',
        'base_url' => 'https://api-m.sandbox.paypal.com',
    ],
    'production' => [
        'client_id' => 'YOUR_PRODUCTION_CLIENT_ID',
        'secret' => 'YOUR_PRODUCTION_SECRET',
        'base_url' => 'https://api-m.paypal.com',
    ],
    'environment' => 'sandbox', // 'sandbox' ou 'production'
    'currency' => 'XAF', // Franc CFA
    'return_url' => 'http://localhost/OG-afrcavenir/payment-success.php',
    'cancel_url' => 'http://localhost/OG-afrcavenir/payment-cancel.php',
    'brand_name' => 'AfricAvenir',
];
