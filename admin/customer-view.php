<?php
/**
 * ONS SOUND — Customer Profile, Addresses & Order History
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Customer Details';

$db = get_db();
$userId = (int)($_GET['id'] ?? 0);

if ($userId <= 0) {
    admin_set_flash('error', 'Invalid customer ID.');
    header("Location: " . admin_url('customers.php'));
    exit;
}

// Fetch Customer
$stmt = $db->prepare("SELECT * FROM `users` WHERE id = ?");
$stmt->execute([$userId]);
$customer = $stmt->fetch();

if (!$customer) {
    admin_set_flash('error', 'Customer not found.');
    header("Location: " . admin_url('customers.php'));
    exit;
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('customer-view.php?id=' . $userId));
        exit;
    }

    $newStatus = clean($_POST['status'] ?? 'active');
    if (in_array($newStatus, ['active', 'inactive', 'banned'])) {
        $db->prepare("UPDATE `users` SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$newStatus, $userId]);
        log_admin_activity('Update Customer Status', "Updated customer #$userId status to $newStatus");
        admin_set_flash('success', "Customer status updated to " . ucfirst($newStatus) . ".");
        header("Location: " . admin_url('customer-view.php?id=' . $userId));
        exit;
    }
}

// Fetch Saved Addresses
$stmt = $db->prepare("SELECT * FROM `addresses` WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll();

// Fetch Customer Orders
$stmt = $db->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM `order_items` WHERE order_id = o.id) AS item_count
    FROM `orders` o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

// Calculate total spend
$totalSpent = 0;
foreach ($orders as $ord) {
    if ($ord['order_status'] === 'delivered') {
        $totalSpent += (float)$ord['total_amount'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Customer: <?= htmlspecialchars($customer['full_name']) ?></h1>
    <a href="<?= admin_url('customers.php') ?>" class="page-title-action">Back to Customers</a>
    <hr class="wp-header-end">

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2" style="display: flex; gap: 20px; align-items: flex-start;">
            
            <!-- Left Column: Order History & Addresses (~68%) -->
            <div id="post-body-content" style="flex: 1; min-width: 0;">
                
                <!-- Order History Postbox -->
                <div class="postbox" style="margin-bottom: 20px;">
                    <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 12px 15px;">
                        <h2 style="font-weight: 600; font-size: 14px; margin: 0;">Order History (<?= count($orders) ?>)</h2>
                    </div>
                    <div class="inside" style="padding: 0;">
                        <table class="wp-list-table widefat fixed striped" style="border: none;">
                            <thead>
                                <tr>
                                    <th style="width: 160px;">Order #</th>
                                    <th style="width: 140px;">Date</th>
                                    <th style="width: 120px;">Status</th>
                                    <th>Items</th>
                                    <th style="width: 120px; text-align: right;">Total</th>
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $o): ?>
                                        <tr>
                                            <td>
                                                <strong><a href="<?= admin_url('order-view.php?id=' . $o['id']) ?>">#<?= htmlspecialchars($o['order_number']) ?></a></strong>
                                            </td>
                                            <td style="font-size: 12px; color: #646970;">
                                                <?= date('M j, Y', strtotime($o['created_at'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= htmlspecialchars($o['order_status']) ?>">
                                                    <?= htmlspecialchars($o['order_status']) ?>
                                                </span>
                                            </td>
                                            <td style="font-size: 13px;">
                                                <?= (int)$o['item_count'] ?> items
                                            </td>
                                            <td style="text-align: right; font-weight: 600;">
                                                <?= format_price($o['total_amount']) ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <a href="<?= admin_url('order-view.php?id=' . $o['id']) ?>" class="button button-small">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 25px; color: #646970;">No orders found for this customer.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Saved Addresses Postbox -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>Saved Delivery Addresses</h2>
                    </div>
                    <div class="inside" style="padding: 15px;">
                        <?php if (!empty($addresses)): ?>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 15px;">
                                <?php foreach ($addresses as $addr): ?>
                                    <div style="border: 1px solid #dcdcde; border-radius: 4px; padding: 12px; background: #fafafa; font-size: 13px; line-height: 1.5;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                            <strong><?= htmlspecialchars($addr['recipient_name'] ?? $customer['full_name']) ?></strong>
                                            <?php if (!empty($addr['is_default'])): ?>
                                                <span class="badge" style="background: #007017; font-size: 10px;">Default</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="color: #646970; font-size: 12px; margin-bottom: 6px;">
                                            <i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($addr['phone'] ?? '—') ?>
                                        </div>
                                        <div style="color: #2c3338;">
                                            <?= htmlspecialchars($addr['address_line1'] ?? '') ?>
                                            <?php if (!empty($addr['address_line2'])): ?>
                                                <br><?= htmlspecialchars($addr['address_line2']) ?>
                                            <?php endif; ?>
                                            <br><?= htmlspecialchars($addr['city'] ?? '') ?>, <?= htmlspecialchars($addr['state'] ?? '') ?> - <?= htmlspecialchars($addr['postal_code'] ?? '') ?>
                                            <br><?= htmlspecialchars($addr['country'] ?? 'India') ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #646970; margin: 0; font-size: 13px;">No saved addresses found in address book.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Right Column: Customer Details (~32%) -->
            <div id="postbox-container-1" style="width: 320px; flex-shrink: 0;">
                
                <!-- Account Status & Details -->
                <div class="postbox" style="margin-bottom: 20px;">
                    <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7;">
                        <h2>Customer Profile</h2>
                    </div>
                    <div class="inside" style="padding: 15px; font-size: 13px;">
                        <div style="text-align: center; margin-bottom: 15px;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: #2271b1; color: #fff; font-size: 24px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">
                                <?= strtoupper(substr($customer['full_name'], 0, 1)) ?>
                            </div>
                            <h3 style="margin: 8px 0 2px; font-size: 16px;"><?= htmlspecialchars($customer['full_name']) ?></h3>
                            <span style="font-size: 12px; color: #646970;">Registered Customer (ID: #<?= $customer['id'] ?>)</span>
                        </div>

                        <div style="border-top: 1px solid #eee; padding-top: 10px; margin-bottom: 8px;">
                            <strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($customer['email']) ?>"><?= htmlspecialchars($customer['email']) ?></a>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong>Phone:</strong> <?= htmlspecialchars($customer['phone'] ?: '—') ?>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong>Joined:</strong> <?= date('F j, Y', strtotime($customer['created_at'])) ?>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <strong>Total Spend:</strong> <span style="font-weight: 700; color: #007017;"><?= format_price($totalSpent) ?></span>
                        </div>

                        <!-- Update Status Form -->
                        <form method="POST" action="" style="border-top: 1px solid #eee; padding-top: 12px;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="update_status" value="1">
                            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Account Status:</label>
                            <select name="status" style="width: 100%; padding: 6px; font-size: 13px; margin-bottom: 10px;">
                                <option value="active" <?= ($customer['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($customer['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                <option value="banned" <?= ($customer['status'] === 'banned') ? 'selected' : '' ?>>Banned / Suspended</option>
                            </select>
                            <button type="submit" class="button button-primary" style="width: 100%;">Save Status</button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
