<?php
/**
 * ONS SOUND — WordPress-Style Admin Header & Navigation
 */

require_once __DIR__ . '/../auth.php';
require_admin();

$currentAdmin = current_admin();
$adminName = $currentAdmin['full_name'] ?? 'Admin';
$adminEmail = $currentAdmin['email'] ?? '';

// Active page determination
$currentScript = basename($_SERVER['PHP_SELF']);
$currentStatusFilter = clean($_GET['status'] ?? '');

// Unread/Pending counts for sidebar badges
$db = get_db();
$pendingOrdersCount = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE order_status = 'pending'")->fetchColumn();
$newEnquiriesCount = (int)$db->query("SELECT COUNT(*) FROM `contact_enquiries` WHERE status = 'new'")->fetchColumn();
$pendingReviewsCount = (int)$db->query("SELECT COUNT(*) FROM `product_reviews` WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en" class="wp-toolbar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= !empty($pageTitle) ? e($pageTitle) . ' &lsaquo; ' : '' ?>ONS SOUND &#8212; WordPress</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- WordPress-Style Admin Panel CSS -->
    <link rel="stylesheet" href="<?= admin_url('assets/css/admin.css') ?>">
</head>
<body class="wp-admin wp-core-ui">

<!-- Top Admin Bar (#wpadminbar) -->
<div id="wpadminbar" role="navigation" aria-label="Toolbar">
    <div class="ab-toolbar-left">
        <button id="wp-responsive-toggle" type="button" aria-label="Toggle navigation menu">
            <i class="bi bi-list"></i>
        </button>
        <a href="<?= admin_url('index.php') ?>" class="ab-brand" title="ONS SOUND Admin">
            <i class="bi bi-soundwave me-1" style="color: #72aee6;"></i> ONS SOUND
        </a>
        <div class="ab-divider"></div>
        <a href="<?= url('index.php') ?>" target="_blank" class="ab-item" title="Visit Storefront">
            <i class="bi bi-house me-1"></i> Visit Site
        </a>
        <?php if ($pendingOrdersCount > 0): ?>
            <a href="<?= admin_url('orders.php?status=pending') ?>" class="ab-item" title="<?= $pendingOrdersCount ?> Pending Orders">
                <i class="bi bi-cart me-1"></i> Orders <span class="ab-item-badge"><?= $pendingOrdersCount ?></span>
            </a>
        <?php endif; ?>
        <?php if ($newEnquiriesCount > 0): ?>
            <a href="<?= admin_url('enquiries.php') ?>" class="ab-item" title="<?= $newEnquiriesCount ?> New Messages">
                <i class="bi bi-chat-left-text me-1"></i> Inquiries <span class="ab-item-badge"><?= $newEnquiriesCount ?></span>
            </a>
        <?php endif; ?>
    </div>

    <div class="ab-toolbar-right">
        <a href="<?= admin_url('users.php') ?>" class="ab-item ab-user-profile" title="My Account">
            Howdy, <strong style="margin-left: 4px;"><?= e($adminName) ?></strong>
        </a>
        <a href="<?= admin_url('logout.php') ?>" class="ab-item ab-logout" title="Log Out">
            <i class="bi bi-box-arrow-right me-1"></i> Log Out
        </a>
    </div>
</div>

<!-- Backdrop Overlay for Mobile Navigation -->
<div id="wp-responsive-overlay"></div>

<!-- Left Sidebar Navigation Menu (#adminmenu) -->
<div id="adminmenuback"></div>
<div id="adminmenuwrap">
    <ul id="adminmenu">
        <!-- Dashboard -->
        <?php $isDash = in_array($currentScript, ['index.php', 'activity.php']); ?>
        <li class="menu-top wp-has-submenu <?= $isDash ? 'wp-has-current-submenu wp-menu-open current' : '' ?>">
            <a href="<?= admin_url('index.php') ?>" class="menu-top-link">
                <i class="bi bi-speedometer2 menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
            <ul class="wp-submenu">
                <li class="<?= ($currentScript === 'index.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('index.php') ?>">Overview</a>
                </li>
                <li class="<?= ($currentScript === 'activity.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('activity.php') ?>">Activity</a>
                </li>
            </ul>
        </li>

        <li class="menu-separator"></li>

        <!-- Products -->
        <?php $isProd = in_array($currentScript, ['products.php', 'product-edit.php', 'categories.php', 'reviews.php']); ?>
        <li class="menu-top wp-has-submenu <?= $isProd ? 'wp-has-current-submenu wp-menu-open current' : '' ?>">
            <a href="<?= admin_url('products.php') ?>" class="menu-top-link">
                <i class="bi bi-box-seam menu-icon"></i>
                <span class="menu-title">Products</span>
                <?php if ($pendingReviewsCount > 0): ?>
                    <span class="menu-badge" style="background:#d63638;"><?= $pendingReviewsCount ?></span>
                <?php endif; ?>
            </a>
            <ul class="wp-submenu">
                <li class="<?= ($currentScript === 'products.php' && empty($_GET['action'])) ? 'current' : '' ?>">
                    <a href="<?= admin_url('products.php') ?>">All Products</a>
                </li>
                <li class="<?= ($currentScript === 'product-edit.php' && empty($_GET['id'])) ? 'current' : '' ?>">
                    <a href="<?= admin_url('product-edit.php') ?>">Add New Product</a>
                </li>
                <li class="<?= ($currentScript === 'categories.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('categories.php') ?>">Categories</a>
                </li>
                <li class="<?= ($currentScript === 'reviews.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('reviews.php') ?>">Product Reviews</a>
                </li>
            </ul>
        </li>

        <!-- Orders -->
        <?php $isOrders = in_array($currentScript, ['orders.php', 'order-view.php']); ?>
        <li class="menu-top wp-has-submenu <?= $isOrders ? 'wp-has-current-submenu wp-menu-open current' : '' ?>">
            <a href="<?= admin_url('orders.php') ?>" class="menu-top-link">
                <i class="bi bi-receipt menu-icon"></i>
                <span class="menu-title">Orders</span>
                <?php if ($pendingOrdersCount > 0): ?>
                    <span class="menu-badge"><?= $pendingOrdersCount ?></span>
                <?php endif; ?>
            </a>
            <ul class="wp-submenu">
                <li class="<?= ($currentScript === 'orders.php' && empty($currentStatusFilter)) ? 'current' : '' ?>">
                    <a href="<?= admin_url('orders.php') ?>">All Orders</a>
                </li>
                <li class="<?= ($currentScript === 'orders.php' && $currentStatusFilter === 'pending') ? 'current' : '' ?>">
                    <a href="<?= admin_url('orders.php?status=pending') ?>">Pending Orders</a>
                </li>
                <li class="<?= ($currentScript === 'orders.php' && $currentStatusFilter === 'processing') ? 'current' : '' ?>">
                    <a href="<?= admin_url('orders.php?status=processing') ?>">Processing Orders</a>
                </li>
                <li class="<?= ($currentScript === 'orders.php' && $currentStatusFilter === 'delivered') ? 'current' : '' ?>">
                    <a href="<?= admin_url('orders.php?status=delivered') ?>">Completed Orders</a>
                </li>
                <li class="<?= ($currentScript === 'orders.php' && $currentStatusFilter === 'cancelled') ? 'current' : '' ?>">
                    <a href="<?= admin_url('orders.php?status=cancelled') ?>">Cancelled Orders</a>
                </li>
            </ul>
        </li>

        <!-- Customers -->
        <?php $isCust = in_array($currentScript, ['customers.php', 'customer-view.php']); ?>
        <li class="menu-top wp-has-submenu <?= $isCust ? 'wp-has-current-submenu wp-menu-open current' : '' ?>">
            <a href="<?= admin_url('customers.php') ?>" class="menu-top-link">
                <i class="bi bi-people menu-icon"></i>
                <span class="menu-title">Customers</span>
            </a>
            <ul class="wp-submenu">
                <li class="<?= ($currentScript === 'customers.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('customers.php') ?>">All Customers</a>
                </li>
            </ul>
        </li>

        <li class="menu-separator"></li>

        <!-- Appearance -->
        <?php $isApp = in_array($currentScript, ['appearance-home.php', 'appearance-banners.php', 'menus.php', 'appearance-footer.php']); ?>
        <li class="menu-top wp-has-submenu <?= $isApp ? 'wp-has-current-submenu wp-menu-open current' : '' ?>">
            <a href="<?= admin_url('appearance-home.php') ?>" class="menu-top-link">
                <i class="bi bi-palette menu-icon"></i>
                <span class="menu-title">Appearance</span>
            </a>
            <ul class="wp-submenu">
                <li class="<?= ($currentScript === 'appearance-home.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('appearance-home.php') ?>">Homepage Sections</a>
                </li>
                <li class="<?= ($currentScript === 'appearance-banners.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('appearance-banners.php') ?>">Homepage Banners</a>
                </li>
                <li class="<?= ($currentScript === 'menus.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('menus.php') ?>">Navigation Menus</a>
                </li>
                <li class="<?= ($currentScript === 'appearance-footer.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('appearance-footer.php') ?>">Footer Settings</a>
                </li>
            </ul>
        </li>

        <!-- Content (Blog) -->
        <?php $isBlog = in_array($currentScript, ['posts.php', 'post-edit.php', 'post-categories.php']); ?>
        <li class="menu-top wp-has-submenu <?= $isBlog ? 'wp-has-current-submenu wp-menu-open current' : '' ?>">
            <a href="<?= admin_url('posts.php') ?>" class="menu-top-link">
                <i class="bi bi-pin-angle menu-icon"></i>
                <span class="menu-title">Content</span>
            </a>
            <ul class="wp-submenu">
                <li class="<?= ($currentScript === 'posts.php' && empty($_GET['action'])) ? 'current' : '' ?>">
                    <a href="<?= admin_url('posts.php') ?>">Blog Posts</a>
                </li>
                <li class="<?= ($currentScript === 'post-edit.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('post-edit.php') ?>">Add New Post</a>
                </li>
                <li class="<?= ($currentScript === 'post-categories.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('post-categories.php') ?>">Blog Categories</a>
                </li>
            </ul>
        </li>

        <!-- Marketing -->
        <?php $isMkt = in_array($currentScript, ['coupons.php', 'reward-popup.php', 'subscribers.php']); ?>
        <li class="menu-top wp-has-submenu <?= $isMkt ? 'wp-has-current-submenu wp-menu-open current' : '' ?>">
            <a href="<?= admin_url('coupons.php') ?>" class="menu-top-link">
                <i class="bi bi-megaphone menu-icon"></i>
                <span class="menu-title">Marketing</span>
            </a>
            <ul class="wp-submenu">
                <li class="<?= ($currentScript === 'coupons.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('coupons.php') ?>">Discount Coupons</a>
                </li>
                <li class="<?= ($currentScript === 'reward-popup.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('reward-popup.php') ?>">Reward Popup</a>
                </li>
                <li class="<?= ($currentScript === 'subscribers.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('subscribers.php') ?>">Newsletter Subscribers</a>
                </li>
            </ul>
        </li>

        <!-- Enquiries -->
        <?php $isEnq = in_array($currentScript, ['enquiries.php']); ?>
        <li class="menu-top <?= $isEnq ? 'current' : '' ?>">
            <a href="<?= admin_url('enquiries.php') ?>" class="menu-top-link">
                <i class="bi bi-chat-square-text menu-icon"></i>
                <span class="menu-title">Enquiries</span>
                <?php if ($newEnquiriesCount > 0): ?>
                    <span class="menu-badge" style="background:#d63638;"><?= $newEnquiriesCount ?></span>
                <?php endif; ?>
            </a>
        </li>

        <li class="menu-separator"></li>

        <!-- Settings -->
        <?php $isSet = in_array($currentScript, ['settings.php', 'settings-store.php', 'settings-email.php', 'users.php']); ?>
        <li class="menu-top wp-has-submenu <?= $isSet ? 'wp-has-current-submenu wp-menu-open current' : '' ?>">
            <a href="<?= admin_url('settings.php') ?>" class="menu-top-link">
                <i class="bi bi-gear menu-icon"></i>
                <span class="menu-title">Settings</span>
            </a>
            <ul class="wp-submenu">
                <li class="<?= ($currentScript === 'settings.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('settings.php') ?>">General Settings</a>
                </li>
                <li class="<?= ($currentScript === 'settings-store.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('settings-store.php') ?>">Store Settings</a>
                </li>
                <li class="<?= ($currentScript === 'settings-email.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('settings-email.php') ?>">Email / SMTP Settings</a>
                </li>
                <li class="<?= ($currentScript === 'users.php') ? 'current' : '' ?>">
                    <a href="<?= admin_url('users.php') ?>">Admin Users</a>
                </li>
            </ul>
        </li>
    </ul>
</div>

<!-- Main Content Area (#wpcontent) -->
<div id="wpcontent">
    <div class="wrap">
        <!-- Display Flash Notices -->
        <?php
        $successNotices = admin_get_flash('success');
        $errorNotices = admin_get_flash('error');
        $warningNotices = admin_get_flash('warning');
        $infoNotices = admin_get_flash('info');
        ?>
        <?php foreach ($successNotices as $n): ?>
            <div class="notice notice-success is-dismissible"><p><?= e($n) ?></p></div>
        <?php endforeach; ?>
        <?php foreach ($errorNotices as $n): ?>
            <div class="notice notice-error is-dismissible"><p><?= e($n) ?></p></div>
        <?php endforeach; ?>
        <?php foreach ($warningNotices as $n): ?>
            <div class="notice notice-warning is-dismissible"><p><?= e($n) ?></p></div>
        <?php endforeach; ?>
        <?php foreach ($infoNotices as $n): ?>
            <div class="notice notice-info is-dismissible"><p><?= e($n) ?></p></div>
        <?php endforeach; ?>
