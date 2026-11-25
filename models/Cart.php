<?php
/**
 * ONS SOUND - Cart Model
 * Operates on `carts` and `cart_items` tables
 */

require_once __DIR__ . '/BaseModel.php';

class Cart extends BaseModel {
    protected string $table = 'carts';

    /**
     * Get or create cart ID for current user or guest session
     */
    public function getActiveCartId(?int $userId = null, ?string $sessionId = null): int {
        if ($userId) {
            $stmt = $this->db->prepare("SELECT id FROM `carts` WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $cartId = $stmt->fetchColumn();

            if ($cartId) {
                return (int)$cartId;
            }

            // If user had a guest cart before logging in, attach it
            if ($sessionId) {
                $stmt = $this->db->prepare("SELECT id FROM `carts` WHERE session_id = ? AND user_id IS NULL LIMIT 1");
                $stmt->execute([$sessionId]);
                $guestCartId = $stmt->fetchColumn();
                if ($guestCartId) {
                    $upStmt = $this->db->prepare("UPDATE `carts` SET user_id = ? WHERE id = ?");
                    $upStmt->execute([$userId, $guestCartId]);
                    return (int)$guestCartId;
                }
            }

            $stmt = $this->db->prepare("INSERT INTO `carts` (user_id, session_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $sessionId]);
            return (int)$this->db->lastInsertId();
        }

        // Guest session
        if (!$sessionId) {
            $sessionId = get_guest_session_id();
        }

        $stmt = $this->db->prepare("SELECT id FROM `carts` WHERE session_id = ? AND user_id IS NULL LIMIT 1");
        $stmt->execute([$sessionId]);
        $cartId = $stmt->fetchColumn();

        if ($cartId) {
            return (int)$cartId;
        }

        $stmt = $this->db->prepare("INSERT INTO `carts` (user_id, session_id, created_at) VALUES (NULL, ?, NOW())");
        $stmt->execute([$sessionId]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get full cart items with product details
     */
    public function getCartContents(int $cartId): array {
        $sql = "SELECT ci.id AS item_id, ci.quantity, ci.product_id, 
                       p.name, p.slug, p.sku, p.price, p.main_image, p.stock_quantity,
                       (ci.quantity * p.price) AS subtotal
                FROM `cart_items` ci
                JOIN `products` p ON ci.product_id = p.id
                WHERE ci.cart_id = ?
                ORDER BY ci.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cartId]);
        return $stmt->fetchAll();
    }

    /**
     * Alias for getCartContents to ensure full compatibility
     */
    public function getItems(int $cartId): array {
        return $this->getCartContents($cartId);
    }

    /**
     * Add product to cart with strict stock availability validation
     */
    public function addItem(int $cartId, int $productId, int $qty = 1): array {
        // Validate product exists, is active and has stock
        $stmt = $this->db->prepare("SELECT id, name, price, stock_quantity, is_active FROM `products` WHERE id = ? LIMIT 1");
        $stmt->execute([$productId]);
        $prod = $stmt->fetch();

        if (!$prod || !$prod['is_active']) {
            return ['success' => false, 'message' => 'Product is currently unavailable.'];
        }

        if ($prod['stock_quantity'] <= 0) {
            return ['success' => false, 'message' => 'Sorry, this product is currently out of stock.'];
        }

        $checkStmt = $this->db->prepare("SELECT id, quantity FROM `cart_items` WHERE cart_id = ? AND product_id = ? LIMIT 1");
        $checkStmt->execute([$cartId, $productId]);
        $existing = $checkStmt->fetch();

        $currentQty = $existing ? (int)$existing['quantity'] : 0;
        $newQty = $currentQty + $qty;

        if ($newQty > $prod['stock_quantity']) {
            $newQty = (int)$prod['stock_quantity'];
            if ($existing) {
                $upStmt = $this->db->prepare("UPDATE `cart_items` SET quantity = ?, updated_at = NOW() WHERE id = ?");
                $upStmt->execute([$newQty, $existing['id']]);
            } else {
                $insStmt = $this->db->prepare("INSERT INTO `cart_items` (cart_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
                $insStmt->execute([$cartId, $productId, $newQty]);
            }
            return [
                'success' => true,
                'message' => 'Added ' . $prod['stock_quantity'] . ' units (maximum available stock).',
                'clamped' => true
            ];
        }

        if ($existing) {
            $upStmt = $this->db->prepare("UPDATE `cart_items` SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $upStmt->execute([$newQty, $existing['id']]);
        } else {
            $insStmt = $this->db->prepare("INSERT INTO `cart_items` (cart_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $insStmt->execute([$cartId, $productId, $newQty]);
        }

        return ['success' => true, 'message' => 'Product added to your cart.'];
    }

    /**
     * Update quantity with stock validation
     */
    public function updateQuantity(int $cartId, int $productId, int $qty): array {
        if ($qty <= 0) {
            $this->removeItem($cartId, $productId);
            return ['success' => true, 'message' => 'Item removed from cart.', 'qty' => 0];
        }

        $stmt = $this->db->prepare("SELECT stock_quantity FROM `products` WHERE id = ? LIMIT 1");
        $stmt->execute([$productId]);
        $stock = (int)$stmt->fetchColumn();

        if ($qty > $stock) {
            $qty = max(1, $stock);
            $upStmt = $this->db->prepare("UPDATE `cart_items` SET quantity = ?, updated_at = NOW() WHERE cart_id = ? AND product_id = ?");
            $upStmt->execute([$qty, $cartId, $productId]);
            return ['success' => true, 'message' => "Quantity adjusted to maximum available stock ($stock).", 'qty' => $qty];
        }

        $upStmt = $this->db->prepare("UPDATE `cart_items` SET quantity = ?, updated_at = NOW() WHERE cart_id = ? AND product_id = ?");
        $upStmt->execute([$qty, $cartId, $productId]);
        return ['success' => true, 'message' => 'Cart updated.', 'qty' => $qty];
    }

    /**
     * Remove item
     */
    public function removeItem(int $cartId, int $productId): bool {
        $stmt = $this->db->prepare("DELETE FROM `cart_items` WHERE cart_id = ? AND product_id = ?");
        return $stmt->execute([$cartId, $productId]);
    }

    /**
     * Clear all items in cart
     */
    public function clearCart(int $cartId): bool {
        $stmt = $this->db->prepare("DELETE FROM `cart_items` WHERE cart_id = ?");
        return $stmt->execute([$cartId]);
    }

    /**
     * Get item count
     */
    public function getItemCount(int $cartId): int {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM `cart_items` WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get grand total
     */
    public function getSubtotal(int $cartId): float {
        $sql = "SELECT COALESCE(SUM(ci.quantity * p.price), 0.00) 
                FROM `cart_items` ci 
                JOIN `products` p ON ci.product_id = p.id 
                WHERE ci.cart_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cartId]);
        return (float)$stmt->fetchColumn();
    }
}
