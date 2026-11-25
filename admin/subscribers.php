<?php
/**
 * ONS SOUND — Newsletter Subscribers Management with CSV Export
 */

$pageTitle = 'Newsletter Subscribers';
require_once __DIR__ . '/auth.php';
require_admin();

$db = get_db();

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $stmt = $db->query("SELECT id, email, status, subscribed_at FROM `newsletter_subscribers` ORDER BY id DESC");
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ons_subscribers_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Email Address', 'Subscription Status', 'Subscribed At']);

    foreach ($subscribers as $row) {
        fputcsv($output, [
            $row['id'],
            $row['email'],
            $row['status'],
            $row['subscribed_at']
        ]);
    }
    fclose($output);
    log_admin_activity('Export Subscribers', 'Exported newsletter subscribers to CSV');
    exit;
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $db->prepare("DELETE FROM `newsletter_subscribers` WHERE id = ?")->execute([$delId]);
    log_admin_activity('Delete Subscriber', "Removed subscriber #$delId");
    admin_set_flash('success', "Subscriber deleted.");
    header("Location: " . admin_url('subscribers.php'));
    exit;
}

require_once __DIR__ . '/includes/header.php';

// Search & Pagination
$search = clean($_GET['s'] ?? '');
$page = max(1, (int)($_GET['paged'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "email LIKE ?";
    $params[] = "%$search%";
}

$whereSql = implode(' AND ', $where);

// Count
$countStmt = $db->prepare("SELECT COUNT(*) FROM `newsletter_subscribers` WHERE $whereSql");
$countStmt->execute($params);
$totalSubscribers = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalSubscribers / $perPage);

// Fetch
$query = "SELECT * FROM `newsletter_subscribers` WHERE $whereSql ORDER BY subscribed_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$subscribers = $stmt->fetchAll();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Newsletter Subscribers</h1>
    <a href="<?= admin_url('subscribers.php?action=export_csv') ?>" class="page-title-action">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export to CSV
    </a>
    <hr class="wp-header-end">

    <!-- Search Form -->
    <form method="GET" action="<?= admin_url('subscribers.php') ?>" style="margin-bottom: 12px; display: flex; justify-content: flex-end;">
        <p class="search-box" style="margin: 0; display: flex; gap: 5px;">
            <input type="search" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Search by email..." style="padding: 4px 8px; font-size: 13px; border: 1px solid #8c8f94; border-radius: 4px;">
            <input type="submit" class="button" value="Search Subscribers">
        </p>
    </form>

    <div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <span class="displaying-num" style="font-size: 13px; color: #646970;"><?= $totalSubscribers ?> total subscribers</span>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>Email Address</th>
                <th style="width: 120px;">Status</th>
                <th style="width: 180px;">Subscribed Date</th>
                <th style="width: 80px; text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($subscribers)): ?>
                <?php foreach ($subscribers as $s): ?>
                    <tr>
                        <td>#<?= $s['id'] ?></td>
                        <td>
                            <strong><a href="mailto:<?= htmlspecialchars($s['email']) ?>"><?= htmlspecialchars($s['email']) ?></a></strong>
                            <div class="row-actions">
                                <span class="trash"><a href="<?= admin_url('subscribers.php?action=delete&id=' . $s['id']) ?>" onclick="return confirm('Remove this subscriber?');" style="color: #d63638;">Remove</a></span>
                            </div>
                        </td>
                        <td>
                            <?php if ($s['status'] === 'active'): ?>
                                <span class="badge" style="background: #007017;">Active</span>
                            <?php else: ?>
                                <span class="badge" style="background: #787c82;">Unsubscribed</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 12px; color: #646970;">
                            <?= date('M j, Y @ g:i a', strtotime($s['subscribed_at'])) ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="<?= admin_url('subscribers.php?action=delete&id=' . $s['id']) ?>" onclick="return confirm('Remove subscriber?');" class="button button-small" style="color: #d63638;" title="Delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 25px; color: #646970;">No subscribers found.</td>
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
                        <a class="button button-small <?= ($pg == $page) ? 'button-primary' : '' ?>" href="<?= admin_url('subscribers.php?paged=' . $pg . (!empty($search) ? '&s=' . urlencode($search) : '')) ?>">
                            <?= $pg ?>
                        </a>
                    <?php endfor; ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
