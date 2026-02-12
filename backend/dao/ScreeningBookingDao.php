<?php
require_once 'BaseDao.php';

class ScreeningBookingDao extends BaseDao {
  public function __construct() {
    parent::__construct("ScreeningBookings", "bookingId");
  }

  public function getByIdForUpdate($bookingId) {
    $stmt = $this->connection->prepare("
      SELECT *
      FROM ScreeningBookings
      WHERE bookingId = :id
      FOR UPDATE
    ");
    $stmt->execute(['id' => (int)$bookingId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function hasPaidBooking($userId, $screeningId) {
    $stmt = $this->connection->prepare("
      SELECT 1
      FROM ScreeningBookings
      WHERE userId = :uid AND screeningId = :sid AND status = 'paid'
      LIMIT 1
    ");
    $stmt->execute(['uid' => (int)$userId, 'sid' => (int)$screeningId]);
    return (bool)$stmt->fetchColumn();
  }

  public function markPaid($bookingId) {
    $stmt = $this->connection->prepare("
      UPDATE ScreeningBookings
      SET status = 'paid'
      WHERE bookingId = :id
    ");
    $stmt->execute(['id' => (int)$bookingId]);
  }
  public function hasActivePendingBooking($userId, $screeningId) {
  $stmt = $this->connection->prepare("
    SELECT 1
    FROM ScreeningBookings
    WHERE userId = :uid
      AND screeningId = :sid
      AND status = 'pending'
      AND bookingDate >= (NOW() - INTERVAL 15 MINUTE)
    LIMIT 1
  ");
  $stmt->execute([
    'uid' => (int)$userId,
    'sid' => (int)$screeningId
  ]);
  return (bool)$stmt->fetchColumn();
}
public function insertPendingBooking($userId, $screeningId) {
  $stmt = $this->connection->prepare("
    INSERT INTO ScreeningBookings (userId, screeningId, status, bookingDate)
    VALUES (:uid, :sid, 'pending', :dt)
  ");
  $stmt->execute([
    "uid" => (int)$userId,
    "sid" => (int)$screeningId,
    "dt"  => date('Y-m-d H:i:s')
  ]);

  return (int)$this->connection->lastInsertId();
}
public function countPaidBookingsForScreening($screeningId) {
  $stmt = $this->connection->prepare("
    SELECT COUNT(*)
    FROM ScreeningBookings
    WHERE screeningId = :sid AND status = 'paid'
  ");
  $stmt->execute(['sid' => (int)$screeningId]);
  return (int)$stmt->fetchColumn();
}
public function getMyBookings($userId) {
  $stmt = $this->connection->prepare("
    SELECT
      sb.bookingId,
      sb.status,
      sb.bookingDate,

      s.screeningId,
      s.screeningTitle,
      s.yearOfRelease,
      s.screeningTime,
      s.screeningImage,

      p.paymentId,
      p.amount,
      p.status AS paymentStatus,
      p.provider,
      p.providerRef,
      p.paymentDate
    FROM ScreeningBookings sb
    JOIN Screenings s ON s.screeningId = sb.screeningId
    LEFT JOIN Payments p ON p.bookingId = sb.bookingId AND p.userId = sb.userId
    WHERE sb.userId = :uid
      AND sb.status = 'paid'
    ORDER BY sb.bookingDate DESC
  ");
  $stmt->execute(['uid' => (int)$userId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



}
