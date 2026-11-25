# ONS SOUND - DATABASE SCHEMA & DATA ARCHITECTURE

This document defines the relational database architecture for the ONS SOUND application.

The database name is `onssound_db`, using character set `utf8mb4` and collation `utf8mb4_unicode_ci` to support multilingual characters and emojis.

---

## 1. Entity Relationship Overview

```text
[users] 1 ──── ∞ [user_addresses]
  │
  ├─────── 1 ──── ∞ [orders] 1 ──── ∞ [order_items]
  │                     │
  │                     └───── ∞ ──── 1 [products]
  ├─────── 1 ──── ∞ [wishlists] ─────── ∞ [products]
  │                                           │
  ├─────── 1 ──── ∞ [cart_items] ────── ∞ ────┤
  │                                           │
  └─────── 1 ──── ∞ [product_reviews] ─ ∞ ────┤
                                              │
[categories] 1 ───────────────────────── ∞ ───┤
                                              │
                                              └── 1 ──── ∞ [product_images]

[blogs] (Independent CMS entity)
[testimonials] (Independent review entity)
[contact_inquiries] (Customer contact submissions)
[newsletter_subscribers] (Email newsletter list)
[site_settings] (Key-value store for store configurations)
```

---

## 2. Table Definitions & Field DDL

### 2.1 `users`
Stores customer accounts and administrator credentials.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `role`: `ENUM('customer', 'admin') NOT NULL DEFAULT 'customer'`
- `full_name`: `VARCHAR(150) NOT NULL`
- `email`: `VARCHAR(191) NOT NULL UNIQUE`
- `password_hash`: `VARCHAR(255) NOT NULL` (via `password_hash()` BCRYPT)
- `phone`: `VARCHAR(30) NULL`
- `status`: `ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active'`
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`
- `updated_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`
- *Indexes*: `(role)`, `(status)`

### 2.2 `user_addresses`
Stores multiple shipping and billing addresses per customer.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `user_id`: `INT NOT NULL` (FK `users.id` ON DELETE CASCADE)
- `address_title`: `VARCHAR(50) DEFAULT 'Home'`
- `recipient_name`: `VARCHAR(150) NOT NULL`
- `phone`: `VARCHAR(30) NOT NULL`
- `address_line1`: `VARCHAR(255) NOT NULL`
- `address_line2`: `VARCHAR(255) NULL`
- `city`: `VARCHAR(100) NOT NULL`
- `state`: `VARCHAR(100) NOT NULL`
- `postal_code`: `VARCHAR(20) NOT NULL`
- `country`: `VARCHAR(100) DEFAULT 'India'`
- `is_default`: `TINYINT(1) DEFAULT 0`
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`

