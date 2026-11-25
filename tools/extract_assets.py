import os
from PIL import Image

def save_img(im, path):
    if im.mode in ('RGBA', 'LA', 'P'):
        im = im.convert('RGB')
    im.save(path, quality=95)

os.makedirs('assets/images/products', exist_ok=True)
os.makedirs('assets/images/categories', exist_ok=True)
os.makedirs('assets/images/blog', exist_ok=True)
os.makedirs('assets/images/banners', exist_ok=True)
os.makedirs('assets/images/about', exist_ok=True)
os.makedirs('assets/images/home', exist_ok=True)

# 1. Products from products.png (1440, 2208)
img_prod = Image.open('ref_img/products.png')

save_img(img_prod.crop((45, 255, 365, 595)), 'assets/images/products/speaker-1.jpg')
save_img(img_prod.crop((390, 255, 710, 595)), 'assets/images/products/speaker-10.jpg')
save_img(img_prod.crop((735, 255, 1055, 595)), 'assets/images/products/speaker-13.jpg')
save_img(img_prod.crop((1080, 255, 1400, 595)), 'assets/images/products/speaker-18.jpg')

save_img(img_prod.crop((45, 715, 365, 1055)), 'assets/images/products/speaker-2.jpg')
save_img(img_prod.crop((390, 715, 710, 1055)), 'assets/images/products/speaker-3.jpg')
save_img(img_prod.crop((735, 715, 1055, 1055)), 'assets/images/products/speaker-4.jpg')
save_img(img_prod.crop((1080, 715, 1400, 1055)), 'assets/images/products/speaker-5.jpg')

# 2. Homepage assets (1440, 6128)
img_home = Image.open('ref_img/homepage.png')

# Hero banner section
save_img(img_home.crop((0, 80, 1440, 725)), 'assets/images/banners/hero-banner.jpg')

# Category cards in "At ONS Sound"
save_img(img_home.crop((120, 1015, 500, 1400)), 'assets/images/categories/tweeters.jpg')
save_img(img_home.crop((530, 1015, 910, 1400)), 'assets/images/categories/midrange-speakers.jpg')
save_img(img_home.crop((940, 1015, 1320, 1400)), 'assets/images/categories/woofers-subwoofers.jpg')

save_img(img_home.crop((105, 1530, 485, 1915)), 'assets/images/categories/speaker-cabinets.jpg')
save_img(img_home.crop((530, 1530, 910, 1915)), 'assets/images/categories/amplifiers.jpg')
save_img(img_home.crop((940, 1530, 1320, 1915)), 'assets/images/categories/portable-trolly-stage.jpg')

# Feature speakers (studio monitor & tower speaker)
save_img(img_home.crop((810, 2260, 1205, 2650)), 'assets/images/home/studio-monitor.jpg')
save_img(img_home.crop((235, 2760, 525, 3260)), 'assets/images/home/tower-speaker.jpg')

# Contact section speaker background
save_img(img_home.crop((55, 3865, 1385, 4420)), 'assets/images/home/contact-box-bg.jpg')

# Shop the look speaker cones
save_img(img_home.crop((710, 4620, 1440, 5220)), 'assets/images/home/shop-the-look.jpg')

# 3. Blog assets from blog.png (1440, 1448)
img_blog = Image.open('ref_img/blog.png')
save_img(img_blog.crop((45, 375, 475, 715)), 'assets/images/blog/blog-1.jpg')
save_img(img_blog.crop((505, 375, 935, 715)), 'assets/images/blog/blog-2.jpg')
save_img(img_blog.crop((965, 375, 1395, 715)), 'assets/images/blog/blog-3.jpg')

# 4. About Us assets from about-us.png (1440, 5406)
img_about = Image.open('ref_img/about-us.png')
save_img(img_about.crop((0, 80, 1440, 735)), 'assets/images/about/about-hero.jpg')
save_img(img_about.crop((45, 1420, 710, 1980)), 'assets/images/about/vision-hand-speaker.jpg')
save_img(img_about.crop((725, 2160, 1395, 2840)), 'assets/images/about/vision-table-speaker.jpg')

for i in range(6):
    gx1 = int(45 + i * 224)
    gx2 = int(gx1 + 215)
    save_img(img_about.crop((gx1, 4280, gx2, 4520)), f'assets/images/about/gram-{i+1}.jpg')

# 5. Product details main speaker image
img_det = Image.open('ref_img/product-details.png')
save_img(img_det.crop((45, 330, 450, 860)), 'assets/images/products/speaker-10-large.jpg')

print('All authentic assets extracted successfully!')
