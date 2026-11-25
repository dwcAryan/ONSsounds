import urllib.request
import re

def test_page(url, name, checks):
    print(f"Testing {name} ({url})...")
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=10) as resp:
            html = resp.read().decode('utf-8')
        for check, desc in checks:
            if check in html:
                print(f"  [PASS] {desc}")
            else:
                print(f"  [FAIL] {desc}")
                return False
        return True
    except Exception as e:
        print(f"  [ERROR] {e}")
        return False

# 1. Homepage Checks
home_checks = [
    ("hero-slider-section", "Full-screen hero slider section class"),
    ("id=\"heroCarousel\"", "Bootstrap hero carousel element"),
    ("hero-slide-1", "Hero Slide 1 container"),
    ("hero-slide-2", "Hero Slide 2 container"),
    ("Hear every word with", "Slide 1 headline"),
    ("Crafted for audiophiles,", "Slide 2 headline"),
    ("carousel-indicators hero-indicators", "Slider bottom indicator dots"),
    ("Shop collection", "Shop collection call-to-action button"),
    ("At ONS Sound", "Category section"),
    ("value-pill-banner", "Value statement pill banner"),
]

# 2. About Page Checks
about_checks = [
    ("about-hero", "About hero section"),
    ("Where innovation leads, sound follows", "About hero headline"),
    ("We are Onssound", "Narrative section heading"),
    ("vision-speaker-1.png", "High-res touch speaker image"),
    ("vision-lifestyle.png", "High-res lifestyle speaker image"),
    ("vision-speaker-2.png", "High-res wooden table speaker image"),
    ("Quality is our priority", "Quality pillars box"),
    ("about-quality-box", "Quality styled beige container"),
    ("Our customer’s reviews", "Customer review headline"),
    ("Shop Gram", "Shop Gram gallery"),
    ("gram-1.png", "Shop Gram image 1"),
    ("gram-6.png", "Shop Gram image 6"),
]

ok1 = test_page("http://localhost/ONSsounds/index.php", "Homepage", home_checks)
ok2 = test_page("http://localhost/ONSsounds/about.php", "About Page", about_checks)

if ok1 and ok2:
    print("\nALL VERIFICATIONS PASSED SUCCESSFULLY!")
else:
    print("\nSOME CHECKS FAILED.")
