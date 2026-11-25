<?php
/**
 * ONS SOUND - Global Header Include
 * Exact fonts & styling from onssound.com (Albert Sans / Schibsted Grotesk)
 */
require_once __DIR__ . '/helpers.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$cartCount = get_cart_count();
$wishlistCount = get_wishlist_count();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . SITE_NAME : SITE_NAME . ' - ' . SITE_TAGLINE ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? e($pageDescription) : 'High-fidelity audio systems, woofers, tweeters and amplifiers engineered for unmatched audio performance.' ?>">

    <!-- Google Fonts: Albert Sans & Schibsted Grotesk (Exact onssound.com typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- ONS SOUND Master CSS -->
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>?v=<?= time() ?>">
</head>
<body>

<!-- Header Navigation -->
<header class="site-header">
    <div class="container-fluid px-3 px-lg-5">
        <div class="header-inner d-flex align-items-center justify-content-between">
            
            <!-- Left: Mobile Menu Toggle (d-lg-none) & Desktop Brand Logo (d-none d-lg-block) -->
            <div class="header-left d-flex align-items-center">
                <button class="header-hamburger btn p-0 border-0 text-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavOffcanvas" aria-controls="mobileNavOffcanvas" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
                <a href="<?= url('index.php') ?>" class="brand-logo text-dark d-none d-lg-inline-block">
                    <?= SITE_NAME ?>
                </a>
            </div>

            <!-- Center: Mobile Centered Brand Logo (d-lg-none) & Desktop Navigation Links (d-none d-lg-flex) -->
            <div class="header-center text-center">
                <a href="<?= url('index.php') ?>" class="brand-logo text-dark d-lg-none">
                    <?= SITE_NAME ?>
                </a>
                <nav class="site-nav d-none d-lg-flex align-items-center justify-content-center">
                    <a href="<?= url('index.php') ?>" class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>">Home</a>
                    <a href="<?= url('products.php') ?>" class="nav-link <?= ($currentPage === 'products.php' || $currentPage === 'product-details.php') ? 'active' : '' ?>">Products</a>
                    <a href="<?= url('blog.php') ?>" class="nav-link <?= ($currentPage === 'blog.php' || $currentPage === 'blog-details.php') ? 'active' : '' ?>">Blog</a>
                    <a href="<?= url('about.php') ?>" class="nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>">About Us</a>
                    <a href="<?= url('contact.php') ?>" class="nav-link <?= ($currentPage === 'contact.php') ? 'active' : '' ?>">Contact Us</a>
                </nav>
            </div>

            <!-- Right: Action Icons (Search, Account, Wishlist, Cart) -->
            <div class="header-right header-actions d-flex align-items-center justify-content-end">
                <!-- Search Trigger: Opens Top Drawer -->
                <button type="button" class="header-action-btn" data-bs-toggle="offcanvas" data-bs-target="#searchTopDrawer" title="Search Products" aria-label="Search">
                    <i class="bi bi-search"></i>
                </button>

                <!-- User Account Action / Dropdown -->
                <?php if (is_logged_in()): ?>
                    <div class="dropdown">
                        <button class="header-action-btn dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false" title="Account" aria-label="Account">
                            <i class="bi bi-person"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userMenuButton" style="min-width: 200px;">
                            <li class="px-3 py-2 border-bottom">
                                <small class="text-muted d-block">Signed in as</small>
                                <strong class="d-block text-truncate"><?= e($user['full_name'] ?? 'User') ?></strong>
                            </li>
                            <?php if (is_admin()): ?>
                                <li><a class="dropdown-item py-2 text-danger fw-semibold" href="<?= url('admin/index.php') ?>"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item py-2" href="<?= url('orders.php') ?>"><i class="bi bi-box-seam me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item py-2" href="<?= url('addresses.php') ?>"><i class="bi bi-geo-alt me-2"></i>My Addresses</a></li>
                            <li><a class="dropdown-item py-2" href="<?= url('profile.php') ?>"><i class="bi bi-gear me-2"></i>Account Details</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 text-muted" href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Log Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Tapping user icon opens Login Modal popup directly -->
                    <button type="button" class="header-action-btn" data-bs-toggle="modal" data-bs-target="#loginModal" title="Log In" aria-label="Log In">
                        <i class="bi bi-person"></i>
                    </button>
                <?php endif; ?>

                <!-- Wishlist Icon with Dynamic Badge -->
                <a href="<?= url('wishlist.php') ?>" class="header-action-btn header-wishlist-btn" title="Wishlist" aria-label="Wishlist">
                    <i class="bi bi-heart"></i>
                    <span class="badge-counter wishlist-badge"><?= $wishlistCount ?></span>
                </a>

                <!-- Cart Icon with Dynamic Badge -->
                <a href="<?= url('cart.php') ?>" class="header-action-btn" title="Cart" aria-label="Shopping Cart">
                    <i class="bi bi-bag"></i>
                    <span class="badge-counter cart-badge"><?= $cartCount ?></span>
                </a>
            </div>

        </div>
    </div>
</header>

<!-- Mobile Navigation Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNavOffcanvas" aria-labelledby="mobileNavLabel">
    <div class="offcanvas-header border-bottom py-3">
        <h5 class="offcanvas-title fw-bold text-uppercase tracking-wider m-0" id="mobileNavLabel"><?= SITE_NAME ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <!-- Quick Search inside Mobile Menu -->
        <form action="<?= url('products.php') ?>" method="GET" class="mb-4">
            <div class="input-group border rounded-2 overflow-hidden">
                <input type="text" name="q" class="form-control border-0 py-2 ps-3 fs-6" placeholder="Search audio products..." required>
                <button class="btn btn-dark px-3" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <!-- Navigation Links -->
        <nav class="nav flex-column gap-1 mb-4">
            <a href="<?= url('index.php') ?>" class="nav-link py-2 fs-5 <?= ($currentPage === 'index.php') ? 'fw-bold text-dark' : 'text-secondary' ?>">
                <i class="bi bi-house me-2"></i>Home
            </a>
            <a href="<?= url('products.php') ?>" class="nav-link py-2 fs-5 <?= ($currentPage === 'products.php' || $currentPage === 'product-details.php') ? 'fw-bold text-dark' : 'text-secondary' ?>">
                <i class="bi bi-grid me-2"></i>Products
            </a>
            <a href="<?= url('blog.php') ?>" class="nav-link py-2 fs-5 <?= ($currentPage === 'blog.php' || $currentPage === 'blog-details.php') ? 'fw-bold text-dark' : 'text-secondary' ?>">
                <i class="bi bi-journal-text me-2"></i>Blog
            </a>
            <a href="<?= url('about.php') ?>" class="nav-link py-2 fs-5 <?= ($currentPage === 'about.php') ? 'fw-bold text-dark' : 'text-secondary' ?>">
                <i class="bi bi-info-circle me-2"></i>About Us
            </a>
            <a href="<?= url('contact.php') ?>" class="nav-link py-2 fs-5 <?= ($currentPage === 'contact.php') ? 'fw-bold text-dark' : 'text-secondary' ?>">
                <i class="bi bi-envelope me-2"></i>Contact Us
            </a>
        </nav>

        <hr class="my-3">

        <!-- User & Shortcuts Section -->
        <div class="d-flex flex-column gap-2 mb-4">
            <a href="<?= url('wishlist.php') ?>" class="nav-link py-2 d-flex align-items-center justify-content-between text-dark">
                <span><i class="bi bi-heart me-2"></i>My Wishlist</span>
                <span class="badge bg-danger rounded-pill px-2"><?= $wishlistCount ?></span>
            </a>
            <a href="<?= url('cart.php') ?>" class="nav-link py-2 d-flex align-items-center justify-content-between text-dark">
                <span><i class="bi bi-bag me-2"></i>Shopping Cart</span>
                <span class="badge bg-danger rounded-pill px-2"><?= $cartCount ?></span>
            </a>

            <?php if (is_logged_in()): ?>
                <div class="mt-2 pt-2 border-top">
                    <small class="text-muted d-block mb-1">Signed in as <strong><?= e($user['full_name'] ?? 'User') ?></strong></small>
                    <a href="<?= url('orders.php') ?>" class="nav-link py-2 text-dark"><i class="bi bi-box-seam me-2"></i>My Orders</a>
                    <a href="<?= url('addresses.php') ?>" class="nav-link py-2 text-dark"><i class="bi bi-geo-alt me-2"></i>My Addresses</a>
                    <a href="<?= url('profile.php') ?>" class="nav-link py-2 text-dark"><i class="bi bi-gear me-2"></i>Account Details</a>
                    <?php if (is_admin()): ?>
                        <a href="<?= url('admin/index.php') ?>" class="nav-link py-2 text-danger fw-bold"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a>
                    <?php endif; ?>
                    <a href="<?= url('logout.php') ?>" class="nav-link py-2 text-muted"><i class="bi bi-box-arrow-right me-2"></i>Log Out</a>
                </div>
            <?php else: ?>
                <div class="mt-2 pt-2 border-top d-flex gap-2">
                    <a href="<?= url('login.php') ?>" class="btn btn-outline-dark w-50 py-2">Sign In</a>
                    <a href="<?= url('register.php') ?>" class="btn btn-dark w-50 py-2">Register</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Store Contact Info -->
        <div class="mt-auto pt-3 border-top small text-muted">
            <div class="mb-1"><i class="bi bi-telephone me-2"></i><?= STORE_PHONE ?></div>
            <div><i class="bi bi-envelope me-2"></i><?= STORE_EMAIL ?></div>
        </div>
    </div>
</div>

<!-- Search Top Drawer (Opens from top on search icon tap - Ref: media_1795532903450_89ec9a6a.jpg) -->
<?php $drawerData = get_search_drawer_data(); ?>
<div class="offcanvas offcanvas-top search-top-drawer" tabindex="-1" id="searchTopDrawer" aria-labelledby="searchTopDrawerLabel" data-bs-scroll="true">
    <div class="search-drawer-scroll-container">
        <div class="container py-4 px-3 px-md-4 position-relative">
            <button type="button" class="btn-close search-drawer-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            
            <div class="text-center mb-4 pt-2">
                <h2 class="search-drawer-title fw-normal mb-3" id="searchTopDrawerLabel">Search our site</h2>
                <form action="<?= url('products.php') ?>" method="GET" class="search-drawer-form mx-auto" id="searchDrawerForm">
                    <div class="search-drawer-input-wrap">
                        <button type="submit" class="search-drawer-submit-btn" title="Search" aria-label="Search">
                            <i class="bi bi-search search-icon"></i>
                        </button>
                        <span class="search-divider">|</span>
                        <input type="text" name="q" id="searchDrawerInput" class="search-drawer-input" placeholder="Search..." autocomplete="off">
                        <div id="searchDrawerSpinner" class="spinner-border spinner-border-sm text-dark search-drawer-spinner d-none" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <button type="button" id="searchDrawerClearBtn" class="search-drawer-clear-btn d-none" title="Clear" aria-label="Clear">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="row pt-2 g-4 g-lg-5 search-drawer-content-row">
                <!-- Left Column: Quick links -->
                <div class="col-md-3 col-lg-2 search-left-col">
                    <h6 class="search-col-heading">Quick links</h6>
                    <ul class="list-unstyled search-quick-links mb-0" id="searchQuickLinksList">
                        <?php if (!empty($drawerData['quick_links'])): ?>
                            <?php foreach ($drawerData['quick_links'] as $ql): ?>
                                <li class="mb-2">
                                    <a href="<?= url('product-details.php?slug=' . urlencode($ql['slug'])) ?>" class="text-decoration-none text-secondary hover-dark">
                                        <?= e($ql['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="mb-2"><a href="<?= url('products.php') ?>" class="text-secondary text-decoration-none">All Speakers</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Right Column: Need some inspiration? / Live Search Results -->
                <div class="col-md-9 col-lg-10 search-right-col">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="search-col-heading mb-0" id="searchInspirationHeading">Need some inspiration?</h6>
                        <a href="<?= url('products.php') ?>" id="searchViewAllLink" class="text-decoration-underline small text-dark d-none fw-medium">
                            View all results (<span id="searchResultCount">0</span>) &#8599;
                        </a>
                    </div>

                    <!-- Products Grid -->
                    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-3 g-3 g-lg-4" id="searchDrawerGrid">
                        <?php foreach ($drawerData['inspiration_products'] as $prod): ?>
                            <div class="col search-prod-col">
                                <a href="<?= url('product-details.php?slug=' . urlencode($prod['slug'])) ?>" class="search-inspiration-card text-decoration-none text-dark d-block">
                                    <div class="search-inspiration-img-box mb-2">
                                        <img src="<?= asset($prod['main_image'] ?? 'assets/images/products/speaker-white.png') ?>" alt="<?= e($prod['name']) ?>" class="img-fluid" loading="lazy">
                                    </div>
                                    <div class="search-inspiration-meta">
                                        <div class="search-stars mb-1">
                                            <?= render_stars($prod['rating'] ?? 5, $prod['reviews_count'] ?? 0) ?>
                                        </div>
                                        <div class="search-prod-title fw-medium text-truncate"><?= e($prod['name']) ?></div>
                                        <div class="search-prod-price text-muted small"><?= format_price($prod['price']) ?></div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Login Modal (Ref: media_1795532886934_fd7c5a3a.png) -->
<div class="modal fade auth-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 490px;">
        <div class="modal-content border-0 p-3 p-sm-4 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0">
                <h3 class="modal-title fw-semibold text-dark fs-4" id="loginModalLabel">Login</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-danger d-none auth-error-alert py-2 mb-3 small" role="alert"></div>
                <form id="ajaxLoginForm" method="POST" action="<?= url('auth_action.php') ?>">
                    <input type="hidden" name="action" value="login">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control auth-input" placeholder="Email" required autocomplete="email">
                    </div>
                    <div class="mb-3 position-relative">
                        <input type="password" name="password" class="form-control auth-input pe-5 auth-password-input" placeholder="Password" required autocomplete="current-password">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1" aria-label="Toggle password visibility">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-4 small">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" value="1">
                            <label class="form-check-label text-muted" for="rememberMe">Remember me</label>
                        </div>
                        <a href="<?= url('forgot-password.php') ?>" class="text-decoration-underline text-muted">Forgot your password?</a>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-1">
                        <button type="submit" class="btn btn-dark px-4 py-2 fw-medium rounded-1 auth-submit-btn">Log in</button>
                        <a href="javascript:void(0)" class="switch-to-register text-dark text-decoration-underline small fw-medium">New customer? Create your account &#8599;</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal (Ref: media_1795532856822_6357e814.jpg) -->
<div class="modal fade auth-modal" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 490px;">
        <div class="modal-content border-0 p-3 p-sm-4 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0">
                <h3 class="modal-title fw-semibold text-dark fs-4" id="registerModalLabel">Register</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-danger d-none auth-error-alert py-2 mb-3 small" role="alert"></div>

                <!-- Step 1: Initial Registration Details Form -->
                <div id="registerFormStep">
                    <form id="ajaxRegisterForm" method="POST" action="<?= url('auth_action.php') ?>">
                        <input type="hidden" name="action" value="send_register_otp">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <input type="text" name="name" class="form-control auth-input" placeholder="Full name" required autocomplete="name">
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control auth-input" placeholder="Email" required autocomplete="email">
                        </div>
                        <div class="mb-3 position-relative">
                            <input type="password" name="password" class="form-control auth-input pe-5 auth-password-input" placeholder="Password" minlength="6" required autocomplete="new-password">
                            <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1" aria-label="Toggle password visibility">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-1">
                            <button type="submit" class="btn btn-dark px-4 py-2 fw-medium rounded-1 auth-submit-btn">Register</button>
                            <a href="javascript:void(0)" class="switch-to-login text-dark text-decoration-underline small fw-medium">Already have an account? Log in here &#8599;</a>
                        </div>
                    </form>
                </div>

                <!-- Step 2: 6-Digit OTP Verification Screen -->
                <div id="registerOtpStep" class="d-none">
                    <div class="text-center mb-3">
                        <div class="mb-2">
                            <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 50px; height: 50px;">
                                <i class="bi bi-envelope-check text-dark fs-3"></i>
                            </span>
                        </div>
                        <h5 class="fw-semibold text-dark mb-1">Verify Your Email</h5>
                        <p class="text-muted small mb-1">We sent a 6-digit verification code to your email</p>
                        <strong class="text-dark d-block small mb-3" id="registerOtpTargetEmail"></strong>
                        <p class="text-muted small mb-0">Please check your inbox (and spam folder) and enter the code below:</p>
                    </div>
                    <form id="ajaxRegisterOtpForm">
                        <input type="hidden" name="action" value="verify_register_otp">
                        <div class="mb-3">
                            <input type="text" name="otp" id="registerOtpInput" class="form-control auth-input text-center fs-3 font-monospace py-2" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="one-time-code" style="letter-spacing: 4px;">
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-dark py-2 fw-medium rounded-1 auth-otp-submit-btn">Verify & Create Account</button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small">
                            <button type="button" class="btn btn-link btn-sm text-dark text-decoration-underline p-0" id="btnResendRegisterOtp">Resend code</button>
                            <button type="button" class="btn btn-link btn-sm text-muted p-0" id="btnBackToRegisterDetails">&larr; Edit details</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Flash Notifications Banner -->
<?php
$successMessages = get_flash('success');
$errorMessages = get_flash('error');
$infoMessages = get_flash('info');
?>
<?php if (!empty($successMessages) || !empty($errorMessages) || !empty($infoMessages)): ?>
    <div class="container mt-3">
        <?php foreach ($successMessages as $msg): ?>
            <div class="alert alert-dark alert-dismissible fade show text-white bg-dark border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= e($msg) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
        <?php foreach ($errorMessages as $msg): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= e($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
        <?php foreach ($infoMessages as $msg): ?>
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-info-circle me-2"></i><?= e($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
