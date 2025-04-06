<?php
require_once 'BaseDao.php';

class ProductDao extends BaseDao {
   public function __construct() {
       parent::__construct("Products");
   }

   public function getByName($name) {
       $stmt = $this->connection->prepare("SELECT * FROM Products WHERE productName = :name");
       $stmt->bindParam(':name', $name);
       $stmt->execute();
       return $stmt->fetch();
   }
}
?>
