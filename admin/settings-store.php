<?php
/**
 * ONS SOUND — WordPress/WooCommerce-Style Store Settings
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Store Settings';

$db = get_db();

function get_store_val($db, $key, $default = '') {
    $stmt = $db->prepare("SELECT setting_value FROM `site_settings` WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return ($val !== false) ? $val : $default;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_store'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('settings-store.php'));
        exit;
    }

    $settings = [
        'store_name' => clean($_POST['store_name'] ?? 'ONS SOUND'),
        'store_legal_name' => clean($_POST['store_legal_name'] ?? 'ONS Sound Acoustic Systems Pvt. Ltd.'),
        'store_address' => clean($_POST['store_address'] ?? 'Ajmer, Rajasthan, India'),
        'store_phone' => clean($_POST['store_phone'] ?? '+91 7976824255'),
        'store_support_email' => clean($_POST['store_support_email'] ?? 'aryan.n.shah.04@gmail.com'),
        'store_shipping_fee' => (float)($_POST['store_shipping_fee'] ?? 0.00),
        'store_low_stock_limit' => (int)($_POST['store_low_stock_limit'] ?? 5),
        'store_order_prefix' => clean($_POST['store_order_prefix'] ?? 'ORD-')
    ];

    $checkStmt = $db->prepare("SELECT id FROM `site_settings` WHERE setting_key = ?");
    $updateStmt = $db->prepare("UPDATE `site_settings` SET setting_value = ? WHERE setting_key = ?");
    $insertStmt = $db->prepare("INSERT INTO `site_settings` (setting_key, setting_value, setting_group) VALUES (?, ?, 'store')");

    foreach ($settings as $k => $v) {
        $checkStmt->execute([$k]);
        if ($checkStmt->fetchColumn()) {
            $updateStmt->execute([$v, $k]);
        } else {
            $insertStmt->execute([$k, $v]);
        }
    }

    log_admin_activity('Update Store Settings', 'Updated store address, inventory thresholds, and support channels');
    admin_set_flash('success', 'Store settings updated successfully.');
    header("Location: " . admin_url('settings-store.php'));
    exit;
}

$storeName = get_store_val($db, 'store_name', 'ONS SOUND');
$storeLegal = get_store_val($db, 'store_legal_name', 'ONS Sound Acoustic Systems Pvt. Ltd.');
$storeAddress = get_store_val($db, 'store_address', 'Ajmer, Rajasthan, India');
$storePhone = get_store_val($db, 'store_phone', '+91 7976824255');
$storeEmail = get_store_val($db, 'store_support_email', STORE_EMAIL);
$shippingFee = get_store_val($db, 'store_shipping_fee', '0.00');
$lowStockLimit = get_store_val($db, 'store_low_stock_limit', '5');
$orderPrefix = get_store_val($db, 'store_order_prefix', 'ORD-');

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Store Settings</h1>
    <hr class="wp-header-end">

    <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="save_store" value="1">

        <table class="form-table" style="max-width: 800px;">
            <tr>
                <th scope="row"><label for="store_name">Store Name</label></th>
                <td>
                    <input name="store_name" type="text" id="store_name" value="<?= htmlspecialchars($storeName) ?>" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="store_legal_name">Legal / Registered Entity</label></th>
                <td>
                    <input name="store_legal_name" type="text" id="store_legal_name" value="<?= htmlspecialchars($storeLegal) ?>" class="regular-text" style="width: 100%;">
                    <p class="description">Used on official order receipts, GST invoices, and delivery notes.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="store_address">Physical Store Address</label></th>
                <td>
                    <textarea name="store_address" id="store_address" rows="3" class="large-text"><?= htmlspecialchars($storeAddress) ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="store_phone">Customer Service Telephone</label></th>
                <td>
                    <input name="store_phone" type="text" id="store_phone" value="<?= htmlspecialchars($storePhone) ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="store_support_email">Order Support Email</label></th>
                <td>
                    <input name="store_support_email" type="email" id="store_support_email" value="<?= htmlspecialchars($storeEmail) ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="store_shipping_fee">Standard Delivery Fee (₹)</label></th>
                <td>
                    <input name="store_shipping_fee" type="number" step="0.01" min="0" id="store_shipping_fee" value="<?= htmlspecialchars($shippingFee) ?>" style="width: 120px;" class="regular-text">
                    <p class="description">Set to 0.00 for Free Delivery.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="store_low_stock_limit">Low Stock Alert Threshold</label></th>
                <td>
                    <input name="store_low_stock_limit" type="number" min="0" id="store_low_stock_limit" value="<?= htmlspecialchars($lowStockLimit) ?>" style="width: 100px;" class="regular-text">
                    <p class="description">Products at or below this quantity will trigger a warning on the dashboard overview.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="store_order_prefix">Order Number Prefix</label></th>
                <td>
                    <input name="store_order_prefix" type="text" id="store_order_prefix" value="<?= htmlspecialchars($orderPrefix) ?>" style="width: 120px;" class="regular-text">
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary button-large" style="padding: 6px 20px; font-weight: 600;">Save Store Settings</button>
        </p>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
