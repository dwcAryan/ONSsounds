# ONS SOUND — WordPress-Style Admin Panel User Guide

The ONS SOUND Administration Panel (`/admin`) provides a familiar, WordPress `wp-admin` inspired dashboard to manage your catalog, orders, customers, content, marketing, and store settings without requiring external CMS frameworks.

---

## 🧭 Navigation Layout

The admin interface consists of:
1. **Top Admin Toolbar (`#wpadminbar`)**: Quick links to view the public site, live pending order and inquiry notification counters, customer review alerts, user profile greeting, and secure log out. On mobile devices, a responsive toggle button activates the navigation drawer.
2. **Left Sidebar Menu (`#adminmenu`)**: Collapsible navigation with expandable submenus, active item highlighting, and badge counters.
3. **Main Content Canvas (`#wpcontent`)**: Page headings, action buttons ("Add New Product", "Back to Orders"), filter tabs (`All`, `Pending`, `Processing`), search forms, data tables, and two-column WordPress postbox containers.

---

## 🗂️ Admin Features & Workflows

### 1. Dashboard (`admin/index.php` & `admin/activity.php`)
- **Metrics Overview**: Real-time sales calculations, total orders, pending orders requiring dispatch, total registered customers, total catalog products, and low-stock alerts.
- **Recent Orders Table**: Quick view of recent customer bookings with direct links to view invoices.
- **Activity Log (`admin/activity.php`)**: Full chronological audit trail of admin actions (status changes, product edits, coupon updates) with timestamps and user identifiers.

---

### 2. Product Management (`admin/products.php` & `admin/product-edit.php`)
- **Product List**: View all products with thumbnails, SKU, category, price, stock status (`In Stock` vs `Out of Stock`), and published status. Supports search and pagination.
- **Add / Edit Product**:
  - **General**: Product Title, SEO Slug (auto-generated or custom), Short Description, and Full Description.
  - **Pricing & Inventory**: Regular Price, Sale Price, SKU, Stock Quantity, and Stock Status.
  - **Categories**: Select primary and secondary speaker / driver categories.
  - **Client-Side Image Auto-Compression**:
    - **Before Upload**: When an administrator selects a photo (Featured Image, Hover Image, or Gallery uploads), the browser automatically resizes and compresses the image client-side via HTML5 Canvas before uploading it to the hosting server.
    - **Bandwidth & Storage Saver**: Downscales oversized camera/phone photos (max 1600px width/height) and optimizes them (82% JPEG quality; preserves full PNG alpha transparency for cut-out speakers).
    - **Live Savings Display**: Displays real-time metrics under the input (e.g. *Compressed before upload: 3.5 MB → 280 KB (92% saved)*) and updates the preview immediately.
    - **Server-Side GD Fallback**: Automatically validates, optimizes, and stores images in `assets/images/products/` with transparent alpha channel preservation.
  - **Images**: Primary featured image, hover/alternate image, and multiple gallery images (supports direct file upload with auto-compression or custom paths).
  - **Specifications Table**: Add custom key-value pairs (e.g. *Frequency Response: 45Hz – 5kHz*, *Impedance: 8 Ohms*, *Sensitivity: 98dB*).
  - **Featured Toggle**: Checkbox to display on the homepage showcase.

---

### 3. Categories Management (`admin/categories.php`)
- Create, edit, and delete product categories (e.g. Compression Drivers, Subwoofers, Tweeters, Complete Speakers).
- Set category slug, description, and thumbnail image.
- View live associated product count per category.

---

### 4. Order Management (`admin/orders.php` & `admin/order-view.php`)
- **Orders Directory**: Filter by status: `Pending`, `Processing`, `Shipped`, `Delivered`, `Cancelled`.
- **Order Details (`admin/order-view.php`)**:
  - Customer contact details (Name, Email, Phone, Delivery Address).
  - Itemized order snapshot showing product names, prices at purchase, and quantities.
  - Subtotal, shipping fee, discount code applied, and total amount.
  - **Status Update Form**: Change status and optionally include administrative notes. Every change is recorded in `order_status_history`.
  - **Printable Invoice**: Direct button to launch customer-facing receipt / invoice for packing and dispatch.

