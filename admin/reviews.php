<?php
/**
 * ONS SOUND — WordPress-Style Product Reviews Management
 */

require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/../models/Product.php';

$pageTitle = 'Product Reviews';
$db = get_db();
$prodModel = new Product();

// Handle Single Actions
if (isset($_GET['action']) && !empty($_GET['id'])) {
    $action = clean($_GET['action']);
    $revId = (int)$_GET['id'];

    $targetProductId = (int)$db->query("SELECT product_id FROM `product_reviews` WHERE id = $revId")->fetchColumn();

    if (in_array($action, ['approve', 'reject', 'unapprove'])) {
        $newStatus = ($action === 'approve') ? 'approved' : (($action === 'reject') ? 'rejected' : 'pending');
        $db->prepare("UPDATE `product_reviews` SET status = ? WHERE id = ?")->execute([$newStatus, $revId]);
        log_admin_activity('Update Review Status', "Marked review #$revId as $newStatus");
        admin_set_flash('success', "Review #$revId status set to $newStatus.");
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM `product_reviews` WHERE id = ?")->execute([$revId]);
        log_admin_activity('Delete Review', "Deleted review #$revId");
        admin_set_flash('success', "Review #$revId permanently deleted.");
    }

    if ($targetProductId > 0) {
        $prodModel->syncProductRating($targetProductId);
    }

    header("Location: " . admin_url('reviews.php'));
    exit;
}

// Handle Bulk Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && !empty($_POST['post'])) {
    $action = clean($_POST['action']);
    $ids = array_map('intval', $_POST['post']);

    if (!empty($ids)) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $targetProductIds = $db->query("SELECT DISTINCT product_id FROM `product_reviews` WHERE id IN (" . implode(',', $ids) . ")")->fetchAll(PDO::FETCH_COLUMN);

        if ($action === 'approve') {
            $db->prepare("UPDATE `product_reviews` SET status = 'approved' WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Approve Reviews', "Approved " . count($ids) . " reviews.");
            admin_set_flash('success', count($ids) . " reviews approved.");
        } elseif ($action === 'reject') {
            $db->prepare("UPDATE `product_reviews` SET status = 'rejected' WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Reject Reviews', "Rejected " . count($ids) . " reviews.");
            admin_set_flash('success', count($ids) . " reviews rejected.");
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM `product_reviews` WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Delete Reviews', "Deleted " . count($ids) . " reviews.");
            admin_set_flash('success', count($ids) . " reviews deleted.");
        }

        foreach ($targetProductIds as $pid) {
            if ($pid > 0) {
                $prodModel->syncProductRating((int)$pid);
            }
        }
    }
    header("Location: " . admin_url('reviews.php'));
    exit;
}

// Counts for status tabs
$countAll = (int)$db->query("SELECT COUNT(*) FROM `product_reviews`")->fetchColumn();
$countPending = (int)$db->query("SELECT COUNT(*) FROM `product_reviews` WHERE status = 'pending'")->fetchColumn();
$countApproved = (int)$db->query("SELECT COUNT(*) FROM `product_reviews` WHERE status = 'approved'")->fetchColumn();
$countRejected = (int)$db->query("SELECT COUNT(*) FROM `product_reviews` WHERE status = 'rejected'")->fetchColumn();

