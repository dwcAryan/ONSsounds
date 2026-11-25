<?php
/**
 * ONS SOUND - Lightweight Base Model
 */

require_once __DIR__ . '/../config/database.php';

abstract class BaseModel {
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = get_db();
    }

    public function all(string $orderBy = 'id DESC'): array {
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findBy(string $field, mixed $value): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$field}` = ? LIMIT 1");
        $stmt->execute([$value]);
        return $stmt->fetch() ?: null;
    }

    public function where(string $field, mixed $value, string $orderBy = 'id DESC'): array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$field}` = ? ORDER BY {$orderBy}");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $keys = array_keys($data);
        $fields = implode('`, `', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));
        
        $sql = "INSERT INTO `{$this->table}` (`{$fields}`) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $set = [];
        $values = [];
        foreach ($data as $key => $val) {
            $set[] = "`{$key}` = ?";
            $values[] = $val;
        }
        $values[] = $id;

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $set) . " WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?");
        return $stmt->execute([$id]);
    }
}
