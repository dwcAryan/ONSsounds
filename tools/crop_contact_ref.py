from PIL import Image

# Let's crop from y=4000 to y=5200 in ref_img/homepage.png
im = Image.open('ref_img/homepage.png')
crop1 = im.crop((0, 4000, 1440, 4800))
crop1.save('tools/home_contact_crop.png')

crop2 = im.crop((0, 4700, 1440, 5500))
crop2.save('tools/home_contact_crop2.png')

print("Saved home_contact_crop.png and crop2.png")
