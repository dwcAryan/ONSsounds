from PIL import Image

im_about = Image.open('ref_img/about-us.png')
# Total size is 1440 x 5406
# Let's crop:
# 1. We are Onssound and Vision 1 & 2 (y: 750 to 2200)
crop1 = im_about.crop((0, 750, 1440, 2200))
crop1.save('tools/ref_about_vision.png')

# 2. Quality is our priority & reviews & Shop gram (y: 2200 to 4500)
crop2 = im_about.crop((0, 2200, 1440, 4500))
crop2.save('tools/ref_about_quality_reviews.png')

print("Cropped vision and quality/reviews")