---

### 5. Customer Management (`admin/customers.php` & `admin/customer-view.php`)
- Directory of all registered customers with registration date, order count, and lifetime total spent.
- Detailed customer view inspecting shipping addresses, phone numbers, and past order history.
- Status management (`active`, `inactive`, `banned`).

---

### 6. Blog & Content Management (`admin/posts.php` & `admin/post-edit.php`)
- Create and edit technical sound articles, acoustic design guides, and company news.
- Manage publication status (`Draft` vs `Published`).
- Categories management via `admin/post-categories.php`.

---

### 7. Product Reviews Moderation (`admin/reviews.php`)
- Moderate customer reviews submitted via the storefront.
- **Verified Identity**: Reviews are submitted strictly by authenticated logged-in customers. Reviewer name and email are automatically linked to verified customer accounts, eliminating spam and fake reviews.
- **Rating Scale**: Customers submit 1–5 integer star ratings with optional comments.
- Filter reviews by `Pending`, `Approved`, or `Rejected`.
- Actions: **Approve**, **Reject**, or **Delete**.
- **Automatic Sync**: Approving or rejecting a review automatically recalculates the parent product's average rating in MySQL (rendered dynamically on the storefront with split-gradient half-stars for float ratings).

---

### 8. Contact Enquiries (`admin/enquiries.php`)
- Read customer inquiries sent via the Contact page.
- Actions: **Mark as Read**, **Mark as Unread**, **Archive**, or **Delete**.
- Modal / drawer view for reading full customer messages and quick email replies.

---

### 9. Appearance & Customization
- **Homepage Sections (`admin/appearance-home.php`)**: Edit headlines, sub-headlines, and visibility toggles for hero sections, featured products, acoustic advantages, and promotional blocks without editing code.
- **Homepage Banners (`admin/appearance-banners.php`)**: Manage hero carousel slides, titles, button labels, links, and background imagery.
- **Navigation Menus (`admin/menus.php`)**: Add, reorder, or update links in the primary navigation bar.
- **Footer Settings (`admin/appearance-footer.php`)**: Update company branding text, store operating hours, contact details, copyright notice, and social media links.

---

### 10. Marketing & Discount Management
- **Discount Coupons (`admin/coupons.php`)**: Create coupon codes with percentage or fixed discounts, minimum cart spend, maximum discount cap, expiration date, and usage limits.
- **Reward Popup (`admin/reward-popup.php`)**: Configure the exit-intent reward popup heading, discount code, and recommended showcase products.
- **Subscribers (`admin/subscribers.php`)**: View and export newsletter subscriber emails.

---

### 11. System & Store Settings
- **General Settings (`admin/settings.php`)**: Store title, tagline, currency symbol (`₹`, `$`, `€`), timezone, and date format.
- **Store Settings (`admin/settings-store.php`)**: Legal registered entity name, physical showroom address, support phone, low-stock threshold, and order prefix.
- **Email & SMTP Settings (`admin/settings-email.php`)**: Host, port, username, password, encryption (`tls`/`ssl`), and sender identity. Includes a **Live Test Email Dispatcher** to immediately verify server connectivity.
- **Admin Users (`admin/users.php`)**: Create and manage administrative accounts with secure passwords.

---

## 📱 Mobile & Tablet View

The administration panel is fully responsive:
- On screens `<= 991px`, the sidebar turns into an off-canvas slide-out drawer toggled via the hamburger icon in the top toolbar.
- Tapping anywhere on the dark backdrop overlay or pressing `Escape` closes the drawer.
- Data tables automatically enable horizontal touch scrolling to preserve readable layouts on compact touchscreens.
