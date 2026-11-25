<?php
/**
 * ONS SOUND - Products Catalog Page
 * Strictly matching ref_img/products.png
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Category.php';

$pageTitle = 'Products';
$pageDescription = 'Browse all ONS SOUND precision speaker transducers, subwoofers, and audio components.';

$productModel = new Product();
$categoryModel = new Category();

$categories = $categoryModel->getActive();

// Filters & Sorting parameters
$selectedCategorySlug = clean($_GET['category'] ?? '');
$searchQuery = clean($_GET['q'] ?? '');
$sort = clean($_GET['sort'] ?? 'default');
$colView = (int)($_GET['cols'] ?? 4);
if (!in_array($colView, [2, 3, 4, 5])) {
    $colView = 4;
}

$filters = [];
if (!empty($selectedCategorySlug)) {
    $cat = $categoryModel->getBySlug($selectedCategorySlug);
    if ($cat) {
        $filters['category_id'] = $cat['id'];
    }
}
if (!empty($searchQuery)) {
    $filters['search'] = $searchQuery;
}

$perPage = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$totalProducts = $productModel->getCatalogCount($filters);
$totalPages = max(1, (int)ceil($totalProducts / $perPage));
if ($page > $totalPages && $totalProducts > 0) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$products = $productModel->getCatalog($filters, $sort, $perPage, $offset);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Products Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">Products</h1>
    </div>
</section>

<!-- Filter & Layout Toolbar Matching ref_img/products.png -->
<section class="border-top border-bottom py-3 bg-white mb-4">
    <div class="container-fluid px-3 px-lg-5">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            
            <!-- Left: Filter Toggle Button -->
            <div>
                <button class="btn btn-outline-dark rounded-1 px-3 py-2 fw-semibold fs-7 text-uppercase" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#filterDrawer" 
                        aria-expanded="false" 
                        aria-controls="filterDrawer">
                    <i class="bi bi-sliders me-2"></i>Filter
                </button>
            </div>

            <!-- Center: View Mode Column Selectors -->
            <div class="d-none d-md-flex align-items-center gap-2 text-muted">
                <a href="?<?= http_build_query(array_merge($_GET, ['cols' => 2])) ?>" 
                   class="text-dark fs-6 px-1 <?= ($colView === 2) ? 'fw-bold text-black' : 'text-muted' ?>" 
                   title="2 Columns">
                    <i class="bi bi-pause"></i>
                </a>
                <a href="?<?= http_build_query(array_merge($_GET, ['cols' => 3])) ?>" 
                   class="text-dark fs-6 px-1 <?= ($colView === 3) ? 'fw-bold text-black' : 'text-muted' ?>" 
                   title="3 Columns">
                    <i class="bi bi-grid-3x2-gap"></i>
                </a>
                <a href="?<?= http_build_query(array_merge($_GET, ['cols' => 4])) ?>" 
                   class="text-dark fs-6 px-1 <?= ($colView === 4) ? 'fw-bold text-black' : 'text-muted' ?>" 
                   title="4 Columns">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </a>
                <a href="?<?= http_build_query(array_merge($_GET, ['cols' => 5])) ?>" 
                   class="text-dark fs-6 px-1 <?= ($colView === 5) ? 'fw-bold text-black' : 'text-muted' ?>" 
                   title="5 Columns">
                    <i class="bi bi-grid"></i>
                </a>
            </div>

            <!-- Right: Sort Dropdown -->
            <div>
                <form action="" method="GET" id="sortForm" class="d-inline-block">
                    <?php if (!empty($selectedCategorySlug)): ?>
                        <input type="hidden" name="category" value="<?= e($selectedCategorySlug) ?>">
                    <?php endif; ?>
                    <?php if (!empty($searchQuery)): ?>
                        <input type="hidden" name="q" value="<?= e($searchQuery) ?>">
                    <?php endif; ?>
                    <input type="hidden" name="cols" value="<?= $colView ?>">
                    
                    <select name="sort" class="form-select border rounded-1 py-2 px-3 fw-medium" onchange="this.form.submit()">
                        <option value="default" <?= ($sort === 'default') ? 'selected' : '' ?>>Default sorting</option>
                        <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Sort by price: low to high</option>
                        <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Sort by price: high to low</option>
                        <option value="rating" <?= ($sort === 'rating') ? 'selected' : '' ?>>Sort by average rating</option>
                        <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>Sort by latest</option>
                    </select>
                </form>
            </div>

        </div>

        <!-- Collapsible Filter Drawer -->
        <div class="collapse mt-3 pt-3 border-top" id="filterDrawer">
            <div class="row g-3">
                <div class="col-md-4">
                    <h6 class="fw-bold mb-2">Category</h6>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <a href="<?= url('products.php') ?>" class="text-decoration-none <?= empty($selectedCategorySlug) ? 'fw-bold text-dark' : 'text-muted' ?>">
                                All Categories (<?= $totalProducts ?>)
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="?category=<?= urlencode($cat['slug']) ?>" class="text-decoration-none <?= ($selectedCategorySlug === $cat['slug']) ? 'fw-bold text-dark' : 'text-muted' ?>">
                                    <?= e($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Catalog Grid -->
<section class="py-4 bg-white">
    <div class="container-fluid px-3 px-lg-5">
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box fs-1 text-muted"></i>
                <h4 class="mt-3">No products found</h4>
                <p class="text-muted">Try clearing your filters or search keywords.</p>
                <a href="<?= url('products.php') ?>" class="btn btn-ons-black mt-2">View All Products</a>
            </div>
        <?php else: ?>
            <?php
            // Calculate column class based on chosen view (col-6 for clean 2-column mobile grid)
            $colClass = match($colView) {
                2 => 'col-lg-6 col-md-6 col-6',
                3 => 'col-lg-4 col-md-6 col-6',
                5 => 'col-lg-2 col-md-4 col-6',
                default => 'col-lg-3 col-md-4 col-6'
            };
            ?>
            <div class="row g-3 g-md-4">
                <?php foreach ($products as $product): ?>
                    <div class="<?= $colClass ?>">
                        <?php require __DIR__ . '/includes/product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Catalog pagination" class="mt-5">
                    <ul class="pagination justify-content-center gap-1">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link rounded-1 border text-dark" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                                <a class="page-link rounded-1 border <?= ($p === $page) ? 'bg-dark text-white border-dark' : 'text-dark' ?>" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>">
                                    <?= $p ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link rounded-1 border text-dark" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
