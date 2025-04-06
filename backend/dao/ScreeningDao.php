<?php
require_once 'BaseDao.php';

class ScreeningDao extends BaseDao {
   public function __construct() {
       parent::__construct("Screenings");
   }

   public function getByFilmId($filmId) {
       $stmt = $this->connection->prepare("SELECT * FROM Screenings WHERE filmId = :filmId");
       $stmt->bindParam(':filmId', $filmId);
       $stmt->execute();
       return $stmt->fetchAll();
   }
}
?>
