<?php

namespace App\Models;

use PDO;
use Exception;

class Product
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Create a new product
     *
     * @param array $data
     * @return int Product ID
     * @throws Exception
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO products (name, sku, price, stock, created_at)
                VALUES (:name, :sku, :price, :stock, NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':sku' => $data['sku'],
                ':price' => $data['price'],
                ':stock' => $data['stock']
            ]);

            return (int) $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Failed to create product: " . $e->getMessage());
        }
    }

    /**
     * Update existing product
     *
     * @param string $sku
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function updateBySku(string $sku, array $data): bool
    {
        $sql = "UPDATE products
                SET name = :name, price = :price, stock = :stock, updated_at = NOW()
                WHERE sku = :sku";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':name' => $data['name'],
                ':price' => $data['price'],
                ':stock' => $data['stock'],
                ':sku' => $sku
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to update product: " . $e->getMessage());
        }
    }

    /**
     * Find product by SKU
     *
     * @param string $sku
     * @return array|null
     */
    public function findBySku(string $sku): ?array
    {
        $sql = "SELECT * FROM products WHERE sku = :sku LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sku' => $sku]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Create or update product (upsert)
     *
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function upsert(array $data): bool
    {
        $existing = $this->findBySku($data['sku']);

        if ($existing) {
            return $this->updateBySku($data['sku'], $data);
        } else {
            $this->create($data);
            return true;
        }
    }

    /**
     * Get all products with pagination
     *
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAll(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
