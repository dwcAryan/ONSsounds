<?php
/**
 * ONS SOUND — WordPress-Style General Settings
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'General Settings';

$db = get_db();

function get_setting_val($db, $key, $default = '') {
    $stmt = $db->prepare("SELECT setting_value FROM `site_settings` WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return ($val !== false) ? $val : $default;
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('settings.php'));
        exit;
    }

    $settings = [
        'site_title' => clean($_POST['site_title'] ?? 'ONS SOUND'),
        'site_tagline' => clean($_POST['site_tagline'] ?? 'Professional Audio Drivers & Sound Systems'),
        'admin_email' => clean($_POST['admin_email'] ?? 'aryan.n.shah.04@gmail.com'),
        'currency_symbol' => clean($_POST['currency_symbol'] ?? '₹'),
        'currency_code' => clean($_POST['currency_code'] ?? 'INR'),
        'timezone' => clean($_POST['timezone'] ?? 'Asia/Kolkata'),
        'date_format' => clean($_POST['date_format'] ?? 'Y-m-d')
    ];

    $checkStmt = $db->prepare("SELECT id FROM `site_settings` WHERE setting_key = ?");
    $updateStmt = $db->prepare("UPDATE `site_settings` SET setting_value = ? WHERE setting_key = ?");
    $insertStmt = $db->prepare("INSERT INTO `site_settings` (setting_key, setting_value, setting_group) VALUES (?, ?, 'general')");

    foreach ($settings as $k => $v) {
        $checkStmt->execute([$k]);
        if ($checkStmt->fetchColumn()) {
            $updateStmt->execute([$v, $k]);
        } else {
            $insertStmt->execute([$k, $v]);
        }
    }

    log_admin_activity('Update General Settings', 'Updated site title, tagline, and locale settings');
    admin_set_flash('success', 'General settings saved successfully.');
    header("Location: " . admin_url('settings.php'));
    exit;
}

$siteTitle = get_setting_val($db, 'site_title', 'ONS SOUND');
$siteTagline = get_setting_val($db, 'site_tagline', 'Professional Audio Drivers & Sound Systems');
$adminEmail = get_setting_val($db, 'admin_email', 'aryan.n.shah.04@gmail.com');
$currSymbol = get_setting_val($db, 'currency_symbol', '₹');
$currCode = get_setting_val($db, 'currency_code', 'INR');
$timezone = get_setting_val($db, 'timezone', 'Asia/Kolkata');
$dateFormat = get_setting_val($db, 'date_format', 'F j, Y');

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">General Settings</h1>
    <hr class="wp-header-end">

    <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="save_settings" value="1">

        <table class="form-table" style="max-width: 800px;">
            <tr>
                <th scope="row"><label for="site_title">Site Title</label></th>
                <td>
                    <input name="site_title" type="text" id="site_title" value="<?= htmlspecialchars($siteTitle) ?>" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="site_tagline">Tagline</label></th>
                <td>
                    <input name="site_tagline" type="text" id="site_tagline" value="<?= htmlspecialchars($siteTagline) ?>" class="regular-text" style="width: 100%;">
                    <p class="description">In a few words, explain what this site is about.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="admin_email">Administration Email Address</label></th>
                <td>
                    <input name="admin_email" type="email" id="admin_email" value="<?= htmlspecialchars($adminEmail) ?>" class="regular-text" required>
                    <p class="description">This address is used for admin notifications regarding orders and customer enquiries.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="currency_symbol">Currency Symbol</label></th>
                <td>
                    <input name="currency_symbol" type="text" id="currency_symbol" value="<?= htmlspecialchars($currSymbol) ?>" style="width: 80px;" class="regular-text">
                    <span style="margin-left: 10px; color: #646970;">Code:</span>
                    <input name="currency_code" type="text" id="currency_code" value="<?= htmlspecialchars($currCode) ?>" style="width: 80px;" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="timezone">Timezone</label></th>
                <td>
                    <select name="timezone" id="timezone">
                        <option value="Asia/Kolkata" <?= ($timezone === 'Asia/Kolkata') ? 'selected' : '' ?>>Asia/Kolkata (IST +05:30)</option>
                        <option value="UTC" <?= ($timezone === 'UTC') ? 'selected' : '' ?>>UTC (Universal Time Coordinated)</option>
                        <option value="America/New_York" <?= ($timezone === 'America/New_York') ? 'selected' : '' ?>>America/New_York (EST)</option>
                        <option value="Europe/London" <?= ($timezone === 'Europe/London') ? 'selected' : '' ?>>Europe/London (GMT)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">Date Format</th>
                <td>
                    <fieldset>
                        <label><input type="radio" name="date_format" value="F j, Y" <?= ($dateFormat === 'F j, Y') ? 'checked' : '' ?>> <span><?= date('F j, Y') ?></span> (<code>F j, Y</code>)</label><br>
                        <label><input type="radio" name="date_format" value="Y-m-d" <?= ($dateFormat === 'Y-m-d') ? 'checked' : '' ?>> <span><?= date('Y-m-d') ?></span> (<code>Y-m-d</code>)</label><br>
                        <label><input type="radio" name="date_format" value="d/m/Y" <?= ($dateFormat === 'd/m/Y') ? 'checked' : '' ?>> <span><?= date('d/m/Y') ?></span> (<code>d/m/Y</code>)</label>
                    </fieldset>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary button-large" style="padding: 6px 20px; font-weight: 600;">Save Changes</button>
        </p>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
