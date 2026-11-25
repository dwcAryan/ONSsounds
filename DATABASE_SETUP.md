# ONS SOUND — Database Setup & Architecture Guide

This document describes the MySQL database architecture, schema setup, automated migration tools, and table specifications for the **ONS SOUND** e-commerce application.

---

## 🗄️ Database Requirements

- **Engine**: MySQL 5.7+ / 8.0+ or MariaDB 10.4+
- **Default Database Name**: `onssound_db`
- **Character Set**: `utf8mb4`
- **Collation**: `utf8mb4_unicode_ci`
- **Supported Connection**: PHP Data Objects (PDO)

---

## 🚀 Setup Methods

### Method 1: Web / CLI Automated Installer (Recommended)

ONS SOUND includes an automated installer script located at `database/install.php`. It will verify or create the database, execute the table schemas, populate default master seed data, and generate a verification audit.

**Via Command Line (CLI):**
```bash
php database/install.php
```

To force a re-seed of default products, categories, settings, and administrator account:
```bash
php database/install.php --seed
```

**Via Web Browser:**
Navigate to:
```
http://localhost/ONSsounds/database/install.php
```

---

### Method 2: Direct SQL Import via phpMyAdmin or MySQL CLI

A complete, self-contained database backup containing all 24 tables with indexes and master seed data is provided at `database/onssound.sql`.

**Via MySQL CLI:**
```bash
# 1. Create the database if it doesn't already exist
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS onssound_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import the unified SQL dump
mysql -u root -p onssound_db < database/onssound.sql
```

**Via phpMyAdmin:**
1. Open `http://localhost/phpmyadmin/`.
2. Click **New** in the left sidebar and create a database named `onssound_db` with collation `utf8mb4_unicode_ci`.
3. Select `onssound_db`, navigate to the **Import** tab.
4. Browse and select `database/onssound.sql`.
5. Click **Import** at the bottom of the page.

---

## 📊 Complete Table Inventory (24 Tables)

| # | Table Name | Purpose / Contents |
|---|------------|-------------------|
| 1 | `users` | Customer and administrator credentials, roles (`customer`, `admin`), email verification, and status. |
| 2 | `addresses` | Customer shipping and billing address book with default selection flags. |
| 3 | `categories` | Product categories with parent hierarchy, slugs, descriptions, and banner images. |
| 4 | `products` | Core catalog: audio drivers, SKU, prices, stock levels, rating metrics, and featured toggles. |
| 5 | `product_images` | Multi-image gallery associations for each product. |
| 6 | `product_specifications` | Key-value technical specifications (Frequency Response, Impedance, Sensitivity, Voice Coil, etc.). |
| 7 | `wishlists` | Customer saved wishlist items. |
| 8 | `carts` | Customer and guest shopping cart session headers. |
| 9 | `cart_items` | Individual cart items with product IDs, quantities, and price snapshots. |
| 10 | `orders` | Customer order records: unique order numbers, total amounts, shipping info, and order statuses. |
| 11 | `order_items` | Immutable snapshot of ordered products, prices, and quantities per order. |
| 12 | `order_status_history` | Audit trail of order status changes, notes, and the admin user who initiated them. |
| 13 | `blog_categories` | Categories for acoustic articles and technical guides. |
| 14 | `blog_posts` | Acoustic engineering articles, technical sound guides, excerpts, and publication states. |
| 15 | `product_reviews` | Customer ratings (1–5 stars) and review text with moderation statuses (`pending`, `approved`, `rejected`). |
| 16 | `coupons` | Percentage and fixed discount codes with expiration dates, spend thresholds, and usage limits. |
| 17 | `contact_enquiries` | Customer messages submitted via the contact form with `new`, `read`, and `archived` states. |
| 18 | `newsletter_subscribers`| Email newsletter subscriptions with active statuses and timestamps. |
| 19 | `homepage_sections` | Manage homepage headings, subheadings, and visibility from the admin dashboard. |
| 20 | `homepage_banners` | Hero carousel slides, headings, background images, and call-to-action buttons. |
| 21 | `navigation_menus` | Primary navigation links, labels, and display order managed via the admin panel. |
| 22 | `site_settings` | Global key-value store (store contact, currency symbol, social links, legal entity name). |
| 23 | `admin_activity_logs` | Security log recording administrator actions and timestamps. |
| 24 | `password_resets` | Secure tokens and OTP codes for customer password resets and email verification. |

---

## 🔑 Default Administrator Account

Upon executing the seed data, the following administrative account is created:

- **Email**: `admin@onssound.com`
- **Password**: `Admin@12345`
- **Role**: `admin`
- **Admin Dashboard URL**: `http://localhost/ONSsounds/admin/`

---

## 🛡️ Database Security Guidelines

1. **Foreign Key Integrity**: Foreign keys cascade safely where appropriate (e.g. deleting a product cleans up gallery images and specifications without leaving orphan records).
2. **Prepared Queries**: All interactions are executed using PDO parameterized statements, shielding the application from SQL injection vulnerabilities.
3. **Optimized Indexes**: Indexed columns on `slug`, `sku`, `category_id`, `user_id`, `order_status`, and `is_active` ensure sub-second query performance even with large product catalogs.
