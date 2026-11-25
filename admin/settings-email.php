<?php
/**
 * ONS SOUND — WordPress-Style Email & SMTP Settings with Live Tester
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Email / SMTP Settings';

$db = get_db();

function get_email_val($db, $key, $default = '') {
    $stmt = $db->prepare("SELECT setting_value FROM `site_settings` WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return ($val !== false) ? $val : $default;
}

$testResult = null;

// Handle Test Email Dispatch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test_email'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('settings-email.php'));
        exit;
    }

    $testRecipient = clean($_POST['test_recipient'] ?? '');

    if (empty($testRecipient) || !filter_var($testRecipient, FILTER_VALIDATE_EMAIL)) {
        admin_set_flash('error', 'Please enter a valid recipient email address.');
    } else {
        $testSubject = 'ONS SOUND — SMTP Configuration Test';
        $testBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 25px; border: 1px solid #dcdcde; border-radius: 8px;'>
                <h2 style='color: #2271b1; margin-top: 0;'><i style='font-style: normal;'>âœ“</i> SMTP Connection Successful!</h2>
                <p style='color: #2c3338; font-size: 15px;'>This is a live test email sent from the <strong>ONS SOUND WordPress-Style Admin Panel</strong>.</p>
                <div style='background: #f6f7f7; padding: 15px; border-radius: 4px; font-size: 13px; color: #50575e; margin: 20px 0;'>
                    <div><strong>Server Host:</strong> " . htmlspecialchars(SMTP_HOST) . "</div>
                    <div><strong>Server Port:</strong> " . htmlspecialchars(SMTP_PORT) . " (" . htmlspecialchars(SMTP_ENCRYPTION) . ")</div>
                    <div><strong>Sender Identity:</strong> " . htmlspecialchars(STORE_NAME) . " &lt;" . htmlspecialchars(STORE_EMAIL) . "&gt;</div>
                    <div><strong>Dispatched At:</strong> " . date('Y-m-d H:i:s T') . "</div>
                </div>
                <p style='color: #646970; font-size: 13px; margin: 0;'>Your automated order booking notices, OTP verification codes, and customer inquiry notifications are operational.</p>
            </div>
        ";

        $res = send_email($testRecipient, 'Store Admin', $testSubject, $testBody);
        if ($res['success']) {
            $testResult = ['success' => true, 'message' => "Success! Test email delivered to <strong>" . htmlspecialchars($testRecipient) . "</strong>."];
            log_admin_activity('SMTP Test Email', "Dispatched successful test email to $testRecipient");
        } else {
            $testResult = ['success' => false, 'message' => "Failed to deliver test email: " . htmlspecialchars($res['error'] ?? 'Unknown SMTP error')];
            log_admin_activity('SMTP Test Error', "Failed test email to $testRecipient: " . ($res['error'] ?? ''));
        }
    }
}

// Handle Settings Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_email_settings'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('settings-email.php'));
        exit;
    }

    $settings = [
        'smtp_host' => clean($_POST['smtp_host'] ?? 'smtp.gmail.com'),
        'smtp_port' => clean($_POST['smtp_port'] ?? '587'),
        'smtp_username' => clean($_POST['smtp_username'] ?? 'aryan.n.shah.04@gmail.com'),
        'smtp_password' => $_POST['smtp_password'] ?? '',
        'smtp_encryption' => clean($_POST['smtp_encryption'] ?? 'tls'),
        'mail_from_email' => clean($_POST['mail_from_email'] ?? 'aryan.n.shah.04@gmail.com'),
        'mail_from_name' => clean($_POST['mail_from_name'] ?? 'ONS SOUND')
    ];

    $checkStmt = $db->prepare("SELECT id FROM `site_settings` WHERE setting_key = ?");
    $updateStmt = $db->prepare("UPDATE `site_settings` SET setting_value = ? WHERE setting_key = ?");
    $insertStmt = $db->prepare("INSERT INTO `site_settings` (setting_key, setting_value, setting_group) VALUES (?, ?, 'email')");

    foreach ($settings as $k => $v) {
        $checkStmt->execute([$k]);
        if ($checkStmt->fetchColumn()) {
            $updateStmt->execute([$v, $k]);
        } else {
            $insertStmt->execute([$k, $v]);
        }
    }

    log_admin_activity('Update SMTP Settings', 'Updated SMTP credentials and dispatch identity');
    admin_set_flash('success', 'Email / SMTP settings saved successfully.');
    header("Location: " . admin_url('settings-email.php'));
    exit;
}

$smtpHost = get_email_val($db, 'smtp_host', defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com');
$smtpPort = get_email_val($db, 'smtp_port', defined('SMTP_PORT') ? SMTP_PORT : 587);
$smtpUser = get_email_val($db, 'smtp_username', defined('SMTP_USER') ? SMTP_USER : 'theartspace44@gmail.com');
$smtpPass = get_email_val($db, 'smtp_password', defined('SMTP_PASS') ? SMTP_PASS : '');
$smtpEnc = get_email_val($db, 'smtp_encryption', defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls');
$fromEmail = get_email_val($db, 'mail_from_email', defined('STORE_EMAIL') ? STORE_EMAIL : 'aryan.n.shah.04@gmail.com');
$fromName = get_email_val($db, 'mail_from_name', defined('STORE_NAME') ? STORE_NAME : 'ONS SOUND');

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Email & SMTP Settings</h1>
    <hr class="wp-header-end">

    <?php if ($testResult): ?>
        <div class="notice notice-<?= $testResult['success'] ? 'success' : 'error' ?> is-dismissible" style="margin-top: 15px;">
            <p><?= $testResult['message'] ?></p>
        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 25px; margin-top: 15px; align-items: flex-start; flex-wrap: wrap;">
        
        <!-- Left Column: Configuration Form (~60%) -->
        <div style="flex: 1; min-width: 450px;">
            <div class="postbox">
                <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 12px 15px;">
                    <h2 style="font-size: 14px; font-weight: 600; margin: 0;">SMTP Server Credentials</h2>
                </div>
                <div class="inside" style="padding: 15px 20px;">
                    <form method="POST" action="">
                        <?= csrf_field() ?>
                        <input type="hidden" name="save_email_settings" value="1">

                        <table class="form-table" style="margin: 0;">
                            <tr>
                                <th scope="row" style="width: 170px;"><label for="smtp_host">SMTP Host</label></th>
                                <td>
                                    <input name="smtp_host" type="text" id="smtp_host" value="<?= htmlspecialchars($smtpHost) ?>" class="regular-text" required>
                                    <p class="description">e.g. <code>smtp.gmail.com</code> or mail server domain.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="smtp_port">SMTP Port</label></th>
                                <td>
                                    <input name="smtp_port" type="number" id="smtp_port" value="<?= htmlspecialchars($smtpPort) ?>" style="width: 100px;" class="regular-text" required>
                                    <span style="font-size: 12px; color: #646970; margin-left: 10px;">Common ports: 587 (TLS), 465 (SSL), 25</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="smtp_encryption">Encryption</label></th>
                                <td>
                                    <select name="smtp_encryption" id="smtp_encryption">
                                        <option value="tls" <?= ($smtpEnc === 'tls') ? 'selected' : '' ?>>TLS (Recommended on Port 587)</option>
                                        <option value="ssl" <?= ($smtpEnc === 'ssl') ? 'selected' : '' ?>>SSL (Port 465)</option>
                                        <option value="none" <?= ($smtpEnc === 'none') ? 'selected' : '' ?>>None (Unencrypted)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="smtp_username">SMTP Username / Email</label></th>
                                <td>
                                    <input name="smtp_username" type="text" id="smtp_username" value="<?= htmlspecialchars($smtpUser) ?>" class="regular-text" style="width: 100%; max-width: 350px;" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="smtp_password">SMTP Password / App Key</label></th>
                                <td>
                                    <input name="smtp_password" type="password" id="smtp_password" value="<?= htmlspecialchars($smtpPass) ?>" class="regular-text" style="width: 100%; max-width: 350px;">
                                    <p class="description">For Gmail, use a 16-character App Password generated in Google Account Security.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mail_from_email">Sender Email Address</label></th>
                                <td>
                                    <input name="mail_from_email" type="email" id="mail_from_email" value="<?= htmlspecialchars($fromEmail) ?>" class="regular-text" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mail_from_name">Sender Display Name</label></th>
                                <td>
                                    <input name="mail_from_name" type="text" id="mail_from_name" value="<?= htmlspecialchars($fromName) ?>" class="regular-text">
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #dcdcde;">
                            <button type="submit" class="button button-primary button-large" style="padding: 6px 20px; font-weight: 600;">Save Email Settings</button>
                        </p>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Live SMTP Connection Tester (~40%) -->
        <div style="width: 340px; flex-shrink: 0;">
            <div class="postbox" style="border-top: 3px solid #2271b1;">
                <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 12px 15px;">
                    <h2 style="font-size: 14px; font-weight: 600; margin: 0;">
                        <i class="bi bi-send-check me-1"></i> Send a Test Email
                    </h2>
                </div>
                <div class="inside" style="padding: 15px 20px;">
                    <p style="font-size: 13px; color: #50575e; margin-top: 0; line-height: 1.5;">
                        Verify your PHPMailer SMTP credentials and deliverability in real-time.
                    </p>

                    <form method="POST" action="">
                        <?= csrf_field() ?>
                        <input type="hidden" name="send_test_email" value="1">

                        <div style="margin-bottom: 15px;">
                            <label for="test_recipient" style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 5px;">Recipient Email Address:</label>
                            <input type="email" name="test_recipient" id="test_recipient" value="<?= htmlspecialchars(OWNER_EMAIL) ?>" required style="width: 100%; padding: 6px 10px; font-size: 13px; border: 1px solid #8c8f94; border-radius: 4px;">
                        </div>

                        <button type="submit" class="button button-primary" style="width: 100%; padding: 6px 12px; font-weight: 600;">
                            <i class="bi bi-paperplane me-1"></i> Send Test Email Now
                        </button>
                    </form>

                    <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #eee; font-size: 12px; color: #646970; line-height: 1.5;">
                        <strong>Current Active Routing:</strong><br>
                        Store notifications & customer inquiries are delivered to <code><?= htmlspecialchars(OWNER_EMAIL) ?></code>.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
