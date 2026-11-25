import urllib.request
import re

req = urllib.request.Request('https://onssound.com/', headers={'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15'})
with urllib.request.urlopen(req, timeout=10) as resp:
    html = resp.read().decode('utf-8', errors='ignore')

mpos = html.find('site-header__mobile')
endpos = html.find('</header>', mpos)
print(html[mpos:endpos+9])
