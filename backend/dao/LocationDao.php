<?php
require_once 'BaseDao.php';

class LocationDao extends BaseDao {
   public function __construct() {
       parent::__construct("Locations");
   }

   public function getByAddress($address) {
       $stmt = $this->connection->prepare("SELECT * FROM Locations WHERE locationAddress = :address");
       $stmt->bindParam(':address', $address);
       $stmt->execute();
       return $stmt->fetch();
   }
}
?>
