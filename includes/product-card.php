<?php
/**
 * ONS SOUND - Reusable Product Card Component
 * Strictly matching ref_img/products.png layout & interaction
 *
 * Variables required:
 * @var array $product Product array from database
 */
if (empty($product)) {
    return;
}

$productUrl = url('product-details.php?slug=' . urlencode($product['slug']));
$hasReviews = ($product['reviews_count'] > 0);
?>
<div class="product-card">
    <div class="product-img-box">
        <a href="<?= $productUrl ?>" aria-label="<?= e($product['name']) ?>">
            <img src="<?= asset($product['main_image']) ?>" 
                 alt="<?= e($product['name']) ?>" 
                 loading="lazy">
        </a>

        <!-- Floating Overlay Actions on Hover -->
        <div class="product-overlay-actions">
            <!-- Wishlist Toggle -->
            <button type="button" 
                    class="btn-overlay-action ajax-toggle-wishlist" 
                    data-product-id="<?= (int)$product['id'] ?>" 
                    title="Add to Wishlist" 
                    aria-label="Wishlist">
                <i class="bi bi-heart"></i>
            </button>

            <!-- Quick View Dialog Trigger -->
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

    <!-- Rating & Review Count -->
    <div class="mb-1">
        <?= render_stars((float)$product['rating'], (int)$product['reviews_count']) ?>
    </div>

    <!-- Product Title -->
    <h3 class="product-card-title">
        <a href="<?= $productUrl ?>" class="text-dark text-decoration-none">
            <?= e($product['name']) ?>
        </a>
    </h3>

    <!-- Price -->
    <div class="product-card-price">
        <?= format_price($product['price']) ?>
        <?php if (!empty($product['compare_at_price']) && $product['compare_at_price'] > $product['price']): ?>
            <span class="text-muted text-decoration-line-through fs-6 ms-1 fw-normal">
                <?= format_price($product['compare_at_price']) ?>
            </span>
        <?php endif; ?>
    </div>
</div>
