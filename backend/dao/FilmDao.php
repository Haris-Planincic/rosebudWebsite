<?php
require_once 'BaseDao.php';

class FilmDao extends BaseDao {
    public function __construct() {
        parent::__construct("Films", "filmId");
    }

    public function getById($id, $primaryKey = 'filmId') {
        return parent::getById($id, $primaryKey);
    }

    public function delete($id, $primaryKey = 'filmId') {
        return parent::delete($id, $primaryKey);
    }
}

