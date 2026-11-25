<?php
/**
 * ONS SOUND — Admin Authentication & Security Guard
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!ob_get_level()) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if current session belongs to an authenticated administrator
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_id']) && ($_SESSION['admin_role'] ?? '') === 'admin';
}

if (!function_exists('require_admin')) {
    /**
     * Enforce admin authentication guard; redirects unauthorized access to login
     */
    function require_admin(string $redirectPath = ''): void {
        if (!is_admin_logged_in()) {
            $currentUri = $_SERVER['REQUEST_URI'] ?? 'index.php';
            $loginUrl = admin_url('login.php');
            if (!empty($redirectPath)) {
                $loginUrl .= '?redirect=' . urlencode($redirectPath);
            } else {
                $loginUrl .= '?redirect=' . urlencode($currentUri);
            }
            header("Location: " . $loginUrl);
            exit;
        }
    }
}

/**
 * Get current admin user record from MySQL
 */
function current_admin(): ?array {
    if (!is_admin_logged_in()) {
        return null;
    }
    try {
        $db = get_db();
        $stmt = $db->prepare("SELECT id, role, full_name, email, phone, status, created_at FROM `users` WHERE id = ? AND role = 'admin' AND status = 'active' LIMIT 1");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Log administrative activity to database for accountability & audit trail
 */
function log_admin_activity(string $action, ?string $details = null): void {
    try {
        $db = get_db();
        $userId = $_SESSION['admin_id'] ?? null;
        $userName = $_SESSION['admin_name'] ?? 'System';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $db->prepare("INSERT INTO `admin_activity_logs` (user_id, user_name, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $userName, clean($action), clean($details), $ip]);
    } catch (Exception $e) {
        error_log("Failed logging admin activity: " . $e->getMessage());
    }
}

/**
 * Admin URL generator
 */
function admin_url(string $path = ''): string {
    return BASE_URL . 'admin/' . ltrim($path, '/');
}

/**
 * Set flash alert in admin session
 */
function admin_set_flash(string $type, string $message): void {
    $_SESSION['admin_flash'][$type][] = $message;
}

/**
 * Retrieve and clear flash alerts in admin session
 */
function admin_get_flash(string $type): array {
    if (isset($_SESSION['admin_flash'][$type])) {
        $msgs = $_SESSION['admin_flash'][$type];
        unset($_SESSION['admin_flash'][$type]);
        return $msgs;
    }
    return [];
}
