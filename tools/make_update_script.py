import urllib.parse
from config import * if False else None

# Connect via MySQL PDO using PHP or direct Python
import os

php_code = """<?php
require_once 'config/database.php';
$db = get_db();

// 1. Update categories with original PNGs
$catUpdates = [
    'tweeters' => 'assets/images/categories/tweeters.png',
    'midrange-speakers' => 'assets/images/categories/midrange-speakers.png',
    'woofers-subwoofers' => 'assets/images/categories/woofers-subwoofers.png',
    'speaker-cabinets' => 'assets/images/categories/speaker-cabinets.png',
    'amplifiers' => 'assets/images/categories/amplifiers.png',
    'portable-trolly-stage' => 'assets/images/categories/portable-trolly-stage.png',
];
$stmt = $db->prepare("UPDATE categories SET image = ? WHERE slug = ?");
foreach ($catUpdates as $slug => $img) {
    $stmt->execute([$img, $slug]);
}

// 2. Update products with original PNGs
$prodUpdates = [
    'speaker-1' => 'assets/images/products/speaker-1.png',
    'speaker-2' => 'assets/images/products/speaker-2.png',
    'speaker-3' => 'assets/images/products/speaker-3.png',
    'speaker-4' => 'assets/images/products/speaker-4.png',
    'speaker-5' => 'assets/images/products/speaker-5.png',
    'speaker-10' => 'assets/images/products/speaker-10.png',
    'speaker-13' => 'assets/images/products/speaker-13.png',
    'speaker-18' => 'assets/images/products/speaker-18.png',
];
$stmt = $db->prepare("UPDATE products SET main_image = ?, hover_image = ? WHERE slug = ?");
foreach ($prodUpdates as $slug => $img) {
    $stmt->execute([$img, $img, $slug]);
}

// 3. Update blogs with original PNGs
$blogUpdates = [
    1 => 'assets/images/blog/blog-1.png',
    2 => 'assets/images/blog/blog-2.png',
    3 => 'assets/images/blog/blog-3.png',
];
$stmt = $db->prepare("UPDATE blog_posts SET image = ? WHERE id = ?");
foreach ($blogUpdates as $id => $img) {
    $stmt->execute([$img, $id]);
}

echo "Database image paths updated to live original PNGs successfully!\\n";
"""

with open('tools/update_images.php', 'w', encoding='utf-8') as f:
    f.write(php_code)

print("Created tools/update_images.php")
