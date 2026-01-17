<?php
ob_start(); // Start output buffering to catch any unwanted output
require_once __DIR__ . '/src/Controllers/MemberController.php';

header('Content-Type: application/json');

try {
    $controller = new \App\Controllers\MemberController();
    
    // Clear buffer before sending response
    ob_clean();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            echo json_encode($controller->delete($id));
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            // Exclude action and id from data passed to update
            $data = $_POST;
            unset($data['action'], $data['id']);
            echo json_encode($controller->update($id, $data));
        } else {
            echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        if ($action === 'get') {
            $id = $_GET['id'] ?? 0;
            echo json_encode($controller->getMember($id));
        } else {
            echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
