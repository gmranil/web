<?php
// Activity logger helper
function logActivity($username, $activityType, $details = null) {
    try {
        require_once __DIR__ . '/db.php';
        $pdo = getDBConnection(DB_LS);
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $pdo->prepare("
            INSERT INTO account_activity (username, activity_type, ip_address, user_agent, details) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $username,
            $activityType,
            $ipAddress,
            $userAgent,
            $details
        ]);
        
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
}

// Coin transaction logger
function logCoinTransaction($username, $amount, $balanceAfter, $type, $description = null) {
    try {
        require_once __DIR__ . '/db.php';
        $pdo = getDBConnection(DB_LS);
        
        $stmt = $pdo->prepare("
            INSERT INTO coin_transactions (username, amount, balance_after, type, description) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $username,
            $amount,
            $balanceAfter,
            $type,
            $description
        ]);
        
    } catch (PDOException $e) {
        error_log("Coin transaction log error: " . $e->getMessage());
    }
}
?>
