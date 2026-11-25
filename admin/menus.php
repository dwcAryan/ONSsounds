<?php
/**
 * ONS SOUND — WordPress-Style Navigation Menus Manager
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Menus';

$db = get_db();

// Handle Edit Fetch
$editId = (int)($_GET['edit'] ?? 0);
$editMenu = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM `navigation_menus` WHERE id = ?");
    $stmt->execute([$editId]);
    $editMenu = $stmt->fetch();
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_menu'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('menus.php'));
        exit;
    }

    $title = clean($_POST['title'] ?? '');
    $url = clean($_POST['url'] ?? '');
    $target = clean($_POST['target'] ?? '_self');
    $order = (int)($_POST['display_order'] ?? 1);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $menuId = (int)($_POST['menu_id'] ?? 0);

    if (empty($title) || empty($url)) {
        admin_set_flash('error', 'Navigation title and URL are required.');
    } else {
        try {
            if ($menuId > 0) {
                $stmt = $db->prepare("UPDATE `navigation_menus` SET title = ?, url = ?, target = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $url, $target, $order, $isActive, $menuId]);
                log_admin_activity('Update Menu Item', "Updated menu item '$title'");
                admin_set_flash('success', "Menu item updated.");
            } else {
                $stmt = $db->prepare("INSERT INTO `navigation_menus` (menu_location, title, url, target, display_order, is_active, created_at) VALUES ('primary', ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $url, $target, $order, $isActive]);
                log_admin_activity('Create Menu Item', "Added menu item '$title'");
                admin_set_flash('success', "Menu item added.");
            }
            header("Location: " . admin_url('menus.php'));
            exit;
        } catch (Exception $e) {
            admin_set_flash('error', 'Error saving menu item: ' . $e->getMessage());
        }
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $db->prepare("DELETE FROM `navigation_menus` WHERE id = ?")->execute([$delId]);
    log_admin_activity('Delete Menu Item', "Deleted menu item #$delId");
    admin_set_flash('success', "Menu item deleted.");
    header("Location: " . admin_url('menus.php'));
    exit;
}

// Fetch all menu items
$menuItems = $db->query("SELECT * FROM `navigation_menus` WHERE menu_location = 'primary' ORDER BY display_order ASC, id ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Navigation Menus</h1>
    <hr class="wp-header-end">

    <div id="col-container" style="display: flex; gap: 25px; margin-top: 15px; align-items: flex-start;">
        
        <!-- Left Column: Add / Edit Form (~35%) -->
        <div id="col-left" style="width: 340px; flex-shrink: 0;">
            <div class="form-wrap" style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px;">
                <h2 style="font-size: 16px; font-weight: 600; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px;">
                    <?= $editMenu ? 'Edit Menu Item' : 'Add Custom Link' ?>
                </h2>

                <form method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_menu" value="1">
                    <?php if ($editMenu): ?>
                        <input type="hidden" name="menu_id" value="<?= $editMenu['id'] ?>">
                    <?php endif; ?>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Navigation Label</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($editMenu['title'] ?? '') ?>" required style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;" placeholder="e.g. Products">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">URL Destination</label>
                        <input type="text" name="url" value="<?= htmlspecialchars($editMenu['url'] ?? '') ?>" required style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;" placeholder="e.g. products.php or https://...">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Open In</label>
                        <select name="target" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                            <option value="_self" <?= (($editMenu['target'] ?? '_self') === '_self') ? 'selected' : '' ?>>Same Window / Tab (_self)</option>
                            <option value="_blank" <?= (($editMenu['target'] ?? '') === '_blank') ? 'selected' : '' ?>>New Window / Tab (_blank)</option>
                        </select>
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Order / Position</label>
                        <input type="number" name="display_order" value="<?= htmlspecialchars($editMenu['display_order'] ?? '1') ?>" min="1" style="width: 100px; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= (!isset($editMenu['is_active']) || $editMenu['is_active'] == 1) ? 'checked' : '' ?>>
                            <span style="font-weight: 600;">Active in Main Header</span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="submit" class="button button-primary">
                            <?= $editMenu ? 'Update Item' : 'Add to Menu' ?>
                        </button>
                        <?php if ($editMenu): ?>
                            <a href="<?= admin_url('menus.php') ?>" class="button button-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Menu Structure Table (~65%) -->
        <div id="col-right" style="flex: 1; min-width: 0;">
            <div class="postbox">
                <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 10px 15px;">
                    <h2 style="font-weight: 600; font-size: 14px; margin: 0;">Primary Header Navigation Structure</h2>
                </div>
                <div class="inside" style="padding: 0;">
                    <table class="wp-list-table widefat fixed striped" style="border: none;">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">Order</th>
                                <th>Navigation Title</th>
                                <th>Destination URL</th>
                                <th style="width: 110px;">Target</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 90px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($menuItems)): ?>
                                <?php foreach ($menuItems as $m): ?>
                                    <tr>
                                        <td style="text-align: center; font-weight: 600;"><?= (int)$m['display_order'] ?></td>
                                        <td>
                                            <strong><a href="<?= admin_url('menus.php?edit=' . $m['id']) ?>"><?= htmlspecialchars($m['title']) ?></a></strong>
                                            <div class="row-actions">
                                                <span class="edit"><a href="<?= admin_url('menus.php?edit=' . $m['id']) ?>">Edit</a> | </span>
                                                <span class="trash"><a href="<?= admin_url('menus.php?action=delete&id=' . $m['id']) ?>" onclick="return confirm('Delete this menu item?');" style="color: #d63638;">Delete</a></span>
                                            </div>
                                        </td>
                                        <td><code><?= htmlspecialchars($m['url']) ?></code></td>
                                        <td><span style="font-size: 12px; color: #646970;"><?= htmlspecialchars($m['target']) ?></span></td>
                                        <td>
                                            <?php if (!empty($m['is_active'])): ?>
                                                <span class="badge" style="background: #007017;">Active</span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #787c82;">Hidden</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="<?= admin_url('menus.php?edit=' . $m['id']) ?>" class="button button-small" title="Edit Item"><i class="bi bi-pencil"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 25px; color: #646970;">No menu items found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
