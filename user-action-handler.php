<?php
require_once 'includes/admin-check.php';
require_once 'src/Controllers/MemberController.php';
require_once 'src/Helpers/RoleHelper.php';

use App\Helpers\RoleHelper;
use App\Controllers\MemberController;

header('Content-Type: application/json');

// Strict Super Admin Check
if (!RoleHelper::isSuperAdmin($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$memberController = new MemberController();

if ($action === 'create') {
    // Prepare data
    $data = [
        'nom' => $_POST['nom'] . ' ' . $_POST['prenom'], // Combine for display name logic in controller
        'first_name' => $_POST['prenom'],
        'last_name' => $_POST['nom'],
        'email' => $_POST['email'],
        'role' => $_POST['role'],
        'categorie' => $_POST['categorie'] ?? 'member-ships'
    ];

    // Use MemberController to create (it handles credentials generation)
    // We need to modify MemberController::create to accept 'role' or handle it here after creation
    // For now, we'll let controller create it as member, then update role here.
    
    // Actually, let's modify MemberController to be cleaner, but for now:
    $result = $memberController->create($data);

    if ($result['success']) {
        $userId = $result['id'];
        // Update Role
        RoleHelper::setUserLevel($userId, $data['role']);
        if ($data['role'] !== RoleHelper::LEVEL_MEMBER) {
            // Remove category if not member (optional, but cleaner)
            // RoleHelper::setMemberCategory($userId, null); 
        } else {
            RoleHelper::setMemberCategory($userId, $data['categorie']);
        }
        
        echo json_encode($result);
    } else {
        echo json_encode($result);
    }

} elseif ($action === 'update') {
    $id = $_POST['id'];
    $data = [
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'email' => $_POST['email'],
        'categorie' => $_POST['categorie']
    ];

    // Update basic info
    $result = $memberController->update($id, $data);

    if ($result['success']) {
        // Update Role
        $role = $_POST['role'];
        RoleHelper::setUserLevel($id, $role);
        
        if ($role === RoleHelper::LEVEL_MEMBER) {
            RoleHelper::setMemberCategory($id, $data['categorie']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour']);
    } else {
        echo json_encode($result);
    }

} elseif ($action === 'delete') {
    $id = $_POST['id'];
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Impossible de se supprimer soi-même']);
        exit;
    }
    
    $result = $memberController->delete($id);
    echo json_encode($result);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
