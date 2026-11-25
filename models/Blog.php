<?php
/**
 * ONS SOUND - Blog Model
 */

require_once __DIR__ . '/BaseModel.php';

class Blog extends BaseModel {
    protected string $table = 'blog_posts';

    public function getRecent(int $limit = 3): array {
        $sql = "SELECT b.*, c.name AS category_name, c.slug AS category_slug 
                FROM `blog_posts` b 
                LEFT JOIN `blog_categories` c ON b.category_id = c.id 
                WHERE b.is_published = 1 
                ORDER BY b.published_at DESC, b.id DESC 
                LIMIT " . (int)$limit;
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAllPublished(): array {
        $sql = "SELECT b.*, c.name AS category_name, c.slug AS category_slug 
                FROM `blog_posts` b 
                LEFT JOIN `blog_categories` c ON b.category_id = c.id 
                WHERE b.is_published = 1 
                ORDER BY b.published_at DESC, b.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array {
        $sql = "SELECT b.*, c.name AS category_name, c.slug AS category_slug 
                FROM `blog_posts` b 
                LEFT JOIN `blog_categories` c ON b.category_id = c.id 
                WHERE b.slug = ? AND b.is_published = 1 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }
}
