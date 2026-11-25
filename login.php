<?php
/**
 * ONS SOUND — Customer Login Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/User.php';

if (is_logged_in()) {
    redirect('profile.php');
}

$userModel = new User();
$error = '';
$redirectUrl = clean($_GET['redirect'] ?? $_POST['redirect'] ?? 'profile.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $email = clean($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            $user = $userModel->authenticate($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];

                // Transfer guest cart & wishlist
                if (!empty($_SESSION['guest_cart_id'])) {
                    try {
                        $db = get_db();
                        $db->prepare("UPDATE carts SET user_id = ? WHERE session_id = ? AND user_id IS NULL")->execute([$user['id'], $_SESSION['guest_cart_id']]);
                        $db->prepare("UPDATE wishlists SET user_id = ? WHERE session_id = ? AND user_id IS NULL")->execute([$user['id'], $_SESSION['guest_cart_id']]);
                    } catch (Exception $e) {}
                }

                set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
                redirect($redirectUrl);
            } else {
                $error = 'Invalid email address or password. Please try again.';
            }
        }
    }
}

$pageTitle = 'Sign In';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Sign In Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">Sign In</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-3">
                <p class="text-muted small mb-4">Access your ONS Sound account, orders, and addresses.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small mb-3"><?= e($error) ?></div>
        <?php endif; ?>

        <form action="<?= url('login.php') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= e($redirectUrl) ?>">

            <div class="mb-3">
                <label class="form-label small text-muted">Email address</label>
                <input type="email" name="email" class="form-control auth-input" value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email">
            </div>

            <div class="mb-3 position-relative">
                <label class="form-label small text-muted">Password</label>
                <div class="position-relative">
                    <input type="password" name="password" class="form-control auth-input pe-5 auth-password-input" required autocomplete="current-password">
                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4 small">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" id="rememberMePage" name="remember" value="1">
                    <label class="form-check-label text-muted" for="rememberMePage">Remember me</label>
                </div>
                <a href="<?= url('forgot-password.php') ?>" class="text-decoration-underline text-muted">Forgot your password?</a>
            </div>

            <button type="submit" class="btn btn-dark w-100 py-2 fw-medium rounded-1 mb-3">Sign In</button>

            <div class="text-center pt-2 border-top">
                <span class="text-muted small">New customer?</span>
                <a href="<?= url('register.php') ?>" class="text-dark fw-medium text-decoration-underline small ms-1">Create an account &#8599;</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
