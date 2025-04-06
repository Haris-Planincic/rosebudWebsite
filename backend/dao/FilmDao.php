<?php
require_once 'BaseDao.php';

class FilmDao extends BaseDao {
    public function __construct() {
        parent::__construct("Films");
    }
    public function getByTitle($title) {
        $stmt = $this->connection->prepare("SELECT * FROM Films WHERE filmTitle = :title");
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>