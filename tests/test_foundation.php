<?php
/**
 * ONS SOUND - Foundation & Database Verification Suite
 */

echo "==========================================================" . PHP_EOL;
echo "   ONS SOUND: FOUNDATION & DATABASE TEST SUITE" . PHP_EOL;
echo "==========================================================" . PHP_EOL . PHP_EOL;

$passCount = 0;
$failCount = 0;

function run_test(string $name, callable $testFunc): void {
    global $passCount, $failCount;
    try {
        $result = $testFunc();
        if ($result === false) {
            echo "[FAIL] " . $name . PHP_EOL;
            $failCount++;
        } else {
            echo "[PASS] " . $name . PHP_EOL;
            $passCount++;
        }
    } catch (Throwable $e) {
        echo "[FAIL] " . $name . ": " . $e->getMessage() . PHP_EOL;
        $failCount++;
    }
}

// 1. PHP Configuration
run_test("PHP Version is 8.0+", function() {
    return version_compare(PHP_VERSION, '8.0.0', '>=');
});

run_test("PDO MySQL Extension is Loaded", function() {
    return extension_loaded('pdo') && extension_loaded('pdo_mysql');
});

// 2. Load Core Config & Helpers
run_test("Load config/config.php and environment", function() {
    require_once __DIR__ . '/../config/config.php';
    return defined('BASE_URL') && defined('DB_NAME') && (DB_NAME === 'onssound_db');
});

run_test("Load includes/helpers.php", function() {
    require_once __DIR__ . '/../includes/helpers.php';
    return function_exists('e') && function_exists('csrf_token') && function_exists('format_price');
});

// 3. Database Connection
run_test("PDO Database Connection", function() {
    $db = get_db();
    return ($db instanceof PDO);
});

// 4. Verification of all 20 required tables
$requiredTables = [
    'users',
    'addresses',
    'categories',
    'products',
    'product_images',
    'product_specifications',
    'wishlists',
    'carts',
    'cart_items',
    'orders',
    'order_items',
    'blog_posts',
    'blog_categories',
    'product_reviews',
    'contact_enquiries',
    'newsletter_subscribers',
    'homepage_sections',
    'homepage_banners',
    'site_settings',
    'password_resets'
];

run_test("Verify exactly 20 required tables exist in onssound_db", function() use ($requiredTables) {
    $db = get_db();
    $stmt = $db->query("SHOW TABLES");
    $actual = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $missing = array_diff($requiredTables, $actual);
    if (!empty($missing)) {
        throw new Exception("Missing tables: " . implode(', ', $missing));
    }
    return count($actual) >= 20;
});

// 5. Session Management Verification
run_test("Session Handling & Hardening", function() {
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }
    $_SESSION['test_var'] = 'ons_active';
    $guestCart = get_guest_session_id();
    $csrf = csrf_token();
    return ($_SESSION['test_var'] === 'ons_active') && !empty($guestCart) && verify_csrf($csrf);
});

// 6. MVC Models Verification
run_test("Model: Product catalog query with specs & images", function() {
    require_once __DIR__ . '/../models/Product.php';
    $prodModel = new Product();
    $products = $prodModel->getCatalog();
    if (empty($products)) {
        throw new Exception("No products returned.");
    }
    $product10 = $prodModel->getBySlug('speaker-10');
    if (!$product10) {
        throw new Exception("Speaker 10 not found.");
    }
    $specs = $prodModel->getSpecifications($product10['id']);
    if (count($specs) < 10) {
        throw new Exception("Speaker 10 specifications incomplete: found " . count($specs));
    }
    return true;
});

run_test("Model: Category list retrieval", function() {
    require_once __DIR__ . '/../models/Category.php';
    $catModel = new Category();
    $cats = $catModel->getActive();
    return count($cats) === 6;
});

run_test("Model: Cart creation and item addition", function() {
    require_once __DIR__ . '/../models/Cart.php';
    $cartModel = new Cart();
    $testSession = 'test_sess_' . bin2hex(random_bytes(8));
    $cartId = $cartModel->getActiveCartId(null, $testSession);
    $added = $cartModel->addItem($cartId, 2, 2); // 2 units of Speaker 10
    $count = $cartModel->getItemCount($cartId);
    $subtotal = $cartModel->getSubtotal($cartId);
    $cartModel->clearCart($cartId); // Clean up
    return ($cartId > 0) && $added && ($count === 2) && ($subtotal > 0);
});

run_test("Model: Order booking transactional simulation", function() {
    require_once __DIR__ . '/../models/Order.php';
    $orderModel = new Order();
    $items = [
        ['product_id' => 2, 'name' => 'Speaker 10', 'sku' => 'ONS-SPK-010', 'price' => 17.99, 'quantity' => 1, 'main_image' => 'assets/images/products/speaker-10.jpg']
    ];
    $customer = [
        'name' => 'Automated Test User',
        'email' => 'test@example.com',
        'phone' => '+91 9999999999',
        'address' => 'Test Sound Lab, Ajmer, Rajasthan',
        'notes' => 'Test booking run'
    ];
    $booking = $orderModel->createBooking($customer, $items);
    if (empty($booking['order_number'])) {
        throw new Exception("Failed to generate order booking.");
    }
    $retrieved = $orderModel->getByOrderNumber($booking['order_number']);
    // Clean up test order
    $db = get_db();
    $db->prepare("DELETE FROM `orders` WHERE id = ?")->execute([$booking['order_id']]);
    return ($retrieved !== null && count($retrieved['items']) === 1);
});

run_test("Frontend Includes & Component Compilation", function() {
    require_once __DIR__ . '/../includes/banner-component.php';
    // Capture component rendering output without echoing to test stream
    ob_start();
    require __DIR__ . '/../includes/ticker.php';
    $tickerOutput = ob_get_clean();
    return str_contains($tickerOutput, 'ticker-bar');
});

echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "RESULTS: {$passCount} PASSED, {$failCount} FAILED." . PHP_EOL;
echo "==========================================================" . PHP_EOL;

if ($failCount > 0) {
    exit(1);
}
