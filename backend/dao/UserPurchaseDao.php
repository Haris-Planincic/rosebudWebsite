<?php
require_once 'BaseDao.php';

class UserPurchaseDao extends BaseDao {
    public function __construct() {
        parent::__construct("UserPurchases", "purchaseId");
    }

    public function insertPurchase($data) {
        $sql = "INSERT INTO UserPurchases (userId, productId, purchaseDate)
                VALUES (:userId, :productId, :purchaseDate)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            "userId" => (int)$data["userId"],
            "productId" => (int)$data["productId"],
            "purchaseDate" => $data["purchaseDate"]
        ]);

        return (int)$this->connection->lastInsertId();
    }
    public function getPurchasesForUser($userId) {
    $stmt = $this->connection->prepare("
        SELECT
            up.purchaseId,
            up.purchaseDate,
            p.productId,
            p.productName,
            p.productPrice,
            p.productDescription,
            p.productImage,
            p.sellerId,
            CONCAT(u.firstName, ' ', u.lastName) AS sellerName
        FROM UserPurchases up
        JOIN Products p ON p.productId = up.productId
        JOIN Users u ON u.userId = p.sellerId
        WHERE up.userId = :uid
        ORDER BY up.purchaseDate DESC
    ");
    $stmt->execute(["uid" => (int)$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
