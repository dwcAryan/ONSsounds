<?php
/**
 * ONS SOUND - About Us Page
 * Strictly matching ref_img/about-us.png & onssound.com
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'About Us';
$pageDescription = 'Where innovation leads, sound follows – Ons sound. Discover our heritage, vision, craftsmanship, and acoustic engineering.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Full-Width About Hero Banner -->
<section class="about-hero">
    <div class="container text-center">
        <h1 class="about-hero-title">Where innovation leads, sound follows Ons sound</h1>
    </div>
</section>

<!-- 2. "We are Onssound" Narrative -->
<section class="py-5 bg-white text-center">
    <div class="container py-lg-4" style="max-width: 960px;">
        <h2 class="about-section-heading mb-4">We are Onssound</h2>
        <p class="about-narrative-text mb-4">
            ONS Sound has its roots in a family tradition that dates back decades. The journey began with my father, Bhanwar Lal Jangid, who owned and operated a radio shop that specialized in manufacturing and repairing radios. Growing up in this environment, I was surrounded by a deep passion for sound technology. My father’s enthusiasm for audio equipment, from the intricacies of radio mechanics to the art of crafting sound, ignited a spark in me from an early age.
        </p>
        <p class="about-narrative-text mb-0">
            Inspired by his work, I joined him in the business, learning the trade and developing a keen interest in speakers and audio systems. As I became more immersed in the world of sound, I recognized an opportunity to expand and innovate in the speaker manufacturing industry. This led me to establish ONS Sound in 1998, with the vision of creating a brand known for its unwavering commitment to quality and performance.
        </p>
        <div class="about-divider-wrap my-5">
            <hr class="about-divider">
        </div>
    </div>
</section>

<!-- 3. Split Story Block 1: Our Vision (Image Left, Text Right) -->
<section class="py-5 bg-white">
    <div class="container-fluid px-3 px-lg-5">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6 text-center">
                <div class="about-vision-img-wrap">
                    <img src="<?= asset('assets/images/about/vision-speaker-1.png') ?>" 
                         alt="Precision speaker touch control" 
                         class="img-fluid rounded-4 shadow-sm about-vision-img" 
                         loading="lazy">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-4 pe-lg-3">
                    <h3 class="about-story-heading mb-4">Our Vision</h3>
                    <p class="about-story-text mb-4">
                        What started as a small family operation has now grown into a leading manufacturer of a wide variety of speakers, amplifiers, and audio components. Our products are built on the same principles of craftsmanship and passion that my father instilled in me. Today, ONS Sound continues to honor that legacy while embracing new technologies and innovations to deliver exceptional audio experiences.
                    </p>
                    <p class="about-story-text mb-4">
                        The passion for sound that began in my father’s radio shop has driven ONS Sound to where it is today, and we remain committed to delivering the highest quality audio solutions to our customers.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4. Split Story Block 2: Our Vision (Text Left, Image Right) -->
<section class="py-5 bg-white">
    <div class="container-fluid px-3 px-lg-5">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <div class="pe-lg-4 ps-lg-3">
                    <h3 class="about-story-heading mb-4">Our Vision</h3>
                    <p class="about-story-text mb-0">
                        Our Vision: to become synonymous with superior sound quality. We strive to create products that not only meet but exceed expectations, ensuring that when people think of outstanding audio, they think of ONS Sound.
                    </p>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="row g-3 justify-content-center align-items-center">
                    <div class="col-6">
                        <img src="<?= asset('assets/images/about/vision-lifestyle.png') ?>" 
                             alt="Portable acoustic clarity" 
                             class="img-fluid rounded-4 shadow-sm about-lifestyle-img" 
                             loading="lazy">
                    </div>
                    <div class="col-6">
                        <img src="<?= asset('assets/images/about/vision-speaker-2.png') ?>" 
                             alt="Transducer on table" 
                             class="img-fluid rounded-4 shadow-sm about-lifestyle-img" 
                             loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. "Quality is our priority" Pillars in Beige Box -->
<section class="py-5 bg-white">
    <div class="container-fluid px-3 px-lg-5">
        <div class="about-quality-box shadow-sm">
            <div class="text-center mb-5">
                <h2 class="about-section-heading mb-3">Quality is our priority</h2>
                <p class="about-quality-subtitle">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla.
                </p>
            </div>

            <div class="row g-4 text-center justify-content-center">
                <!-- Pillar 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="p-3">
                        <div class="about-icon-circle mx-auto mb-3">
                            <i class="bi bi-feather"></i>
                        </div>
                        <h4 class="about-pillar-title mb-2">High-Quality</h4>
                        <p class="about-pillar-desc mb-0">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla.
                        </p>
                    </div>
                </div>

                <!-- Pillar 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="p-3">
                        <div class="about-icon-circle mx-auto mb-3">
                            <i class="bi bi-triangle"></i>
                        </div>
                        <h4 class="about-pillar-title mb-2">Laconic Design</h4>
                        <p class="about-pillar-desc mb-0">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla.
                        </p>
                    </div>
                </div>

                <!-- Pillar 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="p-3">
                        <div class="about-icon-circle mx-auto mb-3">
                            <i class="bi bi-grid-3x3"></i>
                        </div>
                        <h4 class="about-pillar-title mb-2">Various design</h4>
                        <p class="about-pillar-desc mb-0">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. Customer Reviews Section Matching Screenshot -->
<section class="py-5 bg-white">
    <div class="container-fluid px-3 px-lg-5">
        <div class="about-review-container">
            <h2 class="about-section-heading mb-4">Our customer’s reviews</h2>
            <div class="about-quote-mark mb-3">
                <i class="bi bi-quote"></i>
            </div>
            <div class="mb-3 text-warning">
                <i class="bi bi-star-fill text-danger"></i>
                <i class="bi bi-star-fill text-danger"></i>
                <i class="bi bi-star-fill text-danger"></i>
                <i class="bi bi-star-fill text-danger"></i>
                <i class="bi bi-star-fill text-danger"></i>
            </div>
            <p class="about-review-text mb-4">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla.
            </p>
            <div class="about-reviewer-name mb-3">Sandra</div>
            <div class="about-review-nav d-flex gap-2">
                <button type="button" class="btn-review-arrow" aria-label="Previous review"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="btn-review-arrow" aria-label="Next review"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
    </div>
</section>

<!-- 7. "Shop Gram" (6-Image Grid Matching Screenshot) -->
<section class="py-5 bg-white border-top">
    <div class="container-fluid px-3 px-lg-5">
        <div class="text-center mb-5">
            <h2 class="about-section-heading">Shop Gram</h2>
            <p class="section-subtitle">Follow our acoustic journey and explore our precision-built transducers in action.</p>
        </div>

        <div class="row g-3">
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="col-lg-2 col-md-4 col-sm-6 col-6">
                    <div class="gram-img-box">
                        <img src="<?= asset("assets/images/about/gram-{$i}.png") ?>" 
                             alt="ONS Sound transducer showcase <?= $i ?>" 
                             loading="lazy">
                        <div class="gram-overlay">
                            <i class="bi bi-instagram"></i>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
