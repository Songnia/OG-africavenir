<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "--- Categories in wp_usermeta ---\n";
$query = "SELECT meta_value, COUNT(*) as count FROM wp_usermeta WHERE meta_key = 'categorie' GROUP BY meta_value";
$stmt = $conn->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($results);

echo "\n--- Payment Statuses in app_contributions ---\n";
$query = "SELECT status, COUNT(*) as count FROM app_contributions GROUP BY status";
$stmt = $conn->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($results);

echo "\n--- Raw Contributions ---\n";
$query = "SELECT * FROM app_contributions LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($results);
