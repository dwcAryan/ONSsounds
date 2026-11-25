<?php
/**
 * ONS SOUND — Customer Order History Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Order.php';

require_login('orders.php');

$user = current_user();
$orderModel = new Order();
$orders = $orderModel->getUserOrders($user['id']);

$pageTitle = 'Order History';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Orders Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">Order History</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-secondary text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('profile.php') ?>" class="text-secondary text-decoration-none">My Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">Order History</li>
        </ol>
    </nav>

    <div class="row g-4 g-lg-5">
        <!-- Account Sidebar Navigation -->
        <div class="col-lg-3 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                <nav class="nav flex-column gap-1">
                    <a href="<?= url('profile.php') ?>" class="nav-link py-2 px-3 rounded-2 text-secondary hover-dark">
                        <i class="bi bi-person-gear me-2"></i>Account Details
                    </a>
                    <a href="<?= url('addresses.php') ?>" class="nav-link py-2 px-3 rounded-2 text-secondary hover-dark">
                        <i class="bi bi-geo-alt me-2"></i>My Addresses
                    </a>
                    <a href="<?= url('orders.php') ?>" class="nav-link py-2 px-3 rounded-2 fw-medium bg-dark text-white">
                        <i class="bi bi-box-seam me-2"></i>Order History
                        <span class="badge bg-secondary rounded-pill float-end"><?= count($orders) ?></span>
                    </a>
                    <a href="<?= url('wishlist.php') ?>" class="nav-link py-2 px-3 rounded-2 text-secondary hover-dark">
                        <i class="bi bi-heart me-2"></i>My Wishlist
                    </a>
                    <hr class="my-2">
                    <a href="<?= url('logout.php') ?>" class="nav-link py-2 px-3 rounded-2 text-danger hover-dark">
                        <i class="bi bi-box-arrow-right me-2"></i>Log Out
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Orders List -->
        <div class="col-lg-9 col-md-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="fs-3 fw-bold text-dark mb-1">Order History</h1>
                    <p class="text-muted small mb-0">Track shipments, view invoices, and review past purchases.</p>
                </div>
            </div>

            <?php if (empty($orders)): ?>
                <div class="card border-0 shadow-sm p-5 text-center rounded-3">
                    <i class="bi bi-bag-x display-4 text-muted mb-3"></i>
                    <h5 class="fw-semibold text-dark">No Orders Placed Yet</h5>
                    <p class="text-muted small mb-4">You have not booked or placed any orders with ONS Sound yet.</p>
                    <div>
                        <a href="<?= url('products.php') ?>" class="btn btn-dark rounded-1 px-4 py-2 fw-medium">
                            Explore Audio Catalog &rarr;
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($orders as $ord): ?>
                        <?php
                        $statusBadgeClass = match ($ord['order_status']) {
                            'confirmed' => 'bg-info text-dark',
                            'processing' => 'bg-primary text-white',
                            'shipped' => 'bg-warning text-dark',
                            'delivered' => 'bg-success text-white',
                            'cancelled' => 'bg-danger text-white',
                            default => 'bg-secondary text-white'
                        };
                        ?>
                        <div class="card border-0 shadow-sm rounded-3 p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md-3">
                                    <span class="text-muted small d-block">Order Number</span>
                                    <strong class="text-dark fs-6"><?= e($ord['order_number']) ?></strong>
                                    <small class="text-muted d-block"><?= date('M d, Y', strtotime($ord['created_at'])) ?></small>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted small d-block">Status</span>
                                    <span class="badge <?= $statusBadgeClass ?> rounded-pill px-3 py-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">
                                        <?= e(ucfirst($ord['order_status'])) ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted small d-block">Total Amount</span>
                                    <strong class="text-dark fs-5"><?= format_price($ord['total_amount']) ?></strong>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <a href="<?= url('order-details.php?id=' . (int)$ord['id']) ?>" class="btn btn-outline-dark rounded-1 px-3 py-2 btn-sm fw-medium">
                                        View Details &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
