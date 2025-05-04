<?php
require_once 'BaseDao.php';

class ScreeningDao extends BaseDao {
    public function __construct() {
        parent::__construct("Screenings");
    }

    public function getById($id, $primaryKey = 'screeningId') {
        return parent::getById($id, $primaryKey);
    }
}



