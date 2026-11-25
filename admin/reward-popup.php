<?php
/**
 * ONS SOUND — Reward / Exit-Intent Popup Settings
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Reward Popup';

$db = get_db();

// Helper to get or insert setting
function get_popup_setting($db, $key, $default = '') {
    $stmt = $db->prepare("SELECT setting_value FROM `site_settings` WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return ($val !== false) ? $val : $default;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_reward'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('reward-popup.php'));
        exit;
    }

    $settings = [
        'reward_popup_enabled' => isset($_POST['reward_popup_enabled']) ? '1' : '0',
        'reward_popup_heading' => clean($_POST['reward_popup_heading'] ?? 'Exclusive Offer'),
        'reward_popup_text' => clean($_POST['reward_popup_text'] ?? 'Get 20% off your audio order today!'),
        'reward_popup_coupon' => strtoupper(trim(clean($_POST['reward_popup_coupon'] ?? 'OFF20%'))),
        'reward_popup_products' => !empty($_POST['reward_popup_products']) ? implode(',', array_map('intval', $_POST['reward_popup_products'])) : ''
    ];

    $checkStmt = $db->prepare("SELECT id FROM `site_settings` WHERE setting_key = ?");
    $updateStmt = $db->prepare("UPDATE `site_settings` SET setting_value = ? WHERE setting_key = ?");
    $insertStmt = $db->prepare("INSERT INTO `site_settings` (setting_key, setting_value, setting_group) VALUES (?, ?, 'marketing')");

    foreach ($settings as $k => $v) {
        $checkStmt->execute([$k]);
        if ($checkStmt->fetchColumn()) {
            $updateStmt->execute([$v, $k]);
        } else {
            $insertStmt->execute([$k, $v]);
        }
    }

    log_admin_activity('Update Reward Popup', 'Updated reward discount popup configuration');
    admin_set_flash('success', 'Reward popup settings saved.');
    header("Location: " . admin_url('reward-popup.php'));
    exit;
}

$enabled = get_popup_setting($db, 'reward_popup_enabled', '1');
$heading = get_popup_setting($db, 'reward_popup_heading', 'Exclusive Offer');
$text = get_popup_setting($db, 'reward_popup_text', 'Get 20% off your audio order today!');
$couponCode = get_popup_setting($db, 'reward_popup_coupon', 'OFF20%');
$selectedProdIds = array_filter(array_map('intval', explode(',', get_popup_setting($db, 'reward_popup_products', '1,2,3'))));

// Fetch all active products
$allProducts = $db->query("SELECT id, name, price, main_image FROM `products` WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Reward & Discount Popup Settings</h1>
    <a href="<?= url('index.php') ?>" target="_blank" class="page-title-action">
        <i class="bi bi-box-arrow-up-right me-1"></i> Preview on Website
    </a>
    <hr class="wp-header-end">

    <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="save_reward" value="1">

        <div style="max-width: 820px;">
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 10px 15px;">
                    <h2 style="font-weight: 600; font-size: 14px; margin: 0;">Exit-Intent / Reward Popup Configuration</h2>
                </div>
                <div class="inside" style="padding: 15px;">
                    <table class="form-table" style="margin: 0;">
                        <tr>
                            <th style="width: 200px;"><label>Popup Status</label></th>
                            <td>
                                <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                                    <input type="checkbox" name="reward_popup_enabled" value="1" <?= ($enabled === '1') ? 'checked' : '' ?>>
                                    <span>Enable Reward Popup for website visitors</span>
                                </label>
                                <p class="description">Displays with leave-intent detection on desktop and respects user dismissal session.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="reward_popup_heading">Popup Title / Headline</label></th>
                            <td>
                                <input type="text" name="reward_popup_heading" id="reward_popup_heading" value="<?= htmlspecialchars($heading) ?>" class="large-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="reward_popup_text">Subtitle / Offer Description</label></th>
                            <td>
                                <textarea name="reward_popup_text" id="reward_popup_text" rows="3" class="large-text"><?= htmlspecialchars($text) ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="reward_popup_coupon">Discount Coupon Code</label></th>
                            <td>
                                <input type="text" name="reward_popup_coupon" id="reward_popup_coupon" value="<?= htmlspecialchars($couponCode) ?>" class="regular-text" style="font-weight: 700; letter-spacing: 1px;" required>
                                <p class="description">Visitors can copy this code directly with one click to grab the discount.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label>Recommended Products</label></th>
                            <td>
                                <p class="description" style="margin-top: 0; margin-bottom: 8px;">Select products featured inside the reward popup recommendation grid:</p>
                                <div style="max-height: 220px; overflow-y: auto; border: 1px solid #c3c4c7; border-radius: 4px; padding: 10px; background: #fafafa;">
                                    <?php foreach ($allProducts as $p): ?>
                                        <label style="display: flex; align-items: center; gap: 10px; padding: 4px 0; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" name="reward_popup_products[]" value="<?= $p['id'] ?>" <?= in_array($p['id'], $selectedProdIds) ? 'checked' : '' ?>>
                                            <img src="<?= url($p['main_image']) ?>" alt="" style="width: 24px; height: 24px; object-fit: contain;">
                                            <span><strong><?= htmlspecialchars($p['name']) ?></strong> — <?= format_price($p['price']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <button type="submit" class="button button-primary button-large" style="padding: 6px 22px; font-weight: 600;">
                Save Reward Popup Settings
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
