<?php
/**
 * ONS SOUND - Site Settings & Content Model
 */

require_once __DIR__ . '/BaseModel.php';

class Setting extends BaseModel {
    protected string $table = 'site_settings';

    public function get(string $key, string $default = ''): string {
        $stmt = $this->db->prepare("SELECT setting_value FROM `site_settings` WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false && $val !== null) ? $val : $default;
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM `site_settings`");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }

    public function getHomepageSection(string $sectionKey): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `homepage_sections` WHERE section_key = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$sectionKey]);
        return $stmt->fetch() ?: null;
    }

    public function getHomepageBanner(string $sectionKey = 'hero'): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `homepage_banners` WHERE section_key = ? AND is_active = 1 ORDER BY display_order ASC LIMIT 1");
        $stmt->execute([$sectionKey]);
        return $stmt->fetch() ?: null;
    }
}
