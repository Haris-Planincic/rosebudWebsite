<?php
require_once 'BaseDao.php';

class PaymentDao extends BaseDao {
    public function __construct() {
        parent::__construct("Payments", "paymentId");
    }

    public function getById($id, $primaryKey = 'paymentId') {
        return parent::getById($id, $primaryKey);
    }
}


