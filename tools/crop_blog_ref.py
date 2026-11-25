from PIL import Image

im_blog = Image.open('ref_img/blog.png')
print(f"ref_img/blog.png size: {im_blog.size}")

# Let's crop the top and post grid (e.g. 0 to 1200)
crop = im_blog.crop((0, 0, 1440, min(1400, im_blog.size[1])))
crop.save('tools/ref_blog_crop.png')
print("Saved tools/ref_blog_crop.png")
