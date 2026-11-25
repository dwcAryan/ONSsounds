<?php
/**
 * ONS SOUND — WordPress-Style Admin Users Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Admin Users';

$db = get_db();
$currentAdminId = (int)($_SESSION['admin_id'] ?? 0);

// Handle Edit Fetch
$editId = (int)($_GET['edit'] ?? 0);
$editUser = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM `users` WHERE id = ? AND role = 'admin'");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch();
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_admin_user'])) {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed.');
        header("Location: " . admin_url('users.php'));
        exit;
    }

    $fullName = clean($_POST['full_name'] ?? '');
    $email = trim(strtolower(clean($_POST['email'] ?? '')));
    $phone = clean($_POST['phone'] ?? '');
    $status = clean($_POST['status'] ?? 'active');
    $password = $_POST['password'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if (empty($fullName) || empty($email)) {
        admin_set_flash('error', 'Full name and email address are required.');
    } else {
        try {
            // Check email uniqueness
            $chkStmt = $db->prepare("SELECT id FROM `users` WHERE email = ? AND id != ?");
            $chkStmt->execute([$email, $userId]);
            if ($chkStmt->fetchColumn()) {
                admin_set_flash('error', "An account with email '$email' already exists.");
            } else {
                if ($userId > 0) {
                    // Update Admin User
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE `users` SET full_name = ?, email = ?, phone = ?, status = ?, password_hash = ?, updated_at = NOW() WHERE id = ? AND role = 'admin'");
                        $stmt->execute([$fullName, $email, $phone, $status, $hash, $userId]);
                    } else {
                        $stmt = $db->prepare("UPDATE `users` SET full_name = ?, email = ?, phone = ?, status = ?, updated_at = NOW() WHERE id = ? AND role = 'admin'");
                        $stmt->execute([$fullName, $email, $phone, $status, $userId]);
                    }
                    log_admin_activity('Update Admin User', "Updated admin account #$userId ($fullName)");
                    admin_set_flash('success', "Administrator '$fullName' updated.");
                } else {
                    // Create New Admin User
                    if (empty($password)) {
                        admin_set_flash('error', 'A secure password is required for new administrator accounts.');
                        header("Location: " . admin_url('users.php'));
                        exit;
                    }
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        INSERT INTO `users` (
                            role, full_name, email, password_hash, phone, status, email_verified_at, created_at, updated_at
                        ) VALUES (
                            'admin', ?, ?, ?, ?, ?, NOW(), NOW(), NOW()
                        )
                    ");
                    $stmt->execute([$fullName, $email, $hash, $phone, $status]);
                    $newId = (int)$db->lastInsertId();
                    log_admin_activity('Create Admin User', "Created new administrator #$newId ($fullName)");
                    admin_set_flash('success', "New administrator '$fullName' added successfully.");
                }
                header("Location: " . admin_url('users.php'));
                exit;
            }
        } catch (Exception $e) {
            admin_set_flash('error', 'Error saving administrator: ' . $e->getMessage());
        }
    }
}

// Handle Delete (Cannot delete own logged-in account)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delId = (int)$_GET['id'];
    if ($delId === $currentAdminId) {
        admin_set_flash('error', 'You cannot delete your own active administrator account.');
    } else {
        $db->prepare("DELETE FROM `users` WHERE id = ? AND role = 'admin'")->execute([$delId]);
        log_admin_activity('Delete Admin User', "Deleted administrator account #$delId");
        admin_set_flash('success', "Administrator account deleted.");
    }
    header("Location: " . admin_url('users.php'));
    exit;
}

// Fetch all admin accounts
$admins = $db->query("SELECT * FROM `users` WHERE role = 'admin' ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Admin Users</h1>
    <hr class="wp-header-end">

    <div id="col-container" style="display: flex; gap: 25px; margin-top: 15px; align-items: flex-start;">
        
        <!-- Left Column: Add / Edit Admin Form (~38%) -->
        <div id="col-left" style="width: 360px; flex-shrink: 0;">
            <div class="form-wrap" style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px;">
                <h2 style="font-size: 16px; font-weight: 600; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px;">
                    <?= $editUser ? 'Edit Administrator' : 'Add New Administrator' ?>
                </h2>

                <form method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_admin_user" value="1">
                    <?php if ($editUser): ?>
                        <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                    <?php endif; ?>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Full Name *</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($editUser['full_name'] ?? '') ?>" required style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Email Address *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" required style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Phone Number</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($editUser['phone'] ?? '') ?>" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">
                            Password <?= $editUser ? '<span style="font-weight: normal; color: #646970;">(leave blank to keep current)</span>' : '*' ?>
                        </label>
                        <input type="password" name="password" <?= $editUser ? '' : 'required' ?> style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;" placeholder="<?= $editUser ? '••••••••' : 'Enter secure password' ?>">
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Account Status</label>
                        <select name="status" style="width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px;">
                            <option value="active" <?= (($editUser['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (($editUser['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive / Suspended</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="submit" class="button button-primary">
                            <?= $editUser ? 'Update Administrator' : 'Add Administrator' ?>
                        </button>
                        <?php if ($editUser): ?>
                            <a href="<?= admin_url('users.php') ?>" class="button button-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Administrators List Table (~62%) -->
        <div id="col-right" style="flex: 1; min-width: 0;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 45px;"></th>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th style="width: 90px;">Status</th>
                        <th style="width: 120px;">Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($admins)): ?>
                        <?php foreach ($admins as $a): ?>
                            <tr>
                                <td>
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #2271b1; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px;">
                                        <?= strtoupper(substr($a['full_name'], 0, 1)) ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><a href="<?= admin_url('users.php?edit=' . $a['id']) ?>" class="row-title"><?= htmlspecialchars($a['full_name']) ?></a></strong>
                                    <?php if ($a['id'] === $currentAdminId): ?>
                                        <span style="font-size: 11px; color: #007017; font-weight: 600; margin-left: 5px;">(You)</span>
                                    <?php endif; ?>
                                    <div class="row-actions">
                                        <span class="edit"><a href="<?= admin_url('users.php?edit=' . $a['id']) ?>">Edit</a></span>
                                        <?php if ($a['id'] !== $currentAdminId): ?>
                                            | <span class="trash"><a href="<?= admin_url('users.php?action=delete&id=' . $a['id']) ?>" onclick="return confirm('Permanently remove administrator <?= htmlspecialchars($a['full_name']) ?>?');" style="color: #d63638;">Delete</a></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($a['email']) ?>"><?= htmlspecialchars($a['email']) ?></a>
                                </td>
                                <td><?= htmlspecialchars($a['phone'] ?: '—') ?></td>
                                <td>
                                    <span class="badge badge-<?= ($a['status'] === 'active') ? 'active' : 'suspended' ?>">
                                        <?= ucfirst(htmlspecialchars($a['status'])) ?>
                                    </span>
                                </td>
                                <td style="font-size: 12px; color: #646970;">
                                    <?= date('M j, Y', strtotime($a['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 25px; color: #646970;">No administrators found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
