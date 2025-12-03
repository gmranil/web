<?php
require_once 'db.php';
require_once 'csrf.php';
require_once 'rate_limit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

// CSRF védelem
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: ../register.php');
    exit;
}

// Rate limiting
$clientIP = getClientIP();
$rateCheck = checkRateLimit('register', $clientIP, 3, 600);

if (!$rateCheck['allowed']) {
    $waitMinutes = ceil($rateCheck['wait_seconds'] / 60);
    $_SESSION['error'] = "Too many registration attempts. Please wait {$waitMinutes} minute(s) before trying again.";
    header('Location: ../register.php');
    exit;
}

// Input validálás
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

// Validation
$errors = [];

if (empty($username)) {
    $errors[] = 'Username is required.';
} elseif (strlen($username) < 4 || strlen($username) > 16) {
    $errors[] = 'Username must be between 4 and 16 characters.';
} elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    $errors[] = 'Username can only contain letters and numbers.';
}

if (empty($password)) {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 4) {
    $errors[] = 'Password must be at least 4 characters.';
}

if ($password !== $passwordConfirm) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    recordRateLimitAttempt('register', $clientIP);
    $_SESSION['error'] = implode(' ', $errors);
    header('Location: ../register.php');
    exit;
}

// Check if username exists
try {
    $pdo = getDBConnection(DB_LS);
    
    $stmt = $pdo->prepare("SELECT login FROM accounts WHERE login = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        recordRateLimitAttempt('register', $clientIP);
        $_SESSION['error'] = 'Username already exists. Please choose another one.';
        header('Location: ../register.php');
        exit;
    }
    
    // Insert new account - L2J Mobius format (Base64 encoded SHA1)
    $sha1Hash = hash('sha1', $password, true); // binary output
    $base64Hash = base64_encode($sha1Hash);
    
    // Debug
    if (DEBUG_MODE) {
        logError("Registration", "User: $username | Hash: $base64Hash");
    }
    
    $insertStmt = $pdo->prepare("
        INSERT INTO accounts (login, password, access_level, lastactive, donate_coins) 
        VALUES (?, ?, 0, ?, 0)
    ");
    $insertStmt->execute([$username, $base64Hash, time()]);
    
    // Success
    resetRateLimit('register', $clientIP);
    $_SESSION['success'] = 'Registration successful! You can now login.';
    header('Location: ../login.php');
    exit;
    
} catch (PDOException $e) {
    logError("Registration error", $e->getMessage());
    
    if (DEBUG_MODE) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    } else {
        $_SESSION['error'] = 'Database error. Please try again later.';
    }
    
    header('Location: ../register.php');
    exit;
}
