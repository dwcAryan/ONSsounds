with open('tools/onssound_home.html', 'r', encoding='utf-8') as f:
    html = f.read()

import re

# find header
pos = html.find('site-header')
if pos == -1:
    pos = html.find('header')
print("--- HEADER CONTEXT ---")
print(html[max(0, pos-200):min(len(html), pos+2500)])

# search for mobile header or navigation
mobile_matches = re.findall(r'<[^>]*class="[^"]*(?:mobile-header|header-mobile|menu-mobile|canvas-menu|offcanvas)[^"]*"[^>]*>', html)
print("\n--- MOBILE MATCHES ---")
for m in mobile_matches:
    print(m)
