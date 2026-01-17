<?php
require_once __DIR__ . '/src/Controllers/MemberController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new \App\Controllers\MemberController();
    $response = $controller->create($_POST);
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
