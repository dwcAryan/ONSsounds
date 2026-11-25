<?php
/**
 * ONS SOUND - Frontend Reconstruction Validation Suite
 */

echo "==========================================================" . PHP_EOL;
echo "   ONS SOUND: FRONTEND VALIDATION TEST SUITE" . PHP_EOL;
echo "==========================================================" . PHP_EOL . PHP_EOL;

$pass = 0;
$fail = 0;

function check(string $label, bool $condition, string $detail = ''): void {
    global $pass, $fail;
    if ($condition) {
        echo "[PASS] {$label}" . PHP_EOL;
        $pass++;
    } else {
        echo "[FAIL] {$label}" . ($detail ? " - {$detail}" : '') . PHP_EOL;
        $fail++;
    }
}

// 1. Files existence check
$pages = [
    'index.php' => 'Homepage',
    'products.php' => 'Products Catalog',
    'product-details.php' => 'Product Details',
    'blog.php' => 'Blog Listing',
    'blog-details.php' => 'Blog Article Reader',
    'about.php' => 'About Us',
    'contact.php' => 'Contact Us'
];

foreach ($pages as $file => $name) {
    check("File {$file} ({$name}) exists", file_exists(__DIR__ . '/../' . $file));
}

// 2. Reusable components check
$components = [
    'includes/header.php',
    'includes/footer.php',
    'includes/ticker.php',
    'includes/breadcrumb.php',
    'includes/section-heading.php',
    'includes/product-card.php',
    'includes/banner-component.php'
];

foreach ($components as $comp) {
    check("Component {$comp} exists", file_exists(__DIR__ . '/../' . $comp));
}

// 3. Asset Files check
$essentialImages = [
    'assets/images/home/studio-monitor.png',
    'assets/images/home/tower-speaker.png',
    'assets/images/home/shop-the-look.png',
    'assets/images/home/payment-methods.png',
    'assets/images/about/gram-1.png',
    'assets/images/products/speaker-1.png',
    'assets/images/products/speaker-10.png',
    'assets/images/categories/tweeters.png',
    'assets/images/blog/blog-1.png'
];

foreach ($essentialImages as $img) {
    check("Asset {$img} exists", file_exists(__DIR__ . '/../' . $img));
}

// 4. Output verification via HTTP
$urlMap = [
    'http://localhost/ONSsounds/index.php' => ['Hear every word with', 'At ONS Sound', 'Shop by Categories', 'Happy Clients'],
    'http://localhost/ONSsounds/products.php' => ['Products', 'Filter', 'Default sorting', 'Speaker 1'],
    'http://localhost/ONSsounds/product-details.php?slug=speaker-10' => ['Speaker 10', '15 FR200', 'Add to cart', 'Related products'],
    'http://localhost/ONSsounds/blog.php' => ['Blog', 'Read More', 'UNCATEGORIZED', 'TECH'],
    'http://localhost/ONSsounds/about.php' => ['Where innovation leads, sound follows', 'We are Onssound', 'Our Vision', 'Shop Gram'],
    'http://localhost/ONSsounds/contact.php' => ['Contact Us', 'Visit Our Store', 'Get in Touch', 'Spring Clearance Event']
];

foreach ($urlMap as $url => $needles) {
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $html = @file_get_contents($url, false, $ctx);
    if ($html === false) {
        check("HTTP fetch for {$url}", false, "Could not connect");
        continue;
    }
    
    $allFound = true;
    $missingNeedle = '';
    foreach ($needles as $needle) {
        if (!str_contains($html, $needle)) {
            $allFound = false;
            $missingNeedle = $needle;
            break;
        }
    }
    check("Content check for {$url}", $allFound, $missingNeedle ? "Missing text: '{$missingNeedle}'" : '');
}

echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "FRONTEND VALIDATION: {$pass} PASSED, {$fail} FAILED." . PHP_EOL;
echo "==========================================================" . PHP_EOL;

if ($fail > 0) exit(1);
