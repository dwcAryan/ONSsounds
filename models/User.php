<?php
/**
 * ONS SOUND - User Model
 * Secure authentication with password_hash & password_verify
 */

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    protected string $table = 'users';

    public function authenticate(string $email, string $password): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([trim(strtolower($email))]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }

        return null;
    }

    public function register(string $name, string $email, string $password, ?string $phone = null): int {
        $cleanEmail = trim(strtolower($email));
        if ($this->findBy('email', $cleanEmail)) {
            throw new InvalidArgumentException("An account with this email address already exists.");
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->create([
            'role' => 'customer',
            'full_name' => clean($name),
            'email' => $cleanEmail,
            'password_hash' => $hash,
            'phone' => clean($phone),
            'status' => 'active'
        ]);
    }

    public function getById(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT id, role, full_name, email, phone, status, created_at, updated_at FROM `users` WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function updateProfile(int $userId, string $fullName, ?string $phone): bool {
        $stmt = $this->db->prepare("UPDATE `users` SET full_name = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([clean($fullName), clean($phone), $userId]);
    }

    public function updatePassword(int $userId, string $currentPassword, string $newPassword): bool {
        $stmt = $this->db->prepare("SELECT password_hash FROM `users` WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $currentHash = $stmt->fetchColumn();

        if (!$currentHash || !password_verify($currentPassword, $currentHash)) {
            throw new InvalidArgumentException("Current password does not match.");
        }

        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException("New password must be at least 6 characters long.");
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $this->db->prepare("UPDATE `users` SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        return $updateStmt->execute([$newHash, $userId]);
    }

    public function getAddresses(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM `addresses` WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAddressById(int $addressId, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `addresses` WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$addressId, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function addAddress(int $userId, array $data): int {
        // If this is set as default or user has no existing addresses, make it default
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM `addresses` WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $existingCount = (int)$countStmt->fetchColumn();

        $isDefault = (!empty($data['is_default']) || $existingCount === 0) ? 1 : 0;

        if ($isDefault) {
            $this->db->prepare("UPDATE `addresses` SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        }

        $sql = "INSERT INTO `addresses` (
            user_id, address_title, recipient_name, phone, address_line1, address_line2,
            city, state, postal_code, country, is_default, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            clean($data['address_title'] ?? 'Home'),
            clean($data['recipient_name']),
            clean($data['phone']),
            clean($data['address_line1']),
            clean($data['address_line2'] ?? null),
            clean($data['city']),
            clean($data['state']),
            clean($data['postal_code']),
            clean($data['country'] ?? 'India'),
            $isDefault
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateAddress(int $addressId, int $userId, array $data): bool {
        // Verify ownership first
        $existing = $this->getAddressById($addressId, $userId);
        if (!$existing) {
            return false;
        }

        $isDefault = !empty($data['is_default']) ? 1 : 0;
        if ($isDefault) {
            $this->db->prepare("UPDATE `addresses` SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        }

        $sql = "UPDATE `addresses` SET 
            address_title = ?, recipient_name = ?, phone = ?, address_line1 = ?, address_line2 = ?,
            city = ?, state = ?, postal_code = ?, country = ?, is_default = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            clean($data['address_title'] ?? 'Home'),
            clean($data['recipient_name']),
            clean($data['phone']),
            clean($data['address_line1']),
            clean($data['address_line2'] ?? null),
            clean($data['city']),
            clean($data['state']),
            clean($data['postal_code']),
            clean($data['country'] ?? 'India'),
            $isDefault,
            $addressId,
            $userId
        ]);
    }

    public function deleteAddress(int $addressId, int $userId): bool {
        $existing = $this->getAddressById($addressId, $userId);
        if (!$existing) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM `addresses` WHERE id = ? AND user_id = ?");
        $deleted = $stmt->execute([$addressId, $userId]);

        // If deleted address was default, promote the newest remaining address
        if ($deleted && $existing['is_default']) {
            $this->db->prepare("UPDATE `addresses` SET is_default = 1 WHERE user_id = ? ORDER BY id DESC LIMIT 1")->execute([$userId]);
        }

        return $deleted;
    }

    public function setDefaultAddress(int $addressId, int $userId): bool {
        $existing = $this->getAddressById($addressId, $userId);
        if (!$existing) {
            return false;
        }

        $this->db->prepare("UPDATE `addresses` SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        $stmt = $this->db->prepare("UPDATE `addresses` SET is_default = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([$addressId, $userId]);
    }

    public function getDefaultAddress(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `addresses` WHERE user_id = ? AND is_default = 1 LIMIT 1");
        $stmt->execute([$userId]);
        $address = $stmt->fetch();
        if (!$address) {
            $stmt = $this->db->prepare("SELECT * FROM `addresses` WHERE user_id = ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$userId]);
            $address = $stmt->fetch();
        }
        return $address ?: null;
    }

    public function createPasswordReset(string $email): ?string {
        $cleanEmail = trim(strtolower($email));
        $user = $this->findBy('email', $cleanEmail);
        if (!$user) {
            return null; // Return null if user does not exist
        }

        $token = bin2hex(random_bytes(32));
        
        // Remove old reset tokens for this email
        $this->db->prepare("DELETE FROM `password_resets` WHERE email = ?")->execute([$cleanEmail]);

        // Store new token valid for 1 hour
        $stmt = $this->db->prepare("INSERT INTO `password_resets` (email, token, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())");
        $stmt->execute([$cleanEmail, $token]);

        return $token;
    }

    public function verifyResetToken(string $token): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `password_resets` WHERE token = ? AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function resetPasswordWithToken(string $token, string $newPassword): bool {
        $reset = $this->verifyResetToken($token);
        if (!$reset) {
            throw new InvalidArgumentException("Invalid or expired reset token.");
        }

        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException("Password must be at least 6 characters long.");
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE `users` SET password_hash = ?, updated_at = NOW() WHERE email = ?");
        $updated = $stmt->execute([$hash, $reset['email']]);

        if ($updated) {
            $this->db->prepare("DELETE FROM `password_resets` WHERE email = ?")->execute([$reset['email']]);
        }

        return $updated;
    }
}
