<?php
/**
 * ONS SOUND — WordPress-Style Orders Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Orders';
$currentStatusFilter = clean($_GET['status'] ?? '');
$db = get_db();

// Handle Bulk Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && !empty($_POST['post'])) {
    $action = clean($_POST['action']);
    $ids = array_map('intval', $_POST['post']);

    if (!empty($ids)) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        if (in_array($action, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $db->prepare("UPDATE `orders` SET order_status = ?, updated_at = NOW() WHERE id IN ($in)")->execute(array_merge([$action], $ids));
            
            // Log history for each
            $histStmt = $db->prepare("INSERT INTO `order_status_history` (order_id, old_status, new_status, notes, created_by, created_at) VALUES (?, 'bulk', ?, 'Bulk status update by admin', ?, NOW())");
            foreach ($ids as $oid) {
                $histStmt->execute([$oid, $action, $_SESSION['admin_id'] ?? 1]);
            }

            log_admin_activity('Bulk Update Orders', "Changed " . count($ids) . " orders to status: $action");
            admin_set_flash('success', count($ids) . " orders marked as " . ucfirst($action) . ".");
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM `order_items` WHERE order_id IN ($in)")->execute($ids);
            $db->prepare("DELETE FROM `orders` WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Delete Orders', "Deleted " . count($ids) . " orders.");
            admin_set_flash('success', count($ids) . " orders deleted.");
        }
    }
    header("Location: " . admin_url('orders.php' . (!empty($currentStatusFilter) ? '?status=' . urlencode($currentStatusFilter) : '')));
    exit;
}

// Counts for status tabs
$countAll = (int)$db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
$countPending = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE order_status = 'pending'")->fetchColumn();
$countProcessing = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE order_status = 'processing'")->fetchColumn();
$countShipped = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE order_status = 'shipped'")->fetchColumn();
$countDelivered = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE order_status = 'delivered'")->fetchColumn();
$countCancelled = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE order_status = 'cancelled'")->fetchColumn();

// Filter & Search
$search = clean($_GET['s'] ?? '');
$page = max(1, (int)($_GET['paged'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if (!empty($currentStatusFilter)) {
    $where[] = "order_status = ?";
    $params[] = $currentStatusFilter;
}

if (!empty($search)) {
    $where[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

$whereSql = implode(' AND ', $where);

// Count matching
$countStmt = $db->prepare("SELECT COUNT(*) FROM `orders` WHERE $whereSql");
$countStmt->execute($params);
$totalOrders = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// Fetch orders
$query = "
    SELECT o.*, 
           (SELECT COUNT(*) FROM `order_items` WHERE order_id = o.id) AS item_count
    FROM `orders` o
    WHERE $whereSql
    ORDER BY o.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Orders</h1>
    <hr class="wp-header-end">

    <!-- Status Tabs -->
    <ul class="subsubsub">
        <li class="all"><a href="<?= admin_url('orders.php') ?>" class="<?= empty($currentStatusFilter) ? 'current' : '' ?>">All <span class="count">(<?= $countAll ?>)</span></a> |</li>
        <li class="pending"><a href="<?= admin_url('orders.php?status=pending') ?>" class="<?= ($currentStatusFilter === 'pending') ? 'current' : '' ?>">Pending <span class="count">(<?= $countPending ?>)</span></a> |</li>
        <li class="processing"><a href="<?= admin_url('orders.php?status=processing') ?>" class="<?= ($currentStatusFilter === 'processing') ? 'current' : '' ?>">Processing <span class="count">(<?= $countProcessing ?>)</span></a> |</li>
        <li class="shipped"><a href="<?= admin_url('orders.php?status=shipped') ?>" class="<?= ($currentStatusFilter === 'shipped') ? 'current' : '' ?>">Shipped <span class="count">(<?= $countShipped ?>)</span></a> |</li>
        <li class="completed"><a href="<?= admin_url('orders.php?status=delivered') ?>" class="<?= ($currentStatusFilter === 'delivered') ? 'current' : '' ?>">Completed <span class="count">(<?= $countDelivered ?>)</span></a> |</li>
        <li class="cancelled"><a href="<?= admin_url('orders.php?status=cancelled') ?>" class="<?= ($currentStatusFilter === 'cancelled') ? 'current' : '' ?>">Cancelled <span class="count">(<?= $countCancelled ?>)</span></a></li>
    </ul>

    <!-- Search Form -->
    <form method="GET" action="<?= admin_url('orders.php') ?>" style="margin-bottom: 12px; display: flex; justify-content: flex-end;">
        <?php if (!empty($currentStatusFilter)): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatusFilter) ?>">
        <?php endif; ?>
        <p class="search-box" style="margin: 0; display: flex; gap: 5px;">
            <input type="search" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Search orders..." style="padding: 4px 8px; font-size: 13px; border: 1px solid #8c8f94; border-radius: 4px;">
            <input type="submit" class="button" value="Search Orders">
        </p>
    </form>

    <form method="POST" action="<?= admin_url('orders.php' . (!empty($currentStatusFilter) ? '?status=' . urlencode($currentStatusFilter) : '')) ?>">
        <div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div class="alignleft actions bulkactions" style="display: flex; gap: 6px;">
                <select name="action">
                    <option value="">Bulk Actions</option>
                    <option value="pending">Mark Pending</option>
                    <option value="processing">Mark Processing</option>
                    <option value="shipped">Mark Shipped</option>
                    <option value="delivered">Mark Completed (Delivered)</option>
                    <option value="cancelled">Mark Cancelled</option>
                    <option value="delete">Delete Permanently</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
            <div class="tablenav-pages" style="font-size: 13px; color: #646970;">
                <span class="displaying-num"><?= $totalOrders ?> orders</span>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column" style="width: 40px; padding-left: 10px;">
                        <input id="cb-select-all-1" type="checkbox" onclick="document.querySelectorAll('.order-select').forEach(cb => cb.checked = this.checked)">
                    </td>
                    <th style="width: 220px;">Order</th>
                    <th style="width: 140px;">Date</th>
                    <th style="width: 120px;">Status</th>
                    <th>Customer & Delivery Address</th>
                    <th style="width: 110px;">Total</th>
                    <th style="width: 90px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <th scope="row" class="check-column" style="padding-left: 10px;">
                                <input type="checkbox" name="post[]" value="<?= $o['id'] ?>" class="order-select">
                            </th>
                            <td>
                                <strong>
                                    <a href="<?= admin_url('order-view.php?id=' . $o['id']) ?>" class="row-title" style="font-size: 14px;">
                                        #<?= htmlspecialchars($o['order_number']) ?>
                                    </a>
                                </strong>
                                <div style="font-size: 12px; color: #646970; margin-top: 2px;">
                                    by <?= htmlspecialchars($o['customer_name']) ?>
                                </div>
                                <div class="row-actions">
                                    <span class="edit"><a href="<?= admin_url('order-view.php?id=' . $o['id']) ?>">View / Edit</a> | </span>
                                    <span class="view"><a href="<?= url('order-confirmation.php?order=' . urlencode($o['order_number'])) ?>" target="_blank">Frontend Receipt</a></span>
                                </div>
                            </td>
                            <td style="font-size: 12px; color: #646970;">
                                <?= date('M j, Y', strtotime($o['created_at'])) ?><br>
                                <span style="font-size: 11px;"><?= date('g:i a', strtotime($o['created_at'])) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($o['order_status']) ?>">
                                    <?= htmlspecialchars($o['order_status']) ?>
                                </span>
                            </td>
                            <td style="font-size: 13px;">
                                <div><i class="bi bi-person me-1 text-muted"></i> <strong><?= htmlspecialchars($o['customer_name']) ?></strong> (<?= htmlspecialchars($o['customer_phone'] ?? '—') ?>)</div>
                                <div style="font-size: 12px; color: #646970; margin-top: 2px; line-height: 1.4;">
                                    <?= htmlspecialchars(mb_strimwidth($o['shipping_address'] ?? 'No address provided', 0, 80, '...')) ?>
                                </div>
                            </td>
                            <td>
                                <strong style="font-size: 14px;"><?= format_price($o['total_amount']) ?></strong>
                                <div style="font-size: 11px; color: #646970;"><?= (int)$o['item_count'] ?> <?= ($o['item_count'] == 1) ? 'item' : 'items' ?></div>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= admin_url('order-view.php?id=' . $o['id']) ?>" class="button button-small" title="Manage Order">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 25px; color: #646970;">
                            No orders found matching your criteria.
                        </td>
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
                            <a class="button button-small <?= ($p == $page) ? 'button-primary' : '' ?>" href="<?= admin_url('orders.php?paged=' . $p . (!empty($currentStatusFilter) ? '&status=' . urlencode($currentStatusFilter) : '') . (!empty($search) ? '&s=' . urlencode($search) : '')) ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
