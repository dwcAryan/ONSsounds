with open('tools/live_images.txt', encoding='utf-8') as f:
    images = [line.strip() for line in f if line.strip()]

print(f'Total images: {len(images)}')
for img in images:
    print(img)

with open('tools/live_css.txt', encoding='utf-8') as f:
    css = [line.strip() for line in f if line.strip()]

print(f'\nTotal CSS: {len(css)}')
for c in css:
    print(c)
