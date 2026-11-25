import urllib.request
import re
import os
from PIL import Image

def verify():
    print("==========================================================")
    print("   VERIFYING HOMEPAGE CONTACT FORM & SLIDER TOAST")
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

    # 1. Image Check
    bg_path = "assets/images/home/contact-box-bg.png"
    check("contact-box-bg.png exists", os.path.exists(bg_path))
    if os.path.exists(bg_path):
        im = Image.open(bg_path)
        check(f"contact-box-bg.png is valid image ({im.size})", im.size[0] >= 800)

    # 2. HTTP HTML Check
    url = "http://localhost/ONSsounds/index.php"
    req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
    with urllib.request.urlopen(req, timeout=10) as resp:
        html = resp.read().decode('utf-8')

    check("home-contact-box container present in index.php", 'class="home-contact-box"' in html)
    check("home-contact-narrative present", 'class="home-contact-narrative"' in html)
    check("Authentic narrative text present", 'At Onssound, we prioritize your satisfaction' in html)
    check("Label 'Your name' present", '<label for="home_contact_name" class="home-contact-label">Your name</label>' in html)
    check("Label 'Your email' present", '<label for="home_contact_email" class="home-contact-label">Your email</label>' in html)
    check("Label 'Contact Number' present", '<label for="home_contact_phone" class="home-contact-label">Contact Number</label>' in html)
    check("Label 'Your Message' present", '<label for="home_contact_message" class="home-contact-label">Your Message</label>' in html)
    check("Phone input placeholder present", 'placeholder="Enter your phone number"' in html)
    check("Submit button present with class home-contact-submit-btn", 'class="home-contact-submit-btn">Submit</button>' in html)

    # 3. CSS Check
    with open("assets/css/style.css", "r", encoding="utf-8") as f:
        css = f.read()

    check("@keyframes heroToastDown defined", "@keyframes heroToastDown" in css)
    check("Hero title starts at translateY(-45px)", "transform: translateY(-45px)" in css)
    check("Hero active title animation heroToastDown", ".carousel-item.active .hero-title" in css and "heroToastDown" in css)
    check("Hero active button animation heroToastDown", ".carousel-item.active .btn-ons-black" in css and "heroToastDown" in css)
    check("home-contact-box background-image points to contact-box-bg.png", "contact-box-bg.png" in css)
    check("home-contact-label styling defined", ".home-contact-label" in css)
    check("home-contact-submit-btn styling defined", ".home-contact-submit-btn" in css)
    check("home-contact-submit-btn hover effect defined", ".home-contact-submit-btn:hover" in css)

    # 4. JavaScript Check
    with open("assets/js/main.js", "r", encoding="utf-8") as f:
        js = f.read()

    check("slide.bs.carousel listener attached to heroCarousel", "slide.bs.carousel" in js and "heroCarousel" in js)
    check("reflow trigger for animation reset on slide change", "offsetHeight" in js)

    print("==========================================================")
    print(f"RESULTS: {passed} PASSED, {failed} FAILED.")
    print("==========================================================")
    assert failed == 0

if __name__ == "__main__":
    verify()
