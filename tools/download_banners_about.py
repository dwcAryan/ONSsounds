import urllib.request
import os

headers = {'User-Agent': 'Mozilla/5.0'}

downloads = [
    # Hero Slider Images (100% original full res)
    ('https://onssound.com/wp-content/uploads/2024/06/WhatsApp-Image-2024-10-08-at-5.31.15-PM.jpeg', 'assets/images/banners/hero-slide-1.jpg'),
    ('https://onssound.com/wp-content/uploads/2024/11/Untitled-design-2024-11-13T153730.425.png', 'assets/images/banners/hero-slide-2.png'),
    
    # About Page Original Images (100% original full res)
    ('https://onssound.com/wp-content/uploads/2024/12/Untitled-design-2024-12-02T131146.407.png', 'assets/images/about/about-hero.png'),
    ('https://onssound.com/wp-content/uploads/2024/10/Untitled-design-82.png', 'assets/images/about/vision-speaker-1.png'),
    ('https://onssound.com/wp-content/uploads/2024/10/Untitled-design-85.png', 'assets/images/about/vision-speaker-2.png'),
    ('https://onssound.com/wp-content/uploads/2024/12/Untitled-490-x-536-px.png', 'assets/images/about/vision-lifestyle.png'),
]

for url, target in downloads:
    os.makedirs(os.path.dirname(target), exist_ok=True)
    try:
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=15) as resp:
            data = resp.read()
            with open(target, 'wb') as f:
                f.write(data)
            print(f"Downloaded {target} ({len(data)} bytes)")
    except Exception as e:
        print(f"Failed {url}: {e}")

print("Hero & About images downloaded successfully!")
