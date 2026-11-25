-- ONS SOUND - Master Seed Data for 20 Tables
USE `onssound_db`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `cart_items`;
TRUNCATE TABLE `carts`;
TRUNCATE TABLE `wishlists`;
TRUNCATE TABLE `product_specifications`;
TRUNCATE TABLE `product_reviews`;
TRUNCATE TABLE `product_images`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `addresses`;
TRUNCATE TABLE `password_resets`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `blog_posts`;
TRUNCATE TABLE `blog_categories`;
TRUNCATE TABLE `contact_enquiries`;
TRUNCATE TABLE `newsletter_subscribers`;
TRUNCATE TABLE `homepage_banners`;
TRUNCATE TABLE `homepage_sections`;
TRUNCATE TABLE `site_settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. users
INSERT INTO `users` (`id`, `role`, `full_name`, `email`, `password_hash`, `phone`, `status`, `created_at`) VALUES
(1, 'admin', 'ONS Sound Administrator', 'admin@onssound.com', '$2y$10$LIP06Een15TokFvTmNVBE.lUnZmHZwcI23HjpVVg2DJ2DSvkLQAQa', '+91 7976824255', 'active', NOW()),
(2, 'customer', 'Demo Customer', 'customer@onssound.com', '$2y$10$6akr51/H0EVz9BBMToOi3uK6NGb2e2FS05mK8GeogA3iNhpfDSXke', '+91 9876543210', 'active', NOW());

-- 2. addresses
INSERT INTO `addresses` (`id`, `user_id`, `address_title`, `recipient_name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `is_default`, `created_at`) VALUES
(1, 2, 'Default Shipping', 'Demo Customer', '+91 9876543210', '123 Sound Wave Road', 'Near City Center', 'Ajmer', 'Rajasthan', '305001', 'India', 1, NOW());

-- 3. categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `display_order`, `is_active`) VALUES
(1, 'Tweeters', 'tweeters', 'For crystal-clear high-frequency sound reproduction.', 'assets/images/categories/tweeters.jpg', 1, 1),
(2, 'Midrange Speakers', 'midrange-speakers', 'Perfectly balanced for rich, detailed mid-frequency audio.', 'assets/images/categories/midrange-speakers.jpg', 2, 1),
(3, 'Woofers & Subwoofers', 'woofers-subwoofers', 'Powerful low-end sound for deep bass and impactful audio experiences.', 'assets/images/categories/woofers-subwoofers.jpg', 3, 1),
(4, 'Speaker Cabinets', 'speaker-cabinets', 'Custom-built for optimal sound enclosure and durability.', 'assets/images/categories/speaker-cabinets.jpg', 4, 1),
(5, 'Amplifiers', 'amplifiers', 'High-performance amplification to drive your audio system to its full potential.', 'assets/images/categories/amplifiers.jpg', 5, 1),
(6, 'Portable Trolly Stage', 'portable-trolly-stage', 'And PA Speaker System for dynamic, high-output events.', 'assets/images/categories/portable-trolly-stage.jpg', 6, 1);

