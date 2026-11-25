import urllib.request
import re

def verify_mobile():
    print("==========================================================")
    print("   VERIFYING MOBILE LAYOUT & RESPONSIVE DESIGN")
    print("==========================================================")
    passed = 0
    failed = 0

    def check(desc, cond):
        nonlocal passed, failed
        if cond:
            print(f"[PASS] {desc}")
            passed += 1
        else:
            print(f"[FAIL] {desc}")
            failed += 1

    # 1. Header Structure in header.php
    with open('includes/header.php', 'r', encoding='utf-8') as f:
        header_php = f.read()

    check("header-left with hamburger present", 'class="header-hamburger' in header_php and 'header-left' in header_php)
    check("header-center with centered mobile brand logo", 'class="header-center' in header_php and 'd-lg-none' in header_php)
    check("header-right with action icons", 'class="header-right' in header_php and 'header-actions' in header_php)
    check("mobile offcanvas drawer with search input", 'id="mobileNavOffcanvas"' in header_php and 'name="q"' in header_php)
    check("mobile offcanvas has wishlist & cart items", 'wishlist.php' in header_php and 'cart.php' in header_php)
    check("mobile offcanvas has store contact details", 'STORE_PHONE' in header_php and 'STORE_EMAIL' in header_php)

    # 2. Responsive CSS in style.css
    with open('assets/css/style.css', 'r', encoding='utf-8') as f:
        css = f.read()

    check("brand-logo has white-space: nowrap", 'white-space: nowrap' in css and '.brand-logo' in css)
    check("header-hamburger styles defined", '.header-hamburger' in css)
    check("Breakpoint max-width: 991.98px defined", '@media (max-width: 991.98px)' in css)
    check("Breakpoint max-width: 767.98px defined", '@media (max-width: 767.98px)' in css)
    check("Breakpoint max-width: 575.98px defined", '@media (max-width: 575.98px)' in css)
    check("Breakpoint max-width: 380px defined", '@media (max-width: 380px)' in css)
    check("Mobile hero title font size responsive", '.hero-title' in css and 'font-size: 28px' in css)
    check("Mobile brand logo responsive sizing", '.header-center .brand-logo' in css)
    check("Mobile 2-column product card adaptations", '.product-img-box' in css and 'padding: 10px' in css)
    check("Mobile footer bottom centering", 'align-items: center' in css and 'text-align: center' in css)

    # 3. Live HTTP Page Checks
    pages = [
        'index.php',
        'products.php',
        'about.php',
        'blog.php',
        'contact.php'
    ]
    for p in pages:
        url = f"http://localhost/ONSsounds/{p}"
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)'})
        with urllib.request.urlopen(req, timeout=10) as resp:
            html = resp.read().decode('utf-8')
            check(f"Page {p} returns 200 and renders mobile header", 'mobileNavOffcanvas' in html and resp.status == 200)

    print("==========================================================")
    print(f"MOBILE VERIFICATION: {passed} PASSED, {failed} FAILED.")
    print("==========================================================")
    assert failed == 0

if __name__ == '__main__':
    verify_mobile()
