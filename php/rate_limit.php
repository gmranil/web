<?php
// ============================================
// RATE LIMITING
// ============================================

/**
 * Check rate limit for an action
 * 
 * @param string $action Action identifier (e.g., 'login', 'register')
 * @param string $identifier User identifier (IP, username, etc.)
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int, 'wait_seconds' => int]
 */
function checkRateLimit($action, $identifier, $maxAttempts = 5, $timeWindow = 300) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $key = "rate_limit_{$action}_{$identifier}";
    $now = time();
    
    // Initialize if not exists
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => $now,
            'reset_time' => $now + $timeWindow
        ];
    }
    
    $data = $_SESSION[$key];
    
    // Check if time window expired
    if ($now >= $data['reset_time']) {
        // Reset
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => $now,
            'reset_time' => $now + $timeWindow
        ];
        $data = $_SESSION[$key];
    }
    
    // Check if limit exceeded
    if ($data['attempts'] >= $maxAttempts) {
        return [
            'allowed' => false,
            'remaining' => 0,
            'reset_time' => $data['reset_time'],
            'wait_seconds' => $data['reset_time'] - $now
        ];
    }
    
    return [
        'allowed' => true,
        'remaining' => $maxAttempts - $data['attempts'],
        'reset_time' => $data['reset_time'],
        'wait_seconds' => 0
    ];
}

/**
 * Record a rate limit attempt
 */
function recordRateLimitAttempt($action, $identifier) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $key = "rate_limit_{$action}_{$identifier}";
    
    if (isset($_SESSION[$key])) {
        $_SESSION[$key]['attempts']++;
    }
}

/**
 * Reset rate limit for an action
 */
function resetRateLimit($action, $identifier) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $key = "rate_limit_{$action}_{$identifier}";
    unset($_SESSION[$key]);
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}
