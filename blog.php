<?php
/**
 * ONS SOUND - Blog Listing Page
 * Strictly matching ref_img/blog.png
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Blog.php';

$pageTitle = 'Blog';
$pageDescription = 'Read the latest acoustic engineering insights, guides, and tips from the ONS SOUND team.';

$blogModel = new Blog();
$posts = $blogModel->getAllPublished();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Blog Page Title & Breadcrumb Matching ref_img/blog.png -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-2">Blog</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-muted text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Blog</li>
            </ol>
        </nav>
    </div>
</section>

<!-- 3-Column Blog Post Card Grid Matching ref_img/blog.png -->
<section class="pb-5 bg-white">
    <div class="container-fluid px-3 px-lg-5">
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
                <div class="col-lg-4 col-md-6">
                    <article class="blog-card">
                        <div class="blog-img-box">
                            <a href="<?= url('blog-details.php?slug=' . urlencode($post['slug'])) ?>" aria-label="<?= e($post['title']) ?>">
                                <img src="<?= asset($post['image']) ?>" 
                                     alt="<?= e($post['title']) ?>" 
                                     loading="lazy">
                            </a>
                        </div>
                        <div class="blog-card-content">
                            <span class="blog-category-tag"><?= e($post['category_name'] ?? 'TECH') ?></span>
                            <h2 class="blog-card-title">
                                <a href="<?= url('blog-details.php?slug=' . urlencode($post['slug'])) ?>">
                                    <?= e($post['title']) ?>
                                </a>
                            </h2>
                            <a href="<?= url('blog-details.php?slug=' . urlencode($post['slug'])) ?>" class="blog-read-more">
                                <span>Read More</span> <i class="bi bi-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