### 2.3 `categories`
Product category groupings shown on homepage and filters.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `name`: `VARCHAR(100) NOT NULL`
- `slug`: `VARCHAR(120) NOT NULL UNIQUE`
- `description`: `TEXT NULL`
- `image`: `VARCHAR(255) NULL`
- `display_order`: `INT DEFAULT 0`
- `is_active`: `TINYINT(1) DEFAULT 1`
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`

### 2.4 `products`
Comprehensive loudspeaker product catalog including complete acoustic specifications matching original website tables.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `category_id`: `INT NULL` (FK `categories.id` ON DELETE SET NULL)
- `name`: `VARCHAR(191) NOT NULL` (e.g., "Speaker 10")
- `slug`: `VARCHAR(200) NOT NULL UNIQUE`
- `sku`: `VARCHAR(100) UNIQUE NULL`
- `model_name`: `VARCHAR(100) NULL` (e.g., "15 FR200")
- `price`: `DECIMAL(10,2) NOT NULL DEFAULT 0.00`
- `compare_at_price`: `DECIMAL(10,2) NULL`
- `stock_quantity`: `INT NOT NULL DEFAULT 10`
- `stock_status`: `ENUM('in_stock', 'out_of_stock', 'preorder') DEFAULT 'in_stock'`
- `rating`: `DECIMAL(3,2) DEFAULT 5.00`
- `reviews_count`: `INT DEFAULT 0`
- `is_featured`: `TINYINT(1) DEFAULT 0`
- `is_active`: `TINYINT(1) DEFAULT 1`
- `main_image`: `VARCHAR(255) NOT NULL`
- `hover_image`: `VARCHAR(255) NULL`
- `short_description`: `TEXT NULL`
- `full_description`: `LONGTEXT NULL`
- *Technical Audio Specifications*:
  - `normal_diameter`: `VARCHAR(100) NULL` (e.g., "15 inch")
  - `nominal_impedance`: `VARCHAR(100) NULL` (e.g., "8 ohms")
  - `dc_resistance`: `VARCHAR(100) NULL` (e.g., "6.3 ohms")
  - `nominal_power_handling`: `VARCHAR(100) NULL` (e.g., "200W")
  - `continuous_power_handling`: `VARCHAR(100) NULL` (e.g., "400W")
  - `sensitivity`: `VARCHAR(100) NULL` (e.g., "98db")
  - `frequency_range`: `VARCHAR(100) NULL` (e.g., "55-5000Hz")
  - `voice_coil_diameter`: `VARCHAR(100) NULL` (e.g., "2in (50.5 mm)")
  - `voice_coil_material`: `VARCHAR(100) NULL` (e.g., "Ccaw")
  - `former_material`: `VARCHAR(100) NULL` (e.g., "Glass Fiber")
  - `magnet`: `VARCHAR(100) NULL` (e.g., "155 multiply 120 mm")
  - `basket`: `VARCHAR(100) NULL` (e.g., "Steel")
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`
- `updated_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`
- *Indexes*: `(is_active)`, `(is_featured)`, `(price)`

### 2.5 `product_images`
Auxiliary gallery images for single product pages.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `product_id`: `INT NOT NULL` (FK `products.id` ON DELETE CASCADE)
- `image_path`: `VARCHAR(255) NOT NULL`
- `display_order`: `INT DEFAULT 0`
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`

### 2.6 `product_reviews`
Customer product reviews with approval workflow.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `product_id`: `INT NOT NULL` (FK `products.id` ON DELETE CASCADE)
- `user_id`: `INT NULL` (FK `users.id` ON DELETE SET NULL)
- `reviewer_name`: `VARCHAR(150) NOT NULL`
- `reviewer_email`: `VARCHAR(191) NULL`
- `rating`: `TINYINT NOT NULL DEFAULT 5`
- `review_text`: `TEXT NOT NULL`
- `status`: `ENUM('pending', 'approved', 'rejected') DEFAULT 'approved'`
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`

### 2.7 `orders`
Order booking storage (Without online payment; pay on delivery / direct booking contact).
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `order_number`: `VARCHAR(64) NOT NULL UNIQUE` (e.g., "ONS-20261124-ABCD")
- `user_id`: `INT NULL` (FK `users.id` ON DELETE SET NULL)
- `customer_name`: `VARCHAR(150) NOT NULL`
- `customer_email`: `VARCHAR(191) NOT NULL`
- `customer_phone`: `VARCHAR(30) NOT NULL`
- `shipping_address`: `TEXT NOT NULL`
- `subtotal`: `DECIMAL(10,2) NOT NULL DEFAULT 0.00`
- `shipping_fee`: `DECIMAL(10,2) NOT NULL DEFAULT 0.00`
- `total_amount`: `DECIMAL(10,2) NOT NULL DEFAULT 0.00`
- `payment_method`: `VARCHAR(100) DEFAULT 'Order Booking (Pay on Delivery/Contact)'`
- `order_status`: `ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'`
- `customer_notes`: `TEXT NULL`
- `admin_notes`: `TEXT NULL`
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`
- `updated_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`
- *Indexes*: `(order_status)`, `(created_at)`

### 2.8 `order_items`
Snapshot records of products purchased in each order to preserve historical pricing.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `order_id`: `INT NOT NULL` (FK `orders.id` ON DELETE CASCADE)
- `product_id`: `INT NULL` (FK `products.id` ON DELETE SET NULL)
- `product_name`: `VARCHAR(191) NOT NULL`
- `product_sku`: `VARCHAR(100) NULL`
- `product_image`: `VARCHAR(255) NULL`
- `price`: `DECIMAL(10,2) NOT NULL`
- `quantity`: `INT NOT NULL DEFAULT 1`
- `subtotal`: `DECIMAL(10,2) NOT NULL`

### 2.9 `wishlists`
Customer and guest wishlist tracking.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `user_id`: `INT NULL` (FK `users.id` ON DELETE CASCADE)
- `session_id`: `VARCHAR(100) NULL`
- `product_id`: `INT NOT NULL` (FK `products.id` ON DELETE CASCADE)
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`

