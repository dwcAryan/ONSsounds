import urllib.request
from PIL import Image

urls = [
    'https://onssound.com/wp-content/uploads/2024/11/Untitled-800-x-300-px-600-x-300-px-900-x-300-px-900-x-500-px.png',
    'https://i0.wp.com/onssound.com/wp-content/uploads/2024/11/Untitled-800-x-300-px-600-x-300-px-900-x-300-px-900-x-500-px.png'
]

for url in urls:
    print('Trying to download:', url)
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=15) as resp:
            data = resp.read()
            print('Downloaded bytes:', len(data))
            with open('assets/images/home/contact-box-bg.png', 'wb') as f:
                f.write(data)
            im = Image.open('assets/images/home/contact-box-bg.png')
            print('Saved to assets/images/home/contact-box-bg.png! Size:', im.size)
            break
    except Exception as e:
        print('Error:', e)
