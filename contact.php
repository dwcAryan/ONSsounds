<?php
/**
 * ONS SOUND — Contact Us Page
 * Strictly matching ref_img/contact-us.png & media_1795533148022_e13272b4.png
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Contact Us';
$pageDescription = 'Get in touch with ONS SOUND. Visit our Ajmer showroom or send us an inquiry for technical consultation.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Contained Contact Us Section (Ref: media_1795533148022_e13272b4.png) -->
<div class="contact-page-wrapper">
    <!-- Centered Page Title -->
    <h1 class="contact-main-heading">Contact Us</h1>

    <!-- Two-Column Grid: Visit Our Store & Get in Touch -->
    <div class="row g-4 g-lg-5 justify-content-between">
        
        <!-- Left Column: Visit Our Store (~38% width) -->
        <div class="col-lg-5 col-md-5">
            <div class="pe-lg-3">
                <h2 class="contact-block-title">Visit Our Store</h2>
                
                <div class="contact-info-block">
                    <div class="contact-info-label">Address</div>
                    <div class="contact-info-value">
                        Ajmer, Rajasthan<br>
                        India
                    </div>
                </div>

                <div class="contact-info-block">
                    <div class="contact-info-label">Phone</div>
                    <div class="contact-info-value">
                        <a href="tel:+917976824255">+91 7976824255</a>
                    </div>
                </div>

                <div class="contact-info-block">
                    <div class="contact-info-label">Email</div>
                    <div class="contact-info-value">
                        <a href="mailto:<?= STORE_EMAIL ?>"><?= STORE_EMAIL ?></a>
                    </div>
                </div>

                <div class="contact-info-block">
                    <div class="contact-info-label">Open Time</div>
                    <div class="contact-info-value" style="max-width: 300px;">
                        Our store has re-opened for shopping, exchange Every day 11am to 7pm
                    </div>
                </div>

                <!-- Clean Social Glyphs (Matching screenshot: f, X, Instagram, Pinterest) -->
                <div class="contact-social-icons">
                    <a href="https://facebook.com" target="_blank" rel="noopener" class="contact-social-glyph" aria-label="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://twitter.com" target="_blank" rel="noopener" class="contact-social-glyph" aria-label="Twitter X">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" class="contact-social-glyph" aria-label="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="https://pinterest.com" target="_blank" rel="noopener" class="contact-social-glyph" aria-label="Pinterest">
                        <i class="bi bi-pinterest"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Get in Touch Form (~62% width) -->
        <div class="col-lg-7 col-md-7">
            <div class="ps-lg-2">
                <h2 class="contact-block-title">Get in Touch</h2>

                <form action="<?= url('contact_process.php') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="source" value="contact_page">

                    <div class="contact-field-group">
                        <label class="contact-field-label">Your name</label>
                        <input type="text" name="name" class="contact-input-clean" required autocomplete="name">
                    </div>

                    <div class="contact-field-group">
                        <label class="contact-field-label">Your email</label>
                        <input type="email" name="email" class="contact-input-clean" required autocomplete="email">
                    </div>

                    <div class="contact-field-group">
                        <label class="contact-field-label">Contact Number</label>
                        <input type="tel" name="phone" class="contact-input-clean" placeholder="Enter your phone number" autocomplete="tel">
                    </div>

                    <div class="contact-field-group" style="margin-bottom: 24px;">
                        <label class="contact-field-label">Your Message</label>
                        <textarea name="message" class="contact-textarea-clean" rows="7" required></textarea>
                    </div>

                    <div>
                        <button type="submit" class="contact-submit-btn-clean">Submit</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
