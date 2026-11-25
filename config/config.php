<?php
/**
 * ONS SOUND - Core Configuration & Environment Loader
 */

// Function to safely load environment variables from .env file
function load_env(string $path): array {
    $env = [];
    if (!file_exists($path)) {
        return $env;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Strip surrounding quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            $env[$key] = $value;
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }
    return $env;
}

// Load .env
$envVars = load_env(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

// Base Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config');
define('INCLUDES_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'includes');
define('MODELS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'models');
define('ASSETS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'assets');
define('UPLOADS_PATH', ASSETS_PATH . DIRECTORY_SEPARATOR . 'uploads');

// App Environment & Debugging
$appEnv = $envVars['APP_ENV'] ?? 'development';
$appDebug = filter_var($envVars['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN);

define('APP_ENV', $appEnv);
define('APP_DEBUG', $appDebug);

if ($appDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . DIRECTORY_SEPARATOR . 'error.log');

// Timezone
$timezone = $envVars['APP_TIMEZONE'] ?? 'Asia/Kolkata';
date_default_timezone_set($timezone);

// Output Buffering (ensures safe redirects & clean header handling)
if (!ob_get_level()) {
    ob_start();
}

// Hardened Session Management
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $lifetime = (int)($envVars['SESSION_LIFETIME'] ?? 7200);
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string)$lifetime);
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
} elseif (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Database Credentials from Environment
define('DB_HOST', $envVars['DB_HOST'] ?? 'localhost');
define('DB_PORT', $envVars['DB_PORT'] ?? '3306');
define('DB_NAME', $envVars['DB_NAME'] ?? 'onssound_db');
define('DB_USER', $envVars['DB_USER'] ?? 'root');
define('DB_PASS', $envVars['DB_PASS'] ?? '');
define('DB_CHARSET', $envVars['DB_CHARSET'] ?? 'utf8mb4');

// SMTP Credentials
define('SMTP_HOST', $envVars['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', (int)($envVars['SMTP_PORT'] ?? 587));
define('SMTP_USER', $envVars['SMTP_USER'] ?? 'theartspace44@gmail.com');
define('SMTP_PASS', $envVars['SMTP_PASS'] ?? 'dgqbgjjlmxiilrfu');
define('SMTP_ENCRYPTION', $envVars['SMTP_ENCRYPTION'] ?? 'tls');
define('SMTP_FROM_EMAIL', $envVars['SMTP_FROM_EMAIL'] ?? 'theartspace44@gmail.com');
define('SMTP_FROM_NAME', $envVars['SMTP_FROM_NAME'] ?? 'ONS SOUND');

// Auto-detect Base URL or use configured APP_URL
if (!empty($envVars['APP_URL'])) {
    define('BASE_URL', rtrim($envVars['APP_URL'], '/') . '/');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    $currentDir = str_replace('\\', '/', ROOT_PATH);
    $relativeDir = trim(str_replace($docRoot, '', $currentDir), '/');

    if (!empty($relativeDir)) {
        define('BASE_URL', rtrim($protocol . $host . '/' . $relativeDir, '/') . '/');
    } else {
        define('BASE_URL', rtrim($protocol . $host, '/') . '/');
    }
}

// Brand Constants
define('SITE_NAME', $envVars['APP_NAME'] ?? 'ONS SOUND');
define('SITE_TAGLINE', 'Hear every word with precision sound');
define('STORE_PHONE', '+91 7976824255');
define('STORE_EMAIL', $envVars['STORE_EMAIL'] ?? 'aryan.n.shah.04@gmail.com');
define('OWNER_EMAIL', 'aryan.n.shah.04@gmail.com');
define('STORE_ADDRESS', 'Ajmer, Rajasthan, India');
define('STORE_HOURS', 'Our store has re-opened for shopping, exchange Every day 11am to 7pm');
define('CURRENCY_SYMBOL', '₹');
