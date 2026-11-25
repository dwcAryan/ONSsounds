import urllib.request
import re

print("Running blog and hover verification...")

# 1. Check Homepage for no slider buttons
with urllib.request.urlopen("http://localhost/ONSsounds/index.php", timeout=10) as resp:
    home_html = resp.read().decode('utf-8')

if "carousel-control-prev" not in home_html and "carousel-control-next" not in home_html:
    print("[PASS] Hero slider has no arrow buttons (carousel-control-prev/next removed)")
else:
    print("[FAIL] Hero slider still contains arrow buttons!")

if "hero-indicators" in home_html:
    print("[PASS] Hero slider contains clean indicator dots")
else:
    print("[FAIL] Hero slider indicator dots missing!")

# 2. Check Blog Page
with urllib.request.urlopen("http://localhost/ONSsounds/blog.php", timeout=10) as resp:
    blog_html = resp.read().decode('utf-8')

blog_checks = [
    ("blog-card", "Blog card structure"),
    ("blog-img-box", "Blog image container"),
    ("assets/images/blog/blog-1.png", "Post 1 wooden speaker image"),
    ("assets/images/blog/blog-2.png", "Post 2 pink speaker image"),
    ("assets/images/blog/blog-3.png", "Post 3 green speaker image"),
    ("UNCATEGORIZED", "Uncategorized category tag on post 1"),
    ("TECH", "Tech category tag on post 2/3"),
    ("blog-read-more", "Read more link"),
]

for check, label in blog_checks:
    if check in blog_html:
        print(f"[PASS] Blog page: {label}")
    else:
        print(f"[FAIL] Blog page: {label} missing!")

# 3. Check Hover Effects in CSS
with open("assets/css/style.css", encoding="utf-8") as f:
    css = f.read()

hover_checks = [
    (".site-nav .nav-link::after", "Nav link expanding underline on hover"),
    (".header-action-btn:hover", "Header action button lift & scale"),
    (".btn-ons-black:hover", "Pill CTA button lift & arrow translation"),
    (".category-card:hover .category-img-wrapper", "Category card border & shadow hover"),
    (".category-card:hover .category-img-wrapper img", "Category image scale on hover"),
    (".product-card:hover .product-img-box", "Product card border & shadow hover"),
    (".product-card:hover .product-img-box img", "Product image scale on hover"),
    (".product-card:hover .product-card-title a", "Product title red color on hover"),
    (".btn-overlay-action:hover", "Product overlay action button hover state"),
    (".product-btn-add:hover", "Product add to cart button hover state"),
    (".discovery-card:hover", "Discovery card lift & shadow on hover"),
    (".discovery-card:hover .discovery-btn", "Discovery circular button rotate & zoom"),
    (".blog-card:hover .blog-img-box img", "Blog image zoom on hover"),
    (".blog-card:hover .blog-card-title a", "Blog card title hover transition"),
    (".blog-read-more:hover i", "Blog read more arrow movement on hover"),
    (".about-quality-box .p-3:hover .about-icon-circle", "About pillar icon circle hover inversion"),
    (".footer-links a:hover", "Footer link horizontal shift on hover"),
    (".social-circle-btn:hover", "Social circle button scale & color inversion"),
    (".scroll-top-btn:hover", "Scroll to top button hover state"),
]

for check, label in hover_checks:
    if check in css:
        print(f"[PASS] Hover effect: {label}")
    else:
        print(f"[FAIL] Hover effect: {label} missing in CSS!")

print("\nALL BLOG, SLIDER, AND HOVER VERIFICATIONS COMPLETED!")
