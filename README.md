# ONS SOUND — High-Performance E-Commerce & Audio Equipment Store

Welcome to **ONS SOUND**, a full-featured, production-ready e-commerce web application engineered with **Core PHP 8+**, **MySQL PDO**, and a modern, high-conversion frontend designed for high-fidelity audio equipment, professional drivers, subwoofers, and sound systems.

The platform includes a customer-facing storefront and a custom, custom-branded **WordPress `wp-admin` style administration dashboard** built without third-party frameworks.

---

## 🌟 Key Features

### 🛒 Customer Storefront
- **Dynamic Catalog**: Category browsing, brand filters, live search drawer with instant AJAX suggestions, price and stock status filtering.
- **Product Details & Advanced Gallery**: Interactive gallery featuring 5-second automatic sliding with cross-fade, hover-pause, cursor-centered 2x hover zoom, thumbnail switcher, and a fullscreen glassmorphic Lightbox modal with outer-pinned navigation and image counters. Technical acoustic specifications table and related recommendations.
- **Customer Star Reviews**: Exclusively available to logged-in customers; customer name and email are automatically linked from session credentials. Features an interactive clickable 1–5 star selection widget, optional review textarea, and high-precision float star ratings with split-gradient half-stars and solid gray empty stars matching custom design standards.
- **Cart & Wishlist Engine**: Exclusively accessible to registered, logged-in customers. Guests attempting to add items to their cart or wishlist are immediately prompted with a non-intrusive login modal popup.
- **Order Booking & Checkout**: Restricted to authenticated customer accounts to ensure verified identity and seamless tracking; multi-step checkout with address book, coupon codes, and unique order tracking codes.
- **Customer Account Portal**: Profile management, multiple shipping address book with default toggles, complete order history with real-time status tracking, and saved wishlist.
- **Security & Auth**: Dual-factor email OTP verification during registration, secure password hashing (`password_hash`), session hardening, and CSRF token protection on every form.
- **Marketing & Engagement**: Exit-intent reward discount popup, newsletter subscriptions, and responsive promotional carousels.

### 🎛️ WordPress-Style Admin Panel (`/admin`)
- **Familiar UI/UX**: Faithful reproduction of the classic WordPress `wp-admin` desktop and mobile off-canvas drawer navigation.
- **Products & Categories**: Full CRUD management with gallery image uploaders, technical acoustic specification key-value builders, and slug generation.
- **Order Management**: Real-time status lifecycle management (`Pending` → `Confirmed` → `Processing` → `Shipped` → `Delivered` → `Cancelled`), status history logging, and customer invoice printing.
- **Customer Management**: Customer accounts directory, address inspection, and lifetime spend analytics.
- **Blog & Content Editor**: WordPress-style blog post creation, excerpts, categories, and publication scheduling.
- **Customer Reviews & Enquiries**: Product review moderation (approve/reject/delete with automatic product rating resync) and contact message viewer.
- **Appearance & Homepage**: Manage hero carousel banners, homepage section titles, navigation menus, and footer branding without touching source code.
- **Marketing & Coupons**: Fixed and percentage discount coupons, reward popup configurations, and subscriber export.
- **System Settings & Live SMTP Tester**: Site settings, store policies, and SMTP configuration with an integrated real-time test email dispatcher.

---

## 📂 Project Architecture

```
ONSsounds/
├── admin/                  # WordPress-style administration dashboard
│   ├── assets/             # Admin CSS, JS, and vendor UI icons
│   ├── includes/           # Admin header, sidebar navigation, footer, auth guard
│   └── *.php               # Admin CRUD management screens
├── assets/                 # Frontend assets (Bootstrap, Bootstrap Icons, custom CSS/JS, images)
├── config/                 # Core configuration, database PDO connection, environment loader
├── database/               # Database schema, installer, and full onssound.sql export
├── includes/               # Storefront header, footer, navigation drawer, mailer, helpers
├── models/                 # Object-oriented domain models (User, Product, Cart, Order, Category)
├── vendor/                 # PHPMailer runtime libraries
├── *.php                   # Customer storefront pages & API endpoints
├── .env                    # Environment configuration
└── README.md               # Project documentation
```

---

## 🚀 Quick Setup & Documentation

For detailed installation, operational, and deployment guides, refer to:

- 📖 **[INSTALLATION.md](INSTALLATION.md)** — Step-by-step local setup (XAMPP / WAMP / LAMP).
- 🗄️ **[DATABASE_SETUP.md](DATABASE_SETUP.md)** — Database schema import, tables list, and automated installer.
- 🛠️ **[ADMIN_GUIDE.md](ADMIN_GUIDE.md)** — Comprehensive guide for administrators and store managers.
- 🌐 **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** — Production hosting instructions (cPanel, VPS, Nginx, Apache).
- 🧪 **[TEST_REPORT.md](TEST_REPORT.md)** — Comprehensive verification audit and test results.

---

## 🔒 Security Specifications

- **SQL Injection**: 100% prepared PDO statements with bound parameters across all database interactions.
- **Cross-Site Scripting (XSS)**: Strict escaping using `e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`) on all user-supplied data.
- **Cross-Site Request Forgery (CSRF)**: Cryptographically secure tokens validated on all state-changing `POST` requests.
- **Session Security**: `HttpOnly`, `SameSite=Lax`, and `UseOnlyCookies` enabled.
- **Password Security**: Passwords hashed with standard `PASSWORD_DEFAULT` (Bcrypt).
- **Access Control**: Role-based access control protecting all `/admin` routes.

---

## 📄 License & Credits

Developed for **ONS SOUND** — Professional Audio Drivers & Sound Systems.  
All rights reserved © 2026.
