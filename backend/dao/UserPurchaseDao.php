<?php
require_once 'BaseDao.php';

class UserPurchaseDao extends BaseDao {
    public function __construct() {
        parent::__construct("UserPurchases");
    }

    public function getById($id, $primaryKey = 'purchaseId') {
        return parent::getById($id, $primaryKey);
    }
}


