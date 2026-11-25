<?php
/**
 * ONS SOUND — Order Booking Confirmation Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Order.php';

$orderModel = new Order();
$orderNumber = clean($_GET['order'] ?? '');
$order = null;

if (!empty($orderNumber)) {
    $order = $orderModel->getByOrderNumber($orderNumber);
}

$pageTitle = $order ? ('Order Confirmed: ' . $order['order_number']) : 'Order Confirmation';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Confirmation Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">Order Confirmation</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <?php if (!$order): ?>
        <div class="card border-0 shadow-sm p-5 text-center rounded-3 my-4 mx-auto" style="max-width: 600px;">
            <i class="bi bi-question-circle display-4 text-muted mb-3"></i>
            <h4 class="fw-semibold text-dark">Order Not Found</h4>
            <p class="text-muted small mb-4">We could not locate the requested order booking. Please verify the order number or check your account order history.</p>
            <div>
                <a href="<?= url('products.php') ?>" class="btn btn-dark rounded-1 px-4 py-2 fw-medium">
                    Explore Product Collection &rarr;
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <!-- Success Notice Card -->
                <div class="card border-0 shadow-sm p-4 p-md-5 rounded-3 mb-4 text-center">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-circle" style="width: 72px; height: 72px;">
                            <i class="bi bi-check2 fs-1"></i>
                        </span>
                    </div>
                    <h2 class="fs-3 fw-bold text-dark mb-1">Thank You! Your Booking is Confirmed</h2>
                    <p class="text-muted small mb-3">
                        Order <strong>#<?= e($order['order_number']) ?></strong> has been successfully booked on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?>.
                    </p>
                    <div class="alert alert-light border py-2 px-3 small mx-auto text-secondary mb-4" style="max-width: 540px;">
                        <i class="bi bi-envelope-check me-1 text-dark"></i>
                        A booking confirmation with full specifications has been sent to <strong><?= e($order['customer_email']) ?></strong>.
                    </div>

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-1 px-3 py-2" onclick="window.print();">
                            <i class="bi bi-printer me-1"></i>Print Receipt
                        </button>
                        <?php if (is_logged_in()): ?>
                            <a href="<?= url('orders.php') ?>" class="btn btn-dark btn-sm rounded-1 px-3 py-2">
                                <i class="bi bi-box-seam me-1"></i>View in My Orders
                            </a>
                        <?php endif; ?>
                        <a href="<?= url('products.php') ?>" class="btn btn-outline-secondary btn-sm rounded-1 px-3 py-2">
                            Continue Shopping &rarr;
                        </a>
                    </div>
                </div>

                <!-- Order Details & Summary Card -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h5 class="fw-semibold text-dark mb-3 border-bottom pb-2">Order Information</h5>

                    <div class="row g-3 mb-4 small">
                        <div class="col-md-6">
                            <span class="text-muted d-block mb-1">Customer Name:</span>
                            <strong class="text-dark d-block"><?= e($order['customer_name']) ?></strong>
                            <span class="text-muted d-block mt-2 mb-1">Contact Phone:</span>
                            <span class="text-dark d-block"><?= e($order['customer_phone']) ?></span>
                            <span class="text-muted d-block mt-2 mb-1">Payment Method:</span>
                            <span class="badge bg-light text-dark border"><?= e($order['payment_method']) ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block mb-1">Delivery Address:</span>
                            <div class="text-dark lh-sm" style="white-space: pre-line;"><?= e($order['shipping_address']) ?></div>
                            <?php if (!empty($order['customer_notes'])): ?>
                                <span class="text-muted d-block mt-2 mb-1">Order Notes:</span>
                                <span class="text-secondary d-block fst-italic"><?= e($order['customer_notes']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h6 class="fw-semibold text-dark mb-3">Purchased Audio Products</h6>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="border-0">Product</th>
                                    <th scope="col" class="border-0 text-center">Quantity</th>
                                    <th scope="col" class="border-0 text-end">Price</th>
                                    <th scope="col" class="border-0 text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-light rounded p-1 text-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                                                    <img src="<?= asset($item['product_image'] ?? 'assets/images/products/speaker-white.png') ?>" alt="<?= e($item['product_name']) ?>" class="img-fluid" style="max-height: 40px; object-fit: contain;">
                                                </div>
                                                <div>
                                                    <strong class="text-dark small d-block"><?= e($item['product_name']) ?></strong>
                                                    <?php if (!empty($item['product_sku'])): ?>
                                                        <span class="text-muted small">SKU: <?= e($item['product_sku']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center small"><?= (int)$item['quantity'] ?></td>
                                        <td class="text-end small"><?= format_price($item['price']) ?></td>
                                        <td class="text-end small fw-semibold"><?= format_price($item['subtotal']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end small pt-3 text-muted">Subtotal</td>
                                    <td class="text-end small pt-3 fw-medium"><?= format_price($order['subtotal']) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end small text-muted">Shipping</td>
                                    <td class="text-end small text-success fw-medium">Free</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fs-6 fw-bold border-0">Total Amount</td>
                                    <td class="text-end fs-5 fw-bold text-dark border-0"><?= format_price($order['total_amount']) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
