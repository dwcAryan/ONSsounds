<?php
/**
 * ONS SOUND - Reusable Breadcrumb Component
 * Matches the breadcrumb layout from product-details.png and blog.png
 *
 * @var array $breadcrumbs Array of ['label' => string, 'url' => ?string]
 * @var bool $showPagination Whether to show the `< ☷ >` navigation icons on right
 * @var ?string $prevUrl Optional previous product URL
 * @var ?string $nextUrl Optional next product URL
 * @var ?string $gridUrl Optional back to grid URL
 */

$breadcrumbs = $breadcrumbs ?? [
    ['label' => 'Home', 'url' => url('index.php')]
];
$showPagination = $showPagination ?? false;
$prevUrl = $prevUrl ?? '#';
$nextUrl = $nextUrl ?? '#';
$gridUrl = $gridUrl ?? url('products.php');
?>
<nav aria-label="breadcrumb" class="ons-breadcrumb-wrapper py-3 mb-4">
    <div class="container-fluid px-3 px-lg-5">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <ol class="breadcrumb mb-0 flex-grow-1 me-2">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index === count($breadcrumbs) - 1 || empty($crumb['url'])): ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= e($crumb['label']) ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item">
                            <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>

            <?php if ($showPagination): ?>
                <div class="ons-page-nav-icons d-flex align-items-center gap-2 flex-shrink-0">
                    <a href="<?= e($prevUrl) ?>" class="text-dark fs-6" title="Previous product" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <a href="<?= e($gridUrl) ?>" class="text-dark fs-6" title="Back to products" aria-label="All Products">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </a>
                    <a href="<?= e($nextUrl) ?>" class="text-dark fs-6" title="Next product" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