-- 4. products
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `model_name`, `price`, `compare_at_price`, `stock_quantity`, `stock_status`, `rating`, `reviews_count`, `is_featured`, `is_active`, `main_image`, `hover_image`, `short_description`, `full_description`, `created_at`) VALUES
(1, 3, 'Speaker 1', 'speaker-1', 'ONS-SPK-001', '15 FR100', 2000.00, 2400.00, 25, 'in_stock', 5.00, 3, 1, 1, 'assets/images/products/speaker-1.png', 'assets/images/products/speaker-1.png', 'High-performance loudspeaker delivering crisp acoustic output and robust reliability.', 'Engineered with premium acoustic components, Speaker 1 delivers an exceptionally balanced response across demanding audio environments.', NOW()),
(2, 2, 'Speaker 10', 'speaker-10', 'ONS-SPK-010', '15 FR200', 17.99, 25.00, 40, 'in_stock', 5.00, 2, 1, 1, 'assets/images/products/speaker-10.png', 'assets/images/products/speaker-10.png', 'Flagship 15-inch powerhouse transducer for unmatched vocal clarity and musical precision.', 'Ons sound’s speakers capture the full range of sound, from the deepest bass to the crispest treble. Whether you’re listening to music or watching a movie, our speakers deliver a complete, immersive audio experience.', NOW()),
(3, 3, 'Speaker 13', 'speaker-13', 'ONS-SPK-013', '15 FR250', 17.99, 22.00, 15, 'in_stock', 0.00, 0, 0, 1, 'assets/images/products/speaker-13.png', 'assets/images/products/speaker-13.png', 'Heavy duty woofer component built for pristine low-frequency extension and high power tolerance.', 'Speaker 13 features reinforced cone construction and a high-flux motor assembly to maintain distortion-free sound even at extreme sound pressure levels.', NOW()),
(4, 1, 'Speaker 18', 'speaker-18', 'ONS-SPK-018', '18 SUB800', 17.99, 25.00, 10, 'in_stock', 0.00, 0, 0, 1, 'assets/images/products/speaker-18.png', 'assets/images/products/speaker-18.png', 'Pro-grade low frequency transducer designed for sub-bass systems and stadium sound arrays.', 'Built for the most demanding touring and installation applications, Speaker 18 provides deep, tactile bass reproduction with uncompromising structural stability.', NOW()),
(5, 2, 'Speaker 2', 'speaker-2', 'ONS-SPK-002', '12 MD150', 36.00, 45.00, 30, 'in_stock', 5.00, 2, 1, 1, 'assets/images/products/speaker-2.png', 'assets/images/products/speaker-2.png', 'Mid-frequency driver with ultra-responsive transient dynamics and clear projection.', 'Speaker 2 is crafted to ensure natural vocal timbre and instrument presence in multi-way speaker cabinets and stage monitors.', NOW()),
(6, 3, 'Speaker 3', 'speaker-3', 'ONS-SPK-003', '15 PRO500', 20000.00, 24000.00, 8, 'in_stock', 4.00, 2, 1, 1, 'assets/images/products/speaker-3.png', 'assets/images/products/speaker-3.png', 'Flagship professional speaker module with massive power handling and tour-tested durability.', 'The ultimate workhorse for touring systems and concert halls, Speaker 3 delivers colossal sound output while preserving pure tonal purity.', NOW()),
(7, 3, 'Speaker 4', 'speaker-4', 'ONS-SPK-004', '15 FR300', 15000.00, 18000.00, 12, 'in_stock', 5.00, 1, 1, 1, 'assets/images/products/speaker-4.png', 'assets/images/products/speaker-4.png', 'High-fidelity audio driver with custom spider and surround for seamless response linearity.', 'Features an advanced cooling topology and precision-wound voice coil that prevents thermal compression during extended sessions.', NOW()),
(8, 1, 'Speaker 5', 'speaker-5', 'ONS-SPK-005', '10 FR100', 5.99, 9.99, 50, 'in_stock', 5.00, 1, 1, 1, 'assets/images/products/speaker-5.png', 'assets/images/products/speaker-5.png', 'Compact and versatile high-performance speaker for near-field monitoring and background arrays.', 'Offers smooth frequency coverage and wide dispersion characteristics in an accessible form factor.', NOW());

-- 5. product_images
INSERT INTO `product_images` (`product_id`, `image_path`, `display_order`, `is_primary`) VALUES
(2, 'assets/images/products/speaker-10.png', 1, 1),
(2, 'assets/images/products/speaker-10.png', 2, 0),
(1, 'assets/images/products/speaker-1.png', 1, 1),
(7, 'assets/images/products/speaker-4.png', 1, 1);

