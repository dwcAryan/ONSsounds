<?php
/**
 * ONS SOUND — Customer Product Review Submission Endpoint
 * (Restricted to logged-in customers only)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Product.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . url('products.php'));
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
$reviewText = clean($_POST['review_text'] ?? '');
$redirect = !empty($_POST['redirect_url']) ? $_POST['redirect_url'] : url('products.php');

if (!verify_csrf()) {
    set_flash('error', 'Security verification failed. Please try submitting your review again.');
    header("Location: " . $redirect);
    exit;
}

// Enforce: Only logged-in customers can submit a review
if (!is_logged_in() || empty($_SESSION['user_id'])) {
    set_flash('error', 'Only logged-in customers can submit a product review. Please log in first.');
    header("Location: " . url('login.php?redirect=' . urlencode($redirect)));
    exit;
}

$userId = (int)$_SESSION['user_id'];
$name = $_SESSION['user_name'] ?? 'Verified Customer';
$email = $_SESSION['user_email'] ?? '';

// If session name or email is missing, load from database
if (empty($email) || empty($name)) {
    try {
        $db = get_db();
        $uStmt = $db->prepare("SELECT full_name, email FROM `users` WHERE id = ? LIMIT 1");
        $uStmt->execute([$userId]);
        $uRow = $uStmt->fetch();
        if ($uRow) {
            $name = !empty($uRow['full_name']) ? $uRow['full_name'] : $name;
            $email = !empty($uRow['email']) ? $uRow['email'] : $email;
        }
    } catch (Exception $e) {}
}

if ($productId <= 0) {
    set_flash('error', 'Invalid product specified.');
    header("Location: " . $redirect);
    exit;
}

try {
    $productModel = new Product();
    $res = $productModel->addReview($productId, $userId, $name, $email, $rating, $reviewText);

    if ($res['success']) {
        set_flash('success', 'Thank you! Your review has been submitted.');
    } else {
        set_flash('error', $res['error'] ?? 'Unable to save review.');
    }
} catch (Exception $e) {
    error_log("Review Submission Error: " . $e->getMessage());
    set_flash('error', 'An error occurred while saving your review. Please try again.');
}

header("Location: " . $redirect);
exit;
