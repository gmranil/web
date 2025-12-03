<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

/**
 * Get database connection
 */
function getDBConnection($dbType) {
    try {
        if ($dbType === DB_GS) {
            $pdo = new PDO(
                "mysql:host=" . DB_GS_HOST . ";dbname=" . DB_GS_NAME . ";charset=utf8mb4",
                DB_GS_USER,
                DB_GS_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } elseif ($dbType === DB_LS) {
            $pdo = new PDO(
                "mysql:host=" . DB_LS_HOST . ";dbname=" . DB_LS_NAME . ";charset=utf8mb4",
                DB_LS_USER,
                DB_LS_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } else {
            throw new Exception("Invalid database type: $dbType");
        }
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error
        logError("Database Connection Error", $e->getMessage());
        
        if (DEBUG_MODE) {
            die("Database Connection Error: " . $e->getMessage());
        }
        
        die("Database connection failed. Please contact administrator.");
    }
}

/**
 * Log account activity
 */
function logAccountActivity($username, $activityType, $ipAddress, $details = '') {
    try {
        $pdo = getDBConnection(DB_LS);
        $stmt = $pdo->prepare("
            INSERT INTO account_activity (username, activity_type, ip_address, details, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$username, $activityType, $ipAddress, $details]);
    } catch (PDOException $e) {
        logError("Activity Log Error", $e->getMessage());
        // Don't throw - activity logging shouldn't break the app
    }
}

/**
 * Log errors to file
 */
function logError($title, $message) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/error_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $title: $message" . PHP_EOL;
    
    @file_put_contents($logFile, $entry, FILE_APPEND);
    
    if (DEBUG_MODE) {
        error_log($entry);
    }
}

// Database type constants
const DB_GS = 'gameserver';
const DB_LS = 'loginserver';
