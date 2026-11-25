<?php
/**
 * ONS SOUND - Homepage
 * Strictly matching ref_img/homepage.png
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/Setting.php';

$pageTitle = 'Precision Audio Systems & Speakers';
$pageDescription = 'Hear every word with precision sound. Explore premium woofers, tweeters, midrange speakers, and amplifiers.';

$categoryModel = new Category();
$productModel = new Product();
$settingModel = new Setting();

$categories = $categoryModel->getActive();
$featuredProducts = $productModel->getFeatured(4);

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Full-Screen Hero Slider (2 Slides matching live onssound.com & ref_img/homepage.png) -->
<section class="hero-slider-section">
    <div id="heroCarousel" class="carousel slide carousel-fade hero-carousel" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="hover">
        <!-- Indicators (2 dots at bottom left matching ref_img) -->
        <div class="carousel-indicators hero-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        </div>

        <!-- Slides Inner -->
        <div class="carousel-inner h-100">
            <!-- Slide 1 -->
            <div class="carousel-item active hero-slide-1">
                <div class="container-fluid px-3 px-lg-5 hero-slide-container">
                    <div class="row align-items-center h-100">
                        <div class="col-xl-7 col-lg-8 col-md-10">
                            <div class="hero-overlay-content">
                                <h1 class="hero-title">Hear every word with<br>precision sound</h1>
                                <a href="<?= url('products.php') ?>" class="btn btn-ons-black">
                                    <span>Shop collection</span>
                                    <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item hero-slide-2">
                <div class="container-fluid px-3 px-lg-5 hero-slide-container">
                    <div class="row align-items-center h-100">
                        <div class="col-xl-7 col-lg-8 col-md-10">
                            <div class="hero-overlay-content">
                                <h1 class="hero-title">Crafted for audiophiles,<br>loved by all.</h1>
                                <a href="<?= url('products.php') ?>" class="btn btn-ons-black">
                                    <span>Shop collection</span>
                                    <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Marquee Ticker -->
<?php require __DIR__ . '/includes/ticker.php'; ?>

<!-- 3. "At ONS Sound" Category Grid -->
<section class="py-5">
    <div class="container-fluid px-3 px-lg-5">
        <div class="section-header text-center my-4">
            <h2 class="section-title">At ONS Sound</h2>
            <p class="section-subtitle">
                We offer a comprehensive range of high-quality audio products designed to deliver exceptional sound performance for every need. Our product lineup includes:
            </p>
        </div>

        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="category-card">
                        <div class="category-img-wrapper">
                            <a href="<?= url('products.php?category=' . urlencode($cat['slug'])) ?>">
                                <img src="<?= asset($cat['image'] ?? 'assets/images/categories/tweeters.png') ?>" 
                                     alt="<?= e($cat['name']) ?>" 
                                     loading="lazy">
                            </a>
                        </div>
                        <h3 class="category-card-title">
                            <a href="<?= url('products.php?category=' . urlencode($cat['slug'])) ?>" class="text-dark">
                                <?= e($cat['name']) ?>
                            </a>
                        </h3>
                        <p class="category-card-desc"><?= e($cat['description'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 4. Value Statement Pill Banner -->
        <div class="value-pill-banner shadow-sm">
            Whether you're building a professional sound system, upgrading your home audio setup, or seeking the perfect speaker components, ONS Sound offers reliable, top-tier products engineered to provide an unmatched audio experience.
        </div>
    </div>
</section>

<!-- 5. Feature Split Section 1: Superior Audio Engineering -->
<section class="feature-split-section py-4">
    <div class="container-fluid px-3 px-lg-5">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <div class="pe-lg-4">
                    <h2 class="feature-heading">Our Gateway to<br>Superior Audio<br>Engineering</h2>
                    <p class="feature-text">
                        Step into a world of superior sound with ONS Sound. Our speakers are the result of cutting-edge engineering and acoustic expertise, bringing you a sound experience that's powerful, clear, and undeniably captivating.
                    </p>
                    <a href="<?= url('about.php') ?>" class="btn btn-ons-black">About Us</a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="feature-img-box">
                    <img src="<?= asset('assets/images/home/studio-monitor.png') ?>" 
                         alt="Superior Audio Engineering Monitor" 
                         class="img-fluid" 
                         loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. Feature Split Section 2: Feel Every Vibration -->
<section class="feature-split-section py-5">
    <div class="container-fluid px-3 px-lg-5">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6 text-center order-lg-1 order-2">
                <div class="feature-img-box">
                    <img src="<?= asset('assets/images/home/tower-speaker.png') ?>" 
                         alt="Precision Tower Speaker" 
                         class="img-fluid" 
                         loading="lazy">
                </div>
            </div>
            <div class="col-lg-6 order-lg-2 order-1">
                <div class="ps-lg-4">
                    <h2 class="feature-heading">Hear Every Detail,<br>Feel Every Vibration</h2>
                    <p class="feature-text">
                        ONS Sound's speakers capture the full range of sound, from the deepest bass to the crispest treble. Whether you're listening to music or watching a movie, our speakers deliver a complete, immersive audio experience.
                    </p>
                    <a href="<?= url('about.php') ?>" class="btn btn-ons-black">About Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. "SHOP BY CATEGORIES" Carousel / Product Showcase -->
<section class="py-5 bg-white">
    <div class="container-fluid px-3 px-lg-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-dark rounded-circle" type="button" aria-label="Previous" style="width: 32px; height: 32px; padding: 0;"><i class="bi bi-chevron-left"></i></button>
                    <button class="btn btn-sm btn-outline-dark rounded-circle" type="button" aria-label="Next" style="width: 32px; height: 32px; padding: 0;"><i class="bi bi-chevron-right"></i></button>
                </div>
                <h2 class="fs-5 fw-bold text-uppercase mb-0 tracking-wide" style="letter-spacing: 1px;">Shop by Categories</h2>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <?php 
            $carouselProds = array_slice($featuredProducts, 0, 3);
            foreach ($carouselProds as $product): 
            ?>
                <div class="col-lg-3 col-md-6 col-6">
                    <?php require __DIR__ . '/includes/product-card.php'; ?>
                </div>
            <?php endforeach; ?>

            <!-- Discovery Card Matching Screenshot -->
            <div class="col-lg-3 col-md-6 col-6">
                <div class="discovery-card">
                    <div class="discovery-title">
                        Discovery<br>all new items
                    </div>
                    <div>
                        <a href="<?= url('products.php') ?>" class="discovery-btn" aria-label="Discover All Products">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 8. Homepage In-Section Contact Banner Box -->
<section class="py-4">
    <div class="container-fluid px-3 px-lg-5">
        <div class="home-contact-box">
            <div class="row g-4 align-items-center w-100 m-0">
                <div class="col-lg-6 col-md-12 p-0">
                    <div class="home-contact-narrative">
                        <p>
                            At Onssound, we prioritize your satisfaction and are committed to providing exceptional customer service. Our dedicated team is here to assist you with any inquiries or concerns regarding our products. Whether you need assistance with product selection, technical support, or warranty information, we are just a message away. Your experience with us is important, and we strive to ensure that every interaction is positive and fulfilling. Thank you for choosing Onssound, where quality sound meets outstanding service!
                        </p>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 p-0">
                    <div class="home-contact-form-wrap">
                        <form action="<?= url('contact_process.php') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="source" value="homepage_box">
                            <div class="mb-3">
                                <label for="home_contact_name" class="home-contact-label">Your name</label>
                                <input type="text" id="home_contact_name" name="name" class="home-contact-input" required>
                            </div>
                            <div class="mb-3">
                                <label for="home_contact_email" class="home-contact-label">Your email</label>
                                <input type="email" id="home_contact_email" name="email" class="home-contact-input" required>
                            </div>
                            <div class="mb-3">
                                <label for="home_contact_phone" class="home-contact-label">Contact Number</label>
                                <input type="tel" id="home_contact_phone" name="phone" class="home-contact-input" placeholder="Enter your phone number">
                            </div>
                            <div class="mb-3">
                                <label for="home_contact_message" class="home-contact-label">Your Message</label>
                                <textarea id="home_contact_message" name="message" class="home-contact-textarea" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="home-contact-submit-btn">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 9. "Shop the look" Section -->
<section class="py-5">
    <div class="container-fluid px-3 px-lg-5">
        <div class="text-center mb-5">
            <h2 class="section-title">Shop the look</h2>
            <p class="section-subtitle">Inspire and let yourself be inspired, from one unique fashion to another.</p>
        </div>

        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <div class="pe-lg-4">
                    <h3 class="fs-2 fw-bold text-dark mb-3">The Perfect Balance of Design, Technology, and Performance</h3>
                    <p class="text-muted lh-lg mb-4">
                        At ONS Sound, we pride ourselves on seamlessly blending cutting-edge technology with sophisticated and innovative design to craft audio solutions that redefine the listening experience. Our dedication to excellence ensures every product is engineered for extraordinary performance, delivering impeccable sound quality, unmatched durability, and a modern aesthetic.
                    </p>
                    <a href="<?= url('products.php') ?>" class="btn btn-ons-black">Click Here</a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="<?= asset('assets/images/home/shop-the-look.png') ?>" 
                     alt="Shop the look precision transducers" 
                     class="img-fluid rounded-3" 
                     loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- 10. "Happy Clients" (Testimonials) -->
<section class="py-5 bg-white">
    <div class="container-fluid px-3 px-lg-5">
        <div class="text-center mb-5">
            <h2 class="section-title">Happy Clients</h2>
            <p class="section-subtitle">Hear what they say about us</p>
        </div>

        <div class="row g-4">
            <!-- Review 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <div>
                        <?= render_stars(5) ?>
                        <h4 class="testimonial-title">Best Online Speaker Site</h4>
                        <p class="testimonial-text">"I always find something unique and affordable on this website"</p>
                    </div>
                    <div>
                        <div class="testimonial-author">Robert smith</div>
                        <div class="testimonial-role">Customer from USA</div>
                    </div>
                </div>
            </div>

            <!-- Review 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <div>
                        <?= render_stars(5) ?>
                        <h4 class="testimonial-title">Great Selection and Quality</h4>
                        <p class="testimonial-text">"I love the variety of styles and the high-quality sound system on this website"</p>
                    </div>
                    <div>
                        <div class="testimonial-author">Allen Lyn</div>
                        <div class="testimonial-role">Customer from France</div>
                    </div>
                </div>
            </div>

            <!-- Review 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <div>
                        <?= render_stars(5) ?>
                        <h4 class="testimonial-title">Best Customer Service</h4>
                        <p class="testimonial-text">"I finally found a web speaker site with stylish and flattering options in my budget."</p>
                    </div>
                    <div>
                        <div class="testimonial-author">Peter Rope</div>
                        <div class="testimonial-role">Customer from USA</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dynamic Offer Popup Modal (Ref: media_1795532853325_aef24d43.jpg) -->
<?php $offerData = get_offer_popup_data(); ?>
<div class="modal fade offer-popup-modal" id="offerModal" tabindex="-1" aria-labelledby="offerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
        <div class="modal-content border-0 overflow-hidden shadow-2xl" style="border-radius: 12px;">
            <div class="row g-0">
                <!-- Left Column: Dark speaker background & 20% OFF discount -->
                <div class="col-md-6 offer-modal-left text-white d-flex flex-column justify-content-between p-4 p-lg-5 position-relative">
                    <div class="offer-modal-left-content position-relative z-1 text-center text-md-start">
                        <h3 class="offer-lead-title fw-normal mb-2 text-white">Wait! before you leave...</h3>
                        <p class="offer-subtext text-white-50 small mb-4">Get <?= e($offerData['discount_percent']) ?>% off for your first order</p>
                        
                        <!-- Coupon Code Box -->
                        <div class="offer-coupon-box bg-white text-dark d-flex align-items-center justify-content-between px-3 py-2 rounded-2 mb-3">
                            <span class="offer-coupon-code fw-bold fs-5 tracking-wide text-uppercase" id="offerCouponText"><?= e($offerData['coupon_code']) ?></span>
                            <button type="button" class="btn btn-link text-dark p-0 border-0 copy-coupon-trigger" data-coupon="<?= e($offerData['coupon_code']) ?>" title="Copy Coupon" aria-label="Copy coupon code">
                                <i class="bi bi-copy fs-5"></i>
                            </button>
                        </div>
                        <p class="offer-instruction small text-white-50 mb-4" style="font-size: 0.78rem;">Use above code to get <?= e($offerData['discount_percent']) ?>% OFF for your first order when checkout</p>

                        <button type="button" class="btn btn-danger w-100 py-3 fw-bold rounded-1 grab-discount-trigger" data-coupon="<?= e($offerData['coupon_code']) ?>" style="background-color: #e51a24; border-color: #e51a24; font-size: 0.95rem;">
                            Grab the discount
                        </button>
                    </div>
                </div>

                <!-- Right Column: Dynamic Recommended Products -->
                <div class="col-md-6 bg-white p-4 p-lg-5 d-flex flex-column justify-content-between position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    
                    <div>
                        <h5 class="fw-semibold text-dark mb-4" id="offerModalLabel">Recommended Products</h5>
                        
                        <div class="offer-recommended-list d-flex flex-column gap-3">
                            <?php foreach ($offerData['products'] as $item): ?>
                                <a href="<?= url('product-details.php?slug=' . urlencode($item['slug'])) ?>" class="offer-product-item text-decoration-none text-dark d-flex align-items-center gap-3">
                                    <div class="offer-prod-thumb bg-light rounded-2 d-flex align-items-center justify-content-center p-2" style="width: 70px; height: 70px; flex-shrink: 0;">
                                        <img src="<?= asset($item['main_image'] ?? 'assets/images/products/speaker-white.png') ?>" alt="<?= e($item['name']) ?>" class="img-fluid" style="max-height: 55px; object-fit: contain;">
                                    </div>
                                    <div class="offer-prod-info">
                                        <div class="offer-prod-stars mb-1 small">
                                            <?= render_stars($item['rating'] ?? 5, $item['reviews_count'] ?? 0) ?>
                                        </div>
                                        <div class="offer-prod-name fw-medium text-truncate" style="max-width: 190px;"><?= e($item['name']) ?></div>
                                        <div class="offer-prod-price text-dark fw-semibold small"><?= format_price($item['price']) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
