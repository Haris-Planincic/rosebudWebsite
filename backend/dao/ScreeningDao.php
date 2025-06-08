<?php
require_once 'BaseDao.php';

class ScreeningDao extends BaseDao {
    public function __construct() {
        parent::__construct("Screenings", "screeningId");
    }

    public function getById($id, $primaryKey = 'screeningId') {
        return parent::getById($id, $primaryKey);
    }

    public function getAll() {
        $stmt = $this->connection->prepare("
            SELECT screeningId, screeningTitle, yearOfRelease, screeningTime, screeningImage
            FROM Screenings
            ORDER BY screeningTime ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
