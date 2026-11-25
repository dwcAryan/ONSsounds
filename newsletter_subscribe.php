<?php
/**
 * ONS SOUND - Newsletter Subscription Handler
 */
require_once __DIR__ . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

if (!verify_csrf()) {
    json_response(['success' => false, 'message' => 'CSRF verification failed.'], 403);
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    json_response(['success' => false, 'message' => 'Please provide a valid email address.'], 422);
}

try {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE subscribed_at = NOW()");
    $stmt->execute([$email]);
    json_response(['success' => true, 'message' => 'Thank you for subscribing to ONS SOUND!']);
} catch (PDOException $e) {
    error_log("Newsletter error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to subscribe. Please try again.'], 500);
}
