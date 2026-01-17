<?php
/**
 * Admin Check - Require Admin Level
 * Include this at the top of pages that require admin access
 */

require_once __DIR__ . '/../src/Helpers/RoleHelper.php';

use App\Helpers\RoleHelper;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

if (!RoleHelper::isAdmin($_SESSION['user_id'])) {
    header('Location: dashboard.php?error=unauthorized');
    exit;
}
