
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2026 at 05:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `onssound_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_title` varchar(50) NOT NULL DEFAULT 'Home',
  `recipient_name` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'India',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `address_title`, `recipient_name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, 'Default Shipping', 'Demo Customer', '+91 9876543210', '123 Sound Wave Road', 'Near City Center', 'Ajmer', 'Rajasthan', '305001', 'India', 1, '2026-11-25 00:40:32', '2026-11-25 00:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(150) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `user_id`, `user_name`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'ONS Sound Administrator', 'Admin Login', 'Logged in successfully from ::1', '::1', '2026-11-24 23:38:25'),
(2, 1, 'Administrator', 'Update Order Status', 'Changed order #ORD-20261124-7421 status to processing', '127.0.0.1', '2026-11-24 23:58:54'),
(3, 1, 'ONS Sound Administrator', 'Admin Login', 'Logged in successfully from ::1', '::1', '2026-11-25 00:06:11'),
(4, 1, 'ONS Sound Administrator', 'Enquiry Status', 'Marked enquiry #1 as unread', '::1', '2026-11-25 00:08:06'),
(5, 1, 'ONS Sound Administrator', 'Enquiry Status', 'Marked enquiry #1 as unread', '::1', '2026-11-25 00:08:55'),
(6, 1, 'System', 'Enquiry Status', 'Marked enquiry #1 as unread', '127.0.0.1', '2026-11-25 00:24:45'),
(7, 1, 'ONS Sound Administrator', 'Admin Login', 'Logged in successfully from ::1', '::1', '2026-11-25 09:55:01'),
(8, 1, 'ONS Sound Administrator', 'Admin Logout', 'Logged out session.', '::1', '2026-11-25 09:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'UNCATEGORIZED', 'uncategorized', 'General articles and announcements', '2026-11-25 00:40:32'),
(2, 'TECH', 'tech', 'Audio technology, drivers, and acoustic engineering', '2026-11-25 00:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL DEFAULT 'ONS Sound Team',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `category_id`, `title`, `slug`, `excerpt`, `content`, `image`, `author`, `is_published`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Maximizing Sound Quality with Multi-Room Speaker System Christine Is A True Style Icon', 'maximizing-sound-quality-with-multi-room-speaker-system', 'Learn how to calibrate multi-room acoustic zones for an uninterrupted, audiophile-grade sound stage across your home or venue.', '<p>A multi-room sound system allows you to stream your favorite music to multiple speakers throughout your home or commercial space. By synchronizing multiple audio nodes and optimizing speaker placement, you eliminate dead spots and achieve seamless sound coverage.</p><p>At ONS Sound, our precision-crafted speaker components ensure consistent frequency response and zero phase cancellation whether you are entertaining guests or relaxing in your personal listening room.</p>', 'assets/images/blog/blog-1.png', 'ONS Sound Team', 1, '2026-11-25 00:40:32', '2026-11-25 00:40:32', '2026-11-25 07:16:02'),
(2, 2, 'The Importance of Frequency Range in Speaker Selection', 'the-importance-of-frequency-range-in-speaker-selection', 'Understanding frequency response curves and crossovers is crucial to choosing the ideal speaker for your unique acoustic environment.', '<p>When evaluating speakers, frequency range represents the spectrum of sound-from sub-bass rumblings to shimmering highs-that a speaker can accurately reproduce. Human hearing spans approximately 20 Hz to 20,000 Hz.</p><p>Selecting speakers with linear response in their designated band guarantees that vocals sound natural, drums have impact, and high notes stay silky and fatigue-free.</p>', 'assets/images/blog/blog-2.png', 'Audio Engineer', 1, '2026-11-25 00:40:32', '2026-11-25 00:40:32', '2026-11-25 07:16:02'),
(3, 2, 'How to Choose the Perfect Speaker for Your Home Theater', 'how-to-choose-the-perfect-speaker-for-your-home-theater', 'A comprehensive buyer guide to matching sensitivity, impedance, and enclosure volume for cinema-grade home theater audio.', '<p>Building an immersive home theater requires more than just high wattage; it requires balanced acoustic matching. Matching driver sensitivity and nominal impedance with your power amplifier guarantees dynamic headroom without clipping.</p><p>Explore ONS Sound’s dedicated woofers, subwoofers, and mid-frequency units designed specifically to bring blockbuster cinema sound directly to your living room.</p>', 'assets/images/blog/blog-3.png', 'ONS Sound Team', 1, '2026-11-25 00:40:32', '2026-11-25 00:40:32', '2026-11-25 07:16:02');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `session_id`, `created_at`, `updated_at`) VALUES
(1, NULL, 'guest_0d05e55bc2115653711e71ab2ee0c998', '2026-11-25 06:21:52', '2026-11-25 06:21:52'),
(2, NULL, 'guest_c9d5a15f94af8da0352a098c50cc08ae', '2026-11-25 07:10:02', '2026-11-25 07:10:02'),
(3, 3, 'guest_24718062842221d819c8d8695c7c84e4', '2026-11-25 07:17:44', '2026-11-25 07:20:15'),
(4, NULL, 'guest_3919711673a97badce8c5699b3ae5856', '2026-11-25 07:19:33', '2026-11-25 07:19:33'),
(5, NULL, 'guest_76f9ef3dc4071401b55c29ede12fb8ca', '2026-11-25 07:28:05', '2026-11-25 07:28:05'),
(6, NULL, 'guest_1c611f3f81353daab4d444a5dc2259fd', '2026-11-25 07:56:37', '2026-11-25 07:56:37'),
(7, 2, NULL, '2026-11-25 08:27:00', '2026-11-25 08:27:00');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(4, 3, 2, 1, '2026-11-25 09:39:21', '2026-11-25 09:39:21');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Tweeters', 'tweeters', 'For crystal-clear high-frequency sound reproduction.', 'assets/images/categories/tweeters.jpg', 1, 1, '2026-11-25 00:40:32'),
(2, 'Midrange Speakers', 'midrange-speakers', 'Perfectly balanced for rich, detailed mid-frequency audio.', 'assets/images/categories/midrange-speakers.jpg', 2, 1, '2026-11-25 00:40:32'),
(3, 'Woofers & Subwoofers', 'woofers-subwoofers', 'Powerful low-end sound for deep bass and impactful audio experiences.', 'assets/images/categories/woofers-subwoofers.jpg', 3, 1, '2026-11-25 00:40:32'),
(4, 'Speaker Cabinets', 'speaker-cabinets', 'Custom-built for optimal sound enclosure and durability.', 'assets/images/categories/speaker-cabinets.jpg', 4, 1, '2026-11-25 00:40:32'),
(5, 'Amplifiers', 'amplifiers', 'High-performance amplification to drive your audio system to its full potential.', 'assets/images/categories/amplifiers.jpg', 5, 1, '2026-11-25 00:40:32'),
(6, 'Portable Trolly Stage', 'portable-trolly-stage', 'And PA Speaker System for dynamic, high-output events.', 'assets/images/categories/portable-trolly-stage.jpg', 6, 1, '2026-11-25 00:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `contact_enquiries`
--

CREATE TABLE `contact_enquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `message` text NOT NULL,
  `source` enum('contact_page','homepage_box') NOT NULL DEFAULT 'contact_page',
  `status` enum('new','read','replied') NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_spend` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `discount_value`, `min_spend`, `max_discount`, `usage_limit`, `usage_count`, `starts_at`, `expires_at`, `is_active`, `created_at`) VALUES
(1, 'OFF20%', 'percentage', 20.00, 0.00, NULL, NULL, 0, NULL, NULL, 1, '2026-11-24 23:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `homepage_banners`
--

CREATE TABLE `homepage_banners` (
  `id` int(11) NOT NULL,
  `section_key` varchar(50) NOT NULL DEFAULT 'hero',
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homepage_banners`
--

