<?php
require_once 'BaseDao.php';

class LocationDao extends BaseDao {
    public function __construct() {
        parent::__construct("Locations");
    }

    public function getById($id, $primaryKey = 'locationId') {
        return parent::getById($id, $primaryKey);
    }
}
