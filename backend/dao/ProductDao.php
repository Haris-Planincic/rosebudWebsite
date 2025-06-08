<?php
require_once 'BaseDao.php';

class ProductDao extends BaseDao {
    public function __construct() {
        parent::__construct("Products", "productId");
    }

    public function getById($id, $primaryKey = 'productId') {
        return parent::getById($id, $primaryKey);
    }

    // Override getAll to include isSold field
    public function getAll() {
        $stmt = $this->connection->prepare("
            SELECT 
                p.*, 
                EXISTS (
                    SELECT 1 FROM Payments WHERE Payments.productId = p.productId
                ) AS isSold
            FROM Products p
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