INSERT INTO `homepage_banners` (`id`, `section_key`, `title`, `subtitle`, `button_text`, `button_link`, `image_path`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'hero', 'Hear every word with precision sound', 'Experience studio-grade clarity', 'Shop collection >', 'products.php', 'assets/images/banners/hero-banner.jpg', 1, 1, '2026-11-25 00:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `homepage_sections`
--

CREATE TABLE `homepage_sections` (
  `id` int(11) NOT NULL,
  `section_key` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homepage_sections`
--

INSERT INTO `homepage_sections` (`id`, `section_key`, `title`, `subtitle`, `content`, `display_order`, `is_active`, `updated_at`) VALUES
(1, 'hero', 'Hear every word with precision sound', NULL, 'Shop collection >', 1, 1, '2026-11-25 00:40:32'),
(2, 'ticker_home', NULL, NULL, '⚡ Crystal-Clear Sound That Resonates Beyond Boundaries ⚡ Bring Music to Life with Every Note ⚡ Technology Meets Acoustic Excellence', 2, 1, '2026-11-25 00:40:32'),
(3, 'categories_grid', 'At ONS Sound', 'we offer a comprehensive range of high-quality audio products designed to deliver exceptional sound performance for every need. Our product lineup includes:', NULL, 3, 1, '2026-11-25 00:40:32'),
(4, 'value_banner', NULL, NULL, 'Whether you\'re building a professional sound system, upgrading your home audio setup, or seeking the perfect speaker components, ONS Sound offers reliable, top-tier products engineered to provide an unmatched audio experience.', 4, 1, '2026-11-25 00:40:32'),
(5, 'feature_engineering', 'Our Gateway to Superior Audio Engineering', NULL, 'Step into a world of superior sound with Onssound. Our speakers are the result of cutting-edge engineering and acoustic expertise, bringing you a sound experience that\'s powerful, clear, and undeniably captivating.', 5, 1, '2026-11-25 00:40:32'),
(6, 'feature_vibration', 'Hear Every Detail, Feel Every Vibration', NULL, 'Onssound\'s speakers capture the full range of sound, from the deepest bass to the crispest treble. Whether you\'re listening to music or watching a movie, our speakers deliver a complete, immersive audio experience.', 6, 1, '2026-11-25 00:40:32'),
(7, 'shop_the_look', 'The Perfect Balance of Design, Technology, and Performance', 'Inspire and let yourself be inspired, from one unique fashion to another.', 'At Onssound, we pride ourselves on seamlessly blending cutting-edge technology with sophisticated and innovative design to craft audio solutions that redefine the listening experience.', 7, 1, '2026-11-25 00:40:32'),
(8, 'happy_clients', 'Happy Clients', 'Hear what they say about us', NULL, 8, 1, '2026-11-25 00:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `navigation_menus`
--

CREATE TABLE `navigation_menus` (
  `id` int(11) NOT NULL,
  `menu_location` varchar(50) NOT NULL DEFAULT 'primary',
  `title` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `target` varchar(20) NOT NULL DEFAULT '_self',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `navigation_menus`
--

INSERT INTO `navigation_menus` (`id`, `menu_location`, `title`, `url`, `target`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'primary', 'Home', 'index.php', '_self', 1, 1, '2026-11-24 23:11:40'),
(2, 'primary', 'Products', 'products.php', '_self', 2, 1, '2026-11-24 23:11:40'),
(3, 'primary', 'Blog', 'blog.php', '_self', 3, 1, '2026-11-24 23:11:40'),
(4, 'primary', 'About Us', 'about.php', '_self', 4, 1, '2026-11-24 23:11:40'),
(5, 'primary', 'Contact Us', 'contact.php', '_self', 5, 1, '2026-11-24 23:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `status` enum('active','unsubscribed') NOT NULL DEFAULT 'active',
  `subscribed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(191) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `shipping_address` text NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(100) NOT NULL DEFAULT 'Order Booking (Pay on Delivery/Contact)',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `customer_notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `order_status`, `customer_notes`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 'ONS-20261125-DEMO01', 2, 'Demo Customer', 'customer@onssound.com', '+91 9876543210', '123 Sound Wave Road, Ajmer, Rajasthan, 305001', 2000.00, 0.00, 2000.00, 'Order Booking (Pay on Delivery/Contact)', 'pending', 'Please call before delivery', NULL, '2026-11-25 07:08:48', '2026-11-25 07:08:48');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(191) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `notify_customer` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `old_status`, `new_status`, `notes`, `notify_customer`, `created_by`, `created_at`) VALUES
(1, 11, 'pending', 'processing', 'Status updated from Pending to Processing', 0, '1', '2026-11-24 23:58:54');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(3, 'dwc.aryanshah@gmail.com', 'ab12fbeefb5e363027fc2fb024ba6a3a5b065e663097cc89681a9954be477136', '2026-11-25 10:59:29', '2026-11-25 09:59:29');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `model_name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `compare_at_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 10,
  `stock_status` enum('in_stock','out_of_stock','preorder') NOT NULL DEFAULT 'in_stock',
  `rating` decimal(3,2) NOT NULL DEFAULT 5.00,
  `reviews_count` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `main_image` varchar(255) NOT NULL,
  `hover_image` varchar(255) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `full_description` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `model_name`, `price`, `compare_at_price`, `stock_quantity`, `stock_status`, `rating`, `reviews_count`, `is_featured`, `is_active`, `main_image`, `hover_image`, `short_description`, `full_description`, `created_at`, `updated_at`) VALUES
(1, 3, 'Speaker 1', 'speaker-1', 'ONS-SPK-001', '15 FR100', 2000.00, 2400.00, 25, 'in_stock', 5.00, 1, 1, 1, 'assets/images/products/speaker-1.png', 'assets/images/products/speaker-1.png', 'High-performance loudspeaker delivering crisp acoustic output and robust reliability.', 'Engineered with premium acoustic components, Speaker 1 delivers an exceptionally balanced response across demanding audio environments.', '2026-11-25 00:40:32', '2026-11-25 08:34:37'),
(2, 2, 'Speaker 10', 'speaker-10', 'ONS-SPK-010', '15 FR200', 17.99, 25.00, 40, 'in_stock', 5.00, 2, 1, 1, 'assets/images/products/speaker-10.png', 'assets/images/products/speaker-10.png', 'Flagship 15-inch powerhouse transducer for unmatched vocal clarity and musical precision.', 'Ons sound’s speakers capture the full range of sound, from the deepest bass to the crispest treble. Whether you’re listening to music or watching a movie, our speakers deliver a complete, immersive audio experience.', '2026-11-25 00:40:32', '2026-11-25 07:16:02'),
(3, 3, 'Speaker 13', 'speaker-13', 'ONS-SPK-013', '15 FR250', 17.99, 22.00, 15, 'in_stock', 0.00, 0, 0, 1, 'assets/images/products/speaker-13.png', 'assets/images/products/speaker-13.png', 'Heavy duty woofer component built for pristine low-frequency extension and high power tolerance.', 'Speaker 13 features reinforced cone construction and a high-flux motor assembly to maintain distortion-free sound even at extreme sound pressure levels.', '2026-11-25 00:40:32', '2026-11-25 07:16:02'),
(4, 1, 'Speaker 18', 'speaker-18', 'ONS-SPK-018', '18 SUB800', 17.99, 25.00, 10, 'in_stock', 0.00, 0, 0, 1, 'assets/images/products/speaker-18.png', 'assets/images/products/speaker-18.png', 'Pro-grade low frequency transducer designed for sub-bass systems and stadium sound arrays.', 'Built for the most demanding touring and installation applications, Speaker 18 provides deep, tactile bass reproduction with uncompromising structural stability.', '2026-11-25 00:40:32', '2026-11-25 07:16:02'),
(5, 2, 'Speaker 2', 'speaker-2', 'ONS-SPK-002', '12 MD150', 36.00, 45.00, 30, 'in_stock', 5.00, 2, 1, 1, 'assets/images/products/speaker-2.png', 'assets/images/products/speaker-2.png', 'Mid-frequency driver with ultra-responsive transient dynamics and clear projection.', 'Speaker 2 is crafted to ensure natural vocal timbre and instrument presence in multi-way speaker cabinets and stage monitors.', '2026-11-25 00:40:32', '2026-11-25 07:16:02'),
(6, 3, 'Speaker 3', 'speaker-3', 'ONS-SPK-003', '15 PRO500', 20000.00, 24000.00, 8, 'in_stock', 4.00, 2, 1, 1, 'assets/images/products/speaker-3.png', 'assets/images/products/speaker-3.png', 'Flagship professional speaker module with massive power handling and tour-tested durability.', 'The ultimate workhorse for touring systems and concert halls, Speaker 3 delivers colossal sound output while preserving pure tonal purity.', '2026-11-25 00:40:32', '2026-11-25 07:16:02'),
(7, 3, 'Speaker 4', 'speaker-4', 'ONS-SPK-004', '15 FR300', 15000.00, 18000.00, 12, 'in_stock', 5.00, 1, 1, 1, 'assets/images/products/speaker-4.png', 'assets/images/products/speaker-4.png', 'High-fidelity audio driver with custom spider and surround for seamless response linearity.', 'Features an advanced cooling topology and precision-wound voice coil that prevents thermal compression during extended sessions.', '2026-11-25 00:40:32', '2026-11-25 07:16:02'),
(8, 1, 'Speaker 5', 'speaker-5', 'ONS-SPK-005', '10 FR100', 5.99, 9.99, 50, 'in_stock', 5.00, 1, 1, 1, 'assets/images/products/speaker-5.png', 'assets/images/products/speaker-5.png', 'Compact and versatile high-performance speaker for near-field monitoring and background arrays.', 'Offers smooth frequency coverage and wide dispersion characteristics in an accessible form factor.', '2026-11-25 00:40:32', '2026-11-25 07:16:02');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `display_order`, `is_primary`, `created_at`) VALUES
(1, 2, 'assets/images/products/speaker-10.png', 1, 1, '2026-11-25 00:40:32'),
(2, 2, 'assets/images/products/speaker-10.png', 2, 0, '2026-11-25 00:40:32'),
(3, 1, 'assets/images/products/speaker-1.png', 1, 1, '2026-11-25 00:40:32'),
(4, 7, 'assets/images/products/speaker-4.png', 1, 1, '2026-11-25 00:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reviewer_name` varchar(150) NOT NULL,
  `reviewer_email` varchar(191) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `review_text` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `reviewer_name`, `reviewer_email`, `rating`, `review_text`, `status`, `created_at`) VALUES
(1, 2, 2, 'Rajesh Sharma', 'rajesh@example.com', 5, 'Exceptional audio clarity and very sturdy construction. Highly recommended for live sound setups!', 'approved', '2026-11-25 00:40:32'),
(2, 2, NULL, 'Amit Verma', 'amit@example.com', 5, 'Value for money is unbeatable. The 15 FR200 model exceeded my expectations.', 'approved', '2026-11-25 00:40:32'),
(3, 1, NULL, 'Sunil Joshi', 'sunil@example.com', 5, 'Crisp sound and easy to install into our custom enclosures.', 'approved', '2026-11-25 00:40:32'),
(4, 7, NULL, 'Vikas Meena', 'vikas@example.com', 5, 'Massive punch and crystal clear highs. Best purchase this year.', 'approved', '2026-11-25 00:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `product_specifications`
--

CREATE TABLE `product_specifications` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `spec_key` varchar(100) NOT NULL,
  `spec_value` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_specifications`
--

INSERT INTO `product_specifications` (`id`, `product_id`, `spec_key`, `spec_value`, `display_order`, `created_at`) VALUES
(1, 2, 'Model Name', '15 FR200', 1, '2026-11-25 00:40:32'),
(2, 2, 'Normal Diameter', '15 inch', 2, '2026-11-25 00:40:32'),
(3, 2, 'Nominal Impedance', '8 ohms', 3, '2026-11-25 00:40:32'),
(4, 2, 'DC Resistance', '6.3 ohms', 4, '2026-11-25 00:40:32'),
(5, 2, 'Nominal Power Handling', '200W', 5, '2026-11-25 00:40:32'),
(6, 2, 'Continuous Power Handling', '400W', 6, '2026-11-25 00:40:32'),
(7, 2, 'Sensitivity (1 w / 1 m)', '98db', 7, '2026-11-25 00:40:32'),
(8, 2, 'Frequency Range', '55-5000Hz', 8, '2026-11-25 00:40:32'),
(9, 2, 'Voice Coil Diameter', '2in (50.5 mm)', 9, '2026-11-25 00:40:32'),
(10, 2, 'Voice Coil Material', 'Ccaw', 10, '2026-11-25 00:40:32'),
(11, 2, 'Former Material', 'Glass Fiber', 11, '2026-11-25 00:40:32'),
(12, 2, 'Magnet', '155 multiply 120 mm', 12, '2026-11-25 00:40:32'),
(13, 2, 'Basket', 'Steel', 13, '2026-11-25 00:40:32'),
(14, 1, 'Model Name', '15 FR100', 1, '2026-11-25 00:40:32'),
(15, 1, 'Normal Diameter', '12 inch', 2, '2026-11-25 00:40:32'),
(16, 1, 'Nominal Impedance', '8 ohms', 3, '2026-11-25 00:40:32'),
(17, 1, 'Nominal Power Handling', '150W', 4, '2026-11-25 00:40:32'),
(18, 1, 'Sensitivity (1 w / 1 m)', '96db', 5, '2026-11-25 00:40:32'),
(19, 1, 'Basket', 'Steel', 6, '2026-11-25 00:40:32'),
(20, 7, 'Model Name', '15 FR300', 1, '2026-11-25 00:40:32'),
(21, 7, 'Normal Diameter', '15 inch', 2, '2026-11-25 00:40:32'),
(22, 7, 'Nominal Impedance', '8 ohms', 3, '2026-11-25 00:40:32'),
(23, 7, 'Nominal Power Handling', '250W', 4, '2026-11-25 00:40:32'),
(24, 7, 'Continuous Power Handling', '500W', 5, '2026-11-25 00:40:32'),
(25, 7, 'Sensitivity (1 w / 1 m)', '98db', 6, '2026-11-25 00:40:32'),
(26, 7, 'Basket', 'Steel', 7, '2026-11-25 00:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_group`) VALUES
(1, 'site_name', 'ONS SOUND', 'general'),
(2, 'site_tagline', 'Hear every word with precision sound', 'general'),
(3, 'contact_address', 'Ajmer, Rajasthan, India', 'contact'),
(4, 'contact_email', 'sharmatripti2106@gmail.com', 'contact'),
(5, 'contact_phone', '+91 7976824255', 'contact'),
(6, 'contact_hours', 'Our store has re-opened for shopping, exchange Every day 11am to 7pm', 'contact'),
(7, 'ticker_text', '★ Spring Clearance Event: Save Up to 70% ★ Spring Clearance Event: Save Up to 70% ★ Spring Clearance Event: Save Up to 70%', 'announcement'),
(8, 'home_ticker', '⚡ Crystal-Clear Sound That Resonates Beyond Boundaries ⚡ Bring Music to Life with Every Note ⚡ Technology Meets Acoustic', 'announcement'),
(9, 'social_facebook', 'https://facebook.com', 'social'),
(10, 'social_twitter', 'https://twitter.com', 'social'),
(11, 'social_instagram', 'https://instagram.com', 'social'),
(12, 'social_tiktok', 'https://tiktok.com', 'social'),
(13, 'social_pinterest', 'https://pinterest.com', 'social'),
(14, 'offer_coupon_code', 'OFF20%', 'promotions'),
(15, 'offer_discount_percent', '20', 'promotions');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `full_name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `full_name`, `email`, `password_hash`, `phone`, `status`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'ONS Sound Administrator', 'admin@onssound.com', '$2y$10$iOZsxH1VflsxMStxumBRKOhyPpptSdvXrzzf3y/TNop76Q7ZKG4OG', '+91 7976824255', 'active', NULL, '2026-11-25 00:40:32', '2026-11-25 09:59:55'),
(2, 'customer', 'Demo Customer', 'customer@onssound.com', '$2y$10$6akr51/H0EVz9BBMToOi3uK6NGb2e2FS05mK8GeogA3iNhpfDSXke', '+91 9876543210', 'active', NULL, '2026-11-25 00:40:32', '2026-11-25 00:40:32'),
(3, 'customer', 'Aryan', 'dwc.aryanshah@gmail.com', '$2y$10$fgbKOVqVd3U98/4/iZmPdu2UTNGgruWqT1DDL15AuIlVSDc9tBXsu', '', 'active', NULL, '2026-11-25 07:20:15', '2026-11-25 09:59:15');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_blog_published` (`is_published`,`published_at`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `session_id` (`session_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `contact_enquiries`
--
ALTER TABLE `contact_enquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `homepage_banners`
--
ALTER TABLE `homepage_banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homepage_sections`
--
ALTER TABLE `homepage_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_key` (`section_key`);

--
-- Indexes for table `navigation_menus`
--
ALTER TABLE `navigation_menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`order_status`),
  ADD KEY `idx_orders_created` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_reset_email` (`email`),
  ADD KEY `idx_reset_token` (`token`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_products_active` (`is_active`),
  ADD KEY `idx_products_featured` (`is_featured`),
  ADD KEY `idx_products_price` (`price`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prod_specs` (`product_id`,`spec_key`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_wishlist_user` (`user_id`),
  ADD KEY `idx_wishlist_session` (`session_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_enquiries`
--
ALTER TABLE `contact_enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `homepage_banners`
--
ALTER TABLE `homepage_banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `homepage_sections`
--
ALTER TABLE `homepage_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `navigation_menus`
--
ALTER TABLE `navigation_menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_specifications`
--
ALTER TABLE `product_specifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD CONSTRAINT `product_specifications_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
Displaying onssound_db (1).sql.
