<?php
/**
 * ONS SOUND — WordPress-Style Homepage Banners & Carousel Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Homepage Banners';

$db = get_db();

// Handle Edit Fetch
$editId = (int)($_GET['edit'] ?? 0);
$editBanner = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM `homepage_banners` WHERE id = ?");
    $stmt->execute([$editId]);
    $editBanner = $stmt->fetch();
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_banner'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('appearance-banners.php'));
        exit;
    }

    $title = clean($_POST['title'] ?? '');
    $subtitle = clean($_POST['subtitle'] ?? '');
    $buttonText = clean($_POST['button_text'] ?? 'Explore Now');
    $buttonLink = clean($_POST['button_link'] ?? 'products.php');
    $imagePath = clean($_POST['image_path'] ?? 'assets/images/banners/hero-banner.jpg');
    $displayOrder = (int)($_POST['display_order'] ?? 1);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $bannerId = (int)($_POST['banner_id'] ?? 0);

    try {
        if ($bannerId > 0) {
            $stmt = $db->prepare("
                UPDATE `homepage_banners` SET 
                    title = ?, subtitle = ?, button_text = ?, button_link = ?, 
                    image_path = ?, display_order = ?, is_active = ? 
                WHERE id = ?
            ");
            $stmt->execute([$title, $subtitle, $buttonText, $buttonLink, $imagePath, $displayOrder, $isActive, $bannerId]);
            log_admin_activity('Update Banner', "Updated banner #$bannerId ($title)");
            admin_set_flash('success', "Banner updated successfully.");
        } else {
            $stmt = $db->prepare("
                INSERT INTO `homepage_banners` (
                    section_key, title, subtitle, button_text, button_link, image_path, display_order, is_active, created_at
                ) VALUES (
                    'hero_carousel', ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");
            $stmt->execute([$title, $subtitle, $buttonText, $buttonLink, $imagePath, $displayOrder, $isActive]);
            $newId = (int)$db->lastInsertId();
            log_admin_activity('Create Banner', "Created banner #$newId ($title)");
            admin_set_flash('success', "Banner slide created successfully.");
        }
        header("Location: " . admin_url('appearance-banners.php'));
        exit;
    } catch (Exception $e) {
        admin_set_flash('error', 'Error saving banner: ' . $e->getMessage());
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $db->prepare("DELETE FROM `homepage_banners` WHERE id = ?")->execute([$delId]);
    log_admin_activity('Delete Banner', "Deleted banner #$delId");
    admin_set_flash('success', "Banner deleted.");
    header("Location: " . admin_url('appearance-banners.php'));
    exit;
}

// Fetch all banners
$banners = $db->query("SELECT * FROM `homepage_banners` ORDER BY display_order ASC, id ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Homepage Banners</h1>
    <a href="<?= url('index.php') ?>" target="_blank" class="page-title-action">
        <i class="bi bi-box-arrow-up-right me-1"></i> Preview Homepage
    </a>
    <hr class="wp-header-end">

    <div id="col-container" style="display: flex; gap: 25px; margin-top: 15px; align-items: flex-start;">
        
        <!-- Left Column: Add / Edit Banner Form (~40%) -->
        <div id="col-left" style="width: 380px; flex-shrink: 0;">
            <div class="form-wrap" style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px;">
                <h2 style="font-size: 16px; font-weight: 600; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px;">
                    <?= $editBanner ? 'Edit Banner Slide' : 'Add New Carousel Banner' ?>
                </h2>

                <form method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_banner" value="1">
                    <?php if ($editBanner): ?>
                        <input type="hidden" name="banner_id" value="<?= $editBanner['id'] ?>">
                    <?php endif; ?>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Main Headline / Title</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($editBanner['title'] ?? '') ?>" required style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;" placeholder="e.g. Next-Gen Subwoofers">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Subtitle / Tagline</label>
                        <input type="text" name="subtitle" value="<?= htmlspecialchars($editBanner['subtitle'] ?? '') ?>" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;" placeholder="e.g. Engineering Pure Precision Sound">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Button Label</label>
                        <input type="text" name="button_text" value="<?= htmlspecialchars($editBanner['button_text'] ?? 'Explore Products') ?>" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Button URL</label>
                        <input type="text" name="button_link" value="<?= htmlspecialchars($editBanner['button_link'] ?? 'products.php') ?>" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Banner Image Path / URL</label>
                        <input type="text" name="image_path" value="<?= htmlspecialchars($editBanner['image_path'] ?? 'assets/images/banners/hero-banner.jpg') ?>" required style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                        <p class="description" style="font-size: 12px; color: #646970; margin-top: 4px;">Relative path or absolute URL of banner background image.</p>
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Sort / Display Order</label>
                        <input type="number" name="display_order" value="<?= htmlspecialchars($editBanner['display_order'] ?? '1') ?>" min="1" style="width: 100px; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= (!isset($editBanner['is_active']) || $editBanner['is_active'] == 1) ? 'checked' : '' ?>>
                            <span style="font-weight: 600;">Active / Visible in Carousel</span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="submit" class="button button-primary">
                            <?= $editBanner ? 'Update Banner' : 'Add Banner Slide' ?>
                        </button>
                        <?php if ($editBanner): ?>
                            <a href="<?= admin_url('appearance-banners.php') ?>" class="button button-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Banners List Table (~60%) -->
        <div id="col-right" style="flex: 1; min-width: 0;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 120px;">Preview</th>
                        <th>Banner Details</th>
                        <th style="width: 120px;">Button</th>
                        <th style="width: 70px; text-align: center;">Order</th>
                        <th style="width: 80px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($banners)): ?>
                        <?php foreach ($banners as $b): ?>
                            <tr>
                                <td>
                                    <img src="<?= url($b['image_path']) ?>" alt="" style="width: 100px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #dcdcde;">
                                </td>
                                <td>
                                    <strong><a href="<?= admin_url('appearance-banners.php?edit=' . $b['id']) ?>"><?= htmlspecialchars($b['title'] ?: 'Banner #' . $b['id']) ?></a></strong>
                                    <?php if (!empty($b['subtitle'])): ?>
                                        <div style="font-size: 12px; color: #646970; margin-top: 2px;"><?= htmlspecialchars($b['subtitle']) ?></div>
                                    <?php endif; ?>
                                    <div class="row-actions">
                                        <span class="edit"><a href="<?= admin_url('appearance-banners.php?edit=' . $b['id']) ?>">Edit</a> | </span>
                                        <span class="trash"><a href="<?= admin_url('appearance-banners.php?action=delete&id=' . $b['id']) ?>" onclick="return confirm('Permanently delete this banner?');" style="color: #d63638;">Delete</a></span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 12px; font-weight: 600;"><?= htmlspecialchars($b['button_text']) ?></span>
                                    <div style="font-size: 11px; color: #646970;">&rarr; <?= htmlspecialchars($b['button_link']) ?></div>
                                </td>
                                <td style="text-align: center; font-weight: 600;"><?= (int)$b['display_order'] ?></td>
                                <td>
                                    <?php if (!empty($b['is_active'])): ?>
                                        <span class="badge" style="background: #007017;">Active</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #787c82;">Hidden</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 25px; color: #646970;">No banners configured yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
