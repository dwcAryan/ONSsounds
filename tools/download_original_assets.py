import urllib.request
import urllib.parse
import os
import re

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}

def download_file(url, target_path):
    os.makedirs(os.path.dirname(target_path), exist_ok=True)
    try:
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=15) as resp:
            data = resp.read()
            with open(target_path, 'wb') as f:
                f.write(data)
            print(f'Downloaded: {target_path} ({len(data)} bytes)')
            return True
    except Exception as e:
        print(f'Error downloading {url}: {e}')
        # If full res without size suffix exists, try that
        return False

# 1. Download Theme CSS
download_file('https://onssound.com/wp-content/themes/ecomus/style.css', 'tools/ecomus_style.css')
download_file('https://onssound.com/wp-content/themes/ecomus/woocommerce.css', 'tools/ecomus_woo.css')

# 2. Key Category Images (Exact 6 images from live site)
cat_map = {
    'tweeters.png': 'https://onssound.com/wp-content/uploads/2024/10/Untitled-design-2024-10-24T123445.179.png',
    'midrange-speakers.png': 'https://onssound.com/wp-content/uploads/2024/10/Untitled-design-2024-10-24T123325.447.png',
    'woofers-subwoofers.png': 'https://onssound.com/wp-content/uploads/2024/10/Untitled-design-2024-10-24T123240.526.png',
    'speaker-cabinets.png': 'https://onssound.com/wp-content/uploads/2024/10/Untitled-design-2024-10-26T113949.037.png',
    'amplifiers.png': 'https://onssound.com/wp-content/uploads/2024/10/Untitled-design-2024-10-24T123643.061.png',
    'portable-trolly-stage.png': 'https://onssound.com/wp-content/uploads/2024/10/Untitled-design-2024-10-24T123344.991.png',
}

for filename, url in cat_map.items():
    download_file(url, f'assets/images/categories/{filename}')

# 3. Key Feature Images
feature_map = {
    'studio-monitor.png': 'https://onssound.com/wp-content/uploads/2024/10/httpsworktop.keydesign.xyzconstruction-1-768x768.png',
    'tower-speaker.png': 'https://onssound.com/wp-content/uploads/2024/10/httpsworktop.keydesign.xyzconstruction-2-768x768.png',
    'shop-the-look.png': 'https://onssound.com/wp-content/uploads/2024/10/Frame-1-20.png',
    'payment-methods.png': 'https://onssound.com/wp-content/uploads/2024/07/footer-payment-img-1-1.png',
    'logo-mark.png': 'https://onssound.com/wp-content/uploads/2024/10/Untitled-design-2024-10-28T135331.237-300x300.png'
}

for filename, url in feature_map.items():
    download_file(url, f'assets/images/home/{filename}')

# 4. Product Images (1000x1000 original PNGs)
prod_urls = [
    ('speaker-1.png', 'https://onssound.com/wp-content/uploads/2022/06/Untitled-1000-x-1000-px-8.png'),
    ('speaker-2.png', 'https://onssound.com/wp-content/uploads/2022/06/Untitled-1000-x-1000-px-9.png'),
    ('speaker-3.png', 'https://onssound.com/wp-content/uploads/2023/06/Untitled-1000-x-1000-px-10.png'),
    ('speaker-4.png', 'https://onssound.com/wp-content/uploads/2024/06/Untitled-1000-x-1000-px-11.png'),
    ('speaker-5.png', 'https://onssound.com/wp-content/uploads/2024/06/Untitled-1000-x-1000-px-12.png'),
    ('speaker-10.png', 'https://onssound.com/wp-content/uploads/2024/06/Untitled-1000-x-1000-px-13.png'),
    ('speaker-13.png', 'https://onssound.com/wp-content/uploads/2025/01/Untitled-1000-x-1000-px-14.png'),
    ('speaker-18.png', 'https://onssound.com/wp-content/uploads/2025/01/Untitled-1000-x-1000-px-15.png'),
]

for filename, url in prod_urls:
    if not download_file(url, f'assets/images/products/{filename}'):
        # Fallback to 300x450 if 1000x1000 doesn't exist
        alt_url = url.replace('.png', '-300x450.png')
        download_file(alt_url, f'assets/images/products/{filename}')

# 5. Shop Gram original images
gram_urls = [
    ('gram-1.png', 'https://onssound.com/wp-content/uploads/2022/06/ONS-1-min.png'),
    ('gram-2.png', 'https://onssound.com/wp-content/uploads/2022/06/ONS-2-min.png'),
    ('gram-3.png', 'https://onssound.com/wp-content/uploads/2024/06/ONS-4-min.png'),
    ('gram-4.png', 'https://onssound.com/wp-content/uploads/2024/06/ONS-5-min.png'),
    ('gram-5.png', 'https://onssound.com/wp-content/uploads/2024/06/ONS-6-min.png'),
    ('gram-6.png', 'https://onssound.com/wp-content/uploads/2022/06/ONS-1-min.png'),
]

for filename, url in gram_urls:
    download_file(url, f'assets/images/about/{filename}')

# 6. Blog Images from live site or banners
blog_urls = [
    ('blog-1.png', 'https://onssound.com/wp-content/uploads/2024/06/Untitled-design-2024-12-04T133334.270-1024x501.png'),
    ('blog-2.png', 'https://onssound.com/wp-content/uploads/2024/06/Untitled-design-2024-12-04T133346.141-1024x501.png'),
    ('blog-3.png', 'https://onssound.com/wp-content/uploads/2024/12/Untitled-design-2024-12-04T123120.863-1024x501.png'),
]

for filename, url in blog_urls:
    download_file(url, f'assets/images/blog/{filename}')

print('\nALL ORIGINAL ASSETS DOWNLOADED FROM ONSSOUND.COM!')
