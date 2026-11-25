<?php
/**
 * ONS SOUND — WordPress-Style Footer Settings
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Footer Settings';

$db = get_db();

// Helper to get or insert setting
function get_site_setting($db, $key, $default = '') {
    $stmt = $db->prepare("SELECT setting_value FROM `site_settings` WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return ($val !== false) ? $val : $default;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_footer'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('appearance-footer.php'));
        exit;
    }

    $settings = [
        'footer_about' => $_POST['footer_about'] ?? '',
        'footer_store_address' => clean($_POST['footer_store_address'] ?? ''),
        'footer_store_phone' => clean($_POST['footer_store_phone'] ?? ''),
        'footer_store_email' => clean($_POST['footer_store_email'] ?? ''),
        'footer_store_hours' => clean($_POST['footer_store_hours'] ?? ''),
        'footer_social_facebook' => clean($_POST['footer_social_facebook'] ?? ''),
        'footer_social_twitter' => clean($_POST['footer_social_twitter'] ?? ''),
        'footer_social_instagram' => clean($_POST['footer_social_instagram'] ?? ''),
        'footer_social_pinterest' => clean($_POST['footer_social_pinterest'] ?? ''),
        'footer_copyright' => clean($_POST['footer_copyright'] ?? '')
    ];

    $checkStmt = $db->prepare("SELECT id FROM `site_settings` WHERE setting_key = ?");
    $updateStmt = $db->prepare("UPDATE `site_settings` SET setting_value = ? WHERE setting_key = ?");
    $insertStmt = $db->prepare("INSERT INTO `site_settings` (setting_key, setting_value, setting_group) VALUES (?, ?, 'footer')");

    foreach ($settings as $k => $v) {
        $checkStmt->execute([$k]);
        if ($checkStmt->fetchColumn()) {
            $updateStmt->execute([$v, $k]);
        } else {
            $insertStmt->execute([$k, $v]);
        }
    }

    log_admin_activity('Update Footer Settings', 'Updated footer text, address, and social links');
    admin_set_flash('success', 'Footer settings saved successfully.');
    header("Location: " . admin_url('appearance-footer.php'));
    exit;
}

$footerAbout = get_site_setting($db, 'footer_about', 'ONS SOUND provides professional-grade audio drivers, woofers, tweeters, and complete speaker systems designed for acoustic brilliance.');
$footerAddress = get_site_setting($db, 'footer_store_address', 'Ajmer, Rajasthan, India');
$footerPhone = get_site_setting($db, 'footer_store_phone', '+91 7976824255');
$footerEmail = get_site_setting($db, 'footer_store_email', STORE_EMAIL);
$footerHours = get_site_setting($db, 'footer_store_hours', 'Our store has re-opened for shopping, exchange Every day 11am to 7pm');
$socialFb = get_site_setting($db, 'footer_social_facebook', 'https://facebook.com');
$socialX = get_site_setting($db, 'footer_social_twitter', 'https://twitter.com');
$socialInsta = get_site_setting($db, 'footer_social_instagram', 'https://instagram.com');
$socialPin = get_site_setting($db, 'footer_social_pinterest', 'https://pinterest.com');
$copyright = get_site_setting($db, 'footer_copyright', '© 2026 ONS SOUND. All rights reserved.');

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Footer Settings</h1>
    <a href="<?= url('index.php') ?>" target="_blank" class="page-title-action">
        <i class="bi bi-box-arrow-up-right me-1"></i> View Footer on Site
    </a>
    <hr class="wp-header-end">

    <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="save_footer" value="1">

        <div style="max-width: 860px;">
            <!-- About Store Column -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 10px 15px;">
                    <h2 style="font-weight: 600; font-size: 14px; margin: 0;">Store Branding & Description</h2>
                </div>
                <div class="inside" style="padding: 15px;">
                    <table class="form-table" style="margin: 0;">
                        <tr>
                            <th style="width: 180px;"><label for="footer_about">Brand Overview Text</label></th>
                            <td>
                                <textarea name="footer_about" id="footer_about" rows="3" class="large-text"><?= htmlspecialchars($footerAbout) ?></textarea>
                                <p class="description">Displayed in the first column of the footer under the ONS SOUND logo.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="footer_copyright">Copyright Notice</label></th>
                            <td>
                                <input type="text" name="footer_copyright" id="footer_copyright" value="<?= htmlspecialchars($copyright) ?>" class="regular-text" style="width: 100%;">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Contact & Hours -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 10px 15px;">
                    <h2 style="font-weight: 600; font-size: 14px; margin: 0;">Contact Information & Store Hours</h2>
                </div>
                <div class="inside" style="padding: 15px;">
                    <table class="form-table" style="margin: 0;">
                        <tr>
                            <th style="width: 180px;"><label for="footer_store_address">Showroom Address</label></th>
                            <td>
                                <input type="text" name="footer_store_address" id="footer_store_address" value="<?= htmlspecialchars($footerAddress) ?>" class="large-text">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="footer_store_phone">Customer Service Phone</label></th>
                            <td>
                                <input type="text" name="footer_store_phone" id="footer_store_phone" value="<?= htmlspecialchars($footerPhone) ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="footer_store_email">Store Email</label></th>
                            <td>
                                <input type="email" name="footer_store_email" id="footer_store_email" value="<?= htmlspecialchars($footerEmail) ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="footer_store_hours">Opening Hours</label></th>
                            <td>
                                <input type="text" name="footer_store_hours" id="footer_store_hours" value="<?= htmlspecialchars($footerHours) ?>" class="large-text">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Social Links -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 10px 15px;">
                    <h2 style="font-weight: 600; font-size: 14px; margin: 0;">Social Media Profiles</h2>
                </div>
                <div class="inside" style="padding: 15px;">
                    <table class="form-table" style="margin: 0;">
                        <tr>
                            <th style="width: 180px;"><label for="footer_social_facebook"><i class="bi bi-facebook me-1"></i> Facebook URL</label></th>
                            <td>
                                <input type="text" name="footer_social_facebook" id="footer_social_facebook" value="<?= htmlspecialchars($socialFb) ?>" class="large-text">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="footer_social_twitter"><i class="bi bi-twitter-x me-1"></i> Twitter / X URL</label></th>
                            <td>
                                <input type="text" name="footer_social_twitter" id="footer_social_twitter" value="<?= htmlspecialchars($socialX) ?>" class="large-text">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="footer_social_instagram"><i class="bi bi-instagram me-1"></i> Instagram URL</label></th>
                            <td>
                                <input type="text" name="footer_social_instagram" id="footer_social_instagram" value="<?= htmlspecialchars($socialInsta) ?>" class="large-text">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="footer_social_pinterest"><i class="bi bi-pinterest me-1"></i> Pinterest URL</label></th>
                            <td>
                                <input type="text" name="footer_social_pinterest" id="footer_social_pinterest" value="<?= htmlspecialchars($socialPin) ?>" class="large-text">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <button type="submit" class="button button-primary button-large" style="padding: 6px 22px; font-weight: 600;">
                Save Footer Settings
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
