<?php
/**
 * ONS SOUND — Customer Address Book Management
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/User.php';

require_login('addresses.php');

$user = current_user();
$userModel = new User();

$error = '';
$editAddress = null;

// Handle Address Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired. Please refresh and try again.');
        redirect('addresses.php');
    }

    $action = clean($_POST['action'] ?? '');

    if ($action === 'add') {
        $data = [
            'address_title' => clean($_POST['address_title'] ?? 'Home'),
            'recipient_name' => clean($_POST['recipient_name'] ?? ''),
            'phone' => clean($_POST['phone'] ?? ''),
            'address_line1' => clean($_POST['address_line1'] ?? ''),
            'address_line2' => clean($_POST['address_line2'] ?? null),
            'city' => clean($_POST['city'] ?? ''),
            'state' => clean($_POST['state'] ?? ''),
            'postal_code' => clean($_POST['postal_code'] ?? ''),
            'country' => clean($_POST['country'] ?? 'India'),
            'is_default' => !empty($_POST['is_default']) ? 1 : 0
        ];

        if (empty($data['recipient_name']) || empty($data['phone']) || empty($data['address_line1']) || empty($data['city']) || empty($data['postal_code'])) {
            $error = 'Please fill in all mandatory address fields.';
        } else {
            $userModel->addAddress($user['id'], $data);
            set_flash('success', 'New address added successfully.');
            redirect('addresses.php');
        }
    } elseif ($action === 'update') {
        $addressId = (int)($_POST['address_id'] ?? 0);
        $data = [
            'address_title' => clean($_POST['address_title'] ?? 'Home'),
            'recipient_name' => clean($_POST['recipient_name'] ?? ''),
            'phone' => clean($_POST['phone'] ?? ''),
            'address_line1' => clean($_POST['address_line1'] ?? ''),
            'address_line2' => clean($_POST['address_line2'] ?? null),
            'city' => clean($_POST['city'] ?? ''),
            'state' => clean($_POST['state'] ?? ''),
            'postal_code' => clean($_POST['postal_code'] ?? ''),
            'country' => clean($_POST['country'] ?? 'India'),
            'is_default' => !empty($_POST['is_default']) ? 1 : 0
        ];

        if (empty($data['recipient_name']) || empty($data['phone']) || empty($data['address_line1']) || empty($data['city']) || empty($data['postal_code'])) {
            $error = 'Please fill in all mandatory address fields.';
        } else {
            if ($userModel->updateAddress($addressId, $user['id'], $data)) {
                set_flash('success', 'Address updated successfully.');
                redirect('addresses.php');
            } else {
                $error = 'Could not update address. You do not have permission.';
            }
        }
    } elseif ($action === 'set_default') {
        $addressId = (int)($_POST['address_id'] ?? 0);
        if ($userModel->setDefaultAddress($addressId, $user['id'])) {
            set_flash('success', 'Default shipping address updated.');
        }
        redirect('addresses.php');
    } elseif ($action === 'delete') {
        $addressId = (int)($_POST['address_id'] ?? 0);
        if ($userModel->deleteAddress($addressId, $user['id'])) {
            set_flash('success', 'Address removed from your address book.');
        } else {
            set_flash('error', 'Could not delete address.');
        }
        redirect('addresses.php');
    }
}

// Check if editing an existing address
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    $editAddress = $userModel->getAddressById($editId, $user['id']);
}

$addresses = $userModel->getAddresses($user['id']);

$pageTitle = 'My Addresses';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Addresses Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">My Addresses</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-secondary text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('profile.php') ?>" class="text-secondary text-decoration-none">My Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Addresses</li>
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
                    <a href="<?= url('addresses.php') ?>" class="nav-link py-2 px-3 rounded-2 fw-medium bg-dark text-white">
                        <i class="bi bi-geo-alt me-2"></i>My Addresses
                    </a>
                    <a href="<?= url('orders.php') ?>" class="nav-link py-2 px-3 rounded-2 text-secondary hover-dark">
                        <i class="bi bi-box-seam me-2"></i>Order History
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

        <!-- Main Addresses Content -->
        <div class="col-lg-9 col-md-8">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <div>
                    <h1 class="fs-3 fw-bold text-dark mb-1">Shipping Addresses</h1>
                    <p class="text-muted small mb-0">Manage your shipping and billing delivery destinations.</p>
                </div>
                <button type="button" class="btn btn-dark rounded-1 px-3 py-2 fw-medium" data-bs-toggle="collapse" data-bs-target="#addressFormCollapse" aria-expanded="<?= ($editAddress || !empty($error)) ? 'true' : 'false' ?>">
                    <i class="bi bi-plus-lg me-1"></i>Add New Address
                </button>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small mb-4"><?= e($error) ?></div>
            <?php endif; ?>

            <!-- Add / Edit Address Collapsible Form -->
            <div class="collapse <?= ($editAddress || !empty($error)) ? 'show' : '' ?> mb-4" id="addressFormCollapse">
                <div class="card border-0 shadow-sm p-4 rounded-3 bg-light">
                    <h5 class="fw-semibold text-dark mb-3">
                        <?= $editAddress ? 'Edit Address (' . e($editAddress['address_title']) . ')' : 'Add New Shipping Address' ?>
                    </h5>
                    <form action="<?= url('addresses.php') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="<?= $editAddress ? 'update' : 'add' ?>">
                        <?php if ($editAddress): ?>
                            <input type="hidden" name="address_id" value="<?= (int)$editAddress['id'] ?>">
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Address Title (e.g. Home, Office)</label>
                                <input type="text" name="address_title" class="form-control auth-input bg-white" value="<?= e($editAddress['address_title'] ?? 'Home') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Recipient Full Name</label>
                                <input type="text" name="recipient_name" class="form-control auth-input bg-white" value="<?= e($editAddress['recipient_name'] ?? $user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Phone Number</label>
                                <input type="tel" name="phone" class="form-control auth-input bg-white" value="<?= e($editAddress['phone'] ?? $user['phone'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small text-muted">Address Line 1 (Street, Building, Flat)</label>
                                <input type="text" name="address_line1" class="form-control auth-input bg-white" value="<?= e($editAddress['address_line1'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Address Line 2 (Landmark, optional)</label>
                                <input type="text" name="address_line2" class="form-control auth-input bg-white" value="<?= e($editAddress['address_line2'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small text-muted">City</label>
                                <input type="text" name="city" class="form-control auth-input bg-white" value="<?= e($editAddress['city'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">State</label>
                                <input type="text" name="state" class="form-control auth-input bg-white" value="<?= e($editAddress['state'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Postal Code (PIN)</label>
                                <input type="text" name="postal_code" class="form-control auth-input bg-white" value="<?= e($editAddress['postal_code'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small text-muted">Country</label>
                                <input type="text" name="country" class="form-control auth-input bg-white" value="<?= e($editAddress['country'] ?? 'India') ?>" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-center pt-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isDefaultCheck" name="is_default" value="1" <?= (!empty($editAddress['is_default']) || count($addresses) === 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label small text-dark" for="isDefaultCheck">Set as primary default shipping address</label>
                                </div>
                            </div>

                            <div class="col-12 pt-2 d-flex gap-2">
                                <button type="submit" class="btn btn-dark rounded-1 px-4 py-2 fw-medium">
                                    <?= $editAddress ? 'Update Address' : 'Save Address' ?>
                                </button>
                                <?php if ($editAddress): ?>
                                    <a href="<?= url('addresses.php') ?>" class="btn btn-outline-secondary rounded-1 px-3 py-2">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Addresses Grid -->
            <?php if (empty($addresses)): ?>
                <div class="card border-0 shadow-sm p-5 text-center rounded-3">
                    <i class="bi bi-geo-alt display-4 text-muted mb-3"></i>
                    <h5 class="fw-semibold text-dark">No Addresses Saved Yet</h5>
                    <p class="text-muted small mb-3">Add your first shipping destination to speed up your checkout process.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($addresses as $addr): ?>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 rounded-3 h-100 position-relative <?= $addr['is_default'] ? 'border-start border-dark border-4' : '' ?>">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0">
                                        <?= e($addr['address_title']) ?>
                                    </h6>
                                    <?php if ($addr['is_default']): ?>
                                        <span class="badge bg-dark text-white rounded-pill px-2 py-1 small">Default</span>
                                    <?php endif; ?>
                                </div>

                                <div class="text-dark fw-medium small mb-1"><?= e($addr['recipient_name']) ?></div>
                                <div class="text-muted small mb-2" style="line-height: 1.6;">
                                    <?= e($addr['address_line1']) ?>
                                    <?php if (!empty($addr['address_line2'])): ?>
                                        , <?= e($addr['address_line2']) ?>
                                    <?php endif; ?><br>
                                    <?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <?= e($addr['postal_code']) ?><br>
                                    <?= e($addr['country']) ?>
                                </div>
                                <div class="text-muted small mb-3">
                                    <i class="bi bi-telephone me-1"></i><?= e($addr['phone']) ?>
                                </div>

                                <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex gap-2">
                                        <a href="<?= url('addresses.php?edit=' . (int)$addr['id']) ?>" class="btn btn-sm btn-outline-dark rounded-1">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>

                                        <form action="<?= url('addresses.php') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="address_id" value="<?= (int)$addr['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-1">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <?php if (!$addr['is_default']): ?>
                                        <form action="<?= url('addresses.php') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="set_default">
                                            <input type="hidden" name="address_id" value="<?= (int)$addr['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-link text-decoration-underline text-secondary p-0">
                                                Set as Default
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
