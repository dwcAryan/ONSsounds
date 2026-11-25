<?php
/**
 * ONS SOUND — WordPress-Style Product Categories Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Product Categories';

$db = get_db();

// Handle Edit Fetch
$editId = (int)($_GET['edit'] ?? 0);
$editCat = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM `categories` WHERE id = ?");
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch();
}

// Handle Form Submission (Add or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('categories.php'));
        exit;
    }

    $name = clean($_POST['name'] ?? '');
    $slug = clean($_POST['slug'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $image = clean($_POST['image'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $catId = (int)($_POST['category_id'] ?? 0);

    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = trim($slug, '-');
    }

    if (empty($name)) {
        admin_set_flash('error', 'Category name is required.');
    } else {
        try {
            if ($catId > 0) {
                // Update
                $stmt = $db->prepare("UPDATE `categories` SET name = ?, slug = ?, description = ?, image = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $image, $isActive, $catId]);
                log_admin_activity('Update Category', "Updated category #$catId ($name)");
                admin_set_flash('success', "Category '$name' updated successfully.");
            } else {
                // Insert
                $stmt = $db->prepare("INSERT INTO `categories` (name, slug, description, image, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $slug, $description, $image, $isActive]);
                $newId = (int)$db->lastInsertId();
                log_admin_activity('Create Category', "Created category #$newId ($name)");
                admin_set_flash('success', "Category '$name' added successfully.");
            }
            header("Location: " . admin_url('categories.php'));
            exit;
        } catch (Exception $e) {
            admin_set_flash('error', 'Error saving category: ' . $e->getMessage());
        }
    }
}

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT name FROM `categories` WHERE id = ?");
    $stmt->execute([$delId]);
    $catName = $stmt->fetchColumn();

    if ($catName) {
        // Disassociate products from this category
        $db->prepare("UPDATE `products` SET category_id = NULL WHERE category_id = ?")->execute([$delId]);
        $db->prepare("DELETE FROM `categories` WHERE id = ?")->execute([$delId]);

        log_admin_activity('Delete Category', "Deleted category #$delId ($catName)");
        admin_set_flash('success', "Category '$catName' deleted.");
    }
    header("Location: " . admin_url('categories.php'));
    exit;
}

// Fetch all categories with product count
$categories = $db->query("
    SELECT c.*, COUNT(p.id) AS product_count 
    FROM `categories` c
    LEFT JOIN `products` p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Product Categories</h1>
    <hr class="wp-header-end">

    <div id="col-container" style="display: flex; gap: 30px; margin-top: 15px; align-items: flex-start;">
        
        <!-- Left Column: Add / Edit Form (~35%) -->
        <div id="col-left" style="width: 360px; flex-shrink: 0;">
            <div class="form-wrap" style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px;">
                <h2 style="font-size: 16px; font-weight: 600; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px;">
                    <?= $editCat ? 'Edit Category' : 'Add New Category' ?>
                </h2>

                <form method="POST" action="<?= admin_url('categories.php') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_category" value="1">
                    <?php if ($editCat): ?>
                        <input type="hidden" name="category_id" value="<?= $editCat['id'] ?>">
                    <?php endif; ?>

                    <div class="form-field form-required" style="margin-bottom: 16px;">
                        <label for="tag-name" style="display: block; font-weight: 600; margin-bottom: 5px;">Name</label>
                        <input name="name" id="tag-name" type="text" value="<?= htmlspecialchars($editCat['name'] ?? '') ?>" size="40" required style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                        <p class="description" style="font-size: 12px; color: #646970; margin-top: 4px;">The name is how it appears on your site.</p>
                    </div>

                    <div class="form-field" style="margin-bottom: 16px;">
                        <label for="tag-slug" style="display: block; font-weight: 600; margin-bottom: 5px;">Slug</label>
                        <input name="slug" id="tag-slug" type="text" value="<?= htmlspecialchars($editCat['slug'] ?? '') ?>" size="40" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                        <p class="description" style="font-size: 12px; color: #646970; margin-top: 4px;">The “slug” is the URL-friendly version of the name.</p>
                    </div>

                    <div class="form-field" style="margin-bottom: 16px;">
                        <label for="tag-description" style="display: block; font-weight: 600; margin-bottom: 5px;">Description</label>
                        <textarea name="description" id="tag-description" rows="4" cols="40" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;"><?= htmlspecialchars($editCat['description'] ?? '') ?></textarea>
                        <p class="description" style="font-size: 12px; color: #646970; margin-top: 4px;">The description is not prominent by default; however, some themes may show it.</p>
                    </div>

                    <div class="form-field" style="margin-bottom: 16px;">
                        <label for="tag-image" style="display: block; font-weight: 600; margin-bottom: 5px;">Image URL / Path</label>
                        <input name="image" id="tag-image" type="text" value="<?= htmlspecialchars($editCat['image'] ?? '') ?>" placeholder="assets/images/categories/speakers.jpg" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= (!isset($editCat['is_active']) || $editCat['is_active'] == 1) ? 'checked' : '' ?>>
                            <span style="font-weight: 600;">Active Category</span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="submit" class="button button-primary">
                            <?= $editCat ? 'Update Category' : 'Add New Category' ?>
                        </button>
                        <?php if ($editCat): ?>
                            <a href="<?= admin_url('categories.php') ?>" class="button button-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: List Table (~65%) -->
        <div id="col-right" style="flex: 1; min-width: 0;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Slug</th>
                        <th style="width: 80px; text-align: center;">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($c['image'])): ?>
                                        <img src="<?= url($c['image']) ?>" alt="" style="width: 36px; height: 36px; object-fit: cover; border-radius: 3px; border: 1px solid #dcdcde;">
                                    <?php else: ?>
                                        <div style="width: 36px; height: 36px; background: #e2e4e7; border-radius: 3px; display: flex; align-items: center; justify-content: center; color: #8c8f94;">
                                            <i class="bi bi-folder"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><a href="<?= admin_url('categories.php?edit=' . $c['id']) ?>" class="row-title"><?= htmlspecialchars($c['name']) ?></a></strong>
                                    <?php if (!$c['is_active']): ?>
                                        <span class="badge" style="background:#787c82; font-size: 10px; margin-left: 5px;">Inactive</span>
                                    <?php endif; ?>
                                    <div class="row-actions">
                                        <span class="edit"><a href="<?= admin_url('categories.php?edit=' . $c['id']) ?>">Edit</a> | </span>
                                        <span class="trash"><a href="<?= admin_url('categories.php?action=delete&id=' . $c['id']) ?>" onclick="return confirm('Are you sure you want to delete this category? Associated products will be unassigned.');" class="submitdelete" style="color: #d63638;">Delete</a> | </span>
                                        <span class="view"><a href="<?= url('products.php?cat=' . $c['id']) ?>" target="_blank">View</a></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($c['description'] ?: '—') ?></td>
                                <td><code><?= htmlspecialchars($c['slug']) ?></code></td>
                                <td style="text-align: center;">
                                    <a href="<?= admin_url('products.php?cat=' . $c['id']) ?>" style="font-weight: 600; text-decoration: none;">
                                        <?= (int)$c['product_count'] ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #646970;">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
