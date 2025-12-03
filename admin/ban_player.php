<?php
require_once '../php/admin_auth.php';
require_once '../php/db.php';

$action = $_GET['action'] ?? '';
$username = $_GET['username'] ?? '';

if (empty($action) || empty($username)) {
    header('Location: players.php');
    exit;
}

try {
    $pdo = getDBConnection(DB_LS);
    
    if ($action === 'ban') {
        // Ban = accessLevel -100
        $stmt = $pdo->prepare("UPDATE accounts SET accessLevel = -100 WHERE login = ?");
        $stmt->execute([$username]);
        $_SESSION['success'] = "Játékos sikeresen bannolva: $username";
        
    } elseif ($action === 'unban') {
        // Unban = accessLevel 0
        $stmt = $pdo->prepare("UPDATE accounts SET accessLevel = 0 WHERE login = ?");
        $stmt->execute([$username]);
        $_SESSION['success'] = "Ban feloldva: $username";
    }
    
    header('Location: players.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Hiba történt: " . $e->getMessage();
    header('Location: players.php');
    exit;
}
?>
