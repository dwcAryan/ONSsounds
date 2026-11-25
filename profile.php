<?php
/**
 * ONS SOUND — Customer Profile Management Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/Wishlist.php';

require_login('profile.php');

$user = current_user();
$userModel = new User();
$orderModel = new Order();
$wishlistModel = new Wishlist();

$userData = $userModel->getById($user['id']);
$recentOrders = $orderModel->getUserOrders($user['id']);
$defaultAddress = $userModel->getDefaultAddress($user['id']);
$wishlistCount = $wishlistModel->getCount($user['id']);

$profileSuccess = '';
$profileError = '';
$passwordSuccess = '';
$passwordError = '';

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired. Please refresh and try again.');
        redirect('profile.php');
    }

    $formType = clean($_POST['form_type'] ?? '');

    if ($formType === 'update_profile') {
        $fullName = clean($_POST['full_name'] ?? '');
        $phone = clean($_POST['phone'] ?? null);

        if (empty($fullName)) {
            $profileError = 'Full name cannot be empty.';
        } else {
            if ($userModel->updateProfile($user['id'], $fullName, $phone)) {
                $_SESSION['user_name'] = $fullName;
                set_flash('success', 'Profile details updated successfully.');
                redirect('profile.php');
            } else {
                $profileError = 'Failed to update profile. Please try again.';
            }
        }
    } elseif ($formType === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $passwordError = 'Please fill in all password fields.';
        } elseif ($newPassword !== $confirmPassword) {
            $passwordError = 'New passwords do not match.';
        } else {
            try {
                $userModel->updatePassword($user['id'], $currentPassword, $newPassword);
                set_flash('success', 'Your password has been successfully updated.');
                redirect('profile.php');
            } catch (InvalidArgumentException $e) {
                $passwordError = $e->getMessage();
            } catch (Exception $e) {
                $passwordError = 'Could not update password. Please check your credentials.';
            }
        }
    }
}

$pageTitle = 'My Account';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Account Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">My Account</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-secondary text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Account</li>
        </ol>
    </nav>

    <div class="row g-4 g-lg-5">
        <!-- Account Sidebar Navigation -->
        <div class="col-lg-3 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3 p-2 border-bottom pb-3 mb-3">
                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px; flex-shrink: 0;">
                        <?= strtoupper(substr($userData['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="overflow-hidden">
                        <strong class="d-block text-truncate text-dark"><?= e($userData['full_name']) ?></strong>
                        <small class="text-muted d-block text-truncate"><?= e($userData['email']) ?></small>
                    </div>
                </div>

                <nav class="nav flex-column gap-1">
                    <a href="<?= url('profile.php') ?>" class="nav-link py-2 px-3 rounded-2 fw-medium bg-dark text-white">
                        <i class="bi bi-person-gear me-2"></i>Account Details
                    </a>
                    <a href="<?= url('addresses.php') ?>" class="nav-link py-2 px-3 rounded-2 text-secondary hover-dark">
                        <i class="bi bi-geo-alt me-2"></i>My Addresses
                    </a>
                    <a href="<?= url('orders.php') ?>" class="nav-link py-2 px-3 rounded-2 text-secondary hover-dark">
                        <i class="bi bi-box-seam me-2"></i>Order History
                        <?php if (count($recentOrders) > 0): ?>
                            <span class="badge bg-secondary rounded-pill float-end"><?= count($recentOrders) ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= url('wishlist.php') ?>" class="nav-link py-2 px-3 rounded-2 text-secondary hover-dark">
                        <i class="bi bi-heart me-2"></i>My Wishlist
                        <?php if ($wishlistCount > 0): ?>
                            <span class="badge bg-secondary rounded-pill float-end"><?= $wishlistCount ?></span>
                        <?php endif; ?>
                    </a>
                    <hr class="my-2">
                    <a href="<?= url('logout.php') ?>" class="nav-link py-2 px-3 rounded-2 text-danger hover-dark">
                        <i class="bi bi-box-arrow-right me-2"></i>Log Out
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Account Content -->
        <div class="col-lg-9 col-md-8">
            <h1 class="fs-3 fw-bold text-dark mb-4">Account Overview</h1>

            <!-- Quick Stats Overview -->
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm p-3 rounded-3 h-100">
                        <span class="text-muted small">Total Orders</span>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <h3 class="fw-bold mb-0 text-dark"><?= count($recentOrders) ?></h3>
                            <a href="<?= url('orders.php') ?>" class="btn btn-sm btn-outline-dark rounded-1">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm p-3 rounded-3 h-100">
                        <span class="text-muted small">Wishlist Saved</span>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <h3 class="fw-bold mb-0 text-dark"><?= $wishlistCount ?></h3>
                            <a href="<?= url('wishlist.php') ?>" class="btn btn-sm btn-outline-dark rounded-1">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm p-3 rounded-3 h-100">
                        <span class="text-muted small">Default Address</span>
                        <div class="mt-2">
                            <strong class="d-block text-truncate small text-dark"><?= e($defaultAddress['city'] ?? 'No address saved') ?></strong>
                            <a href="<?= url('addresses.php') ?>" class="small text-decoration-underline text-secondary">Manage</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Details Form -->
            <div class="card border-0 shadow-sm p-4 p-lg-5 rounded-3 mb-4">
                <h4 class="fs-4 fw-semibold text-dark mb-1">Personal Details</h4>
                <p class="text-muted small mb-4">Update your profile name and contact number.</p>

                <?php if (!empty($profileError)): ?>
                    <div class="alert alert-danger py-2 small mb-3"><?= e($profileError) ?></div>
                <?php endif; ?>

                <form action="<?= url('profile.php') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_type" value="update_profile">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Full Name</label>
                            <input type="text" name="full_name" class="form-control auth-input" value="<?= e($userData['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Email Address (Primary)</label>
                            <input type="email" class="form-control auth-input bg-light" value="<?= e($userData['email']) ?>" readonly title="Email cannot be changed directly for security reasons">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Contact Phone</label>
                            <input type="tel" name="phone" class="form-control auth-input" value="<?= e($userData['phone'] ?? '') ?>" placeholder="+91...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Member Since</label>
                            <input type="text" class="form-control auth-input bg-light" value="<?= date('F j, Y', strtotime($userData['created_at'])) ?>" readonly>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-dark rounded-1 px-4 py-2 fw-medium">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Password Change Form -->
            <div class="card border-0 shadow-sm p-4 p-lg-5 rounded-3 mb-4">
                <h4 class="fs-4 fw-semibold text-dark mb-1">Security & Password</h4>
                <p class="text-muted small mb-4">Ensure your account is using a secure password (minimum 6 characters).</p>

                <?php if (!empty($passwordError)): ?>
                    <div class="alert alert-danger py-2 small mb-3"><?= e($passwordError) ?></div>
                <?php endif; ?>

                <form action="<?= url('profile.php') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_type" value="change_password">

                    <div class="row g-3">
                        <div class="col-md-12 position-relative">
                            <label class="form-label small text-muted">Current Password</label>
                            <div class="position-relative">
                                <input type="password" name="current_password" class="form-control auth-input pe-5 auth-password-input" required autocomplete="current-password">
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label class="form-label small text-muted">New Password</label>
                            <div class="position-relative">
                                <input type="password" name="new_password" class="form-control auth-input pe-5 auth-password-input" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label class="form-label small text-muted">Confirm New Password</label>
                            <div class="position-relative">
                                <input type="password" name="confirm_password" class="form-control auth-input pe-5 auth-password-input" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-outline-dark rounded-1 px-4 py-2 fw-medium">Update Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
