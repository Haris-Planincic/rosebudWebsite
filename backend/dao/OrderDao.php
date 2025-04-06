<?php
require_once 'BaseDao.php';

class OrderDao extends BaseDao {
   public function __construct() {
       parent::__construct("UserPurchases"); 
   }

   public function getByUserId($userId) {
       $stmt = $this->connection->prepare("SELECT * FROM UserPurchases WHERE userId = :userId");
       $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
} 
?>
