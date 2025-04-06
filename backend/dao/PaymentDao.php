<?php
require_once 'BaseDao.php';

class PaymentDao extends BaseDao {
   public function __construct() {
       parent::__construct("Payments");
   }

   public function getByUserId($userId) {
       $stmt = $this->connection->prepare("SELECT * FROM Payments WHERE userId = :userId");
       $stmt->bindParam(':userId', $userId);
       $stmt->execute();
       return $stmt->fetchAll();
   }
}
?>
