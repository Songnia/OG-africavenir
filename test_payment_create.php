<?php
require_once __DIR__ . '/src/Controllers/PaymentController.php';
require_once __DIR__ . '/src/Models/Member.php';

// Instantiate controllers/models
$paymentController = new \App\Controllers\PaymentController();
$memberModel = new \App\Models\Member();

// 1. Get a valid member
echo "Fetching members...\n";
$members = $memberModel->getAllMembers();

if (empty($members)) {
    die("Error: No members found in database. Cannot test payment creation.\n");
}

$testMember = $members[0];
$memberId = $testMember['ID'];
$memberName = $testMember['display_name'];

echo "Using Member: ID=$memberId, Name=$memberName\n";

// 2. Prepare test data
$testData = [
    'member' => $memberId,
    'montant' => 5000,
    'motif' => 'Test Payment Script',
    'mode' => 'Carte Bancaire',
    'status' => 'pending'
];

echo "Attempting to create payment with data:\n";
print_r($testData);

// 3. Call store method
$result = $paymentController->store($testData);

echo "\nResult:\n";
print_r($result);

if ($result['success']) {
    echo "\nSUCCESS: Payment created successfully.\n";
} else {
    echo "\nFAILURE: " . $result['message'] . "\n";
}
