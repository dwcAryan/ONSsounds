<?php
/**
 * ONS SOUND — Forgot Password Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/User.php';

$userModel = new User();
$message = '';
$error = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $email = clean($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $token = $userModel->createPasswordReset($email);
            if ($token) {
                $resetLink = url('reset-password.php?token=' . urlencode($token));
                $message = 'A password reset token has been generated successfully. You can reset your password using the link below:';
            } else {
                // If account doesn't exist, provide a helpful note
                $error = 'No registered account found with that email address.';
            }
        }
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Forgot Password Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0">Reset Password</h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-3">
                <p class="text-muted small mb-4">Enter your registered email address to receive password reset instructions.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small mb-3"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success py-3 small mb-4">
                <p class="mb-2 fw-medium"><i class="bi bi-check-circle me-1"></i><?= e($message) ?></p>
                <div class="p-3 bg-white border rounded-2 mt-2">
                    <span class="text-muted small d-block mb-1">Click below to proceed to the secure reset form:</span>
                    <a href="<?= e($resetLink) ?>" class="btn btn-dark btn-sm rounded-1 px-3 py-2 fw-medium text-decoration-none d-inline-block">
                        <i class="bi bi-key me-1"></i>Reset Password Now &rarr;
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($resetLink)): ?>
            <form action="<?= url('forgot-password.php') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-4">
                    <label class="form-label small text-muted">Email address</label>
                    <input type="email" name="email" class="form-control auth-input" value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@example.com" required autocomplete="email">
                </div>

                <button type="submit" class="btn btn-dark w-100 py-2 fw-medium rounded-1 mb-3">Send Reset Instructions</button>
            </form>
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
