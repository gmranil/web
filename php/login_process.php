<?php
require_once 'db.php';
require_once 'csrf.php';
require_once 'rate_limit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// CSRF védelem
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: ../login.php');
    exit;
}

// Rate limiting
$clientIP = getClientIP();
$rateCheck = checkRateLimit('login', $clientIP, 5, 300);

if (!$rateCheck['allowed']) {
    $waitMinutes = ceil($rateCheck['wait_seconds'] / 60);
    $_SESSION['error'] = "Too many login attempts. Please wait {$waitMinutes} minute(s) before trying again.";
    header('Location: ../login.php');
    exit;
}

// Input validálás
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    recordRateLimitAttempt('login', $clientIP);
    $_SESSION['error'] = 'Username and password are required.';
    header('Location: ../login.php');
    exit;
}

// Login attempt
try {
    $pdo = getDBConnection(DB_LS);
    
    $stmt = $pdo->prepare("SELECT login, password, access_level FROM accounts WHERE login = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        recordRateLimitAttempt('login', $clientIP);
        $_SESSION['error'] = 'Invalid username or password.';
        header('Location: ../login.php');
        exit;
    }
    
    // Password check - L2J Mobius format (Base64 encoded SHA1)
    $sha1Hash = hash('sha1', $password, true); // binary output
    $base64Hash = base64_encode($sha1Hash);
    
    // Debug logging
    if (DEBUG_MODE) {
        logError("Login attempt", "User: $username | Hash: $base64Hash | DB: {$user['password']}");
    }
    
    if ($base64Hash !== $user['password']) {
        recordRateLimitAttempt('login', $clientIP);
        $_SESSION['error'] = 'Invalid username or password.';
        header('Location: ../login.php');
        exit;
    }
    
    // Sikeres login
    resetRateLimit('login', $clientIP);
    regenerateCSRFToken();
    
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $user['login'];
    $_SESSION['access_level'] = $user['access_level'];
    
    // Update last active
    try {
        $updateStmt = $pdo->prepare("UPDATE accounts SET lastactive = ? WHERE login = ?");
        $updateStmt->execute([time(), $user['login']]);
    } catch (PDOException $e) {
        logError("Update lastactive failed", $e->getMessage());
    }
    
    // Activity log
    try {
        logAccountActivity($user['login'], 'login', $clientIP, 'Successful login');
    } catch (Exception $e) {
        logError("Activity log failed", $e->getMessage());
    }
    
    header('Location: ../account.php');
    exit;
    
} catch (PDOException $e) {
    logError("Login process error", $e->getMessage());
    
    if (DEBUG_MODE) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    } else {
        $_SESSION['error'] = 'Database error. Please try again later.';
    }
    
    header('Location: ../login.php');
    exit;
}
