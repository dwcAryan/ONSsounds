<?php
/**
 * ONS SOUND — WordPress-Style Customers Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Customers';

$db = get_db();

// Handle Status Toggle (activate / ban)
if (isset($_GET['action']) && !empty($_GET['id'])) {
    $action = clean($_GET['action']);
    $userId = (int)$_GET['id'];

    if ($action === 'ban') {
        $db->prepare("UPDATE `users` SET status = 'banned' WHERE id = ? AND role = 'customer'")->execute([$userId]);
        log_admin_activity('Customer Status Change', "Banned customer user #$userId");
        admin_set_flash('success', "Customer user #$userId has been banned.");
    } elseif ($action === 'activate') {
        $db->prepare("UPDATE `users` SET status = 'active' WHERE id = ? AND role = 'customer'")->execute([$userId]);
        log_admin_activity('Customer Status Change', "Activated customer user #$userId");
        admin_set_flash('success', "Customer user #$userId is now active.");
    }
    header("Location: " . admin_url('customers.php'));
    exit;
}

// Search & Pagination
$search = clean($_GET['s'] ?? '');
$page = max(1, (int)($_GET['paged'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = ["u.role = 'customer'"];
$params = [];

if (!empty($search)) {
    $where[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
}

$whereSql = implode(' AND ', $where);

// Count matching
$countStmt = $db->prepare("SELECT COUNT(*) FROM `users` u WHERE $whereSql");
$countStmt->execute($params);
$totalCustomers = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalCustomers / $perPage);

// Fetch customers with order stats
$query = "
    SELECT u.*,
           COUNT(o.id) AS order_count,
           COALESCE(SUM(CASE WHEN o.order_status = 'delivered' THEN o.total_amount ELSE 0 END), 0) AS total_spent
    FROM `users` u
    LEFT JOIN `orders` o ON o.user_id = u.id
    WHERE $whereSql
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Customers</h1>
    <hr class="wp-header-end">

    <!-- Search Box -->
    <form method="GET" action="<?= admin_url('customers.php') ?>" style="margin-bottom: 15px; display: flex; justify-content: flex-end;">
        <p class="search-box" style="margin: 0; display: flex; gap: 5px;">
            <input type="search" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Search customers..." style="padding: 4px 8px; font-size: 13px; border: 1px solid #8c8f94; border-radius: 4px;">
            <input type="submit" class="button" value="Search Customers">
        </p>
    </form>

    <div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <span class="displaying-num" style="font-size: 13px; color: #646970;"><?= $totalCustomers ?> registered customers</span>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 50px;">Avatar</th>
                <th>Customer Name</th>
                <th>Email Address</th>
                <th>Phone Number</th>
                <th style="width: 100px; text-align: center;">Orders</th>
                <th style="width: 130px; text-align: right;">Total Spent</th>
                <th style="width: 100px;">Status</th>
                <th style="width: 130px;">Registered</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($customers)): ?>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: #2271b1; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                                <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                            </div>
                        </td>
                        <td>
                            <strong>
                                <a href="<?= admin_url('customer-view.php?id=' . $c['id']) ?>" class="row-title">
                                    <?= htmlspecialchars($c['full_name']) ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="edit"><a href="<?= admin_url('customer-view.php?id=' . $c['id']) ?>">View Profile & Orders</a> | </span>
                                <?php if ($c['status'] === 'active'): ?>
                                    <span class="trash"><a href="<?= admin_url('customers.php?action=ban&id=' . $c['id']) ?>" onclick="return confirm('Ban this customer account?');" style="color: #d63638;">Suspend / Ban</a></span>
                                <?php else: ?>
                                    <span class="approve"><a href="<?= admin_url('customers.php?action=activate&id=' . $c['id']) ?>" style="color: #007017;">Activate</a></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email']) ?></a>
                        </td>
                        <td><?= htmlspecialchars($c['phone'] ?: '—') ?></td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: #f0f0f1; color: #2c3338; font-weight: 600; border: 1px solid #dcdcde;">
                                <?= (int)$c['order_count'] ?>
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600;">
                            <?= format_price($c['total_spent']) ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($c['status']) ?>">
                                <?= ucfirst(htmlspecialchars($c['status'])) ?>
                            </span>
                        </td>
                        <td style="font-size: 12px; color: #646970;">
                            <?= date('M j, Y', strtotime($c['created_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 25px; color: #646970;">No customers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="tablenav bottom" style="display: flex; justify-content: flex-end; margin-top: 10px;">
            <div class="tablenav-pages">
                <span class="pagination-links">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a class="button button-small <?= ($p == $page) ? 'button-primary' : '' ?>" href="<?= admin_url('customers.php?paged=' . $p . (!empty($search) ? '&s=' . urlencode($search) : '')) ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
