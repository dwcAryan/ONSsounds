<?php
/**
 * ONS SOUND - Wishlist Model
 */

require_once __DIR__ . '/BaseModel.php';

class Wishlist extends BaseModel {
    protected string $table = 'wishlists';

    public function toggle(int $productId, ?int $userId = null, ?string $sessionId = null): array {
        if (!$sessionId) {
            $sessionId = get_guest_session_id();
        }

        if ($userId) {
            $stmt = $this->db->prepare("SELECT id FROM `wishlists` WHERE user_id = ? AND product_id = ? LIMIT 1");
            $stmt->execute([$userId, $productId]);
            $exists = $stmt->fetch();

            if ($exists) {
                $del = $this->db->prepare("DELETE FROM `wishlists` WHERE id = ?");
                $del->execute([$exists['id']]);
                return ['in_wishlist' => false, 'count' => $this->getCount($userId, $sessionId)];
            } else {
                $ins = $this->db->prepare("INSERT INTO `wishlists` (user_id, session_id, product_id, created_at) VALUES (?, ?, ?, NOW())");
                $ins->execute([$userId, $sessionId, $productId]);
                return ['in_wishlist' => true, 'count' => $this->getCount($userId, $sessionId)];
            }
        }

        // Guest session
        $stmt = $this->db->prepare("SELECT id FROM `wishlists` WHERE session_id = ? AND user_id IS NULL AND product_id = ? LIMIT 1");
        $stmt->execute([$sessionId, $productId]);
        $exists = $stmt->fetch();

        if ($exists) {
            $del = $this->db->prepare("DELETE FROM `wishlists` WHERE id = ?");
            $del->execute([$exists['id']]);
            return ['in_wishlist' => false, 'count' => $this->getCount($userId, $sessionId)];
        } else {
            $ins = $this->db->prepare("INSERT INTO `wishlists` (user_id, session_id, product_id, created_at) VALUES (NULL, ?, ?, NOW())");
            $ins->execute([$sessionId, $productId]);
            return ['in_wishlist' => true, 'count' => $this->getCount($userId, $sessionId)];
        }
    }

    public function getCount(?int $userId = null, ?string $sessionId = null): int {
        if ($userId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM `wishlists` WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            if (!$sessionId) $sessionId = get_guest_session_id();
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM `wishlists` WHERE session_id = ? AND user_id IS NULL");
            $stmt->execute([$sessionId]);
        }
        return (int)$stmt->fetchColumn();
    }

    public function getItems(?int $userId = null, ?string $sessionId = null): array {
        $sql = "SELECT w.id AS wishlist_id, p.* 
                FROM `wishlists` w 
                JOIN `products` p ON w.product_id = p.id 
                WHERE ";

        if ($userId) {
            $sql .= "w.user_id = ?";
            $params = [$userId];
        } else {
            if (!$sessionId) $sessionId = get_guest_session_id();
            $sql .= "w.session_id = ? AND w.user_id IS NULL";
            $params = [$sessionId];
        }

        $sql .= " ORDER BY w.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
