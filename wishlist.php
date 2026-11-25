<?php
/**
 * ONS SOUND — Customer Wishlist Page
 * Designed to match products.php aesthetic with full-width container spacing
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Wishlist.php';

require_login('wishlist.php');

$wishlistModel = new Wishlist();
$userId = (int)$_SESSION['user_id'];

// Handle Direct POST actions (remove item)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect('wishlist.php');
    }

    $action = clean($_POST['action'] ?? '');
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($action === 'remove' && $productId > 0) {
        $wishlistModel->toggle($productId, $userId, null);
        set_flash('success', 'Item removed from your wishlist.');
        redirect('wishlist.php');
    }
}

$items = $wishlistModel->getItems($userId, null);

$pageTitle = 'My Wishlist';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Wishlist Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">My Wishlist</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5 mb-4">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 pb-3 border-bottom">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-secondary text-decoration-none">Home</a></li>
                <?php if (is_logged_in()): ?>
                    <li class="breadcrumb-item"><a href="<?= url('profile.php') ?>" class="text-secondary text-decoration-none">My Account</a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page">Wishlist</li>
            </ol>
        </nav>

        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-dark rounded-pill px-3 py-2 fw-normal fs-7">
                <?= count($items) ?> <?= count($items) === 1 ? 'item' : 'items' ?> saved
            </span>
            <a href="<?= url('products.php') ?>" class="text-muted small text-decoration-none hover-dark">
                <i class="bi bi-arrow-left me-1"></i>Continue Shopping
            </a>
        </div>
    </div>

    <?php if (!is_logged_in() && !empty($items)): ?>
        <div class="alert alert-light border shadow-sm py-3 px-4 small d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 rounded-2">
            <div>
                <i class="bi bi-info-circle text-dark me-2"></i>
                <span>You are currently viewing a guest wishlist. Sign in to sync your saved items across devices.</span>
            </div>
            <button type="button" class="btn btn-dark btn-sm rounded-1 px-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                Sign in to sync wishlist
            </button>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="card border-0 shadow-sm p-5 text-center rounded-3 my-5">
            <div class="mb-3">
                <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 80px; height: 80px;">
                    <i class="bi bi-heart display-5 text-muted"></i>
                </span>
            </div>
            <h4 class="fw-semibold text-dark mb-2">Your Wishlist is Empty</h4>
            <p class="text-muted small mb-4 mx-auto" style="max-width: 460px;">
                You haven't saved any audio systems or transducers yet. Click the heart icon on any product to save it here for later.
            </p>
            <div>
                <a href="<?= url('products.php') ?>" class="btn btn-dark rounded-1 px-4 py-2 fw-medium">
                    Explore Product Collection &rarr;
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Product Grid Matching products.php Layout -->
        <div class="row g-3 g-md-4">
            <?php foreach ($items as $product): ?>
                <?php
                $productUrl = url('product-details.php?slug=' . urlencode($product['slug']));
                ?>
                <div class="col-lg-3 col-md-4 col-6">
                    <div class="product-card h-100 d-flex flex-column">
                        <div class="product-img-box position-relative">
                            <a href="<?= $productUrl ?>" aria-label="<?= e($product['name']) ?>">
                                <img src="<?= asset($product['main_image']) ?>" 
                                     alt="<?= e($product['name']) ?>" 
                                     loading="lazy">
                            </a>

                            <!-- Floating Overlay Actions on Hover -->
                            <div class="product-overlay-actions">
                                <!-- Wishlist Toggle (Remove) -->
                                <form action="<?= url('wishlist.php') ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                    <button type="submit" 
                                            class="btn-overlay-action text-danger" 
                                            title="Remove from Wishlist" 
                                            aria-label="Remove">
                                        <i class="bi bi-heart-fill"></i>
                                    </button>
                                </form>

                                <!-- Quick View Link -->
                                <a href="<?= $productUrl ?>" 
                                   class="btn-overlay-action" 
                                   title="View Product Details" 
                                   aria-label="Quick View">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <!-- Add to Cart Pill Button -->
                                <button type="button" 
                                        class="product-btn-add ajax-add-to-cart" 
                                        data-product-id="<?= (int)$product['id'] ?>">
                                    ADD TO CART
                                </button>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="pt-3 d-flex flex-column flex-grow-1">
                            <!-- Rating -->
                            <div class="mb-1">
                                <?= render_stars((float)$product['rating'], (int)$product['reviews_count']) ?>
                            </div>

                            <!-- Product Title -->
                            <h3 class="product-card-title mb-1 flex-grow-1">
                                <a href="<?= $productUrl ?>" class="text-dark text-decoration-none">
                                    <?= e($product['name']) ?>
                                </a>
                            </h3>

                            <!-- Price and Quick Remove Button -->
                            <div class="d-flex align-items-center justify-content-between pt-2 mt-auto">
                                <div class="product-card-price text-dark fw-bold">
                                    <?= format_price($product['price']) ?>
                                </div>
                                <form action="<?= url('wishlist.php') ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                    <button type="submit" class="btn btn-link text-danger p-0 small text-decoration-none" title="Remove from wishlist">
                                        <i class="bi bi-trash fs-6"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
