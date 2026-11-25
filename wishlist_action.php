<?php
/**
 * ONS SOUND - AJAX Wishlist Actions Handler
 * (Restricted to logged-in customers only)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Wishlist.php';

// Enforce login for wishlist operations
if (!is_logged_in() || empty($_SESSION['user_id'])) {
    json_response([
        'success' => false,
        'require_login' => true,
        'message' => 'Please log in to save items to your wishlist.'
    ], 401);
}

$productId = (int)($_POST['product_id'] ?? 0);
$action = clean($_POST['action'] ?? '');

if ($productId <= 0 || $action !== 'toggle_wishlist') {
    json_response(['success' => false, 'message' => 'Invalid product.'], 400);
}

$wishlistModel = new Wishlist();
$userId = (int)$_SESSION['user_id'];

$result = $wishlistModel->toggle($productId, $userId, null);

json_response([
    'success' => true,
    'in_wishlist' => $result['in_wishlist'],
    'wishlist_count' => $result['count'],
    'message' => $result['in_wishlist'] ? 'Added to your wishlist!' : 'Removed from your wishlist.'
]);
