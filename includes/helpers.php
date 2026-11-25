<?php
/**
 * ONS SOUND - Application Helpers & Security Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Escape HTML output to prevent XSS
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Sanitize text input
 */
function clean(?string $value): string {
    return trim(strip_tags($value ?? ''));
}

/**
 * Generate absolute URL for a path
 */
function url(string $path = ''): string {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Generate absolute URL for an asset
 */
function asset(string $path): string {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Format currency consistent with Indian Rupee display (e.g. ₹2,000.00 or ₹17.99)
 */
function format_price(float|int|string $amount): string {
    $num = (float)$amount;
    return CURRENCY_SYMBOL . number_format($num, 2);
}

/**
 * CSRF Protection
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool {
    $token = $token ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash messaging system
 */
function set_flash(string $type, string $message): void {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type][] = $message;
}

function get_flash(string $type): array {
    if (isset($_SESSION['flash'][$type])) {
        $messages = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $messages;
    }
    return [];
}

function has_flash(string $type): bool {
    return !empty($_SESSION['flash'][$type]);
}

/**
 * Redirect helper
 */
function redirect(string $path): void {
    $target = str_starts_with($path, 'http') ? $path : url($path);
    header("Location: " . $target);
    exit;
}

/**
 * User Authentication Helpers
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }

    static $cachedUser = null;
    if ($cachedUser !== null && $cachedUser['id'] == $_SESSION['user_id']) {
        return $cachedUser;
    }

    try {
        $db = get_db();
        $stmt = $db->prepare("SELECT id, role, full_name, email, phone, status, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $cachedUser = $stmt->fetch() ?: null;
        return $cachedUser;
    } catch (PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return null;
    }
}

function is_admin(): bool {
    return is_logged_in() && (($_SESSION['user_role'] ?? '') === 'admin');
}

function require_login(string $returnUrl = ''): void {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        $redirectUrl = 'login.php' . ($returnUrl ? '?redirect=' . urlencode($returnUrl) : '');
        redirect($redirectUrl);
    }
}

if (!function_exists('require_admin')) {
    function require_admin(): void {
        if (!empty($_SESSION['admin_id']) && ($_SESSION['admin_role'] ?? '') === 'admin') {
            return;
        }
        if (!is_admin()) {
            if (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false) {
                header("Location: " . admin_url('login.php'));
                exit;
            }
            set_flash('error', 'Administrator access required.');
            redirect('login.php');
        }
    }
}

/**
 * Guest Session ID tracking for cart and wishlist
 */
function get_guest_session_id(): string {
    if (empty($_SESSION['guest_cart_id'])) {
        $_SESSION['guest_cart_id'] = 'guest_' . bin2hex(random_bytes(16));
    }
    return $_SESSION['guest_cart_id'];
}

/**
 * Cart Item Count for Header Badge
 */
function get_cart_count(): int {
    if (!is_logged_in()) {
        return 0;
    }
    try {
        $db = get_db();
        $stmt = $db->prepare("SELECT COALESCE(SUM(ci.quantity), 0) AS total 
                              FROM cart_items ci 
                              JOIN carts c ON ci.cart_id = c.id 
                              WHERE c.user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Wishlist Item Count for Header Badge
 */
function get_wishlist_count(): int {
    if (!is_logged_in()) {
        return 0;
    }
    try {
        $db = get_db();
        $stmt = $db->prepare("SELECT COUNT(*) AS total FROM wishlists WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get Site Setting from Database
 */
function get_setting(string $key, string $default = ''): string {
    static $settingsCache = null;
    if ($settingsCache === null) {
        try {
            $db = get_db();
            $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
            $settingsCache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        } catch (PDOException $e) {
            $settingsCache = [];
        }
    }
    return $settingsCache[$key] ?? $default;
}

/**
 * Render Star Rating HTML
 * Matching layout from reference: solid gold stars (#ffb800), solid gray stars (#d1d5db),
 * and gradient half-stars for float ratings (e.g. 4.5, 3.5, etc.)
 */
function render_stars(float|int $rating, int $reviewsCount = -1): string {
    $num = (float)$rating;
    $rounded = round($num * 2) / 2; // rounds to nearest 0.5
    $full = (int)floor($rounded);
    $half = ($rounded - $full) >= 0.5 ? 1 : 0;
    $empty = max(0, 5 - ($full + $half));

    $html = '<div class="star-rating d-inline-flex align-items-center" aria-label="' . number_format($num, 1) . ' out of 5 stars">';
    $html .= '<span class="star-pill-box">';
    for ($i = 0; $i < $full; $i++) {
        $html .= '<i class="bi bi-star-fill star-gold"></i>';
    }
    if ($half) {
        $html .= '<i class="bi bi-star-fill star-half"></i>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<i class="bi bi-star-fill star-gray"></i>';
    }
    $html .= '</span>';
    if ($reviewsCount >= 0) {
        $html .= '<span class="ms-1 review-count text-muted">(' . (int)$reviewsCount . ')</span>';
    }
    $html .= '</div>';

    return $html;
}

/**
 * JSON Response Helper for AJAX
 */
function json_response(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Dynamic Data for Top Search Drawer
 */
function get_search_drawer_data(): array {
    try {
        $db = get_db();
        // 5 Quick links (Speaker 1, Speaker 2, Speaker 3, Speaker 4, Speaker 5)
        $stmtQuick = $db->query("SELECT name, slug FROM products WHERE is_active = 1 ORDER BY id ASC LIMIT 5");
        $quickLinks = $stmtQuick->fetchAll() ?: [];

        // 6 Inspiration Products matching screenshot grid
        $stmtProds = $db->query("SELECT id, name, slug, price, rating, reviews_count, main_image FROM products WHERE is_active = 1 ORDER BY is_featured DESC, id ASC LIMIT 6");
        $inspirationProducts = $stmtProds->fetchAll() ?: [];

        return [
            'quick_links' => $quickLinks,
            'inspiration_products' => $inspirationProducts
        ];
    } catch (PDOException $e) {
        return ['quick_links' => [], 'inspiration_products' => []];
    }
}

/**
 * Dynamic Recommended Products for Index Offer Popup
 */
function get_offer_popup_data(): array {
    try {
        $db = get_db();
        // Ensure discount settings exist dynamically in site_settings
        $check = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'offer_coupon_code' LIMIT 1")->fetchColumn();
        if ($check === false) {
            $stmtInsert = $db->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES 
                ('offer_coupon_code', 'OFF20%', 'promotions'),
                ('offer_discount_percent', '20', 'promotions')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmtInsert->execute();
        }

        // Fetch recommended products matching screenshot (Speaker 2, Speaker 4, Speaker 5)
        $stmt = $db->query("SELECT id, name, slug, price, rating, reviews_count, main_image FROM products WHERE is_active = 1 ORDER BY rating DESC, price ASC LIMIT 3");
        $recommended = $stmt->fetchAll() ?: [];
        return [
            'coupon_code' => get_setting('offer_coupon_code', 'OFF20%'),
            'discount_percent' => get_setting('offer_discount_percent', '20'),
            'products' => $recommended
        ];
    } catch (PDOException $e) {
        return [
            'coupon_code' => 'OFF20%',
            'discount_percent' => '20',
            'products' => []
        ];
    }
}

// Autoload vendor libraries if present (PHPMailer, etc.)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * Send email using official PHPMailer with SMTP credentials with graceful fallback
 */
function send_email(string $to, string $subject, string $body, bool $isHtml = false): bool {
    $host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
    $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
    $user = defined('SMTP_USER') ? SMTP_USER : '';
    $pass = defined('SMTP_PASS') ? SMTP_PASS : '';
    $from = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'theartspace44@gmail.com';
    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'ONS SOUND';

    // If PHPMailer is available and SMTP credentials exist, use PHPMailer
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer') && !empty($user) && !empty($pass)) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;
            
            $encryption = defined('SMTP_ENCRYPTION') ? strtolower(SMTP_ENCRYPTION) : 'tls';
            if ($encryption === 'ssl' || $port === 465) {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port = $port;
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 10;

            // Sender & Recipient
            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($from, $fromName);

            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $body;
            if ($isHtml) {
                $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $body)));
            }

            return $mail->send();
        } catch (\Throwable $e) {
            error_log("PHPMailer send failed: " . $e->getMessage());
        }
    }

    // Native mail() fallback
    $headers = "From: " . $from . "\r\nReply-To: " . $from . "\r\nX-Mailer: PHP/" . phpversion();
    if ($isHtml) {
        $headers .= "\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8";
    }
    return @mail($to, $subject, $body, $headers);
}
