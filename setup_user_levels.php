<?php
/**
 * Setup User Levels
 * Run this script to assign levels to existing users
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Helpers/RoleHelper.php';

use App\Helpers\RoleHelper;

echo "=== AfricAvenir User Level Setup ===\n\n";

// Get all users
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT ID, user_login, user_email FROM wp_users ORDER BY ID";
$stmt = $conn->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($users) . " users\n\n";

foreach ($users as $user) {
    echo "User ID: {$user['ID']} - {$user['user_login']} ({$user['user_email']})\n";
    
    // Check existing level
    $existingLevel = RoleHelper::getUserLevel($user['ID']);
    echo "  Current level: $existingLevel\n";
    
    // Prompt for level
    echo "  Set level (1=SuperAdmin, 2=admin-mtm, 3=member, skip=keep current): ";
    $input = trim(fgets(STDIN));
    
    if ($input === '' || $input === 'skip') {
        echo "  Skipped\n\n";
        continue;
    }
    
    $level = null;
    $category = null;
    
    switch ($input) {
        case '1':
            $level = RoleHelper::LEVEL_SUPERADMIN;
            break;
        case '2':
            $level = RoleHelper::LEVEL_ADMIN_MTM;
            break;
        case '3':
            $level = RoleHelper::LEVEL_MEMBER;
            // Ask for category
            echo "  Select category:\n";
            echo "    1 = Member-ships\n";
            echo "    2 = Alumni\n";
            echo "    3 = Cercle d'amis\n";
            echo "    4 = Mécène\n";
            echo "  Choice: ";
            $catInput = trim(fgets(STDIN));
            switch ($catInput) {
                case '1':
                    $category = 'member-ships';
                    break;
                case '2':
                    $category = 'alumni';
                    break;
                case '3':
                    $category = 'cercle-amis';
                    break;
                case '4':
                    $category = 'mecene';
                    break;
                default:
                    $category = 'member-ships';
            }
            break;
        default:
            echo "  Invalid input, skipping\n\n";
            continue 2;
    }
    
    // Set level
    if (RoleHelper::setUserLevel($user['ID'], $level)) {
        echo "  ✓ Set level: $level\n";
        
        // Set category if member
        if ($category) {
            if (RoleHelper::setMemberCategory($user['ID'], $category)) {
                echo "  ✓ Set category: $category\n";
            }
        }
    } else {
        echo "  ✗ Failed to set level\n";
    }
    
    echo "\n";
}

echo "\n=== Setup Complete ===\n";
echo "\nVerify in database:\n";
echo "SELECT u.ID, u.user_login, m1.meta_value as level, m2.meta_value as category\n";
echo "FROM wp_users u\n";
echo "LEFT JOIN wp_usermeta m1 ON u.ID = m1.user_id AND m1.meta_key = 'user_level'\n";
echo "LEFT JOIN wp_usermeta m2 ON u.ID = m2.user_id AND m2.meta_key = 'member_category';\n";
