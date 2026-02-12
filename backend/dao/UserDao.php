<?php
require_once 'BaseDao.php';

class UserDao extends BaseDao {
    public function __construct() {
        parent::__construct("Users", "userId"); 
    }

    public function getById($id, $primaryKey = 'userId') {
        return parent::getById($id, $primaryKey);
    }
  public function searchByName($q, $meId) {
  $like = "%" . $q . "%";
  $meId = (int)$meId;

  $sql = "
    SELECT userId, firstName, lastName, email
    FROM Users
    WHERE userId <> :me
      AND (
        firstName LIKE :q
        OR lastName LIKE :q
        OR CONCAT(firstName, ' ', lastName) LIKE :q
        OR email LIKE :q
      )
    ORDER BY firstName ASC, lastName ASC
    LIMIT 10
  ";

  $stmt = $this->connection->prepare($sql);
  $stmt->bindParam(":me", $meId, PDO::PARAM_INT);
  $stmt->bindParam(":q", $like, PDO::PARAM_STR);
  $stmt->execute();

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getPasswordHashById($userId) {
  $stmt = $this->connection->prepare("SELECT password FROM Users WHERE userId = :id");
  $stmt->bindParam(":id", $userId, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ? $row['password'] : null;
}

public function updatePassword($userId, $hashedPassword) {
  $stmt = $this->connection->prepare("UPDATE Users SET password = :pw WHERE userId = :id");
  $stmt->bindParam(":pw", $hashedPassword, PDO::PARAM_STR);
  $stmt->bindParam(":id", $userId, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->rowCount() > 0;
}
public function getSafeById($id) {
    $stmt = $this->connection->prepare("
        SELECT userId, firstName, lastName, email, role, accountCreated
        FROM Users
        WHERE userId = :id
    ");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


}



