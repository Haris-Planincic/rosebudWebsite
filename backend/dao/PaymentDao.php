<?php
require_once 'BaseDao.php';

class PaymentDao extends BaseDao {
    public function __construct() {
        parent::__construct("Payments", "paymentId");
    }

    // Return PDO so services can run transactions safely
    public function getConnection() {
        return $this->connection;
    }

    // Insert payment and return its ID (not true/false)
    public function insertPayment($data) {
        $sql = "INSERT INTO Payments (userId, amount, paymentDate, productId)
                VALUES (:userId, :amount, :paymentDate, :productId)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            "userId" => (int)$data["userId"],
            "amount" => $data["amount"],
            "paymentDate" => $data["paymentDate"],
            "productId" => (int)$data["productId"]
        ]);

        return (int)$this->connection->lastInsertId();
    }
    public function insertPendingPayment($data) {
    $sql = "INSERT INTO Payments (userId, amount, paymentDate, productId, status, provider)
            VALUES (:userId, :amount, :paymentDate, :productId, 'pending', 'stripe')";
    $stmt = $this->connection->prepare($sql);
    $stmt->execute([
        "userId" => (int)$data["userId"],
        "amount" => $data["amount"],
        "paymentDate" => $data["paymentDate"],
        "productId" => (int)$data["productId"]
    ]);
    return (int)$this->connection->lastInsertId();
}

public function setProviderRef($paymentId, $providerRef) {
    $stmt = $this->connection->prepare("
        UPDATE Payments SET providerRef = :ref WHERE paymentId = :id
    ");
    $stmt->execute(["ref" => $providerRef, "id" => (int)$paymentId]);
}

public function setStatusByProviderRef($providerRef, $status) {
    $stmt = $this->connection->prepare("
        UPDATE Payments SET status = :status WHERE providerRef = :ref
    ");
    $stmt->execute(["status" => $status, "ref" => $providerRef]);
}

public function getByProviderRef($providerRef) {
    $stmt = $this->connection->prepare("
        SELECT * FROM Payments WHERE providerRef = :ref LIMIT 1
    ");
    $stmt->execute(["ref" => $providerRef]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Optional: prevent multiple pending holds for same product (15 min window)
public function hasActivePendingForProduct($productId) {
    $stmt = $this->connection->prepare("
        SELECT 1 FROM Payments
        WHERE productId = :pid
          AND status = 'pending'
          AND paymentDate >= (NOW() - INTERVAL 15 MINUTE)
        LIMIT 1
    ");
    $stmt->execute(["pid" => (int)$productId]);
    return (bool)$stmt->fetchColumn();
}
public function insertPendingBookingPayment($data) {
  $sql = "INSERT INTO Payments (userId, amount, paymentDate, bookingId, status, provider)
          VALUES (:userId, :amount, :paymentDate, :bookingId, 'pending', 'stripe')";
  $stmt = $this->connection->prepare($sql);
  $stmt->execute([
    "userId" => (int)$data["userId"],
    "amount" => $data["amount"],
    "paymentDate" => $data["paymentDate"],
    "bookingId" => (int)$data["bookingId"]
  ]);
  return (int)$this->connection->lastInsertId();
}

public function hasActivePendingForBooking($bookingId) {
  $stmt = $this->connection->prepare("
    SELECT 1 FROM Payments
    WHERE bookingId = :bid
      AND status = 'pending'
      AND paymentDate >= (NOW() - INTERVAL 15 MINUTE)
    LIMIT 1
  ");
  $stmt->execute(["bid" => (int)$bookingId]);
  return (bool)$stmt->fetchColumn();
}




}
