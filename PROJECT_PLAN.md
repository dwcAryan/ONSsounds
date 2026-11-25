# ONS SOUND - MASTER PROJECT PLAN

## 1. Project Overview
ONS SOUND is a full-featured, database-driven audio product e-commerce web application built strictly with **Core PHP 8.2+**, **MySQL**, **PDO**, **HTML5**, **CSS3**, **Vanilla JavaScript**, and **Bootstrap 5** (used selectively for grid layout and utility classes).

The visual source of truth is the authentic screenshot collection located in `ref_img/`. The visual identity, layout hierarchy, component proportions, typography, and color scheme of the original website are preserved with precision, alongside modern mobile responsiveness and secure backend engineering.

---

## 2. Recommended Core PHP Directory Structure

```text
ONSsounds/
│
├── config/
│   ├── config.php                # App constants, session hardening, base URL auto-detection
│   └── database.php              # Singleton PDO database wrapper with prepared statements
│
├── includes/
│   ├── helpers.php               # Sanitization, CSRF, flash alerts, formatters, auth guards
│   ├── header.php                # Reusable header with navigation, search modal, badge counts
│   ├── footer.php                # 4-column footer, social icons, newsletter form, payment badges
│   ├── ticker.php                # Dynamic marquee ticker (lightning/star announcement bar)
│   ├── product-card.php          # Reusable product card matching original hover & badges
│   └── mailer.php                # PHPMailer SMTP email notification helper
│
├── assets/
│   ├── css/
│   │   ├── style.css             # Authentic custom styles matching reference screenshots
│   │   └── admin.css             # Admin dashboard styling
│   ├── js/
│   │   ├── main.js               # Vanilla JavaScript for AJAX cart, wishlist, UI interactions
│   │   └── admin.js              # Admin interactions, image upload previews, order status updates
│   └── images/
│       ├── products/             # Sliced authentic product images (Speaker 1, 2, 3, 4, 5, 10, etc.)
│       ├── categories/           # Authentic category thumbnails (Tweeters, Woofers, Amplifiers, etc.)
│       ├── banners/              # Hero banners & promotional graphics
│       ├── blog/                 # Blog post featured images
│       ├── about/                # Story & vision photographs
│       └── home/                 # Studio monitors, towers, contact backgrounds
│
├── database/
│   ├── schema.sql                # Complete MySQL relational database DDL with indexes & FKs
│   └── seed.sql                  # Comprehensive seed data matching reference screenshots
│
├── admin/
│   ├── index.php                 # Admin dashboard overview & analytics
│   ├── login.php                 # Admin secure login
│   ├── products.php              # Product catalog & stock management
│   ├── product-add.php           # Add new product with specs & images
│   ├── product-edit.php          # Edit product details & specs
│   ├── categories.php            # Category management
│   ├── orders.php                # Customer order bookings list & status filters
│   ├── order-details.php         # Order item breakdown & customer info
│   ├── customers.php             # Customer management
│   ├── blogs.php                 # Blog article management
│   ├── inquiries.php             # Contact form submissions manager
│   ├── settings.php              # Homepage content & store settings CMS
│   └── includes/
│       ├── admin-header.php      # Admin navigation & sidebar
│       └── admin-footer.php      # Admin scripts & footer
│
├── index.php                     # Homepage matching homepage.png
├── products.php                  # Product listing with filters & sort matching products.png
├── product-details.php           # Product page with tabs & specs matching product-details.png
├── blog.php                      # Blog listing matching blog.png
├── blog-details.php              # Single article reader
├── about.php                     # About Us page matching about-us.png
├── contact.php                   # Contact Us page with map matching contact-us.png
├── privacy-policy.php            # Privacy policy
├── faq.php                       # Frequently asked questions
│
├── cart.php                      # Shopping cart page
├── cart_action.php               # AJAX cart handler (add, update qty, remove, clear)
├── checkout.php                  # Order booking checkout (no online payment gateway)
├── order-success.php             # Order confirmation page
│
├── wishlist.php                  # Customer wishlist page
├── wishlist_action.php           # AJAX wishlist toggle handler
│
├── login.php                     # Customer sign-in
├── register.php                  # Customer sign-up
├── logout.php                    # Secure session termination
├── profile.php                   # Customer profile & password change
├── addresses.php                 # Customer shipping address management
├── orders.php                    # Customer order booking history
│
├── newsletter_subscribe.php      # AJAX newsletter subscriber endpoint
├── contact_process.php           # Contact form submission handler
└── ref_img/                      # Ground-truth reference screenshots (Read-only)
```

---

## 3. Implementation Phases & Roadmap

| Phase | Scope | Deliverables |
|---|---|---|
| **Phase 1** | Analysis & Planning | `PROJECT_PLAN.md`, `DESIGN_REFERENCE.md`, `PAGE_STRUCTURE.md`, `DATABASE_PLAN.md`, asset inventory. |
| **Phase 2** | Database Setup & Seeds | Execution of `database/schema.sql` and `database/seed.sql` on MySQL with 15 tables and verified seed records. |
| **Phase 3** | Core Framework & Master Layout | `config/config.php`, `config/database.php`, `includes/helpers.php`, `includes/header.php`, `includes/footer.php`, `includes/ticker.php`, `assets/css/style.css`, `assets/js/main.js`. |
| **Phase 4** | Public Frontend Catalog | `index.php`, `products.php`, `product-details.php`, `blog.php`, `blog-details.php`, `about.php`, `contact.php`, static pages. |
| **Phase 5** | Customer Auth & Booking Flow | Customer registration, login, profile, addresses, cart (`cart.php`, `cart_action.php`), wishlist (`wishlist.php`), checkout booking (`checkout.php`), order history. |
| **Phase 6** | Admin Management Dashboard | Secure admin auth, product CRUD with audio specifications, inventory tracking, order status updater, customer management, blog CMS, inquiries manager, site settings. |
| **Phase 7** | Quality Assurance & Responsive Verification | Cross-browser responsiveness checks, CSRF & SQL injection testing, input validation audits, and final verification. |

---

## 4. Dependencies & Third-Party Assets

### Embedded & Active
- **Bootstrap 5.3.3 (CSS & JS Bundle)**: Lightweight responsive grid system, offcanvas mobile navigation, and modal dialogs.
- **Bootstrap Icons 1.11.3**: Clean vector icons for search, cart, wishlist, star ratings, and contact info.
- **Google Fonts (Plus Jakarta Sans)**: Primary typography matching the modern sans-serif aesthetic of the original design.
- **PHPMailer (v6.x)**: For transactional email notifications (order booking confirmations and contact inquiries).

### Missing Assets & Sourcing Strategy
1. **Product Images**: Sliced directly from `ref_img/products.png` and `ref_img/product-details.png` using non-destructive coordinate cropping to preserve the authentic dual-view speaker imagery (front cone + back magnet).
2. **Category Thumbnails**: Sliced from `ref_img/homepage.png` ("At ONS Sound" 6-grid section).
3. **Banner Visuals**: Extracted directly from `ref_img/homepage.png` and `ref_img/about-us.png`.
4. **Google Maps Embed**: Generated standard interactive iframe embed targeted to Ajmer, Rajasthan, India matching `ref_img/contact-us.png`.