-- 6. product_specifications (Matching original table in product-details.png)
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `display_order`) VALUES
(2, 'Model Name', '15 FR200', 1),
(2, 'Normal Diameter', '15 inch', 2),
(2, 'Nominal Impedance', '8 ohms', 3),
(2, 'DC Resistance', '6.3 ohms', 4),
(2, 'Nominal Power Handling', '200W', 5),
(2, 'Continuous Power Handling', '400W', 6),
(2, 'Sensitivity (1 w / 1 m)', '98db', 7),
(2, 'Frequency Range', '55-5000Hz', 8),
(2, 'Voice Coil Diameter', '2in (50.5 mm)', 9),
(2, 'Voice Coil Material', 'Ccaw', 10),
(2, 'Former Material', 'Glass Fiber', 11),
(2, 'Magnet', '155 multiply 120 mm', 12),
(2, 'Basket', 'Steel', 13),

(1, 'Model Name', '15 FR100', 1),
(1, 'Normal Diameter', '12 inch', 2),
(1, 'Nominal Impedance', '8 ohms', 3),
(1, 'Nominal Power Handling', '150W', 4),
(1, 'Sensitivity (1 w / 1 m)', '96db', 5),
(1, 'Basket', 'Steel', 6),

(7, 'Model Name', '15 FR300', 1),
(7, 'Normal Diameter', '15 inch', 2),
(7, 'Nominal Impedance', '8 ohms', 3),
(7, 'Nominal Power Handling', '250W', 4),
(7, 'Continuous Power Handling', '500W', 5),
(7, 'Sensitivity (1 w / 1 m)', '98db', 6),
(7, 'Basket', 'Steel', 7);

-- 7. product_reviews
INSERT INTO `product_reviews` (`product_id`, `user_id`, `reviewer_name`, `reviewer_email`, `rating`, `review_text`, `status`, `created_at`) VALUES
(2, 2, 'Rajesh Sharma', 'rajesh@example.com', 5, 'Exceptional audio clarity and very sturdy construction. Highly recommended for live sound setups!', 'approved', NOW()),
(2, NULL, 'Amit Verma', 'amit@example.com', 5, 'Value for money is unbeatable. The 15 FR200 model exceeded my expectations.', 'approved', NOW()),
(1, NULL, 'Sunil Joshi', 'sunil@example.com', 5, 'Crisp sound and easy to install into our custom enclosures.', 'approved', NOW()),
(7, NULL, 'Vikas Meena', 'vikas@example.com', 5, 'Massive punch and crystal clear highs. Best purchase this year.', 'approved', NOW());

-- 8. blog_categories
INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'UNCATEGORIZED', 'uncategorized', 'General articles and announcements'),
(2, 'TECH', 'tech', 'Audio technology, drivers, and acoustic engineering');

-- 9. blog_posts (Authentic from blog.png)
INSERT INTO `blog_posts` (`id`, `category_id`, `title`, `slug`, `excerpt`, `content`, `image`, `author`, `is_published`) VALUES
(1, 1, 'Maximizing Sound Quality with Multi-Room Speaker System Christine Is A True Style Icon', 'maximizing-sound-quality-with-multi-room-speaker-system', 'Learn how to calibrate multi-room acoustic zones for an uninterrupted, audiophile-grade sound stage across your home or venue.', '<p>A multi-room sound system allows you to stream your favorite music to multiple speakers throughout your home or commercial space. By synchronizing multiple audio nodes and optimizing speaker placement, you eliminate dead spots and achieve seamless sound coverage.</p><p>At ONS Sound, our precision-crafted speaker components ensure consistent frequency response and zero phase cancellation whether you are entertaining guests or relaxing in your personal listening room.</p>', 'assets/images/blog/blog-1.png', 'ONS Sound Team', 1),
(2, 2, 'The Importance of Frequency Range in Speaker Selection', 'the-importance-of-frequency-range-in-speaker-selection', 'Understanding frequency response curves and crossovers is crucial to choosing the ideal speaker for your unique acoustic environment.', '<p>When evaluating speakers, frequency range represents the spectrum of sound-from sub-bass rumblings to shimmering highs-that a speaker can accurately reproduce. Human hearing spans approximately 20 Hz to 20,000 Hz.</p><p>Selecting speakers with linear response in their designated band guarantees that vocals sound natural, drums have impact, and high notes stay silky and fatigue-free.</p>', 'assets/images/blog/blog-2.png', 'Audio Engineer', 1),
(3, 2, 'How to Choose the Perfect Speaker for Your Home Theater', 'how-to-choose-the-perfect-speaker-for-your-home-theater', 'A comprehensive buyer guide to matching sensitivity, impedance, and enclosure volume for cinema-grade home theater audio.', '<p>Building an immersive home theater requires more than just high wattage; it requires balanced acoustic matching. Matching driver sensitivity and nominal impedance with your power amplifier guarantees dynamic headroom without clipping.</p><p>Explore ONS Sound’s dedicated woofers, subwoofers, and mid-frequency units designed specifically to bring blockbuster cinema sound directly to your living room.</p>', 'assets/images/blog/blog-3.png', 'ONS Sound Team', 1);

