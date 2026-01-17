<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Models/Contribution.php';

$contribution = new \App\Models\Contribution();

// Test delete with ID 7
$result = $contribution->delete(7);

echo "Delete result: " . ($result ? 'true' : 'false') . "\n";

// Check if it exists
$query = "SELECT * FROM app_contributions WHERE id = 7";
$database = new \Database();
$conn = $database->getConnection();
$stmt = $conn->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Row after delete: ";
var_dump($row);
