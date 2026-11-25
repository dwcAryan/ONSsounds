<?php
/**
 * ONS SOUND — Dynamic Real-Time Live Search API
 * Returns matching products & quick links as JSON for the Search Drawer
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['q'] ?? '');
// Limit query to 60 characters to prevent excessive payload
$query = mb_substr($query, 0, 60);

if (strlen($query) < 1) {
    // Return empty result without querying DB
    echo json_encode([
        'success' => true,
        'is_empty' => true,
        'query' => '',
        'count' => 0,
        'products' => []
    ]);
    exit;
}

try {
    $db = get_db();
    $searchTerm = '%' . $query . '%';
    $exactTerm = $query . '%';

    $stmt = $db->prepare("
        SELECT p.id, p.name, p.slug, p.price, p.rating, p.reviews_count, p.main_image, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1
          AND (p.name LIKE ? OR p.short_description LIKE ? OR p.model_name LIKE ? OR c.name LIKE ?)
        ORDER BY 
          (p.name LIKE ?) DESC,
          (p.name LIKE ?) DESC,
          p.is_featured DESC,
          p.id ASC
        LIMIT 9
    ");

    $stmt->execute([
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $exactTerm,
        $searchTerm
    ]);

    $rows = $stmt->fetchAll() ?: [];
    $products = [];

    foreach ($rows as $row) {
        $products[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'price' => (float)$row['price'],
            'formatted_price' => format_price($row['price']),
            'rating' => (float)$row['rating'],
            'reviews_count' => (int)$row['reviews_count'],
            'image' => asset($row['main_image'] ?? 'assets/images/products/speaker-white.png'),
            'url' => url('product-details.php?slug=' . urlencode($row['slug'])),
            'stars_html' => render_stars($row['rating'] ?? 5, $row['reviews_count'] ?? 0)
        ];
    }

    echo json_encode([
        'success' => true,
        'is_empty' => false,
        'query' => $query,
        'count' => count($products),
        'products' => $products,
        'view_all_url' => url('products.php?q=' . urlencode($query))
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error querying search: ' . $e->getMessage(),
        'products' => []
    ]);
    exit;
}