-- 10. homepage_sections
INSERT INTO `homepage_sections` (`section_key`, `title`, `subtitle`, `content`, `display_order`, `is_active`) VALUES
('hero', 'Hear every word with precision sound', NULL, 'Shop collection >', 1, 1),
('ticker_home', NULL, NULL, '⚡ Crystal-Clear Sound That Resonates Beyond Boundaries ⚡ Bring Music to Life with Every Note ⚡ Technology Meets Acoustic Excellence', 2, 1),
('categories_grid', 'At ONS Sound', 'we offer a comprehensive range of high-quality audio products designed to deliver exceptional sound performance for every need. Our product lineup includes:', NULL, 3, 1),
('value_banner', NULL, NULL, 'Whether you''re building a professional sound system, upgrading your home audio setup, or seeking the perfect speaker components, ONS Sound offers reliable, top-tier products engineered to provide an unmatched audio experience.', 4, 1),
('feature_engineering', 'Our Gateway to Superior Audio Engineering', NULL, 'Step into a world of superior sound with Onssound. Our speakers are the result of cutting-edge engineering and acoustic expertise, bringing you a sound experience that''s powerful, clear, and undeniably captivating.', 5, 1),
('feature_vibration', 'Hear Every Detail, Feel Every Vibration', NULL, 'Onssound''s speakers capture the full range of sound, from the deepest bass to the crispest treble. Whether you''re listening to music or watching a movie, our speakers deliver a complete, immersive audio experience.', 6, 1),
('shop_the_look', 'The Perfect Balance of Design, Technology, and Performance', 'Inspire and let yourself be inspired, from one unique fashion to another.', 'At Onssound, we pride ourselves on seamlessly blending cutting-edge technology with sophisticated and innovative design to craft audio solutions that redefine the listening experience.', 7, 1),
('happy_clients', 'Happy Clients', 'Hear what they say about us', NULL, 8, 1);

-- 11. homepage_banners
INSERT INTO `homepage_banners` (`section_key`, `title`, `subtitle`, `button_text`, `button_link`, `image_path`, `display_order`, `is_active`) VALUES
('hero', 'Hear every word with precision sound', 'Experience studio-grade clarity', 'Shop collection >', 'products.php', 'assets/images/banners/hero-banner.jpg', 1, 1);

-- 12. site_settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'ONS SOUND', 'general'),
('site_tagline', 'Hear every word with precision sound', 'general'),
('contact_address', 'Ajmer, Rajasthan, India', 'contact'),
('contact_email', 'sharmatripti2106@gmail.com', 'contact'),
('contact_phone', '+91 7976824255', 'contact'),
('contact_hours', 'Our store has re-opened for shopping, exchange Every day 11am to 7pm', 'contact'),
('ticker_text', '★ Spring Clearance Event: Save Up to 70% ★ Spring Clearance Event: Save Up to 70% ★ Spring Clearance Event: Save Up to 70%', 'announcement'),
('home_ticker', '⚡ Crystal-Clear Sound That Resonates Beyond Boundaries ⚡ Bring Music to Life with Every Note ⚡ Technology Meets Acoustic', 'announcement'),
('social_facebook', 'https://facebook.com', 'social'),
('social_twitter', 'https://twitter.com', 'social'),
('social_instagram', 'https://instagram.com', 'social'),
('social_tiktok', 'https://tiktok.com', 'social'),
('social_pinterest', 'https://pinterest.com', 'social');
