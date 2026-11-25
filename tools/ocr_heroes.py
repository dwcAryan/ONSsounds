import pytesseract
from PIL import Image

try:
    text_home = pytesseract.image_to_string(Image.open('tools/ref_hero_home.png'))
    print("--- OCR HOME HERO ---")
    print(text_home)
except Exception as e:
    print("OCR not available:", e)

try:
    text_about = pytesseract.image_to_string(Image.open('tools/ref_hero_about.png'))
    print("--- OCR ABOUT HERO ---")
    print(text_about)
except Exception as e:
    print("OCR not available:", e)
