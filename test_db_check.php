<?php
require_once __DIR__ . '/src/Models/Member.php';
require_once __DIR__ . '/config/database.php';

use App\Models\Member;

echo "Testing Member::findByEmail for test9@gmail.com\n";

$member = new Member();
$result = $member->findByEmail('test9@gmail.com');

echo "Result:\n";
var_dump($result);

echo "\nDatabase Info:\n";
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT DATABASE()");
var_dump($stmt->fetchColumn());

echo "\nChecking wp_users content for test9:\n";
$stmt = $conn->query("SELECT ID, user_email FROM wp_users WHERE user_email LIKE 'test9%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_dump($rows);
