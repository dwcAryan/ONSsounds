<?php
/**
 * ONS SOUND — WordPress/WooCommerce-Style Coupons Manager
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Discount Coupons';

$db = get_db();

// Handle Edit Fetch
$editId = (int)($_GET['edit'] ?? 0);
$editCoupon = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM `coupons` WHERE id = ?");
    $stmt->execute([$editId]);
    $editCoupon = $stmt->fetch();
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_coupon'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('coupons.php'));
        exit;
    }

    $code = strtoupper(trim(clean($_POST['code'] ?? '')));
    $type = clean($_POST['type'] ?? 'percentage');
    $discountValue = (float)($_POST['discount_value'] ?? 0);
    $minSpend = !empty($_POST['min_spend']) ? (float)$_POST['min_spend'] : 0.00;
    $maxDiscount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
    $usageLimit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
    $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $couponId = (int)($_POST['coupon_id'] ?? 0);

    if (empty($code) || $discountValue <= 0) {
        admin_set_flash('error', 'Coupon code and a valid discount value are required.');
    } else {
        try {
            if ($couponId > 0) {
                $stmt = $db->prepare("
                    UPDATE `coupons` SET 
                        code = ?, type = ?, discount_value = ?, min_spend = ?, 
                        max_discount = ?, usage_limit = ?, expires_at = ?, is_active = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$code, $type, $discountValue, $minSpend, $maxDiscount, $usageLimit, $expiresAt, $isActive, $couponId]);
                log_admin_activity('Update Coupon', "Updated coupon code '$code'");
                admin_set_flash('success', "Coupon '$code' updated.");
            } else {
                $stmt = $db->prepare("
                    INSERT INTO `coupons` (
                        code, type, discount_value, min_spend, max_discount, usage_limit, usage_count, expires_at, is_active, created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, 0, ?, ?, NOW()
                    )
                ");
                $stmt->execute([$code, $type, $discountValue, $minSpend, $maxDiscount, $usageLimit, $expiresAt, $isActive]);
                log_admin_activity('Create Coupon', "Added coupon code '$code'");
                admin_set_flash('success', "Coupon '$code' created.");
            }
            header("Location: " . admin_url('coupons.php'));
            exit;
        } catch (Exception $e) {
            admin_set_flash('error', 'Error saving coupon: ' . $e->getMessage());
        }
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $db->prepare("DELETE FROM `coupons` WHERE id = ?")->execute([$delId]);
    log_admin_activity('Delete Coupon', "Deleted coupon #$delId");
    admin_set_flash('success', "Coupon deleted.");
    header("Location: " . admin_url('coupons.php'));
    exit;
}

// Fetch all coupons
$coupons = $db->query("SELECT * FROM `coupons` ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Discount Coupons</h1>
    <hr class="wp-header-end">

    <div id="col-container" style="display: flex; gap: 25px; margin-top: 15px; align-items: flex-start;">
        
        <!-- Left Column: Add / Edit Form (~38%) -->
        <div id="col-left" style="width: 360px; flex-shrink: 0;">
            <div class="form-wrap" style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px;">
                <h2 style="font-size: 16px; font-weight: 600; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px;">
                    <?= $editCoupon ? 'Edit Coupon' : 'Add New Coupon' ?>
                </h2>

                <form method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_coupon" value="1">
                    <?php if ($editCoupon): ?>
                        <input type="hidden" name="coupon_id" value="<?= $editCoupon['id'] ?>">
                    <?php endif; ?>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Coupon Code *</label>
                        <input type="text" name="code" value="<?= htmlspecialchars($editCoupon['code'] ?? '') ?>" required style="width: 100%; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;" placeholder="e.g. WELCOME10">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Discount Type</label>
                        <select name="type" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                            <option value="percentage" <?= (($editCoupon['type'] ?? 'percentage') === 'percentage') ? 'selected' : '' ?>>Percentage discount (%)</option>
                            <option value="fixed" <?= (($editCoupon['type'] ?? '') === 'fixed') ? 'selected' : '' ?>>Fixed cart discount (₹)</option>
                        </select>
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Coupon Amount / Value *</label>
                        <input type="number" step="0.01" min="0" name="discount_value" value="<?= htmlspecialchars($editCoupon['discount_value'] ?? '10') ?>" required style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                        <p class="description" style="font-size: 12px; color: #646970; margin-top: 4px;">Percentage (e.g. 20 for 20%) or flat rupee value.</p>
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Minimum Spend (₹)</label>
                        <input type="number" step="0.01" min="0" name="min_spend" value="<?= htmlspecialchars($editCoupon['min_spend'] ?? '0') ?>" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Usage Limit</label>
                        <input type="number" min="1" name="usage_limit" value="<?= htmlspecialchars($editCoupon['usage_limit'] ?? '') ?>" placeholder="Unlimited" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Coupon Expiry Date</label>
                        <input type="date" name="expires_at" value="<?= !empty($editCoupon['expires_at']) ? date('Y-m-d', strtotime($editCoupon['expires_at'])) : '' ?>" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= (!isset($editCoupon['is_active']) || $editCoupon['is_active'] == 1) ? 'checked' : '' ?>>
                            <span style="font-weight: 600;">Active / Usable at Checkout</span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="submit" class="button button-primary">
                            <?= $editCoupon ? 'Update Coupon' : 'Create Coupon' ?>
                        </button>
                        <?php if ($editCoupon): ?>
                            <a href="<?= admin_url('coupons.php') ?>" class="button button-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Coupons List Table (~62%) -->
        <div id="col-right" style="flex: 1; min-width: 0;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th style="width: 120px;">Coupon Type</th>
                        <th style="width: 110px;">Coupon Amount</th>
                        <th style="width: 100px;">Usage / Limit</th>
                        <th style="width: 120px;">Expiry Date</th>
                        <th style="width: 80px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($coupons)): ?>
                        <?php foreach ($coupons as $c): ?>
                            <tr>
                                <td>
                                    <strong><a href="<?= admin_url('coupons.php?edit=' . $c['id']) ?>" class="row-title" style="letter-spacing: 0.5px;"><?= htmlspecialchars($c['code']) ?></a></strong>
                                    <div class="row-actions">
                                        <span class="edit"><a href="<?= admin_url('coupons.php?edit=' . $c['id']) ?>">Edit</a> | </span>
                                        <span class="trash"><a href="<?= admin_url('coupons.php?action=delete&id=' . $c['id']) ?>" onclick="return confirm('Permanently delete coupon <?= htmlspecialchars($c['code']) ?>?');" style="color: #d63638;">Delete</a></span>
                                    </div>
                                </td>
                                <td><?= ($c['type'] === 'percentage') ? 'Percentage discount' : 'Fixed cart discount' ?></td>
                                <td>
                                    <strong>
                                        <?= ($c['type'] === 'percentage') ? ((float)$c['discount_value'] . '%') : format_price($c['discount_value']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <?= (int)$c['usage_count'] ?> / <?= !empty($c['usage_limit']) ? (int)$c['usage_limit'] : '&infin;' ?>
                                </td>
                                <td style="font-size: 12px; color: #646970;">
                                    <?= !empty($c['expires_at']) ? date('M j, Y', strtotime($c['expires_at'])) : 'Never expires' ?>
                                </td>
                                <td>
                                    <?php if (!empty($c['is_active'])): ?>
                                        <span class="badge" style="background: #007017;">Active</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #787c82;">Disabled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 25px; color: #646970;">No coupons found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
