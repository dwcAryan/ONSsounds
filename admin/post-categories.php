<?php
/**
 * ONS SOUND — WordPress-Style Blog Categories Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Blog Categories';

$db = get_db();

// Handle Edit Fetch
$editId = (int)($_GET['edit'] ?? 0);
$editCat = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM `blog_categories` WHERE id = ?");
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('post-categories.php'));
        exit;
    }

    $name = clean($_POST['name'] ?? '');
    $slug = clean($_POST['slug'] ?? '');
    $description = clean($_POST['description'] ?? '');
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
                $stmt = $db->prepare("UPDATE `blog_categories` SET name = ?, slug = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $catId]);
                log_admin_activity('Update Blog Category', "Updated category '$name'");
                admin_set_flash('success', "Category '$name' updated.");
            } else {
                $stmt = $db->prepare("INSERT INTO `blog_categories` (name, slug, description, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$name, $slug, $description]);
                log_admin_activity('Create Blog Category', "Added category '$name'");
                admin_set_flash('success', "Category '$name' created.");
            }
            header("Location: " . admin_url('post-categories.php'));
            exit;
        } catch (Exception $e) {
            admin_set_flash('error', 'Error saving category: ' . $e->getMessage());
        }
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $db->prepare("UPDATE `blog_posts` SET category_id = NULL WHERE category_id = ?")->execute([$delId]);
    $db->prepare("DELETE FROM `blog_categories` WHERE id = ?")->execute([$delId]);
    log_admin_activity('Delete Blog Category', "Deleted category #$delId");
    admin_set_flash('success', "Category deleted.");
    header("Location: " . admin_url('post-categories.php'));
    exit;
}

// Fetch categories with post count
$categories = $db->query("
    SELECT c.*, COUNT(p.id) AS post_count
    FROM `blog_categories` c
    LEFT JOIN `blog_posts` p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Blog Categories</h1>
    <hr class="wp-header-end">

    <div id="col-container" style="display: flex; gap: 30px; margin-top: 15px; align-items: flex-start;">
        
        <!-- Left Column: Add / Edit Form (~35%) -->
        <div id="col-left" style="width: 360px; flex-shrink: 0;">
            <div class="form-wrap" style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px;">
                <h2 style="font-size: 16px; font-weight: 600; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px;">
                    <?= $editCat ? 'Edit Category' : 'Add New Category' ?>
                </h2>

                <form method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_category" value="1">
                    <?php if ($editCat): ?>
                        <input type="hidden" name="category_id" value="<?= $editCat['id'] ?>">
                    <?php endif; ?>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($editCat['name'] ?? '') ?>" required style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Slug</label>
                        <input type="text" name="slug" value="<?= htmlspecialchars($editCat['slug'] ?? '') ?>" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Description</label>
                        <textarea name="description" rows="4" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;"><?= htmlspecialchars($editCat['description'] ?? '') ?></textarea>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="submit" class="button button-primary">
                            <?= $editCat ? 'Update Category' : 'Add New Category' ?>
                        </button>
                        <?php if ($editCat): ?>
                            <a href="<?= admin_url('post-categories.php') ?>" class="button button-secondary">Cancel</a>
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
                                    <strong><a href="<?= admin_url('post-categories.php?edit=' . $c['id']) ?>" class="row-title"><?= htmlspecialchars($c['name']) ?></a></strong>
                                    <div class="row-actions">
                                        <span class="edit"><a href="<?= admin_url('post-categories.php?edit=' . $c['id']) ?>">Edit</a> | </span>
                                        <span class="trash"><a href="<?= admin_url('post-categories.php?action=delete&id=' . $c['id']) ?>" onclick="return confirm('Delete this category? Posts will be unassigned.');" style="color: #d63638;">Delete</a></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($c['description'] ?: '—') ?></td>
                                <td><code><?= htmlspecialchars($c['slug']) ?></code></td>
                                <td style="text-align: center; font-weight: 600;">
                                    <?= (int)$c['post_count'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 25px; color: #646970;">No blog categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
