<?php
/**
 * ONS SOUND - Blog Single Article Reader
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Blog.php';

$slug = clean($_GET['slug'] ?? '');
$blogModel = new Blog();
$post = $blogModel->getBySlug($slug);

if (!$post) {
    // Fallback to first available post
    $all = $blogModel->getRecent(1);
    $post = $all[0] ?? null;
}

if (!$post) {
    redirect('blog.php');
}

$pageTitle = $post['title'];
$pageDescription = $post['excerpt'] ?? 'Acoustic engineering guide from ONS SOUND.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Article Breadcrumbs -->
<nav aria-label="breadcrumb" class="ons-breadcrumb-wrapper py-3 mb-4">
    <div class="container-fluid px-3 px-lg-5">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('blog.php') ?>" class="text-muted">Blog</a></li>
            <li class="breadcrumb-item active text-dark" aria-current="page"><?= e($post['title']) ?></li>
        </ol>
    </div>
</nav>

<!-- Main Article Section -->
<article class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <span class="blog-category-tag mb-2"><?= e($post['category_name'] ?? 'TECH') ?></span>
                <h1 class="display-6 fw-bold text-dark mb-3"><?= e($post['title']) ?></h1>
                
                <div class="d-flex align-items-center gap-3 text-muted small mb-4 pb-3 border-bottom">
                    <span><i class="bi bi-person me-1"></i> <?= e($post['author'] ?? 'ONS Sound Team') ?></span>
                    <span><i class="bi bi-calendar3 me-1"></i> <?= date('F d, Y', strtotime($post['created_at'])) ?></span>
                </div>

                <div class="rounded-3 overflow-hidden mb-5">
                    <img src="<?= asset($post['image']) ?>" alt="<?= e($post['title']) ?>" class="img-fluid w-100">
                </div>

                <div class="article-content fs-6 lh-lg text-secondary">
                    <?= $post['content'] ?>
                </div>

                <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                    <a href="<?= url('blog.php') ?>" class="btn btn-outline-dark rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i> Back to Blog
                    </a>
                </div>
            </div>
        </div>
    </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
