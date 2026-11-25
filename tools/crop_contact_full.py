from PIL import Image

im = Image.open('ref_img/homepage.png')
crop = im.crop((0, 3400, 1440, 4300))
crop.save('tools/home_contact_full.png')
print("Saved tools/home_contact_full.png")
