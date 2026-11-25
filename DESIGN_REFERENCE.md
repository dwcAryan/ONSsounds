# ONS SOUND - DESIGN REFERENCE SYSTEM

This document specifies the exact visual identity, tokens, dimensions, and component behaviors observed in the original website screenshots (`ref_img/`).

---

## 1. Typography

The design uses a clean, modern neo-grotesque sans-serif typeface matching **Plus Jakarta Sans** (with fallback to Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto).

| Role | Font Size | Font Weight | Line Height | Letter Spacing | Color |
|---|---|---|---|---|---|
| **Site Logo** | 24px | 800 (Extra Bold) | 1.0 | 0.5px | `#000000` |
| **Hero Title** | 52px (Desktop) / 34px (Mobile) | 700 (Bold) | 1.15 | -0.5px | `#111111` |
| **Section Title** | 38px | 700 (Bold) | 1.2 | Normal | `#111111` |
| **Product Detail Title** | 32px | 700 (Bold) | 1.25 | Normal | `#111111` |
| **Navigation Links** | 15px | 500 (Medium) | 1.0 | Normal | `#333333` (Hover: `#000000`) |
| **Product Card Title** | 15px | 600 (Semi-Bold) | 1.3 | Normal | `#111111` |
| **Product Price** | 15px (Card) / 26px (Detail) | 700 (Bold) | 1.0 | Normal | `#111111` |
| **Body / Paragraphs** | 14px - 15px | 400 (Regular) | 1.6 - 1.7 | Normal | `#555555` / `#666666` |
| **Small / Meta Labels** | 11px - 13px | 600 - 700 | 1.4 | 0.5px (Uppercase) | `#666666` / `#888888` |
| **Footer Headings** | 16px | 700 (Bold) | 1.3 | Normal | `#FFFFFF` |

---

## 2. Color Palette

| Token Name | Hex Code | RGB | Usage |
|---|---|---|---|
| `ons-black` | `#000000` | `rgb(0, 0, 0)` | Footer background, ticker bar, primary buttons, logo |
| `ons-dark` | `#111111` | `rgb(17, 17, 17)` | Primary headings, product titles, dark accents |
| `ons-white` | `#FFFFFF` | `rgb(255, 255, 255)` | Header background, product card canvas, body background |
| `ons-gray-bg` | `#F6F6F6` | `rgb(246, 246, 246)` | Hero banner fallback, product image placeholder background |
| `ons-border` | `#E5E5E5` | `rgb(229, 229, 229)` | Card borders, dividers, specification table borders |
| `ons-text-muted` | `#666666` | `rgb(102, 102, 102)` | Subheadings, secondary copy, review dates |
| `ons-yellow-accent`| `#FFC107` | `rgb(255, 193, 7)` | Ticker lightning bolts `⚡`, rating star fills |
| `ons-badge-red` | `#E53935` | `rgb(229, 57, 53)` | Header counter badges (wishlist `0`, cart `0`) |

---

## 3. Layout Dimensions & Spacing

- **Reference Canvas**: 1440px viewport width.
- **Main Container**: Max-width `1320px` (or full width with `px-lg-5` matching original edge spacing).
- **Header Height**: ~76px fixed / sticky.
- **Section Vertical Spacing**:
  - Major sections: `60px` to `80px` top and bottom margins on desktop.
  - Minor element groupings: `24px` to `35px` margins.
- **Grid Gutters**: Standard 24px - 30px horizontal gaps between cards.
- **Border Radius**:
  - Primary Action Buttons: `30px` (Full Pill).
  - Overlay Action Icons: `50%` (Circle).
  - Cards & Containers: `6px` to `12px` (Subtle rounded corners).
  - Value Pill Banner: `40px` (Large pill capsule).

---

## 4. Header & Navigation Specifications

1. **Top Row Configuration**:
   - Left: `ONS SOUND` logo text in bold uppercase.
   - Center: Horizontal navigation links: `Home`, `Products`, `Blog`, `About Us`, `Contact Us`.
   - Right: Functional action buttons:
     - Search icon (`bi-search`) triggering clean modal overlay.
     - User account icon (`bi-person`) triggering authentication dropdown.
     - Wishlist icon (`bi-heart`) with top-right red badge counter.
     - Shopping bag icon (`bi-bag`) with top-right red badge counter.
2. **Behavior**:
   - White solid background `#FFFFFF` with ultra-light border bottom.
   - Sticky positioning (`position: sticky; top: 0; z-index: 1020`).
   - On viewports < 992px, desktop links collapse into a clean offcanvas drawer with hamburger button, preserving desktop look for larger screens.

---

## 5. Footer Specifications

1. **Background**: Pure black `#000000`, text `#FFFFFF`.
2. **4-Column Layout**:
   - **Column 1 (Brand & Contact)**: Large white `ONS SOUND` title, physical address in Ajmer, email `sharmatripti2106@gmail.com`, and phone number `+91 7976824255`.
   - **Column 2 (Help)**: Links to Privacy Policy, FAQ's, Menu item, `Get direction ↗` (outbound link to Google Maps), followed by circular outlined social buttons (Facebook, X, Instagram, TikTok, Pinterest).
   - **Column 3 (Useful Links)**: Links to Home, Products, Blog, About Us, Contact Us.
   - **Column 4 (Sign Up for Email)**: Copy text, white input field with placeholder "Your email", and full-width white button with black text reading "Submit".
3. **Subfooter**:
   - Copyright left: `ONSSOUND. All Rights Reserved`.
   - Center / Right: Distinct payment badge indicators (`VISA`, `PayPal`, `Mastercard`, `AMEX`, `Maestro`).
   - Far Right: Square black button with white chevron `^` for smooth scroll-to-top.

---

## 6. Product Card Specifications

1. **Card Container**:
   - Clean white card, borderless or ultra-fine border, `position: relative`.
2. **Product Image Area**:
   - 1:1 Aspect ratio square canvas.
   - Dual-speaker presentation: showcases the front speaker cone and the rear magnet/basket assembly.
3. **Rating Block**:
   - 5-star rating stars (`bi-star-fill` in golden yellow `#FFC107`, empty stars in `#E0E0E0`).
   - Review count in parentheses, e.g., `(3)`, `(2)`, `(0)`.
4. **Product Metadata**:
   - Title: e.g. "Speaker 10" (15px font, bold).
   - Price: formatted with Indian Rupee symbol `₹`, e.g. `₹17.99` or `₹2,000.00`.
5. **Interactive Hover State**:
   - Floating action pill and circle buttons slide up:
     - Circular wishlist toggle button (heart icon).
     - Circular quick-view modal trigger (eye icon).
     - Black pill button with white text: `ADD TO CART`.

---

## 7. Responsive Behavior Guidelines

- **Mobile Viewports (< 576px)**:
  - Header: Logo and action icons stay inline; navigation moves to offcanvas sidebar.
  - Category and product grids collapse to 1 or 2 columns with touch-friendly spacing.
  - Feature splits stack vertically with image centered above or below copy.
  - Footer columns stack into clean vertical sections.
- **Tablet Viewports (576px – 991px)**:
  - Product catalog adapts to 2 or 3 columns.
  - About Us and Feature splits retain comfortable margins.
- **Desktop Viewports (≥ 992px)**:
  - Strictly preserves original 1440px desktop proportions, horizontal alignment, and visual density.
