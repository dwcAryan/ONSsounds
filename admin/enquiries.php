<?php
/**
 * ONS SOUND — WordPress-Style Contact Enquiries Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Contact Messages';

$db = get_db();

// Handle Single Actions
if (isset($_GET['action']) && !empty($_GET['id'])) {
    $action = clean($_GET['action']);
    $enqId = (int)$_GET['id'];

    if ($action === 'mark_read') {
        $db->prepare("UPDATE `contact_enquiries` SET status = 'read' WHERE id = ?")->execute([$enqId]);
        log_admin_activity('Enquiry Status', "Marked enquiry #$enqId as read");
        admin_set_flash('success', "Message #$enqId marked as read.");
    } elseif ($action === 'mark_unread') {
        $db->prepare("UPDATE `contact_enquiries` SET status = 'new' WHERE id = ?")->execute([$enqId]);
        log_admin_activity('Enquiry Status', "Marked enquiry #$enqId as unread");
        admin_set_flash('success', "Message #$enqId marked as unread.");
    } elseif ($action === 'archive') {
        $db->prepare("UPDATE `contact_enquiries` SET status = 'archived' WHERE id = ?")->execute([$enqId]);
        log_admin_activity('Enquiry Status', "Archived enquiry #$enqId");
        admin_set_flash('success', "Message #$enqId archived.");
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM `contact_enquiries` WHERE id = ?")->execute([$enqId]);
        log_admin_activity('Delete Enquiry', "Deleted enquiry #$enqId");
        admin_set_flash('success', "Message #$enqId permanently deleted.");
    }
    header("Location: " . admin_url('enquiries.php'));
    exit;
}

// Handle Bulk Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && !empty($_POST['post'])) {
    $action = clean($_POST['action']);
    $ids = array_map('intval', $_POST['post']);

    if (!empty($ids)) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'mark_read') {
            $db->prepare("UPDATE `contact_enquiries` SET status = 'read' WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Update Enquiries', "Marked " . count($ids) . " enquiries as read.");
            admin_set_flash('success', count($ids) . " messages marked as read.");
        } elseif ($action === 'archive') {
            $db->prepare("UPDATE `contact_enquiries` SET status = 'archived' WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Archive Enquiries', "Archived " . count($ids) . " enquiries.");
            admin_set_flash('success', count($ids) . " messages archived.");
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM `contact_enquiries` WHERE id IN ($in)")->execute($ids);
            log_admin_activity('Bulk Delete Enquiries', "Deleted " . count($ids) . " enquiries.");
            admin_set_flash('success', count($ids) . " messages deleted.");
        }
    }
    header("Location: " . admin_url('enquiries.php'));
    exit;
}

// Check if viewing single message
$viewId = (int)($_GET['view'] ?? 0);
$viewEnquiry = null;
if ($viewId > 0) {
    $stmt = $db->prepare("SELECT * FROM `contact_enquiries` WHERE id = ?");
    $stmt->execute([$viewId]);
    $viewEnquiry = $stmt->fetch();

    if ($viewEnquiry && $viewEnquiry['status'] === 'new') {
        // Automatically mark as read
        $db->prepare("UPDATE `contact_enquiries` SET status = 'read' WHERE id = ?")->execute([$viewId]);
        $viewEnquiry['status'] = 'read';
    }
}

// Counts
$countAll = (int)$db->query("SELECT COUNT(*) FROM `contact_enquiries`")->fetchColumn();
$countNew = (int)$db->query("SELECT COUNT(*) FROM `contact_enquiries` WHERE status = 'new'")->fetchColumn();
$countRead = (int)$db->query("SELECT COUNT(*) FROM `contact_enquiries` WHERE status = 'read'")->fetchColumn();
$countArchived = (int)$db->query("SELECT COUNT(*) FROM `contact_enquiries` WHERE status = 'archived'")->fetchColumn();

// Filter & Search
$statusFilter = clean($_GET['status'] ?? 'all');
$search = clean($_GET['s'] ?? '');
$page = max(1, (int)($_GET['paged'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if (in_array($statusFilter, ['new', 'read', 'archived'])) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

$whereSql = implode(' AND ', $where);

// Count matching
$countStmt = $db->prepare("SELECT COUNT(*) FROM `contact_enquiries` WHERE $whereSql");
$countStmt->execute($params);
$totalEnquiries = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalEnquiries / $perPage);

// Fetch
$query = "SELECT * FROM `contact_enquiries` WHERE $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$enquiries = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Contact Enquiries</h1>
    <hr class="wp-header-end">

    <?php if ($viewEnquiry): ?>
        <!-- Single Message View Box -->
        <div class="postbox" style="margin-bottom: 25px; border-left: 4px solid var(--wp-primary);">
            <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 15px; font-weight: 600; margin: 0;">
                    Message from <?= htmlspecialchars($viewEnquiry['name']) ?>
                </h2>
                <div>
                    <a href="<?= admin_url('enquiries.php') ?>" class="button button-small">&larr; Close / Back to List</a>
                </div>
            </div>
            <div class="inside" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 16px; border-bottom: 1px solid #eee; padding-bottom: 14px;">
                    <div>
                        <div style="font-size: 16px; font-weight: 700; color: #1d2327; margin-bottom: 4px;"><?= htmlspecialchars($viewEnquiry['name']) ?></div>
                        <div style="font-size: 13px; color: #646970;">
                            Email: <a href="mailto:<?= htmlspecialchars($viewEnquiry['email']) ?>"><?= htmlspecialchars($viewEnquiry['email']) ?></a>
                            <?php if (!empty($viewEnquiry['phone'])): ?>
                                &bull; Phone: <a href="tel:<?= htmlspecialchars($viewEnquiry['phone']) ?>"><?= htmlspecialchars($viewEnquiry['phone']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align: right; font-size: 12px; color: #8c8f94;">
                        Received on: <?= date('F j, Y @ g:i a', strtotime($viewEnquiry['created_at'])) ?><br>
                        Source: <code><?= htmlspecialchars($viewEnquiry['source'] ?? 'contact_page') ?></code>
                    </div>
                </div>

                <div style="font-size: 14px; line-height: 1.7; color: #2c3338; margin-bottom: 25px; background: #fafafa; padding: 16px; border-radius: 4px; border: 1px solid #e2e4e7;">
                    <?= nl2br(htmlspecialchars($viewEnquiry['message'])) ?>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="mailto:<?= htmlspecialchars($viewEnquiry['email']) ?>?subject=Re:%20ONS%20SOUND%20Inquiry" class="button button-primary">
                        <i class="bi bi-reply me-1"></i> Reply via Email
                    </a>
                    <?php if ($viewEnquiry['status'] !== 'new'): ?>
                        <a href="<?= admin_url('enquiries.php?action=mark_unread&id=' . $viewEnquiry['id']) ?>" class="button button-secondary">Mark as Unread</a>
                    <?php endif; ?>
                    <a href="<?= admin_url('enquiries.php?action=delete&id=' . $viewEnquiry['id']) ?>" onclick="return confirm('Permanently delete this enquiry?');" class="button button-secondary" style="color: #d63638;">Delete</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Status Subsubsub Bar -->
    <ul class="subsubsub">
        <li class="all"><a href="<?= admin_url('enquiries.php') ?>" class="<?= ($statusFilter === 'all') ? 'current' : '' ?>">All <span class="count">(<?= $countAll ?>)</span></a> |</li>
        <li class="new"><a href="<?= admin_url('enquiries.php?status=new') ?>" class="<?= ($statusFilter === 'new') ? 'current' : '' ?>">New / Unread <span class="count">(<?= $countNew ?>)</span></a> |</li>
        <li class="read"><a href="<?= admin_url('enquiries.php?status=read') ?>" class="<?= ($statusFilter === 'read') ? 'current' : '' ?>">Read <span class="count">(<?= $countRead ?>)</span></a> |</li>
        <li class="archived"><a href="<?= admin_url('enquiries.php?status=archived') ?>" class="<?= ($statusFilter === 'archived') ? 'current' : '' ?>">Archived <span class="count">(<?= $countArchived ?>)</span></a></li>
    </ul>

    <!-- Search Form -->
    <form method="GET" action="<?= admin_url('enquiries.php') ?>" style="margin-bottom: 12px; display: flex; justify-content: flex-end;">
        <?php if ($statusFilter !== 'all'): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
        <?php endif; ?>
        <p class="search-box" style="margin: 0; display: flex; gap: 5px;">
            <input type="search" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Search messages..." style="padding: 4px 8px; font-size: 13px; border: 1px solid #8c8f94; border-radius: 4px;">
            <input type="submit" class="button" value="Search Enquiries">
        </p>
    </form>

    <form method="POST" action="<?= admin_url('enquiries.php') ?>">
        <div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div class="alignleft actions bulkactions" style="display: flex; gap: 6px;">
                <select name="action">
                    <option value="">Bulk Actions</option>
                    <option value="mark_read">Mark Read</option>
                    <option value="archive">Archive</option>
                    <option value="delete">Delete Permanently</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
            <div class="tablenav-pages" style="font-size: 13px; color: #646970;">
                <span class="displaying-num"><?= $totalEnquiries ?> enquiries</span>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column" style="width: 40px; padding-left: 10px;">
                        <input id="cb-select-all-1" type="checkbox" onclick="document.querySelectorAll('.enq-select').forEach(cb => cb.checked = this.checked)">
                    </td>
                    <th style="width: 170px;">Sender</th>
                    <th style="width: 190px;">Contact</th>
                    <th>Message</th>
                    <th style="width: 90px;">Status</th>
                    <th style="width: 130px;">Received On</th>
                    <th style="width: 70px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($enquiries)): ?>
                    <?php foreach ($enquiries as $e): ?>
                        <tr style="<?= ($e['status'] === 'new') ? 'font-weight: 600; background: #fdfdfd;' : '' ?>">
                            <th scope="row" class="check-column" style="padding-left: 10px;">
                                <input type="checkbox" name="post[]" value="<?= $e['id'] ?>" class="enq-select">
                            </th>
                            <td>
                                <strong>
                                    <a href="<?= admin_url('enquiries.php?view=' . $e['id']) ?>" class="row-title">
                                        <?= htmlspecialchars($e['name']) ?>
                                    </a>
                                </strong>
                                <div class="row-actions">
                                    <span class="view"><a href="<?= admin_url('enquiries.php?view=' . $e['id']) ?>">Read</a> | </span>
                                    <?php if ($e['status'] === 'new'): ?>
                                        <span class="mark"><a href="<?= admin_url('enquiries.php?action=mark_read&id=' . $e['id']) ?>">Mark Read</a> | </span>
                                    <?php else: ?>
                                        <span class="mark"><a href="<?= admin_url('enquiries.php?action=mark_unread&id=' . $e['id']) ?>">Mark Unread</a> | </span>
                                    <?php endif; ?>
                                    <span class="trash"><a href="<?= admin_url('enquiries.php?action=delete&id=' . $e['id']) ?>" onclick="return confirm('Delete this message?');" style="color: #d63638;">Delete</a></span>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($e['email']) ?>"><?= htmlspecialchars($e['email']) ?></a>
                                <?php if (!empty($e['phone'])): ?>
                                    <div style="font-size: 12px; color: #646970; font-weight: normal;"><?= htmlspecialchars($e['phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 13px; color: #2c3338;">
                                    <?= htmlspecialchars(mb_strimwidth($e['message'], 0, 95, '...')) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?= ($e['status'] === 'new') ? 'cancelled' : (($e['status'] === 'read') ? 'active' : 'secondary') ?>">
                                    <?= ucfirst(htmlspecialchars($e['status'])) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: #646970; font-weight: normal;">
                                <?= date('Y/m/d \a\t g:i a', strtotime($e['created_at'])) ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= admin_url('enquiries.php?view=' . $e['id']) ?>" class="button button-small" title="Read Message">
                                    <i class="bi bi-envelope-open"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 25px; color: #646970;">No enquiries found.</td>
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
                            <a class="button button-small <?= ($pg == $page) ? 'button-primary' : '' ?>" href="<?= admin_url('enquiries.php?paged=' . $pg . (!empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '') . (!empty($search) ? '&s=' . urlencode($search) : '')) ?>">
                                <?= $pg ?>
                            </a>
                        <?php endfor; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
