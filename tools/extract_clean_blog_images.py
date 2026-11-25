from PIL import Image

im = Image.open('ref_img/blog.png')

# Card 1: Wooden speakers
b1 = im.crop((50, 376, 477, 732))
b1.save('assets/images/blog/blog-1.png')
b1.convert('RGB').save('assets/images/blog/blog-1.jpg')

# Card 2: Pink cube speakers
b2 = im.crop((507, 376, 933, 732))
b2.save('assets/images/blog/blog-2.png')
b2.convert('RGB').save('assets/images/blog/blog-2.jpg')

# Card 3: Green monitor speakers
b3 = im.crop((963, 376, 1390, 732))
b3.save('assets/images/blog/blog-3.png')
b3.convert('RGB').save('assets/images/blog/blog-3.jpg')

print("Extracted exact unmasked blog images from ref_img/blog.png:")
print("  blog-1: size =", b1.size)
print("  blog-2: size =", b2.size)
print("  blog-3: size =", b3.size)
