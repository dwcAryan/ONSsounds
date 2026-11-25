<?php
/**
 * ONS SOUND - Category Model
 */

require_once __DIR__ . '/BaseModel.php';

class Category extends BaseModel {
    protected string $table = 'categories';

    public function getActive(): array {
        $stmt = $this->db->query("SELECT * FROM `categories` WHERE is_active = 1 ORDER BY display_order ASC, name ASC");
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array {
        return $this->findBy('slug', $slug);
    }
}
