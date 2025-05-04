<?php
require_once 'BaseDao.php';

class ProductDao extends BaseDao {
    public function __construct() {
        parent::__construct("Products");
    }

    public function getById($id, $primaryKey = 'productId') {
        return parent::getById($id, $primaryKey);
    }
}



