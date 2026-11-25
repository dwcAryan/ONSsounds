<?php
/**
 * ONS SOUND — Checkout & Order Booking Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Cart.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/User.php';

require_login('checkout.php');

$cartModel = new Cart();
$orderModel = new Order();
$userModel = new User();

$userId = (int)$_SESSION['user_id'];
$cartId = $cartModel->getActiveCartId($userId, null);

$items = $cartModel->getItems($cartId);
$subtotal = $cartModel->getSubtotal($cartId);

// If cart is empty, redirect to cart page
if (empty($items)) {
    set_flash('error', 'Your shopping cart is empty. Please add items before proceeding to checkout.');
    redirect('cart.php');
}

$currentUser = null;
$savedAddresses = [];
$defaultAddress = null;

if (is_logged_in() && $userId) {
    $currentUser = $userModel->getById($userId);
    $savedAddresses = $userModel->getAddresses($userId);
    $defaultAddress = $userModel->getDefaultAddress($userId);
}

$error = '';

// Handle Checkout Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $customerName = clean($_POST['name'] ?? ($currentUser['full_name'] ?? ''));
        $customerEmail = filter_var($_POST['email'] ?? ($currentUser['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $customerPhone = clean($_POST['phone'] ?? ($currentUser['phone'] ?? ''));
        $customerNotes = clean($_POST['notes'] ?? '');

        // Address resolution
        $addressOption = clean($_POST['address_option'] ?? 'new');
        $formattedAddress = '';

        if ($addressOption === 'saved' && !empty($_POST['saved_address_id']) && $userId) {
            $addrId = (int)$_POST['saved_address_id'];
            $selectedAddr = $userModel->getAddressById($addrId, $userId);
            if ($selectedAddr) {
                $formattedAddress = "{$selectedAddr['recipient_name']}\n{$selectedAddr['address_line1']}" .
                    (!empty($selectedAddr['address_line2']) ? ", {$selectedAddr['address_line2']}" : "") .
                    "\n{$selectedAddr['city']}, {$selectedAddr['state']} - {$selectedAddr['postal_code']}\n{$selectedAddr['country']}\nPhone: {$selectedAddr['phone']}";
            }
        }

        if (empty($formattedAddress)) {
            $line1 = clean($_POST['address_line1'] ?? '');
            $line2 = clean($_POST['address_line2'] ?? '');
            $city = clean($_POST['city'] ?? '');
            $state = clean($_POST['state'] ?? '');
            $postalCode = clean($_POST['postal_code'] ?? '');
            $country = clean($_POST['country'] ?? 'India');

            if (empty($line1) || empty($city) || empty($postalCode)) {
                $error = 'Please fill in all mandatory delivery address fields.';
            } else {
                $formattedAddress = "{$customerName}\n{$line1}" .
                    (!empty($line2) ? ", {$line2}" : "") .
                    "\n{$city}, {$state} - {$postalCode}\n{$country}\nPhone: {$customerPhone}";

                // Optionally save this address if user is logged in
                if (!empty($_POST['save_to_address_book']) && $userId) {
                    try {
                        $userModel->addAddress($userId, [
                            'address_title' => 'Delivery Address',
                            'recipient_name' => $customerName,
                            'phone' => $customerPhone,
                            'address_line1' => $line1,
                            'address_line2' => $line2,
                            'city' => $city,
                            'state' => $state,
                            'postal_code' => $postalCode,
                            'country' => $country,
                            'is_default' => empty($savedAddresses) ? 1 : 0
                        ]);
                    } catch (Exception $e) {}
                }
            }
        }

        if (empty($customerName) || !$customerEmail || empty($customerPhone)) {
            $error = 'Please provide your full name, valid email address, and contact phone number.';
        } elseif (empty($error)) {
            try {
                $orderData = [
                    'user_id' => $userId,
                    'cart_id' => $cartId,
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $customerPhone,
                    'address' => $formattedAddress,
                    'notes' => $customerNotes
                ];

                $booking = $orderModel->createBooking($orderData, $items);

                // Redirect to confirmation page with clean URL
                redirect('order-confirmation.php?order=' . urlencode($booking['order_number']));
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            } catch (Exception $e) {
                error_log("Checkout error: " . $e->getMessage());
                $error = 'Could not place order booking. Please verify product availability and try again.';
            }
        }
    }
}

$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Checkout Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">Order Checkout</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-secondary text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('cart.php') ?>" class="text-secondary text-decoration-none">Shopping Cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-3 small mb-4 rounded-2">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('checkout.php') ?>" method="POST" id="checkoutForm">
        <?= csrf_field() ?>

        <div class="row g-4 g-lg-5">
            <!-- Left Column: Customer & Delivery Details Form -->
            <div class="col-lg-7">
                <!-- 1. Contact Information -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h5 class="fw-semibold text-dark mb-0"><i class="bi bi-person me-2"></i>1. Customer Information</h5>
                        <?php if (!is_logged_in()): ?>
                            <span class="small text-muted">
                                Have an account? <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#loginModal" class="text-dark fw-medium text-decoration-underline">Sign In</a>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small text-muted">Full Name *</label>
                            <input type="text" name="name" class="form-control auth-input" value="<?= e($_POST['name'] ?? $currentUser['full_name'] ?? '') ?>" required placeholder="John Doe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Email Address * (For order confirmation)</label>
                            <input type="email" name="email" class="form-control auth-input" value="<?= e($_POST['email'] ?? $currentUser['email'] ?? '') ?>" required placeholder="john@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Phone Number * (For delivery updates)</label>
                            <input type="tel" name="phone" class="form-control auth-input" value="<?= e($_POST['phone'] ?? $currentUser['phone'] ?? '') ?>" required placeholder="+91 98765 43210">
                        </div>
                    </div>
                </div>

                <!-- 2. Delivery Address -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h5 class="fw-semibold text-dark mb-3 border-bottom pb-2"><i class="bi bi-geo-alt me-2"></i>2. Delivery Address</h5>

                    <?php if (!empty($savedAddresses)): ?>
                        <div class="mb-4">
                            <label class="form-label small text-muted fw-semibold">Select a Saved Address:</label>
                            <div class="d-flex flex-column gap-2 mb-3">
                                <?php foreach ($savedAddresses as $idx => $sAddr): ?>
                                    <div class="form-check p-3 border rounded-2 <?= ($sAddr['is_default'] || $idx === 0) ? 'border-dark bg-light' : '' ?>">
                                        <input class="form-check-input" type="radio" name="address_option" id="addr_<?= $sAddr['id'] ?>" value="saved" <?= ($sAddr['is_default'] || $idx === 0) ? 'checked' : '' ?> onchange="toggleNewAddressSection(false)">
                                        <label class="form-check-label w-100 cursor-pointer" for="addr_<?= $sAddr['id'] ?>">
                                            <input type="hidden" name="saved_address_id" value="<?= $sAddr['id'] ?>">
                                            <strong class="text-dark small d-block"><?= e($sAddr['address_title']) ?> &mdash; <?= e($sAddr['recipient_name']) ?></strong>
                                            <span class="text-muted small d-block"><?= e($sAddr['address_line1']) ?>, <?= e($sAddr['city']) ?>, <?= e($sAddr['state']) ?> - <?= e($sAddr['postal_code']) ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                <div class="form-check p-3 border rounded-2">
                                    <input class="form-check-input" type="radio" name="address_option" id="addr_new" value="new" onchange="toggleNewAddressSection(true)">
                                    <label class="form-check-label w-100 cursor-pointer" for="addr_new">
                                        <strong class="text-dark small">+ Deliver to a Different Address</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div id="newAddressFields" class="<?= !empty($savedAddresses) ? 'd-none' : '' ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small text-muted">Street Address *</label>
                                <input type="text" name="address_line1" class="form-control auth-input" value="<?= e($_POST['address_line1'] ?? '') ?>" placeholder="House / Flat No., Building, Street">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Apartment, suite, landmark (optional)</label>
                                <input type="text" name="address_line2" class="form-control auth-input" value="<?= e($_POST['address_line2'] ?? '') ?>" placeholder="Near Landmark / Area">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small text-muted">City *</label>
                                <input type="text" name="city" class="form-control auth-input" value="<?= e($_POST['city'] ?? '') ?>" placeholder="City">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">State *</label>
                                <input type="text" name="state" class="form-control auth-input" value="<?= e($_POST['state'] ?? '') ?>" placeholder="State">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Postal / PIN Code *</label>
                                <input type="text" name="postal_code" class="form-control auth-input" value="<?= e($_POST['postal_code'] ?? '') ?>" placeholder="PIN Code">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Country</label>
                                <input type="text" name="country" class="form-control auth-input bg-light" value="India" readonly>
                            </div>
                            <?php if (is_logged_in()): ?>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="save_to_address_book" id="saveAddrCheck" value="1" checked>
                                        <label class="form-check-label small text-muted" for="saveAddrCheck">
                                            Save this address to my account address book
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 3. Payment Method & Notes -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h5 class="fw-semibold text-dark mb-3 border-bottom pb-2"><i class="bi bi-wallet2 me-2"></i>3. Payment & Booking Terms</h5>
                    <div class="alert alert-light border py-3 px-3 mb-3 rounded-2">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-check2-circle fs-3 text-dark"></i>
                            <div>
                                <strong class="text-dark d-block">Order Booking (No Online Payment Required)</strong>
                                <small class="text-muted d-block">You can place this order booking directly without online payment. Our audio logistics specialists will reach out via phone/email to verify order details and schedule dispatch.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small text-muted">Order Instructions / Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="form-control auth-input" placeholder="Notes about your order, e.g. special delivery instructions or transducer specs."><?= e($_POST['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary & Place Order Button -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 p-4 sticky-top" style="top: 100px;">
                    <h5 class="fw-semibold text-dark mb-3 border-bottom pb-2">Order Summary (<?= count($items) ?> <?= count($items) === 1 ? 'item' : 'items' ?>)</h5>

                    <!-- Items List -->
                    <div class="d-flex flex-column gap-3 mb-4 overflow-auto" style="max-height: 280px;">
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex align-items-center justify-content-between gap-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded p-1 text-center" style="width: 52px; height: 52px; flex-shrink: 0;">
                                        <img src="<?= asset($item['main_image'] ?? 'assets/images/products/speaker-white.png') ?>" alt="<?= e($item['name']) ?>" class="img-fluid" style="max-height: 44px; object-fit: contain;">
                                    </div>
                                    <div>
                                        <strong class="text-dark small d-block text-truncate" style="max-width: 180px;"><?= e($item['name']) ?></strong>
                                        <span class="text-muted small">Qty: <?= (int)$item['quantity'] ?> &times; <?= format_price($item['price']) ?></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong class="text-dark small"><?= format_price($item['subtotal']) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Cost Calculations -->
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
                        <strong class="text-dark fs-5">Total</strong>
                        <strong class="text-dark fs-4"><?= format_price($subtotal) ?></strong>
                    </div>

                    <!-- Submit Order Button -->
                    <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-1 text-uppercase letter-spacing-1 mb-3">
                        Place Order Booking &rarr;
                    </button>

                    <div class="text-center">
                        <a href="<?= url('cart.php') ?>" class="text-muted small text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Return to Shopping Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function toggleNewAddressSection(show) {
    const el = document.getElementById('newAddressFields');
    if (el) {
        if (show) {
            el.classList.remove('d-none');
            const firstInput = el.querySelector('input[name="address_line1"]');
            if (firstInput) firstInput.focus();
        } else {
            el.classList.add('d-none');
        }
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
