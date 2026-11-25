<?php
/**
 * ONS SOUND - Reusable Value & Announcement Banners Component
 */

/**
 * Render Value Statement Pill Banner
 */
function render_value_pill_banner(string $text): void {
    ?>
    <div class="container my-5">
        <div class="value-pill-banner shadow-sm">
            <?= e($text) ?>
        </div>
    </div>
    <?php
}

/**
 * Render Category Card
 */
function render_category_card(array $category): void {
    $catUrl = url('products.php?category=' . urlencode($category['slug']));
    ?>
    <div class="col-lg-4 col-md-6">
        <div class="category-card">
            <div class="category-img-wrapper">
                <a href="<?= $catUrl ?>" aria-label="<?= e($category['name']) ?>">
                    <img src="<?= asset($category['image'] ?? 'assets/images/categories/tweeters.jpg') ?>" 
                         alt="<?= e($category['name']) ?>" 
                         loading="lazy">
                </a>
            </div>
            <h4 class="category-card-title">
                <a href="<?= $catUrl ?>" class="text-dark text-decoration-none">
                    <?= e($category['name']) ?>
                </a>
            </h4>
            <p class="category-card-desc"><?= e($category['description'] ?? '') ?></p>
        </div>
    </div>
    <?php
}
