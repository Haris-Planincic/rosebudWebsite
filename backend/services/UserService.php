<?php
require_once __DIR__ . '/../dao/UserDao.php';

class UserService {
    private $dao;

    public function __construct() {
        $this->dao = new UserDao();
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function getById($id) {
        return $this->dao->getById($id);
    }

    public function create($data) {
    if (!isset($data['role'])) {
        $data['role'] = 'user'; // set default
    }

    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    return $this->dao->insert($data);
}


    public function update($id, $data) {
        return $this->dao->update($id, $data);
    }

    public function delete($id) {
        return $this->dao->delete($id);
    }

 public function searchByName($q, $meId) {
  return $this->dao->searchByName($q, (int)$meId);
}
public function getMe($id) {
  $u = $this->dao->getById($id);
  if (!$u) return null;
  unset($u['password']); // never send password hash
  return $u;
}

public function updateMe($id, $data) {
    $allowed = ['firstName', 'lastName', 'email'];
    $filtered = array_intersect_key($data, array_flip($allowed));

    $this->dao->update($id, $filtered);

    return $this->dao->getSafeById($id);
}


public function changePassword($id, $currentPassword, $newPassword) {
  $hash = $this->dao->getPasswordHashById($id);
  if (!$hash) {
    Flight::halt(404, "User not found");
  }

  if (!password_verify($currentPassword, $hash)) {
    Flight::halt(400, "Current password is incorrect");
  }

  if (strlen($newPassword) < 6) {
    Flight::halt(400, "New password must be at least 6 characters");
  }

  $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
  return $this->dao->updatePassword($id, $newHash);
}


}
?>
