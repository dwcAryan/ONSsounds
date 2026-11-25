<?php
/**
 * ONS SOUND - Authentication AJAX & Form Handler
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/User.php';

$action = clean($_POST['action'] ?? $_GET['action'] ?? '');
$userModel = new User();

// Handle Logout
if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    session_start();
    set_flash('success', 'You have been logged out.');
    redirect('index.php');
}

// Ensure POST for login/register
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

// Handle Login
if ($action === 'login') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        json_response(['success' => false, 'message' => 'Please enter both email and password.'], 400);
    }

    $user = $userModel->authenticate($email, $password);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];

        // Transfer any guest cart or wishlist to this user
        if (!empty($_SESSION['guest_cart_id'])) {
            try {
                $db = get_db();
                $stmt = $db->prepare("UPDATE carts SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
                $stmt->execute([$user['id'], $_SESSION['guest_cart_id']]);
                $stmt2 = $db->prepare("UPDATE wishlists SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
                $stmt2->execute([$user['id'], $_SESSION['guest_cart_id']]);
            } catch (Exception $e) {
                // Ignore transfer errors
            }
        }

        json_response([
            'success' => true,
            'message' => 'Signed in successfully as ' . $user['full_name'] . '!',
            'redirect' => url('index.php'),
            'user' => [
                'id' => $user['id'],
                'name' => $user['full_name'],
                'role' => $user['role']
            ]
        ]);
    } else {
        json_response(['success' => false, 'message' => 'Invalid email or password. Please try again.'], 401);
    }
}

// Handle Send Registration OTP (or initial register step)
if ($action === 'send_register_otp' || $action === 'register') {
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = clean($_POST['phone'] ?? null);

    if (empty($name) || empty($email) || empty($password)) {
        json_response(['success' => false, 'message' => 'Please fill in all required fields.'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['success' => false, 'message' => 'Please provide a valid email address.'], 400);
    }

    if (strlen($password) < 6) {
        json_response(['success' => false, 'message' => 'Password must be at least 6 characters long.'], 400);
    }

    // Check if email already exists
    $existing = $userModel->findBy('email', trim(strtolower($email)));
    if ($existing) {
        json_response(['success' => false, 'message' => 'An account with this email address already exists. Please log in instead.'], 409);
    }

    // Generate 6-digit OTP
    $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

    $_SESSION['pending_registration'] = [
        'name' => $name,
        'email' => trim(strtolower($email)),
        'password' => $password,
        'phone' => $phone,
        'otp' => $otp,
        'expires_at' => time() + 600, // Valid for 10 minutes
        'attempts' => 0
    ];

    // Send verification email via configured SMTP
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

    json_response([
        'success' => true,
        'step' => 'verify_otp',
        'email' => $email,
        'message' => 'Verification code sent to ' . $email . '. Please check your inbox and enter the 6-digit code.'
    ]);
}

// Handle Verify Registration OTP
if ($action === 'verify_register_otp') {
    $otp = clean($_POST['otp'] ?? '');

    if (empty($_SESSION['pending_registration'])) {
        json_response(['success' => false, 'message' => 'No registration in progress. Please fill out the registration form.'], 400);
    }

    $pending = $_SESSION['pending_registration'];

    if (time() > $pending['expires_at']) {
        unset($_SESSION['pending_registration']);
        json_response(['success' => false, 'message' => 'Verification code has expired. Please register again.'], 400);
    }

    if ($pending['attempts'] >= 5) {
        unset($_SESSION['pending_registration']);
        json_response(['success' => false, 'message' => 'Too many failed attempts. Please register again.'], 429);
    }

    if ($otp !== $pending['otp']) {
        $_SESSION['pending_registration']['attempts']++;
        $remaining = 5 - $_SESSION['pending_registration']['attempts'];
        json_response(['success' => false, 'message' => 'Invalid verification code. Please try again (' . $remaining . ' attempts remaining).'], 400);
    }

    // OTP verified! Create user in database
    try {
        $userId = $userModel->register($pending['name'], $pending['email'], $pending['password'], $pending['phone']);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = 'customer';
        $_SESSION['user_name'] = $pending['name'];

        // Transfer guest cart & wishlist if applicable
        if (!empty($_SESSION['guest_cart_id'])) {
            try {
                $db = get_db();
                $db->prepare("UPDATE carts SET user_id = ? WHERE session_id = ? AND user_id IS NULL")->execute([$userId, $_SESSION['guest_cart_id']]);
                $db->prepare("UPDATE wishlists SET user_id = ? WHERE session_id = ? AND user_id IS NULL")->execute([$userId, $_SESSION['guest_cart_id']]);
            } catch (Exception $e) {}
        }

        unset($_SESSION['pending_registration']);

        json_response([
            'success' => true,
            'message' => 'Email verified! Account created successfully. Welcome to ONS Sound!',
            'redirect' => url('profile.php'),
            'user' => [
                'id' => $userId,
                'name' => $pending['name'],
                'role' => 'customer'
            ]
        ]);
    } catch (InvalidArgumentException $e) {
        json_response(['success' => false, 'message' => $e->getMessage()], 409);
    } catch (Exception $e) {
        error_log("Registration verification error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Could not complete registration. Please try again.'], 500);
    }
}

// Handle Resend Registration OTP
if ($action === 'resend_register_otp') {
    if (empty($_SESSION['pending_registration'])) {
        json_response(['success' => false, 'message' => 'No registration in progress. Please start over.'], 400);
    }

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

    json_response([
        'success' => true,
        'message' => 'A new 6-digit verification code has been sent to ' . $email . '. Please check your inbox.'
    ]);
}

json_response(['success' => false, 'message' => 'Unknown action.'], 400);
