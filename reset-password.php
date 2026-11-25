<?php
/**
 * ONS SOUND — Reset Password Form Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/User.php';

$userModel = new User();
$token = clean($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$tokenValid = false;
$resetData = null;

if (empty($token)) {
    $error = 'No reset token provided. Please request a new password reset link.';
} else {
    $resetData = $userModel->verifyResetToken($token);
    if (!$resetData) {
        $error = 'This password reset link is invalid or has expired. Please request a new one.';
    } else {
        $tokenValid = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    if (!verify_csrf()) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($password)) {
            $error = 'Please enter your new password.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match. Please re-enter.';
        } else {
            try {
                $userModel->resetPasswordWithToken($token, $password);
                set_flash('success', 'Your password has been successfully reset! Please sign in with your new password.');
                redirect('login.php');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Set New Password';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Set New Password Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">Set New Password</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-3">
                <p class="text-muted small mb-4">
                    <?php if ($tokenValid): ?>
                        Enter a secure new password for <strong><?= e($resetData['email']) ?></strong>.
                    <?php else: ?>
                        Password reset link verification.
                    <?php endif; ?>
                </p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small mb-4"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($tokenValid): ?>
            <form action="<?= url('reset-password.php') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <div class="mb-3 position-relative">
                    <label class="form-label small text-muted">New Password (min 6 characters)</label>
                    <div class="position-relative">
                        <input type="password" name="password" class="form-control auth-input pe-5 auth-password-input" minlength="6" required autocomplete="new-password">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4 position-relative">
                    <label class="form-label small text-muted">Confirm New Password</label>
                    <div class="position-relative">
                        <input type="password" name="confirm_password" class="form-control auth-input pe-5 auth-password-input" minlength="6" required autocomplete="new-password">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-2 fw-medium rounded-1 mb-3">Update Password</button>
            </form>
        <?php else: ?>
            <div class="text-center pt-2">
                <a href="<?= url('forgot-password.php') ?>" class="btn btn-outline-dark rounded-1 px-4 py-2 small">Request New Reset Link</a>
            </div>
        <?php endif; ?>

        <div class="text-center pt-3 border-top mt-2">
            <a href="<?= url('login.php') ?>" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back to Sign In
            </a>
        </div>
    </div>
</div>
</div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
