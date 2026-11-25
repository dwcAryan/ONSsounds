<?php
/**
 * ONS SOUND — Individual Order Details & Status Tracking Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Order.php';

require_login('orders.php');

$user = current_user();
$orderModel = new Order();

$orderId = (int)($_GET['id'] ?? 0);
$order = null;

if ($orderId > 0) {
    // Strictly verify ownership - user can only see their own order
    $order = $orderModel->getUserOrderWithItems($orderId, $user['id']);
}

if (!$order) {
    set_flash('error', 'Order not found or you do not have permission to view it.');
    redirect('orders.php');
}

$statusSteps = [
    'pending' => 'Booking Received',
    'confirmed' => 'Order Confirmed',
    'processing' => 'Acoustic Check & Packing',
    'shipped' => 'Dispatched',
    'delivered' => 'Delivered'
];

$allStatuses = array_keys($statusSteps);
$currentStatusIndex = array_search($order['order_status'], $allStatuses);
if ($currentStatusIndex === false && $order['order_status'] === 'cancelled') {
    $currentStatusIndex = -1;
}

$pageTitle = 'Order ' . $order['order_number'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Order Details Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">Order #<?= e($order['order_number']) ?></h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-secondary text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('profile.php') ?>" class="text-secondary text-decoration-none">My Account</a></li>
            <li class="breadcrumb-item"><a href="<?= url('orders.php') ?>" class="text-secondary text-decoration-none">Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($order['order_number']) ?></li>
        </ol>
    </nav>

    <!-- Header Actions -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="fs-3 fw-bold text-dark mb-1">Order #<?= e($order['order_number']) ?></h1>
            <p class="text-muted small mb-0">Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-dark btn-sm rounded-1 px-3" onclick="window.print();">
                <i class="bi bi-printer me-1"></i>Print Invoice
            </button>
            <a href="<?= url('orders.php') ?>" class="btn btn-dark btn-sm rounded-1 px-3">
                &larr; Back to Orders
            </a>
        </div>
    </div>

    <!-- Visual Order Status Tracking Stepper -->
    <div class="card border-0 shadow-sm rounded-3 p-4 p-md-5 mb-4">
        <h5 class="fw-semibold text-dark mb-4">Shipment & Delivery Tracker</h5>

        <?php if ($order['order_status'] === 'cancelled'): ?>
            <div class="alert alert-danger mb-0">
                <i class="bi bi-x-circle me-2"></i>This order has been <strong>Cancelled</strong>. Please reach out to customer support if you need assistance.
            </div>
        <?php else: ?>
            <div class="order-stepper position-relative py-3">
                <div class="row text-center position-relative z-1 g-2">
                    <?php foreach ($allStatuses as $idx => $statusKey): ?>
                        <?php
                        $isCompleted = ($currentStatusIndex !== false && $idx <= $currentStatusIndex);
                        $isCurrent = ($idx === $currentStatusIndex);
                        ?>
                        <div class="col">
                            <div class="stepper-node d-inline-flex align-items-center justify-content-center rounded-circle mb-2 <?= $isCompleted ? 'bg-dark text-white' : 'bg-light text-muted border' ?>" style="width: 38px; height: 38px; font-size: 14px;">
                                <?php if ($isCompleted && !$isCurrent): ?>
                                    <i class="bi bi-check-lg"></i>
                                <?php else: ?>
                                    <?= $idx + 1 ?>
                                <?php endif; ?>
                            </div>
                            <div class="stepper-label small <?= $isCurrent ? 'fw-bold text-dark' : ($isCompleted ? 'text-dark' : 'text-muted') ?>" style="font-size: 0.8rem;">
                                <?= e($statusSteps[$statusKey]) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-4 g-lg-5">
        <!-- Left: Order Items Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h5 class="fw-semibold text-dark mb-4">Ordered Transducers & Components</h5>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="border-0">Item</th>
                                <th scope="col" class="border-0 text-center">Qty</th>
                                <th scope="col" class="border-0 text-end">Price</th>
                                <th scope="col" class="border-0 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light rounded-2 p-1 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; flex-shrink: 0;">
                                                <img src="<?= asset($item['product_image'] ?? 'assets/images/products/speaker-white.png') ?>" alt="<?= e($item['product_name']) ?>" class="img-fluid" style="max-height: 44px; object-fit: contain;">
                                            </div>
                                            <div>
                                                <strong class="d-block text-dark small"><?= e($item['product_name']) ?></strong>
                                                <?php if (!empty($item['product_sku'])): ?>
                                                    <span class="text-muted" style="font-size: 0.75rem;">SKU: <?= e($item['product_sku']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center small"><?= (int)$item['quantity'] ?></td>
                                    <td class="text-end small"><?= format_price($item['price']) ?></td>
                                    <td class="text-end fw-semibold text-dark small"><?= format_price($item['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($order['customer_notes'])): ?>
                    <div class="mt-3 p-3 bg-light rounded-2 small">
                        <strong>Order Notes:</strong> <?= e($order['customer_notes']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Order Summary & Shipping Address -->
        <div class="col-lg-4">
            <!-- Order Financial Summary -->
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-semibold text-dark mb-3">Order Summary</h5>

                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Subtotal</span>
                    <span class="text-dark fw-medium"><?= format_price($order['subtotal']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Shipping</span>
                    <span class="text-success fw-medium"><?= $order['shipping_fee'] > 0 ? format_price($order['shipping_fee']) : 'Free' ?></span>
                </div>
                <hr class="my-3">
                <div class="d-flex justify-content-between mb-0">
                    <strong class="text-dark fs-6">Total Amount</strong>
                    <strong class="text-dark fs-5"><?= format_price($order['total_amount']) ?></strong>
                </div>

                <div class="mt-3 pt-3 border-top small text-muted">
                    <span class="d-block mb-1"><strong>Payment Method:</strong></span>
                    <?= e($order['payment_method']) ?>
                </div>
            </div>

            <!-- Delivery Address Card -->
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h5 class="fw-semibold text-dark mb-3">Delivery Destination</h5>

                <div class="fw-semibold text-dark small mb-1"><?= e($order['customer_name']) ?></div>
                <div class="text-muted small mb-3" style="line-height: 1.6;">
                    <?= nl2br(e($order['shipping_address'])) ?>
                </div>

                <div class="small text-muted mb-1">
                    <i class="bi bi-telephone me-1"></i><?= e($order['customer_phone']) ?>
                </div>
                <div class="small text-muted">
                    <i class="bi bi-envelope me-1"></i><?= e($order['customer_email']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
