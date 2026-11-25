import urllib.request
import re

url = 'https://onssound.com/wp-content/themes/ecomus/style.css?ver=20240809'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=10) as resp:
        css = resp.read().decode('utf-8', errors='ignore')

    for term in ['site-header__mobile', 'header-mobile', 'header-hamburger']:
        matches = re.findall(r'[^{}]*' + term + r'[^{}]*\{[^}]+\}', css)
        print(f"=== {term} ({len(matches)} rules) ===")
        for m in matches[:5]:
            print(m)
except Exception as e:
    print('Error:', e)
