<?php
require_once 'db.php';
require_once 'csrf.php';
require_once 'rate_limit.php';

// Check if logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../change_password.php');
    exit;
}

// CSRF védelem
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: ../change_password.php');
    exit;
}

// Rate limiting
$clientIP = getClientIP();
$rateCheck = checkRateLimit('change_password', $clientIP, 3, 600); // 3 attempts / 10 min

if (!$rateCheck['allowed']) {
    $waitMinutes = ceil($rateCheck['wait_seconds'] / 60);
    $_SESSION['error'] = "Too many attempts. Please wait {$waitMinutes} minute(s) before trying again.";
    header('Location: ../change_password.php');
    exit;
}

$username = $_SESSION['username'];
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($oldPassword)) {
    $errors[] = 'Current password is required.';
}

if (empty($newPassword)) {
    $errors[] = 'New password is required.';
} elseif (strlen($newPassword) < 4) {
    $errors[] = 'New password must be at least 4 characters.';
}

if ($newPassword !== $confirmPassword) {
    $errors[] = 'New passwords do not match.';
}

if ($oldPassword === $newPassword) {
    $errors[] = 'New password must be different from current password.';
}

if (!empty($errors)) {
    recordRateLimitAttempt('change_password', $clientIP);
    $_SESSION['error'] = implode(' ', $errors);
    header('Location: ../change_password.php');
    exit;
}

try {
    $pdo = getDBConnection(DB_LS);
    
    // Get current password
    $stmt = $pdo->prepare("SELECT password FROM accounts WHERE login = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        header('Location: ../change_password.php');
        exit;
    }
    
    // Verify old password - L2J Mobius format (Base64 SHA1)
    $oldSha1Hash = hash('sha1', $oldPassword, true);
    $oldBase64Hash = base64_encode($oldSha1Hash);
    
    if ($oldBase64Hash !== $user['password']) {
        recordRateLimitAttempt('change_password', $clientIP);
        $_SESSION['error'] = 'Current password is incorrect.';
        header('Location: ../change_password.php');
        exit;
    }
    
    // Update to new password - L2J Mobius format (Base64 SHA1)
    $newSha1Hash = hash('sha1', $newPassword, true);
    $newBase64Hash = base64_encode($newSha1Hash);
    
    $updateStmt = $pdo->prepare("UPDATE accounts SET password = ? WHERE login = ?");
    $updateStmt->execute([$newBase64Hash, $username]);
    
    // Log activity
    logAccountActivity($username, 'password_change', $clientIP, 'Password changed successfully');
    
    // Success - NINCS logout, marad bejelentkezve!
    resetRateLimit('change_password', $clientIP);
    $_SESSION['success'] = 'Password changed successfully!';
    
    // Redirect vissza change_password.php-ra (NEM logout!)
    header('Location: ../change_password.php');
    exit;
    
} catch (PDOException $e) {
    logError("Change password error", $e->getMessage());
    
    if (DEBUG_MODE) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    } else {
        $_SESSION['error'] = 'An error occurred. Please try again later.';
    }
    
    header('Location: ../change_password.php');
    exit;
}
