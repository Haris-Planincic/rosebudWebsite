<?php
require_once __DIR__ . '/../dao/ScreeningBookingDao.php';

class ScreeningBookingService {
  private $dao;

  public function __construct() {
    $this->dao = new ScreeningBookingDao();
  }

 public function createPending($userId, $screeningId) {
  if ($this->dao->hasPaidBooking($userId, $screeningId)) {
    Flight::halt(409, "You already booked this screening.");
  }

  if ($this->dao->hasActivePendingBooking($userId, $screeningId)) {
    Flight::halt(409, "This screening is currently in checkout. Try again in a few minutes.");
  }

  $bookingId = $this->dao->insertPendingBooking((int)$userId, (int)$screeningId);
  return ["bookingId" => (int)$bookingId];
}


  public function markPaid($bookingId) {
    $this->dao->markPaid((int)$bookingId);
  }
  public function getMyPaidBookings($userId) {
  return $this->dao->getMyBookings((int)$userId);
}

}
