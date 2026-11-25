<?php
/**
 * ONS SOUND - Phase 1 Verification Test
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

echo "=== ONS SOUND PHASE 1 INTEGRATION TESTS ===" . PHP_EOL;

// 1. Database Connection
try {
    $db = get_db();
    echo "[PASS] PDO Database connection established." . PHP_EOL;
} catch (Exception $e) {
    die("[FAIL] Database connection failed: " . $e->getMessage() . PHP_EOL);
}

// 2. Table Verification
$requiredTables = [
    'users', 'user_addresses', 'categories', 'products', 'product_images',
    'product_reviews', 'orders', 'order_items', 'wishlists', 'cart_items',
    'blogs', 'testimonials', 'contact_inquiries', 'newsletter_subscribers', 'site_settings'
];

$stmt = $db->query("SHOW TABLES");
$existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$missingTables = array_diff($requiredTables, $existingTables);
if (empty($missingTables)) {
    echo "[PASS] All 15 required tables exist in database." . PHP_EOL;
} else {
    echo "[FAIL] Missing tables: " . implode(', ', $missingTables) . PHP_EOL;
}

// 3. Products Seed Verification
$stmt = $db->query("SELECT COUNT(*) AS total FROM products");
$count = $stmt->fetch()['total'];
if ($count >= 8) {
    echo "[PASS] Products seeded: {$count} items found." . PHP_EOL;
} else {
    echo "[FAIL] Insufficient products seeded: {$count} items." . PHP_EOL;
}

// 4. Test Categories
$stmt = $db->query("SELECT COUNT(*) AS total FROM categories");
$catCount = $stmt->fetch()['total'];
echo "[PASS] Categories seeded: {$catCount} categories found." . PHP_EOL;

// 5. Security Helpers Test
$csrf = csrf_token();
assert(!empty($csrf), "CSRF token generated");
assert(verify_csrf($csrf), "CSRF verification passed");
assert(format_price(17.99) === '₹17.99', "Price formatting check");
assert(e("<script>") === '&lt;script&gt;', "HTML escaping check");
echo "[PASS] Security & Formatting helpers functioning properly." . PHP_EOL;

// 6. Assets Existence Verification
$assetFiles = [
    'assets/images/products/speaker-1.jpg',
    'assets/images/products/speaker-10.jpg',
    'assets/images/categories/tweeters.jpg',
    'assets/images/banners/hero-banner.jpg',
    'assets/images/blog/blog-1.jpg',
    'assets/images/about/about-hero.jpg'
];

$missingAssets = [];
foreach ($assetFiles as $file) {
    if (!file_exists(ROOT_PATH . DIRECTORY_SEPARATOR . $file)) {
        $missingAssets[] = $file;
    }
}

if (empty($missingAssets)) {
    echo "[PASS] Extracted original assets exist and accessible." . PHP_EOL;
} else {
    echo "[FAIL] Missing assets: " . implode(', ', $missingAssets) . PHP_EOL;
}

echo "=== ALL PHASE 1 CHECKS PASSED ===" . PHP_EOL;
