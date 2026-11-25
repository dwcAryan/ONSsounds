<?php
require_once __DIR__ . '/../config/database.php';
$db = get_db();

$db->exec("UPDATE product_images SET image_path = 'assets/images/products/speaker-10.png' WHERE product_id = 2");
$db->exec("UPDATE product_images SET image_path = 'assets/images/products/speaker-1.png' WHERE product_id = 1");
$db->exec("UPDATE product_images SET image_path = 'assets/images/products/speaker-4.png' WHERE product_id = 7");

echo "product_images gallery updated successfully!\n";
