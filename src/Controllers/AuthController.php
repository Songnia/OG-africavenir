<?php
namespace App\Controllers;

require_once __DIR__ . '/../Auth/Auth.php';
require_once __DIR__ . '/../Helpers/RoleHelper.php';

use App\Auth\Auth;
use App\Helpers\RoleHelper;

class AuthController {
    private $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    public function login() {
        // If already logged in, redirect based on role
        if ($this->auth->isLoggedIn()) {
            $this->redirectBasedOnRole();
        }
        
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['motdepasse'] ?? '';

            if ($this->auth->login($username, $password)) {
                // Login successful - redirect based on role
                $this->redirectBasedOnRole();
            } else {
                $error = "Identifiants incorrects.";
            }
        }
        return $error;
    }
    
    /**
     * Redirect user based on their role
     */
    private function redirectBasedOnRole() {
        if (RoleHelper::currentUserIsAdmin()) {
            header("Location: dashboard-admin.php");
        } else {
            header("Location: member-contribution.php");
        }
        exit;
    }

    public function logout() {
        $this->auth->logout();
        header("Location: index.php");
        exit;
    }
    
    public function requireLogin() {
        if (!$this->auth->isLoggedIn()) {
            header("Location: index.php");
            exit;
        }
    }
}
