<?php
/**
 * ONS SOUND — WordPress-Style Admin Dashboard Overview
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// 1. Live Database Aggregations
$totalSales = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0.00) FROM `orders` WHERE order_status != 'cancelled'")->fetchColumn();
$totalOrders = (int)$db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
$pendingOrders = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE order_status = 'pending'")->fetchColumn();
$totalCustomers = (int)$db->query("SELECT COUNT(*) FROM `users` WHERE role = 'customer'")->fetchColumn();
$totalProducts = (int)$db->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
$lowStockProducts = (int)$db->query("SELECT COUNT(*) FROM `products` WHERE stock_quantity <= 5 AND is_active = 1")->fetchColumn();

// 2. Recent Orders
$recentOrdersStmt = $db->query("SELECT * FROM `orders` ORDER BY id DESC LIMIT 5");
$recentOrders = $recentOrdersStmt->fetchAll();

// 3. Recent Customers
$recentCustomersStmt = $db->query("SELECT id, full_name, email, phone, created_at, status FROM `users` WHERE role = 'customer' ORDER BY id DESC LIMIT 5");
$recentCustomers = $recentCustomersStmt->fetchAll();

// 4. Latest Contact Enquiries
$recentEnquiriesStmt = $db->query("SELECT * FROM `contact_enquiries` ORDER BY id DESC LIMIT 5");
$recentEnquiries = $recentEnquiriesStmt->fetchAll();

// 5. Recent Admin Activity Logs
$recentActivityStmt = $db->query("SELECT * FROM `admin_activity_logs` ORDER BY id DESC LIMIT 6");
$recentActivity = $recentActivityStmt->fetchAll();
?>

<h1 class="wp-heading-inline">Dashboard</h1>
<hr class="wp-header-end">

<!-- Welcome Panel matching WordPress wp-admin -->
<div class="postbox" style="background: #fff; padding: 20px; border-left: 4px solid var(--wp-primary); margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 600; margin: 0 0 8px 0; color: #1d2327;">
        Welcome to ONS SOUND Control Center!
    </h2>
    <p style="color: #646970; font-size: 13px; margin: 0 0 16px 0; max-width: 750px;">
        We've assembled some links to get you started with precision speaker drivers, booking management, customer orders, and storefront appearance.
    </p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= admin_url('product-edit.php') ?>" class="button button-primary button-hero">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
        <a href="<?= admin_url('orders.php') ?>" class="button button-secondary button-hero">
            <i class="bi bi-receipt me-1"></i> Manage Orders (<?= $totalOrders ?>)
        </a>
        <a href="<?= admin_url('appearance-home.php') ?>" class="button button-secondary button-hero">
            <i class="bi bi-palette me-1"></i> Customize Homepage
        </a>
    </div>
</div>

<!-- Dynamic Summary Cards Grid -->
<div class="dashboard-widgets-grid">
    <div class="dash-stat-card stat-success">
        <div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value"><?= format_price($totalSales) ?></div>
            <small class="text-muted"><?= $totalOrders ?> booked orders</small>
        </div>
        <i class="bi bi-currency-rupee stat-icon"></i>
    </div>

    <div class="dash-stat-card stat-warning">
        <div>
            <div class="stat-label">Pending Orders</div>
            <div class="stat-value"><?= $pendingOrders ?></div>
            <a href="<?= admin_url('orders.php?status=pending') ?>" style="font-size: 11px; text-decoration: none; color: var(--wp-primary);">Review bookings &rarr;</a>
        </div>
        <i class="bi bi-clock-history stat-icon" style="color: #dba617;"></i>
    </div>

    <div class="dash-stat-card">
        <div>
            <div class="stat-label">Total Products</div>
            <div class="stat-value"><?= $totalProducts ?></div>
            <a href="<?= admin_url('products.php') ?>" style="font-size: 11px; text-decoration: none; color: var(--wp-primary);">View catalog &rarr;</a>
        </div>
        <i class="bi bi-speaker stat-icon"></i>
    </div>

    <div class="dash-stat-card <?= $lowStockProducts > 0 ? 'stat-danger' : '' ?>">
        <div>
            <div class="stat-label">Low Stock Alert</div>
            <div class="stat-value"><?= $lowStockProducts ?></div>
            <small class="text-muted"><?= $lowStockProducts > 0 ? 'Requires replenishment' : 'All stocks healthy' ?></small>
        </div>
        <i class="bi bi-exclamation-diamond stat-icon" style="<?= $lowStockProducts > 0 ? 'color: #d63638;' : '' ?>"></i>
    </div>

    <div class="dash-stat-card">
        <div>
            <div class="stat-label">Registered Customers</div>
            <div class="stat-value"><?= $totalCustomers ?></div>
            <a href="<?= admin_url('customers.php') ?>" style="font-size: 11px; text-decoration: none; color: var(--wp-primary);">View customers &rarr;</a>
        </div>
        <i class="bi bi-people stat-icon"></i>
    </div>
</div>

<!-- 2-Column Dashboard Postboxes Layout -->
<div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 20px;" class="dashboard-columns">
    <!-- Left Column: Recent Orders & Recent Inquiries -->
    <div>
        <!-- Recent Orders Postbox -->
        <div class="postbox">
            <div class="postbox-header">
                <h2><i class="bi bi-cart3 me-1"></i> Recent Order Bookings</h2>
                <a href="<?= admin_url('orders.php') ?>" class="button button-small">View All Orders</a>
            </div>
            <div class="inside" style="padding: 0;">
                <?php if (empty($recentOrders)): ?>
                    <p style="padding: 16px; color: #646970; margin: 0;">No orders booked yet.</p>
                <?php else: ?>
                    <table class="wp-list-table striped" style="border: none; box-shadow: none;">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $ord): ?>
                                <tr>
                                    <td>
                                        <a href="<?= admin_url('order-view.php?id=' . (int)$ord['id']) ?>" style="font-weight: 600; text-decoration: none; color: var(--wp-primary);">
                                            #<?= e($ord['order_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?= e($ord['customer_name']) ?></strong><br>
                                        <small class="text-muted"><?= e($ord['customer_phone']) ?></small>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($ord['created_at'])) ?></td>
                                    <td>
                                        <span class="status-pill status-<?= e($ord['order_status']) ?>">
                                            <?= e($ord['order_status']) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right; font-weight: 600;">
                                        <?= format_price($ord['total_amount']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Latest Contact Enquiries Postbox -->
        <div class="postbox">
            <div class="postbox-header">
                <h2><i class="bi bi-chat-left-text me-1"></i> Latest Customer Inquiries</h2>
                <a href="<?= admin_url('enquiries.php') ?>" class="button button-small">View All Inquiries</a>
            </div>
            <div class="inside" style="padding: 0;">
                <?php if (empty($recentEnquiries)): ?>
                    <p style="padding: 16px; color: #646970; margin: 0;">No customer inquiries received yet.</p>
                <?php else: ?>
                    <table class="wp-list-table striped" style="border: none; box-shadow: none;">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Subject / Message</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEnquiries as $enq): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($enq['name']) ?></strong><br>
                                        <small class="text-muted"><?= e($enq['email']) ?></small>
                                    </td>
                                    <td style="max-width: 320px;">
                                        <span style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; color: #444;">
                                            <?= e($enq['message']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($enq['created_at'])) ?></td>
                                    <td>
                                        <span class="status-pill status-<?= ($enq['status'] === 'new') ? 'pending' : 'approved' ?>">
                                            <?= e($enq['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Quick Draft / Shortcuts / Recent Activity -->
    <div>
        <!-- Quick Action Shortcuts -->
        <div class="postbox">
            <div class="postbox-header">
                <h2><i class="bi bi-lightning me-1"></i> Quick Shortcuts</h2>
            </div>
            <div class="inside">
                <ul style="list-style: none; margin: 0; padding: 0; line-height: 2;">
                    <li><i class="bi bi-plus-circle me-2 text-primary"></i><a href="<?= admin_url('product-edit.php') ?>" style="text-decoration:none; color:var(--wp-primary);">Add a New Product</a></li>
                    <li><i class="bi bi-tags me-2 text-primary"></i><a href="<?= admin_url('coupons.php?action=new') ?>" style="text-decoration:none; color:var(--wp-primary);">Create a Coupon Code</a></li>
                    <li><i class="bi bi-pencil-square me-2 text-primary"></i><a href="<?= admin_url('post-edit.php') ?>" style="text-decoration:none; color:var(--wp-primary);">Write a Blog Post</a></li>
                    <li><i class="bi bi-card-image me-2 text-primary"></i><a href="<?= admin_url('appearance-banners.php') ?>" style="text-decoration:none; color:var(--wp-primary);">Update Homepage Carousel Banners</a></li>
                    <li><i class="bi bi-envelope-at me-2 text-primary"></i><a href="<?= admin_url('settings-email.php') ?>" style="text-decoration:none; color:var(--wp-primary);">Configure SMTP Email</a></li>
                </ul>
            </div>
        </div>

        <!-- Recent Customer Signups -->
        <div class="postbox">
            <div class="postbox-header">
                <h2><i class="bi bi-person-check me-1"></i> Recent Customers</h2>
                <a href="<?= admin_url('customers.php') ?>" class="button button-small">View All</a>
            </div>
            <div class="inside">
                <?php if (empty($recentCustomers)): ?>
                    <p style="color: #646970; margin: 0;">No customer registrations recorded.</p>
                <?php else: ?>
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <?php foreach ($recentCustomers as $c): ?>
                            <li style="padding: 6px 0; border-bottom: 1px solid #f0f0f1; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 13px; color: #1d2327;"><?= e($c['full_name']) ?></strong>
                                    <small style="display: block; color: #646970;"><?= e($c['email']) ?></small>
                                </div>
                                <span class="status-pill status-active"><?= e($c['status']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Admin Activity Logs -->
        <div class="postbox">
            <div class="postbox-header">
                <h2><i class="bi bi-clock-history me-1"></i> Admin Activity Log</h2>
                <a href="<?= admin_url('activity.php') ?>" class="button button-small">View All</a>
            </div>
            <div class="inside">
                <?php if (empty($recentActivity)): ?>
                    <p style="color: #646970; margin: 0;">No admin activities logged yet.</p>
                <?php else: ?>
                    <ul style="list-style: none; margin: 0; padding: 0; font-size: 12px;">
                        <?php foreach ($recentActivity as $act): ?>
                            <li style="padding: 6px 0; border-bottom: 1px solid #f0f0f1;">
                                <strong style="color: #1d2327;"><?= e($act['action']) ?></strong>
                                <span style="display: block; color: #555;"><?= e($act['details'] ?? '') ?></span>
                                <small style="color: #8c8f94; font-size: 11px;"><?= e($act['user_name']) ?> &bull; <?= date('M j, g:i A', strtotime($act['created_at'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media screen and (max-width: 960px) {
    .dashboard-columns {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
