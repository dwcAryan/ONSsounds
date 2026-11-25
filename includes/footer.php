<?php
/**
 * ONS SOUND - Global Footer Include
 * Exact layout & typography from onssound.com
 */
require_once __DIR__ . '/helpers.php';
?>

<!-- Global Site Footer -->
<footer class="site-footer">
    <div class="container-fluid px-3 px-lg-5">
        <div class="row g-4 justify-content-between">
            
            <!-- Col 1: Store Brand & Info -->
            <div class="col-lg-3 col-md-6">
                <div class="footer-brand"><?= SITE_NAME ?></div>
                <div class="footer-info-text">
                    <strong>Address:</strong> <?= STORE_ADDRESS ?>
                </div>
                <div class="footer-info-text">
                    <strong>Email:</strong> <a href="mailto:<?= STORE_EMAIL ?>"><?= STORE_EMAIL ?></a>
                </div>
                <div class="footer-info-text">
                    <strong>Phone:</strong> <a href="tel:<?= str_replace(' ', '', STORE_PHONE) ?>"><?= STORE_PHONE ?></a>
                </div>
            </div>

            <!-- Col 2: Help Links & Social -->
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-heading">Help</h5>
                <ul class="footer-links">
                    <li><a href="<?= url('privacy-policy.php') ?>">Privacy policy</a></li>
                    <li><a href="<?= url('faq.php') ?>">FAQ'S</a></li>
                    <li><a href="<?= url('products.php') ?>">Menu item</a></li>
                    <li>
                        <a href="https://maps.google.com/?q=Ajmer+Rajasthan+India" target="_blank" rel="noopener noreferrer">
                            Get direction <i class="bi bi-arrow-up-right ms-1"></i>
                        </a>
                    </li>
                </ul>
                <div class="footer-social-icons">
                    <a href="https://facebook.com" target="_blank" rel="noopener" class="social-circle-btn" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://twitter.com" target="_blank" rel="noopener" class="social-circle-btn" aria-label="X Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" class="social-circle-btn" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://tiktok.com" target="_blank" rel="noopener" class="social-circle-btn" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                    <a href="https://pinterest.com" target="_blank" rel="noopener" class="social-circle-btn" aria-label="Pinterest"><i class="bi bi-pinterest"></i></a>
                </div>
            </div>

            <!-- Col 3: Useful Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-heading">Useful Links</h5>
                <ul class="footer-links">
                    <li><a href="<?= url('index.php') ?>">Home</a></li>
                    <li><a href="<?= url('products.php') ?>">Products</a></li>
                    <li><a href="<?= url('blog.php') ?>">Blog</a></li>
                    <li><a href="<?= url('about.php') ?>">About Us</a></li>
                    <li><a href="<?= url('contact.php') ?>">Contact Us</a></li>
                </ul>
            </div>

            <!-- Col 4: Newsletter Signup -->
            <div class="col-lg-4 col-md-6">
                <h5 class="footer-heading">Sign Up for Email</h5>
                <p class="footer-info-text mb-3">
                    Sign up to get first dibs on new arrivals, sales, exclusive content, events and more!
                </p>
                <form id="newsletterFooterForm" action="<?= url('newsletter_subscribe.php') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <input type="email" name="email" class="newsletter-input" placeholder="Your email" required>
                    </div>
                    <button type="submit" class="newsletter-btn">Submit</button>
                    <div id="newsletterMsg" class="mt-2 text-warning small" style="display:none;"></div>
                </form>
            </div>

        </div>

        <!-- Footer Bottom Bar -->
        <div class="footer-bottom">
            <div>
                &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All Rights Reserved
            </div>

            <div class="payment-badges">
                <img src="<?= asset('assets/images/home/payment-methods.png') ?>" alt="Payment methods" style="max-height: 24px; filter: brightness(1.2);">
            </div>
        </div>
    </div>
</footer>

<!-- Jump To Top Floating Button with Border Scroll Progress -->
<button type="button" class="scroll-top-btn" id="scrollTopBtn" title="Back to top" aria-label="Back to top">
    <svg class="scroll-progress-svg" viewBox="0 0 46 46" width="46" height="46">
        <path class="scroll-progress-bg" d="M 23,2 H 36 A 8 8 0 0 1 44,10 V 36 A 8 8 0 0 1 36,44 H 10 A 8 8 0 0 1 2,36 V 10 A 8 8 0 0 1 10,2 H 23" />
        <path class="scroll-progress-bar" id="scrollProgressBar" d="M 23,2 H 36 A 8 8 0 0 1 44,10 V 36 A 8 8 0 0 1 36,44 H 10 A 8 8 0 0 1 2,36 V 10 A 8 8 0 0 1 10,2 H 23" />
    </svg>
    <i class="bi bi-chevron-up scroll-top-icon"></i>
</button>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ONS SOUND JavaScript Core -->
<script src="<?= asset('assets/js/main.js') ?>"></script>

<script>
// AJAX Newsletter Subscription
document.getElementById('newsletterFooterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const msgBox = document.getElementById('newsletterMsg');
    const formData = new FormData(form);

    fetch('<?= url("newsletter_subscribe.php") ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        msgBox.style.display = 'block';
        msgBox.textContent = data.message;
        if (data.success) {
            msgBox.className = 'mt-2 text-success small';
            form.reset();
        } else {
            msgBox.className = 'mt-2 text-warning small';
        }
    })
    .catch(() => {
        msgBox.style.display = 'block';
        msgBox.className = 'mt-2 text-danger small';
        msgBox.textContent = 'Could not subscribe right now. Please try again.';
    });
});
</script>

</body>
</html>
