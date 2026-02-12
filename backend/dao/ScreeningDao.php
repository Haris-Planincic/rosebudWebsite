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
        SELECT 
            s.screeningId,
            s.screeningTitle,
            s.yearOfRelease,
            s.screeningTime,
            s.screeningImage,
            s.capacity,
            (
                SELECT COUNT(*)
                FROM ScreeningBookings sb
                WHERE sb.screeningId = s.screeningId
                  AND sb.status = 'paid'
            ) AS bookedCount
        FROM Screenings s
        ORDER BY s.screeningTime ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getByIdForUpdate($id) {
  $stmt = $this->connection->prepare("
    SELECT *
    FROM Screenings
    WHERE screeningId = :id
    FOR UPDATE
  ");
  $stmt->execute(['id' => (int)$id]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getCapacityForUpdate($id) {
  $stmt = $this->connection->prepare("
    SELECT screeningId, capacity
    FROM Screenings
    WHERE screeningId = :id
    FOR UPDATE
  ");
  $stmt->execute(['id' => (int)$id]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}
public function searchScreening($q) {
    $q = trim($q);
    $like = "%" . $q . "%";

    $stmt = $this->connection->prepare("
        SELECT 
            s.screeningId,
            s.screeningTitle,
            s.yearOfRelease,
            s.screeningTime,
            s.screeningImage,
            s.capacity,
            (
                SELECT COUNT(*)
                FROM ScreeningBookings sb
                WHERE sb.screeningId = s.screeningId
                  AND sb.status = 'paid'
            ) AS bookedCount 
        FROM Screenings s
        WHERE s.screeningTitle LIKE :q
           OR CAST(s.yearOfRelease AS CHAR) LIKE :q
        ORDER BY s.screeningTime ASC
    ");
    $stmt->bindParam(":q", $like, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>