// Status Filter & Search
$statusFilter = clean($_GET['status'] ?? 'all');
$search = clean($_GET['s'] ?? '');
$page = max(1, (int)($_GET['paged'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
    $where[] = "r.status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $where[] = "(r.reviewer_name LIKE ? OR r.reviewer_email LIKE ? OR r.review_text LIKE ? OR p.name LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

$whereSql = implode(' AND ', $where);

// Count total matching
$countStmt = $db->prepare("
    SELECT COUNT(*) 
    FROM `product_reviews` r
    LEFT JOIN `products` p ON p.id = r.product_id
    WHERE $whereSql
");
$countStmt->execute($params);
$totalReviews = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalReviews / $perPage);

// Fetch reviews
$query = "
    SELECT r.*, p.name AS product_name, p.slug AS product_slug, p.main_image AS product_image
    FROM `product_reviews` r
    LEFT JOIN `products` p ON p.id = r.product_id
    WHERE $whereSql
    ORDER BY r.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Product Reviews</h1>
    <hr class="wp-header-end">

    <!-- Status Subsubsub Bar -->
    <ul class="subsubsub">
        <li class="all"><a href="<?= admin_url('reviews.php') ?>" class="<?= ($statusFilter === 'all') ? 'current' : '' ?>">All <span class="count">(<?= $countAll ?>)</span></a> |</li>
        <li class="pending"><a href="<?= admin_url('reviews.php?status=pending') ?>" class="<?= ($statusFilter === 'pending') ? 'current' : '' ?>">Pending <span class="count">(<?= $countPending ?>)</span></a> |</li>
        <li class="approved"><a href="<?= admin_url('reviews.php?status=approved') ?>" class="<?= ($statusFilter === 'approved') ? 'current' : '' ?>">Approved <span class="count">(<?= $countApproved ?>)</span></a> |</li>
        <li class="rejected"><a href="<?= admin_url('reviews.php?status=rejected') ?>" class="<?= ($statusFilter === 'rejected') ? 'current' : '' ?>">Rejected <span class="count">(<?= $countRejected ?>)</span></a></li>
    </ul>

    <!-- Filter Form -->
    <form method="GET" action="<?= admin_url('reviews.php') ?>" style="margin-bottom: 12px; display: flex; justify-content: flex-end;">
        <?php if ($statusFilter !== 'all'): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
        <?php endif; ?>
        <p class="search-box" style="margin: 0; display: flex; gap: 5px;">
            <input type="search" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Search reviews..." style="padding: 4px 8px; font-size: 13px; border: 1px solid #8c8f94; border-radius: 4px;">
            <input type="submit" class="button" value="Search Reviews">
        </p>
    </form>

    <form method="POST" action="<?= admin_url('reviews.php') ?>">
        <div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div class="alignleft actions bulkactions" style="display: flex; gap: 6px;">
                <select name="action">
                    <option value="">Bulk Actions</option>
                    <option value="approve">Approve</option>
                    <option value="reject">Reject</option>
                    <option value="delete">Delete Permanently</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
            <div class="tablenav-pages" style="font-size: 13px; color: #646970;">
                <span class="displaying-num"><?= $totalReviews ?> items</span>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column" style="width: 40px; padding-left: 10px;">
                        <input id="cb-select-all-1" type="checkbox" onclick="document.querySelectorAll('.rev-select').forEach(cb => cb.checked = this.checked)">
                    </td>
                    <th style="width: 180px;">Author</th>
                    <th style="width: 100px;">Rating</th>
                    <th>Comment</th>
                    <th style="width: 220px;">In Response To</th>
                    <th style="width: 110px;">Status</th>
                    <th style="width: 130px;">Submitted On</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <th scope="row" class="check-column" style="padding-left: 10px;">
                                <input type="checkbox" name="post[]" value="<?= $r['id'] ?>" class="rev-select">
                            </th>
                            <td>
                                <strong><?= htmlspecialchars($r['reviewer_name']) ?></strong><br>
                                <span style="font-size: 12px; color: #646970;"><?= htmlspecialchars($r['reviewer_email'] ?? '—') ?></span>
                                <div class="row-actions">
                                    <?php if ($r['status'] !== 'approved'): ?>
                                        <span class="approve"><a href="<?= admin_url('reviews.php?action=approve&id=' . $r['id']) ?>" style="color: #007017;">Approve</a> | </span>
                                    <?php else: ?>
                                        <span class="unapprove"><a href="<?= admin_url('reviews.php?action=unapprove&id=' . $r['id']) ?>" style="color: #996800;">Unapprove</a> | </span>
                                    <?php endif; ?>
                                    <?php if ($r['status'] !== 'rejected'): ?>
                                        <span class="reject"><a href="<?= admin_url('reviews.php?action=reject&id=' . $r['id']) ?>" style="color: #d63638;">Reject</a> | </span>
                                    <?php endif; ?>
                                    <span class="trash"><a href="<?= admin_url('reviews.php?action=delete&id=' . $r['id']) ?>" onclick="return confirm('Permanently delete review #<?= $r['id'] ?>?');" style="color: #d63638;">Trash</a></span>
                                </div>
                            </td>
                            <td>
                                <div style="color: #f59e0b; font-size: 14px;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= ($i <= $r['rating']) ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 13px; line-height: 1.5;">
                                    <?= nl2br(htmlspecialchars($r['review_text'])) ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($r['product_name'])): ?>
                                    <strong><a href="<?= admin_url('product-edit.php?id=' . $r['product_id']) ?>"><?= htmlspecialchars($r['product_name']) ?></a></strong>
                                    <div style="font-size: 12px; margin-top: 2px;">
                                        <a href="<?= url('product-details.php?slug=' . urlencode($r['product_slug'] ?? '')) ?>" target="_blank" style="color: #646970; text-decoration: none;">View Product <i class="bi bi-box-arrow-up-right"></i></a>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #8c8f94;">[Deleted Product #<?= $r['product_id'] ?>]</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($r['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($r['status'])) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: #646970;">
                                <?= date('Y/m/d \a\t g:i a', strtotime($r['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 25px; color: #646970;">
                            No reviews found matching your criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
