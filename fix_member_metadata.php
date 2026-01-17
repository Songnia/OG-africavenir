<?php
/**
 * Fix existing members' metadata field names
 * Convert English field names to French
 */

require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "=== Fixing Member Metadata Field Names ===\n\n";

// Field name mappings: old => new
$fieldMappings = [
    'phone' => 'telephone',
    'address' => 'adresse',
    'city' => 'ville'
];

foreach ($fieldMappings as $oldKey => $newKey) {
    echo "Converting '$oldKey' to '$newKey'...\n";
    
    // Check if any records exist with old key
    $checkQuery = "SELECT COUNT(*) as count FROM wp_usermeta WHERE meta_key = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->execute([$oldKey]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    
    if ($count > 0) {
        echo "  Found $count records with '$oldKey'\n";
        
        // Update the meta_key
        $updateQuery = "UPDATE wp_usermeta SET meta_key = ? WHERE meta_key = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute([$newKey, $oldKey]);
        
        echo "  ✓ Updated to '$newKey'\n";
    } else {
        echo "  No records found with '$oldKey'\n";
    }
}

echo "\n=== Fix Complete ===\n\n";

// Verify the changes
echo "Verifying changes for user ID 15:\n";
$query = "SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id = 15 AND meta_key IN ('telephone', 'adresse', 'ville') ORDER BY meta_key";
$stmt = $conn->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    echo "  {$row['meta_key']}: {$row['meta_value']}\n";
}