### 2.10 `cart_items`
Persistent shopping cart items for both registered users and guest sessions.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `user_id`: `INT NULL` (FK `users.id` ON DELETE CASCADE)
- `session_id`: `VARCHAR(100) NULL`
- `product_id`: `INT NOT NULL` (FK `products.id` ON DELETE CASCADE)
- `quantity`: `INT NOT NULL DEFAULT 1`
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`
- `updated_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`

### 2.11 `blogs`
Article publishing and technical guides.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `title`: `VARCHAR(255) NOT NULL`
- `slug`: `VARCHAR(255) NOT NULL UNIQUE`
- `category`: `VARCHAR(100) NOT NULL DEFAULT 'TECH'`
- `excerpt`: `TEXT NULL`
- `content`: `LONGTEXT NOT NULL`
- `image`: `VARCHAR(255) NOT NULL`
- `author`: `VARCHAR(100) DEFAULT 'ONS Sound Admin'`
- `is_published`: `TINYINT(1) DEFAULT 1`
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`
- `updated_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`

### 2.12 `testimonials`
Customer reviews showcased on the homepage.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `title`: `VARCHAR(150) NOT NULL`
- `review_text`: `TEXT NOT NULL`
- `client_name`: `VARCHAR(100) NOT NULL`
- `client_location`: `VARCHAR(100) NOT NULL`
- `rating`: `TINYINT DEFAULT 5`
- `display_order`: `INT DEFAULT 0`
- `is_active`: `TINYINT(1) DEFAULT 1`

### 2.13 `contact_inquiries`
Customer messages from contact page and homepage contact box.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `name`: `VARCHAR(150) NOT NULL`
- `email`: `VARCHAR(191) NOT NULL`
- `phone`: `VARCHAR(30) NULL`
- `message`: `TEXT NOT NULL`
- `status`: `ENUM('new', 'read', 'replied') DEFAULT 'new'`
- `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`

### 2.14 `newsletter_subscribers`
Email subscribers captured via the footer and popup prompts.
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `email`: `VARCHAR(191) NOT NULL UNIQUE`
- `subscribed_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP`

### 2.15 `site_settings`
Configurable store information (contact email, phone, physical address, announcement tickers, social media URLs).
- `id`: `INT AUTO_INCREMENT PRIMARY KEY`
- `setting_key`: `VARCHAR(100) NOT NULL UNIQUE`
- `setting_value`: `LONGTEXT NULL`
- `setting_group`: `VARCHAR(50) DEFAULT 'general'`

---

## 3. Transaction & Integrity Rules

1. **Order Booking Transactions**: Placing an order runs inside a strict PDO transaction (`$db->beginTransaction()` ... `$db->commit()`), ensuring the `orders` master record and each `order_items` entry succeed atomically.
2. **Historical Data Preservation**: `order_items` snapshots `product_name`, `product_sku`, and `price` at the moment of checkout, preventing future catalog edits from altering historic order records.
3. **Prepared Statements**: Every query utilizes parameterized PDO statements with emulation disabled (`PDO::ATTR_EMULATE_PREPARES => false`), preventing SQL injection attacks.
4. **Foreign Key Integrity**: Standardized cascading deletes for dependent relations (e.g. `order_items` deleted if parent `order` deleted) and nullification for catalog references (`category_id` set to null if category deleted).
