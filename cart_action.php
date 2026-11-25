<?php
/**
 * ONS SOUND - AJAX & Direct Cart Actions Handler
 * (Restricted to logged-in customers only)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Cart.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
          (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
          (isset($_POST['action']) && isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'multipart/form-data'));

// Enforce login for cart actions
if (!is_logged_in() || empty($_SESSION['user_id'])) {
    if ($isAjax) {
        json_response([
            'success' => false,
            'require_login' => true,
            'message' => 'Please log in to add products to your cart.'
        ], 401);
    }
    set_flash('error', 'Please log in to add products to your cart.');
    redirect('login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER'] ?? 'products.php'));
}

$action = clean($_POST['action'] ?? $_GET['action'] ?? '');
$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

$cartModel = new Cart();
$userId = (int)$_SESSION['user_id'];
$cartId = $cartModel->getActiveCartId($userId, null);

if ($action === 'add_to_cart') {
    if ($productId <= 0) {
        if ($isAjax) json_response(['success' => false, 'message' => 'Invalid product.'], 400);
        set_flash('error', 'Invalid product.');
        redirect('products.php');
    }

    $res = $cartModel->addItem($cartId, $productId, $quantity);
    $cartCount = $cartModel->getItemCount($cartId);

    if ($isAjax) {
        json_response([
            'success' => $res['success'],
            'message' => $res['message'],
            'cart_count' => $cartCount
        ], $res['success'] ? 200 : 400);
    }

    if ($res['success']) {
        set_flash('success', $res['message']);
    } else {
        set_flash('error', $res['message']);
    }
    redirect('cart.php');
}

if ($action === 'update_qty') {
    $res = $cartModel->updateQuantity($cartId, $productId, $quantity);
    $cartCount = $cartModel->getItemCount($cartId);
    $subtotal = $cartModel->getSubtotal($cartId);

    if ($isAjax) {
        json_response([
            'success' => $res['success'],
            'message' => $res['message'],
            'cart_count' => $cartCount,
            'subtotal' => format_price($subtotal)
        ]);
    }
    redirect('cart.php');
}

if ($action === 'remove') {
    $cartModel->removeItem($cartId, $productId);
    $cartCount = $cartModel->getItemCount($cartId);

    if ($isAjax) {
        json_response([
            'success' => true,
            'cart_count' => $cartCount
        ]);
    }
    redirect('cart.php');
}

if ($isAjax) {
    json_response(['success' => false, 'message' => 'Unknown action.'], 400);
}
redirect('products.php');
