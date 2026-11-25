<?php
/**
 * ONS SOUND - Idempotent Database Installation & Migration Script
 * Usage:
 *   CLI: php database/install.php [--seed]
 *   Web: http://localhost/ONSsounds/database/install.php?seed=1
 */

require_once __DIR__ . '/../config/config.php';

$isCli = (php_sapi_name() === 'cli');
$doSeed = $isCli ? in_array('--seed', $argv ?? []) : isset($_GET['seed']);

if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "=== ONS SOUND DATABASE INSTALLER ===" . PHP_EOL;

try {
    // 1. Initial connection to MySQL server
    $dsnServer = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
    $pdoServer = new PDO($dsnServer, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 2. Ensure Database exists
    $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");
    echo "[OK] Database `" . DB_NAME . "` confirmed/created." . PHP_EOL;

    // 3. Connect directly to database
    $dsnDb = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsnDb, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // 4. Execute Schema
    $schemaFile = __DIR__ . '/schema_v2.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Schema file not found at: {$schemaFile}");
    }

    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    echo "[OK] Schema executed successfully (20 tables configured)." . PHP_EOL;

    // 5. Seed Data Execution (if requested or if users table is empty)
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users");
    $userCount = (int)$stmt->fetch()['total'];

    if ($doSeed || $userCount === 0) {
        $seedFile = __DIR__ . '/seed_v2.sql';
        if (file_exists($seedFile)) {
            $seedSql = file_get_contents($seedFile);
            $pdo->exec($seedSql);
            echo "[OK] Master seed data inserted successfully." . PHP_EOL;
        }
    } else {
        echo "[INFO] Existing data detected. Seed skipped (use --seed to force re-seed)." . PHP_EOL;
    }

    // 6. Verify All 24 Required Tables
    $requiredTables = [
        'users', 'addresses', 'categories', 'products', 'product_images',
        'product_specifications', 'wishlists', 'carts', 'cart_items', 'orders',
        'order_items', 'blog_posts', 'blog_categories', 'product_reviews',
        'contact_enquiries', 'newsletter_subscribers', 'homepage_sections',
        'homepage_banners', 'site_settings', 'password_resets',
        'coupons', 'navigation_menus', 'order_status_history', 'admin_activity_logs'
    ];

    $stmt = $pdo->query("SHOW TABLES");
    $actualTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $missing = array_diff($requiredTables, $actualTables);

    echo PHP_EOL . "=== TABLE VERIFICATION REPORT ===" . PHP_EOL;
    echo "Required Tables: " . count($requiredTables) . PHP_EOL;
    echo "Found Tables: " . count($actualTables) . PHP_EOL;

    if (empty($missing)) {
        echo "[SUCCESS] ALL 24 REQUIRED TABLES VERIFIED IN DATABASE:" . PHP_EOL;
        foreach ($requiredTables as $t) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$t}`");
            $rows = $countStmt->fetchColumn();
            echo "  - {$t} ({$rows} records)" . PHP_EOL;
        }
    } else {
        echo "[ERROR] Missing tables: " . implode(', ', $missing) . PHP_EOL;
        exit(1);
    }

    echo PHP_EOL . "=== INSTALLATION COMPLETED SUCCESSFULLY ===" . PHP_EOL;

} catch (Exception $e) {
    echo "[FATAL ERROR] Installation failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
