<?php
/**
 * ONS SOUND - Product Model
 */

require_once __DIR__ . '/BaseModel.php';

class Product extends BaseModel {
    protected string $table = 'products';

    /**
     * Get active products with sorting & filtering
     */
    public function getCatalog(array $filters = [], string $sort = 'default', int $limit = 12, int $offset = 0): array {
        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                FROM `{$this->table}` p 
                LEFT JOIN `categories` c ON p.category_id = c.id 
                WHERE p.is_active = 1";
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.short_description LIKE ? OR p.model_name LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }

        // Sorting
        $sql .= match ($sort) {
            'price_asc' => " ORDER BY p.price ASC",
            'price_desc' => " ORDER BY p.price DESC",
            'rating' => " ORDER BY p.rating DESC, p.reviews_count DESC",
            'newest' => " ORDER BY p.id DESC",
            default => " ORDER BY p.is_featured DESC, p.id ASC"
        };

        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Total count matching filter
     */
    public function getCatalogCount(array $filters = []): int {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` p WHERE p.is_active = 1";
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.short_description LIKE ? OR p.model_name LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get single product by slug with category info
     */
    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                                    FROM `{$this->table}` p 
                                    LEFT JOIN `categories` c ON p.category_id = c.id 
                                    WHERE p.slug = ? AND p.is_active = 1 LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get specifications from `product_specifications` table
     */
    public function getSpecifications(int $productId): array {
        $stmt = $this->db->prepare("SELECT spec_key, spec_value FROM `product_specifications` WHERE product_id = ? ORDER BY display_order ASC, id ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get product gallery images
     */
    public function getGallery(int $productId): array {
        $stmt = $this->db->prepare("SELECT image_path, is_primary FROM `product_images` WHERE product_id = ? ORDER BY display_order ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get approved reviews
     */
    public function getReviews(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM `product_reviews` WHERE product_id = ? AND status = 'approved' ORDER BY id DESC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Submit a customer review and sync aggregate rating
     */
    public function addReview(int $productId, ?int $userId, string $name, string $email, int $rating, ?string $reviewText = ''): array {
        if (empty($name)) {
            return ['success' => false, 'error' => 'Reviewer name is required.'];
        }

        $rating = max(1, min(5, $rating));
        $status = 'approved';
        $reviewContent = trim($reviewText ?? '');
        if ($reviewContent === '') {
            $reviewContent = "Rated {$rating} out of 5 stars.";
        }

        $stmt = $this->db->prepare("
            INSERT INTO `product_reviews` 
            (product_id, user_id, reviewer_name, reviewer_email, rating, review_text, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$productId, $userId, $name, $email, $rating, $reviewContent, $status]);

        $this->syncProductRating($productId);

        return ['success' => true, 'message' => 'Thank you! Your review has been published.'];
    }

    /**
     * Recalculate average rating and review count from approved reviews
     */
    public function syncProductRating(int $productId): void {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count, AVG(rating) as avg_rating 
            FROM `product_reviews` 
            WHERE product_id = ? AND status = 'approved'
        ");
        $stmt->execute([$productId]);
        $stats = $stmt->fetch();

        $count = (int)($stats['count'] ?? 0);
        $avg = $count > 0 ? round((float)$stats['avg_rating'], 1) : 0.0;

        $updateStmt = $this->db->prepare("UPDATE `{$this->table}` SET rating = ?, reviews_count = ? WHERE id = ?");
        $updateStmt->execute([$avg, $count, $productId]);
    }

    /**
     * Get related products
     */
    public function getRelated(int $categoryId, int $excludeId, int $limit = 4): array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE category_id = ? AND id != ? AND is_active = 1 ORDER BY is_featured DESC LIMIT " . (int)$limit);
        $stmt->execute([$categoryId, $excludeId]);
        return $stmt->fetchAll();
    }

    /**
     * Get featured products for homepage
     */
    public function getFeatured(int $limit = 8): array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE is_active = 1 ORDER BY is_featured DESC, id ASC LIMIT " . (int)$limit);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
