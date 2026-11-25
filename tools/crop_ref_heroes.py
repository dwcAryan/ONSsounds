from PIL import Image

# Crop top hero of homepage.png (1440x900)
im_home = Image.open('ref_img/homepage.png')
hero_home = im_home.crop((0, 0, 1440, 950))
hero_home.save('tools/ref_hero_home.png')

# Crop top hero of about-us.png (1440x800)
im_about = Image.open('ref_img/about-us.png')
hero_about = im_about.crop((0, 0, 1440, 800))
hero_about.save('tools/ref_hero_about.png')

print("Saved ref crops for inspection")
