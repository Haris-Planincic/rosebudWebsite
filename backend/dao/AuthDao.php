<?php
require_once 'BaseDao.php';

class AuthDao extends BaseDao {
    public function __construct() {
        parent::__construct("Users", "userId");
    }

    public function get_user_by_email($email) {
        $stmt = $this->connection->prepare("SELECT * FROM Users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
