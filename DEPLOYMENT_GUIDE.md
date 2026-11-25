# ONS SOUND — Production Deployment Guide

This guide gives you simple, step-by-step instructions to deploy the **ONS SOUND** website and database to your **Hostinger Shared Hosting** plan (or any cPanel / PHP host).

---

## 📁 1. Which Folder To Use

We have already created a clean, production-ready folder for you:
`E:\Xammp\htdocs\ONS-SOUND-DEPLOYMENT\`

This folder has all production files and **excludes** all development tools, tests, reference screenshots, and log files.

---

## 📋 2. What To Upload vs What NOT To Upload

### ✅ MUST UPLOAD (Everything inside `ONS-SOUND-DEPLOYMENT`):

| Type | Name | Purpose |
|---|---|---|
| **Folder** | `admin/` | Custom WordPress-style Admin Dashboard |
| **Folder** | `assets/` | CSS, JS, Fonts, Icons, cut-out Product PNGs, Banners |
| **Folder** | `config/` | Application & Database configuration loader |
| **Folder** | `database/` | Contains `onssound.sql` master database dump |
| **Folder** | `includes/` | Header, Footer, Navigation, Mailer, Security Helpers |
| **Folder** | `models/` | Product, Order, User, Cart, Review database models |
| **Folder** | `vendor/` | PHPMailer runtime libraries for email sending |
| **Config** | `.htaccess` | Clean URL routing, security, and `.env` protection |
| **Config** | `.env` | Database credentials and SMTP email settings |
| **PHP Files** | `index.php` | Storefront Homepage |
| **PHP Files** | `products.php`, `product-details.php` | Product catalog & details |
| **PHP Files** | `cart.php`, `cart_action.php` | Shopping cart |
| **PHP Files** | `checkout.php`, `order-confirmation.php` | Checkout & booking confirmation |
| **PHP Files** | `orders.php`, `order-details.php` | Customer order history |
| **PHP Files** | `wishlist.php`, `wishlist_action.php` | Customer wishlist |
| **PHP Files** | `login.php`, `register.php`, `logout.php`, `auth_action.php` | Customer accounts & OTP verification |
| **PHP Files** | `forgot-password.php`, `reset-password.php` | Password recovery |
| **PHP Files** | `contact.php`, `contact_process.php` | Contact form & messages |
| **PHP Files** | `blog.php`, `blog-details.php` | Acoustic news & blog posts |
| **PHP Files** | `about.php`, `addresses.php`, `profile.php` | Static & account pages |
| **PHP Files** | `newsletter_subscribe.php`, `review_submit.php`, `search_api.php` | Live search, reviews & newsletter |

---

### ❌ DO NOT UPLOAD (Leave these out — they are NOT needed in production):

| Name | Reason To Exclude |
|---|---|
| `tests/` | Automated test suite scripts (only for local testing) |
| `tools/` | Development helper scripts |
| `ref_img/` | Design reference screenshots |
| `.qodo/` | Local AI editor cache |
| `.git/` | Local Git revision history |
| `error.log` | Local PHP error log (production generates its own) |
| `*.md` | Markdown documentation files (`DATABASE_PLAN.md`, `PROJECT_PLAN.md`, etc.) |

---

## 🚀 3. Step-by-Step Hostinger Deployment (Plain English)

### Step 1: Zip the Deployment Folder
1. Go to `E:\Xammp\htdocs\ONS-SOUND-DEPLOYMENT` on your computer.
2. Select **all files and folders inside** this folder.
3. Right-click → **Compress to ZIP file** (name it `onssound.zip`).
   *(Make sure `index.php` is inside the root of the zip, not inside another subfolder).*

---

### Step 2: Upload to Hostinger File Manager
1. Log in to your **Hostinger hPanel** (`hpanel.hostinger.com`).
2. Go to **Websites** → click **Manage** next to your domain.
3. In the search bar or under **Files**, click **File Manager**.
4. Open the **`public_html`** folder.
5. Click the **Upload** button (arrow pointing up) at the top right.
6. Select your `onssound.zip` file and wait for the upload to reach 100%.
7. Right-click on `onssound.zip` inside `public_html` → click **Extract**.
8. Choose `public_html` as the destination.
9. Verify that `index.php`, `.env`, `admin/`, `assets/`, etc. are directly inside `public_html`.
10. Delete the `onssound.zip` file to save disk space.

> **Tip**: If `.env` or `.htaccess` does not show up, click the **Settings / Cog icon** in File Manager and turn on **"Show Hidden Files (dotfiles)"**.

---

### Step 3: Create MySQL Database on Hostinger
1. In your Hostinger hPanel, go to **Databases** → **MySQL Databases**.
2. Under **Create a New MySQL Database and User**, enter:
   - **MySQL Database Name**: e.g., `onssound` *(Hostinger will prefix it, e.g., `u123456789_onssound`)*
   - **MySQL Username**: e.g., `onsuser` *(Hostinger will prefix it, e.g., `u123456789_onsuser`)*
   - **Password**: Create a strong password (e.g., `MyStrongPass#2026!`).
3. Click **Create**.
4. **Copy these three details down**:
   - Exact Database Name (`u123456789_onssound`)
   - Exact Username (`u123456789_onsuser`)
   - Password you just entered

---

### Step 4: Import Seed Data (All Tables & Products)
1. In the same **MySQL Databases** screen in Hostinger, scroll down to **List of Current MySQL Databases And Users**.
2. Find your new database and click the button **Enter phpMyAdmin** next to it.
3. In phpMyAdmin, click the **Import** tab at the top.
4. Click **Choose File** (or Browse) and select `database/onssound.sql` from your computer (located at `E:\Xammp\htdocs\ONS-SOUND-DEPLOYMENT\database\onssound.sql`).
5. Scroll down and click **Import** (or **Go**).
6. You will see a green success banner: *"Import has been successfully finished, 24 tables imported."*
   *(This loads all 8 products with .png images, categories, blog posts, reviews, and admin accounts).*

---

### Step 5: Update `.env` with Your Hostinger Database Details
1. Go back to Hostinger **File Manager** inside `public_html`.
2. Right-click `.env` and click **Edit**.
3. Update the database lines with the details from Step 3:
   ```ini
   APP_NAME="ONS SOUND"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   APP_TIMEZONE=Asia/Kolkata

   # Database settings on Hostinger:
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=u123456789_onssound
   DB_USER=u123456789_onsuser
   DB_PASS=MyStrongPass#2026!
   DB_CHARSET=utf8mb4

   # SMTP Email settings:
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USER=theartspace44@gmail.com
   SMTP_PASS=dgqbgjjlmxiilrfu
   SMTP_ENCRYPTION=tls
   STORE_EMAIL=aryan.n.shah.04@gmail.com
   STORE_NAME="ONS SOUND"
   ```
4. Click **Save & Close**.

---

### Step 6: Test Your Live Website & Admin Panel
1. **Visit your website**: Open `https://yourdomain.com` in your browser.
   - Products, sliders, search, categories, and PNG images will load immediately from MySQL.
2. **Visit your Admin Panel**: Open `https://yourdomain.com/admin/login.php`.
   - **Email**: `admin@onssound.com`
   - **Password**: `Admin@12345`
3. In the admin panel, go to **Settings → Email / SMTP Settings** and send a test email to verify order booking emails work smoothly.

---

## 🔒 4. Security Notes
- The included `.htaccess` file blocks public access to `.env`, preventing anyone from reading your database password in a browser.
- Set folder permissions to `755` and file permissions to `644` in Hostinger File Manager (Hostinger sets these automatically by default).
