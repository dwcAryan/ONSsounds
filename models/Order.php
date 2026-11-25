<?php
/**
 * ONS SOUND - Order Model
 * Handles Order Booking without online payment
 * Guarantees transactional integrity and historical data preservation
 */

require_once __DIR__ . '/BaseModel.php';

class Order extends BaseModel {
    protected string $table = 'orders';

    /**
     * Create an Order Booking wrapped in a Database Transaction with Inventory Updates
     */
    public function createBooking(array $customerData, array $cartItems): array {
        if (empty($cartItems)) {
            throw new InvalidArgumentException("Cannot create an order with an empty cart.");
        }

        $this->db->beginTransaction();

        try {
            // 1. Stock Validation with row locking
            foreach ($cartItems as $item) {
                $pCheck = $this->db->prepare("SELECT stock_quantity, name, is_active FROM `products` WHERE id = ? FOR UPDATE");
                $pCheck->execute([$item['product_id']]);
                $prod = $pCheck->fetch();

                if (!$prod || !$prod['is_active']) {
                    throw new RuntimeException("Product '{$item['name']}' is no longer available.");
                }

                if ($prod['stock_quantity'] < $item['quantity']) {
                    throw new RuntimeException("Insufficient stock for '{$item['name']}'. Only {$prod['stock_quantity']} units available.");
                }
            }

            // 2. Generate clean unique order booking number
            $orderNumber = 'ONS-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            // 3. Calculate subtotal
            $subtotal = 0.00;
            foreach ($cartItems as $item) {
                $subtotal += ($item['quantity'] * $item['price']);
            }
            $shippingFee = 0.00;
            $totalAmount = $subtotal + $shippingFee;

            // 4. Insert master order record
            $sql = "INSERT INTO `orders` (
                order_number, user_id, customer_name, customer_email, customer_phone,
                shipping_address, subtotal, shipping_fee, total_amount, payment_method,
                order_status, customer_notes, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $orderNumber,
                $customerData['user_id'] ?? null,
                $customerData['name'],
                $customerData['email'],
                $customerData['phone'],
                $customerData['address'],
                $subtotal,
                $shippingFee,
                $totalAmount,
                'Order Booking (Pay on Delivery/Contact)',
                $customerData['notes'] ?? null
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // 5. Insert order items snapshot & decrement inventory stock
            $itemSql = "INSERT INTO `order_items` (
                order_id, product_id, product_name, product_sku, product_image, price, quantity, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $itemStmt = $this->db->prepare($itemSql);

            $decStmt = $this->db->prepare("UPDATE `products` SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE id = ?");

            foreach ($cartItems as $item) {
                $itemSubtotal = ($item['quantity'] * $item['price']);
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $item['sku'] ?? null,
                    $item['main_image'] ?? null,
                    $item['price'],
                    $item['quantity'],
                    $itemSubtotal
                ]);

                // Decrement inventory stock
                $decStmt->execute([$item['quantity'], $item['product_id']]);
            }

            // 6. Clear cart contents if cartId provided
            if (!empty($customerData['cart_id'])) {
                $this->db->prepare("DELETE FROM `cart_items` WHERE cart_id = ?")->execute([$customerData['cart_id']]);
            }

            // Commit the transaction
            $this->db->commit();

