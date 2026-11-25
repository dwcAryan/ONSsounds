<?php
/**
 * ONS SOUND — WordPress-Style Homepage Sections Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Homepage Sections';

$db = get_db();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sections'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('appearance-home.php'));
        exit;
    }

    if (!empty($_POST['sections']) && is_array($_POST['sections'])) {
        $stmt = $db->prepare("
            UPDATE `homepage_sections` 
            SET title = ?, subtitle = ?, content = ?, is_active = ?, updated_at = NOW() 
            WHERE section_key = ?
        ");

        foreach ($_POST['sections'] as $key => $sec) {
            $title = clean($sec['title'] ?? '');
            $subtitle = clean($sec['subtitle'] ?? '');
            $content = clean($sec['content'] ?? '');
            $isActive = isset($sec['is_active']) ? 1 : 0;
            $stmt->execute([$title, $subtitle, $content, $isActive, $key]);
        }

        log_admin_activity('Update Homepage Sections', 'Updated homepage layout sections');
        admin_set_flash('success', 'Homepage sections updated successfully.');
        header("Location: " . admin_url('appearance-home.php'));
        exit;
    }
}

// Fetch All Sections
$sections = $db->query("SELECT * FROM `homepage_sections` ORDER BY display_order ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Homepage Sections</h1>
    <a href="<?= url('index.php') ?>" target="_blank" class="page-title-action">
        <i class="bi bi-box-arrow-up-right me-1"></i> Preview Homepage
    </a>
    <hr class="wp-header-end">

    <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="save_sections" value="1">

        <p style="color: #646970; font-size: 13px; margin-bottom: 20px;">
            Customize headlines, subheadings, and visibility of main landing page sections.
        </p>

        <?php if (!empty($sections)): ?>
            <div style="display: flex; flex-direction: column; gap: 20px; max-width: 900px;">
                <?php foreach ($sections as $s): ?>
                    <div class="postbox" style="margin: 0;">
                        <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
                            <h2 style="font-weight: 600; font-size: 14px; margin: 0;">
                                <i class="bi bi-layout-text-window me-2"></i> Section: <?= htmlspecialchars(ucwords(str_replace('_', ' ', $s['section_key']))) ?>
                            </h2>
                            <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px; font-weight: normal;">
                                <input type="checkbox" name="sections[<?= htmlspecialchars($s['section_key']) ?>][is_active]" value="1" <?= (!empty($s['is_active'])) ? 'checked' : '' ?>>
                                <span>Visible on Homepage</span>
                            </label>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <table class="form-table" style="margin: 0;">
                                <tr>
                                    <th style="width: 160px;"><label>Heading / Title</label></th>
                                    <td>
                                        <input type="text" name="sections[<?= htmlspecialchars($s['section_key']) ?>][title]" value="<?= htmlspecialchars($s['title'] ?? '') ?>" class="large-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th><label>Subheading / Tagline</label></th>
                                    <td>
                                        <input type="text" name="sections[<?= htmlspecialchars($s['section_key']) ?>][subtitle]" value="<?= htmlspecialchars($s['subtitle'] ?? '') ?>" class="large-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th><label>Content / Descriptions</label></th>
                                    <td>
                                        <textarea name="sections[<?= htmlspecialchars($s['section_key']) ?>][content]" rows="3" class="large-text" placeholder="Optional description or features summary"><?= htmlspecialchars($s['content'] ?? '') ?></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 25px;">
                <button type="submit" class="button button-primary button-large" style="padding: 6px 20px; font-weight: 600;">
                    Save Homepage Sections
                </button>
            </div>
        <?php else: ?>
            <p style="color: #646970;">No homepage sections configured.</p>
        <?php endif; ?>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
