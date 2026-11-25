<?php
/**
 * ONS SOUND — WordPress-Style Add / Edit Blog Post
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Edit Post';

$db = get_db();
$postId = (int)($_GET['id'] ?? 0);
$isEdit = ($postId > 0);
$post = null;

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM `blog_posts` WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if (!$post) {
        admin_set_flash('error', 'Post not found.');
        header("Location: " . admin_url('posts.php'));
        exit;
    }
}

// Fetch Blog Categories
$categories = $db->query("SELECT * FROM `blog_categories` ORDER BY name ASC")->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $title = clean($_POST['title'] ?? '');
    $slug = clean($_POST['slug'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $excerpt = clean($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $author = clean($_POST['author'] ?? 'ONS Sound Team');
    $image = clean($_POST['image'] ?? 'assets/images/blog/blog-1.jpg');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');
    }

    if (empty($title)) {
        admin_set_flash('error', 'Post title is required.');
    } else {
        try {
            if ($isEdit) {
                $stmt = $db->prepare("
                    UPDATE `blog_posts` SET 
                        category_id = ?, title = ?, slug = ?, excerpt = ?, 
                        content = ?, image = ?, author = ?, is_published = ?,
                        updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$categoryId, $title, $slug, $excerpt, $content, $image, $author, $isPublished, $postId]);
                log_admin_activity('Update Blog Post', "Updated post #$postId ($title)");
                admin_set_flash('success', "Post updated successfully.");
                $targetId = $postId;
            } else {
                $stmt = $db->prepare("
                    INSERT INTO `blog_posts` (
                        category_id, title, slug, excerpt, content, 
                        image, author, is_published, published_at, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, 
                        ?, ?, ?, NOW(), NOW(), NOW()
                    )
                ");
                $stmt->execute([$categoryId, $title, $slug, $excerpt, $content, $image, $author, $isPublished]);
                $targetId = (int)$db->lastInsertId();
                log_admin_activity('Create Blog Post', "Created post #$targetId ($title)");
                admin_set_flash('success', "Post published successfully.");
            }

            header("Location: " . admin_url('post-edit.php?id=' . $targetId));
            exit;
        } catch (Exception $e) {
            admin_set_flash('error', 'Error saving post: ' . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?= $isEdit ? 'Edit Post' : 'Add New Post' ?>
    </h1>
    <a href="<?= admin_url('posts.php') ?>" class="page-title-action">Back to Posts</a>
    <hr class="wp-header-end">

    <form method="POST" action="" id="post">
        <?= csrf_field() ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2" style="display: flex; gap: 20px; align-items: flex-start;">
                
                <!-- Main Column (~70%) -->
                <div id="post-body-content" style="flex: 1; min-width: 0;">
                    
                    <!-- Title Wrap -->
                    <div id="titlediv" style="margin-bottom: 20px;">
                        <input type="text" name="title" size="30" value="<?= htmlspecialchars($post['title'] ?? '') ?>" id="title" placeholder="Add title" required style="width: 100%; font-size: 20px; font-weight: 600; padding: 10px 14px; border: 1px solid #8c8f94; border-radius: 4px; box-sizing: border-box;">
                        <div style="margin-top: 6px; font-size: 13px; color: #646970;">
                            <strong>Permalink:</strong> 
                            <?= url('blog-single.php?slug=') ?><input type="text" name="slug" value="<?= htmlspecialchars($post['slug'] ?? '') ?>" placeholder="auto-generated-slug" style="font-size: 12px; padding: 2px 6px; border: 1px solid #dcdcde; border-radius: 3px; width: 220px;">
                        </div>
                    </div>

                    <!-- Excerpt Metabox -->
                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header">
                            <h2>Excerpt</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <textarea name="excerpt" rows="3" style="width: 100%; border: 1px solid #8c8f94; border-radius: 4px; padding: 10px; font-size: 13px; box-sizing: border-box;" placeholder="Brief summary of the article"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                            <p class="description" style="margin-top: 5px;">Excerpts are optional hand-crafted summaries of your content that can be used in your theme.</p>
                        </div>
                    </div>

                    <!-- Content Editor Metabox -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2>Post Content</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <textarea name="content" rows="18" style="width: 100%; border: 1px solid #8c8f94; border-radius: 4px; padding: 12px; font-size: 14px; line-height: 1.6; box-sizing: border-box;" placeholder="Write your full article body, guide, or technical acoustic documentation..."><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                        </div>
                    </div>

                </div>

                <!-- Right Column (Sidebar ~30%) -->
                <div id="postbox-container-1" style="width: 300px; flex-shrink: 0;">
                    
                    <!-- Publish Box -->
                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7;">
                            <h2>Publish</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <div style="margin-bottom: 12px; font-size: 13px;">
                                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="checkbox" name="is_published" value="1" <?= (!isset($post['is_published']) || $post['is_published'] == 1) ? 'checked' : '' ?>>
                                    <span><strong>Publish immediately</strong></span>
                                </label>
                            </div>
                            <div style="margin-bottom: 14px; font-size: 13px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 4px;">Author:</label>
                                <input type="text" name="author" value="<?= htmlspecialchars($post['author'] ?? 'ONS Sound Team') ?>" style="width: 100%; padding: 4px 8px; font-size: 13px;">
                            </div>
                            <?php if ($isEdit && !empty($post['created_at'])): ?>
                                <div style="font-size: 12px; color: #646970; margin-bottom: 14px; border-top: 1px solid #eee; padding-top: 10px;">
                                    Published on: <strong><?= date('M j, Y @ H:i', strtotime($post['created_at'])) ?></strong>
                                </div>
                            <?php endif; ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #dcdcde; padding-top: 12px;">
                                <?php if ($isEdit): ?>
                                    <a href="<?= admin_url('posts.php?action=delete&id=' . $postId) ?>" onclick="return confirm('Delete this post?');" style="color: #d63638; text-decoration: none; font-size: 13px;">Move to Trash</a>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>
                                <button type="submit" class="button button-primary button-large" style="padding: 6px 18px; font-weight: 600;">
                                    <?= $isEdit ? 'Update Post' : 'Publish' ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Box -->
                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header">
                            <h2>Categories</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <select name="category_id" style="width: 100%; padding: 6px 10px; font-size: 13px;">
                                <option value="">— Uncategorized —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (($post['category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div style="margin-top: 10px; font-size: 12px;">
                                <a href="<?= admin_url('post-categories.php') ?>" target="_blank" style="text-decoration: none;">+ Manage Categories</a>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image Box -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2>Featured Image</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <?php $currImg = $post['image'] ?? 'assets/images/blog/blog-1.jpg'; ?>
                            <div style="text-align: center; margin-bottom: 10px; background: #fafafa; border: 1px solid #e2e4e7; border-radius: 4px; padding: 6px;">
                                <img src="<?= url($currImg) ?>" alt="" style="max-height: 120px; max-width: 100%; object-fit: cover;">
                            </div>
                            <input type="text" name="image" value="<?= htmlspecialchars($currImg) ?>" style="width: 100%; font-size: 12px;" placeholder="assets/images/blog/blog-1.jpg">
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
