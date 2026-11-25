<?php
/**
 * ONS SOUND - Product Details Page
 * Strictly matching ref_img/product-details.png
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Category.php';

$slug = clean($_GET['slug'] ?? 'speaker-10');
$productModel = new Product();
$product = $productModel->getBySlug($slug);

if (!$product) {
    // Fallback to first available product
    $all = $productModel->getCatalog([], 'default', 1);
    $product = $all[0] ?? null;
}

if (!$product) {
    redirect('products.php');
}

$pageTitle = $product['name'];
$pageDescription = $product['short_description'] ?? 'Precision audio driver engineered by ONS Sound.';

// Fetch specs, gallery, reviews, and related items
$specifications = $productModel->getSpecifications($product['id']);
$gallery = $productModel->getGallery($product['id']);
$reviews = $productModel->getReviews($product['id']);
$relatedProducts = $productModel->getRelated($product['category_id'] ?? 1, $product['id'], 4);

// Build unified gallery list (main image + extra gallery images)
$galleryImages = [];
if (!empty($product['main_image'])) {
    $galleryImages[] = $product['main_image'];
}
if (!empty($gallery)) {
    foreach ($gallery as $g) {
        if (!empty($g['image_path'])) {
            $galleryImages[] = $g['image_path'];
        }
    }
} elseif (!empty($product['hover_image']) && $product['hover_image'] !== $product['main_image']) {
    $galleryImages[] = $product['hover_image'];
}
if (empty($galleryImages)) {
    $galleryImages[] = 'assets/images/products/speaker-1.png';
}

$breadcrumbs = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => $product['category_name'] ?? 'Speaker', 'url' => url('products.php?category=' . urlencode($product['category_slug'] ?? ''))],
    ['label' => $product['name'], 'url' => '']
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs & Product Navigation -->
<nav aria-label="breadcrumb" class="ons-breadcrumb-wrapper py-3 mb-4">
    <div class="container-fluid px-3 px-lg-5">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <ol class="breadcrumb mb-0 flex-grow-1 me-2">
                <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('products.php') ?>" class="text-muted"><?= e($product['category_name'] ?? 'Speaker') ?></a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= e($product['name']) ?></li>
            </ol>

            <div class="ons-page-nav-icons d-flex align-items-center gap-2 flex-shrink-0">
                <a href="<?= url('products.php') ?>" class="text-dark fs-6" title="Previous"><i class="bi bi-chevron-left"></i></a>
                <a href="<?= url('products.php') ?>" class="text-dark fs-6" title="All Products"><i class="bi bi-grid-3x3-gap"></i></a>
                <a href="<?= url('products.php') ?>" class="text-dark fs-6" title="Next"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Product Details Showcase -->
<section class="py-4 bg-white">
    <div class="container-fluid px-3 px-lg-5">
        <?php if (has_flash('success')): ?>
            <?php foreach (get_flash('success') as $msg): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (has_flash('error')): ?>
            <?php foreach (get_flash('error') as $msg): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="row g-4 g-lg-5">
            
            <!-- Left Column: Product Imagery -->
            <div class="col-lg-6">
                <div class="product-detail-main-img-box" id="productMainImgBox" title="Click for full view or hover to zoom">
                    <img id="mainProductImg" 
                         src="<?= asset($galleryImages[0]) ?>" 
                         alt="<?= e($product['name']) ?>" 
                         class="product-detail-main-img">
                    <button type="button" class="product-zoom-icon" id="openFullViewBtn" title="Full screen view" aria-label="Full screen view">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>

                <?php if (count($galleryImages) > 1): ?>
                    <div class="product-gallery-thumbnails d-flex flex-wrap gap-2 gap-sm-3 mt-3" id="galleryThumbsContainer">
                        <?php foreach ($galleryImages as $idx => $imgPath): ?>
                            <div class="gallery-thumb <?= ($idx === 0) ? 'active' : '' ?>" 
                                 data-index="<?= $idx ?>" 
                                 data-src="<?= asset($imgPath) ?>" 
                                 title="View image <?= $idx + 1 ?>">
                                <img src="<?= asset($imgPath) ?>" alt="<?= e($product['name']) ?> thumbnail <?= $idx + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Product Meta & Purchase Action -->
            <div class="col-lg-6">
                <div class="ps-lg-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="text-muted small text-uppercase fw-semibold"><?= e($product['category_name'] ?? 'Speaker') ?></span>
                        <div class="ms-2">
                            <?= render_stars((float)$product['rating']) ?>
                            <span class="text-muted small ms-1">( <?= (int)$product['reviews_count'] ?> reviews )</span>
                        </div>
                    </div>

                    <h1 class="product-detail-title"><?= e($product['name']) ?></h1>
                    <div class="product-detail-price"><?= format_price($product['price']) ?></div>

                    <!-- Add to Cart Form -->
                    <form id="detailAddToCartForm" action="<?= url('cart_action.php') ?>" method="POST" class="my-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-semibold">Quantity:</label>
                            <div class="d-flex align-items-center gap-2 gap-sm-3 flex-wrap">
                                <div class="qty-control flex-shrink-0">
                                    <button type="button" class="qty-btn qty-minus">−</button>
                                    <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?= (int)$product['stock_quantity'] ?>">
                                    <button type="button" class="qty-btn qty-plus">+</button>
                                </div>

                                <button type="submit" class="btn btn-add-to-cart-large flex-grow-1 text-nowrap">
                                    Add to cart - <?= format_price($product['price']) ?>
                                </button>

                                <button type="button" 
                                        class="btn-wishlist-detail ajax-toggle-wishlist flex-shrink-0" 
                                        data-product-id="<?= (int)$product['id'] ?>" 
                                        title="Add to Wishlist" 
                                        aria-label="Wishlist">
                                    <i class="bi bi-heart"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Meta Tags & Share -->
                    <div class="border-top pt-3 mt-4 text-muted small">
                        <div class="mb-2">
                            <a href="#" class="text-muted text-decoration-none" onclick="navigator.clipboard?.writeText(window.location.href); alert('Product link copied to clipboard!'); return false;">
                                <i class="bi bi-share me-1"></i> Share
                            </a>
                        </div>
                        <div class="mb-2">
                            <strong>Category:</strong> <a href="<?= url('products.php?category=' . urlencode($product['category_slug'] ?? '')) ?>" class="text-dark"><?= e($product['category_name'] ?? 'Speaker') ?></a>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-3 text-dark fw-semibold">
                            <i class="bi bi-shield-check fs-5 text-dark"></i> Guarantee Safe Checkout
                        </div>
                    </div>

                    <!-- Policies & Shipping Box (ref_img/product-details.png) -->
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="policy-info-box">
                                <div class="policy-info-icon"><i class="bi bi-box-seam"></i></div>
                                <p class="policy-info-text">
                                    <strong>Estimate delivery times:</strong> 12-26 days (International), 3-6 days (United States).
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="policy-info-box">
                                <div class="policy-info-icon"><i class="bi bi-arrow-repeat"></i></div>
                                <p class="policy-info-text">
                                    <strong>Return within 30 days</strong> of purchase. Duties & taxes are non-refundable.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- Product Information Tabs -->
<section class="py-5 bg-white border-top">
    <div class="container-fluid px-3 px-lg-5">
        
        <ul class="nav nav-tabs ons-detail-tabs mb-4" id="productTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-pane" type="button" role="tab" aria-controls="desc-pane" aria-selected="true">Description</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="spec-tab" data-bs-toggle="tab" data-bs-target="#spec-pane" type="button" role="tab" aria-controls="spec-pane" aria-selected="false">Additional information</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane" type="button" role="tab" aria-controls="reviews-pane" aria-selected="false">Reviews (<?= count($reviews) ?>)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="size-tab" data-bs-toggle="tab" data-bs-target="#size-pane" type="button" role="tab" aria-controls="size-pane" aria-selected="false">Size Guide</button>
            </li>
        </ul>

        <div class="tab-content" id="productTabsContent">
            
            <!-- Tab 1: Description & Primary Spec Table Matching Screenshot -->
            <div class="tab-pane fade show active" id="desc-pane" role="tabpanel" aria-labelledby="desc-tab">
                <div class="row">
                    <div class="col-lg-8">
                        <table class="spec-table">
                            <tbody>
                                <?php if (!empty($specifications)): ?>
                                    <?php foreach ($specifications as $spec): ?>
                                        <tr>
                                            <td><?= e($spec['spec_key']) ?></td>
                                            <td><?= e($spec['spec_value']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td>Model Name</td><td><?= e($product['model_name'] ?? '15 FR200') ?></td></tr>
                                    <tr><td>Nominal Impedance</td><td>8 ohms</td></tr>
                                    <tr><td>Basket</td><td>Steel</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Additional Info -->
            <div class="tab-pane fade" id="spec-pane" role="tabpanel" aria-labelledby="spec-tab">
                <div class="py-3">
                    <p class="text-muted lh-lg"><?= nl2br(e($product['full_description'] ?? $product['short_description'])) ?></p>
                </div>
            </div>

            <!-- Tab 3: Reviews -->
            <div class="tab-pane fade" id="reviews-pane" role="tabpanel" aria-labelledby="reviews-tab">
                <div class="py-3">
                    <h5 class="fw-bold mb-4">Customer Reviews</h5>
                    <?php if (empty($reviews)): ?>
                        <p class="text-muted">There are no reviews yet. Be the first to review this product!</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong><?= e($rev['reviewer_name']) ?></strong>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                                </div>
                                <?= render_stars((float)$rev['rating']) ?>
                                <p class="text-muted mt-2 mb-0"><?= e($rev['review_text']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Write a Customer Review Form (Restricted to logged-in users only) -->
                    <div class="card border-0 shadow-sm mt-4 p-4" style="background: #fafbfc; border-radius: 10px;">
                        <h6 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2 text-primary"></i>Write a Customer Review</h6>
                        <?php if (is_logged_in()): ?>
                            <form method="POST" action="<?= url('review_submit.php') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                <input type="hidden" name="redirect_url" value="<?= url('product-details.php?slug=' . urlencode($product['slug'])) ?>">

                                <div class="row g-3">
                                    <div class="col-12 col-md-7">
                                        <label class="form-label small fw-semibold text-muted d-block mb-2">Your Rating (Click on stars) *</label>
                                        <div class="interactive-star-rating" id="interactiveStarRating">
                                            <div class="stars-box" role="group" aria-label="Select star rating">
                                                <i class="bi bi-star-fill star-item active" data-rating="1" title="1 Star - Poor"></i>
                                                <i class="bi bi-star-fill star-item active" data-rating="2" title="2 Stars - Fair"></i>
                                                <i class="bi bi-star-fill star-item active" data-rating="3" title="3 Stars - Average"></i>
                                                <i class="bi bi-star-fill star-item active" data-rating="4" title="4 Stars - Good"></i>
                                                <i class="bi bi-star-fill star-item active" data-rating="5" title="5 Stars - Excellent"></i>
                                            </div>
                                            <input type="hidden" name="rating" id="reviewRatingInput" value="5">
                                            <span class="rating-text-label" id="ratingTextDisplay">5 Stars (Excellent)</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5 d-flex align-items-center">
                                        <div class="small text-muted pt-md-4">
                                            <i class="bi bi-person-check text-success me-1"></i> Reviewing as <strong><?= e($_SESSION['user_name'] ?? 'Customer') ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-muted">Review Comments (Optional)</label>
                                        <textarea name="review_text" rows="3" class="form-control" placeholder="Share your experience with sound clarity, build quality, frequency response, and performance (optional)..." style="border-radius: 6px; font-size: 14px;"></textarea>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-dark btn-sm px-4 py-2" style="border-radius: 6px; font-weight: 600;">
                                            <i class="bi bi-send me-1"></i> Submit Review
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <p class="text-muted small mb-3">Only logged-in customers can submit a review for this product.</p>
                                <button type="button" class="btn btn-outline-dark btn-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#loginModal" style="border-radius: 6px; font-weight: 600;">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Log In to Submit Review
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Size Guide -->
            <div class="tab-pane fade" id="size-pane" role="tabpanel" aria-labelledby="size-tab">
                <div class="py-3">
                    <p class="text-muted">Standard chassis mounting hole pattern with reinforced flange diameter. Compatible with universal speaker enclosures.</p>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- Related Products Section -->
<section class="py-5 bg-white border-top">
    <div class="container-fluid px-3 px-lg-5">
        <h2 class="section-title text-center mb-5">Related products</h2>
        <div class="row g-4">
            <?php foreach ($relatedProducts as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <?php require __DIR__ . '/includes/product-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Sticky Bottom Add to Cart Bar (Appears on scroll) -->
<div class="sticky-cart-bar">
    <div class="container-fluid px-3 px-lg-5">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 gap-sm-3 text-truncate" style="max-width: 60%;">
                <img src="<?= asset($product['main_image']) ?>" alt="<?= e($product['name']) ?>" style="width: 44px; height: 44px; object-fit: contain; flex-shrink: 0;">
                <div class="text-truncate">
                    <strong class="d-block text-dark text-truncate" style="font-size: 14px;"><?= e($product['name']) ?></strong>
                    <span class="text-muted small"><?= format_price($product['price']) ?></span>
                </div>
            </div>

            <div class="d-flex align-items-center flex-shrink-0">
                <form action="<?= url('cart_action.php') ?>" method="POST" class="d-flex align-items-center gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-dark fw-bold px-3 py-2 px-sm-4" style="white-space: nowrap;">Add to cart</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Full View Product Image Lightbox Modal -->
<div class="modal fade product-lightbox-modal" id="productLightboxModal" tabindex="-1" aria-hidden="true">
    <!-- Pinned Glassmorphic Close Button in Top Right of Screen -->
    <button type="button" class="lightbox-close-btn" data-bs-dismiss="modal" aria-label="Close" title="Close (Esc)">
        <i class="bi bi-x-lg"></i>
    </button>

    <?php if (count($galleryImages) > 1): ?>
        <!-- Pinned Navigation Arrows on Viewport Edges -->
        <button type="button" class="lightbox-nav-btn lightbox-prev" aria-label="Previous image" title="Previous (Left Arrow)">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" class="lightbox-nav-btn lightbox-next" aria-label="Next image" title="Next (Right Arrow)">
            <i class="bi bi-chevron-right"></i>
        </button>
    <?php endif; ?>

    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center d-flex flex-column align-items-center justify-content-center">
                <div class="lightbox-img-wrapper">
                    <img id="lightboxImg" src="<?= asset($galleryImages[0]) ?>" alt="<?= e($product['name']) ?> full view">
                </div>
                <?php if (count($galleryImages) > 1): ?>
                    <div class="lightbox-counter mt-3" id="lightboxCounter">
                        <span id="lightboxCurrentIndex">1</span> / <?= count($galleryImages) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
