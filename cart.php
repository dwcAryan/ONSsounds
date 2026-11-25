<?php
/**
 * ONS SOUND — Shopping Cart Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Cart.php';

require_login('cart.php');

$cartModel = new Cart();
$userId = (int)$_SESSION['user_id'];
$cartId = $cartModel->getActiveCartId($userId, null);

// Handle direct form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect('cart.php');
    }

    $action = clean($_POST['action'] ?? '');
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if ($action === 'update' && $productId > 0) {
        $cartModel->updateQuantity($cartId, $productId, $quantity);
        set_flash('success', 'Cart updated.');
        redirect('cart.php');
    } elseif ($action === 'remove' && $productId > 0) {
        $cartModel->removeItem($cartId, $productId);
        set_flash('success', 'Item removed from your cart.');
        redirect('cart.php');
    } elseif ($action === 'clear') {
        $cartModel->clearCart($cartId);
        set_flash('success', 'Your cart has been cleared.');
        redirect('cart.php');
    }
}

$items = $cartModel->getItems($cartId);
$subtotal = $cartModel->getSubtotal($cartId);

$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Cart Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">Shopping Cart</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-secondary text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fs-4 fw-bold text-dark mb-1">Your Items (<?= count($items) ?>)</h2>
            <p class="text-muted small mb-0">Review your selected precision sound products.</p>
        </div>
        <?php if (!empty($items)): ?>
            <form action="<?= url('cart.php') ?>" method="POST" onsubmit="return confirm('Clear all items from your cart?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-1 px-3">
                    <i class="bi bi-trash me-1"></i>Clear Cart
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($items)): ?>
        <div class="card border-0 shadow-sm p-5 text-center rounded-3 my-4">
            <i class="bi bi-bag-x display-4 text-muted mb-3"></i>
            <h4 class="fw-semibold text-dark">Your Cart is Empty</h4>
            <p class="text-muted small mb-4">You have not added any audio transducers or speakers to your cart yet.</p>
            <div>
                <a href="<?= url('products.php') ?>" class="btn btn-dark rounded-1 px-4 py-2 fw-medium">
                    Explore Product Collection &rarr;
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4 g-lg-5">
            <!-- Left: Cart Items Table -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="border-0">Product</th>
                                    <th scope="col" class="border-0">Price</th>
                                    <th scope="col" class="border-0 text-center">Quantity</th>
                                    <th scope="col" class="border-0 text-end">Total</th>
                                    <th scope="col" class="border-0 text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="<?= url('product-details.php?slug=' . urlencode($item['slug'])) ?>" class="bg-light rounded-2 p-1 d-flex align-items-center justify-content-center text-decoration-none" style="width: 64px; height: 64px; flex-shrink: 0;">
                                                    <img src="<?= asset($item['main_image'] ?? 'assets/images/products/speaker-white.png') ?>" alt="<?= e($item['name']) ?>" class="img-fluid" style="max-height: 54px; object-fit: contain;">
                                                </a>
                                                <div>
                                                    <a href="<?= url('product-details.php?slug=' . urlencode($item['slug'])) ?>" class="text-dark fw-semibold text-decoration-none small d-block">
                                                        <?= e($item['name']) ?>
                                                    </a>
                                                    <span class="text-muted small">In Stock</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark small fw-medium"><?= format_price($item['price']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <form action="<?= url('cart.php') ?>" method="POST" class="d-inline-flex align-items-center justify-content-center border rounded-2 px-1 py-0 bg-light">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                                <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>" min="1" max="99" class="form-control form-control-sm border-0 text-center bg-transparent p-0" style="width: 44px; font-weight: 600;" onchange="this.form.submit();">
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-dark small"><?= format_price($item['subtotal']) ?></strong>
                                        </td>
                                        <td class="text-end">
                                            <form action="<?= url('cart.php') ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                                <button type="submit" class="btn btn-link text-danger p-0" title="Remove item">
                                                    <i class="bi bi-x-circle fs-5"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <h5 class="fw-semibold text-dark mb-3">Cart Summary</h5>

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal</span>
                        <span class="text-dark fw-medium"><?= format_price($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Delivery</span>
                        <span class="text-success fw-medium">Free</span>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between mb-4">
                        <strong class="text-dark fs-6">Estimated Total</strong>
                        <strong class="text-dark fs-5"><?= format_price($subtotal) ?></strong>
                    </div>

                    <a href="<?= url('checkout.php') ?>" class="btn btn-dark w-100 py-3 fw-medium rounded-1 mb-2 text-uppercase fs-7 letter-spacing-1">
                        Proceed to Checkout &rarr;
                    </a>

                    <div class="text-center pt-2">
                        <a href="<?= url('products.php') ?>" class="text-muted small text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
