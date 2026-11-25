<?php
/**
 * ONS SOUND — Customer Registration Page with 6-Digit OTP Verification
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/User.php';

if (is_logged_in()) {
    redirect('profile.php');
}

$userModel = new User();
$error = '';
$success = '';
$step = 'form'; // 'form' or 'otp'

// Check if user already has an active pending registration in session
if (!empty($_SESSION['pending_registration']) && time() <= $_SESSION['pending_registration']['expires_at']) {
    $step = 'otp';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $postAction = clean($_POST['action'] ?? 'send_otp');

        // Action: Back to edit details
        if ($postAction === 'edit_details') {
            $step = 'form';
        }
        // Action: Resend OTP
        elseif ($postAction === 'resend_otp') {
            if (!empty($_SESSION['pending_registration'])) {
                $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['pending_registration']['otp'] = $otp;
                $_SESSION['pending_registration']['expires_at'] = time() + 600;
                $_SESSION['pending_registration']['attempts'] = 0;

                $email = $_SESSION['pending_registration']['email'];
                $name = $_SESSION['pending_registration']['name'];

                $subject = "Your New ONS Sound Verification Code: " . $otp;
                $mailMsg = "
                <div style='font-family: Arial, sans-serif; max-width: 520px; margin: 0 auto; padding: 24px; border: 1px solid #e5e5e5; border-radius: 8px;'>
                    <h2 style='color: #111; margin-bottom: 8px;'>ONS SOUND</h2>
                    <p style='color: #555; font-size: 15px;'>Hello " . htmlspecialchars($name) . ",</p>
                    <p style='color: #555; font-size: 15px;'>Your requested new 6-digit verification code is:</p>
                    <div style='background: #f4f4f4; padding: 18px; text-align: center; border-radius: 6px; margin: 24px 0;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #111; font-family: monospace;'>" . $otp . "</span>
                    </div>
                    <p style='color: #777; font-size: 13px;'>This code is valid for 10 minutes. If you did not request this, please disregard this email.</p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='color: #999; font-size: 12px; margin: 0;'>ONS SOUND &mdash; Hear every word with precision sound.</p>
                </div>";
                send_email($email, $subject, $mailMsg, true);

                $success = "A new verification code has been sent to " . e($email) . ". Please check your inbox.";
                $step = 'otp';
            } else {
                $error = "No active registration in progress. Please fill out the form.";
                $step = 'form';
            }
        }
        // Action: Send OTP (Step 1)
        elseif ($postAction === 'send_otp') {
            $name = clean($_POST['name'] ?? '');
            $email = clean($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone = clean($_POST['phone'] ?? null);

            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Please fill in all required fields.';
                $step = 'form';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please provide a valid email address.';
                $step = 'form';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
                $step = 'form';
            } else {
                $existing = $userModel->findBy('email', trim(strtolower($email)));
                if ($existing) {
                    $error = 'An account with this email address already exists. Please log in instead.';
                    $step = 'form';
                } else {
                    $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

                    $_SESSION['pending_registration'] = [
                        'name' => $name,
                        'email' => trim(strtolower($email)),
                        'password' => $password,
                        'phone' => $phone,
                        'otp' => $otp,
                        'expires_at' => time() + 600,
                        'attempts' => 0
                    ];

                    $subject = "Your ONS Sound Verification Code: " . $otp;
                    $mailMsg = "
                    <div style='font-family: Arial, sans-serif; max-width: 520px; margin: 0 auto; padding: 24px; border: 1px solid #e5e5e5; border-radius: 8px;'>
                        <h2 style='color: #111; margin-bottom: 8px;'>ONS SOUND</h2>
                        <p style='color: #555; font-size: 15px;'>Hello " . htmlspecialchars($name) . ",</p>
                        <p style='color: #555; font-size: 15px;'>Thank you for registering. Please enter the following 6-digit verification code to complete your registration:</p>
                        <div style='background: #f4f4f4; padding: 18px; text-align: center; border-radius: 6px; margin: 24px 0;'>
                            <span style='font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #111; font-family: monospace;'>" . $otp . "</span>
                        </div>
                        <p style='color: #777; font-size: 13px;'>This code is valid for 10 minutes. If you did not request this, please disregard this email.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='color: #999; font-size: 12px; margin: 0;'>ONS SOUND &mdash; Hear every word with precision sound.</p>
                    </div>";
                    send_email($email, $subject, $mailMsg, true);

                    $success = "Verification code sent to " . e($email) . ". Please check your inbox and enter the 6-digit code below.";
                    $step = 'otp';
                }
            }
        }
        // Action: Verify OTP (Step 2)
        elseif ($postAction === 'verify_otp') {
            $enteredOtp = clean($_POST['otp'] ?? '');

            if (empty($_SESSION['pending_registration'])) {
                $error = 'Session expired. Please fill out the registration form again.';
                $step = 'form';
            } else {
                $pending = $_SESSION['pending_registration'];

                if (time() > $pending['expires_at']) {
                    unset($_SESSION['pending_registration']);
                    $error = 'Verification code has expired. Please register again.';
                    $step = 'form';
                } elseif ($pending['attempts'] >= 5) {
                    unset($_SESSION['pending_registration']);
                    $error = 'Too many failed attempts. Please register again.';
                    $step = 'form';
                } elseif ($enteredOtp !== $pending['otp']) {
                    $_SESSION['pending_registration']['attempts']++;
                    $remaining = 5 - $_SESSION['pending_registration']['attempts'];
                    $error = "Invalid verification code. Please try again ({$remaining} attempts remaining).";
                    $step = 'otp';
                } else {
                    // OTP is valid! Register customer
                    try {
                        $userId = $userModel->register($pending['name'], $pending['email'], $pending['password'], $pending['phone']);

                        $_SESSION['user_id'] = $userId;
                        $_SESSION['user_role'] = 'customer';
                        $_SESSION['user_name'] = $pending['name'];
                        $_SESSION['user_email'] = $pending['email'];

                        // Transfer guest cart & wishlist
                        if (!empty($_SESSION['guest_cart_id'])) {
                            try {
                                $db = get_db();
                                $db->prepare("UPDATE carts SET user_id = ? WHERE session_id = ? AND user_id IS NULL")->execute([$userId, $_SESSION['guest_cart_id']]);
                                $db->prepare("UPDATE wishlists SET user_id = ? WHERE session_id = ? AND user_id IS NULL")->execute([$userId, $_SESSION['guest_cart_id']]);
                            } catch (Exception $e) {}
                        }

                        unset($_SESSION['pending_registration']);

                        set_flash('success', 'Email verified and account created! Welcome to ONS Sound.');
                        redirect('profile.php');
                    } catch (InvalidArgumentException $e) {
                        $error = $e->getMessage();
                        $step = 'form';
                    } catch (Exception $e) {
                        $error = 'Could not create account. Please try again.';
                        $step = 'form';
                    }
                }
            }
        }
    }
}

$pageTitle = ($step === 'otp') ? 'Verify Email' : 'Create Account';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Registration Page Title Banner -->
<section class="py-5 bg-white text-center">
    <div class="container-fluid px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-dark mb-0"><?= ($step === 'otp') ? 'Verify Your Email' : 'Create Account' ?></h1>
    </div>
</section>

<div class="container-fluid px-3 px-lg-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-3">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2 small mb-3"><?= e($success) ?></div>
                <?php endif; ?>

                <?php if ($step === 'otp'): ?>
                    <!-- Step 2: 6-Digit OTP Verification Screen -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 60px; height: 60px;">
                                <i class="bi bi-envelope-check text-dark fs-2"></i>
                            </span>
                        </div>
                        <h4 class="fw-semibold text-dark mb-1">Enter Verification Code</h4>
                        <p class="text-muted small mb-1">We sent a 6-digit verification code to your email</p>
                        <strong class="text-dark d-block mb-3"><?= e($_SESSION['pending_registration']['email'] ?? '') ?></strong>
                        <p class="text-muted small mb-0">Please check your inbox (and spam folder) and enter the 6-digit code below:</p>
                    </div>

                    <form action="<?= url('register.php') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="verify_otp">

                        <div class="mb-4">
                            <label class="form-label small text-muted text-center d-block">6-Digit Code</label>
                            <input type="text" name="otp" class="form-control auth-input text-center fs-3 font-monospace py-2" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="one-time-code" style="letter-spacing: 4px;" autofocus>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-3 fw-medium rounded-1 mb-3">Verify & Create Account</button>
                    </form>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                        <form action="<?= url('register.php') ?>" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="resend_otp">
                            <button type="submit" class="btn btn-link btn-sm text-dark text-decoration-underline p-0">Resend code</button>
                        </form>
                        <form action="<?= url('register.php') ?>" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="edit_details">
                            <button type="submit" class="btn btn-link btn-sm text-muted p-0">&larr; Change details</button>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Step 1: Initial Registration Form -->
                    <p class="text-muted small mb-4">Register with ONS Sound to manage precision audio orders and track shipments.</p>

                    <form action="<?= url('register.php') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="send_otp">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control auth-input" value="<?= e($_POST['name'] ?? $_SESSION['pending_registration']['name'] ?? '') ?>" required autocomplete="name" placeholder="John Doe">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Email address</label>
                            <input type="email" name="email" class="form-control auth-input" value="<?= e($_POST['email'] ?? $_SESSION['pending_registration']['email'] ?? '') ?>" required autocomplete="email" placeholder="john@example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Phone Number (optional)</label>
                            <input type="tel" name="phone" class="form-control auth-input" value="<?= e($_POST['phone'] ?? $_SESSION['pending_registration']['phone'] ?? '') ?>" placeholder="+91..." autocomplete="tel">
                        </div>

                        <div class="mb-4 position-relative">
                            <label class="form-label small text-muted">Password (min 6 characters)</label>
                            <div class="position-relative">
                                <input type="password" name="password" class="form-control auth-input pe-5 auth-password-input" minlength="6" required autocomplete="new-password" placeholder="Create password">
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-3 fw-medium rounded-1 mb-3">Continue to Verification</button>

                        <div class="text-center pt-2 border-top">
                            <span class="text-muted small">Already have an account?</span>
                            <a href="<?= url('login.php') ?>" class="text-dark fw-medium text-decoration-underline small ms-1">Sign In &#8599;</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
