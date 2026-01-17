<?php
ob_start();
require_once __DIR__ . '/src/Controllers/PaymentController.php';

header('Content-Type: application/json');

try {
    $controller = new \App\Controllers\PaymentController();
    ob_clean();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            echo json_encode($controller->delete($id));
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $data = $_POST;
            unset($data['action'], $data['id']);
            echo json_encode($controller->update($id, $data));
        } elseif ($action === 'create') {
            $data = $_POST;
            unset($data['action']);
            echo json_encode($controller->store($data));
        } else {
            echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        if ($action === 'get') {
            $id = $_GET['id'] ?? 0;
            echo json_encode($controller->get($id));
        } elseif ($action === 'list') {
            echo json_encode($controller->index());
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
