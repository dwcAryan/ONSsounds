<?php
/**
 * ONS SOUND — Admin Logout Handler
 */

require_once __DIR__ . '/auth.php';

if (is_admin_logged_in()) {
    log_admin_activity('Admin Logout', 'Logged out session.');
}

// Clear admin session variables
unset($_SESSION['admin_id'], $_SESSION['admin_role'], $_SESSION['admin_name'], $_SESSION['admin_email']);

header("Location: " . admin_url('login.php?loggedout=1'));
exit;
