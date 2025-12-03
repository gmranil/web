<?php
// ============================================
// ADMIN AUTHENTICATION CHECK
// ============================================

require_once __DIR__ . '/../php/db.php';

// Session már elindul a db.php-ban, de biztos ami biztos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug mode logging (ha DEBUG_MODE aktív)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    $debugInfo = [
        'logged_in' => $_SESSION['logged_in'] ?? 'not set',
        'username' => $_SESSION['username'] ?? 'not set',
        'access_level' => $_SESSION['access_level'] ?? 'not set'
    ];
    logError("Admin auth check", json_encode($debugInfo));
}

// Check if logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = 'Please login to access admin panel.';
    header('Location: ../login.php');
    exit;
}

// Check admin access level (must be >= 100)
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 100) {
    $_SESSION['error'] = 'Access denied. Admin privileges required. Your access level: ' . ($_SESSION['access_level'] ?? 'undefined');
    header('Location: ../account.php');
    exit;
}

// If we got here, user is authenticated and has admin privileges
// Nothing else to do, the including file will continue
