<?php
/**
 * ONS SOUND — WordPress-Style Products List Management
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Products';
require_once __DIR__ . '/../models/Category.php';

$db = get_db();
$catModel = new Category();
$categories = $catModel->getActive();

// Handle Single Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT name FROM `products` WHERE id = ?");
    $stmt->execute([$delId]);
    $pName = $stmt->fetchColumn();

    if ($pName) {
        $db->prepare("DELETE FROM `products` WHERE id = ?")->execute([$delId]);
        log_admin_activity('Delete Product', "Deleted product #$delId: $pName");
        admin_set_flash('success', "Product '$pName' permanently deleted.");
    }
    header("Location: " . admin_url('products.php'));
    exit;
}

// Handle Bulk Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && !empty($_POST['post'])) {
    $action = clean($_POST['action']);
    $ids = array_map('intval', $_POST['post']);

    if ($action === 'delete' && !empty($ids)) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $db->prepare("DELETE FROM `products` WHERE id IN ($in)")->execute($ids);
        log_admin_activity('Bulk Delete Products', "Deleted " . count($ids) . " products.");
        admin_set_flash('success', count($ids) . " products permanently deleted.");
        header("Location: " . admin_url('products.php'));
        exit;
    }
}

// Filters & Pagination
$statusFilter = clean($_GET['stock_status'] ?? 'all');
$catFilter = (int)($_GET['cat'] ?? 0);
$search = clean($_GET['s'] ?? '');
$page = max(1, (int)($_GET['paged'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if ($statusFilter === 'instock') {
    $where[] = "p.stock_quantity > 0";
} elseif ($statusFilter === 'outofstock') {
    $where[] = "p.stock_quantity <= 0";
}

if ($catFilter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $catFilter;
}

if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.model_name LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
}

$whereSql = "WHERE " . implode(' AND ', $where);

// Counts for subsubsub
$totalCount = (int)$db->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
$inStockCount = (int)$db->query("SELECT COUNT(*) FROM `products` WHERE stock_quantity > 0")->fetchColumn();
$outStockCount = (int)$db->query("SELECT COUNT(*) FROM `products` WHERE stock_quantity <= 0")->fetchColumn();

// Filtered count
$countStmt = $db->prepare("SELECT COUNT(*) FROM `products` p $whereSql");
$countStmt->execute($params);
$filteredCount = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($filteredCount / $perPage));

// Fetch products
$sql = "SELECT p.*, c.name AS category_name 
        FROM `products` p 
        LEFT JOIN `categories` c ON p.category_id = c.id 
        $whereSql 
        ORDER BY p.id DESC 
        LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<h1 class="wp-heading-inline">Products</h1>
<a href="<?= admin_url('product-edit.php') ?>" class="page-title-action">Add New Product</a>
<hr class="wp-header-end">

<!-- Filter Views (All | In Stock | Out of Stock) -->
<ul class="subsubsub">
    <li class="all">
        <a href="<?= admin_url('products.php') ?>" class="<?= ($statusFilter === 'all') ? 'current' : '' ?>">
            All <span class="count">(<?= $totalCount ?>)</span>
        </a> |
    </li>
    <li class="instock">
        <a href="<?= admin_url('products.php?stock_status=instock') ?>" class="<?= ($statusFilter === 'instock') ? 'current' : '' ?>">
            In Stock <span class="count">(<?= $inStockCount ?>)</span>
        </a> |
    </li>
    <li class="outofstock">
        <a href="<?= admin_url('products.php?stock_status=outofstock') ?>" class="<?= ($statusFilter === 'outofstock') ? 'current' : '' ?>">
            Out of Stock <span class="count">(<?= $outStockCount ?>)</span>
        </a>
    </li>
</ul>

<!-- Top Table Navigation & Filters -->
<form method="GET" action="<?= admin_url('products.php') ?>">
    <?php if ($statusFilter !== 'all'): ?>
        <input type="hidden" name="stock_status" value="<?= e($statusFilter) ?>">
    <?php endif; ?>

    <div class="tablenav top">
        <div class="alignleft actions">
            <select name="cat">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($catFilter == $cat['id']) ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" class="button button-secondary" value="Filter">
        </div>

        <div style="display:flex; align-items:center; gap: 4px;">
            <input type="search" name="s" value="<?= e($search) ?>" placeholder="Search products...">
            <input type="submit" class="button button-secondary" value="Search Products">
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="tablenav-pages">
                <span>Page <?= $page ?> of <?= $totalPages ?> (<?= $filteredCount ?> items)</span>
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['paged' => $page - 1])) ?>">&lsaquo;</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['paged' => $page + 1])) ?>">&rsaquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</form>

<!-- Products Table Form for Bulk Actions -->
<form method="POST" action="<?= admin_url('products.php') ?>">
    <div class="tablenav top" style="margin-top: 0;">
        <div class="alignleft actions bulkactions">
            <select name="action">
                <option value="-1">Bulk actions</option>
                <option value="delete">Delete permanently</option>
            </select>
            <input type="submit" class="button button-secondary action" value="Apply" onclick="return confirm('Apply bulk action to selected products?');">
        </div>
    </div>

    <table class="wp-list-table striped">
        <thead>
            <tr>
                <td class="check-column"><input type="checkbox" id="cb-select-all-1"></td>
                <th style="width: 50px;">Image</th>
                <th>Name</th>
                <th>SKU</th>
                <th>Stock</th>
                <th>Price</th>
                <th>Category</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="9" style="text-align:center; padding: 20px; color: #646970;">No precision audio products found.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox" name="post[]" value="<?= (int)$prod['id'] ?>">
                        </th>
                        <td>
                            <img src="<?= asset($prod['main_image']) ?>" alt="" style="width: 44px; height: 44px; object-fit: contain; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 3px; padding: 2px;">
                        </td>
                        <td>
                            <strong>
                                <a href="<?= admin_url('product-edit.php?id=' . (int)$prod['id']) ?>" style="color: var(--wp-primary); text-decoration: none; font-size: 14px;">
                                    <?= e($prod['name']) ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span><a href="<?= admin_url('product-edit.php?id=' . (int)$prod['id']) ?>">Edit</a> | </span>
                                <span><a href="<?= url('product-details.php?slug=' . urlencode($prod['slug'])) ?>" target="_blank">View</a> | </span>
                                <span class="trash"><a href="<?= admin_url('products.php?action=delete&id=' . (int)$prod['id']) ?>" class="submitdelete" data-confirm="Are you sure you want to permanently delete '<?= e($prod['name']) ?>'?">Delete</a></span>
                            </div>
                        </td>
                        <td><code><?= e($prod['sku'] ?: '—') ?></code></td>
                        <td>
                            <?php if ($prod['stock_quantity'] > 0): ?>
                                <span class="badge badge-active">In stock (<?= (int)$prod['stock_quantity'] ?>)</span>
                            <?php else: ?>
                                <span class="badge badge-cancelled">Out of stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color: #1d2327;"><?= format_price($prod['price']) ?></strong>
                            <?php if (!empty($prod['compare_price']) && $prod['compare_price'] > $prod['price']): ?>
                                <del class="text-muted small" style="display:block;"><?= format_price($prod['compare_price']) ?></del>
                            <?php endif; ?>
                        </td>
                        <td><?= e($prod['category_name'] ?: 'Uncategorized') ?></td>
                        <td>
                            <span class="status-pill status-<?= $prod['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $prod['is_active'] ? 'Published' : 'Draft' ?>
                            </span>
                        </td>
                        <td><?= date('Y/m/d', strtotime($prod['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
