<?php
require_once 'BaseDao.php';

class UserDao extends BaseDao {
    public function __construct() {
        parent::__construct("Users", "userId"); 
    }

    public function getById($id, $primaryKey = 'userId') {
        return parent::getById($id, $primaryKey);
    }
}



