<?php
/**
 * ONS SOUND — WordPress-Style Blog Posts Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Blog Posts';

$db = get_db();

// Handle Single Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT title FROM `blog_posts` WHERE id = ?");
    $stmt->execute([$delId]);
    $pTitle = $stmt->fetchColumn();

    if ($pTitle) {
        $db->prepare("DELETE FROM `blog_posts` WHERE id = ?")->execute([$delId]);
        log_admin_activity('Delete Blog Post', "Deleted post #$delId ($pTitle)");
        admin_set_flash('success', "Post '$pTitle' deleted.");
    }
    header("Location: " . admin_url('posts.php'));
    exit;
}

// Handle Bulk Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && !empty($_POST['post'])) {
    $action = clean($_POST['action']);
    $ids = array_map('intval', $_POST['post']);

    if (!empty($ids)) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'delete') {
            $db->prepare("DELETE FROM `blog_posts` WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Delete Posts', "Deleted " . count($ids) . " posts.");
            admin_set_flash('success', count($ids) . " posts deleted.");
        } elseif ($action === 'publish') {
            $db->prepare("UPDATE `blog_posts` SET is_published = 1, published_at = NOW() WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Publish Posts', "Published " . count($ids) . " posts.");
            admin_set_flash('success', count($ids) . " posts published.");
        } elseif ($action === 'draft') {
            $db->prepare("UPDATE `blog_posts` SET is_published = 0 WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Unpublish Posts', "Moved " . count($ids) . " posts to draft.");
            admin_set_flash('success', count($ids) . " posts moved to draft.");
        }
    }
    header("Location: " . admin_url('posts.php'));
    exit;
}

// Filter, Search & Pagination
$statusFilter = clean($_GET['status'] ?? 'all');
$search = clean($_GET['s'] ?? '');
$page = max(1, (int)($_GET['paged'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if ($statusFilter === 'published') {
    $where[] = "p.is_published = 1";
} elseif ($statusFilter === 'draft') {
    $where[] = "p.is_published = 0";
}

if (!empty($search)) {
    $where[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
}

$whereSql = implode(' AND ', $where);

// Counts
$countAll = (int)$db->query("SELECT COUNT(*) FROM `blog_posts`")->fetchColumn();
$countPub = (int)$db->query("SELECT COUNT(*) FROM `blog_posts` WHERE is_published = 1")->fetchColumn();
$countDraft = (int)$db->query("SELECT COUNT(*) FROM `blog_posts` WHERE is_published = 0")->fetchColumn();

// Count matching
$countStmt = $db->prepare("SELECT COUNT(*) FROM `blog_posts` p WHERE $whereSql");
$countStmt->execute($params);
$totalPosts = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalPosts / $perPage);

// Fetch posts
$query = "
    SELECT p.*, c.name AS category_name
    FROM `blog_posts` p
    LEFT JOIN `blog_categories` c ON c.id = p.category_id
    WHERE $whereSql
    ORDER BY p.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Blog Posts</h1>
    <a href="<?= admin_url('post-edit.php') ?>" class="page-title-action">Add New Post</a>
    <hr class="wp-header-end">

    <!-- Status Tabs -->
    <ul class="subsubsub">
        <li class="all"><a href="<?= admin_url('posts.php') ?>" class="<?= ($statusFilter === 'all') ? 'current' : '' ?>">All <span class="count">(<?= $countAll ?>)</span></a> |</li>
        <li class="publish"><a href="<?= admin_url('posts.php?status=published') ?>" class="<?= ($statusFilter === 'published') ? 'current' : '' ?>">Published <span class="count">(<?= $countPub ?>)</span></a> |</li>
        <li class="draft"><a href="<?= admin_url('posts.php?status=draft') ?>" class="<?= ($statusFilter === 'draft') ? 'current' : '' ?>">Drafts <span class="count">(<?= $countDraft ?>)</span></a></li>
    </ul>

    <!-- Search Form -->
    <form method="GET" action="<?= admin_url('posts.php') ?>" style="margin-bottom: 12px; display: flex; justify-content: flex-end;">
        <?php if ($statusFilter !== 'all'): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
        <?php endif; ?>
        <p class="search-box" style="margin: 0; display: flex; gap: 5px;">
            <input type="search" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Search posts..." style="padding: 4px 8px; font-size: 13px; border: 1px solid #8c8f94; border-radius: 4px;">
            <input type="submit" class="button" value="Search Posts">
        </p>
    </form>

    <form method="POST" action="<?= admin_url('posts.php') ?>">
        <div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div class="alignleft actions bulkactions" style="display: flex; gap: 6px;">
                <select name="action">
                    <option value="">Bulk Actions</option>
                    <option value="publish">Publish</option>
                    <option value="draft">Move to Draft</option>
                    <option value="delete">Delete Permanently</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
            <div class="tablenav-pages" style="font-size: 13px; color: #646970;">
                <span class="displaying-num"><?= $totalPosts ?> posts</span>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column" style="width: 40px; padding-left: 10px;">
                        <input id="cb-select-all-1" type="checkbox" onclick="document.querySelectorAll('.post-select').forEach(cb => cb.checked = this.checked)">
                    </td>
                    <th style="width: 60px;">Image</th>
                    <th>Title</th>
                    <th style="width: 140px;">Author</th>
                    <th style="width: 160px;">Category</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 130px;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $p): ?>
                        <tr>
                            <th scope="row" class="check-column" style="padding-left: 10px;">
                                <input type="checkbox" name="post[]" value="<?= $p['id'] ?>" class="post-select">
                            </th>
                            <td>
                                <?php if (!empty($p['image'])): ?>
                                    <img src="<?= url($p['image']) ?>" alt="" style="width: 45px; height: 35px; object-fit: cover; border-radius: 3px; border: 1px solid #dcdcde;">
                                <?php else: ?>
                                    <div style="width: 45px; height: 35px; background: #e2e4e7; border-radius: 3px; display: flex; align-items: center; justify-content: center; color: #8c8f94; font-size: 12px;">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>
                                    <a href="<?= admin_url('post-edit.php?id=' . $p['id']) ?>" class="row-title">
                                        <?= htmlspecialchars($p['title']) ?>
                                    </a>
                                </strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="<?= admin_url('post-edit.php?id=' . $p['id']) ?>">Edit</a> | </span>
                                    <span class="trash"><a href="<?= admin_url('posts.php?action=delete&id=' . $p['id']) ?>" onclick="return confirm('Permanently delete this post?');" style="color: #d63638;">Delete</a> | </span>
                                    <span class="view"><a href="<?= url('blog.php') ?>" target="_blank">View on Blog</a></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($p['author'] ?: 'ONS Team') ?></td>
                            <td><?= htmlspecialchars($p['category_name'] ?: 'Uncategorized') ?></td>
                            <td>
                                <span class="badge badge-<?= !empty($p['is_published']) ? 'active' : 'draft' ?>">
                                    <?= !empty($p['is_published']) ? 'Published' : 'Draft' ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: #646970;">
                                <?= date('Y/m/d', strtotime($p['created_at'])) ?><br>
                                <?= (!empty($p['is_published'])) ? 'Published' : 'Last Modified' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 25px; color: #646970;">No posts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="tablenav bottom" style="display: flex; justify-content: flex-end; margin-top: 10px;">
                <div class="tablenav-pages">
                    <span class="pagination-links">
                        <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
                            <a class="button button-small <?= ($pg == $page) ? 'button-primary' : '' ?>" href="<?= admin_url('posts.php?paged=' . $pg . (!empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '') . (!empty($search) ? '&s=' . urlencode($search) : '')) ?>">
                                <?= $pg ?>
                            </a>
                        <?php endfor; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
