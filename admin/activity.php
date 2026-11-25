<?php
/**
 * ONS SOUND — Admin Activity Logs
 */

$pageTitle = 'Activity Log';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$search = clean($_GET['s'] ?? '');
$page = max(1, (int)($_GET['paged'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$whereSql = "WHERE 1=1";
$params = [];
if (!empty($search)) {
    $whereSql .= " AND (action LIKE ? OR details LIKE ? OR user_name LIKE ? OR ip_address LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM `admin_activity_logs` $whereSql");
$countStmt->execute($params);
$totalLogs = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalLogs / $perPage));

$sql = "SELECT * FROM `admin_activity_logs` $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<h1 class="wp-heading-inline">Admin Activity Log</h1>
<hr class="wp-header-end">

<!-- Search Box -->
<form method="GET" action="<?= admin_url('activity.php') ?>" style="float: right; margin-bottom: 10px;">
    <input type="search" name="s" value="<?= e($search) ?>" placeholder="Search logs...">
    <input type="submit" class="button button-secondary" value="Search Logs">
</form>

<div class="tablenav top">
    <div class="alignleft actions">
        <span class="displaying-num"><?= $totalLogs ?> items</span>
    </div>
    <?php if ($totalPages > 1): ?>
        <div class="tablenav-pages">
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page > 1): ?>
                <a href="?paged=<?= $page - 1 ?>&s=<?= urlencode($search) ?>">&lsaquo;</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?paged=<?= $page + 1 ?>&s=<?= urlencode($search) ?>">&rsaquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<table class="wp-list-table striped">
    <thead>
        <tr>
            <th style="width: 160px;">Date & Time</th>
            <th style="width: 140px;">Administrator</th>
            <th style="width: 160px;">Action</th>
            <th>Details</th>
            <th style="width: 120px;">IP Address</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($logs)): ?>
            <tr><td colspan="5" style="text-align:center; padding: 20px; color: #646970;">No activity records found.</td></tr>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= date('Y/m/d g:i:s a', strtotime($log['created_at'])) ?></td>
                    <td><strong><?= e($log['user_name']) ?></strong></td>
                    <td><span class="status-pill status-confirmed"><?= e($log['action']) ?></span></td>
                    <td><?= e($log['details']) ?></td>
                    <td><code><?= e($log['ip_address']) ?></code></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
