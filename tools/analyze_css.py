import re

with open('tools/ecomus_style.css', encoding='utf-8') as f:
    css = f.read()

# Look for typography definitions
print("=== ROOT CSS VARIABLES ===")
vars = re.findall(r'(--[a-zA-Z0-9_-]+:\s*[^;]+;)', css)
for v in vars[:30]:
    if any(k in v for k in ['font', 'color', 'size', 'width', 'primary']):
        print(' ', v)

# Look for font-family
print("\n=== FONT FAMILIES ===")
fonts = re.findall(r'font-family:\s*([^;]+);', css)
for f in set(fonts[:20]):
    print(' ', f)

# Look for body, headings, product card styles
print("\n=== HEADINGS & PRODUCT STYLES ===")
for match in re.finditer(r'(h[1-6]|body|\.product-item|\.product-title|\.price|\.site-header)[^{]*\{([^}]+)\}', css):
    sel = match.group(1).strip()
    props = match.group(2).strip()
    print(f"\nSelector: {sel}")
    for p in props.split(';'):
        if any(k in p for k in ['font', 'color', 'margin', 'padding', 'width', 'letter']):
            print('  ', p.strip())
