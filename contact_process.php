<?php
/**
 * ONS SOUND - Contact Inquiry Form Processor
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('contact.php');
}

if (!verify_csrf()) {
    set_flash('error', 'Security verification token invalid. Please try again.');
    redirect('contact.php');
}

$name = clean($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = clean($_POST['phone'] ?? '');
$message = clean($_POST['message'] ?? '');
$source = clean($_POST['source'] ?? 'contact_page');

if (empty($name) || !$email || empty($message)) {
    set_flash('error', 'Please fill in all required fields with a valid email.');
    redirect($source === 'homepage_box' ? 'index.php' : 'contact.php');
}

try {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO `contact_enquiries` (name, email, phone, message, source, status, created_at) VALUES (?, ?, ?, ?, ?, 'new', NOW())");
    $stmt->execute([$name, $email, $phone, $message, $source]);

    // Send notification email to store owner
    $ownerEmail = defined('OWNER_EMAIL') ? OWNER_EMAIL : STORE_EMAIL;
    $ownerSubject = "New Inquiry from " . $name . " - ONS SOUND";
    $ownerBody = "
    <div style='font-family: Arial, sans-serif; max-width: 540px; margin: 0 auto; padding: 24px; border: 1px solid #e5e5e5; border-radius: 8px;'>
        <h3 style='color: #111; margin-bottom: 12px;'>New Contact Inquiry Received</h3>
        <p style='margin: 6px 0;'><strong>Customer Name:</strong> " . htmlspecialchars($name) . "</p>
        <p style='margin: 6px 0;'><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p style='margin: 6px 0;'><strong>Phone:</strong> " . htmlspecialchars($phone ?: 'Not provided') . "</p>
        <p style='margin: 6px 0;'><strong>Source:</strong> " . htmlspecialchars($source) . "</p>
        <hr style='border: none; border-top: 1px solid #eee; margin: 16px 0;'>
        <p style='margin: 6px 0;'><strong>Message:</strong></p>
        <p style='background: #f8f9fa; padding: 14px; border-radius: 6px; white-space: pre-wrap; font-size: 14px; color: #333; margin: 8px 0;'>" . htmlspecialchars($message) . "</p>
        <hr style='border: none; border-top: 1px solid #eee; margin: 16px 0;'>
        <p style='color: #888; font-size: 12px; margin: 0;'>Sent via ONS SOUND Store Contact System</p>
    </div>";
    send_email($ownerEmail, $ownerSubject, $ownerBody, true);

    set_flash('success', 'Thank you! Your message has been sent successfully. Our team will contact you shortly.');
} catch (PDOException $e) {
    error_log("Contact submission error: " . $e->getMessage());
    set_flash('error', 'Could not send your inquiry. Please try again later.');
}

redirect($source === 'homepage_box' ? 'index.php' : 'contact.php');