            // 7. Dispatch Email Notifications via PHPMailer / SMTP without risking order integrity
            if (function_exists('send_email')) {
                // Customer Order Confirmation Email
                try {
                    $custSubject = "Order Booking Confirmed: #" . $orderNumber . " - ONS SOUND";
                    $itemsHtml = '';
                    foreach ($cartItems as $item) {
                        $itemsHtml .= "
                        <tr>
                            <td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($item['name']) . "</td>
                            <td style='padding: 8px; border-bottom: 1px solid #eee; text-align: center;'>" . (int)$item['quantity'] . "</td>
                            <td style='padding: 8px; border-bottom: 1px solid #eee; text-align: right;'>" . format_price($item['price'] * $item['quantity']) . "</td>
                        </tr>";
                    }

                    $custBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e5e5e5; border-radius: 8px;'>
                        <h2 style='color: #111; margin-bottom: 4px;'>ONS SOUND</h2>
                        <h3 style='color: #2e7d32; margin-top: 0;'>Order Booking Confirmed!</h3>
                        <p>Hello " . htmlspecialchars($customerData['name']) . ",</p>
                        <p>Thank you for choosing ONS Sound. Your precision audio order booking has been received and is being processed.</p>
                        <div style='background: #f8f9fa; padding: 14px; border-radius: 6px; margin: 16px 0;'>
                            <p style='margin: 4px 0;'><strong>Order Number:</strong> " . htmlspecialchars($orderNumber) . "</p>
                            <p style='margin: 4px 0;'><strong>Status:</strong> Pending (Confirmed Booking)</p>
                            <p style='margin: 4px 0;'><strong>Payment Method:</strong> Pay on Delivery / Direct Contact</p>
                            <p style='margin: 4px 0;'><strong>Delivery Address:</strong> " . nl2br(htmlspecialchars($customerData['address'])) . "</p>
                        </div>
                        <table style='width: 100%; border-collapse: collapse; margin: 16px 0;'>
                            <thead>
                                <tr style='background: #111; color: #fff;'>
                                    <th style='padding: 8px; text-align: left;'>Product</th>
                                    <th style='padding: 8px; text-align: center;'>Qty</th>
                                    <th style='padding: 8px; text-align: right;'>Total</th>
                                </tr>
                            </thead>
                            <tbody>" . $itemsHtml . "</tbody>
                            <tfoot>
                                <tr>
                                    <td colspan='2' style='padding: 10px 8px; text-align: right; font-weight: bold;'>Total:</td>
                                    <td style='padding: 10px 8px; text-align: right; font-weight: bold; font-size: 16px;'>" . format_price($totalAmount) . "</td>
                                </tr>
                            </tfoot>
                        </table>
                        <p style='color: #666; font-size: 13px;'>Our audio engineers will contact you prior to dispatch for shipment confirmation.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='color: #999; font-size: 12px; margin: 0;'>ONS SOUND &mdash; Hear every word with precision sound.</p>
                    </div>";

                    @send_email($customerData['email'], $custSubject, $custBody, true);
                } catch (\Throwable $eCust) {
                    error_log("Failed sending customer order confirmation: " . $eCust->getMessage());
                }

                // Store Owner Notification Email
                try {
                    $ownerEmail = defined('OWNER_EMAIL') ? OWNER_EMAIL : (defined('STORE_EMAIL') ? STORE_EMAIL : 'aryan.n.shah.04@gmail.com');
                    $ownerSubject = "New Order Booking: #" . $orderNumber . " - " . format_price($totalAmount);
                    $ownerBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e5e5e5; border-radius: 8px;'>
                        <h3 style='color: #111; margin-bottom: 12px;'>New Customer Order Booking Received</h3>
                        <p style='margin: 6px 0;'><strong>Order Number:</strong> " . htmlspecialchars($orderNumber) . "</p>
                        <p style='margin: 6px 0;'><strong>Customer:</strong> " . htmlspecialchars($customerData['name']) . "</p>
                        <p style='margin: 6px 0;'><strong>Email:</strong> " . htmlspecialchars($customerData['email']) . "</p>
                        <p style='margin: 6px 0;'><strong>Phone:</strong> " . htmlspecialchars($customerData['phone']) . "</p>
                        <p style='margin: 6px 0;'><strong>Total Amount:</strong> " . format_price($totalAmount) . "</p>
                        <p style='margin: 6px 0;'><strong>Shipping Address:</strong> " . nl2br(htmlspecialchars($customerData['address'])) . "</p>
                        <p style='margin: 6px 0;'><strong>Notes:</strong> " . htmlspecialchars($customerData['notes'] ?? 'None') . "</p>
                        <table style='width: 100%; border-collapse: collapse; margin-top: 16px;'>
                            <thead>
                                <tr style='background: #f4f4f4;'>
                                    <th style='padding: 8px; text-align: left;'>Product</th>
                                    <th style='padding: 8px; text-align: center;'>Qty</th>
                                    <th style='padding: 8px; text-align: right;'>Price</th>
                                </tr>
                            </thead>
                            <tbody>" . $itemsHtml . "</tbody>
                        </table>
                    </div>";

                    @send_email($ownerEmail, $ownerSubject, $ownerBody, true);
                } catch (\Throwable $eOwner) {
                    error_log("Failed sending owner order notification: " . $eOwner->getMessage());
                }
            }

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Order booking creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get order details with items
     */
    public function getOrderWithItems(int $orderId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `orders` WHERE id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        $itemStmt = $this->db->prepare("SELECT * FROM `order_items` WHERE order_id = ?");
        $itemStmt->execute([$orderId]);
        $order['items'] = $itemStmt->fetchAll();

        return $order;
    }

    /**
     * Get order by order_number
     */
    public function getByOrderNumber(string $orderNumber): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `orders` WHERE order_number = ? LIMIT 1");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        $itemStmt = $this->db->prepare("SELECT * FROM `order_items` WHERE order_id = ?");
        $itemStmt->execute([$order['id']]);
        $order['items'] = $itemStmt->fetchAll();

        return $order;
    }

    /**
     * Get user orders
     */
    public function getUserOrders(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get specific user order with items - strictly scoped to user_id to prevent unauthorized access
     */
    public function getUserOrderWithItems(int $orderId, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        $itemStmt = $this->db->prepare("SELECT * FROM `order_items` WHERE order_id = ?");
        $itemStmt->execute([$orderId]);
        $order['items'] = $itemStmt->fetchAll();

        return $order;
    }
}
