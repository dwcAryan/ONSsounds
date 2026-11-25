<?php
/**
 * ONS SOUND — WordPress/WooCommerce-Style Order Details & Status Manager
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Order Details';

$db = get_db();
$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    admin_set_flash('error', 'Invalid order ID.');
    header("Location: " . admin_url('orders.php'));
    exit;
}

// Fetch Order
$stmt = $db->prepare("SELECT * FROM `orders` WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    admin_set_flash('error', 'Order not found.');
    header("Location: " . admin_url('orders.php'));
    exit;
}

// Fetch Order Items
$stmt = $db->prepare("SELECT * FROM `order_items` WHERE order_id = ? ORDER BY id ASC");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();

// Handle Status & Notes Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('order-view.php?id=' . $orderId));
        exit;
    }

    $newStatus = clean($_POST['order_status'] ?? $order['order_status']);
    $adminNotes = clean($_POST['admin_notes'] ?? '');
    $notifyCustomer = isset($_POST['notify_customer']) ? 1 : 0;
    $oldStatus = $order['order_status'];

    try {
        $stmt = $db->prepare("UPDATE `orders` SET order_status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $adminNotes, $orderId]);

        // If status changed, record in history
        if ($newStatus !== $oldStatus) {
            $hStmt = $db->prepare("INSERT INTO `order_status_history` (order_id, old_status, new_status, notes, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $hNotes = "Status updated from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus);
            $hStmt->execute([$orderId, $oldStatus, $newStatus, $hNotes, $_SESSION['admin_id'] ?? 1]);

            log_admin_activity('Update Order Status', "Changed order #{$order['order_number']} status to $newStatus");

            // Dispatch customer email if requested
            if ($notifyCustomer && !empty($order['customer_email'])) {
                $statusSubject = "Order Status Update: #" . $order['order_number'] . " is now " . ucfirst($newStatus);
                $statusBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;'>
                        <h2 style='color: #0f172a; margin-top: 0;'>Hello " . htmlspecialchars($order['customer_name']) . ",</h2>
                        <p style='color: #475569; font-size: 15px;'>Your order <strong>#" . htmlspecialchars($order['order_number']) . "</strong> has been updated to:</p>
                        <div style='background: #f8fafc; border-left: 4px solid #2271b1; padding: 12px 16px; margin: 20px 0;'>
                            <h3 style='margin: 0; color: #2271b1; text-transform: capitalize;'>" . htmlspecialchars($newStatus) . "</h3>
                        </div>
                        <p style='color: #475569; font-size: 14px;'>You can review your order receipt and tracking status anytime on our website:</p>
                        <p><a href='" . url('order-confirmation.php?order=' . urlencode($order['order_number'])) . "' style='background: #0f172a; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 4px; display: inline-block; font-weight: 600;'>View Order Details</a></p>
                        <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;'>
                        <p style='font-size: 12px; color: #94a3b8; margin: 0;'>ONS SOUND — Professional Audio Equipment & Technical Consultation</p>
                    </div>
                ";
                @send_email($order['customer_email'], $order['customer_name'], $statusSubject, $statusBody);
            }
        }

        admin_set_flash('success', "Order #{$order['order_number']} updated successfully.");
        header("Location: " . admin_url('order-view.php?id=' . $orderId));
        exit;
    } catch (Exception $e) {
        admin_set_flash('error', 'Error updating order: ' . $e->getMessage());
    }
}

// Fetch Status History
$stmt = $db->prepare("
    SELECT h.*, u.full_name AS admin_user_name 
    FROM `order_status_history` h
    LEFT JOIN `users` u ON u.id = h.created_by
    WHERE h.order_id = ?
    ORDER BY h.created_at DESC
");
$stmt->execute([$orderId]);
$statusHistory = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        Order #<?= htmlspecialchars($order['order_number']) ?>
        <span class="badge badge-<?= htmlspecialchars($order['order_status']) ?>" style="font-size: 12px; vertical-align: middle; margin-left: 8px;">
            <?= htmlspecialchars($order['order_status']) ?>
        </span>
    </h1>
    <a href="<?= admin_url('orders.php') ?>" class="page-title-action">Back to Orders</a>
    <a href="<?= url('order-confirmation.php?order=' . urlencode($order['order_number'])) ?>" target="_blank" class="page-title-action">
        <i class="bi bi-printer me-1"></i> Print / View Invoice
    </a>
    <hr class="wp-header-end">

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2" style="display: flex; gap: 20px; align-items: flex-start;">
            
            <!-- Left Column: Order Items & Notes (~68%) -->
            <div id="post-body-content" style="flex: 1; min-width: 0;">
                
                <!-- Order Items Postbox -->
                <div class="postbox" style="margin-bottom: 20px;">
                    <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 12px 15px;">
                        <h2 style="font-weight: 600; font-size: 14px; margin: 0;">Order Items Snapshot</h2>
                    </div>
                    <div class="inside" style="padding: 0;">
                        <table class="wp-list-table widefat fixed striped" style="border: none;">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Item</th>
                                    <th>Product Name & SKU</th>
                                    <th style="width: 100px; text-align: right;">Cost</th>
                                    <th style="width: 70px; text-align: center;">Qty</th>
                                    <th style="width: 110px; text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($orderItems)): ?>
                                    <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= url($item['product_image'] ?? 'assets/images/products/speaker-1.png') ?>" alt="" style="width: 38px; height: 38px; object-fit: contain; background: #fafafa; border: 1px solid #dcdcde; border-radius: 4px;">
                                            </td>
                                            <td>
                                                <strong>
                                                    <?php if (!empty($item['product_id'])): ?>
                                                        <a href="<?= admin_url('product-edit.php?id=' . $item['product_id']) ?>" style="text-decoration: none;">
                                                            <?= htmlspecialchars($item['product_name']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($item['product_name']) ?>
                                                    <?php endif; ?>
                                                </strong>
                                                <?php if (!empty($item['product_sku'])): ?>
                                                    <div style="font-size: 12px; color: #646970;">SKU: <?= htmlspecialchars($item['product_sku']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: right;"><?= format_price($item['price']) ?></td>
                                            <td style="text-align: center;">&times; <?= (int)$item['quantity'] ?></td>
                                            <td style="text-align: right;"><strong><?= format_price($item['subtotal']) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 20px; color: #646970;">No order items found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Totals Summary Table -->
                        <div style="padding: 15px 20px; background: #fafafa; border-top: 1px solid #e2e4e7; display: flex; justify-content: flex-end;">
                            <div style="width: 280px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px; color: #646970;">
                                    <span>Items Subtotal:</span>
                                    <span><?= format_price($order['subtotal']) ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px; color: #646970;">
                                    <span>Shipping Fee:</span>
                                    <span><?= ($order['shipping_fee'] > 0) ? format_price($order['shipping_fee']) : 'Free (Standard Ground)' ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; border-top: 1px solid #c3c4c7; padding-top: 8px; margin-top: 8px; font-size: 15px; font-weight: 700; color: #1d2327;">
                                    <span>Order Total:</span>
                                    <span style="color: var(--wp-primary);"><?= format_price($order['total_amount']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Notes Postbox -->
                <?php if (!empty($order['customer_notes'])): ?>
                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header">
                            <h2>Customer Provided Note / Special Instructions</h2>
                        </div>
                        <div class="inside" style="padding: 15px; font-size: 13px; color: #2c3338; background: #fffdf5; border-left: 4px solid #dba617;">
                            <?= nl2br(htmlspecialchars($order['customer_notes'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Status History Timeline Postbox -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>Order Activity & Status History</h2>
                    </div>
                    <div class="inside" style="padding: 15px;">
                        <?php if (!empty($statusHistory)): ?>
                            <ul style="list-style: none; margin: 0; padding: 0;">
                                <?php foreach ($statusHistory as $h): ?>
                                    <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f1; font-size: 13px;">
                                        <div style="display: flex; justify-content: space-between;">
                                            <div>
                                                <strong>Status:</strong> <span class="badge badge-<?= htmlspecialchars($h['new_status']) ?>" style="font-size: 11px; padding: 2px 8px;"><?= htmlspecialchars($h['new_status']) ?></span>
                                                <?php if (!empty($h['notes'])): ?>
                                                    <span style="color: #646970;">— <?= htmlspecialchars($h['notes']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div style="font-size: 12px; color: #8c8f94;">
                                                <?= date('M j, Y @ g:i a', strtotime($h['created_at'])) ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div style="font-size: 13px; color: #646970;">No previous status change recorded.</div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Right Column: Actions & Customer Info (~32%) -->
            <div id="postbox-container-1" style="width: 320px; flex-shrink: 0;">
                
                <!-- Order Actions / Status Changer -->
                <form method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" name="update_order" value="1">

                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7;">
                            <h2>Order Actions</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <div style="margin-bottom: 14px;">
                                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 5px;">Order Status:</label>
                                <select name="order_status" style="width: 100%; padding: 6px 10px; font-size: 13px; font-weight: 600;">
                                    <?php
                                    $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
                                    foreach ($statuses as $st):
                                    ?>
                                        <option value="<?= $st ?>" <?= ($order['order_status'] === $st) ? 'selected' : '' ?>>
                                            <?= ucfirst($st) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="margin-bottom: 14px;">
                                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 5px;">Internal Admin Notes:</label>
                                <textarea name="admin_notes" rows="3" style="width: 100%; font-size: 12px; padding: 6px;" placeholder="Private notes visible only to store administrators"><?= htmlspecialchars($order['admin_notes'] ?? '') ?></textarea>
                            </div>

                            <div style="margin-bottom: 16px;">
                                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px;">
                                    <input type="checkbox" name="notify_customer" value="1" checked>
                                    <span>Send status notification email to customer</span>
                                </label>
                            </div>

                            <div style="border-top: 1px solid #dcdcde; padding-top: 12px; text-align: right;">
                                <button type="submit" class="button button-primary button-large" style="width: 100%; font-weight: 600;">
                                    Update Order
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Customer Details Postbox -->
                <div class="postbox" style="margin-bottom: 20px;">
                    <div class="postbox-header">
                        <h2>Customer Information</h2>
                    </div>
                    <div class="inside" style="padding: 15px; font-size: 13px;">
                        <div style="margin-bottom: 8px;">
                            <strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>"><?= htmlspecialchars($order['customer_email']) ?></a>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>"><?= htmlspecialchars($order['customer_phone'] ?? '—') ?></a>
                        </div>
                        <?php if (!empty($order['user_id'])): ?>
                            <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 8px;">
                                <a href="<?= admin_url('customer-view.php?id=' . $order['user_id']) ?>" style="text-decoration: none; font-weight: 600;">
                                    <i class="bi bi-person-badge me-1"></i> View Registered Customer Profile
                                </a>
                            </div>
                        <?php else: ?>
                            <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 8px; color: #646970;">
                                <em>Guest Checkout Order</em>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Delivery Address Postbox -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>Shipping / Delivery Address</h2>
                    </div>
                    <div class="inside" style="padding: 15px; font-size: 13px; line-height: 1.6;">
                        <p style="margin: 0; color: #2c3338;">
                            <?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'No address provided')) ?>
                        </p>
                        <div style="margin-top: 12px; font-size: 12px; color: #646970;">
                            <strong>Payment Method:</strong> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method'] ?? 'Pay on Delivery'))) ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
