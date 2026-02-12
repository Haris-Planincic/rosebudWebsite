<?php
require_once 'BaseDao.php';

class ProductDao extends BaseDao {
    public function __construct() {
        parent::__construct("Products", "productId");
    }

   public function getById($id) {
    $stmt = $this->connection->prepare("
        SELECT 
            p.*,
            CONCAT(u.firstName, ' ', u.lastName) AS sellerName
        FROM Products p
        JOIN Users u ON u.userId = p.sellerId
        WHERE p.productId = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => (int)$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


   public function getAll() {
    $stmt = $this->connection->prepare("
        SELECT p.*
        FROM Products p
        WHERE p.isSold = 0
        ORDER BY p.productId DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getByIdForUpdate($id) {
    $stmt = $this->connection->prepare("
        SELECT *
        FROM Products
        WHERE productId = :id
        FOR UPDATE
    ");
    $stmt->execute(['id' => (int)$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function markSold($id) {
    $stmt = $this->connection->prepare("
        UPDATE Products
        SET isSold = 1
        WHERE productId = :id
    ");
    return $stmt->execute(['id' => (int)$id]);
}
public function getOwnedById($productId, $sellerId) {
    $stmt = $this->connection->prepare("
        SELECT *
        FROM Products
        WHERE productId = :pid AND sellerId = :sid
        LIMIT 1
    ");
    $stmt->execute([
        'pid' => (int)$productId,
        'sid' => (int)$sellerId
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update ONLY if the product belongs to sellerId and (optionally) is not sold.
 * Returns number of affected rows.
 */
public function updateOwned($productId, $sellerId, $data, $blockIfSold = true) {
    $allowed = ['productName', 'productPrice', 'productDescription', 'productImage'];
    $setParts = [];
    $params = [
        'pid' => (int)$productId,
        'sid' => (int)$sellerId
    ];

    foreach ($allowed as $key) {
        if (array_key_exists($key, $data)) {
            $setParts[] = "{$key} = :{$key}";
            $params[$key] = $data[$key];
        }
    }

    if (count($setParts) === 0) {
        return 0;
    }

    $soldClause = $blockIfSold ? " AND isSold = 0 " : "";

    $sql = "
        UPDATE Products
        SET " . implode(", ", $setParts) . "
        WHERE productId = :pid AND sellerId = :sid
        {$soldClause}
    ";

    $stmt = $this->connection->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}
public function searchPublic($q) {
    $q = trim($q);
    $like = "%" . $q . "%";

    $stmt = $this->connection->prepare("
        SELECT p.*
        FROM Products p
        WHERE p.isSold = 0
          AND (
            p.productName LIKE :q
            OR p.productDescription LIKE :q
          )
        ORDER BY p.productId DESC
    ");
    $stmt->bindParam(":q", $like, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




}
