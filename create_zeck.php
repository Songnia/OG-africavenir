<?php
require_once __DIR__ . '/src/Models/Member.php';
require_once __DIR__ . '/src/Helpers/RoleHelper.php';

use App\Models\Member;
use App\Helpers\RoleHelper;

$memberModel = new Member();

// Check if user exists first to avoid duplicates/errors
$existing = $memberModel->findByEmail('zeck@admin.com');
if ($existing) {
    echo "User already exists (ID: " . $existing['ID'] . "). Updating role...\n";
    RoleHelper::setUserLevel($existing['ID'], RoleHelper::LEVEL_SUPERADMIN);
    exit;
}

$data = [
    'username' => 'Zeck',
    'password' => '123456',
    'email' => 'zeck@admin.com',
    'display_name' => 'Zeck',
    'first_name' => 'Zeck',
    'last_name' => 'Admin',
    'categorie' => 'member-ships'
];

echo "Creating Super Admin 'Zeck'...\n";
$userId = $memberModel->createMember($data);

if ($userId) {
    RoleHelper::setUserLevel($userId, RoleHelper::LEVEL_SUPERADMIN);
    echo "Success! Super Admin 'Zeck' created with ID: $userId\n";
    echo "Username: Zeck\n";
    echo "Password: 123456\n";
} else {
    echo "Failed to create user.\n";
}
